<?php

namespace App\Http\Controllers;

use App\Http\Requests\Karyawan\KaryawanCreateRequest;
use App\Http\Requests\Karyawan\KaryawanUpdateRequest;
use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\Role;
use App\Models\User;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    use WithModuleFilter;

    private function generateNoAnggota(string $kategori): string
    {
        $prefix = $kategori === 'karyawan' ? 'KRY-' : 'ANG-';
        $tahun = date('Y');
        $maxRetry = 10;
        $retry = 0;

        do {
            $random = str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            $candidate = $prefix . $tahun . '-' . $random;
            $exists = Anggota::where('no_anggota', $candidate)->exists();
            $retry++;
        } while ($exists && $retry < $maxRetry);

        if ($exists) {
            throw new \RuntimeException('Gagal generate Nomor Anggota unik setelah ' . $maxRetry . ' percobaan. Silakan coba kembali.');
        }

        return $candidate;
    }

    public function index(Request $request)
    {
        try {
            if (auth()->check() && auth()->user()->hasRole('Karyawan')) {
                $dataKaryawan = Anggota::with(['cabang', 'user'])
                    ->where('users_id', auth()->id())
                    ->where('kategori_anggota', 'karyawan')
                    ->get();
                $cabangFilterList = collect();
                $currentModuleFilter = ['cabang_id' => null, 'tanggal_awal' => null, 'tanggal_akhir' => null];
                $isKaryawanFilter = true;
                $moduleFilterSummary = null;
                $filterFormAction = route('karyawan.index');
                $hideFilterBar = true;
                return view("modules.karyawan.index", compact(
                    'dataKaryawan',
                    'cabangFilterList',
                    'currentModuleFilter',
                    'isKaryawanFilter',
                    'moduleFilterSummary',
                    'filterFormAction',
                    'hideFilterBar',
                ));
            }

            if (!$request->has('apply_filter') && !$request->has('_reset_filter') && !$request->isMethod('POST')) {
                $request->session()->forget('module_filter_v2_anggota');
            }
            
            $resolved = $this->resolveFilter($request, 'anggota', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'created_at',
                'force_scope_cabang_user' => false,
            ]);

            $query = Anggota::where('kategori_anggota', 'karyawan')
                ->with(['cabang', 'user']);

            $this->applyFilter($query, $resolved);

            $dataKaryawan = $query->orderBy('created_at', 'desc')->get();
            $filterFormAction = route('anggota.index');
            $filterView = $this->buildViewFilterVars($resolved);

            return view("modules.karyawan.index", array_merge(compact(
                'dataKaryawan',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data karyawan: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);

        return view("modules.karyawan.create", compact('cabang'));
    }

    public function store(KaryawanCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $kodeRole = "ROL-KARYAWAN";
            $roleTarget = Role::where('kode_role', $kodeRole)->first();

            $userId = null;
            if ($request->filled('email') && $roleTarget) {
                $email = trim(mb_strtolower($request->input('email')));
                $nomorHp = $request->filled('no_hp') ? $request->input('no_hp') : null;

                $user = User::create([
                    'id' => Str::uuid()->toString(),
                    'cabang_id' => $request->input('cabang_id'),
                    'nama' => $request->input('nama'),
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role_id' => $roleTarget->id,
                    'nomor_hp' => $nomorHp,
                    'is_active' => ($request->input('status') === 'aktif') ? '1' : '0',
                    'force_change_password' => true,
                    'password_changed_at' => null,
                ]);
                $userId = $user->id;
            }

            $payload = $request->validated();
            $payload['no_anggota'] = $this->generateNoAnggota("karyawan");
            $payload['users_id'] = $userId;
            unset($payload['email']);
            $payload['kategori_anggota'] = 'karyawan';

            Anggota::create($payload);

            DB::commit();

            $msg = 'Data berhasil disimpan. Nomor Karyawan: <b>' . e($payload['no_anggota']) . '</b>';
            if ($userId) {
                $namaRole = "karyawan";
                $msg .= '. Akun login otomatis dibuat (Role: <b>' . $namaRole . '</b>): Email <b>' . e($request->input('email')) . '</b>, Password default: <b>password</b> (wajib diganti saat login pertama).';
            } else {
                $msg .= '. Catatan: Email karyawan tidak diisi, akun login tidak dibuat. Isi email karyawan jika ingin karyawan bisa login mandiri.';
            }
            return redirect()->route('karyawan.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $karyawan = Anggota::with(['cabang:id,kode_cabang,nama_cabang', 'user', 'verifikator:id,nama'])->findOrFail($id);

            $user = Auth::user();
            if ($user && $user->hasRole('Karyawan')) {
                if ((string) $karyawan->users_id !== (string) $user->id) {
                    abort(403, 'Anda tidak memiliki izin melihat data karyawan lain.');
                }
            }
            if ($user && ($user->hasRole('Teller') || $user->hasRole('Kepala Cabang')) && $user->cabang_id) {
                if ((string) $karyawan->cabang_id !== (string) $user->cabang_id) {
                    abort(403, 'Anda hanya bisa melihat karyawan di cabang Anda sendiri.');
                }
            }

            $daftarSimpanan = \App\Models\Simpanan::with('jenisSimpanan:id,nama_jenis')
                ->where('anggota_id', $karyawan->id)
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $totalSimpanan = (float) $daftarSimpanan->last()?->saldo ?? (float) $daftarSimpanan->sum('nominal');
            $simpananPerJenis = $daftarSimpanan->groupBy(fn($s) => $s->jenisSimpanan?->nama_jenis ?? 'Lainnya')
                ->map(fn($grp) => (float) $grp->last()?->saldo ?? 0);

            $daftarPinjaman = \App\Models\Pinjaman::with(['jenisPinjaman:id,nama_jenis', 'angsuran'])
                ->where('anggota_id', $karyawan->id)
                ->orderBy('tgl_pengajuan', 'desc')
                ->get();

            return view("modules.karyawan.show", compact(
                'karyawan',
                'daftarSimpanan',
                'totalSimpanan',
                'simpananPerJenis',
                'daftarPinjaman',
            ));
        } catch (\Exception $e) {
            return redirect()->route('anggota.index')->with('error', 'Gagal memuat detail anggota: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $karyawan = Anggota::with('user')->findOrFail($id);
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);

        return view("modules.karyawan.edit", compact('karyawan', 'cabang'));
    }

    public function update(KaryawanUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $karyawan = Anggota::findOrFail($id);
            $kategoriBaru = $request->input('kategori_anggota', 'karyawan');
            $kategoriLama = $karyawan->kategori_anggota ?? 'karyawan';
            $kodeRoleBaru = "ROL-KARYAWAN";
            $roleTarget = Role::where('kode_role', $kodeRoleBaru)->first();

            $emailBaru = $request->filled('email') ? trim(mb_strtolower($request->input('email'))) : null;
            $emailLama = $karyawan->user?->email;

            $user = null;
            if ($karyawan->users_id) {
                $user = User::find($karyawan->users_id);
            }

            $isStaff = $user && (
                $user->hasRole('Administrator') ||
                $user->hasRole('Teller') ||
                $user->hasRole('Kepala Cabang')
            );

            if ($emailBaru && $roleTarget) {
                if ($user) {
                    $updateUser = [
                        'cabang_id' => $request->input('cabang_id'),
                        'nama' => $request->input('nama'),
                        'email' => $emailBaru,
                        'nomor_hp' => $request->filled('no_hp') ? $request->input('no_hp') : $user->nomor_hp,
                        'is_active' => ($request->input('status') === 'aktif') ? '1' : '0',
                    ];
                    if (! $isStaff && $kategoriBaru !== $kategoriLama) {
                        $updateUser['role_id'] = $roleTarget->id;
                    }
                    $user->update($updateUser);
                } else {
                    $user = User::create([
                        'id' => Str::uuid()->toString(),
                        'cabang_id' => $request->input('cabang_id'),
                        'nama' => $request->input('nama'),
                        'email' => $emailBaru,
                        'password' => Hash::make('password'),
                        'role_id' => $roleTarget->id,
                        'nomor_hp' => $request->filled('no_hp') ? $request->input('no_hp') : null,
                        'is_active' => ($request->input('status') === 'aktif') ? '1' : '0',
                        'force_change_password' => true,
                        'password_changed_at' => null,
                    ]);
                }
            } elseif (! $emailBaru && $user && $roleTarget) {
                if (! $isStaff) {
                    if ($karyawan->users_id === $user->id) {
                        $karyawan->users_id = null;
                        $karyawan->save();
                    }
                    $user->delete();
                    $user = null;
                }
            } elseif (! $emailBaru && $user) {
                if ($request->input('status') === 'nonaktif' && ($user->hasRole('Anggota') || $user->hasRole('Karyawan'))) {
                    $user->update(['is_active' => '0']);
                }
            }

            if (! $emailBaru && $user && ! $isStaff && $kategoriBaru !== $kategoriLama && $roleTarget) {
                $user->update(['role_id' => $roleTarget->id]);
            }

            $payload = $request->validated();
            if ($user) {
                $payload['users_id'] = $user->id;
            }
            unset($payload['email']);

            $karyawan->update($payload);

            DB::commit();

            $msg = 'Data berhasil diubah';
            if ($kategoriBaru !== $kategoriLama && $user && ! $isStaff) {
                $namaRoleBaru = "Karyawan";
                $msg .= '. Role akun login otomatis disesuaikan menjadi <b>' . $namaRoleBaru . '</b>.';
            }
            if ($emailBaru && ! $emailLama && $user) {
                $msg .= ' Akun login karyawan baru dibuat: Email <b>' . e($emailBaru) . '</b>, Password default: <b>password</b> (wajib diganti saat login pertama).';
            }
            return redirect()->route('karyawan.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $karyawan = Anggota::findOrFail($id);
            $userId = $karyawan->users_id;
            $karyawan->delete();

            if ($userId) {
                $user = User::find($userId);
                if ($user && $user->hasRole('Karyawan')) {
                    $user->delete();
                }
            }

            DB::commit();
            return redirect()->route('karyawan.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }

    public function pendaftaranMenunggu()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect(route('login'));
        }

        $dataAnggota = Anggota::with(['cabang', 'user', 'verifikator:id,nama'])
            ->where('status_pendaftaran', 'menunggu_verifikasi')
            ->when($user->hasRole('Teller') || $user->hasRole('Kepala Cabang'), function ($q) use ($user) {
                $cabangId = $user->cabang_id;
                if ($cabangId) {
                    $q->where('cabang_id', $cabangId);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('modules.anggota.pendaftaran-menunggu', compact('dataAnggota'));
    }

    public function formVerifikasiPendaftaran($id)
    {
        $anggota = Anggota::with(['cabang', 'user'])->findOrFail($id);
        if ($anggota->status_pendaftaran !== 'menunggu_verifikasi') {
            return redirect(route('anggota.pendaftaran.menunggu'))
                ->with('warning', 'Anggota ini sudah diverifikasi sebelumnya.');
        }
        $user = Auth::user();
        if (($user->hasRole('Teller') || $user->hasRole('Kepala Cabang')) && $user->cabang_id) {
            if ((string) $anggota->cabang_id !== (string) $user->cabang_id) {
                return redirect(route('anggota.pendaftaran.menunggu'))
                    ->with('error', 'Anda hanya bisa memverifikasi anggota di cabang Anda sendiri.');
            }
        }

        $noAnggotaSuggest = 'ANG-' . Carbon::now()->format('Y') . '-' . str_pad((string) (random_int(1, 99999)), 5, '0', STR_PAD_LEFT);

        return view('modules.anggota.verifikasi-pendaftaran', compact('anggota', 'noAnggotaSuggest'));
    }

    public function verifikasiPendaftaran(AnggotaVerifikasiPendaftaranRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $anggota = Anggota::with(['cabang', 'user'])->findOrFail($id);
            if ($anggota->status_pendaftaran !== 'menunggu_verifikasi') {
                return redirect(route('anggota.pendaftaran.menunggu'))
                    ->with('warning', 'Anggota ini sudah diverifikasi sebelumnya.');
            }

            $userAuth = Auth::user();
            if (($userAuth->hasRole('Teller') || $userAuth->hasRole('Kepala Cabang')) && $userAuth->cabang_id) {
                if ((string) $anggota->cabang_id !== (string) $userAuth->cabang_id) {
                    return redirect(route('anggota.pendaftaran.menunggu'))
                        ->with('error', 'Anda hanya bisa memverifikasi anggota di cabang Anda sendiri.');
                }
            }

            $keputusan = (string) $request->input('keputusan');
            $anggota->status_pendaftaran = $keputusan;
            $anggota->tgl_verifikasi_pendaftaran = now();
            $anggota->catatan_verifikasi_pendaftaran = trim((string) $request->input('catatan_verifikasi_pendaftaran'));
            $anggota->verifikator_users_id = $userAuth->id;

            if ($keputusan === 'disetujui') {
                $anggota->no_anggota = trim((string) $request->input('no_anggota_baru'));
                $anggota->status_anggota = now()->toDateString();
                $anggota->status = 'aktif';
            }

            $anggota->save();

            if ($keputusan === 'disetujui') {
                $passwordDefault = trim((string) $request->input('password_default'));
                if ($passwordDefault === '') {
                    $passwordDefault = 'Password123!';
                }
                $userAnggota = null;
                if ($anggota->users_id) {
                    $userAnggota = User::find($anggota->users_id);
                }
                if ($userAnggota) {
                    $userAnggota->force_change_password = true;
                    $userAnggota->password = Hash::make($passwordDefault);
                    $userAnggota->is_active = true;
                    $userAnggota->save();
                }

                $emailTujuan = $userAnggota?->email;
                if ($emailTujuan) {
                    try {
                        Mail::to($emailTujuan)->send(new PendaftaranAnggotaDisetujuiMail($anggota, $passwordDefault, $emailTujuan));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            } else {
                if ($anggota->users_id) {
                    $userAnggota = User::find($anggota->users_id);
                    if ($userAnggota) {
                        $userAnggota->is_active = false;
                        $userAnggota->save();
                    }
                    try {
                        Mail::to($userAnggota->email)->send(new PendaftaranAnggotaDitolakMail($anggota));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }

            try {
                AktivitasLog::record(
                    'VERIFIKASI_PENDAFTARAN',
                    'Verifikasi pendaftaran anggota ' . $anggota->nama . ' (NIK ' . $anggota->nik . ') oleh ' . $userAuth->nama . ' (ID ' . $userAuth->id . ') => KEPUTUSAN=' . strtoupper($keputusan) . '. No.Anggota=' . ($anggota->no_anggota ?? '-') . '. Catatan: ' . Str::limit($anggota->catatan_verifikasi_pendaftaran, 200)
                );
            } catch (\Throwable $e) {
                report($e);
            }

            DB::commit();

            if ($keputusan === 'disetujui') {
                $msg = 'Pendaftaran anggota <b>' . e($anggota->nama) . '</b> <span class="text-success">DISETUJUI</span>. Nomor Anggota: <b>' . e($anggota->no_anggota) . '</b>. Akun login: <b>' . e($anggota->user?->email ?? '-') . '</b>, Password default: <b>' . e($passwordDefault) . '</b>. Email notifikasi telah dikirim ke anggota.';
                return redirect(route('anggota.pendaftaran.menunggu'))
                    ->with('success', $msg);
            }

            return redirect(route('anggota.pendaftaran.menunggu'))
                ->with('warning', 'Pendaftaran anggota <b>' . e($anggota->nama) . '</b> <span class="text-danger">DITOLAK</span>. Akun login dinonaktifkan dan email notifikasi penolakan telah dikirim.')
                ->with('catatan_penolakan', $anggota->catatan_verifikasi_pendaftaran);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Verifikasi gagal diproses: ' . $e->getMessage());
        }
    }
}

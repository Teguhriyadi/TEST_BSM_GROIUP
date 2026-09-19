<?php

namespace App\Http\Controllers;

use App\Mail\PendaftaranAnggotaDitolakMail;
use App\Mail\PendaftaranAnggotaDisetujuiMail;
use App\Models\Anggota;
use App\Models\AktivitasLog;
use App\Models\Cabang;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\Anggota\AnggotaCreateRequest;
use App\Http\Requests\Anggota\AnggotaUpdateRequest;
use App\Http\Requests\Anggota\AnggotaVerifikasiPendaftaranRequest;
use App\Traits\WithModuleFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AnggotaController extends Controller
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
            if (auth()->check() && auth()->user()->hasRole('Anggota')) {
                $dataAnggota = Anggota::with(['cabang', 'user'])
                    ->where('users_id', auth()->id())
                    ->get();
                $cabangFilterList = collect();
                $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
                $isAnggotaFilter = true;
                $moduleFilterSummary = null;
                $filterFormAction = route('anggota.index');
                $hideFilterBar = true;
                return view("modules.anggota.index", compact(
                    'dataAnggota',
                    'cabangFilterList',
                    'currentModuleFilter',
                    'isAnggotaFilter',
                    'moduleFilterSummary',
                    'filterFormAction',
                    'hideFilterBar',
                ));
            }

            $resolved = $this->resolveFilter($request, 'anggota', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'created_at',
                'force_scope_cabang_user' => true,
            ]);
            $query = Anggota::with(['cabang', 'user']);
            $this->applyFilter($query, $resolved);
            $dataAnggota = $query->orderBy('created_at', 'desc')->get();
            $filterFormAction = route('anggota.index');
            $filterView = $this->buildViewFilterVars($resolved);
            return view("modules.anggota.index", array_merge(compact(
                'dataAnggota',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data anggota: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);
        $kategoriOptions = [
            ['value' => 'anggota_baru', 'label' => 'Anggota Baru'],
            ['value' => 'karyawan', 'label' => 'Karyawan'],
        ];
        $nextNoAnggota = 'ANG-' . date('Y') . '-_____';
        return view("modules.anggota.create", compact('cabang', 'kategoriOptions', 'nextNoAnggota'));
    }

    public function store(AnggotaCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $kategori = $request->input('kategori_anggota', 'anggota_baru');
            $kodeRole = $kategori === 'karyawan' ? 'ROL-KARYAWAN' : 'ROL-ANGGOTA';
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
            if (empty(trim((string) $payload['no_anggota']))) {
                $payload['no_anggota'] = $this->generateNoAnggota($kategori);
            }
            $payload['users_id'] = $userId;
            unset($payload['email']);

            Anggota::create($payload);

            DB::commit();

            $msg = 'Data berhasil disimpan. Nomor Anggota: <b>' . e($payload['no_anggota']) . '</b>';
            if ($userId) {
                $namaRole = $kategori === 'karyawan' ? 'Karyawan' : 'Anggota';
                $msg .= '. Akun login otomatis dibuat (Role: <b>' . $namaRole . '</b>): Email <b>' . e($request->input('email')) . '</b>, Password default: <b>password</b> (wajib diganti saat login pertama).';
            } else {
                $msg .= '. Catatan: Email anggota tidak diisi, akun login tidak dibuat. Isi email anggota jika ingin anggota bisa login mandiri.';
            }
            return redirect()->route('anggota.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $anggota = Anggota::with(['cabang:id,kode_cabang,nama_cabang', 'user', 'verifikator:id,nama'])->findOrFail($id);

            $user = Auth::user();
            if ($user && $user->hasRole('Anggota')) {
                if ((string) $anggota->users_id !== (string) $user->id) {
                    abort(403, 'Anda tidak memiliki izin melihat data anggota lain.');
                }
            }
            if ($user && ($user->hasRole('Teller') || $user->hasRole('Kepala Cabang')) && $user->cabang_id) {
                if ((string) $anggota->cabang_id !== (string) $user->cabang_id) {
                    abort(403, 'Anda hanya bisa melihat anggota di cabang Anda sendiri.');
                }
            }

            $daftarSimpanan = \App\Models\Simpanan::with('jenisSimpanan:id,nama_jenis')
                ->where('anggota_id', $anggota->id)
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $totalSimpanan = (float) $daftarSimpanan->last()?->saldo ?? (float) $daftarSimpanan->sum('nominal');
            $simpananPerJenis = $daftarSimpanan->groupBy(fn($s) => $s->jenisSimpanan?->nama_jenis ?? 'Lainnya')
                ->map(fn($grp) => (float) $grp->last()?->saldo ?? 0);

            $daftarPinjaman = \App\Models\Pinjaman::with(['jenisPinjaman:id,nama_jenis', 'angsuran'])
                ->where('anggota_id', $anggota->id)
                ->orderBy('tgl_pengajuan', 'desc')
                ->get();

            return view("modules.anggota.show", compact(
                'anggota',
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
        $anggota = Anggota::with('user')->findOrFail($id);
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);
        $kategoriOptions = [
            ['value' => 'anggota_baru', 'label' => 'Anggota Baru'],
            ['value' => 'karyawan', 'label' => 'Karyawan'],
        ];
        return view("modules.anggota.edit", compact('anggota', 'cabang', 'kategoriOptions'));
    }

    public function update(AnggotaUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $anggota = Anggota::findOrFail($id);
            $kategoriBaru = $request->input('kategori_anggota', 'anggota_baru');
            $kategoriLama = $anggota->kategori_anggota ?? 'anggota_baru';
            $kodeRoleBaru = $kategoriBaru === 'karyawan' ? 'ROL-KARYAWAN' : 'ROL-ANGGOTA';
            $roleTarget = Role::where('kode_role', $kodeRoleBaru)->first();

            $emailBaru = $request->filled('email') ? trim(mb_strtolower($request->input('email'))) : null;
            $emailLama = $anggota->user?->email;

            $user = null;
            if ($anggota->users_id) {
                $user = User::find($anggota->users_id);
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
                    if ($anggota->users_id === $user->id) {
                        $anggota->users_id = null;
                        $anggota->save();
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
            if (empty(trim((string) $payload['no_anggota']))) {
                $payload['no_anggota'] = $anggota->no_anggota;
            }
            if ($user) {
                $payload['users_id'] = $user->id;
            }
            unset($payload['email']);

            $anggota->update($payload);

            DB::commit();

            $msg = 'Data berhasil diubah. Nomor Anggota: <b>' . e($anggota->no_anggota) . '</b>';
            if ($kategoriBaru !== $kategoriLama && $user && ! $isStaff) {
                $namaRoleBaru = $kategoriBaru === 'karyawan' ? 'Karyawan' : 'Anggota';
                $msg .= '. Role akun login otomatis disesuaikan menjadi <b>' . $namaRoleBaru . '</b>.';
            }
            if ($emailBaru && ! $emailLama && $user) {
                $msg .= ' Akun login anggota baru dibuat: Email <b>' . e($emailBaru) . '</b>, Password default: <b>password</b> (wajib diganti saat login pertama).';
            }
            return redirect()->route('anggota.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $anggota = Anggota::findOrFail($id);
            $userId = $anggota->users_id;
            $anggota->delete();

            if ($userId) {
                $user = User::find($userId);
                if ($user && $user->hasRole('Anggota')) {
                    $user->delete();
                }
            }

            DB::commit();
            return redirect()->route('anggota.index')->with('success', 'Data berhasil dihapus');
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

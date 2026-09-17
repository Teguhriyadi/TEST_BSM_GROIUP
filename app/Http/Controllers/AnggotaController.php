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
        return view("modules.anggota.create", compact('cabang'));
    }

    public function store(AnggotaCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $roleAnggota = Role::where('kode_role', 'ROL-ANGGOTA')->first();

            $userId = null;
            if ($request->filled('email') && $roleAnggota) {
                $email = trim(mb_strtolower($request->input('email')));
                $nomorHp = $request->filled('no_hp') ? $request->input('no_hp') : null;

                $user = User::create([
                    'id' => Str::uuid()->toString(),
                    'cabang_id' => $request->input('cabang_id'),
                    'nama' => $request->input('nama'),
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role_id' => $roleAnggota->id,
                    'nomor_hp' => $nomorHp,
                    'is_active' => ($request->input('status') === 'aktif') ? '1' : '0',
                    'force_change_password' => true,
                    'password_changed_at' => null,
                ]);
                $userId = $user->id;
            }

            $payload = $request->validated();
            $payload['users_id'] = $userId;
            unset($payload['email']);

            Anggota::create($payload);

            DB::commit();

            $msg = 'Data berhasil disimpan';
            if ($userId) {
                $msg .= '. Akun login anggota otomatis dibuat: Email <b>' . e($request->input('email')) . '</b>, Password default: <b>password</b> (wajib diganti saat login pertama).';
            } else {
                $msg .= '. Catatan: Email anggota tidak diisi, akun login tidak dibuat. Isi email anggota jika ingin anggota bisa login mandiri.';
            }
            return redirect()->route('anggota.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $anggota = Anggota::with('user')->findOrFail($id);
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);
        return view("modules.anggota.edit", compact('anggota', 'cabang'));
    }

    public function update(AnggotaUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $anggota = Anggota::findOrFail($id);
            $roleAnggota = Role::where('kode_role', 'ROL-ANGGOTA')->first();

            $emailBaru = $request->filled('email') ? trim(mb_strtolower($request->input('email'))) : null;
            $emailLama = $anggota->user?->email;

            $user = null;
            if ($anggota->users_id) {
                $user = User::find($anggota->users_id);
            }

            if ($emailBaru && $roleAnggota) {
                if ($user) {
                    $user->update([
                        'cabang_id' => $request->input('cabang_id'),
                        'nama' => $request->input('nama'),
                        'email' => $emailBaru,
                        'nomor_hp' => $request->filled('no_hp') ? $request->input('no_hp') : $user->nomor_hp,
                        'is_active' => ($request->input('status') === 'aktif') ? '1' : '0',
                    ]);
                } else {
                    $user = User::create([
                        'id' => Str::uuid()->toString(),
                        'cabang_id' => $request->input('cabang_id'),
                        'nama' => $request->input('nama'),
                        'email' => $emailBaru,
                        'password' => Hash::make('password'),
                        'role_id' => $roleAnggota->id,
                        'nomor_hp' => $request->filled('no_hp') ? $request->input('no_hp') : null,
                        'is_active' => ($request->input('status') === 'aktif') ? '1' : '0',
                        'force_change_password' => true,
                        'password_changed_at' => null,
                    ]);
                }
            } elseif (! $emailBaru && $user && $roleAnggota) {
                if (! $user->hasRole('Administrator') && ! $user->hasRole('Teller') && ! $user->hasRole('Kepala Cabang')) {
                    if ($anggota->users_id === $user->id) {
                        $anggota->users_id = null;
                        $anggota->save();
                    }
                    $user->delete();
                    $user = null;
                }
            } elseif (! $emailBaru && $user) {
                if ($request->input('status') === 'nonaktif' && $user->hasRole('Anggota')) {
                    $user->update(['is_active' => '0']);
                }
            }

            $payload = $request->validated();
            if ($user) {
                $payload['users_id'] = $user->id;
            }
            unset($payload['email']);

            $anggota->update($payload);

            DB::commit();

            $msg = 'Data berhasil diubah';
            if ($emailBaru && ! $emailLama && $user) {
                $msg .= '. Akun login anggota baru dibuat: Email <b>' . e($emailBaru) . '</b>, Password default: <b>password</b> (wajib diganti saat login pertama).';
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

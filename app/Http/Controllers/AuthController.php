<?php

namespace App\Http\Controllers;

use App\Http\Requests\Anggota\RegisterAnggotaRequest;
use App\Http\Requests\Password\ForgotPasswordRequest;
use App\Http\Requests\Password\ResetPasswordRequest;
use App\Http\Requests\Password\UpdatePasswordSelfRequest;
use App\Mail\PendaftaranAnggotaDiterimaMail;
use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\AktivitasLog;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login()
    {
        return view("auth.login");
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                $userLogin = auth()->user();
                if ($userLogin->hasRole('Anggota')) {
                    $anggotaProfil = Anggota::where('users_id', $userLogin->id)->first();
                    if ($anggotaProfil) {
                        if ($anggotaProfil->status_pendaftaran === 'menunggu_verifikasi') {
                            Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();
                            return back()
                                ->withInput($request->only('email'))
                                ->with('error', 'Maaf, pendaftaran Anda sedang diverifikasi petugas kami. Silakan cek email notifikasi secara berkala atau hubungi kantor cabang terdekat.');
                        }
                        if ($anggotaProfil->status_pendaftaran === 'ditolak') {
                            Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();
                            return back()
                                ->withInput($request->only('email'))
                                ->with('error', 'Maaf, pendaftaran Anda ditolak. Silakan hubungi kantor cabang untuk klarifikasi lebih lanjut.');
                        }
                    }
                }

                try {
                    AktivitasLog::record(
                        'LOGIN',
                        'User login: ' . auth()->user()->nama . ' (' . auth()->user()->email . ')'
                    );
                } catch (\Throwable $e) { report($e); }

                return redirect()
                    ->intended('/modules/dashboard')
                    ->with('success', 'Anda Berhasil Login');
            }

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email atau password tidak sesuai');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Terjadi kesalahan pada sistem: ' . $e->getMessage());
        }
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request)
    {
        try {
            $user = User::where('email', $request->input('email'))->first();

            if (! $user) {
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Email tidak ditemukan.');
            }

            $token = Str::random(60);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            try {
                $user->notify(new ResetPasswordNotification($token, $user->email));
            } catch (\Throwable $e) {
                report($e);
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Gagal mengirim email reset password. Periksa konfigurasi SMTP atau hubungi administrator. Error: ' . $e->getMessage());
            }

            return back()
                ->with('success', 'Link reset password berhasil dikirim ke email Anda. Silakan cek kotak masuk / spam email Anda untuk melanjutkan.');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $email = $request->input('email');
            $tokenInput = $request->input('token');

            $resetRow = DB::table('password_resets')
                ->where('email', $email)
                ->first();

            if (! $resetRow) {
                return back()
                    ->withInput()
                    ->with('error', 'Permintaan reset password tidak ditemukan atau sudah kedaluwarsa.');
            }

            if (! Hash::check($tokenInput, $resetRow->token)) {
                return back()
                    ->withInput()
                    ->with('error', 'Token reset password tidak valid.');
            }

            $createdAt = \Carbon\Carbon::parse($resetRow->created_at);
            if ($createdAt->diffInMinutes(now()) > 60) {
                DB::table('password_resets')->where('email', $email)->delete();
                DB::commit();
                return redirect(route('password.request'))
                    ->with('error', 'Link reset password sudah kedaluwarsa. Silakan ajukan permintaan reset password kembali.');
            }

            $user = User::where('email', $email)->firstOrFail();
            $user->password = Hash::make($request->input('password_baru'));
            $user->force_change_password = false;
            $user->password_changed_at = now();
            $user->save();

            try {
                AktivitasLog::record(
                    'RESET_PASSWORD',
                    'User reset password via email link: ' . $user->nama . ' (' . $user->email . ')'
                );
            } catch (\Throwable $e) { report($e); }

            DB::table('password_resets')->where('email', $email)->delete();

            DB::commit();
            return redirect(route('login'))
                ->with('success', 'Password Anda berhasil direset. Silakan login dengan password baru Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }

    public function updatePasswordSelf(UpdatePasswordSelfRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            $user->password = Hash::make($request->input('password_baru'));
            $user->force_change_password = false;
            $user->password_changed_at = now();
            $user->save();

            AktivitasLog::record(
                'UBAH_PASSWORD',
                'User memperbarui password sendiri: ' . $user->nama . ' (' . $user->email . ')'
            );

            DB::commit();
            return redirect()->back()->with('success', 'Password berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['password_lama' => $e->getMessage()])->with('error', 'Gagal memperbarui password: ' . $e->getMessage());
        }
    }

    public function showRegisterAnggota()
    {
        $cabang = Cabang::where('is_active', '1')
            ->orderBy('nama_cabang', 'asc')
            ->get(['id', 'nama_cabang', 'alamat']);
        return view('auth.register-anggota', compact('cabang'));
    }

    public function registerAnggota(RegisterAnggotaRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->safe()->only([
                'cabang_id', 'nik', 'nama', 'jenis_kelamin', 'alamat', 'tgl_lahir', 'no_hp',
            ]);
            $data['status'] = 'aktif';
            $data['status_pendaftaran'] = 'menunggu_verifikasi';
            $data['no_anggota'] = null;
            $data['status_anggota'] = null;

            $anggota = Anggota::create($data);

            $roleAnggota = Role::where('kode_role', 'ROL-ANGGOTA')->first();
            if (! $roleAnggota) {
                throw new \RuntimeException('Konfigurasi role anggota belum tersedia. Hubungi Administrator.');
            }

            $passwordBaru = (string) $request->input('password');
            $user = User::create([
                'id' => (string) Str::uuid(),
                'cabang_id' => $anggota->cabang_id,
                'name' => trim((string) $request->input('nama')),
                'nama' => trim((string) $request->input('nama')),
                'email' => Str::lower(trim((string) $request->input('email'))),
                'password' => Hash::make($passwordBaru),
                'force_change_password' => false,
                'is_active' => true,
                'role_id' => $roleAnggota->id,
                'nomor_hp' => $request->filled('no_hp') ? trim((string) $request->input('no_hp')) : null,
            ]);
            
            $anggota->users_id = $user->id;
            $anggota->save();

            try {
                AktivitasLog::record(
                    'REGISTRASI_ANGGOTA',
                    'Pendaftaran anggota mandiri: NIK ' . $anggota->nik . ' - ' . $anggota->nama . ' (email: ' . $user->email . '). Status: menunggu_verifikasi. ID anggota: ' . $anggota->id
                );
            } catch (\Throwable $e) {
                report($e);
            }

            DB::commit();

            try {
                Mail::to($user->email)->send(new PendaftaranAnggotaDiterimaMail($anggota));
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect(route('login'))
                ->with('success', 'Pendaftaran berhasil! Akun Anda saat ini statusnya MENUNGGU VERIFIKASI petugas cabang. Kami juga mengirimkan notifikasi ke email Anda. Silakan tunggu email pemberitahuan selanjutnya atau cek secara berkala.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Pendaftaran gagal diproses. Silakan coba kembali. Error: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $namaUser = auth()->check() ? auth()->user()->nama : 'Guest';
        try {
            AktivitasLog::record(
                'LOGOUT',
                'User logout: ' . $namaUser
            );
        } catch (\Throwable $e) { report($e); }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/pages/login')
            ->with('success', 'Anda Berhasil Logout');
    }
}

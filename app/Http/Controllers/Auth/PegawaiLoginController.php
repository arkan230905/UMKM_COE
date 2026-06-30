<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PegawaiLoginController extends Controller
{
    /**
     * Show pegawai login form
     */
    public function showLoginForm()
    {
        // Redirect if already logged in
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'pegawai') {
                return redirect()->route('pegawai.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return view('auth.pegawai-login');
    }

    /**
     * Handle pegawai login
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'kode_perusahaan' => 'required|string|min:6|max:20',
            'email' => 'required|email',
        ], [
            'kode_perusahaan.required' => 'Kode perusahaan harus diisi',
            'kode_perusahaan.min' => 'Kode perusahaan minimal 6 karakter',
            'kode_perusahaan.max' => 'Kode perusahaan maksimal 20 karakter',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
        ]);

        $kodePerusahaan = strtoupper($request->kode_perusahaan);
        $email = $request->email;

        // 1. Cek apakah kode perusahaan valid
        $perusahaan = Perusahaan::where('kode', $kodePerusahaan)->first();

        if (!$perusahaan) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak valid']);
        }

        // 2. Cek apakah pegawai dengan email tersebut ada
        $pegawai = Pegawai::withoutGlobalScopes()
            ->where('email', $email)
            ->first();

        if (!$pegawai) {
            return back()
                ->withInput($request->only('kode_perusahaan', 'email'))
                ->withErrors(['email' => 'Email pegawai tidak terdaftar']);
        }

        // 3. Cek atau buat user account untuk pegawai
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Auto-create user account jika belum ada
            $user = new User();
            $user->name = $pegawai->nama;
            $user->email = $pegawai->email;
            $user->password = Hash::make('pegawai123'); // Password dummy (tidak digunakan)
            $user->role = 'pegawai';
            $user->perusahaan_id = $perusahaan->id;
            $user->save();

            // Ensure user is linked to pegawai
            $user->pegawai_id = $pegawai->id;
            $user->save();
        }

        // 4. Pastikan user adalah pegawai
        if ($user->role !== 'pegawai') {
            return back()
                ->withInput($request->only('kode_perusahaan', 'email'))
                ->withErrors(['email' => 'Akun ini bukan akun pegawai. Silakan gunakan halaman login biasa.']);
        }

        // 5. Pastikan pegawai terhubung dengan user
        if ($user->pegawai_id !== $pegawai->id) {
            $user->pegawai_id = $pegawai->id;
            $user->save();
        }

        // 6. Login user
        Auth::login($user, true); // true = remember me

        // 7. Regenerate session untuk keamanan
        $request->session()->regenerate();

        // 8. Redirect ke dashboard pegawai
        return redirect()->intended(route('pegawai.dashboard'))
            ->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    /**
     * Logout pegawai
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pegawai.login')
            ->with('success', 'Anda telah logout');
    }
}

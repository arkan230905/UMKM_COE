<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeLoginRequest;
use App\Models\Pegawai;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    /**
     * Form login admin
     */
    public function showLogin()
    {
        if (session('admin_id')) {
            return redirect()->route('dashboard');
        }
        // Redirect to main login page
        return redirect()->route('login');
    }

    /**
     * Proses login admin - menggunakan email pegawai + kode perusahaan
     * Admin adalah pegawai dengan jabatan admin dari tabel pegawais
     */
    public function login(EmployeeLoginRequest $request)
    {
        try {
            $validated = $request->validated();

            // Cari perusahaan berdasarkan kode
            $perusahaan = Perusahaan::where('kode', $validated['kode_perusahaan'])->first();
            if (!$perusahaan) {
                Log::warning('Admin login failed - company not found', [
                    'kode_perusahaan' => $validated['kode_perusahaan']
                ]);
                return back()->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak ditemukan'])
                    ->withInput();
            }

            // Cari pegawai dengan email dan jabatan admin
            // Pegawai harus ada di tabel pegawais dengan jabatan yang mengandung kata 'admin'
            $pegawai = Pegawai::where('email', $validated['email'])
                ->where(function($q) {
                    $q->where('jabatan', 'like', '%admin%')
                      ->orWhere('jabatan', 'like', '%Admin%')
                      ->orWhere('jabatan', '=', 'admin')
                      ->orWhere('jabatan', '=', 'Admin');
                })
                ->first();

            if (!$pegawai) {
                Log::warning('Admin login attempt failed - email not found or not admin role', [
                    'email' => $validated['email'],
                    'kode_perusahaan' => $validated['kode_perusahaan'],
                    'available_pegawai' => Pegawai::where('email', $validated['email'])->pluck('jabatan')->toArray()
                ]);
                return back()->withErrors(['email' => 'Email tidak terdaftar sebagai admin di master data pegawai'])
                    ->withInput();
            }

            // Set session dengan data lengkap
            session([
                'admin_id' => $pegawai->id,
                'admin_nama' => $pegawai->nama,
                'admin_kode' => $pegawai->kode_pegawai,
                'admin_email' => $pegawai->email,
                'admin_jabatan' => $pegawai->jabatan,
                'perusahaan_id' => $perusahaan->id,
                'perusahaan_nama' => $perusahaan->nama,
                'perusahaan_kode' => $perusahaan->kode,
            ]);

            Log::info('Admin login successful', [
                'pegawai_id' => $pegawai->id,
                'nama' => $pegawai->nama,
                'email' => $pegawai->email,
                'jabatan' => $pegawai->jabatan,
                'perusahaan' => $perusahaan->nama
            ]);

            return redirect()->intended(route('dashboard'))->with('success', 'Login berhasil! Selamat datang ' . $pegawai->nama);

        } catch (\Exception $e) {
            Log::error('Admin login error', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan saat login. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Proses logout admin
     */
    public function logout(Request $request)
    {
        $adminId = session('admin_id');
        $adminNama = session('admin_nama');

        // Clear session
        session()->forget([
            'admin_id',
            'admin_nama',
            'admin_kode',
            'admin_email',
            'admin_jabatan',
            'perusahaan_id',
            'perusahaan_nama',
            'perusahaan_kode',
        ]);

        Log::info('Admin logout', [
            'admin_id' => $adminId,
            'nama' => $adminNama,
        ]);

        return redirect()->route('login')->with('status', 'Anda telah logout');
    }
}
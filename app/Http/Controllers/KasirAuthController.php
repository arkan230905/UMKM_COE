<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeLoginRequest;
use App\Models\Kasir;
use App\Models\Pegawai;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KasirAuthController extends Controller
{
    /**
     * Form login kasir (pegawai dengan jabatan kasir)
     */
    public function showLogin()
    {
        if (session('kasir_id')) {
            return redirect()->route('kasir.pos');
        }
        // Redirect to main login page
        return redirect()->route('login');
    }

    /**
     * Proses login kasir - menggunakan email pegawai + kode perusahaan
     * Kasir adalah pegawai dengan jabatan kasir dari tabel pegawais
     */
    public function login(EmployeeLoginRequest $request)
    {
        try {
            $validated = $request->validated();

            // Cari perusahaan berdasarkan kode
            $perusahaan = Perusahaan::where('kode', $validated['kode_perusahaan'])->first();
            if (!$perusahaan) {
                Log::warning('Kasir login failed - company not found', [
                    'kode_perusahaan' => $validated['kode_perusahaan']
                ]);
                return back()->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak ditemukan'])
                    ->withInput();
            }

            // Cari pegawai dengan email dan jabatan kasir
            // Pegawai harus ada di tabel pegawais dengan jabatan yang mengandung kata 'kasir'
            $pegawai = Pegawai::where('email', $validated['email'])
                ->where(function($q) {
                    $q->where('jabatan', 'like', '%kasir%')
                      ->orWhere('jabatan', 'like', '%Kasir%')
                      ->orWhere('jabatan', '=', 'kasir')
                      ->orWhere('jabatan', '=', 'Kasir');
                })
                ->first();

            if (!$pegawai) {
                Log::warning('Kasir login attempt failed - email not found or not kasir role', [
                    'email' => $validated['email'],
                    'kode_perusahaan' => $validated['kode_perusahaan'],
                    'available_pegawai' => Pegawai::where('email', $validated['email'])->pluck('jabatan')->toArray()
                ]);
                return back()->withErrors(['email' => 'Email tidak terdaftar sebagai kasir di master data pegawai'])
                    ->withInput();
            }

            // Set session dengan data lengkap
            session([
                'kasir_id' => $pegawai->id,
                'kasir_nama' => $pegawai->nama,
                'kasir_kode' => $pegawai->kode_pegawai,
                'kasir_email' => $pegawai->email,
                'kasir_jabatan' => $pegawai->jabatan,
                'perusahaan_id' => $perusahaan->id,
                'perusahaan_nama' => $perusahaan->nama,
                'perusahaan_kode' => $perusahaan->kode,
            ]);

            Log::info('Kasir login successful', [
                'pegawai_id' => $pegawai->id,
                'nama' => $pegawai->nama,
                'email' => $pegawai->email,
                'jabatan' => $pegawai->jabatan,
                'perusahaan' => $perusahaan->nama
            ]);

            return redirect()->route('kasir.pos')->with('success', 'Login berhasil! Selamat datang ' . $pegawai->nama);
            
        } catch (\Exception $e) {
            Log::error('Kasir login error', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat login. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Logout kasir
     */
    public function logout()
    {
        session()->forget(['kasir_id', 'kasir_nama', 'kasir_kode', 'kasir_email', 'kasir_jabatan', 'perusahaan_id', 'perusahaan_nama', 'perusahaan_kode']);
        return redirect()->route('kasir.login');
    }
}

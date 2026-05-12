<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeLoginRequest;
use App\Models\Pegawai;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GudangAuthController extends Controller
{
    /**
     * Form login pegawai gudang
     */
    public function showLogin()
    {
        if (session('gudang_id')) {
            return redirect()->route('pegawai-gudang.dashboard');
        }
        // Redirect to main login page
        return redirect()->route('login');
    }

    /**
     * Proses login pegawai gudang - menggunakan email + kode perusahaan
     * Pegawai gudang adalah pegawai dengan jabatan gudang dari tabel pegawais
     * Pegawai gudang juga menangani fungsi pembelian (menggantikan role pegawai pembelian)
     */
    public function login(EmployeeLoginRequest $request)
    {
        try {
            $validated = $request->validated();

            // Cari perusahaan berdasarkan kode
            $perusahaan = Perusahaan::where('kode', $validated['kode_perusahaan'])->first();
            if (!$perusahaan) {
                Log::warning('Gudang login failed - company not found', [
                    'kode_perusahaan' => $validated['kode_perusahaan']
                ]);
                return back()->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak ditemukan'])
                    ->withInput();
            }

            // Cari pegawai dengan email dan jabatan gudang
            // Pegawai harus ada di tabel pegawais dengan jabatan yang mengandung kata 'gudang'
            $pegawai = Pegawai::where('email', $validated['email'])
                ->where(function($q) {
                    $q->where('jabatan', 'like', '%gudang%')
                      ->orWhere('jabatan', 'like', '%Gudang%')
                      ->orWhere('jabatan', '=', 'Bagian Gudang')
                      ->orWhere('jabatan', '=', 'bagian gudang')
                      ->orWhere('jabatan', '=', 'gudang')
                      ->orWhere('jabatan', '=', 'Gudang')
                      ->orWhere('jabatan', '=', 'Pegawai Gudang')
                      ->orWhere('jabatan', '=', 'pegawai gudang');
                })
                ->first();

            if (!$pegawai) {
                Log::warning('Gudang login attempt failed - email not found or not gudang role', [
                    'email' => $validated['email'],
                    'kode_perusahaan' => $validated['kode_perusahaan'],
                    'available_pegawai' => Pegawai::where('email', $validated['email'])->pluck('jabatan')->toArray()
                ]);
                return back()->withErrors(['email' => 'Email tidak terdaftar sebagai pegawai gudang di master data pegawai'])
                    ->withInput();
            }

            // Set session dengan data lengkap
            session([
                'gudang_id' => $pegawai->id,
                'gudang_nama' => $pegawai->nama,
                'gudang_kode' => $pegawai->kode_pegawai,
                'gudang_email' => $pegawai->email,
                'gudang_jabatan' => $pegawai->jabatan,
                'perusahaan_id' => $perusahaan->id,
                'perusahaan_nama' => $perusahaan->nama,
                'perusahaan_kode' => $perusahaan->kode,
            ]);

            Log::info('Gudang login successful', [
                'pegawai_id' => $pegawai->id,
                'nama' => $pegawai->nama,
                'email' => $pegawai->email,
                'jabatan' => $pegawai->jabatan,
                'perusahaan' => $perusahaan->nama
            ]);

            // Redirect ke dashboard pegawai gudang (menangani gudang dan pembelian)
            return redirect()->route('pegawai-gudang.dashboard')->with('success', 'Login berhasil! Selamat datang ' . $pegawai->nama);
            
        } catch (\Exception $e) {
            Log::error('Gudang login error', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat login. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Logout pegawai gudang
     */
    public function logout()
    {
        session()->forget(['gudang_id', 'gudang_nama', 'gudang_kode', 'gudang_email', 'gudang_jabatan', 'perusahaan_id', 'perusahaan_nama', 'perusahaan_kode']);
        return redirect()->route('gudang.login');
    }
}

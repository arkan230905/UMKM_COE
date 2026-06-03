<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePegawaiTenant
{
    /**
     * Handle an incoming request.
     * 
     * Middleware ini memastikan pegawai dan pegawai_pembelian punya relasi yang valid dengan:
     * 1. Pegawai record (via pegawai_id)
     * 2. Perusahaan (via perusahaan_id)
     * 
     * Ini mencegah silent redirect dan memberikan error message yang jelas
     * untuk debugging multi-tenant issues.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Hanya validate untuk pegawai dan pegawai_pembelian
        if (!in_array($user->role ?? null, ['pegawai', 'pegawai_pembelian'])) {
            return $next($request);
        }

        // ✅ Validasi 1: User punya pegawai_id
        if (!$user->pegawai_id) {
            \Log::error('VALIDATION FAILED: User tidak punya pegawai_id', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);
            
            abort(500, '❌ ERROR: User tidak terhubung dengan data pegawai (pegawai_id kosong). 
                       Hubungi administrator untuk menyelesaikan setup akun.');
        }

        // ✅ Validasi 2: Pegawai record exist
        $pegawai = $user->pegawai;
        if (!$pegawai) {
            \Log::error('VALIDATION FAILED: Pegawai record tidak ditemukan', [
                'user_id' => $user->id,
                'pegawai_id' => $user->pegawai_id,
                'email' => $user->email,
                'role' => $user->role,
            ]);
            
            abort(500, '❌ ERROR: Data pegawai tidak ditemukan di database. 
                       Pegawai_ID: ' . $user->pegawai_id . '. 
                       Hubungi administrator untuk memeriksa integritas data.');
        }

        // ✅ Validasi 3: User punya perusahaan_id
        if (!$user->perusahaan_id) {
            \Log::error('VALIDATION FAILED: User tidak punya perusahaan_id', [
                'user_id' => $user->id,
                'pegawai_id' => $user->pegawai_id,
                'email' => $user->email,
                'role' => $user->role,
            ]);
            
            abort(500, '❌ ERROR: User tidak terhubung dengan perusahaan (perusahaan_id kosong). 
                       Hubungi administrator untuk setup perusahaan.');
        }

        // ✅ Validasi 4: Perusahaan record exist (optional tapi recommended)
        if (method_exists($user, 'perusahaan') && $user->perusahaan === null) {
            \Log::warning('VALIDATION WARNING: Perusahaan record tidak ditemukan', [
                'user_id' => $user->id,
                'perusahaan_id' => $user->perusahaan_id,
                'email' => $user->email,
            ]);
            // Don't abort, hanya warning
        }

        // ✅ Validasi 5: Perusahaan_id di session
        // Session diisi oleh LoginController, tapi jika tidak ada, isi dari user
        if (!session('perusahaan_id')) {
            session(['perusahaan_id' => $user->perusahaan_id]);
            
            \Log::info('Session perusahaan_id di-set dari user record', [
                'user_id' => $user->id,
                'perusahaan_id' => $user->perusahaan_id,
            ]);
        }

        return $next($request);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// FIXED: Migration ini insert data COA. Di fresh install, user belum ada.
// Sekarang hanya insert jika ada user yang bisa dipakai.
return new class extends Migration
{
    public function up(): void
    {
        // Ambil semua user yang ada (skip jika belum ada user)
        $users = DB::table('users')->get();
        if ($users->isEmpty()) {
            return; // Fresh install tanpa user, skip
        }

        foreach ($users as $user) {
            $companyId = $user->perusahaan_id ?? null;
            if (!$companyId) continue;

            $this->createCoaIfNotExists('56',  'Harga Pokok Penjualan', $user->id, $companyId);
            $this->createCoaIfNotExists('552', 'HPP',                   $user->id, $companyId);
        }
    }

    private function createCoaIfNotExists(string $kode, string $nama, int $userId, int $companyId): void
    {
        $exists = DB::table('coas')
            ->where('kode_akun', $kode)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            DB::table('coas')->insert([
                'kode_akun'    => $kode,
                'nama_akun'    => $nama,
                'tipe_akun'    => 'Biaya',
                'kategori_akun'=> 'Biaya',
                'saldo_awal'   => 0,
                'saldo_normal' => 'Debit',
                'user_id'      => $userId,
                'company_id'   => $companyId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('coas')->whereIn('kode_akun', ['56', '552'])
            ->whereIn('nama_akun', ['Harga Pokok Penjualan', 'HPP'])
            ->delete();
    }
};

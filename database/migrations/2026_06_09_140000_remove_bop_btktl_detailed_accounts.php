<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus akun COA 540-546 (detail BOP BTKTL) dari semua user
     * Hanya menyisakan akun '54' (BOP BTKTL parent)
     */
    public function up(): void
    {
        // Daftar kode akun yang akan dihapus
        $accountsToDelete = ['540', '541', '542', '543', '544', '545', '546'];
        
        // Hapus dari tabel coas
        DB::table('coas')
            ->whereIn('kode_akun', $accountsToDelete)
            ->delete();
        
        echo "✓ Berhasil menghapus " . count($accountsToDelete) . " akun BOP BTKTL detail (540-546)\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore accounts jika rollback
        $now = now();
        $accountsToRestore = [
            ['kode_akun' => '540', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Pemasaran', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '541', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Kemasan', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '542', 'nama_akun' => 'BOP BTKTL - Biaya Satpam Pabrik', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '543', 'nama_akun' => 'BOP BTKTL - Biaya Cleaning Service', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '544', 'nama_akun' => 'BOP BTKTL - Biaya Mandor', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '545', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Keuangan', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '546', 'nama_akun' => 'BOP BTKTL - BTKTL Lainnya', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
        ];
        
        // Get all users untuk restore per user
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            foreach ($accountsToRestore as $account) {
                DB::table('coas')->insert([
                    'user_id' => $user->id,
                    'kode_akun' => $account['kode_akun'],
                    'nama_akun' => $account['nama_akun'],
                    'tipe_akun' => $account['tipe_akun'],
                    'kategori_akun' => $account['tipe_akun'],
                    'saldo_normal' => $account['saldo_normal'],
                    'saldo_awal' => 0,
                    'tanggal_saldo_awal' => $now,
                    'posted_saldo_awal' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ambil semua user yang bukan admin dan belum punya akun COA dasar
        $users = DB::table('users')
            ->where('role', '!=', 'admin')
            ->get();

        $defaultAccounts = [
            // ASSET - Kas
            ['kode' => '101', 'nama' => 'Kas', 'tipe' => 'Asset', 'kategori' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode' => '111', 'nama' => 'Kas Bank', 'tipe' => 'Asset', 'kategori' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode' => '112', 'nama' => 'Kas', 'tipe' => 'Asset', 'kategori' => 'Asset', 'saldo_normal' => 'debit'],
            
            // ASSET - Piutang
            ['kode' => '113', 'nama' => 'Piutang Usaha', 'tipe' => 'Asset', 'kategori' => 'Asset', 'saldo_normal' => 'debit'],
            
            // ASSET - Persediaan
            ['kode' => '121', 'nama' => 'Persediaan Barang Jadi', 'tipe' => 'Asset', 'kategori' => 'Asset', 'saldo_normal' => 'debit'],
            
            // LIABILITY - PPN
            ['kode' => '211', 'nama' => 'PPN Keluaran', 'tipe' => 'Liability', 'kategori' => 'Liability', 'saldo_normal' => 'kredit'],
            
            // REVENUE - Penjualan
            ['kode' => '401', 'nama' => 'Penjualan', 'tipe' => 'Revenue', 'kategori' => 'Revenue', 'saldo_normal' => 'kredit'],
            ['kode' => '411', 'nama' => 'Pendapatan Lain-lain', 'tipe' => 'Revenue', 'kategori' => 'Revenue', 'saldo_normal' => 'kredit'],
            
            // EXPENSE - HPP
            ['kode' => '501', 'nama' => 'Harga Pokok Penjualan', 'tipe' => 'Expense', 'kategori' => 'Expense', 'saldo_normal' => 'debit'],
            
            // EXPENSE - Diskon
            ['kode' => '511', 'nama' => 'Diskon Penjualan', 'tipe' => 'Expense', 'kategori' => 'Expense', 'saldo_normal' => 'debit'],
        ];

        foreach ($users as $user) {
            foreach ($defaultAccounts as $account) {
                // Cek apakah akun sudah ada untuk user ini
                $exists = DB::table('coas')
                    ->where('user_id', $user->id)
                    ->where('kode_akun', $account['kode'])
                    ->exists();

                if (!$exists) {
                    try {
                        DB::table('coas')->insert([
                            'user_id' => $user->id,
                            'kode_akun' => $account['kode'],
                            'nama_akun' => $account['nama'],
                            'tipe_akun' => $account['tipe'],
                            'kategori_akun' => $account['kategori'],
                            'is_akun_header' => false,
                            'saldo_normal' => $account['saldo_normal'],
                            'saldo_awal' => 0,
                            'posted_saldo_awal' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        Log::info("Created default COA {$account['kode']} ({$account['nama']}) for existing user {$user->id}");
                    } catch (\Exception $e) {
                        Log::warning("Failed to create COA {$account['kode']} for existing user {$user->id}: " . $e->getMessage());
                    }
                }
            }
        }

        Log::info("Migration completed: Default COA accounts created for existing users");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus akun COA yang dibuat oleh migration ini (hanya untuk user non-admin)
        DB::table('coas')
            ->whereIn('kode_akun', ['101', '111', '112', '113', '121', '211', '401', '411', '501', '511'])
            ->where('user_id', '!=', null)
            ->delete();
            
        Log::info("Migration rolled back: Default COA accounts removed");
    }
};

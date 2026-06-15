<?php

namespace App\Observers;

use App\Models\User;
use App\Services\InitMasterDataService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            // Hanya initialize untuk user biasa (bukan admin)
            if ($user->role !== 'admin') {
                Log::info("New user created: {$user->id} ({$user->email}), initializing master data");
                
                // Buat kategori BTKL dan BTKTL untuk user baru
                $this->createKategoriPegawai($user->id);
                
                // Buat akun COA default untuk user baru
                $this->createDefaultCoaAccounts($user->id);
                
                Log::info("Master data initialized for user {$user->id}");
            } else {
                Log::info("Admin user created: {$user->id} ({$user->email}), skipping master data initialization");
            }
        } catch (\Exception $e) {
            Log::error("Failed to initialize master data for user {$user->id}: " . $e->getMessage());
            // Don't throw exception to prevent user creation failure
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Optional: Handle role changes
        if ($user->isDirty('role') && $user->role !== 'admin') {
            try {
                Log::info("User role changed to non-admin: {$user->id}, checking master data");
                
                $initService = new InitMasterDataService();
                $initService->initializeForUser($user->id);
            } catch (\Exception $e) {
                Log::error("Failed to initialize master data after role change for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Master data akan terhapus otomatis karena foreign key cascade
        Log::info("User deleted: {$user->id}, master data will be cleaned up automatically");
    }
    
    /**
     * Create default kategori pegawai for new user
     */
    private function createKategoriPegawai($userId)
    {
        $kategoriData = [
            [
                'nama' => 'BTKL',
                'deskripsi' => 'Tenaga Kerja Langsung'
            ],
            [
                'nama' => 'BTKTL',
                'deskripsi' => 'Bukan Tenaga Kerja Langsung'
            ]
        ];
        
        foreach ($kategoriData as $kategori) {
            // Cek apakah kategori sudah ada untuk user ini
            $exists = DB::table('kategori_pegawai')
                ->where('user_id', $userId)
                ->where('nama', $kategori['nama'])
                ->exists();
                
            if (!$exists) {
                // Insert kategori pegawai
                DB::table('kategori_pegawai')->insert([
                    'user_id' => $userId,
                    'nama' => $kategori['nama'],
                    'deskripsi' => $kategori['deskripsi'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Create default COA accounts for new user (untuk sales journal)
     */
    private function createDefaultCoaAccounts($userId)
    {
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

        foreach ($defaultAccounts as $account) {
            // Cek apakah akun sudah ada untuk user ini
            $exists = DB::table('coas')
                ->where('user_id', $userId)
                ->where('kode_akun', $account['kode'])
                ->exists();

            if (!$exists) {
                try {
                    DB::table('coas')->insert([
                        'user_id' => $userId,
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
                    
                    Log::info("Created default COA {$account['kode']} ({$account['nama']}) for user {$userId}");
                } catch (\Exception $e) {
                    Log::warning("Failed to create COA {$account['kode']} for user {$userId}: " . $e->getMessage());
                }
            }
        }
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah COA yang mungkin hilang untuk jurnal umum
        $missingCoas = [
            [
                'kode_akun' => '1104',
                'nama_akun' => 'Persediaan Bahan Baku',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Persediaan',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '2103',
                'nama_akun' => 'Hutang Gaji (BTKL)',
                'tipe_akun' => 'Liability',
                'kategori_akun' => 'Hutang',
                'saldo_normal' => 'kredit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '2104',
                'nama_akun' => 'Hutang BOP',
                'tipe_akun' => 'Liability',
                'kategori_akun' => 'Hutang',
                'saldo_normal' => 'kredit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '1105',
                'nama_akun' => 'Persediaan Barang Dalam Proses (WIP)',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Persediaan',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '1106',
                'nama_akun' => 'Persediaan Barang Dalam Proses',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Persediaan',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '1107',
                'nama_akun' => 'Persediaan Barang Jadi',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Persediaan',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '5101',
                'nama_akun' => 'Gaji & Upah',
                'tipe_akun' => 'Expense',
                'kategori_akun' => 'Biaya',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '5102',
                'nama_akun' => 'Biaya Overhead Pabrik (BOP)',
                'tipe_akun' => 'Expense',
                'kategori_akun' => 'Biaya',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
        ];

        foreach ($missingCoas as $coa) {
            // Cek apakah COA sudah ada
            $exists = DB::table('coas')->where('kode_akun', $coa['kode_akun'])->exists();
            
            if (!$exists) {
                DB::table('coas')->insert(array_merge($coa, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                
                echo "Added COA: {$coa['kode_akun']} - {$coa['nama_akun']}\n";
            }
        }

        // Sync ke accounts table juga
        $this->syncToAccountsTable();
    }

    /**
     * Sync COA ke accounts table
     */
    private function syncToAccountsTable(): void
    {
        $coas = DB::table('coas')->whereNotIn('kode_akun', function($query) {
            $query->select('code')->from('accounts');
        })->get();

        foreach ($coas as $coa) {
            DB::table('accounts')->insertOrIgnore([
                'code' => $coa->kode_akun,
                'name' => $coa->nama_akun,
                'type' => strtolower($coa->tipe_akun),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus COA yang ditambahkan
        $codesToRemove = ['1104', '2103', '2104', '1105', '1106', '1107', '5101', '5102'];
        
        DB::table('coas')->whereIn('kode_akun', $codesToRemove)->delete();
        DB::table('accounts')->whereIn('code', $codesToRemove)->delete();
    }
};
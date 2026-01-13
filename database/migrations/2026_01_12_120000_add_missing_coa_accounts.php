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
        // Tambah akun kas/bank yang mungkin hilang
        $missingAccounts = [
            [
                'kode_akun' => '1101',
                'nama_akun' => 'Kas',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Kas & Bank',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '1102',
                'nama_akun' => 'Bank BCA',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Kas & Bank',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '1103',
                'nama_akun' => 'Bank BNI',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Kas & Bank',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '101',
                'nama_akun' => 'Kas Kecil',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Kas & Bank',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '102',
                'nama_akun' => 'Bank Kecil',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Kas & Bank',
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '2101',
                'nama_akun' => 'Hutang Usaha',
                'tipe_akun' => 'Liability',
                'kategori_akun' => 'Hutang',
                'saldo_normal' => 'kredit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
            ],
        ];

        foreach ($missingAccounts as $account) {
            // Cek apakah akun sudah ada
            $exists = DB::table('coas')->where('kode_akun', $account['kode_akun'])->exists();
            
            if (!$exists) {
                DB::table('coas')->insert(array_merge($account, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $codes = ['1101', '1102', '1103', '101', '102', '2101'];
        DB::table('coas')->whereIn('kode_akun', $codes)->delete();
    }
};

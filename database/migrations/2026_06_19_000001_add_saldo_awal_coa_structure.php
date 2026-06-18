<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migration ini:
     * 1. Memastikan struktur header COA lengkap
     * 2. Menambahkan parent relationships (kode_induk)
     * 3. Mengatur hierarki akun dengan benar
     */
    public function up(): void
    {
        $now = Carbon::now();

        // Daftar akun header yang diperlukan
        $headerAccounts = [
            // Level 1 - Main Groups
            ['kode_akun' => '1', 'nama_akun' => 'Aset', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1],
            ['kode_akun' => '2', 'nama_akun' => 'Kewajiban', 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'is_header' => 1],
            ['kode_akun' => '3', 'nama_akun' => 'Modal', 'tipe_akun' => 'Modal', 'saldo_normal' => 'kredit', 'is_header' => 1],
            ['kode_akun' => '4', 'nama_akun' => 'Pendapatan', 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'is_header' => 1],
            ['kode_akun' => '5', 'nama_akun' => 'Biaya', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit', 'is_header' => 1],

            // Level 2 - Aset Sub-groups
            ['kode_akun' => '11', 'nama_akun' => 'Aset Lancar', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '1'],
            ['kode_akun' => '12', 'nama_akun' => 'Aset Tetap', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '1'],

            // Level 2 - Kewajiban Sub-groups
            ['kode_akun' => '21', 'nama_akun' => 'Kewajiban Jangka Pendek', 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'is_header' => 1, 'parent' => '2'],

            // Level 2 - Modal Sub-groups
            ['kode_akun' => '31', 'nama_akun' => 'Modal', 'tipe_akun' => 'Modal', 'saldo_normal' => 'kredit', 'is_header' => 1, 'parent' => '3'],

            // Level 2 - Pendapatan Sub-groups
            ['kode_akun' => '41', 'nama_akun' => 'Penjualan', 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'is_header' => 1, 'parent' => '4'],
            ['kode_akun' => '42', 'nama_akun' => 'Retur Penjualan', 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'is_header' => 1, 'parent' => '4'],
            ['kode_akun' => '43', 'nama_akun' => 'Pendapatan Lain-lain', 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'is_header' => 1, 'parent' => '4'],

            // Level 2 - Biaya Sub-groups
            ['kode_akun' => '51', 'nama_akun' => 'BBB - Biaya Bahan Baku', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '5'],
            ['kode_akun' => '52', 'nama_akun' => 'BTKL - Biaya Tenaga Kerja Langsung', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '5'],
            ['kode_akun' => '53', 'nama_akun' => 'BOP - Biaya Overhead Pabrik', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '5'],
            ['kode_akun' => '54', 'nama_akun' => 'BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '5'],
            ['kode_akun' => '55', 'nama_akun' => 'BOP - Lainnya', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '5'],
            ['kode_akun' => '56', 'nama_akun' => 'HPP - Harga Pokok Penjualan', 'tipe_akun' => 'Beban', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '5'],

            // Level 3 - Kas & Bank
            ['kode_akun' => '111', 'nama_akun' => 'Kas Bank', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '11'],
            ['kode_akun' => '112', 'nama_akun' => 'Kas', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '11'],
            ['kode_akun' => '113', 'nama_akun' => 'Kas Kecil', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '11'],

            // Level 4 - Bank Detail
            ['kode_akun' => '1111', 'nama_akun' => 'Bank BRI', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '111'],
            ['kode_akun' => '1112', 'nama_akun' => 'Bank BCA', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '111'],
            ['kode_akun' => '1123', 'nama_akun' => 'Bank Mandiri', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '111'],
            ['kode_akun' => '1124', 'nama_akun' => 'Seabank', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '111'],

            // Level 3 - Persediaan
            ['kode_akun' => '114', 'nama_akun' => 'Pers. Bahan Baku', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '11'],
            ['kode_akun' => '115', 'nama_akun' => 'Pers. Bahan Pendukung', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '11'],
            ['kode_akun' => '116', 'nama_akun' => 'Pers. Barang Jadi', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '11'],
            ['kode_akun' => '117', 'nama_akun' => 'Pers. Barang Dalam Proses', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 1, 'parent' => '11'],
            ['kode_akun' => '118', 'nama_akun' => 'Piutang', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '11'],

            // Level 3 - Aset Tetap
            ['kode_akun' => '119', 'nama_akun' => 'Peralatan', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '120', 'nama_akun' => 'Akumulasi Penyusutan Peralatan', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '121', 'nama_akun' => 'Gedung', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '122', 'nama_akun' => 'Akumulasi Penyusutan Gedung', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '123', 'nama_akun' => 'Kendaraan', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '124', 'nama_akun' => 'Akumulasi Penyusutan Kendaraan', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '125', 'nama_akun' => 'Mesin', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '126', 'nama_akun' => 'Akumulasi Penyusutan Mesin', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],
            ['kode_akun' => '127', 'nama_akun' => 'PPN Masukkan', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit', 'is_header' => 0, 'parent' => '12'],

            // Level 3 - Kewajiban
            ['kode_akun' => '210', 'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'is_header' => 0, 'parent' => '21'],
            ['kode_akun' => '211', 'nama_akun' => 'Hutang Gaji', 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'is_header' => 0, 'parent' => '21'],
            ['kode_akun' => '212', 'nama_akun' => 'PPN Keluaran', 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'is_header' => 0, 'parent' => '21'],

            // Level 3 - Modal
            ['kode_akun' => '310', 'nama_akun' => 'Modal Usaha', 'tipe_akun' => 'Modal', 'saldo_normal' => 'kredit', 'is_header' => 0, 'parent' => '31'],
            ['kode_akun' => '311', 'nama_akun' => 'Prive', 'tipe_akun' => 'Modal', 'saldo_normal' => 'kredit', 'is_header' => 0, 'parent' => '31'],
        ];

        // Insert or update header accounts
        foreach ($headerAccounts as $account) {
            $exists = DB::table('coas')
                ->where('kode_akun', $account['kode_akun'])
                ->exists();

            if (!$exists) {
                DB::table('coas')->insert([
                    'user_id' => null,
                    'company_id' => null,
                    'kode_akun' => $account['kode_akun'],
                    'nama_akun' => $account['nama_akun'],
                    'tipe_akun' => $account['tipe_akun'],
                    'kategori_akun' => '-',
                    'is_akun_header' => $account['is_header'],
                    'kode_induk' => $account['parent'] ?? null,
                    'saldo_normal' => $account['saldo_normal'],
                    'saldo_awal' => 0,
                    'tanggal_saldo_awal' => null,
                    'posted_saldo_awal' => 0,
                    'keterangan' => null,
                    'nomor_rekening' => null,
                    'atas_nama' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                // Update if already exists to ensure hierarchy is correct
                DB::table('coas')
                    ->where('kode_akun', $account['kode_akun'])
                    ->update([
                        'is_akun_header' => $account['is_header'],
                        'kode_induk' => $account['parent'] ?? null,
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu delete karena ini hanya update struktur existing
        // Data saldo awal tetap aman
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JasukeCoaSeeder extends Seeder
{
    /**
     * SEEDER UNTUK TABEL ACCOUNTS
     * 
     * Standarisasi: Menggunakan tabel 'accounts' (bukan 'coas')
     * Kolom yang digunakan: kode_akun, nama_akun, tipe_akun, saldo_normal, saldo_awal
     */
    public function run(): void
    {
        // Daftar COA sesuai permintaan Owner (Sistem Manufaktur Jasuke)
        $accounts = [
            // Nama Akun, Kode Akun, Tipe, Posisi (Saldo Normal)
            ['Aset', '11', 'Aset', 'debit'],
            ['Kas Bank', '111', 'Aset', 'debit'],
            ['Kas', '112', 'Aset', 'debit'],
            ['Kas Kecil', '113', 'Aset', 'debit'],
            ['Pers. Bahan Baku', '114', 'Aset', 'debit'],
            ['Pers. Bahan Baku Jagung', '1141', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung', '115', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Susu', '1151', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Keju', '1152', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Kemasan (Cup)', '1153', 'Aset', 'debit'],
            ['Pers. Barang Jadi', '116', 'Aset', 'debit'],
            ['Pers. Barang Jadi Jasuke', '1161', 'Aset', 'debit'],
            ['Pers. Barang dalam Proses', '117', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BBB', '1171', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BTKL', '1172', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BOP', '1173', 'Aset', 'debit'],
            ['Piutang', '118', 'Aset', 'debit'],
            ['Peralatan', '119', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Peralatan', '120', 'Aset', 'debit'],
            ['Mesin', '125', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Mesin', '126', 'Aset', 'debit'],
            ['PPN Masukkan', '127', 'Aset', 'debit'],
            ['Hutang', '21', 'Kewajiban', 'kredit'],
            ['Hutang Usaha', '210', 'Kewajiban', 'kredit'],
            ['Hutang Gaji', '211', 'Kewajiban', 'kredit'],
            ['PPN Keluaran', '212', 'Kewajiban', 'kredit'],
            ['Modal', '31', 'Modal', 'kredit'],
            ['Modal Usaha', '310', 'Modal', 'kredit'],
            ['Prive', '311', 'Modal', 'kredit'],
            ['Penjualan', '41', 'Pendapatan', 'kredit'],
            ['Penjualan - Jasuke', '410', 'Pendapatan', 'kredit'],
            ['Retur Penjualan', '42', 'Pendapatan', 'kredit'],
            ['BBB - Biaya Bahan Baku', '51', 'Biaya', 'debit'],
            ['BBB - Jagung', '510', 'Biaya', 'debit'],
            ['Beban Tunjangan', '513', 'Biaya', 'debit'],
            ['Beban Asuransi', '514', 'Biaya', 'debit'],
            ['Beban Bonus', '515', 'Biaya', 'debit'],
            ['Potongan Gaji', '516', 'Biaya', 'debit'],
            ['BTKL', '52', 'Biaya', 'debit'],
            ['BTKL - Produksi Jasuke', '520', 'Biaya', 'debit'],
            ['BOP', '53', 'Biaya', 'debit'],
            ['BOP - Susu', '530', 'Biaya', 'debit'],
            ['BOP - Keju', '531', 'Biaya', 'debit'],
            ['BOP - Kemasan', '532', 'Biaya', 'debit'],
            ['Beban Sewa', '54', 'Biaya', 'debit'],
            ['BOP Lain', '55', 'Biaya', 'debit'],
            ['BOP - Listrik', '550', 'Biaya', 'debit'],
            ['BOP - Air', '551', 'Biaya', 'debit'],
            ['BOP - Gas', '552', 'Biaya', 'debit'],
            ['BOP - Penyusutan Peralatan', '553', 'Biaya', 'debit'],
            ['Harga Pokok Penjualan', '554', 'Biaya', 'debit'],
        ];

        foreach ($accounts as $account) {
            DB::table('accounts')->updateOrInsert(
                ['kode_akun' => $account[1]], // Cek berdasarkan kode unik
                [
                    'company_id' => null, // Nullable - bisa diisi manual nanti
                    'nama_akun' => $account[0],
                    'tipe_akun' => $account[2],
                    'saldo_normal' => strtolower($account[3]),
                    'saldo_awal' => 0, // Sesuai permintaan Owner: Murni Manual, tidak otomatis
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        echo "✅ JasukeCoaSeeder: Seluruh akun manufaktur berhasil disinkronkan ke tabel 'accounts'.\n";
    }
}

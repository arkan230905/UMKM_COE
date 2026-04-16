<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

/**
 * COA Template Seeder
 * Seeder ini untuk membuat template COA yang bersih untuk user baru yang registrasi
 * Template ini cocok untuk berbagai jenis bisnis
 */
class CoaTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCoaTemplate();
    }

    /**
     * Seed COA template untuk user baru
     * Data ini akan di-copy saat user registrasi dengan company_id yang sesuai
     */
    public function seedCoaTemplate(): void
    {
        $coaTemplate = [
            // ===================== ASSET (11) =====================
            ['kode_akun' => '11',   'nama_akun' => 'ASSET', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '111',  'nama_akun' => 'Kas Bank', 'tipe_akun' => 'Asset', 'saldo_awal' => 100000000],
            ['kode_akun' => '112',  'nama_akun' => 'Kas', 'tipe_akun' => 'Asset', 'saldo_awal' => 75000000],
            ['kode_akun' => '113',  'nama_akun' => 'Kas Kecil', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            
            // Persediaan Bahan Baku
            ['kode_akun' => '114',  'nama_akun' => 'Persediaan Bahan Baku', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1141', 'nama_akun' => 'Persediaan Bahan Baku Ayam Potong', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1142', 'nama_akun' => 'Persediaan Bahan Baku Ayam Kampung', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1143', 'nama_akun' => 'Persediaan Bahan Baku Bebek', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1144', 'nama_akun' => 'Persediaan Bahan Baku Ayam Lainnya', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            
            // Persediaan Bahan Pendukung
            ['kode_akun' => '115',  'nama_akun' => 'Persediaan Bahan Pendukung', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1150', 'nama_akun' => 'Persediaan Air', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1151', 'nama_akun' => 'Persediaan Minyak Goreng', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1152', 'nama_akun' => 'Persediaan Tepung Terigu', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1153', 'nama_akun' => 'Persediaan Tepung Maizena', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1154', 'nama_akun' => 'Persediaan Lada', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1155', 'nama_akun' => 'Persediaan Bubuk Kaldu', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1156', 'nama_akun' => 'Persediaan Bubuk Bawang Putih', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1157', 'nama_akun' => 'Persediaan Kemasan', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            
            // Persediaan Barang Jadi
            ['kode_akun' => '116',  'nama_akun' => 'Persediaan Barang Jadi', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1161', 'nama_akun' => 'Persediaan Ayam Crispy Macdi', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '1162', 'nama_akun' => 'Persediaan Ayam Goreng Bundo', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            
            ['kode_akun' => '117',  'nama_akun' => 'Barang Dalam Proses', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '118',  'nama_akun' => 'Piutang', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            
            // Aset Tetap
            ['kode_akun' => '119',  'nama_akun' => 'Peralatan', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Peralatan', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '121',  'nama_akun' => 'Gedung', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '122',  'nama_akun' => 'Akumulasi Penyusutan Gedung', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '123',  'nama_akun' => 'Kendaraan', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '124',  'nama_akun' => 'Akumulasi Penyusutan Kendaraan', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '125',  'nama_akun' => 'Mesin', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '126',  'nama_akun' => 'Akumulasi Penyusutan Mesin', 'tipe_akun' => 'Asset', 'saldo_awal' => null],
            ['kode_akun' => '127',  'nama_akun' => 'PPN Masukan', 'tipe_akun' => 'Asset', 'saldo_awal' => null],

            // ===================== KEWAJIBAN (21) =====================
            ['kode_akun' => '21',   'nama_akun' => 'KEWAJIBAN', 'tipe_akun' => 'Liability', 'saldo_awal' => null],
            ['kode_akun' => '210',  'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Liability', 'saldo_awal' => null],
            ['kode_akun' => '211',  'nama_akun' => 'Hutang Gaji', 'tipe_akun' => 'Liability', 'saldo_awal' => null],
            ['kode_akun' => '212',  'nama_akun' => 'PPN Keluaran', 'tipe_akun' => 'Liability', 'saldo_awal' => null],

            // ===================== MODAL (31) =====================
            ['kode_akun' => '31',   'nama_akun' => 'MODAL', 'tipe_akun' => 'Equity', 'saldo_awal' => null],
            ['kode_akun' => '310',  'nama_akun' => 'Modal Usaha', 'tipe_akun' => 'Equity', 'saldo_awal' => null],
            ['kode_akun' => '311',  'nama_akun' => 'Prive', 'tipe_akun' => 'Equity', 'saldo_awal' => null],

            // ===================== PENDAPATAN (41) =====================
            ['kode_akun' => '41',   'nama_akun' => 'PENDAPATAN', 'tipe_akun' => 'Revenue', 'saldo_awal' => null],
            ['kode_akun' => '410',  'nama_akun' => 'Penjualan Ayam Crispy Macdi', 'tipe_akun' => 'Revenue', 'saldo_awal' => null],
            ['kode_akun' => '411',  'nama_akun' => 'Penjualan Ayam Goreng Bundo', 'tipe_akun' => 'Revenue', 'saldo_awal' => null],
            ['kode_akun' => '42',   'nama_akun' => 'Retur Penjualan', 'tipe_akun' => 'Revenue', 'saldo_awal' => null],
            ['kode_akun' => '43',   'nama_akun' => 'Pendapatan Ongkir', 'tipe_akun' => 'Revenue', 'saldo_awal' => null],

            // ===================== BIAYA (51-55) =====================
            // BBB - Biaya Bahan Baku
            ['kode_akun' => '51',   'nama_akun' => 'BIAYA BAHAN BAKU (BBB)', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '510',  'nama_akun' => 'BBB Ayam Potong', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '511',  'nama_akun' => 'BBB Ayam Kampung', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '512',  'nama_akun' => 'BBB Bebek', 'tipe_akun' => 'Expense', 'saldo_awal' => null],

            // BTKL - Biaya Tenaga Kerja Langsung
            ['kode_akun' => '52',   'nama_akun' => 'BIAYA TENAGA KERJA LANGSUNG (BTKL)', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '520',  'nama_akun' => 'BTKL Perbumbuan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '521',  'nama_akun' => 'BTKL Penggorengan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '522',  'nama_akun' => 'BTKL Pengemasan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],

            // BOP - Biaya Overhead Pabrik
            ['kode_akun' => '53',   'nama_akun' => 'BIAYA OVERHEAD PABRIK (BOP)', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '530',  'nama_akun' => 'BOP Bahan Tidak Langsung', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '531',  'nama_akun' => 'BOP Air', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '532',  'nama_akun' => 'BOP Minyak Goreng', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '533',  'nama_akun' => 'BOP Tepung Terigu', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '534',  'nama_akun' => 'BOP Tepung Maizena', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '535',  'nama_akun' => 'BOP Lada', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '536',  'nama_akun' => 'BOP Bubuk Kaldu', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '537',  'nama_akun' => 'BOP Bubuk Bawang Putih', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '538',  'nama_akun' => 'BOP Kemasan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],

            // BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung
            ['kode_akun' => '54',   'nama_akun' => 'BOP TENAGA KERJA TIDAK LANGSUNG', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '540',  'nama_akun' => 'BTKTL Pegawai Pemasaran', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '541',  'nama_akun' => 'BTKTL Pegawai Kemasan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '542',  'nama_akun' => 'BTKTL Satpam Pabrik', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '543',  'nama_akun' => 'BTKTL Cleaning Service', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '544',  'nama_akun' => 'BTKTL Mandor', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '545',  'nama_akun' => 'BTKTL Pegawai Keuangan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '546',  'nama_akun' => 'BTKTL Lainnya', 'tipe_akun' => 'Expense', 'saldo_awal' => null],

            // BOP TL - BOP Tidak Langsung Lainnya
            ['kode_akun' => '55',   'nama_akun' => 'BOP TIDAK LANGSUNG LAINNYA', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '550',  'nama_akun' => 'BOP Listrik', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '551',  'nama_akun' => 'BOP Sewa Tempat', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '552',  'nama_akun' => 'BOP Penyusutan Gedung', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '553',  'nama_akun' => 'BOP Penyusutan Peralatan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '554',  'nama_akun' => 'BOP Penyusutan Kendaraan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '555',  'nama_akun' => 'BOP Penyusutan Mesin', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '556',  'nama_akun' => 'BOP Air Tambahan', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '557',  'nama_akun' => 'BOP Lainnya', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '558',  'nama_akun' => 'Beban Transport Pembelian', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
            ['kode_akun' => '559',  'nama_akun' => 'Diskon Pembelian', 'tipe_akun' => 'Expense', 'saldo_awal' => null],
        ];

        foreach ($coaTemplate as $coa) {
            Coa::withoutGlobalScopes()->updateOrCreate(
                ['kode_akun' => $coa['kode_akun'], 'company_id' => null],
                [
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['tipe_akun'],
                    'saldo_awal' => $coa['saldo_awal'] ?? 0,
                    'tanggal_saldo_awal' => now(),
                    'posted_saldo_awal' => false,
                    'company_id' => null, // Template, akan di-copy dengan company_id saat registrasi
                ]
            );
        }

        $this->command->info('✓ COA Template berhasil di-seed! Total: ' . count($coaTemplate) . ' akun.');
    }

    /**
     * Copy COA template untuk company tertentu
     * Method ini akan dipanggil saat user baru registrasi
     */
    public static function copyCoaTemplateForCompany(int $companyId): void
    {
        // Ambil semua COA template (company_id = null)
        $templates = Coa::withoutGlobalScopes()
            ->whereNull('company_id')
            ->get();

        foreach ($templates as $template) {
            Coa::withoutGlobalScopes()->create([
                'kode_akun' => $template->kode_akun,
                'nama_akun' => $template->nama_akun,
                'tipe_akun' => $template->tipe_akun,
                'kategori_akun' => $template->kategori_akun,
                'saldo_awal' => $template->saldo_awal,
                'tanggal_saldo_awal' => now(),
                'posted_saldo_awal' => false,
                'company_id' => $companyId,
            ]);
        }
    }
}

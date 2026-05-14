<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompleteBalancedDataSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Seeding Complete Balanced Data...\n\n";
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 1. SALDO AWAL COA
        $this->seedSaldoAwal();
        
        // 2. MASTER DATA
        $this->seedSatuan();
        $this->seedVendor();
        $this->seedBahanBaku();
        $this->seedProduk();
        $this->seedJabatan();
        $this->seedPegawai();
        $this->seedBOP();
        
        // 3. TRANSAKSI (dengan jurnal balance)
        $this->seedPembelian();
        $this->seedProduksi();
        $this->seedPenjualan();
        $this->seedPenggajian();
        $this->seedPembayaranBeban();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "\n✅ Seeding selesai! Neraca Saldo BALANCE!\n";
    }
    
    private function seedSaldoAwal()
    {
        echo "💰 Setting saldo awal COA...\n";
        
        // Set saldo awal yang balance
        DB::table('coas')->where('kode_akun', '1101')->update(['saldo_awal' => 50000000]); // Kas
        DB::table('coas')->where('kode_akun', '1102')->update(['saldo_awal' => 30000000]); // Bank
        DB::table('coas')->where('kode_akun', '1104')->update(['saldo_awal' => 20000000]); // Persediaan Bahan Baku
        DB::table('coas')->where('kode_akun', '1107')->update(['saldo_awal' => 15000000]); // Persediaan Barang Jadi
        DB::table('coas')->where('kode_akun', '1201')->update(['saldo_awal' => 50000000]); // Peralatan
        DB::table('coas')->where('kode_akun', '3101')->update(['saldo_awal' => 165000000]); // Modal
        
        echo "  ✓ Saldo awal set (Total Debit = Total Kredit = 165,000,000)\n";
    }
    
    private function seedSatuan()
    {
        echo "📏 Seeding satuan...\n";
        
        $satuans = [
            ['nama' => 'Kilogram', 'kode' => 'kg'],
            ['nama' => 'Gram', 'kode' => 'gr'],
            ['nama' => 'Liter', 'kode' => 'ltr'],
            ['nama' => 'Pieces', 'kode' => 'pcs'],
            ['nama' => 'Pack', 'kode' => 'pack'],
        ];
        
        foreach ($satuans as $satuan) {
            DB::table('satuans')->insertOrIgnore($satuan);
        }
        
        echo "  ✓ 5 satuan\n";
    }
    
    private function seedVendor()
    {
        echo "🏪 Seeding vendor...\n";
        
        $vendors = [
            ['nama_vendor' => 'PT Sumber Makmur', 'alamat' => 'Jl. Raya Industri No. 123', 'telepon' => '021-12345678', 'email' => 'info@sumbermakmur.com'],
            ['nama_vendor' => 'CV Mitra Jaya', 'alamat' => 'Jl. Perdagangan No. 45', 'telepon' => '021-87654321', 'email' => 'sales@mitrajaya.com'],
            ['nama_vendor' => 'UD Berkah Sejahtera', 'alamat' => 'Jl. Pasar Baru No. 67', 'telepon' => '021-55556666', 'email' => 'berkah@gmail.com'],
        ];
        
        foreach ($vendors as $vendor) {
            DB::table('vendors')->insert($vendor);
        }
        
        echo "  ✓ 3 vendor\n";
    }
    
    private function seedBahanBaku()
    {
        echo "🥘 Seeding bahan baku...\n";
        
        $bahanBakus = [
            ['nama_bahan' => 'Tepung Terigu', 'satuan_id' => 1, 'stok' => 0, 'harga_satuan' => 12000],
            ['nama_bahan' => 'Gula Pasir', 'satuan_id' => 1, 'stok' => 0, 'harga_satuan' => 15000],
            ['nama_bahan' => 'Telur Ayam', 'satuan_id' => 1, 'stok' => 0, 'harga_satuan' => 28000],
            ['nama_bahan' => 'Mentega', 'satuan_id' => 1, 'stok' => 0, 'harga_satuan' => 45000],
            ['nama_bahan' => 'Susu Bubuk', 'satuan_id' => 1, 'stok' => 0, 'harga_satuan' => 80000],
        ];
        
        foreach ($bahanBakus as $bb) {
            DB::table('bahan_bakus')->insert($bb);
        }
        
        echo "  ✓ 5 bahan baku\n";
    }
    
    private function seedProduk()
    {
        echo "🍰 Seeding produk...\n";
        
        $produks = [
            ['nama_produk' => 'Roti Tawar Premium', 'satuan_id' => 4, 'stok' => 0, 'harga_jual' => 25000, 'btkl_default' => 5000, 'bop_default' => 3000],
            ['nama_produk' => 'Kue Brownies', 'satuan_id' => 4, 'stok' => 0, 'harga_jual' => 35000, 'btkl_default' => 7000, 'bop_default' => 4000],
            ['nama_produk' => 'Cookies Coklat', 'satuan_id' => 5, 'stok' => 0, 'harga_jual' => 45000, 'btkl_default' => 8000, 'bop_default' => 5000],
        ];
        
        foreach ($produks as $produk) {
            DB::table('produks')->insert($produk);
        }
        
        echo "  ✓ 3 produk\n";
    }
    
    private function seedJabatan()
    {
        echo "👔 Seeding jabatan...\n";
        
        $jabatans = [
            ['nama_jabatan' => 'Operator Produksi', 'gaji_pokok' => 4500000],
            ['nama_jabatan' => 'Quality Control', 'gaji_pokok' => 5000000],
            ['nama_jabatan' => 'Supervisor Produksi', 'gaji_pokok' => 7000000],
        ];
        
        foreach ($jabatans as $jabatan) {
            DB::table('jabatans')->insert($jabatan);
        }
        
        echo "  ✓ 3 jabatan\n";
    }
    
    private function seedPegawai()
    {
        echo "👥 Seeding pegawai...\n";
        
        $pegawais = [
            ['nama' => 'Budi Santoso', 'jabatan_id' => 1, 'jenis_pegawai' => 'BTKL', 'tarif_per_jam' => 25000, 'email' => 'budi@company.com', 'telepon' => '081234567890'],
            ['nama' => 'Siti Aminah', 'jabatan_id' => 2, 'jenis_pegawai' => 'BTKTL', 'gaji_pokok' => 5000000, 'email' => 'siti@company.com', 'telepon' => '081234567891'],
            ['nama' => 'Ahmad Yani', 'jabatan_id' => 3, 'jenis_pegawai' => 'BTKTL', 'gaji_pokok' => 7000000, 'email' => 'ahmad@company.com', 'telepon' => '081234567892'],
        ];
        
        foreach ($pegawais as $pegawai) {
            DB::table('pegawais')->insert($pegawai);
        }
        
        echo "  ✓ 3 pegawai\n";
    }
    
    private function seedBOP()
    {
        echo "🏭 Seeding BOP...\n";
        
        $bops = [
            ['nama_bop' => 'Listrik Pabrik', 'kategori' => 'Utilitas', 'biaya_per_unit' => 500],
            ['nama_bop' => 'Air Pabrik', 'kategori' => 'Utilitas', 'biaya_per_unit' => 200],
            ['nama_bop' => 'Pemeliharaan Mesin', 'kategori' => 'Pemeliharaan', 'biaya_per_unit' => 1000],
        ];
        
        foreach ($bops as $bop) {
            DB::table('bops')->insert($bop);
        }
        
        echo "  ✓ 3 BOP\n";
    }
    
    private function seedPembelian()
    {
        echo "\n📦 Seeding transaksi pembelian (dengan jurnal)...\n";
        
        $tanggal = Carbon::now()->subDays(30);
        
        // Pembelian 1: Tepung Terigu 100kg @ 12,000 = 1,200,000 (Tunai)
        $pembelianId = DB::table('pembelians')->insertGetId([
            'nomor_pembelian' => 'PB-' . $tanggal->format('Ymd') . '-0001',
            'vendor_id' => 1,
            'tanggal' => $tanggal,
            'total_harga' => 1200000,
            'terbayar' => 1200000,
            'sisa_pembayaran' => 0,
            'status' => 'lunas',
            'payment_method' => 'cash',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('pembelian_details')->insert([
            'pembelian_id' => $pembelianId,
            'bahan_baku_id' => 1,
            'jumlah' => 100,
            'harga_satuan' => 12000,
            'subtotal' => 1200000,
            'satuan' => 'kg',
            'faktor_konversi' => 1,
        ]);
        
        // Update stok bahan baku
        DB::table('bahan_bakus')->where('id', 1)->increment('stok', 100);
        
        // Jurnal: Dr. Persediaan Bahan Baku 1,200,000 | Cr. Kas 1,200,000
        $this->createJournal($tanggal, 'purchase', $pembelianId, 'Pembelian Tepung Terigu', [
            ['code' => '1104', 'debit' => 1200000, 'credit' => 0],
            ['code' => '1101', 'debit' => 0, 'credit' => 1200000],
        ]);
        
        echo "  ✓ Pembelian 1: Rp 1,200,000 (Balance)\n";
        
        // Pembelian 2: Gula 50kg @ 15,000 = 750,000 (Transfer)
        $tanggal2 = $tanggal->copy()->addDays(2);
        $pembelianId2 = DB::table('pembelians')->insertGetId([
            'nomor_pembelian' => 'PB-' . $tanggal2->format('Ymd') . '-0002',
            'vendor_id' => 2,
            'tanggal' => $tanggal2,
            'total_harga' => 750000,
            'terbayar' => 750000,
            'sisa_pembayaran' => 0,
            'status' => 'lunas',
            'payment_method' => 'transfer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('pembelian_details')->insert([
            'pembelian_id' => $pembelianId2,
            'bahan_baku_id' => 2,
            'jumlah' => 50,
            'harga_satuan' => 15000,
            'subtotal' => 750000,
            'satuan' => 'kg',
            'faktor_konversi' => 1,
        ]);
        
        DB::table('bahan_bakus')->where('id', 2)->increment('stok', 50);
        
        // Jurnal: Dr. Persediaan Bahan Baku 750,000 | Cr. Bank 750,000
        $this->createJournal($tanggal2, 'purchase', $pembelianId2, 'Pembelian Gula Pasir', [
            ['code' => '1104', 'debit' => 750000, 'credit' => 0],
            ['code' => '1102', 'debit' => 0, 'credit' => 750000],
        ]);
        
        echo "  ✓ Pembelian 2: Rp 750,000 (Balance)\n";
    }
    
    private function seedProduksi()
    {
        echo "\n🏭 Seeding transaksi produksi (dengan jurnal)...\n";
        
        $tanggal = Carbon::now()->subDays(25);
        
        // Produksi Roti Tawar: 50 pcs
        // Bahan: Tepung 20kg @ 12,000 = 240,000
        // BTKL: 5,000 x 50 = 250,000
        // BOP: 3,000 x 50 = 150,000
        // Total HPP: 640,000
        
        $produksiId = DB::table('produksis')->insertGetId([
            'tanggal' => $tanggal,
            'produk_id' => 1,
            'jumlah' => 50,
            'total_biaya_bahan' => 240000,
            'total_btkl' => 250000,
            'total_bop' => 150000,
            'total_hpp' => 640000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update stok
        DB::table('bahan_bakus')->where('id', 1)->decrement('stok', 20);
        DB::table('produks')->where('id', 1)->increment('stok', 50);
        
        // Jurnal Produksi:
        // Dr. Barang Dalam Proses 640,000
        // Cr. Persediaan Bahan Baku 240,000
        // Cr. Gaji & Upah 250,000
        // Cr. BOP 150,000
        $this->createJournal($tanggal, 'production', $produksiId, 'Produksi Roti Tawar 50 pcs', [
            ['code' => '1106', 'debit' => 640000, 'credit' => 0], // Barang Dalam Proses
            ['code' => '1104', 'debit' => 0, 'credit' => 240000], // Persediaan Bahan Baku
            ['code' => '5101', 'debit' => 0, 'credit' => 250000], // Gaji & Upah
            ['code' => '5102', 'debit' => 0, 'credit' => 150000], // BOP
        ]);
        
        // Transfer ke Barang Jadi
        $this->createJournal($tanggal, 'production_finish', $produksiId, 'Transfer ke Barang Jadi', [
            ['code' => '1107', 'debit' => 640000, 'credit' => 0], // Persediaan Barang Jadi
            ['code' => '1106', 'debit' => 0, 'credit' => 640000], // Barang Dalam Proses
        ]);
        
        echo "  ✓ Produksi Roti Tawar: 50 pcs, HPP Rp 640,000 (Balance)\n";
    }
    
    private function seedPenjualan()
    {
        echo "\n💰 Seeding transaksi penjualan (dengan jurnal)...\n";
        
        $tanggal = Carbon::now()->subDays(20);
        
        // Penjualan 30 pcs Roti @ 25,000 = 750,000
        // HPP: 640,000 / 50 * 30 = 384,000
        
        $penjualanId = DB::table('penjualans')->insertGetId([
            'nomor_penjualan' => 'PJ-' . $tanggal->format('Ymd') . '-0001',
            'tanggal' => $tanggal,
            'produk_id' => 1,
            'jumlah' => 30,
            'harga_satuan' => 25000,
            'diskon_nominal' => 0,
            'total' => 750000,
            'payment_method' => 'cash',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update stok
        DB::table('produks')->where('id', 1)->decrement('stok', 30);
        
        // Jurnal Penjualan: Dr. Kas 750,000 | Cr. Penjualan 750,000
        $this->createJournal($tanggal, 'sale', $penjualanId, 'Penjualan Roti Tawar 30 pcs', [
            ['code' => '1101', 'debit' => 750000, 'credit' => 0],
            ['code' => '4101', 'debit' => 0, 'credit' => 750000],
        ]);
        
        // Jurnal HPP: Dr. HPP 384,000 | Cr. Persediaan Barang Jadi 384,000
        $this->createJournal($tanggal, 'sale_cogs', $penjualanId, 'HPP Penjualan', [
            ['code' => '5001', 'debit' => 384000, 'credit' => 0],
            ['code' => '1107', 'debit' => 0, 'credit' => 384000],
        ]);
        
        echo "  ✓ Penjualan: Rp 750,000, HPP Rp 384,000 (Balance)\n";
    }
    
    private function seedPenggajian()
    {
        echo "\n💵 Seeding penggajian (dengan jurnal)...\n";
        
        $tanggal = Carbon::now()->subDays(15);
        
        // Gaji Budi (BTKL): 160 jam x 25,000 = 4,000,000
        $penggajianId = DB::table('penggajians')->insertGetId([
            'pegawai_id' => 1,
            'tanggal_penggajian' => $tanggal,
            'tarif_per_jam' => 25000,
            'total_jam_kerja' => 160,
            'gaji_pokok' => 0,
            'tunjangan' => 500000,
            'bonus' => 0,
            'potongan' => 0,
            'asuransi' => 200000,
            'total_gaji' => 4300000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Jurnal: Dr. Beban Gaji 4,300,000 | Cr. Kas 4,300,000
        $this->createJournal($tanggal, 'payroll', $penggajianId, 'Gaji Budi Santoso', [
            ['code' => '5101', 'debit' => 4300000, 'credit' => 0],
            ['code' => '1101', 'debit' => 0, 'credit' => 4300000],
        ]);
        
        echo "  ✓ Penggajian: Rp 4,300,000 (Balance)\n";
    }
    
    private function seedPembayaranBeban()
    {
        echo "\n💸 Seeding pembayaran beban (dengan jurnal)...\n";
        
        $tanggal = Carbon::now()->subDays(10);
        
        // Bayar Listrik: 1,500,000
        $expenseId = DB::table('expense_payments')->insertGetId([
            'tanggal' => $tanggal,
            'coa_id' => DB::table('coas')->where('kode_akun', '5201')->value('id'), // Beban Listrik
            'jumlah' => 1500000,
            'keterangan' => 'Pembayaran listrik bulan ini',
            'metode_pembayaran' => 'transfer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Jurnal: Dr. Beban Listrik 1,500,000 | Cr. Bank 1,500,000
        $this->createJournal($tanggal, 'expense', $expenseId, 'Pembayaran Listrik', [
            ['code' => '5201', 'debit' => 1500000, 'credit' => 0],
            ['code' => '1102', 'debit' => 0, 'credit' => 1500000],
        ]);
        
        echo "  ✓ Beban Listrik: Rp 1,500,000 (Balance)\n";
    }
    
    private function createJournal($tanggal, $refType, $refId, $description, $lines)
    {
        // Create journal entry
        $entryId = DB::table('journal_entries')->insertGetId([
            'tanggal' => $tanggal,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create journal lines
        foreach ($lines as $line) {
            $account = DB::table('accounts')->where('code', $line['code'])->first();
            if (!$account) {
                // Create account if not exists
                $coa = DB::table('coas')->where('kode_akun', $line['code'])->first();
                if ($coa) {
                    $accountId = DB::table('accounts')->insertGetId([
                        'code' => $line['code'],
                        'name' => $coa->nama_akun,
                        'type' => $this->getAccountType($line['code']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    continue;
                }
            } else {
                $accountId = $account->id;
            }
            
            DB::table('journal_lines')->insert([
                'journal_entry_id' => $entryId,
                'account_id' => $accountId,
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function getAccountType($code)
    {
        $firstDigit = substr($code, 0, 1);
        switch ($firstDigit) {
            case '1': return 'asset';
            case '2': return 'liability';
            case '3': return 'equity';
            case '4': return 'revenue';
            case '5': return 'expense';
            default: return 'asset';
        }
    }
}

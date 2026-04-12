<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== MEMBUAT DATA AWAL UNTUK PENDAFTARAN BARU ===\n\n";

try {
    // 1. Buat user admin
    echo "=== MEMBUAT USER ADMIN ===\n";
    
    // Cek apakah user admin sudah ada
    $existingAdmin = DB::table('users')->where('email', 'admin@umkm.com')->first();
    if (!$existingAdmin) {
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Administrator',
            'email' => 'admin@umkm.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ User admin dibuat (ID: {$adminId})\n";
    } else {
        echo "- User admin sudah ada\n";
    }

    // 2. Buat jabatan
    echo "\n=== MEMBUAT JABATAN ===\n";
    
    $jabatans = [
        ['nama' => 'Operator Produksi'],
        ['nama' => 'Mandor Produksi'],
        ['nama' => 'Supervisor Produksi'],
        ['nama' => 'Staff Gudang'],
        ['nama' => 'Quality Control'],
    ];
    
    foreach ($jabatans as $index => $jabatan) {
        $existingJabatan = DB::table('jabatans')->where('nama', $jabatan['nama'])->first();
        if (!$existingJabatan) {
            $jabatanId = DB::table('jabatans')->insertGetId([
                'nama' => $jabatan['nama'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✓ Jabatan {$jabatan['nama']} dibuat (ID: {$jabatanId})\n";
        } else {
            echo "- Jabatan {$jabatan['nama']} sudah ada\n";
        }
    }

    // 3. Buat satuan
    echo "\n=== MEMBUAT SATUAN ===\n";
    
    $satuans = [
        ['nama' => 'Kg', 'deskripsi' => 'Kilogram'],
        ['nama' => 'Liter', 'deskripsi' => 'Liter'],
        ['nama' => 'Pcs', 'deskripsi' => 'Pieces'],
        ['nama' => 'Box', 'deskripsi' => 'Box'],
        ['nama' => 'Karung', 'deskripsi' => 'Karung'],
    ];
    
    foreach ($satuans as $satuan) {
        $existingSatuan = DB::table('satuans')->where('nama', $satuan['nama'])->first();
        if (!$existingSatuan) {
            $satuanId = DB::table('satuans')->insertGetId([
                'nama' => $satuan['nama'],
                'deskripsi' => $satuan['deskripsi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✓ Satuan {$satuan['nama']} dibuat (ID: {$satuanId})\n";
        } else {
            echo "- Satuan {$satuan['nama']} sudah ada\n";
        }
    }

    // 4. Buat COA awal
    echo "\n=== MEMBUAT COA AWAL ===\n";
    
    $coas = [
        ['kode_akun' => '1001', 'nama_akun' => 'Kas', 'tipe_akun' => 'assets', 'saldo_normal' => 'debit'],
        ['kode_akun' => '4001', 'nama_akun' => 'Penjualan', 'tipe_akun' => 'revenue', 'saldo_normal' => 'kredit'],
        ['kode_akun' => '5001', 'nama_akun' => 'HPP', 'tipe_akun' => 'cost', 'saldo_normal' => 'debit'],
        ['kode_akun' => '6001', 'nama_akun' => 'Biaya Operasional', 'tipe_akun' => 'expense', 'saldo_normal' => 'debit'],
    ];
    
    foreach ($coas as $coa) {
        $existingCoa = DB::table('coas')->where('kode_akun', $coa['kode_akun'])->first();
        if (!$existingCoa) {
            $coaId = DB::table('coas')->insertGetId([
                'kode_akun' => $coa['kode_akun'],
                'nama_akun' => $coa['nama_akun'],
                'tipe_akun' => $coa['tipe_akun'],
                'saldo_normal' => $coa['saldo_normal'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✓ COA {$coa['nama_akun']} ({$coa['kode_akun']}) dibuat (ID: {$coaId})\n";
        } else {
            echo "- COA {$coa['nama_akun']} sudah ada\n";
        }
    }

    // 5. Buat proses produksi awal
    echo "\n=== MEMBUAT PROSES PRODUKSI AWAL ===\n";
    
    $prosesProduksi = [
        [
            'kode_proses' => 'PRO-001',
            'nama_proses' => 'Perbumbuan',
            'deskripsi' => 'Proses perbumbuan produk makanan',
            'tarif_btkl' => 25000,
            'satuan_btkl' => 'jam',
            'kapasitas_per_jam' => 200,
            'jabatan_id' => 1, // Operator Produksi
        ],
        [
            'kode_proses' => 'PRO-002',
            'nama_proses' => 'Penggorengan',
            'deskripsi' => 'Proses penggorengan produk',
            'tarif_btkl' => 30000,
            'satuan_btkl' => 'jam',
            'kapasitas_per_jam' => 100,
            'jabatan_id' => 2, // Mandor Produksi
        ],
        [
            'kode_proses' => 'PRO-003',
            'nama_proses' => 'Pengemasan',
            'deskripsi' => 'Proses pengemasan produk jadi',
            'tarif_btkl' => 20000,
            'satuan_btkl' => 'jam',
            'kapasitas_per_jam' => 150,
            'jabatan_id' => 3, // Supervisor Produksi
        ],
    ];
    
    foreach ($prosesProduksi as $index => $proses) {
        $existingProses = DB::table('proses_produksis')->where('kode_proses', $proses['kode_proses'])->first();
        if (!$existingProses) {
            $prosesId = DB::table('proses_produksis')->insertGetId([
                'kode_proses' => $proses['kode_proses'],
                'nama_proses' => $proses['nama_proses'],
                'deskripsi' => $proses['deskripsi'],
                'tarif_btkl' => $proses['tarif_btkl'],
                'satuan_btkl' => $proses['satuan_btkl'],
                'kapasitas_per_jam' => $proses['kapasitas_per_jam'],
                'jabatan_id' => $proses['jabatan_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Hitung biaya per produk
            $biayaPerProduk = $proses['kapasitas_per_jam'] > 0 ? $proses['tarif_btkl'] / $proses['kapasitas_per_jam'] : 0;
            
            echo "✓ Proses {$proses['nama_proses']} dibuat (ID: {$prosesId})\n";
            echo "  - Tarif: Rp {$proses['tarif_btkl']}/jam\n";
            echo "  - Kapasitas: {$proses['kapasitas_per_jam']} pcs/jam\n";
            echo "  - Biaya/Produk: Rp " . number_format($biayaPerProduk, 2) . "\n";
        } else {
            echo "- Proses {$proses['nama_proses']} sudah ada\n";
        }
    }

    // 6. Buat BOP proses awal
    echo "\n=== MEMBUAT BOP PROSES AWAL ===\n";
    
    foreach ($prosesProduksi as $proses) {
        $existingBop = DB::table('bop_proses')->where('proses_produksi_id', function($query) use ($proses) {
            $query->select('id')->from('proses_produksis')->where('kode_proses', $proses['kode_proses']);
        })->first();
        
        if (!$existingBop) {
            // Ambil ID proses yang baru dibuat
            $prosesRecord = DB::table('proses_produksis')->where('kode_proses', $proses['kode_proses'])->first();
            
            if ($prosesRecord) {
                $bopId = DB::table('bop_proses')->insertGetId([
                    'proses_produksi_id' => $prosesRecord->id,
                    'listrik_per_jam' => 5000,
                    'gas_bbm_per_jam' => 3000,
                    'penyusutan_mesin_per_jam' => 2000,
                    'maintenance_per_jam' => 1500,
                    'gaji_mandor_per_jam' => 1000,
                    'lain_lain_per_jam' => 500,
                    'total_bop_per_jam' => 13000,
                    'kapasitas_per_jam' => $proses['kapasitas_per_jam'],
                    'bop_per_unit' => 13000,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "✓ BOP Proses untuk {$proses['nama_proses']} dibuat (ID: {$bopId})\n";
            }
        }
    }

    // 7. Verifikasi data yang dibuat
    echo "\n=== VERIFIKASI DATA ===\n";
    
    $tables = ['users', 'jabatans', 'satuans', 'coas', 'proses_produksis', 'bop_proses'];
    foreach ($tables as $table) {
        $count = DB::table($table)->count();
        echo "{$table}: {$count} records\n";
    }

    echo "\n=== DATA AWAL BERHASIL DIBUAT ===\n";
    echo "✅ User admin: admin@umkm.com / admin123\n";
    echo "✅ Jabatan: " . DB::table('jabatans')->count() . " records\n";
    echo "✅ Satuan: " . DB::table('satuans')->count() . " records\n";
    echo "✅ COA: " . DB::table('coas')->count() . " records\n";
    echo "✅ Proses Produksi: " . DB::table('proses_produksis')->count() . " records\n";
    echo "✅ BOP Proses: " . DB::table('bop_proses')->count() . " records\n";
    echo "\n🚀 SISTEM SIAP UNTUK PENDAFTARAN BARU!\n";
    echo "🌐 Server berjalan di: http://127.0.0.1:8000\n";
    echo "👤 Login dengan: admin@umkm.com / admin123\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ Stack trace: " . $e->getTraceAsString() . "\n";
}

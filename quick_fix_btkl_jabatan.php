<?php

/**
 * Quick fix untuk masalah BTKL yang menampilkan data publik
 * Akan menambahkan method create di BtklController
 */

$controllerPath = 'app/Http/Controllers/MasterData/BtklController.php';
$controllerContent = file_get_contents($controllerPath);

// Cek apakah method create sudah ada
if (strpos($controllerContent, 'public function create()') === false) {
    // Tambahkan method create setelah method index
    $createMethod = '
    public function create()
    {
        try {
            // Generate kode proses otomatis
            $lastBtkl = Btkl::orderBy(\'kode_proses\', \'desc\')->first();
            $nextNumber = $lastBtkl ? (int)substr($lastBtkl->kode_proses, 2) + 1 : 1;
            $nextKode = \'BT\' . str_pad($nextNumber, 3, \'0\', STR_PAD_LEFT);

            // Ambil jabatan BTKL yang memiliki pegawai dengan user_id yang sama dengan user login
            $jabatanBtkl = Jabatan::where(\'kategori_tenaga_kerja\', \'BTKL\')
                ->whereHas(\'pegawais\', function($query) {
                    $query->where(\'user_id\', auth()->id());
                })
                ->with([\'pegawais\' => function($query) {
                    $query->where(\'user_id\', auth()->id());
                }])
                ->get();

            // Hitung jumlah pegawai per jabatan dan siapkan data untuk JavaScript
            $employeeData = $jabatanBtkl->map(function($jabatan) {
                return [
                    \'id\' => $jabatan->id,
                    \'nama\' => $jabatan->nama,
                    \'tarif\' => $jabatan->tarif ?? 0,
                    \'pegawai_count\' => $jabatan->pegawais->count()
                ];
            });

            $satuanOptions = [\'pcs\', \'kg\', \'liter\', \'meter\', \'unit\'];

            return view(\'master-data.btkl.create\', compact(
                \'nextKode\', 
                \'jabatanBtkl\', 
                \'employeeData\', 
                \'satuanOptions\'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()->with(\'error\', \'Terjadi kesalahan: \' . $e->getMessage());
        }
    }
';

    // Cari posisi setelah method index
    $indexMethodEnd = strpos($controllerContent, '}', strpos($controllerContent, 'public function index()'));
    
    if ($indexMethodEnd !== false) {
        // Insert method create setelah method index
        $newContent = substr($controllerContent, 0, $indexMethodEnd + 1) . 
                     $createMethod . 
                     substr($controllerContent, $indexMethodEnd + 1);
        
        file_put_contents($controllerPath, $newContent);
        echo "✅ Method create berhasil ditambahkan ke BtklController\n";
    } else {
        echo "❌ Tidak dapat menemukan method index\n";
    }
} else {
    echo "✅ Method create sudah ada di BtklController\n";
}

// Perbaiki model Jabatan jika ada duplikasi boot method
$jabatanPath = 'app/Models/Jabatan.php';
$jabatanContent = file_get_contents($jabatanPath);

// Cek apakah ada duplikasi boot method
$bootCount = substr_count($jabatanContent, 'protected static function boot()');
if ($bootCount > 1) {
    echo "❌ Ditemukan duplikasi boot method di Jabatan.php\n";
    echo "Silakan perbaiki manual dengan menggabungkan kedua method boot\n";
} else {
    echo "✅ Model Jabatan tidak ada duplikasi boot method\n";
}

echo "\n=== QUICK FIX SELESAI ===\n";
echo "Sekarang halaman BTKL create seharusnya hanya menampilkan jabatan milik user yang login.\n";
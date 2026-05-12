<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING DEDI GUNAWAN SALARY DATA ===\n\n";

try {
    DB::beginTransaction();
    
    // 1. Find Dedi Gunawan
    $dedi = DB::table('pegawais')
        ->where('nama', 'like', '%Dedi%')
        ->first();
    
    if (!$dedi) {
        throw new Exception("Dedi Gunawan not found!");
    }
    
    echo "BEFORE FIX:\n";
    echo "Dedi Gunawan ID: {$dedi->id}\n";
    echo "Jabatan ID: " . ($dedi->jabatan_id ?? 'NULL') . "\n";
    echo "Gaji Pokok: " . number_format($dedi->gaji_pokok ?? 0, 0, ',', '.') . "\n";
    echo "Tarif per Jam: " . number_format($dedi->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
    echo "\n";
    
    // 2. Find Bagian Gudang jabatan
    $jabatanGudang = DB::table('jabatans')
        ->where('nama', 'Bagian Gudang')
        ->first();
    
    if (!$jabatanGudang) {
        throw new Exception("Jabatan 'Bagian Gudang' not found!");
    }
    
    echo "JABATAN FOUND:\n";
    echo "Jabatan ID: {$jabatanGudang->id}\n";
    echo "Nama: {$jabatanGudang->nama}\n";
    echo "Kategori: {$jabatanGudang->kategori}\n";
    echo "Gaji Pokok: " . number_format($jabatanGudang->gaji_pokok ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan: " . number_format($jabatanGudang->tunjangan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: " . number_format($jabatanGudang->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: " . number_format($jabatanGudang->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    echo "Asuransi: " . number_format($jabatanGudang->asuransi ?? 0, 0, ',', '.') . "\n";
    echo "\n";
    
    // 3. Update Dedi Gunawan's jabatan_id
    $updated = DB::table('pegawais')
        ->where('id', $dedi->id)
        ->update([
            'jabatan_id' => $jabatanGudang->id,
            'jenis_pegawai' => 'btktl', // Ensure consistent with jabatan
            'updated_at' => now()
        ]);
    
    if ($updated) {
        echo "SUCCESS: Updated Dedi Gunawan's jabatan_id to {$jabatanGudang->id}\n";
    } else {
        throw new Exception("Failed to update Dedi Gunawan's jabatan_id");
    }
    
    // 4. Verify the fix
    $dediAfter = DB::table('pegawais')
        ->where('id', $dedi->id)
        ->first();
    
    echo "\nAFTER FIX:\n";
    echo "Dedi Gunawan ID: {$dediAfter->id}\n";
    echo "Jabatan ID: {$dediAfter->jabatan_id}\n";
    echo "Jenis Pegawai: {$dediAfter->jenis_pegawai}\n";
    echo "\n";
    
    // 5. Test the relationship
    echo "TESTING RELATIONSHIP:\n";
    $jabatanData = DB::table('jabatans')
        ->where('id', $dediAfter->jabatan_id)
        ->first();
    
    if ($jabatanData) {
        echo "✅ Relationship works!\n";
        echo "Jabatan: {$jabatanData->nama}\n";
        echo "Gaji Pokok: " . number_format($jabatanData->gaji_pokok ?? 0, 0, ',', '.') . "\n";
        echo "Total Tunjangan: " . number_format(($jabatanData->tunjangan ?? 0) + ($jabatanData->tunjangan_transport ?? 0) + ($jabatanData->tunjangan_konsumsi ?? 0), 0, ',', '.') . "\n";
        echo "Asuransi: " . number_format($jabatanData->asuransi ?? 0, 0, ',', '.') . "\n";
        
        $totalGaji = ($jabatanData->gaji_pokok ?? 0) + 
                    ($jabatanData->tunjangan ?? 0) + 
                    ($jabatanData->tunjangan_transport ?? 0) + 
                    ($jabatanData->tunjangan_konsumsi ?? 0) + 
                    ($jabatanData->asuransi ?? 0);
        echo "TOTAL GAJI EXPECTED: " . number_format($totalGaji, 0, ',', '.') . "\n";
    } else {
        throw new Exception("Relationship test failed!");
    }
    
    DB::commit();
    echo "\n✅ FIX COMPLETED SUCCESSFULLY!\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIX COMPLETE ===\n";
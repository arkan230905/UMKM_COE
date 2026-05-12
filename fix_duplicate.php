<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING DUPLICATE ISSUE ===\n";

// Find all Air records
$airRecords = DB::table('bahan_pendukungs')->where('nama_bahan', 'Air')->get();
echo "Found " . $airRecords->count() . " Air records:\n";

foreach ($airRecords as $record) {
    echo "ID: {$record->id} - {$record->kode_bahan} - Sub1: {$record->sub_satuan_1_nilai}\n";
}

// Delete the newer record (ID 32) and keep/update the older one if it exists
if ($airRecords->count() > 1) {
    echo "\nDeleting duplicate records...\n";
    
    // Keep the record with the lowest ID, delete others
    $keepRecord = $airRecords->sortBy('id')->first();
    $deleteRecords = $airRecords->where('id', '!=', $keepRecord->id);
    
    foreach ($deleteRecords as $record) {
        echo "Deleting ID: {$record->id}\n";
        DB::table('bahan_pendukungs')->where('id', $record->id)->delete();
    }
    
    echo "Kept record ID: {$keepRecord->id}\n";
    
    // Update the kept record with correct data
    echo "Updating kept record with correct data...\n";
    DB::table('bahan_pendukungs')
        ->where('id', $keepRecord->id)
        ->update([
            'sub_satuan_1_nilai' => 1000,
            'sub_satuan_2_nilai' => 1,
            'sub_satuan_3_nilai' => 15000,
            'updated_at' => now()
        ]);
    
    echo "✅ Updated successfully!\n";
}

// If we need ID 23 specifically, let's update the existing record to have ID 23
$currentAir = DB::table('bahan_pendukungs')->where('nama_bahan', 'Air')->first();
if ($currentAir && $currentAir->id != 23) {
    echo "\nChanging ID from {$currentAir->id} to 23...\n";
    
    // First, check if ID 23 exists
    $id23Exists = DB::table('bahan_pendukungs')->where('id', 23)->exists();
    
    if (!$id23Exists) {
        DB::statement('SET foreign_key_checks=0');
        DB::table('bahan_pendukungs')->where('id', $currentAir->id)->update(['id' => 23]);
        DB::statement('SET foreign_key_checks=1');
        echo "✅ Changed ID to 23!\n";
    } else {
        echo "ID 23 already exists, updating that record instead...\n";
        DB::table('bahan_pendukungs')
            ->where('id', 23)
            ->update([
                'kode_bahan' => 'AIR001',
                'nama_bahan' => 'Air',
                'sub_satuan_1_nilai' => 1000,
                'sub_satuan_2_nilai' => 1,
                'sub_satuan_3_nilai' => 15000,
                'updated_at' => now()
            ]);
        
        // Delete the other Air record
        DB::table('bahan_pendukungs')->where('id', $currentAir->id)->delete();
        echo "✅ Updated ID 23 and removed duplicate!\n";
    }
}

// Final verification
echo "\n=== FINAL VERIFICATION ===\n";
$finalAir = DB::table('bahan_pendukungs')->where('id', 23)->first();
if ($finalAir) {
    echo "ID 23 - {$finalAir->nama_bahan}:\n";
    echo "Sub1: {$finalAir->sub_satuan_1_nilai}\n";
    echo "Sub2: {$finalAir->sub_satuan_2_nilai}\n";
    echo "Sub3: {$finalAir->sub_satuan_3_nilai}\n";
} else {
    echo "ID 23 not found!\n";
}
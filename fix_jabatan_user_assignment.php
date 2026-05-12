<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING JABATAN USER ASSIGNMENT ===\n\n";

// Step 1: Fix orphan Jabatan (assign to user_id 1 or first owner)
$orphanJabatans = \App\Models\Jabatan::whereNull('user_id')->orWhere('user_id', 0)->get();
echo "Found {$orphanJabatans->count()} orphan Jabatan records\n";

if ($orphanJabatans->count() > 0) {
    $firstOwner = \App\Models\User::where('role', 'owner')->first();
    if ($firstOwner) {
        foreach ($orphanJabatans as $jab) {
            $jab->user_id = $firstOwner->id;
            $jab->save();
            echo "  ✓ Assigned '{$jab->nama}' to user {$firstOwner->name} (ID: {$firstOwner->id})\n";
        }
    }
}

// Step 2: Create default Jabatan for owners who don't have any
$owners = \App\Models\User::where('role', 'owner')->get();
echo "\nChecking owners without Jabatan data...\n";

foreach ($owners as $owner) {
    $jabatanCount = \App\Models\Jabatan::where('user_id', $owner->id)->count();
    
    if ($jabatanCount == 0) {
        echo "\nOwner '{$owner->name}' (ID: {$owner->id}) has NO Jabatan. Creating defaults...\n";
        
        // Generate unique kode_jabatan
        $lastBtkl = \App\Models\Jabatan::where('kode_jabatan', 'like', 'BT%')->orderBy('kode_jabatan', 'desc')->first();
        $nextNumber = $lastBtkl ? ((int) substr($lastBtkl->kode_jabatan, 2) + 1) : 1;
        
        // Create default BTKL Jabatan
        $btklJabatan = \App\Models\Jabatan::create([
            'user_id' => $owner->id,
            'kode_jabatan' => 'BT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT),
            'nama' => 'Operator Produksi - ' . $owner->name,
            'kategori' => 'btkl',
            'gaji_pokok' => 0,
            'tarif_per_jam' => 15000,
            'tarif' => 15000,
            'tunjangan' => 0,
            'tunjangan_transport' => 0,
            'tunjangan_konsumsi' => 0,
            'asuransi' => 0,
        ]);
        echo "  ✓ Created BTKL Jabatan: {$btklJabatan->nama}\n";
        
        $nextNumber++;
        
        // Create default BTKTL Jabatan
        $btktlJabatan = \App\Models\Jabatan::create([
            'user_id' => $owner->id,
            'kode_jabatan' => 'BT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT),
            'nama' => 'Staff Administrasi - ' . $owner->name,
            'kategori' => 'btktl',
            'gaji_pokok' => 3000000,
            'tarif_per_jam' => 0,
            'tarif' => 0,
            'tunjangan' => 500000,
            'tunjangan_transport' => 200000,
            'tunjangan_konsumsi' => 300000,
            'asuransi' => 100000,
        ]);
        echo "  ✓ Created BTKTL Jabatan: {$btktlJabatan->nama}\n";
    } else {
        echo "  ✓ Owner '{$owner->name}' already has {$jabatanCount} Jabatan record(s)\n";
    }
}

echo "\n=== SUMMARY ===\n";
foreach ($owners as $owner) {
    $btklCount = \App\Models\Jabatan::where('user_id', $owner->id)->where('kategori', 'btkl')->count();
    $btktlCount = \App\Models\Jabatan::where('user_id', $owner->id)->where('kategori', 'btktl')->count();
    echo "Owner: {$owner->name} (ID: {$owner->id})\n";
    echo "  - BTKL Jabatan: {$btklCount}\n";
    echo "  - BTKTL Jabatan: {$btktlCount}\n";
}

echo "\n✅ DONE! All owners now have Jabatan data.\n";

<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;

echo "🧪 Testing Bahan Baku & Pendukung After Fix\n";
echo "============================================\n\n";

// Simulate login as each user and check what they see
$users = DB::table('users')->get(['id', 'name']);

foreach ($users as $user) {
    echo "👤 USER {$user->id}: {$user->name}\n";
    echo str_repeat("-", 50) . "\n";
    
    // Simulate auth
    auth()->loginUsingId($user->id);
    
    // Test Bahan Baku
    $bahanBakus = BahanBaku::where('user_id', auth()->id())->get();
    echo "📦 Bahan Baku: {$bahanBakus->count()} records\n";
    foreach ($bahanBakus as $bahan) {
        echo "  - {$bahan->nama_bahan} (ID: {$bahan->id}, User: {$bahan->user_id})\n";
    }
    
    // Test Bahan Pendukung
    $bahanPendukungs = BahanPendukung::where('user_id', auth()->id())->get();
    echo "🔧 Bahan Pendukung: {$bahanPendukungs->count()} records\n";
    foreach ($bahanPendukungs as $bahan) {
        echo "  - {$bahan->nama_bahan} (ID: {$bahan->id}, User: {$bahan->user_id})\n";
    }
    
    echo "\n";
}

echo "✅ Test completed!\n";
echo "\nExpected behavior:\n";
echo "- Each user should ONLY see their own data\n";
echo "- User 1 should see their own bahan\n";
echo "- User 2 should see their own bahan\n";
echo "- User 3 should see their own bahan\n";
echo "- User 4 should see ZERO bahan (no data yet)\n";

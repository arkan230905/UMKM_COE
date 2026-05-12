<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing BOP tables with proper user_id for multi-tenant...\n\n";

// Get all users
$users = \App\Models\User::all();

foreach ($users as $user) {
    echo "Processing user: " . $user->name . " (ID: " . $user->id . ")\n";
    
    // Fix bops table
    $bops = \App\Models\Bop::whereNull('user_id')->get();
    foreach ($bops as $bop) {
        $bop->user_id = $user->id;
        $bop->save();
        echo "  - Updated BOP: " . $bop->nama_akun . " (ID: " . $bop->id . ")\n";
    }
    
    // Fix bop_proses table
    $bopProses = \App\Models\BopProses::whereNull('user_id')->get();
    foreach ($bopProses as $bp) {
        // Check if this bop_proses is related to user's proses_produksi
        if ($bp->proses_produksi_id) {
            $prosesProduksi = \App\Models\ProsesProduksi::find($bp->proses_produksi_id);
            if ($prosesProduksi && $prosesProduksi->user_id == $user->id) {
                $bp->user_id = $user->id;
                $bp->save();
                echo "  - Updated BOP Proses for proses: " . $prosesProduksi->nama_proses . " (ID: " . $bp->id . ")\n";
            }
        }
    }
    
    // Fix beban_operasional table
    $bebanOperasional = \App\Models\BebanOperasional::whereNull('user_id')->get();
    foreach ($bebanOperasional as $bo) {
        // If created_by matches current user, assign user_id
        if ($bo->created_by == $user->id) {
            $bo->user_id = $user->id;
            $bo->save();
            echo "  - Updated Beban Operasional: " . $bo->nama_beban . " (ID: " . $bo->id . ")\n";
        }
    }
    
    echo "\n";
}

echo "=== VERIFICATION ===\n";

// Check bops
echo "BOPs with user_id:\n";
$bops = \App\Models\Bop::whereNotNull('user_id')->get();
foreach ($bops as $bop) {
    $userName = $bop->user ? $bop->user->name : 'Unknown';
    echo "- " . $bop->nama_akun . " (User: " . $userName . ")\n";
}

echo "\nBOP Proses with user_id:\n";
$bopProses = \App\Models\BopProses::whereNotNull('user_id')->get();
foreach ($bopProses as $bp) {
    $userName = $bp->user ? $bp->user->name : 'Unknown';
    echo "- BOP Proses ID " . $bp->id . " (User: " . $userName . ")\n";
}

echo "\nBeban Operasional with user_id:\n";
$bebanOperasional = \App\Models\BebanOperasional::whereNotNull('user_id')->get();
foreach ($bebanOperasional as $bo) {
    $userName = $bo->user ? $bo->user->name : 'Unknown';
    echo "- " . $bo->nama_beban . " (User: " . $userName . ")\n";
}

echo "\nDone! BOP tables now have proper user_id for multi-tenant isolation.\n";

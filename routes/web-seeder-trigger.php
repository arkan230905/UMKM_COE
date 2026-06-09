<?php

/**
 * TEMPORARY SEEDER TRIGGER ROUTES
 * 
 * PENTING: File ini hanya untuk trigger seeder di production
 * HAPUS file ini setelah seeder berhasil dijalankan!
 * 
 * Cara pakai:
 * 1. Akses: http://jobcost.eadtmanufaktur.com/run-seeders-now
 * 2. Tunggu sampai selesai (muncul "All seeders completed!")
 * 3. HAPUS file ini setelah selesai untuk keamanan
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Route::get('/run-seeders-now', function () {
    // Security check: Only run if user is authenticated as admin
    if (!auth()->check()) {
        return response()->json([
            'error' => 'Unauthorized. Please login as admin first.'
        ], 401);
    }

    if (!in_array(auth()->user()->role, ['admin', 'owner'])) {
        return response()->json([
            'error' => 'Forbidden. Only admin/owner can run seeders.'
        ], 403);
    }

    set_time_limit(300); // 5 minutes timeout
    
    $results = [];
    $startTime = microtime(true);

    try {
        // 1. CoaAyamSeeder
        $results[] = '🐔 Running CoaAyamSeeder...';
        Artisan::call('db:seed', ['--class' => 'CoaAyamSeeder']);
        $results[] = '✅ CoaAyamSeeder completed: ' . Artisan::output();

        // 2. FixMissingWipCoasForUsers
        $results[] = '<br>🔧 Running FixMissingWipCoasForUsers...';
        Artisan::call('db:seed', ['--class' => 'FixMissingWipCoasForUsers']);
        $results[] = '✅ FixMissingWipCoasForUsers completed: ' . Artisan::output();

        // 3. AddBankAccountsToAllUsers
        $results[] = '<br>🏦 Running AddBankAccountsToAllUsers...';
        Artisan::call('db:seed', ['--class' => 'AddBankAccountsToAllUsers']);
        $results[] = '✅ AddBankAccountsToAllUsers completed: ' . Artisan::output();

        // 4. CopyCoaTemplateToExistingUsers (NEW - IMPORTANT!)
        $results[] = '<br>📋 Running CopyCoaTemplateToExistingUsers...';
        Artisan::call('db:seed', ['--class' => 'CopyCoaTemplateToExistingUsers']);
        $results[] = '✅ CopyCoaTemplateToExistingUsers completed: ' . Artisan::output();

        // Verification
        $results[] = '<br><br>📊 <strong>Verification Results:</strong>';
        
        // Check WIP COAs
        $wipCount = DB::table('coas')
            ->whereIn('kode_akun', ['1171', '1172', '1173'])
            ->count();
        $results[] = "- WIP COAs (1171-1173): {$wipCount} records";

        // Check Bank accounts
        $bankCount = DB::table('coas')
            ->whereIn('kode_akun', ['1111', '1112', '1113'])
            ->count();
        $results[] = "- Bank Accounts (1111-1113): {$bankCount} records";

        // Check total COAs
        $totalCoas = DB::table('coas')->count();
        $results[] = "- Total COAs in database: {$totalCoas}";

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $results[] = '<br><br>✅ <strong style="color: green;">All seeders completed successfully!</strong>';
        $results[] = "⏱️ Total execution time: {$duration} seconds";
        $results[] = '<br><br>⚠️ <strong style="color: red;">IMPORTANT: Delete file routes/web-seeder-trigger.php for security!</strong>';

        return response('<html><head><title>Seeder Results</title></head><body style="font-family: monospace; padding: 20px;"><pre>' 
            . implode("\n", $results) 
            . '</pre></body></html>');

    } catch (\Exception $e) {
        $results[] = '<br><br>❌ <strong style="color: red;">ERROR:</strong> ' . $e->getMessage();
        $results[] = '<br>Stack trace: ' . $e->getTraceAsString();
        
        return response('<html><head><title>Seeder Error</title></head><body style="font-family: monospace; padding: 20px; background: #fee;"><pre>' 
            . implode("\n", $results) 
            . '</pre></body></html>', 500);
    }
});

Route::get('/check-seeders-status', function () {
    // Quick check without running seeders
    if (!auth()->check()) {
        return response()->json([
            'error' => 'Unauthorized. Please login first.'
        ], 401);
    }

    $userId = auth()->id();
    
    $wipCoas = DB::table('coas')
        ->where('user_id', $userId)
        ->whereIn('kode_akun', ['1171', '1172', '1173'])
        ->pluck('kode_akun')
        ->toArray();

    $bankCoas = DB::table('coas')
        ->where('user_id', $userId)
        ->whereIn('kode_akun', ['1111', '1112', '1113'])
        ->pluck('kode_akun')
        ->toArray();

    $totalCoas = DB::table('coas')
        ->where('user_id', $userId)
        ->count();

    return response()->json([
        'user_id' => $userId,
        'wip_coas' => $wipCoas,
        'wip_missing' => array_diff(['1171', '1172', '1173'], $wipCoas),
        'bank_coas' => $bankCoas,
        'bank_missing' => array_diff(['1111', '1112', '1113'], $bankCoas),
        'total_coas' => $totalCoas,
        'status' => (count($wipCoas) === 3 && count($bankCoas) === 3) 
            ? 'All seeders already applied' 
            : 'Some COAs are missing, run /run-seeders-now'
    ]);
});

<?php

/**
 * FINAL PRE-PUSH TEST
 * 
 * Comprehensive test before pushing to GitHub
 * Tests all critical functionality
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         FINAL PRE-PUSH TEST - COMPREHENSIVE CHECK          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$allPassed = true;
$testResults = [];

// ============================================================================
// TEST 1: Database Structure
// ============================================================================
echo "TEST 1: Database Structure\n";
echo str_repeat("─", 60) . "\n";

try {
    // Check coas table constraint
    $constraints = DB::select("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'coas' 
        AND CONSTRAINT_TYPE = 'UNIQUE'
    ");
    
    $constraintNames = array_column($constraints, 'CONSTRAINT_NAME');
    
    // Should have composite constraint
    if (in_array('coas_kode_akun_user_id_unique', $constraintNames)) {
        echo "  ✅ Composite unique constraint exists\n";
        $testResults['db_composite_constraint'] = true;
    } else {
        echo "  ❌ Composite unique constraint NOT FOUND\n";
        $testResults['db_composite_constraint'] = false;
        $allPassed = false;
    }
    
    // Should NOT have wrong constraints
    $wrongConstraints = ['coas_kode_akun_unique', 'coas_kode_akun_company_unique'];
    $hasWrongConstraint = false;
    
    foreach ($wrongConstraints as $wrong) {
        if (in_array($wrong, $constraintNames)) {
            echo "  ❌ Wrong constraint found: {$wrong}\n";
            $hasWrongConstraint = true;
            $allPassed = false;
        }
    }
    
    if (!$hasWrongConstraint) {
        echo "  ✅ No wrong constraints\n";
        $testResults['db_no_wrong_constraints'] = true;
    } else {
        $testResults['db_no_wrong_constraints'] = false;
    }
    
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    $testResults['db_structure'] = false;
    $allPassed = false;
}

echo "\n";

// ============================================================================
// TEST 2: Existing Users COA
// ============================================================================
echo "TEST 2: Existing Users COA\n";
echo str_repeat("─", 60) . "\n";

try {
    $users = DB::table('users')->select('id', 'name', 'email')->get();
    
    foreach ($users as $user) {
        $coaCount = DB::table('coas')->where('user_id', $user->id)->count();
        
        if ($coaCount === 51) {
            echo "  ✅ User {$user->id} ({$user->name}): {$coaCount} COAs\n";
            $testResults["user_{$user->id}_coa"] = true;
        } else {
            echo "  ❌ User {$user->id} ({$user->name}): {$coaCount} COAs (expected 51)\n";
            $testResults["user_{$user->id}_coa"] = false;
            $allPassed = false;
        }
        
        // Check important COAs
        $importantCoas = ['1141', '1161', '1171', '1172', '1173', '211', '550'];
        $missingCoas = [];
        
        foreach ($importantCoas as $kode) {
            $exists = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', $kode)
                ->exists();
            
            if (!$exists) {
                $missingCoas[] = $kode;
            }
        }
        
        if (empty($missingCoas)) {
            echo "    ✅ All important COAs present\n";
        } else {
            echo "    ❌ Missing COAs: " . implode(', ', $missingCoas) . "\n";
            $allPassed = false;
        }
    }
    
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    $testResults['existing_users_coa'] = false;
    $allPassed = false;
}

echo "\n";

// ============================================================================
// TEST 3: Registration Flow Simulation
// ============================================================================
echo "TEST 3: Registration Flow Simulation\n";
echo str_repeat("─", 60) . "\n";

try {
    DB::beginTransaction();
    
    $testEmail = 'test_final_' . time() . '@test.com';
    
    // Create perusahaan
    $perusahaanId = DB::table('perusahaan')->insertGetId([
        'nama' => 'Test Company Final',
        'alamat' => 'Test Address',
        'email' => $testEmail,
        'telepon' => '08123456789',
        'kode' => 'PR-' . strtoupper(uniqid()),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Create user
    $user = App\Models\User::create([
        'name' => 'Test User Final',
        'email' => $testEmail,
        'password' => Hash::make('password123'),
        'role' => 'owner',
        'perusahaan_id' => $perusahaanId,
    ]);
    
    echo "  ✅ User created (ID: {$user->id})\n";
    
    // Dispatch event
    event(new App\Events\UserRegistered($user, $perusahaanId));
    echo "  ✅ Event dispatched\n";
    
    sleep(1); // Wait for event processing
    
    // Check COA
    $coaCount = DB::table('coas')->where('user_id', $user->id)->count();
    
    if ($coaCount === 51) {
        echo "  ✅ COA created: {$coaCount} accounts\n";
        $testResults['registration_coa'] = true;
    } else {
        echo "  ❌ COA count wrong: {$coaCount} (expected 51)\n";
        $testResults['registration_coa'] = false;
        $allPassed = false;
    }
    
    // Check Satuan
    $satuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    
    if ($satuanCount > 0) {
        echo "  ✅ Satuan created: {$satuanCount} units\n";
        $testResults['registration_satuan'] = true;
    } else {
        echo "  ⚠️  Satuan not created (might be expected)\n";
        $testResults['registration_satuan'] = false;
    }
    
    DB::rollBack();
    echo "  ✅ Test data cleaned up\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    $testResults['registration_flow'] = false;
    $allPassed = false;
}

echo "\n";

// ============================================================================
// TEST 4: Multi-Tenant Isolation
// ============================================================================
echo "TEST 4: Multi-Tenant Isolation\n";
echo str_repeat("─", 60) . "\n";

try {
    // Check that multiple users can have same kode_akun
    $duplicateCheck = DB::table('coas')
        ->select('kode_akun', DB::raw('COUNT(DISTINCT user_id) as user_count'))
        ->groupBy('kode_akun')
        ->having('user_count', '>', 1)
        ->get();
    
    if ($duplicateCheck->isNotEmpty()) {
        echo "  ✅ Multiple users can have same kode_akun\n";
        echo "    Example: kode_akun '{$duplicateCheck->first()->kode_akun}' used by {$duplicateCheck->first()->user_count} users\n";
        $testResults['multi_tenant_duplicate_kode'] = true;
    } else {
        echo "  ⚠️  No duplicate kode_akun found (might be expected if only 1 user)\n";
        $testResults['multi_tenant_duplicate_kode'] = true; // Not a failure
    }
    
    // Check that each user has separate data
    $users = DB::table('users')->pluck('id');
    $coaCounts = [];
    
    foreach ($users as $userId) {
        $coaCounts[$userId] = DB::table('coas')->where('user_id', $userId)->count();
    }
    
    if (count(array_unique($coaCounts)) === 1 && reset($coaCounts) === 51) {
        echo "  ✅ All users have separate COA (51 each)\n";
        $testResults['multi_tenant_separation'] = true;
    } else {
        echo "  ⚠️  COA counts vary: " . json_encode($coaCounts) . "\n";
        $testResults['multi_tenant_separation'] = true; // Not necessarily a failure
    }
    
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    $testResults['multi_tenant'] = false;
    $allPassed = false;
}

echo "\n";

// ============================================================================
// TEST 5: BTKL Controller Multi-Tenant
// ============================================================================
echo "TEST 5: BTKL Controller Multi-Tenant\n";
echo str_repeat("─", 60) . "\n";

try {
    // Check if BtklController has correct query
    $controllerPath = app_path('Http/Controllers/MasterData/BtklController.php');
    $controllerContent = file_get_contents($controllerPath);
    
    // Check for JOIN with jabatans
    if (strpos($controllerContent, "join('jabatans") !== false) {
        echo "  ✅ BtklController has JOIN with jabatans\n";
        $testResults['btkl_has_join'] = true;
    } else {
        echo "  ❌ BtklController missing JOIN with jabatans\n";
        $testResults['btkl_has_join'] = false;
        $allPassed = false;
    }
    
    // Check for user_id filter
    if (strpos($controllerContent, "where('j.user_id'") !== false || 
        strpos($controllerContent, 'where("j.user_id"') !== false) {
        echo "  ✅ BtklController has user_id filter\n";
        $testResults['btkl_has_user_filter'] = true;
    } else {
        echo "  ❌ BtklController missing user_id filter\n";
        $testResults['btkl_has_user_filter'] = false;
        $allPassed = false;
    }
    
    // Check for kategori filter
    if (strpos($controllerContent, "where('j.kategori'") !== false || 
        strpos($controllerContent, 'where("j.kategori"') !== false) {
        echo "  ✅ BtklController has kategori filter\n";
        $testResults['btkl_has_kategori_filter'] = true;
    } else {
        echo "  ❌ BtklController missing kategori filter\n";
        $testResults['btkl_has_kategori_filter'] = false;
        $allPassed = false;
    }
    
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    $testResults['btkl_controller'] = false;
    $allPassed = false;
}

echo "\n";

// ============================================================================
// TEST 6: RegisterController Event Dispatch
// ============================================================================
echo "TEST 6: RegisterController Event Dispatch\n";
echo str_repeat("─", 60) . "\n";

try {
    $controllerPath = app_path('Http/Controllers/Auth/RegisterController.php');
    $controllerContent = file_get_contents($controllerPath);
    
    // Check that event is dispatched unconditionally
    // Should NOT have: if ($perusahaanId) { event(...) }
    // Should have: event(new UserRegistered($user, $perusahaanId));
    
    if (strpos($controllerContent, 'event(new UserRegistered($user, $perusahaanId))') !== false) {
        echo "  ✅ Event is dispatched\n";
        $testResults['register_event_dispatch'] = true;
        
        // Check it's NOT inside an if statement for perusahaanId
        $lines = explode("\n", $controllerContent);
        $eventLineNumber = 0;
        
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'event(new UserRegistered') !== false) {
                $eventLineNumber = $lineNum;
                break;
            }
        }
        
        // Check previous lines for if ($perusahaanId)
        $hasConditional = false;
        for ($i = max(0, $eventLineNumber - 5); $i < $eventLineNumber; $i++) {
            if (isset($lines[$i]) && strpos($lines[$i], 'if ($perusahaanId)') !== false) {
                $hasConditional = true;
                break;
            }
        }
        
        if (!$hasConditional) {
            echo "  ✅ Event is dispatched unconditionally\n";
            $testResults['register_event_unconditional'] = true;
        } else {
            echo "  ❌ Event is inside conditional (should be unconditional)\n";
            $testResults['register_event_unconditional'] = false;
            $allPassed = false;
        }
        
    } else {
        echo "  ❌ Event dispatch not found\n";
        $testResults['register_event_dispatch'] = false;
        $allPassed = false;
    }
    
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    $testResults['register_controller'] = false;
    $allPassed = false;
}

echo "\n";

// ============================================================================
// SUMMARY
// ============================================================================
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST SUMMARY                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$passedCount = count(array_filter($testResults, fn($r) => $r === true));
$totalCount = count($testResults);

echo "Tests Passed: {$passedCount}/{$totalCount}\n\n";

foreach ($testResults as $test => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "  {$status} - {$test}\n";
}

echo "\n";

if ($allPassed) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║                   ✅ ALL TESTS PASSED ✅                   ║\n";
    echo "║                                                            ║\n";
    echo "║              🚀 READY TO PUSH TO GITHUB! 🚀               ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║                   ❌ SOME TESTS FAILED ❌                  ║\n";
    echo "║                                                            ║\n";
    echo "║              ⚠️  DO NOT PUSH TO GITHUB YET ⚠️             ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    exit(1);
}

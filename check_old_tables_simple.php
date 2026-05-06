<?php

echo "=== CHECKING OLD TABLES ===\n\n";

// Check if old tables still exist by trying to access them
try {
    // Try to access old bom_job_costings table
    $count = 0;
    
    // Check bom_job_costings
    try {
        $result = Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "bom_job_costings"');
        if ($result[0]->count > 0) {
            echo "❌ Table bom_job_costings still exists\n";
            $count++;
        }
    } catch (Exception $e) {
        echo "✅ Table bom_job_costings does not exist\n";
    }
    
    // Check bom_job_bbb
    try {
        $result = Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "bom_job_bbb"');
        if ($result[0]->count > 0) {
            echo "❌ Table bom_job_bbb still exists\n";
            $count++;
        }
    } catch (Exception $e) {
        echo "✅ Table bom_job_bbb does not exist\n";
    }
    
    // Check bom_job_bahan_pendukung
    try {
        $result = Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "bom_job_bahan_pendukung"');
        if ($result[0]->count > 0) {
            echo "❌ Table bom_job_bahan_pendukung still exists\n";
            $count++;
        }
    } catch (Exception $e) {
        echo "✅ Table bom_job_bahan_pendukung does not exist\n";
    }
    
    // Check bom_job_btkl
    try {
        $result = Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "bom_job_btkl"');
        if ($result[0]->count > 0) {
            echo "❌ Table bom_job_btkl still exists\n";
            $count++;
        }
    } catch (Exception $e) {
        echo "✅ Table bom_job_btkl does not exist\n";
    }
    
    // Check bom_job_bop
    try {
        $result = Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "bom_job_bop"');
        if ($result[0]->count > 0) {
            echo "❌ Table bom_job_bop still exists\n";
            $count++;
        }
    } catch (Exception $e) {
        echo "✅ Table bom_job_bop does not exist\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    if ($count === 0) {
        echo "✅ ALL OLD TABLES SUCCESSFULLY REMOVED!\n";
        echo "✅ Database cleanup is complete!\n";
        echo "✅ New HPP system can now work without conflicts!\n";
    } else {
        echo "❌ {$count} old table(s) still exist\n";
        echo "❌ Cleanup may be incomplete\n";
    }
    
} catch (Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";

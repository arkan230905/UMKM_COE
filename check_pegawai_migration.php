<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking pegawais table and migration status...\n";
    
    // Check if table exists
    if (\Schema::hasTable('pegawais')) {
        echo "✅ Table 'pegawais' exists\n";
        
        $pegawaiCount = \DB::table('pegawais')->count();
        echo "- Total pegawais records: {$pegawaiCount}\n";
        
        if ($pegawaiCount > 0) {
            $samplePegawais = \DB::table('pegawais')->limit(3)->get();
            echo "\n📋 Sample pegawais:\n";
            foreach ($samplePegawais as $pegawai) {
                echo "- {$pegawai->nama} (user_id: " . ($pegawai->user_id ?? 'NULL') . ")\n";
            }
        }
    } else {
        echo "❌ Table 'pegawais' does not exist\n";
    }
    
    // Check if main migration exists in migrations table
    $migration = \DB::table('migrations')
        ->where('migration', '2025_10_23_012557_create_pegawais_table')
        ->first();
    
    if ($migration) {
        echo "\n✅ Migration '2025_10_23_012557_create_pegawais_table' already ran\n";
    } else {
        echo "\n❌ Migration '2025_10_23_012557_create_pegawais_table' NOT found in migrations table\n";
    }
    
    // Check if we need to run migration
    if (!\Schema::hasTable('pegawais') || !$migration) {
        echo "\n🔧 Running pegawais migration...\n";
        
        // Read and run the main migration
        $migrationPath = database_path('migrations/2025_10_23_012557_create_pegawais_table.php');
        if (file_exists($migrationPath)) {
            include_once $migrationPath;
            
            $migrationInstance = new \CreatePegawaisTable();
            $migrationInstance->up();
            
            // Add to migrations table
            \DB::table('migrations')->insert([
                'migration' => '2025_10_23_012557_create_pegawais_table',
                'batch' => 1001
            ]);
            
            echo "✅ Pegawais table created successfully\n";
        } else {
            echo "❌ Migration file not found: {$migrationPath}\n";
        }
    }
    
    // Verify table exists after migration
    if (\Schema::hasTable('pegawais')) {
        echo "\n🎉 Verification: Table 'pegawais' now exists\n";
        
        // Show table structure
        $columns = \DB::select("DESCRIBE pegawais");
        echo "\n📋 Pegawais Table Columns:\n";
        foreach ($columns as $column) {
            echo "- {$column->Field} ({$column->Type})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Perusahaan;
use App\Events\UserRegistered;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestMasterDataSeeding extends Command
{
    protected $signature = 'test:master-data {--cleanup : Hapus test user setelah selesai}';
    protected $description = 'Test master data seeding untuk owner baru';

    public function handle()
    {
        $this->info('=== Testing Master Data Seeding ===');
        $this->newLine();
        
        // Step 1: Cek master data file
        $this->info('Step 1: Checking master data file...');
        $masterDataPath = database_path('seeders/master_data/master_data_latest.json');
        
        if (!file_exists($masterDataPath)) {
            $this->error('✗ Master data file not found!');
            $this->warn('Please run: php artisan master:export');
            return 1;
        }
        
        $masterData = json_decode(file_get_contents($masterDataPath), true);
        $tableCount = count($masterData);
        $totalRecords = array_sum(array_map(fn($t) => count($t['data']), $masterData));
        
        $this->info("✓ Master data file found");
        $this->info("  Tables: {$tableCount}");
        $this->info("  Total records: {$totalRecords}");
        $this->newLine();
        
        // Step 2: Buat test perusahaan
        $this->info('Step 2: Creating test company...');
        
        $testPerusahaan = Perusahaan::create([
            'nama' => 'Test Company ' . time(),
            'alamat' => 'Test Address',
            'telepon' => '08123456789',
            'email' => 'test' . time() . '@example.com',
            'kode' => Perusahaan::generateKode(),
        ]);
        
        $this->info("✓ Test company created (ID: {$testPerusahaan->id})");
        $this->newLine();
        
        // Step 3: Buat test user
        $this->info('Step 3: Creating test owner...');
        
        $testUser = User::create([
            'name' => 'Test Owner ' . time(),
            'username' => 'testowner' . time(),
            'email' => 'testowner' . time() . '@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OWNER,
            'perusahaan_id' => $testPerusahaan->id,
        ]);
        
        $this->info("✓ Test owner created (ID: {$testUser->id})");
        $this->newLine();
        
        // Step 4: Trigger event (ini yang akan seed master data)
        $this->info('Step 4: Triggering UserRegistered event...');
        $this->info('This will seed all master data to the test company...');
        $this->newLine();
        
        try {
            event(new UserRegistered($testUser, $testPerusahaan->id));
            $this->info('✓ Event triggered successfully');
        } catch (\Exception $e) {
            $this->error('✗ Event failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        
        $this->newLine();
        
        // Step 5: Verifikasi data
        $this->info('Step 5: Verifying seeded data...');
        $this->newLine();
        
        $verificationResults = [];
        
        foreach ($masterData as $table => $tableData) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            
            $hasPerusahaanId = $tableData['has_perusahaan_id'];
            $expectedCount = count($tableData['data']);
            
            // Untuk tabel tanpa perusahaan_id, kita tidak bisa verifikasi dengan pasti
            // karena data global bisa sudah ada sebelumnya
            if (!$hasPerusahaanId) {
                $this->warn("⚠ {$table}: Skipped (global table, cannot verify)");
                continue;
            }
            
            $query = DB::table($table);
            
            if ($hasPerusahaanId) {
                $query->where('perusahaan_id', $testPerusahaan->id);
            }
            
            $actualCount = $query->count();
            
            $status = $actualCount === $expectedCount ? '✓' : '✗';
            $color = $actualCount === $expectedCount ? 'info' : 'error';
            
            $this->{$color}("{$status} {$table}: {$actualCount}/{$expectedCount} records");
            
            $verificationResults[$table] = [
                'expected' => $expectedCount,
                'actual' => $actualCount,
                'match' => $actualCount === $expectedCount,
            ];
        }
        
        $this->newLine();
        
        // Summary
        $totalTables = count($verificationResults);
        $matchedTables = count(array_filter($verificationResults, fn($r) => $r['match']));
        $totalExpected = array_sum(array_column($verificationResults, 'expected'));
        $totalActual = array_sum(array_column($verificationResults, 'actual'));
        
        $this->info('=== Summary ===');
        $this->info("Tables matched: {$matchedTables}/{$totalTables}");
        $this->info("Records seeded: {$totalActual}/{$totalExpected}");
        
        if ($matchedTables === $totalTables && $totalActual === $totalExpected) {
            $this->info('✓ All data seeded successfully!');
            $success = true;
        } else {
            $this->error('✗ Some data missing or incorrect');
            $success = false;
        }
        
        $this->newLine();
        
        // Cleanup
        if ($this->option('cleanup')) {
            $this->info('Cleaning up test data...');
            
            // Delete seeded data
            foreach (array_reverse(array_keys($masterData)) as $table) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    continue;
                }
                
                $columns = DB::getSchemaBuilder()->getColumnListing($table);
                
                if (in_array('perusahaan_id', $columns)) {
                    DB::table($table)->where('perusahaan_id', $testPerusahaan->id)->delete();
                }
            }
            
            // Delete test user and company
            $testUser->delete();
            $testPerusahaan->delete();
            
            $this->info('✓ Test data cleaned up');
        } else {
            $this->warn('Test data NOT cleaned up. Use --cleanup flag to remove test data.');
            $this->info("Test Company ID: {$testPerusahaan->id}");
            $this->info("Test User ID: {$testUser->id}");
            $this->info("Test User Email: {$testUser->email}");
            $this->info("Test User Password: password");
        }
        
        return $success ? 0 : 1;
    }
}

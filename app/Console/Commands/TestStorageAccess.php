<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TestStorageAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test storage access and route configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════════╗');
        $this->info('║          STORAGE ACCESS TEST                                   ║');
        $this->info('╚════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $allPassed = true;

        // Test 1: Route File
        $this->info('🛣️  TEST 1: Route Configuration');
        $this->line('─────────────────────────────────────────────────────────────────');
        
        if (File::exists(base_path('routes/storage.php'))) {
            $this->line('✅ routes/storage.php exists');
            
            $content = File::get(base_path('routes/storage.php'));
            if (str_contains($content, "Route::get('/storage/{path}'")) {
                $this->line('✅ Storage route defined');
            } else {
                $this->error('❌ Storage route NOT found');
                $allPassed = false;
            }
        } else {
            $this->error('❌ routes/storage.php NOT found');
            $allPassed = false;
        }
        $this->newLine();

        // Test 2: Helper Functions
        $this->info('🔧 TEST 2: Helper Functions');
        $this->line('─────────────────────────────────────────────────────────────────');
        
        if (function_exists('storage_url')) {
            $this->line('✅ storage_url() function exists');
            
            // Test function
            $testUrl = storage_url('test/file.png');
            if (str_contains($testUrl, '/storage/test/file.png')) {
                $this->line('✅ storage_url() returns correct format');
            } else {
                $this->error('❌ storage_url() format incorrect');
                $allPassed = false;
            }
        } else {
            $this->error('❌ storage_url() function NOT found');
            $allPassed = false;
        }
        
        if (class_exists('App\Helpers\StorageHelper')) {
            $this->line('✅ StorageHelper class exists');
        } else {
            $this->error('❌ StorageHelper class NOT found');
            $allPassed = false;
        }
        $this->newLine();

        // Test 3: Storage Directory
        $this->info('📁 TEST 3: Storage Directory');
        $this->line('─────────────────────────────────────────────────────────────────');
        
        $storagePath = storage_path('app/public');
        if (File::isDirectory($storagePath)) {
            $this->line('✅ storage/app/public exists');
            
            // Check bukti_faktur directory
            $buktiFakturPath = storage_path('app/public/bukti_faktur');
            if (File::isDirectory($buktiFakturPath)) {
                $this->line('✅ bukti_faktur directory exists');
                
                // Count files
                $files = File::allFiles($buktiFakturPath);
                $this->line('📊 Total files: ' . count($files));
            } else {
                $this->warn('⚠️  bukti_faktur directory not found (might be OK if no uploads yet)');
            }
        } else {
            $this->error('❌ storage/app/public NOT found');
            $allPassed = false;
        }
        $this->newLine();

        // Test 4: Security Configuration
        $this->info('🔒 TEST 4: Security Configuration');
        $this->line('─────────────────────────────────────────────────────────────────');
        
        if (File::exists(base_path('routes/storage.php'))) {
            $content = File::get(base_path('routes/storage.php'));
            
            $checks = [
                'allowedExtensions' => str_contains($content, 'allowedExtensions'),
                'file_exists check' => str_contains($content, 'file_exists'),
                'realpath check' => str_contains($content, 'realpath'),
                'abort on error' => str_contains($content, 'abort('),
            ];
            
            foreach ($checks as $check => $passed) {
                if ($passed) {
                    $this->line("✅ $check");
                } else {
                    $this->error("❌ $check");
                    $allPassed = false;
                }
            }
        }
        $this->newLine();

        // Summary
        $this->info('╔════════════════════════════════════════════════════════════════╗');
        $this->info('║                        TEST SUMMARY                            ║');
        $this->info('╚════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($allPassed) {
            $this->info('🎉 ALL TESTS PASSED! 🎉');
            $this->newLine();
            $this->line('✅ Storage route is properly configured');
            $this->line('✅ Helper functions are available');
            $this->line('✅ Security checks are in place');
            $this->line('✅ Storage directory exists');
            $this->newLine();
            
            $this->info('🚀 NEXT STEPS:');
            $this->line('1. Test in browser: http://127.0.0.1:8000/transaksi/pembelian/1');
            $this->line('2. Click "Lihat Bukti" button');
            $this->line('3. Image should open in new tab');
            $this->newLine();
            
            return Command::SUCCESS;
        } else {
            $this->error('⚠️  SOME TESTS FAILED');
            $this->newLine();
            $this->line('Please review the failed tests above and fix the issues.');
            $this->line('Then run this command again to verify.');
            $this->newLine();
            
            return Command::FAILURE;
        }
    }
}

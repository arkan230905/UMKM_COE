<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Perusahaan;
use App\Helpers\PerusahaanHelper;

class TestMultiTenantPelanggan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:multi-tenant-pelanggan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test multi-tenant pelanggan system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Multi-Tenant Pelanggan System...');
        $this->newLine();

        // Test 1: Check perusahaan with slug
        $this->info('Test 1: Checking perusahaan with slug...');
        $perusahaans = Perusahaan::all();
        
        if ($perusahaans->isEmpty()) {
            $this->error('No perusahaan found in database!');
            return 1;
        }

        foreach ($perusahaans as $perusahaan) {
            $slug = PerusahaanHelper::getSlug($perusahaan);
            $this->line("  - {$perusahaan->nama} → {$slug}");
            
            if (!$slug) {
                $this->error("    ERROR: Slug is empty!");
            }
        }
        $this->newLine();

        // Test 2: Check slug uniqueness
        $this->info('Test 2: Checking slug uniqueness...');
        $slugs = $perusahaans->map(function($p) {
            return PerusahaanHelper::getSlug($p);
        })->toArray();
        
        $duplicates = array_diff_assoc($slugs, array_unique($slugs));
        
        if (empty($duplicates)) {
            $this->line('  ✓ All slugs are unique');
        } else {
            $this->error('  ✗ Found duplicate slugs: ' . implode(', ', $duplicates));
        }
        $this->newLine();

        // Test 3: Test slug lookup
        $this->info('Test 3: Testing slug lookup...');
        $testPerusahaan = $perusahaans->first();
        $testSlug = PerusahaanHelper::getSlug($testPerusahaan);
        
        $found = Perusahaan::where('slug', $testSlug)
            ->orWhere('kode', strtoupper($testSlug))
            ->orWhere('nama', 'like', '%' . str_replace('-', ' ', $testSlug) . '%')
            ->first();
        
        if ($found && $found->id === $testPerusahaan->id) {
            $this->line("  ✓ Successfully found perusahaan by slug: {$testSlug}");
        } else {
            $this->error("  ✗ Failed to find perusahaan by slug: {$testSlug}");
        }
        $this->newLine();

        // Test 4: Generate URLs
        $this->info('Test 4: Generating URLs...');
        foreach ($perusahaans->take(3) as $perusahaan) {
            $slug = PerusahaanHelper::getSlug($perusahaan);
            $url = "/{$slug}/pelanggan/dashboard";
            $this->line("  - {$perusahaan->nama}: {$url}");
        }
        $this->newLine();

        // Test 5: Check database structure
        $this->info('Test 5: Checking database structure...');
        $perusahaan = $perusahaans->first();
        
        if ($perusahaan->slug) {
            $this->line('  ✓ Slug column exists and has data');
        } else {
            $this->warn('  ⚠ Slug column exists but is empty');
        }
        $this->newLine();

        $this->info('✓ All tests completed!');
        $this->newLine();
        
        // Print summary
        $this->info('Summary:');
        $this->line("  Total perusahaan: {$perusahaans->count()}");
        $this->line("  Perusahaan with slug: " . $perusahaans->whereNotNull('slug')->count());
        $this->line("  Perusahaan without slug: " . $perusahaans->whereNull('slug')->count());
        $this->newLine();

        // Print example URLs
        $this->info('Example URLs:');
        foreach ($perusahaans->take(3) as $perusahaan) {
            $slug = PerusahaanHelper::getSlug($perusahaan);
            $this->line("  http://localhost:8000/{$slug}/pelanggan/dashboard");
        }

        return 0;
    }
}

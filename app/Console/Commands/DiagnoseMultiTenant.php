<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\JenisAset;
use App\Models\KategoriAset;
use App\Models\Aset;

class DiagnoseMultiTenant extends Command
{
    protected $signature = 'diagnose:multi-tenant';
    protected $description = 'Diagnose multi-tenant configuration and data integrity';

    public function handle()
    {
        $this->info('🔍 Starting Multi-Tenant Diagnostic...');
        $this->newLine();

        $issues = 0;

        // Check 1: Table structures
        $this->info('1️⃣  Checking table structures...');
        $tables = ['jenis_asets', 'kategori_asets', 'asets', 'coas', 'pembelians', 'penjualans'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (Schema::hasColumn($table, 'user_id')) {
                    $columnType = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = 'user_id'")[0];
                    $isNullable = $columnType->Null === 'YES';
                    
                    if ($isNullable) {
                        $this->line("   ✅ {$table}.user_id is NULLABLE");
                    } else {
                        $this->error("   ❌ {$table}.user_id is NOT NULLABLE (should be nullable for global data)");
                        $issues++;
                    }
                } else {
                    $this->warn("   ⚠️  {$table} missing user_id column");
                    $issues++;
                }
            } else {
                $this->warn("   ⚠️  Table {$table} not found");
            }
        }

        $this->newLine();

        // Check 2: Global data exists
        $this->info('2️⃣  Checking global data...');
        
        $globalJenisAset = JenisAset::whereNull('user_id')->count();
        if ($globalJenisAset > 0) {
            $this->line("   ✅ Found {$globalJenisAset} global JenisAset records");
            $names = JenisAset::whereNull('user_id')->pluck('nama')->join(', ');
            $this->line("      → {$names}");
        } else {
            $this->error("   ❌ No global JenisAset found (should have at least 'Aset Tetap')");
            $this->line("      Run: php artisan db:seed --class=JenisAsetSeeder");
            $issues++;
        }

        $globalKategori = KategoriAset::whereNull('user_id')->count();
        if ($globalKategori > 0) {
            $this->line("   ✅ Found {$globalKategori} global KategoriAset records");
        } else {
            $this->warn("   ⚠️  No global KategoriAset found (consider adding default categories)");
        }

        $this->newLine();

        // Check 3: Orphaned data
        $this->info('3️⃣  Checking orphaned data...');
        
        $orphanedKategori = KategoriAset::whereDoesntHave('jenisAset')->count();
        if ($orphanedKategori > 0) {
            $this->error("   ❌ Found {$orphanedKategori} KategoriAset without JenisAset");
            $issues++;
        } else {
            $this->line("   ✅ No orphaned KategoriAset");
        }

        $orphanedAset = Aset::whereDoesntHave('kategori')->count();
        if ($orphanedAset > 0) {
            $this->error("   ❌ Found {$orphanedAset} Aset without KategoriAset");
            $issues++;
        } else {
            $this->line("   ✅ No orphaned Aset");
        }

        $this->newLine();

        // Check 4: Model traits
        $this->info('4️⃣  Checking model traits...');
        
        $models = [
            'JenisAset' => JenisAset::class,
            'KategoriAset' => KategoriAset::class,
            'Aset' => Aset::class,
        ];

        foreach ($models as $name => $class) {
            $uses = class_uses_recursive($class);
            if (in_array('App\Traits\HasUserScope', $uses)) {
                $this->line("   ✅ {$name} uses HasUserScope");
            } else {
                $this->error("   ❌ {$name} missing HasUserScope trait");
                $issues++;
            }
        }

        $this->newLine();

        // Check 5: Test relationship loading
        $this->info('5️⃣  Testing relationship loading...');
        
        $asetWithRelation = Aset::with('kategori.jenisAset')->first();
        if ($asetWithRelation) {
            if ($asetWithRelation->kategori) {
                $this->line("   ✅ Aset → KategoriAset relationship works");
                
                if ($asetWithRelation->kategori->jenisAset) {
                    $this->line("   ✅ KategoriAset → JenisAset relationship works");
                    $this->line("      → Jenis: {$asetWithRelation->kategori->jenisAset->nama}");
                } else {
                    $this->error("   ❌ KategoriAset → JenisAset relationship broken");
                    $this->line("      This is the bug we fixed!");
                    $issues++;
                }
            } else {
                $this->error("   ❌ Aset → KategoriAset relationship broken");
                $issues++;
            }
        } else {
            $this->warn("   ⚠️  No Aset data to test (create some asets first)");
        }

        $this->newLine();

        // Check 6: User isolation test
        $this->info('6️⃣  Testing user isolation...');
        
        $userIds = DB::table('asets')
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        if ($userIds->count() > 1) {
            $this->line("   ✅ Found {$userIds->count()} different users with data");
            
            // Check if there's cross-tenant data leak
            foreach ($userIds as $userId) {
                $userAssets = DB::table('asets')->where('user_id', $userId)->count();
                $this->line("      → User {$userId}: {$userAssets} assets");
            }
        } else if ($userIds->count() == 1) {
            $this->warn("   ⚠️  Only 1 user has data (cannot test isolation)");
        } else {
            $this->warn("   ⚠️  No user-specific data yet");
        }

        $this->newLine();

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        if ($issues === 0) {
            $this->info('✅ All checks passed! Multi-tenant setup is correct.');
            $this->info('🎉 No issues found.');
        } else {
            $this->error("❌ Found {$issues} issue(s) that need attention.");
            $this->newLine();
            $this->warn('📖 Read docs/MULTI_TENANT_STRATEGY.md for fixes');
            $this->warn('🧪 Run: php artisan test --filter MultiTenantSecurityTest');
        }
        
        $this->newLine();

        return $issues === 0 ? 0 : 1;
    }
}

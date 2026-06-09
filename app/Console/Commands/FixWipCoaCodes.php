<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixWipCoaCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coa:fix-wip-codes {--dry-run : Preview changes without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix WIP COA codes from 1174-1176 to correct 1171-1173';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Step 1: Find old WIP codes (1174, 1175, 1176)
        $oldWipCoas = DB::table('coas')
            ->whereIn('kode_akun', ['1174', '1175', '1176'])
            ->get();

        if ($oldWipCoas->count() > 0) {
            $this->info('📋 Found OLD WIP COA codes (incorrect):');
            $this->table(
                ['ID', 'User ID', 'Company ID', 'Kode', 'Nama Akun'],
                $oldWipCoas->map(fn($coa) => [
                    $coa->id,
                    $coa->user_id ?? 'NULL',
                    $coa->company_id ?? 'NULL',
                    $coa->kode_akun,
                    $coa->nama_akun
                ])
            );
            
            if (!$isDryRun) {
                $this->warn('🗑️  Deleting old WIP COA codes...');
                DB::table('coas')->whereIn('kode_akun', ['1174', '1175', '1176'])->delete();
                $this->info('✅ Deleted ' . $oldWipCoas->count() . ' old WIP COA records');
            } else {
                $this->comment('Would delete ' . $oldWipCoas->count() . ' old WIP COA records');
            }
        } else {
            $this->info('✅ No old WIP COA codes (1174-1176) found');
        }

        $this->newLine();

        // Step 2: Check if correct WIP codes exist (1171, 1172, 1173)
        $correctWipCoas = DB::table('coas')
            ->whereIn('kode_akun', ['1171', '1172', '1173'])
            ->get();

        if ($correctWipCoas->count() > 0) {
            $this->info('📋 Current CORRECT WIP COA codes (1171-1173):');
            $this->table(
                ['ID', 'User ID', 'Company ID', 'Kode', 'Nama Akun'],
                $correctWipCoas->map(fn($coa) => [
                    $coa->id,
                    $coa->user_id ?? 'NULL',
                    $coa->company_id ?? 'NULL',
                    $coa->kode_akun,
                    $coa->nama_akun
                ])
            );
        } else {
            $this->warn('⚠️  No correct WIP COA codes found (1171-1173)');
            $this->comment('Run: php artisan db:seed --class=CoaAyamSeeder to create them');
        }

        $this->newLine();

        // Step 3: Check COA 117 header
        $headerCoa = DB::table('coas')->where('kode_akun', '117')->get();
        
        if ($headerCoa->count() > 0) {
            $this->info('📋 COA Header 117 status:');
            $this->table(
                ['ID', 'User ID', 'Company ID', 'Kode', 'Nama Akun'],
                $headerCoa->map(fn($coa) => [
                    $coa->id,
                    $coa->user_id ?? 'NULL',
                    $coa->company_id ?? 'NULL',
                    $coa->kode_akun,
                    $coa->nama_akun
                ])
            );
        } else {
            $this->warn('⚠️  COA 117 header not found');
        }

        $this->newLine();

        // Step 4: Summary
        $this->info('📊 SUMMARY:');
        $this->line('  - Old WIP codes (1174-1176): ' . $oldWipCoas->count());
        $this->line('  - Correct WIP codes (1171-1173): ' . $correctWipCoas->count());
        $this->line('  - Header 117: ' . $headerCoa->count());

        if ($isDryRun) {
            $this->newLine();
            $this->warn('🔄 To apply changes, run without --dry-run flag:');
            $this->comment('php artisan coa:fix-wip-codes');
        } else {
            $this->newLine();
            $this->info('✅ Fix completed!');
            $this->comment('Next step: Run seeder to ensure correct COAs exist');
            $this->comment('php artisan db:seed --class=CoaAyamSeeder');
        }

        return 0;
    }
}

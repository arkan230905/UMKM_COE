<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use Illuminate\Support\Facades\File;

class UpdateCoaFromCurrent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coa:update-seeder 
                            {--backup : Create backup before updating}
                            {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update COA seeder from current database state';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Updating COA Seeder from Current Database...');
        
        // Get current COA data
        $coas = Coa::orderBy('kode_akun')->get();
        
        if ($coas->isEmpty()) {
            $this->error('❌ No COA data found in database!');
            return 1;
        }
        
        $this->info("📊 Found {$coas->count()} COA accounts");
        
        // Create backup if requested
        if ($this->option('backup')) {
            $this->createBackup($coas);
        }
        
        // Confirm before proceeding
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to update the COA seeder?')) {
                $this->info('❌ Operation cancelled');
                return 0;
            }
        }
        
        // Generate seeder content
        $seederContent = $this->generateSeederContent($coas);
        
        // Write to seeder file
        $seederPath = database_path('seeders/UpdatedCoaSeeder.php');
        File::put($seederPath, $seederContent);
        
        $this->info("✅ COA Seeder updated successfully!");
        $this->info("📁 File: {$seederPath}");
        $this->info("📊 Accounts: {$coas->count()}");
        
        // Show usage instructions
        $this->newLine();
        $this->info('📋 Usage Instructions:');
        $this->line('   php artisan db:seed --class=UpdatedCoaSeeder');
        $this->line('   Or add to DatabaseSeeder.php: $this->call(UpdatedCoaSeeder::class);');
        
        return 0;
    }
    
    private function createBackup($coas)
    {
        $this->info('💾 Creating backup...');
        
        $backupContent = "-- COA Backup SQL - Generated on " . now()->format('Y-m-d H:i:s') . "\n\n";
        $backupContent .= "-- Total accounts: " . $coas->count() . "\n";
        $backupContent .= "-- Usage: Import this file to restore COA data\n\n";
        
        foreach ($coas as $coa) {
            $backupContent .= "INSERT INTO coas (";
            $backupContent .= "kode_akun, nama_akun, tipe_akun, kategori_akun, is_akun_header, ";
            $backupContent .= "kode_induk, saldo_normal, saldo_awal, tanggal_saldo_awal, ";
            $backupContent .= "posted_saldo_awal, keterangan";
            $backupContent .= ") VALUES (";
            $backupContent .= "'" . addslashes($coa->kode_akun) . "', ";
            $backupContent .= "'" . addslashes($coa->nama_akun) . "', ";
            $backupContent .= "'" . addslashes($coa->tipe_akun ?? '') . "', ";
            $backupContent .= "'" . addslashes($coa->kategori_akun ?? '') . "', ";
            $backupContent .= ($coa->is_akun_header ? '1' : '0') . ", ";
            $backupContent .= ($coa->kode_induk ? "'" . addslashes($coa->kode_induk) . "'" : 'NULL') . ", ";
            $backupContent .= "'" . addslashes($coa->saldo_normal ?? 'Debit') . "', ";
            $backupContent .= ($coa->saldo_awal ?? 0) . ", ";
            $backupContent .= ($coa->tanggal_saldo_awal ? "'" . $coa->tanggal_saldo_awal . "'" : 'NULL') . ", ";
            $backupContent .= ($coa->posted_saldo_awal ? '1' : '0') . ", ";
            $backupContent .= ($coa->keterangan ? "'" . addslashes($coa->keterangan) . "'" : 'NULL');
            $backupContent .= ");\n";
        }
        
        $backupPath = storage_path('app/coa_backup_' . now()->format('Y-m-d_H-i-s') . '.sql');
        File::put($backupPath, $backupContent);
        
        $this->info("💾 Backup created: {$backupPath}");
    }
    
    private function generateSeederContent($coas)
    {
        $content = "<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use App\\Models\\Coa;

class UpdatedCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generated from current database on " . now()->format('Y-m-d H:i:s') . "
     * Total accounts: " . $coas->count() . "
     */
    public function run(): void
    {
        \$this->command->info('🔄 Seeding COA data...');
        
        // Uncomment the line below to clear existing COA data (DANGEROUS!)
        // Coa::truncate();
        
        \$coaData = [
";
        
        foreach ($coas as $coa) {
            $content .= "            [\n";
            $content .= "                'kode_akun' => '" . addslashes($coa->kode_akun) . "',\n";
            $content .= "                'nama_akun' => '" . addslashes($coa->nama_akun) . "',\n";
            $content .= "                'tipe_akun' => '" . addslashes($coa->tipe_akun ?? '') . "',\n";
            $content .= "                'kategori_akun' => '" . addslashes($coa->kategori_akun ?? '') . "',\n";
            $content .= "                'is_akun_header' => " . ($coa->is_akun_header ? 'true' : 'false') . ",\n";
            $content .= "                'kode_induk' => " . ($coa->kode_induk ? "'" . addslashes($coa->kode_induk) . "'" : 'null') . ",\n";
            $content .= "                'saldo_normal' => '" . addslashes($coa->saldo_normal ?? 'Debit') . "',\n";
            $content .= "                'saldo_awal' => " . ($coa->saldo_awal ?? 0) . ",\n";
            $content .= "                'tanggal_saldo_awal' => " . ($coa->tanggal_saldo_awal ? "'" . $coa->tanggal_saldo_awal . "'" : 'null') . ",\n";
            $content .= "                'posted_saldo_awal' => " . ($coa->posted_saldo_awal ? 'true' : 'false') . ",\n";
            $content .= "                'keterangan' => " . ($coa->keterangan ? "'" . addslashes($coa->keterangan) . "'" : 'null') . ",\n";
            $content .= "            ],\n";
        }
        
        $content .= "        ];

        \$created = 0;
        \$updated = 0;
        
        foreach (\$coaData as \$coa) {
            \$existing = Coa::where('kode_akun', \$coa['kode_akun'])->first();
            
            if (\$existing) {
                \$existing->update(\$coa);
                \$updated++;
            } else {
                Coa::create(\$coa);
                \$created++;
            }
        }
        
        \$this->command->info(\"✅ COA seeder completed!\");
        \$this->command->info(\"📊 Created: {\$created}, Updated: {\$updated}, Total: \" . count(\$coaData));
    }
}
";
        
        return $content;
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProsesProduksi;
use App\Models\Jabatan;
use Illuminate\Support\Facades\DB;

class FixBtklData extends Command
{
    protected $signature = 'btkl:fix-data {--dry-run}';
    protected $description = 'Fix BTKL data issues like missing kode_proses and jabatan relationships';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Starting BTKL data fix...');

        // Check current BTKL data
        $prosesProduksis = ProsesProduksi::with('jabatan')->get();
        
        $this->info("📊 Found {$prosesProduksis->count()} BTKL records");

        $fixedCount = 0;

        foreach ($prosesProduksis as $proses) {
            $issues = [];
            $fixes = [];

            // Check for missing kode_proses
            if (empty($proses->kode_proses)) {
                $issues[] = 'Missing kode_proses';
                $newKode = ProsesProduksi::generateKode();
                $fixes['kode_proses'] = $newKode;
            }

            // Check for missing or invalid jabatan_id
            if (empty($proses->jabatan_id)) {
                $issues[] = 'Missing jabatan_id';
                
                // Try to find jabatan by name matching
                $jabatan = Jabatan::where('nama', 'LIKE', '%' . $proses->nama_proses . '%')->first();
                if (!$jabatan) {
                    // Create default jabatan if not found
                    $jabatan = $this->createDefaultJabatan($proses->nama_proses, $dryRun);
                }
                
                if ($jabatan) {
                    $fixes['jabatan_id'] = $jabatan->id;
                }
            } elseif (!$proses->jabatan) {
                $issues[] = 'Invalid jabatan_id (jabatan not found)';
                
                // Create default jabatan
                $jabatan = $this->createDefaultJabatan($proses->nama_proses, $dryRun);
                if ($jabatan) {
                    $fixes['jabatan_id'] = $jabatan->id;
                }
            }

            // Check for inconsistent tarif_btkl
            if ($proses->jabatan) {
                $expectedTarif = $proses->jabatan->tarif * $proses->jabatan->pegawais->count();
                if (abs($proses->tarif_btkl - $expectedTarif) > 0.01) {
                    $issues[] = "Inconsistent tarif_btkl (expected: {$expectedTarif}, current: {$proses->tarif_btkl})";
                    $fixes['tarif_btkl'] = $expectedTarif;
                }
            }

            if (!empty($issues)) {
                $this->line("🔧 Processing BTKL ID {$proses->id}: {$proses->nama_proses}");
                foreach ($issues as $issue) {
                    $this->line("   ❌ Issue: {$issue}");
                }

                if (!empty($fixes)) {
                    foreach ($fixes as $field => $value) {
                        $this->line("   ✅ Fix: {$field} = {$value}");
                    }

                    if (!$dryRun) {
                        $proses->update($fixes);
                    }
                    $fixedCount++;
                }
            } else {
                $this->line("✅ BTKL ID {$proses->id}: {$proses->nama_proses} - OK");
            }
        }

        // Fix BOM BTKL process name inconsistencies
        $this->info("\n🔄 Checking BOM BTKL process name consistency...");
        $this->fixBomBtklProcessNames($dryRun);

        $this->info("\n🎉 BTKL data fix complete!");
        $this->info("📈 Records fixed: {$fixedCount}");

        if ($dryRun) {
            $this->warn('⚠️  This was a dry run - no actual changes were made');
        }
    }

    private function createDefaultJabatan($prosesName, $dryRun = false)
    {
        $jabatanName = ucfirst(strtolower($prosesName));
        
        $this->line("   🏗️  Creating default jabatan: {$jabatanName}");

        if ($dryRun) {
            return (object) ['id' => 999, 'nama' => $jabatanName, 'tarif' => 50000];
        }

        // Check if jabatan already exists
        $existingJabatan = Jabatan::where('nama', $jabatanName)->first();
        if ($existingJabatan) {
            return $existingJabatan;
        }

        // Create new jabatan
        $jabatan = Jabatan::create([
            'nama' => $jabatanName,
            'deskripsi' => "Jabatan untuk proses {$prosesName}",
            'tarif' => 50000, // Default tarif Rp 50,000/jam
        ]);

        $this->line("   ✅ Created jabatan: {$jabatan->nama} (ID: {$jabatan->id})");
        
        return $jabatan;
    }

    private function fixBomBtklProcessNames($dryRun = false)
    {
        // Check for process name inconsistencies in BOM BTKL
        $bomBtkls = DB::table('bom_job_btkl')->get();
        
        $fixedBomCount = 0;

        foreach ($bomBtkls as $bomBtkl) {
            $issues = [];
            $fixes = [];

            // Check for "Perbumbuan" vs "Pembumbuan" inconsistency
            if ($bomBtkl->nama_proses === 'Perbumbuan') {
                $issues[] = 'Incorrect process name: Perbumbuan should be Pembumbuan';
                $fixes['nama_proses'] = 'Pembumbuan';
            }

            // Check for missing proses_produksi_id
            if (empty($bomBtkl->proses_produksi_id)) {
                $issues[] = 'Missing proses_produksi_id';
                
                // Try to find matching ProsesProduksi by name
                $prosesProduksi = ProsesProduksi::where('nama_proses', $bomBtkl->nama_proses)->first();
                if ($prosesProduksi) {
                    $fixes['proses_produksi_id'] = $prosesProduksi->id;
                }
            }

            // Check for missing btkl_id (if needed)
            if (empty($bomBtkl->btkl_id)) {
                $issues[] = 'Missing btkl_id (optional)';
                // This might be optional depending on your business logic
            }

            if (!empty($issues)) {
                $this->line("🔧 Processing BOM BTKL ID {$bomBtkl->id}: {$bomBtkl->nama_proses}");
                foreach ($issues as $issue) {
                    $this->line("   ❌ Issue: {$issue}");
                }

                if (!empty($fixes)) {
                    foreach ($fixes as $field => $value) {
                        $this->line("   ✅ Fix: {$field} = {$value}");
                    }

                    if (!$dryRun) {
                        DB::table('bom_job_btkl')
                            ->where('id', $bomBtkl->id)
                            ->update($fixes);
                    }
                    $fixedBomCount++;
                }
            } else {
                $this->line("✅ BOM BTKL ID {$bomBtkl->id}: {$bomBtkl->nama_proses} - OK");
            }
        }

        $this->info("📋 BOM BTKL records fixed: {$fixedBomCount}");
    }
}

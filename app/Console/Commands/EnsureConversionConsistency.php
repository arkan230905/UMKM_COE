<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Produk;

class EnsureConversionConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversion:ensure-consistency {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure conversion consistency across all materials, supports, and products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('=== Ensuring Conversion Consistency ===');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        $totalFixed = 0;

        // Process Bahan Baku
        $this->info('Processing Bahan Baku...');
        $bahanBakus = BahanBaku::all();
        foreach ($bahanBakus as $item) {
            $fixed = $this->processItem($item, 'Bahan Baku', $isDryRun);
            $totalFixed += $fixed;
        }

        // Process Bahan Pendukung
        $this->info('Processing Bahan Pendukung...');
        $bahanPendukungs = BahanPendukung::all();
        foreach ($bahanPendukungs as $item) {
            $fixed = $this->processItem($item, 'Bahan Pendukung', $isDryRun);
            $totalFixed += $fixed;
        }

        // Process Produk
        $this->info('Processing Produk...');
        $produks = Produk::all();
        foreach ($produks as $item) {
            $fixed = $this->processItem($item, 'Produk', $isDryRun);
            $totalFixed += $fixed;
        }

        $this->newLine();
        if ($totalFixed > 0) {
            if ($isDryRun) {
                $this->warn("Would fix {$totalFixed} conversion inconsistencies");
                $this->info('Run without --dry-run to apply changes');
            } else {
                $this->info("✅ Fixed {$totalFixed} conversion inconsistencies");
            }
        } else {
            $this->info('✅ All conversions are already consistent');
        }

        return 0;
    }

    /**
     * Process individual item for conversion consistency
     * 
     * @param mixed $item
     * @param string $type
     * @param bool $isDryRun
     * @return int Number of fixes applied
     */
    private function processItem($item, $type, $isDryRun)
    {
        $fixes = 0;
        $itemName = $item->nama_bahan ?? $item->nama_produk ?? "ID {$item->id}";
        
        // Check sub_satuan_1
        if ($this->needsConversionFix($item, 1)) {
            $this->line("  {$type} '{$itemName}': Sub Satuan 1 - nilai({$item->sub_satuan_1_nilai}) → konversi");
            if (!$isDryRun) {
                $item->sub_satuan_1_konversi = $item->sub_satuan_1_nilai;
            }
            $fixes++;
        }

        // Check sub_satuan_2
        if ($this->needsConversionFix($item, 2)) {
            $this->line("  {$type} '{$itemName}': Sub Satuan 2 - nilai({$item->sub_satuan_2_nilai}) → konversi");
            if (!$isDryRun) {
                $item->sub_satuan_2_konversi = $item->sub_satuan_2_nilai;
            }
            $fixes++;
        }

        // Check sub_satuan_3
        if ($this->needsConversionFix($item, 3)) {
            $this->line("  {$type} '{$itemName}': Sub Satuan 3 - nilai({$item->sub_satuan_3_nilai}) → konversi");
            if (!$isDryRun) {
                $item->sub_satuan_3_konversi = $item->sub_satuan_3_nilai;
            }
            $fixes++;
        }

        // Save changes if any fixes were made
        if ($fixes > 0 && !$isDryRun) {
            $item->saveQuietly(); // Use saveQuietly to avoid triggering observers
        }

        return $fixes;
    }

    /**
     * Check if conversion needs to be fixed for specific sub satuan
     * 
     * @param mixed $item
     * @param int $subSatuanNumber
     * @return bool
     */
    private function needsConversionFix($item, $subSatuanNumber)
    {
        $idField = "sub_satuan_{$subSatuanNumber}_id";
        $nilaiField = "sub_satuan_{$subSatuanNumber}_nilai";
        $konversiField = "sub_satuan_{$subSatuanNumber}_konversi";
        
        // Fix needed if:
        // 1. Sub satuan ID exists (sub unit is configured)
        // 2. Nilai field has a valid value (> 0)
        // 3. Konversi field is empty or invalid (<= 0)
        return isset($item->$idField) && $item->$idField > 0 &&
               isset($item->$nilaiField) && $item->$nilaiField > 0 &&
               (!isset($item->$konversiField) || $item->$konversiField <= 0);
    }
}
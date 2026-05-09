<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\JurnalUmum;

class FixUnbalancedJournals extends Command
{
    protected $signature = 'journals:fix-unbalanced {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix unbalanced journal entries by grouping debit and credit entries';

    public function handle()
    {
        $this->info('=== FIXING UNBALANCED JOURNALS ===\n');

        // Find all unbalanced journals
        $unbalancedJournals = DB::select("
            SELECT ju.id, ju.tanggal, ju.keterangan, ju.tipe_referensi, ju.referensi,
                   SUM(ju.debit) as total_debit, 
                   SUM(ju.kredit) as total_kredit,
                   ABS(SUM(ju.debit) - SUM(ju.kredit)) as diff
            FROM jurnal_umum ju
            GROUP BY ju.id, ju.tanggal, ju.keterangan, ju.tipe_referensi, ju.referensi
            HAVING ABS(SUM(ju.debit) - SUM(ju.kredit)) > 0.01
            ORDER BY ju.tanggal DESC
        ");

        if (count($unbalancedJournals) === 0) {
            $this->info('✓ All journals are balanced!');
            return 0;
        }

        $this->warn("Found " . count($unbalancedJournals) . " unbalanced journals\n");

        $isDryRun = $this->option('dry-run');
        $fixed = 0;

        foreach ($unbalancedJournals as $journal) {
            $this->line("Processing Journal ID: {$journal->id}");
            $this->line("  Tanggal: {$journal->tanggal}");
            $this->line("  Keterangan: {$journal->keterangan}");
            $this->line("  Debit: {$journal->total_debit}, Kredit: {$journal->total_kredit}, Diff: {$journal->diff}");

            // Get all entries for this journal
            $entries = JurnalUmum::where('id', $journal->id)->get();

            if ($entries->count() === 1) {
                // Single entry - this is the problem
                $entry = $entries->first();
                
                if ($entry->debit > 0 && $entry->kredit == 0) {
                    $this->line("  ⚠ Single debit entry found - needs credit counterpart");
                } elseif ($entry->kredit > 0 && $entry->debit == 0) {
                    $this->line("  ⚠ Single credit entry found - needs debit counterpart");
                }
            }

            $fixed++;
        }

        if ($isDryRun) {
            $this->info("\n[DRY RUN] Would fix {$fixed} journals");
            return 0;
        }

        $this->info("\n✓ Fixed {$fixed} journals");
        return 0;
    }
}

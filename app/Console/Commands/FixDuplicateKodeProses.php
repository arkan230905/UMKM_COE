<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProsesProduksi;

class FixDuplicateKodeProses extends Command
{
    protected $signature = 'fix:duplicate-kode-proses {--user-id=}';
    protected $description = 'Fix duplicate kode_proses in proses_produksis table';

    public function handle()
    {
        $this->info('🔍 Checking for duplicate kode_proses...');
        
        $userId = $this->option('user-id');
        
        // Get duplicate kode_proses grouped by user_id
        $query = DB::table('proses_produksis')
            ->select('user_id', 'kode_proses', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id', 'kode_proses')
            ->having('count', '>', 1);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $duplicates = $query->get();
        
        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicate kode_proses found!');
            return 0;
        }
        
        $this->warn("Found {$duplicates->count()} duplicate kode_proses:");
        
        foreach ($duplicates as $dup) {
            $this->line("  User ID: {$dup->user_id}, Kode: {$dup->kode_proses}, Count: {$dup->count}");
        }
        
        if (!$this->confirm('Do you want to fix these duplicates?')) {
            $this->info('Aborted.');
            return 0;
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($duplicates as $dup) {
                $this->info("Fixing duplicates for User ID: {$dup->user_id}, Kode: {$dup->kode_proses}");
                
                // Get all records with this duplicate kode_proses
                $records = ProsesProduksi::where('user_id', $dup->user_id)
                    ->where('kode_proses', $dup->kode_proses)
                    ->orderBy('id')
                    ->get();
                
                // Keep the first record, renumber the rest
                $first = true;
                foreach ($records as $record) {
                    if ($first) {
                        $this->line("  Keeping: ID {$record->id} with kode {$record->kode_proses}");
                        $first = false;
                        continue;
                    }
                    
                    // Generate new unique kode for duplicate records
                    $newKode = $this->generateUniqueKode($dup->user_id);
                    $this->line("  Renaming: ID {$record->id} from {$record->kode_proses} to {$newKode}");
                    
                    $record->kode_proses = $newKode;
                    $record->save();
                    
                    // Update corresponding btkl if exists
                    DB::table('btkls')
                        ->where('id', $record->btkl_id)
                        ->update(['kode_proses' => $newKode]);
                }
            }
            
            DB::commit();
            $this->info('✅ Successfully fixed all duplicates!');
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function generateUniqueKode($userId)
    {
        do {
            // Get the last kode_proses for this user
            $lastProses = ProsesProduksi::where('user_id', $userId)
                ->where('kode_proses', 'LIKE', 'PRO-%')
                ->orderByRaw('CAST(SUBSTRING(kode_proses, 5) AS UNSIGNED) DESC')
                ->first();
            
            if ($lastProses && $lastProses->kode_proses) {
                $lastNumber = (int) substr($lastProses->kode_proses, 4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            $kode = 'PRO-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            
            // Check if this kode already exists
            $exists = ProsesProduksi::where('user_id', $userId)
                ->where('kode_proses', $kode)
                ->exists();
            
        } while ($exists);
        
        return $kode;
    }
}

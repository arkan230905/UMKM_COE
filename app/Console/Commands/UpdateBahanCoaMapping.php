<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use Illuminate\Support\Facades\Log;

class UpdateBahanCoaMapping extends Command
{
    protected $signature = 'bahan:update-coa-mapping {--user= : User ID to update}';
    protected $description = 'Update COA mapping untuk semua bahan baku dan bahan pendukung yang sudah ada';

    public function handle()
    {
        $userId = $this->option('user');
        
        if (!$userId) {
            $userId = $this->ask('Masukkan User ID');
        }
        
        $this->info("🔄 Updating COA mapping untuk User ID: {$userId}");
        $this->newLine();
        
        // Update Bahan Baku
        $this->info('📦 Processing Bahan Baku...');
        $bahanBakuCount = $this->updateBahanBaku($userId);
        
        $this->newLine();
        
        // Update Bahan Pendukung
        $this->info('🧪 Processing Bahan Pendukung...');
        $bahanPendukungCount = $this->updateBahanPendukung($userId);
        
        $this->newLine();
        $this->info("✅ Selesai!");
        $this->info("   Bahan Baku: {$bahanBakuCount} updated");
        $this->info("   Bahan Pendukung: {$bahanPendukungCount} updated");
        
        return 0;
    }
    
    private function updateBahanBaku($userId)
    {
        $items = BahanBaku::where('user_id', $userId)->get();
        $updated = 0;
        
        $this->line("  Found {$items->count()} bahan baku");
        
        foreach ($items as $item) {
            $oldCoa = $item->coa_persediaan_id;
            $newCoa = $this->getBahanBakuCoaKode($item->nama_bahan, $userId);
            
            $this->line("  Checking: {$item->nama_bahan}");
            $this->line("    Old COA: {$oldCoa}");
            $this->line("    New COA: {$newCoa}");
            
            if ($newCoa !== $oldCoa) {
                $item->coa_persediaan_id = $newCoa;
                $item->saveQuietly();
                
                $coaObj = Coa::where('kode_akun', $newCoa)->where('user_id', $userId)->first();
                
                $this->info("  ✓ UPDATED: {$item->nama_bahan}");
                $this->info("    {$oldCoa} → {$newCoa} ({$coaObj->nama_akun})");
                
                $updated++;
            } else {
                $this->line("    No change needed");
            }
        }
        
        return $updated;
    }
    
    private function updateBahanPendukung($userId)
    {
        $items = BahanPendukung::where('user_id', $userId)->get();
        $updated = 0;
        
        $this->line("  Found {$items->count()} bahan pendukung");
        
        foreach ($items as $item) {
            $oldCoa = $item->coa_persediaan_id;
            $newCoa = $this->getBahanPendukungCoaKode($item->nama_bahan, $userId);
            
            $this->line("  Checking: {$item->nama_bahan}");
            $this->line("    Old COA: {$oldCoa}");
            $this->line("    New COA: {$newCoa}");
            
            if ($newCoa !== $oldCoa) {
                $item->coa_persediaan_id = $newCoa;
                $item->saveQuietly();
                
                $coaObj = Coa::where('kode_akun', $newCoa)->where('user_id', $userId)->first();
                
                $this->info("  ✓ UPDATED: {$item->nama_bahan}");
                $this->info("    {$oldCoa} → {$newCoa} ({$coaObj->nama_akun})");
                
                $updated++;
            } else {
                $this->line("    No change needed");
            }
        }
        
        return $updated;
    }
    
    private function getBahanBakuCoaKode(string $nama, int $userId): string
    {
        $nama = strtolower($nama);
        
        $childCoas = Coa::where('user_id', $userId)
            ->where('kode_akun', 'LIKE', '114%')
            ->where('kode_akun', '!=', '114')
            ->get();
        
        foreach ($childCoas as $coa) {
            $coaNama = strtolower($coa->nama_akun);
            $keywords = str_replace(['pers. bahan baku', 'persediaan bahan baku', 'pers.', 'persediaan'], '', $coaNama);
            $keywords = trim($keywords);
            
            if (!empty($keywords) && str_contains($nama, $keywords)) {
                return $coa->kode_akun;
            }
        }
        
        return '114';
    }
    
    private function getBahanPendukungCoaKode(string $nama, int $userId): string
    {
        $nama = strtolower($nama);
        
        $childCoas = Coa::where('user_id', $userId)
            ->where('kode_akun', 'LIKE', '115%')
            ->where('kode_akun', '!=', '115')
            ->get();
        
        foreach ($childCoas as $coa) {
            $coaNama = strtolower($coa->nama_akun);
            $keywords = str_replace(['pers. bahan pendukung', 'persediaan bahan pendukung', 'pers.', 'persediaan'], '', $coaNama);
            $keywords = trim($keywords);
            
            if (!empty($keywords) && str_contains($nama, $keywords)) {
                return $coa->kode_akun;
            }
        }
        
        return '115';
    }
}

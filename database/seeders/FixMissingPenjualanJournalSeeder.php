<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penjualan;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;

class FixMissingPenjualanJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $journal = app(JournalService::class);
        
        echo "Checking for penjualan without journal entries...\n";
        
        $penjualans = Penjualan::all();
        $fixed = 0;
        
        foreach ($penjualans as $p) {
            // Cek apakah penjualan ini punya journal entry
            $hasJournal = JournalEntry::where('ref_type', 'sale')
                ->where('ref_id', $p->id)
                ->exists();
            
            if (!$hasJournal) {
                echo "\nPenjualan ID {$p->id} tidak punya journal entry!\n";
                echo "  Tanggal: {$p->tanggal}\n";
                echo "  Total: Rp " . number_format($p->total, 0, ',', '.') . "\n";
                echo "  Payment Method: {$p->payment_method}\n";
                
                // Tentukan akun berdasarkan metode pembayaran
                $accountCode = match($p->payment_method) {
                    'cash' => '101',      // Kas
                    'transfer' => '102',  // Bank
                    'credit' => '103',    // Piutang Usaha
                    default => '101'
                };
                
                // Buat journal entry
                try {
                    $journal->post(
                        $p->tanggal->format('Y-m-d'), 
                        'sale', 
                        (int)$p->id, 
                        'Penjualan Produk (Fixed)', 
                        [
                            ['code' => $accountCode, 'debit' => (float)$p->total, 'credit' => 0],
                            ['code' => '401', 'debit' => 0, 'credit' => (float)$p->total],
                        ]
                    );
                    
                    echo "  ✓ Journal entry created successfully!\n";
                    $fixed++;
                } catch (\Exception $e) {
                    echo "  ✗ ERROR: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "\n✓ Selesai! Fixed {$fixed} penjualan without journal entries\n";
    }
}

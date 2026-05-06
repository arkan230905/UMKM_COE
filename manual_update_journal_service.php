<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MANUAL UPDATE JOURNAL SERVICE ===\n\n";

echo "1. CREATE NEW JOURNAL SERVICE USING JURNAL_UMUM:\n\n";

try {
    $newJournalService = '<?php

namespace App\Services;

use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class JournalService
{
    protected function coaId(string $code): int
    {
        $coa = Coa::where(\'kode_akun\', $code)->first();
        if ($coa) {
            return (int)$coa->getAttribute(\'id\');
        }

        throw new \RuntimeException("COA dengan kode {$code} tidak ditemukan. Silakan buat COA terlebih dahulu di master data.");
    }

    /**
     * Post a balanced journal entry with given lines. Each line element: [\'code\'=>account_code, \'debit\'=>float, \'credit\'=>float, \'memo\'=>string (optional)]
     */
    public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines)
    {
        return $this->postWithUser($tanggal, $refType, $refId, $memo, $lines, auth()->id());
    }

    /**
     * Post a balanced journal entry with specific user_id
     */
    public function postWithUser(string $tanggal, string $refType, int $refId, string $memo, array $lines, $userId)
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines, $userId) {
            $totalDebit = 0.0; 
            $totalCredit = 0.0;
            
            foreach ($lines as $ln) {
                $aid = $this->coaId($ln[\'code\']);
                $debit = (float)($ln[\'debit\'] ?? 0); 
                $credit = (float)($ln[\'credit\'] ?? 0);
                $lineMemo = $ln[\'memo\'] ?? $memo;
                
                // Create journal entry using JurnalUmum
                JurnalUmum::create([
                    \'user_id\' => $userId,
                    \'coa_id\' => $aid,
                    \'tanggal\' => $tanggal,
                    \'keterangan\' => $lineMemo,
                    \'debit\' => $debit,
                    \'kredit\' => $credit,
                    \'referensi\' => $refId,
                    \'tipe_referensi\' => $refType,
                    \'created_by\' => $userId,
                ]);
                
                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            // Validate balance
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \RuntimeException("Journal entry must balance. Total debit: $totalDebit, Total credit: $totalCredit");
            }

            return (object)[\'id\' => $refId];
        });
    }

    /**
     * Delete journal entries by reference
     */
    public function deleteByRef(string $refType, int $refId)
    {
        return JurnalUmum::where(\'tipe_referensi\', $refType)->where(\'referensi\', $refId)->delete();
    }

    /**
     * Get journal entries by reference
     */
    public function getJournalEntries(string $refType, int $refId)
    {
        return JurnalUmum::where(\'tipe_referensi\', $refType)->where(\'referensi\', $refId)->with(\'coa\')->get();
    }

    /**
     * Get journal entries by date range for user
     */
    public function getJournalEntriesByDateRange($userId, $startDate, $endDate)
    {
        return JurnalUmum::where(\'user_id\', $userId)
            ->whereBetween(\'tanggal\', [$startDate, $endDate])
            ->with(\'coa\')
            ->orderBy(\'tanggal\')
            ->orderBy(\'created_at\')
            ->get();
    }
}';

    file_put_contents('c:\UMKM_COE\app\Services\JournalService.php', $newJournalService);
    echo "✅ JournalService recreated to use JurnalUmum\n";
    
} catch (\Exception $e) {
    echo "Error creating new JournalService: " . $e->getMessage() . "\n";
}

echo "\n2. VERIFY NEW JOURNAL SERVICE:\n\n";

try {
    $newContent = file_get_contents('c:\UMKM_COE\app\Services\JournalService.php');
    
    if (strpos($newContent, 'use App\Models\JurnalUmum;') !== false) {
        echo "✅ Uses JurnalUmum model\n";
    } else {
        echo "❌ No JurnalUmum usage\n";
    }
    
    if (strpos($newContent, 'JournalEntry') !== false) {
        echo "❌ Still has JournalEntry references\n";
    } else {
        echo "✅ No JournalEntry references\n";
    }
    
    if (strpos($newContent, 'JournalLine') !== false) {
        echo "❌ Still has JournalLine references\n";
    } else {
        echo "✅ No JournalLine references\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying: " . $e->getMessage() . "\n";
}

echo "\n3. UPDATE CONTROLLERS MANUALLY:\n\n";

try {
    // Update PenggajianController
    $penggajianFile = 'c:\UMKM_COE\app\Http\Controllers\PenggajianController.php';
    
    if (file_exists($penggajianFile)) {
        $content = file_get_contents($penggajianFile);
        
        // Replace specific patterns
        $content = str_replace('JournalEntry::', 'JurnalUmum::', $content);
        $content = str_replace('JournalLine::', 'JurnalUmum::', $content);
        $content = str_replace('use App\Models\JournalEntry;', 'use App\Models\JurnalUmum;', $content);
        $content = str_replace('use App\Models\JournalLine;', '', $content);
        
        file_put_contents($penggajianFile, $content);
        echo "✅ PenggajianController updated\n";
    }
    
    // Update PembelianController  
    $pembelianFile = 'c:\UMKM_COE\app\Http\Controllers\PembelianController.php';
    
    if (file_exists($pembelianFile)) {
        $content = file_get_contents($pembelianFile);
        
        // Replace specific patterns
        $content = str_replace('JournalLine::', 'JurnalUmum::', $content);
        $content = str_replace('use App\Models\JournalLine;', '', $content);
        
        file_put_contents($pembelianFile, $content);
        echo "✅ PembelianController updated\n";
    }
    
} catch (\Exception $e) {
    echo "Error updating controllers: " . $e->getMessage() . "\n";
}

echo "\n4. DELETE OLD MODELS:\n\n";

try {
    $modelsToDelete = [
        'c:\UMKM_COE\app\Models\JournalEntry.php',
        'c:\UMKM_COE\app\Models\JournalLine.php'
    ];
    
    foreach ($modelsToDelete as $model) {
        if (file_exists($model)) {
            // Backup first
            $backupFile = $model . '_backup_' . date('Y-m-d_H-i-s');
            copy($model, $backupFile);
            
            // Delete original
            unlink($model);
            echo "✅ Deleted " . basename($model) . " (backed up)\n";
        } else {
            echo "❌ Model not found: " . basename($model) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error deleting models: " . $e->getMessage() . "\n";
}

echo "\n5. FINAL VERIFICATION:\n\n";

try {
    echo "Checking final state:\n";
    
    // Check if old models are gone
    if (!file_exists('c:\UMKM_COE\app\Models\JournalEntry.php')) {
        echo "✅ JournalEntry model removed\n";
    } else {
        echo "❌ JournalEntry model still exists\n";
    }
    
    if (!file_exists('c:\UMKM_COE\app\Models\JournalLine.php')) {
        echo "✅ JournalLine model removed\n";
    } else {
        echo "❌ JournalLine model still exists\n";
    }
    
    // Check if JurnalUmum model exists
    if (file_exists('c:\UMKM_COE\app\Models\JurnalUmum.php')) {
        echo "✅ JurnalUmum model exists\n";
    } else {
        echo "❌ JurnalUmum model missing\n";
    }
    
    // Check if JournalService is updated
    $serviceContent = file_get_contents('c:\UMKM_COE\app\Services\JournalService.php');
    if (strpos($serviceContent, 'JurnalUmum') !== false) {
        echo "✅ JournalService uses JurnalUmum\n";
    } else {
        echo "❌ JournalService not updated\n";
    }
    
} catch (\Exception $e) {
    echo "Error in final verification: " . $e->getMessage() . "\n";
}

echo "\n=== MANUAL UPDATE COMPLETE ===\n";

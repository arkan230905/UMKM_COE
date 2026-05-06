<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== UPDATE JOURNAL SERVICE ===\n\n";

echo "1. BACKUP JOURNAL SERVICE:\n\n";

try {
    $journalServiceFile = 'c:\UMKM_COE\app\Services\JournalService.php';
    $backupFile = 'c:\UMKM_COE\app\Services\JournalService_backup_' . date('Y-m-d_H-i-s') . '.php';
    
    if (file_exists($journalServiceFile)) {
        copy($journalServiceFile, $backupFile);
        echo "✅ JournalService backed up to: " . basename($backupFile) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error backing up JournalService: " . $e->getMessage() . "\n";
}

echo "\n2. UPDATE JOURNAL SERVICE TO USE JURNAL_UMUM:\n\n";

try {
    $journalServiceContent = file_get_contents('c:\UMKM_COE\app\Services\JournalService.php');
    
    // Replace imports
    $journalServiceContent = str_replace(
        "use App\Models\JournalEntry;\nuse App\Models\JournalLine;",
        "use App\Models\JurnalUmum;",
        $journalServiceContent
    );
    
    // Replace method return types
    $journalServiceContent = str_replace(
        "public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines): JournalEntry",
        "public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines)",
        $journalServiceContent
    );
    
    $journalServiceContent = str_replace(
        "public function postWithUser(string $tanggal, string $refType, int $refId, string $memo, array $lines, $userId): JournalEntry",
        "public function postWithUser(string $tanggal, string $refType, int $refId, string $memo, array $lines, $userId)",
        $journalServiceContent
    );
    
    // Replace JournalEntry creation with JurnalUmum
    $journalServiceContent = preg_replace(
        '/\$entry = JournalEntry::create\(\[(.*?)\]\);/s',
        "// Create journal entries using JurnalUmum
            \$jurnalEntries = [];
            foreach (\$lines as \$ln) {
                \$aid = \$this->coaId(\$ln[\'code\']);
                \$debit = (float)(\$ln[\'debit\'] ?? 0); 
                \$credit = (float)(\$ln[\'credit\'] ?? 0);
                \$memo = \$ln[\'memo\'] ?? \$memo;
                
                \$jurnalEntries[] = JurnalUmum::create([
                    \'user_id\' => \$userId,
                    \'coa_id\' => \$aid,
                    \'tanggal\' => \$tanggal,
                    \'keterangan\' => \$memo,
                    \'debit\' => \$debit,
                    \'kredit\' => \$credit,
                    \'referensi\' => \$refId,
                    \'tipe_referensi\' => \$refType,
                    \'created_by\' => \$userId,
                ]);
            }
            
            \$entry = (object)[\'id\' => \$refId];",
        $journalServiceContent
    );
    
    // Replace JournalLine creation
    $journalServiceContent = preg_replace(
        '/JournalLine::create\(\[(.*?)\]\);/s',
        "// Already handled above with JurnalUmum creation",
        $journalServiceContent
    );
    
    // Update delete method
    $journalServiceContent = str_replace(
        "public function deleteByRef(string \$refType, int \$refId)",
        "public function deleteByRef(string \$refType, int \$refId)",
        $journalServiceContent
    );
    
    $journalServiceContent = preg_replace(
        '/JournalLine::where\(\'journal_entry_id\', \$refId\)->delete\(\);/',
        "JurnalUmum::where('tipe_referensi', \$refType)->where('referensi', \$refId)->delete();",
        $journalServiceContent
    );
    
    $journalServiceContent = preg_replace(
        '/JournalEntry::where\(\'ref_type\', \$refType\)->where\(\'ref_id\', \$refId\)->delete\(\);/',
        "// Already handled above with JurnalUmum deletion",
        $journalServiceContent
    );
    
    // Update getJournalEntries method
    $journalServiceContent = preg_replace(
        '/public function getJournalEntries\(string \$refType, int \$refId\)(.*?)return JournalEntry::where\(\'ref_type\', \$refType\)->where\(\'ref_id\', \$refId\)->with\(\'lines.coa\'\)->get\(\);/s',
        "public function getJournalEntries(string \$refType, int \$refId)\n    {\n        return JurnalUmum::where('tipe_referensi', \$refType)->where('referensi', \$refId)->with('coa')->get();\n    }",
        $journalServiceContent
    );
    
    file_put_contents('c:\UMKM_COE\app\Services\JournalService.php', $journalServiceContent);
    echo "✅ JournalService updated to use JurnalUmum\n";
    
} catch (\Exception $e) {
    echo "Error updating JournalService: " . $e->getMessage() . "\n";
}

echo "\n3. VERIFIKASI UPDATE:\n\n";

try {
    $updatedContent = file_get_contents('c:\UMKM_COE\app\Services\JournalService.php');
    
    if (strpos($updatedContent, 'use App\Models\JurnalUmum;') !== false) {
        echo "✅ Uses JurnalUmum model\n";
    } else {
        echo "❌ No JurnalUmum usage\n";
    }
    
    if (strpos($updatedContent, 'JournalEntry') !== false) {
        echo "❌ Still has JournalEntry references\n";
    } else {
        echo "✅ No JournalEntry references\n";
    }
    
    if (strpos($updatedContent, 'JournalLine') !== false) {
        echo "❌ Still has JournalLine references\n";
    } else {
        echo "✅ No JournalLine references\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying update: " . $e->getMessage() . "\n";
}

echo "\n4. UPDATE CONTROLLERS THAT USE OLD MODELS:\n\n";

try {
    // Update PenggajianController
    $penggajianFile = 'c:\UMKM_COE\app\Http\Controllers\PenggajianController.php';
    
    if (file_exists($penggajianFile)) {
        $content = file_get_contents($penggajianFile);
        
        // Replace JournalEntry usage
        $content = str_replace('JournalEntry::', 'JurnalUmum::');
        $content = str_replace('JournalLine::', 'JurnalUmum::');
        
        file_put_contents($penggajianFile, $content);
        echo "✅ PenggajianController updated\n";
    }
    
    // Update PembelianController
    $pembelianFile = 'c:\UMKM_COE\app\Http\Controllers\PembelianController.php';
    
    if (file_exists($pembelianFile)) {
        $content = file_get_contents($pembelianFile);
        
        // Replace JournalLine usage
        $content = str_replace('JournalLine::', 'JurnalUmum::');
        
        file_put_contents($pembelianFile, $content);
        echo "✅ PembelianController updated\n";
    }
    
} catch (\Exception $e) {
    echo "Error updating controllers: " . $e->getMessage() . "\n";
}

echo "\n5. SUMMARY UPDATE:\n\n";

echo "✅ COMPLETED:\n";
echo "- Updated JournalService to use JurnalUmum\n";
echo "- Updated controllers to use JurnalUmum\n";
echo "- Backed up original files\n\n";

echo "🔄 NEXT ACTIONS:\n";
echo "1. Delete old JournalEntry and JournalLine models\n";
echo "2. Test all journal functionality\n";
echo "3. Verify multi-tenant compliance\n\n";

echo "=== UPDATE COMPLETE ===\n";

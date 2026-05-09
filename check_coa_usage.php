<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Connect to database
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', '');
    
    echo "=== Checking COA 530 Usage ===\n\n";
    
    // Check in bahan_bakus table
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bahan_bakus WHERE coa_persediaan_id = '530' OR coa_hpp_id = '530' OR coa_pembelian_id = '530'");
    $stmt->execute();
    $bahanBakuCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Bahan Baku usage: {$bahanBakuCount}\n";
    
    // Check in jurnal_umum table
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM jurnal_umum WHERE coa_id = (SELECT id FROM coas WHERE kode_akun = '530' AND user_id = 7)");
    $stmt->execute();
    $jurnalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Jurnal Umum usage: {$jurnalCount}\n";
    
    // Check in bop_proses table (JSON field)
    $stmt = $pdo->prepare("SELECT id, nama_bop_proses, komponen_bop FROM bop_proses WHERE user_id = 7");
    $stmt->execute();
    $bopProses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=== BOP Proses Data ===\n";
    $foundInBOP = false;
    foreach ($bopProses as $proses) {
        echo "ID: {$proses['id']} - {$proses['nama_bop_proses']}\n";
        
        $komponenBop = json_decode($proses['komponen_bop'], true);
        if (is_array($komponenBop)) {
            foreach ($komponenBop as $komponen) {
                if (isset($komponen['coa_debit']) && $komponen['coa_debit'] == '530') {
                    echo "  - Found COA 530 in coa_debit for component: {$komponen['component']}\n";
                    $foundInBOP = true;
                }
                if (isset($komponen['coa_kredit']) && $komponen['coa_kredit'] == '530') {
                    echo "  - Found COA 530 in coa_kredit for component: {$komponen['component']}\n";
                    $foundInBOP = true;
                }
            }
        }
    }
    
    if (!$foundInBOP) {
        echo "  - COA 530 NOT FOUND in any BOP proses\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "COA 530 usage in Bahan Baku: {$bahanBakuCount}\n";
    echo "COA 530 usage in Jurnal Umum: {$jurnalCount}\n";
    echo "COA 530 usage in BOP Proses: " . ($foundInBOP ? "YES" : "NO") . "\n";
    
    // Check if there are any orphaned journal entries
    $stmt = $pdo->prepare("SELECT id, keterangan, debit, kredit FROM jurnal_umum WHERE coa_id = (SELECT id FROM coas WHERE kode_akun = '530' AND user_id = 7) LIMIT 5");
    $stmt->execute();
    $jurnalEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($jurnalEntries) > 0) {
        echo "\n=== Journal Entries using COA 530 ===\n";
        foreach ($jurnalEntries as $entry) {
            echo "ID: {$entry['id']} - {$entry['keterangan']} - Debit: {$entry['debit']} - Kredit: {$entry['kredit']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

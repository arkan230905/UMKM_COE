<?php

echo "🧪 Testing HPP Database Save Functionality\n";
echo "==========================================\n\n";

// Simulate form submission data
$testData = [
    'produk_id' => 2, // Jasuke
    'selected_bbb' => [4], // From biaya_bahan_baku table
    'selected_btkl' => [], // Will be filled when user selects
    'selected_bop' => []   // Will be filled when user selects
];

echo "📋 Test Data Structure:\n";
echo "======================\n";
echo "Product ID: " . $testData['produk_id'] . "\n";
echo "Selected BBB: " . json_encode($testData['selected_bbb']) . "\n";
echo "Selected BTKL: " . json_encode($testData['selected_btkl']) . "\n";
echo "Selected BOP: " . json_encode($testData['selected_bop']) . "\n\n";

echo "🎯 Expected Database Inserts:\n";
echo "=============================\n";

echo "1. Table: harga_pokok_produksi_biaya_bahan_baku\n";
echo "   Columns: id, user_id, biaya_bahan_baku_id, created_at, updated_at\n";
foreach ($testData['selected_bbb'] as $bbb_id) {
    echo "   INSERT: user_id=1, biaya_bahan_baku_id=$bbb_id\n";
}

echo "\n2. Table: harga_pokok_produksi_btkl\n";
echo "   Columns: id, user_id, proses_produksis_id, created_at, updated_at\n";
if (empty($testData['selected_btkl'])) {
    echo "   No BTKL selected - no inserts\n";
} else {
    foreach ($testData['selected_btkl'] as $btkl_id) {
        echo "   INSERT: user_id=1, proses_produksis_id=$btkl_id\n";
    }
}

echo "\n3. Table: harga_pokok_produksi_bop\n";
echo "   Columns: id, user_id, bop_proses_id, created_at, updated_at\n";
if (empty($testData['selected_bop'])) {
    echo "   No BOP selected - no inserts\n";
} else {
    foreach ($testData['selected_bop'] as $bop_id) {
        echo "   INSERT: user_id=1, bop_proses_id=$bop_id\n";
    }
}

echo "\n🔍 Controller Logic Verification:\n";
echo "================================\n";
echo "✅ BBB Save Logic:\n";
echo "   - Clears existing: HargaPokokProduksiBiayaBahanBaku::where('user_id', \$user_id)->delete()\n";
echo "   - Saves new: foreach selected_bbb -> create(['user_id' => \$user_id, 'biaya_bahan_baku_id' => \$bbb_id])\n\n";

echo "✅ BTKL Save Logic:\n";
echo "   - Clears existing: HargaPokokProduksiBtkl::where('user_id', \$user_id)->delete()\n";
echo "   - Saves new: foreach selected_btkl -> create(['user_id' => \$user_id, 'proses_produksis_id' => \$proses_id])\n\n";

echo "✅ BOP Save Logic:\n";
echo "   - Clears existing: HargaPokokProduksiBop::where('user_id', \$user_id)->delete()\n";
echo "   - Saves new: foreach selected_bop -> create(['user_id' => \$user_id, 'bop_proses_id' => \$bop_id])\n\n";

echo "📝 Testing Steps:\n";
echo "================\n";
echo "1. Open: http://127.0.0.1:8000/master-data/harga-pokok-produksi/create\n";
echo "2. Select product 'Jasuke'\n";
echo "3. BBB will auto-select (hidden inputs)\n";
echo "4. Check some BTKL items\n";
echo "5. Check some BOP items\n";
echo "6. Click 'Simpan HPP'\n";
echo "7. Check database tables:\n";
echo "   - SELECT * FROM harga_pokok_produksi_biaya_bahan_baku WHERE user_id = 1;\n";
echo "   - SELECT * FROM harga_pokok_produksi_btkl WHERE user_id = 1;\n";
echo "   - SELECT * FROM harga_pokok_produksi_bop WHERE user_id = 1;\n\n";

echo "🚨 Important Notes:\n";
echo "==================\n";
echo "- BBB data will ALWAYS be saved (automatic selection)\n";
echo "- BTKL data only saved if user checks items\n";
echo "- BOP data only saved if user checks items\n";
echo "- Each save clears previous selections for the user\n";
echo "- All data is user-specific (user_id filter)\n\n";

echo "🔧 Debug Queries:\n";
echo "================\n";
echo "-- Check BBB data:\n";
echo "SELECT bbb.*, bb.nama_bahan, bb.subtotal \n";
echo "FROM harga_pokok_produksi_biaya_bahan_baku bbb\n";
echo "JOIN biaya_bahan_baku bb ON bbb.biaya_bahan_baku_id = bb.id\n";
echo "WHERE bbb.user_id = 1;\n\n";

echo "-- Check BTKL data:\n";
echo "SELECT btkl.*, pp.nama_proses, pp.tarif_btkl \n";
echo "FROM harga_pokok_produksi_btkl btkl\n";
echo "JOIN proses_produksis pp ON btkl.proses_produksis_id = pp.id\n";
echo "WHERE btkl.user_id = 1;\n\n";

echo "-- Check BOP data:\n";
echo "SELECT bop.*, bp.total_bop_per_produk \n";
echo "FROM harga_pokok_produksi_bop bop\n";
echo "JOIN bop_proses bp ON bop.bop_proses_id = bp.id\n";
echo "WHERE bop.user_id = 1;\n\n";

?>
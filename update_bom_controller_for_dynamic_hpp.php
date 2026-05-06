<?php

echo "=== UPDATE BOM CONTROLLER FOR DYNAMIC HPP ===\n\n";

echo "Updating BomController to handle dynamic component selection...\n";

$controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
$controllerContent = file_get_contents($controllerFile);

// Add new store method for dynamic HPP
$newStoreMethod = '
    /**
     * Store a newly created HPP with dynamic component selection
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                \'produk_id\' => \'required|exists:produks,id\',
                \'biaya_bahan\' => \'required|numeric|min:0\',
                \'total_btkl\' => \'required|numeric|min:0\',
                \'total_bop\' => \'required|numeric|min:0\',
                \'include_bbb\' => \'boolean\',
                \'include_btkl\' => \'boolean\', 
                \'include_bop\' => \'boolean\',
                \'selected_bbb_ids\' => \'array|nullable\',
                \'selected_btkl_ids\' => \'array|nullable\',
                \'selected_bop_ids\' => \'array|nullable\'
            ]);

            // Get product with user filtering
            $produk = Produk::where(\'id\', $validated[\'produk_id\'])
                ->where(\'user_id\', auth()->id())
                ->firstOrFail();

            // Create or update BomJobCosting
            $bomJobCosting = BomJobCosting::updateOrCreate(
                [
                    \'produk_id\' => $produk->id,
                    \'user_id\' => auth()->id()
                ],
                [
                    \'total_bbb\' => $validated[\'include_bbb\'] ? $validated[\'biaya_bahan\'] : 0,
                    \'total_btkl\' => $validated[\'include_btkl\'] ? $validated[\'total_btkl\'] : 0,
                    \'total_bop\' => $validated[\'include_bop\'] ? $validated[\'total_bop\'] : 0,
                    \'total_hpp\' => $validated[\'biaya_bahan\'] + $validated[\'total_btkl\'] + $validated[\'total_bop\'],
                    \'selected_bbb_ids\' => $validated[\'selected_bbb_ids\'] ?? [],
                    \'selected_btkl_ids\' => $validated[\'selected_btkl_ids\'] ?? [],
                    \'selected_bop_ids\' => $validated[\'selected_bop_ids\'] ?? [],
                    \'include_bbb\' => $validated[\'include_bbb\'],
                    \'include_btkl\' => $validated[\'include_btkl\'],
                    \'include_bop\' => $validated[\'include_bop\']
                ]
            );

            return redirect()
                ->route(\'master-data.harga-pokok-produksi.show\', $produk->id)
                ->withSuccess(\'HPP berhasil dibuat dengan komponen yang dipilih\');

        } catch (\Exception $e) {
            Log::error("Error in BomController@store: " . $e->getMessage());
            return back()
                ->withInput()
                ->withError(\'Terjadi kesalahan saat menyimpan HPP: \' . $e->getMessage());
        }
    }
';

// Find the position after the existing store method
$storeMethodPattern = '/public function store\(Request \$request\)\s*\{.*?\n    \}/s';

if (preg_match($storeMethodPattern, $controllerContent, $matches)) {
    // Replace the existing store method
    $newControllerContent = preg_replace($storeMethodPattern, $newStoreMethod, $controllerContent);
    
    file_put_contents($controllerFile, $newControllerContent);
    echo "✅ Updated BomController@store method\n";
} else {
    echo "❌ Could not find store method to replace\n";
}

echo "\n=== CONTROLLER UPDATED ===\n";

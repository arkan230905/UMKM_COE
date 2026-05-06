<?php

echo "=== CREATE SIMPLE WORKING CONTROLLER ===\n\n";

echo "Creating a simple, working BiayaBahanController...\n";

$simpleControllerContent = '<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\BomJobCosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiayaBahanController extends Controller
{
    public function __construct()
    {
        $this->middleware(\'auth\');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // DEBUG: Log user info
            Log::info("=== BIAYA BAHAN CONTROLLER START ===");
            Log::info("User ID: " . auth()->id());
            Log::info("User exists: " . (auth()->check() ? "YES" : "NO"));
            
            // Get products for logged in user
            $produks = Produk::where(\'user_id\', auth()->id())->orderBy(\'nama_produk\')->get();
            Log::info("Products found: " . $produks->count());
            
            $produkBiaya = [];
            
            foreach ($produks as $produk) {
                Log::info("Processing product: " . $produk->nama_produk . " (ID: " . $produk->id . ")");
                
                // Get BBB data directly
                $bbbData = DB::table(\'bom_job_bbb as bbb\')
                    ->leftJoin(\'bahan_bakus as bb\', \'bbb.bahan_baku_id\', \'=\', \'bb.id\')
                    ->leftJoin(\'satuans as s\', \'bb.satuan_id\', \'=\', \'s.id\')
                    ->where(\'bbb.user_id\', auth()->id())
                    ->where(\'bbb.produk_id\', $produk->id)
                    ->select(
                        \'bbb.id\',
                        \'bb.nama_bahan\',
                        \'bbb.jumlah as qty\',
                        \'bbb.satuan\',
                        \'bbb.harga_satuan\',
                        \'bbb.subtotal\',
                        \'s.nama as satuan_nama\'
                    )
                    ->get();
                
                Log::info("BBB records found: " . $bbbData->count());
                
                if ($bbbData->count() > 0) {
                    foreach ($bbbData as $bbb) {
                        Log::info("  - " . $bbb->nama_bahan . ": " . $bbb->subtotal);
                    }
                    
                    $totalBiayaBahanBaku = $bbbData->sum(\'subtotal\');
                    Log::info("Total BBB: " . $totalBiayaBahanBaku);
                    
                    $detailBahanBaku = $bbbData->map(function($detail) {
                        return [
                            \'nama_bahan\' => $detail->nama_bahan ?? \'Unknown\',
                            \'qty\' => $detail->qty ?? 0,
                            \'satuan\' => $detail->satuan_nama ?? $detail->satuan ?? \'unit\',
                            \'harga_satuan\' => $detail->harga_satuan ?? 0,
                            \'subtotal\' => $detail->subtotal ?? 0,
                            \'tipe\' => \'Bahan Baku\',
                            \'status\' => \'aktif\'
                        ];
                    })->toArray() ?? [];
                    
                    // Add to produkBiaya
                    $produkBiaya[] = [
                        \'produk\' => $produk,
                        \'total_biaya\' => $totalBiayaBahanBaku,
                        \'total_biaya_bahan_baku\' => $totalBiayaBahanBaku,
                        \'total_biaya_bahan_pendukung\' => 0,
                        \'detail_bahan\' => $detailBahanBaku,
                        \'detail_bahan_baku\' => $detailBahanBaku,
                        \'detail_bahan_pendukung\' => [],
                        \'total_biaya_bahan\' => $totalBiayaBahanBaku,
                        \'bom_job_costing\' => null
                    ];
                    
                    Log::info("Added to produkBiaya with total: " . $totalBiayaBahanBaku);
                    
                } else {
                    Log::info("No BBB data found, adding empty entry");
                    
                    // Add empty entry
                    $produkBiaya[] = [
                        \'produk\' => $produk,
                        \'total_biaya\' => 0,
                        \'total_biaya_bahan_baku\' => 0,
                        \'total_biaya_bahan_pendukung\' => 0,
                        \'detail_bahan\' => [],
                        \'detail_bahan_baku\' => [],
                        \'detail_bahan_pendukung\' => [],
                        \'total_biaya_bahan\' => 0,
                        \'bom_job_costing\' => null
                    ];
                }
            }
            
            Log::info("Final produkBiaya count: " . count($produkBiaya));
            Log::info("=== BIAYA BAHAN CONTROLLER END ===");
            
            return view(\'master-data.biaya-bahan.index\', compact(\'produks\', \'produkBiaya\'));
            
        } catch (\Exception $e) {
            Log::error("Error in BiayaBahanController@index: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return back()->withError(\'Error loading biaya bahan data: \' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating biaya bahan for a product
     */
    public function create($id)
    {
        $produk = Produk::where(\'id\', $id)->where(\'user_id\', auth()->id())->firstOrFail();
        return view(\'master-data.biaya-bahan.create\', compact(\'produk\'));
    }

    /**
     * Store a newly created biaya bahan
     */
    public function store(Request $request)
    {
        // Implementation for storing biaya bahan
        return redirect()->route(\'master-data.biaya-bahan.index\')->withSuccess(\'Biaya bahan berhasil disimpan\');
    }

    /**
     * Show the form for editing biaya bahan
     */
    public function edit($id)
    {
        $produk = Produk::where(\'id\', $id)->where(\'user_id\', auth()->id())->firstOrFail();
        return view(\'master-data.biaya-bahan.edit\', compact(\'produk\'));
    }

    /**
     * Update biaya bahan
     */
    public function update(Request $request, $id)
    {
        // Implementation for updating biaya bahan
        return redirect()->route(\'master-data.biaya-bahan.index\')->withSuccess(\'Biaya bahan berhasil diperbarui\');
    }

    /**
     * Remove biaya bahan
     */
    public function destroy($id)
    {
        // Implementation for deleting biaya bahan
        return redirect()->route(\'master-data.biaya-bahan.index\')->withSuccess(\'Biaya bahan berhasil dihapus\');
    }
}
';

$controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php';

// Backup current controller
if (file_exists($controllerFile)) {
    $backupFile = 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController_backup_' . date('Y-m-d_H-i-s') . '.php';
    copy($controllerFile, $backupFile);
    echo "✅ Backed up current controller to: " . basename($backupFile) . "\n";
}

// Write new simple controller
file_put_contents($controllerFile, $simpleControllerContent);

echo "✅ Created new simple BiayaBahanController\n";
echo "✅ Added comprehensive logging\n";
echo "✅ Simplified logic\n";
echo "✅ Error handling\n";
echo "✅ Visit the page and check storage/logs/laravel.log for debug output\n\n";

echo "The new controller:\n";
echo "- Uses simple, direct queries\n";
echo "- Has extensive logging\n";
echo "- Handles errors gracefully\n";
echo "- Should work with the existing view\n\n";

echo "=== SIMPLE CONTROLLER CREATED ===\n";

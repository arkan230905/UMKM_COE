<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Produk;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:pegawai_gudang');
    }

    public function index(Request $request)
    {
        $tipe = $request->get('tipe', 'material'); // material|bahan_pendukung|product
        
        // Get data dengan logic yang SAMA PERSIS dengan master-data
        if ($tipe == 'material') {
            // Sama seperti BahanBakuController::index()
            $items = BahanBaku::with('satuan')->get();
            
            // Hitung harga rata-rata untuk setiap bahan baku (sama seperti master-data)
            foreach ($items as $item) {
                $averageHarga = $this->getAverageHargaSatuan($item->id);
                
                // Jika ada harga rata-rata, gunakan itu. Jika tidak, gunakan harga default
                if ($averageHarga > 0) {
                    $item->harga_satuan_display = $averageHarga;
                } else {
                    $item->harga_satuan_display = $item->harga_satuan;
                }
            }
        } elseif ($tipe == 'bahan_pendukung') {
            // Sama seperti BahanPendukungController::index()
            $query = \App\Models\BahanPendukung::with(['satuan', 'kategoriBahanPendukung']);
            
            $items = $query->orderBy('nama_bahan')->get();
            
            // Hitung harga rata-rata untuk setiap bahan pendukung (sama seperti master-data)
            foreach ($items as $item) {
                // Get all pembelian detail records for this bahan pendukung
                $pembelianDetails = \App\Models\PembelianDetail::where('bahan_pendukung_id', $item->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                if ($pembelianDetails->count() > 0) {
                    // Calculate average price from pembelian details
                    $totalHarga = 0;
                    $totalQty = 0;
                    
                    foreach ($pembelianDetails as $detail) {
                        $totalHarga += ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                        $totalQty += ($detail->jumlah ?? 0);
                    }
                    
                    $avgPrice = $totalQty > 0 ? $totalHarga / $totalQty : 0;
                    
                    // Add display property (jangan update database)
                    $item->harga_satuan_display = $avgPrice;
                } else {
                    // Use default price if no pembelian details
                    $item->harga_satuan_display = $item->harga_satuan;
                }
            }
        } else {
            // Get products with real-time stock calculation
            $items = Produk::with('satuan')->get();
            
            // Calculate real-time stock for each product (sama seperti yang diupdate oleh ProduksiController)
            foreach ($items as $item) {
                // Use StockService to get available quantity
                $stockService = new \App\Services\StockService();
                $item->stok_tersedia = $stockService->getAvailableQty('product', $item->id);
                
                // Also display the database stok for comparison
                $item->stok_database = $item->stok ?? 0;
            }
        }
        
        return view('pegawai-gudang.stok.index', compact('items', 'tipe'));
    }

    /**
     * Get average harga satuan untuk bahan baku (SAMA PERSIS dengan BahanBakuController)
     */
    private function getAverageHargaSatuan($bahanBakuId)
    {
        $bahanBaku = BahanBaku::findOrFail($bahanBakuId);
        
        // Ambil semua pembelian detail untuk bahan baku ini
        $details = \App\Models\PembelianDetail::where('bahan_baku_id', $bahanBakuId)
            ->with(['pembelian'])
            ->get();
        
        if ($details->isEmpty()) {
            return 0;
        }
        
        // Hitung total harga dan total quantity
        $totalHarga = 0;
        $totalQuantity = 0;
        
        foreach ($details as $detail) {
            $totalHarga += ($detail->harga_satuan ?? 0) * ($detail->jumlah ?? 0);
            $totalQuantity += ($detail->jumlah ?? 0);
        }
        
        // Hitung harga rata-rata
        $averageHarga = $totalQuantity > 0 ? $totalHarga / $totalQuantity : 0;
        
        return $averageHarga;
    }
}

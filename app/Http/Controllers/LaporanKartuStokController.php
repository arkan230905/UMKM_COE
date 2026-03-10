<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

class LaporanKartuStokController extends Controller
{
    public function index(Request $request)
    {
        $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::with('satuan')->orderBy('nama_bahan')->get();
        
        $stockMovements = collect();
        
        // Get stock movements if material is selected - ORDER BY DATE for proper sequence
        if ($request->filled('material_type') && $request->filled('material_id')) {
            if ($request->material_type == 'bahan_baku') {
                $stockMovements = StockMovement::where('item_type', 'material')
                    ->where('item_id', $request->material_id)
                    ->orderBy('tanggal') // Order by date instead of created_at
                    ->get();
            } elseif ($request->material_type == 'bahan_pendukung') {
                $stockMovements = StockMovement::where('item_type', 'support')
                    ->where('item_id', $request->material_id)
                    ->orderBy('tanggal') // Order by date instead of created_at
                    ->get();
            }
        }
        
        return view('laporan.kartu-stok', compact(
            'bahanBakus',
            'bahanPendukungs', 
            'stockMovements'
        ));
    }

    /**
     * Reset product stock to zero before new production
     */
    public function resetProdukStok(Request $request)
    {
        try {
            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id',
                'konfirmasi' => 'required|boolean'
            ]);

            if (!$validated['konfirmasi']) {
                return back()->with('error', 'Anda harus mencentang konfirmasi untuk mereset stok produk.');
            }

            $produk = \App\Models\Produk::findOrFail($validated['produk_id']);
            
            // Reset stock to zero
            $produk->stok = 0;
            $produk->save();

            // Clear all stock layers for this product
            \App\Models\StockLayer::where('item_type', 'product')
                ->where('item_id', $produk->id)
                ->delete();

            // Create stock movement record for audit trail
            \App\Models\StockMovement::create([
                'item_type' => 'product',
                'item_id' => $produk->id,
                'tanggal' => now()->format('Y-m-d'),
                'direction' => 'reset',
                'qty' => $produk->stok,
                'satuan' => $produk->satuan->nama ?? 'Unit',
                'unit_cost' => 0,
                'total_cost' => 0,
                'ref_type' => 'stock_reset',
                'ref_id' => 0,
                'keterangan' => 'Reset stok produk sebelum produksi: ' . $produk->nama_produk,
            ]);

            return back()->with('success', 'Stok produk "' . $produk->nama_produk . '" berhasil direset ke 0. Siap untuk produksi baru.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset stok: ' . $e->getMessage());
        }
    }

    /**
     * Show reset stock form
     */
    public function createResetForm()
    {
        $products = \App\Models\Produk::orderBy('nama_produk')->get();
        
        return view('laporan.kartu-stok.reset', compact('products'));
    }
}
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
        
        // Get stock movements if material is selected
        if ($request->filled('material_type') && $request->filled('material_id')) {
            if ($request->material_type == 'bahan_baku') {
                $stockMovements = StockMovement::where('item_type', 'material')
                    ->where('item_id', $request->material_id)
                    ->orderBy('created_at')
                    ->get();
            } elseif ($request->material_type == 'bahan_pendukung') {
                $stockMovements = StockMovement::where('item_type', 'support')
                    ->where('item_id', $request->material_id)
                    ->orderBy('created_at')
                    ->get();
            }
        }
        
        return view('laporan.kartu-stok', compact(
            'bahanBakus',
            'bahanPendukungs', 
            'stockMovements'
        ));
    }
}
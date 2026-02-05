<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use Illuminate\Http\Request;

class BiayaBahanPerUnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display biaya bahan per unit dengan konversi
     */
    public function index(Request $request)
    {
        $query = BahanBaku::query();
        
        // Filter by nama
        if ($request->filled('nama_bahan')) {
            $query->where('nama_bahan', 'like', '%' . $request->nama_bahan . '%');
        }
        
        // Filter by status stok
        if ($request->filled('status_stok')) {
            $status = $request->status_stok;
            if ($status == 'habis') {
                $query->where('stok', 0);
            } elseif ($status == 'menipis') {
                $query->whereColumn('stok', '<=', 'stok_minimum')
                    ->where('stok', '>', 0);
            } elseif ($status == 'aman') {
                $query->whereColumn('stok', '>', 'stok_minimum');
            }
        }
        
        $bahanBakus = $query->with('satuanRelation')->orderBy('nama_bahan')->paginate(15);
        
        // Hitung biaya per unit untuk setiap bahan
        $bahanBakus->getCollection()->transform(function ($bahan) {
            $data = $bahan->toArray();
            
            // Biaya per satuan dasar
            $data['biaya_per_satuan_dasar'] = $bahan->harga_rata_rata;
            
            // Biaya per gram
            $data['biaya_per_gram'] = $bahan->satuan_dasar == 'KG' ? $bahan->harga_rata_rata / 1000 : $bahan->harga_rata_rata;
            
            // Biaya per ml
            $data['biaya_per_ml'] = $bahan->satuan_dasar == 'L' ? $bahan->harga_rata_rata / 1000 : $bahan->harga_rata_rata;
            
            // Biaya per pcs (untuk satuan diskrit)
            $data['biaya_per_pcs'] = !in_array($bahan->satuan_dasar, ['KG', 'L', 'GRAM', 'ML']) ? $bahan->harga_rata_rata : null;
            
            // Konversi ke berbagai satuan
            $data['harga_per_kg'] = $bahan->harga_rata_rata;
            $data['harga_per_gram'] = $bahan->harga_rata_rata / 1000;
            $data['harga_per_mg'] = $bahan->harga_rata_rata * 1000;
            $data['harga_per_liter'] = $bahan->satuan_dasar == 'L' ? $bahan->harga_rata_rata : $bahan->harga_rata_rata;
            $data['harga_per_ml'] = $bahan->harga_rata_rata / 1000;
            $data['harga_per_mililiter'] = $bahan->harga_rata_rata * 1000;
            $data['harga_per_pcs'] = $data['biaya_per_pcs'];
            
            // Total nilai stok
            $data['total_nilai_stok'] = $bahan->harga_rata_rata * $bahan->stok;
            
            // Status stok
            if ($bahan->stok == 0) {
                $data['status_stok'] = 'Habis';
                $data['status_color'] = 'danger';
            } elseif ($bahan->stok <= $bahan->stok_minimum) {
                $data['status_stok'] = 'Menipis';
                $data['status_color'] = 'warning';
            } else {
                $data['status_stok'] = 'Aman';
                $data['status_color'] = 'success';
            }
            
            return $data;
        });
        
        return view('master-data.biaya-bahan-per-unit.index', compact('bahanBakus'));
    }
    
    /**
     * Show detail biaya bahan dengan konversi lengkap
     */
    public function show($id)
    {
        $bahanBaku = BahanBaku::with('satuanRelation')->findOrFail($id);
        
        // Hitung semua konversi
        $konversi = [
            'kg' => [
                'nama' => 'Kilogram',
                'harga' => $bahanBaku->harga_rata_rata,
                'simbol' => 'kg'
            ],
            'gram' => [
                'nama' => 'Gram',
                'harga' => $bahanBaku->harga_rata_rata / 1000,
                'simbol' => 'g'
            ],
            'mg' => [
                'nama' => 'Miligram',
                'harga' => $bahanBaku->harga_rata_rata * 1000,
                'simbol' => 'mg'
            ],
            'liter' => [
                'nama' => 'Liter',
                'harga' => $bahanBaku->satuan_dasar == 'L' ? $bahanBaku->harga_rata_rata : $bahanBaku->harga_rata_rata,
                'simbol' => 'L'
            ],
            'ml' => [
                'nama' => 'Mililiter',
                'harga' => $bahanBaku->harga_rata_rata / 1000,
                'simbol' => 'ml'
            ],
            'pcs' => [
                'nama' => 'Pieces',
                'harga' => !in_array($bahanBaku->satuan_dasar, ['KG', 'L', 'GRAM', 'ML']) ? $bahanBaku->harga_rata_rata : null,
                'simbol' => 'pcs'
            ]
        ];
        
        // History pembelian
        $history = $bahanBaku->pembelianDetails()
            ->with('pembelian')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('master-data.biaya-bahan-per-unit.show', compact('bahanBaku', 'konversi', 'history'));
    }
    
    /**
     * Update biaya bahan manual (jika diperlukan)
     */
    public function update(Request $request, $id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        
        $request->validate([
            'harga_rata_rata' => 'required|numeric|min:0',
            'satuan_dasar' => 'required|string',
            'faktor_konversi' => 'required|numeric|min:0'
        ]);
        
        $bahanBaku->update([
            'harga_rata_rata' => $request->harga_rata_rata,
            'satuan_dasar' => $request->satuan_dasar,
            'faktor_konversi' => $request->faktor_konversi,
            'harga_per_satuan_dasar' => $request->harga_rata_rata
        ]);
        
        // Trigger update BOM otomatis
        $this->triggerBomUpdate($bahanBaku->id);
        
        return back()->with('success', 'Biaya bahan berhasil diperbarui dan BOM otomatis diupdate');
    }
    
    /**
     * Trigger update BOM otomatis saat harga berubah
     */
    private function triggerBomUpdate($bahanBakuId)
    {
        // Update semua BOM yang menggunakan bahan ini
        $bomJobCostings = \App\Models\BomJobCosting::whereHas('detailBBB', function($query) use ($bahanBakuId) {
            $query->where('bahan_baku_id', $bahanBakuId);
        })->get();
        
        foreach ($bomJobCostings as $bomJobCosting) {
            $bomJobCosting->recalculate();
        }
    }
}

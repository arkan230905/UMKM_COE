<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Services\BomSyncService;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    // Menampilkan semua data bahan baku
    public function index()
    {
        $bahanBaku = BahanBaku::with('satuan')->get();
        
        // Hitung harga rata-rata untuk setiap bahan baku
        foreach ($bahanBaku as $bahan) {
            $averageHarga = $this->getAverageHargaSatuan($bahan->id);
            
            // Jika ada harga rata-rata, gunakan itu. Jika tidak, gunakan harga default
            if ($averageHarga > 0) {
                $bahan->harga_satuan_display = $averageHarga;
            } else {
                $bahan->harga_satuan_display = $bahan->harga_satuan;
            }
        }
        
        return view('master-data.bahan-baku.index', compact('bahanBaku'));
    }

    // Menampilkan form tambah data
    public function create()
    {
        $satuans = Satuan::all();
        return view('master-data.bahan-baku.create', compact('satuans'));
    }

    // Simpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        // Get the satuan name from the satuan_id
        $satuan = Satuan::findOrFail($request->satuan_id);
        
        BahanBaku::create([
            'nama_bahan' => $request->nama_bahan,
            'satuan_id' => $request->satuan_id,
            'satuan' => $satuan->nama, // Add the satuan name
            'stok' => $request->stok,
            'harga_satuan' => $request->harga_satuan,
        ]);

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $bahanBaku = BahanBaku::with('satuan')->findOrFail($id);
        $satuans = Satuan::all();
        return view('master-data.bahan-baku.edit', compact('bahanBaku', 'satuans'));
    }

    // Update data
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $bahanBaku = BahanBaku::findOrFail($id);
        
        // Get the satuan name from the satuan_id
        $satuan = Satuan::findOrFail($request->satuan_id);
        
        // Update properties one by one and save
        $bahanBaku->nama_bahan = $request->nama_bahan;
        $bahanBaku->satuan_id = $request->satuan_id;
        $bahanBaku->satuan = $satuan->nama; // Update the satuan name
        $bahanBaku->stok = $request->stok;
        $bahanBaku->harga_satuan = $request->harga_satuan;
        
        // Save changes
        $bahanBaku->save();

        // Sync BOM when bahan baku price changes
        BomSyncService::syncBomFromMaterialChange('bahan_baku', $bahanBaku->id);

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil diperbarui!');
    }

    // Hapus data
    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->delete();

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil dihapus!');
    }

    /**
     * Get average harga satuan untuk bahan baku
     */
    public function getAverageHargaSatuan($bahanBakuId)
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

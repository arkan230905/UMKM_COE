<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanBaku;

class BahanBakuController extends Controller
{
    // Menampilkan daftar bahan baku
    public function index()
    {
        $bahanBaku = BahanBaku::orderBy('id', 'asc')->get();

        // Tambahkan logika perhitungan detail harga
        foreach ($bahanBaku as $item) {
            $item->detail_harga = $this->generateDetailHarga($item);
        }

        return view('master-data.bahan-baku.index', compact('bahanBaku'));
    }

    // Form tambah bahan baku
    public function create()
    {
        $satuanOptions = ['Kg', 'Liter', 'Pcs', 'Unit'];
        return view('master-data.bahan-baku.create', compact('satuanOptions'));
    }

    // Simpan bahan baku baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'stok' => 'required|numeric|min:0',
            'satuan' => 'required|string',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        BahanBaku::create($request->all());
        return redirect()->route('master-data.bahan-baku.index')
                         ->with('success', 'Bahan Baku berhasil ditambahkan.');
    }

    // Form edit bahan baku
    public function edit(BahanBaku $bahanBaku)
    {
        $satuanOptions = ['Kg', 'Liter', 'Pcs', 'Unit'];
        return view('master-data.bahan-baku.edit', compact('bahanBaku', 'satuanOptions'));
    }

    // Update bahan baku
    public function update(Request $request, BahanBaku $bahanBaku)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'stok' => 'required|numeric|min:0',
            'satuan' => 'required|string',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $bahanBaku->update($request->all());
        return redirect()->route('master-data.bahan-baku.index')
                         ->with('success', 'Bahan Baku berhasil diperbarui.');
    }

    // Hapus bahan baku
    public function destroy(BahanBaku $bahanBaku)
    {
        $bahanBaku->delete();
        return redirect()->route('master-data.bahan-baku.index')
                         ->with('success', 'Bahan Baku berhasil dihapus.');
    }

    // 🔹 Fungsi tambahan untuk menghitung detail harga berdasarkan satuan utama
    private function generateDetailHarga($item)
    {
        $harga = $item->harga_satuan;
        $satuan = strtolower($item->satuan);

        switch ($satuan) {
            case 'kg':
                return [
                    'g' => 'Rp ' . number_format($harga / 1000, 0, ',', '.'),
                    'mg' => 'Rp ' . number_format($harga / 1000000, 0, ',', '.'),
                ];

            case 'liter':
                return [
                    'ml' => 'Rp ' . number_format($harga / 1000, 0, ',', '.'),
                    'cl' => 'Rp ' . number_format($harga / 100, 0, ',', '.'),
                ];

            case 'pcs':
                return [
                    'buah' => 'Rp ' . number_format($harga, 0, ',', '.'),
                ];

            case 'unit':
                return [
                    'buah' => 'Rp ' . number_format($harga, 0, ',', '.'),
                ];

            default:
                return ['-' => '-'];
        }
    }
}

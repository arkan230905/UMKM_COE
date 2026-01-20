<?php

namespace App\Http\Controllers;

use App\Models\BahanPendukung;
use App\Models\KategoriBahanPendukung;
use App\Models\Satuan;
use Illuminate\Http\Request;

class BahanPendukungController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = BahanPendukung::with(['satuan', 'kategoriBahanPendukung']);
        
        // Filter kategori
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }
        
        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_bahan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_bahan', 'like', '%' . $request->search . '%');
            });
        }
        
        $bahanPendukungs = $query->orderBy('nama_bahan')->paginate(15);
        $kategoris = KategoriBahanPendukung::active()->orderBy('nama')->get();
        
        // Calculate average prices for each bahan pendukung
        foreach ($bahanPendukungs as $bahanPendukung) {
            // Get all pembelian detail records for this bahan pendukung
            $pembelianDetails = \App\Models\PembelianDetail::where('bahan_pendukung_id', $bahanPendukung->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($pembelianDetails->count() > 0) {
                // Calculate average price from pembelian details
                $totalHarga = $pembelianDetails->sum('subtotal');
                $totalQty = $pembelianDetails->sum('jumlah');
                $avgPrice = $totalQty > 0 ? $totalHarga / $totalQty : 0;
                
                // Update harga_satuan with average price
                $bahanPendukung->update(['harga_satuan' => $avgPrice]);
            }
        }
        
        return view('master-data.bahan-pendukung.index', compact('bahanPendukungs', 'kategoris'));
    }

    public function create()
    {
        $satuans = Satuan::orderBy('nama')->get();
        $kategoris = KategoriBahanPendukung::active()->orderBy('nama')->get();
        return view('master-data.bahan-pendukung.create', compact('satuans', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan_id' => 'required|exists:satuans,id',
            'harga_satuan' => 'required|numeric|min:0',
            'stok' => 'nullable|numeric|min:0',
            'stok_minimum' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori_bahan_pendukung,id',
        ]);

        // Set kategori string dari nama kategori untuk backward compatibility
        $kategori = KategoriBahanPendukung::find($validated['kategori_id']);
        $validated['kategori'] = strtolower($kategori->nama);

        // Create bahan pendukung
        BahanPendukung::create($validated);

        return redirect()->route('master-data.bahan-pendukung.index')
            ->with('success', 'Bahan pendukung berhasil ditambahkan');
    }

    public function show(BahanPendukung $bahanPendukung)
    {
        $bahanPendukung->load(['satuan', 'kategoriBahanPendukung']);
        return view('master-data.bahan-pendukung.show', compact('bahanPendukung'));
    }

    public function edit(BahanPendukung $bahanPendukung)
    {
        $satuans = Satuan::orderBy('nama')->get();
        $kategoris = KategoriBahanPendukung::active()->orderBy('nama')->get();
        return view('master-data.bahan-pendukung.edit', compact('bahanPendukung', 'satuans', 'kategoris'));
    }

    public function update(Request $request, BahanPendukung $bahanPendukung)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan_id' => 'required|exists:satuans,id',
            'harga_satuan' => 'required|numeric|min:0',
            'stok' => 'nullable|numeric|min:0',
            'stok_minimum' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori_bahan_pendukung,id',
        ]);

        // Handle checkbox - tidak perlu validasi boolean
        $validated['is_active'] = $request->has('is_active');
        
        // Set kategori string dari nama kategori untuk backward compatibility
        $kategori = KategoriBahanPendukung::find($validated['kategori_id']);
        if ($kategori) {
            $validated['kategori'] = strtolower($kategori->nama);
        }
        
        $bahanPendukung->update($validated);
        
        return redirect()->route('master-data.bahan-pendukung.index')
            ->with('success', 'Bahan pendukung berhasil diperbarui');
    }

    public function destroy(BahanPendukung $bahanPendukung)
    {
        try {
            $bahanPendukung->delete();
            return redirect()->route('master-data.bahan-pendukung.index')
                ->with('success', 'Bahan pendukung berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}

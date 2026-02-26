<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\PenjualanDetail;
use App\Models\Produksi;
use App\Models\Bom;
use App\Models\StockLayer;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Support\UnitConverter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index()
    {
        // Get all products with their HPP data from BomJobCosting
        $produks = Produk::with(['bomJobCosting'])->get();
        
        // Force refresh HPP data for all products to ensure latest data
        foreach ($produks as $produk) {
            $bomJobCosting = $produk->bomJobCosting;
            if ($bomJobCosting) {
                // Force recalculate to get latest HPP
                $bomJobCosting->recalculate();
                
                // Force update product with latest HPP
                $totalHPP = $bomJobCosting->total_hpp;
                if ($produk->harga_bom != $totalHPP) {
                    DB::table('produks')
                        ->where('id', $produk->id)
                        ->update([
                            'harga_bom' => $totalHPP,
                            'updated_at' => now()
                        ]);
                    $produk->harga_bom = $totalHPP; // Update model instance
                }
            }
        }
        
        // Get HPP data for each product from HPP page calculations
        $hargaBom = [];
        foreach ($produks as $produk) {
            $bomJobCosting = $produk->bomJobCosting;
            
            if ($bomJobCosting) {
                // Use HPP data from BomJobCosting (same as HPP page)
                $totalHPP = $bomJobCosting->total_hpp;
                $hargaBom[$produk->id] = $totalHPP;
            } else {
                // For products without BomJobCosting, use existing harga_bom or default to 0
                $hargaBom[$produk->id] = $produk->harga_bom ?? 0;
            }
        }
        
        return view('master-data.produk.index', compact('produks', 'hargaBom'));
    }

    public function katalogPelanggan()
    {
        $produks = Produk::select([
            'id',
            'nama_produk',
            'foto',
            'deskripsi',
            'harga_jual',
            'stok',
        ])->whereNotNull('harga_jual')->get();

        return view('pelanggan.produk.index', compact('produks'));
    }

    public function create()
    {
        return view('master-data.produk.create');
    }
    
    public function show($id)
    {
        $produk = Produk::with(['boms.details.bahanBaku'])->findOrFail($id);
        
        // Hitung total biaya BOM dari details
        $totalBiayaBom = 0;
        $bom = $produk->boms->first();
        if ($bom && $bom->details) {
            $totalBiayaBom = $bom->details->sum('subtotal') ?? 0;
        }
        
        // Hitung harga jual berdasarkan margin
        $hargaJual = $totalBiayaBom * (1 + (($produk->margin_percent ?? 30) / 100));
        
        return view('master-data.produk.show', [
            'produk' => $produk,
            'totalBiayaBom' => $totalBiayaBom,
            'hargaJual' => $hargaJual
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'margin_percent' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('produk', 'public');
        }

        // Harga jual kosong dulu; akan dihitung dari BOM
        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'harga_jual' => null,
            'margin_percent' => $request->input('margin_percent'),
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
        ]);

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan. Silakan tambahkan BOM.');
    }

    public function edit(Produk $produk)
    {
        return view('master-data.produk.edit', compact('produk'));
    }
    
    /**
     * Print barcode labels for a product
     */
    public function printBarcode(Produk $produk)
    {
        return view('master-data.produk.print-barcode', compact('produk'));
    }
    
    /**
     * Print barcode labels for all products
     */
    public function printBarcodeAll()
    {
        $produks = Produk::whereNotNull('barcode')->get();
        return view('master-data.produk.print-barcode-bulk', compact('produks'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'margin_percent' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'margin_percent' => $request->input('margin_percent'),
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Produk $produk)
    {
        // Cegah hapus jika masih dipakai transaksi atau master terkait
        $blockers = [];
        $pd = PenjualanDetail::where('produk_id', $produk->id)->count();
        if ($pd > 0) $blockers[] = "Dipakai di Penjualan (".$pd.")";
        $pr = Produksi::where('produk_id', $produk->id)->count();
        if ($pr > 0) $blockers[] = "Dipakai di Produksi (".$pr.")";
        $bm = Bom::where('produk_id', $produk->id)->count();
        if ($bm > 0) $blockers[] = "Memiliki BOM (".$bm.")";
        $sl = StockLayer::where('item_type','product')->where('item_id',$produk->id)->count();
        if ($sl > 0) $blockers[] = "Memiliki Stock Layer (".$sl.")";
        $sm = StockMovement::where('item_type','product')->where('item_id',$produk->id)->count();
        if ($sm > 0) $blockers[] = "Memiliki Mutasi Stok (".$sm.")";

        if (!empty($blockers)) {
            return redirect()->route('master-data.produk.index')
                ->with('error', 'Produk tidak dapat dihapus karena masih terkait: '.implode(', ', $blockers).'. Hapus/arsipkan transaksi terkait terlebih dahulu.');
        }

        $produk->delete();
        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Display public catalog page for customers
     */
    public function catalog(Request $request)
    {
        // Get products with stock and pricing info
        $query = Produk::select([
            'id',
            'nama_produk',
            'deskripsi',
            'harga_jual',
            'stok',
            'foto',
            'barcode',
            'created_at'
        ])
        ->where('stok', '>=', 0); // Show all products including those with zero stock

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_produk', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('barcode', 'LIKE', "%{$searchTerm}%");
            });
        }

        $produks = $query->orderBy('nama_produk', 'asc')->get();

        return view('catalog.index', compact('produks'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\BiayaBahanBaku;
use App\Models\HargaPokokProduksiBiayaBahanBaku;
use App\Models\HargaPokokProduksiBtkl;
use App\Models\HargaPokokProduksiBop;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Get all HPP records for current user
        $hppRecords = $this->getHppRecords();
        
        // Filter by product name
        if ($request->filled('nama_produk')) {
            $hppRecords = $hppRecords->filter(function($record) {
                return stripos($record['nama_produk'], $request->nama_produk) !== false;
            });
        }
        
        // Paginate results
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        $total = $hppRecords->count();
        $sliced = $hppRecords->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $hppRecords = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        return view('master-data.bom.index', compact('hppRecords'));
    }

    public function create()
    {

        return view('master-data.bom.create');
}

    public function store(Request $request)
    {

        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'selected_bbb' => 'nullable|array',
            'selected_bbb.*' => 'exists:biaya_bahan_baku,id',
            'selected_btkl' => 'nullable|array',
            'selected_btkl.*' => 'exists:proses_produksis,id',
            'selected_bop' => 'nullable|array',
            'selected_bop.*' => 'exists:bop_proses,id',
        ]);
$user_id = auth()->id();
        $produk_id = $request->produk_id;

        // Clear existing selections for this product
        HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)->delete();
        HargaPokokProduksiBtkl::where('user_id', $user_id)->delete();
        HargaPokokProduksiBop::where('user_id', $user_id)->delete();


        // Save BBB selections
        if ($request->filled('selected_bbb')) {
            foreach ($request->selected_bbb as $bbb_id) {
                HargaPokokProduksiBiayaBahanBaku::create([
                    'user_id' => $user_id,
                    'bom_job_bbb_id' => $bbb_id,
                ]);
            }
        }

        // Save BTKL selections
        if ($request->filled('selected_btkl')) {
            foreach ($request->selected_btkl as $proses_id) {
                HargaPokokProduksiBtkl::create([
                    'user_id' => $user_id,
                    'proses_produksis_id' => $proses_id,
                ]);
            }
        }

        // Save BOP selections
        if ($request->filled('selected_bop')) {
            foreach ($request->selected_bop as $bop_id) {
                HargaPokokProduksiBop::create([
                    'user_id' => $user_id,
                    'bop_proses_id' => $bop_id,
                ]);
            }
        }

        return redirect()->route('master-data.harga-pokok-produksi.index')
            ->with('success', 'Harga Pokok Produksi berhasil disimpan!');
    }

    public function show($produk_id)
    {
        $produk = Produk::findOrFail($produk_id);
        
        // Get selected components for this product
        $selectedBbb = HargaPokokProduksiBiayaBahanBaku::where('user_id', auth()->id())
            ->with('biayaBahanBaku.bahanBaku')
            ->get();
            
        $selectedBtkl = HargaPokokProduksiBtkl::where('user_id', auth()->id())
            ->with('prosesProduksi')
            ->get();
            
        $selectedBop = HargaPokokProduksiBop::where('user_id', auth()->id())
            ->with('bopProses')
            ->get();

        // Calculate totals
        $totalBbb = $this->calculateTotalBbb($selectedBbb);
        $totalBtkl = $this->calculateTotalBtkl($selectedBtkl);
        $totalBop = $this->calculateTotalBop($selectedBop);
        $totalHpp = $totalBbb + $totalBtkl + $totalBop;

        return view('master-data.bom.show', compact(
            'produk',
            'selectedBbb',
            'selectedBtkl', 
            'selectedBop',
            'totalBbb',
            'totalBtkl',
            'totalBop',
            'totalHpp'
        ));
    }

    public function destroy($produk_id)
    {
        $user_id = auth()->id();
        
        // Delete all selections for this product
        HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)->delete();
        HargaPokokProduksiBtkl::where('user_id', $user_id)->delete();
        HargaPokokProduksiBop::where('user_id', $user_id)->delete();

        return redirect()->route('master-data.harga-pokok-produksi.index')
            ->with('success', 'Harga Pokok Produksi berhasil dihapus!');
    }

    private function getHppRecords()
    {
        $user_id = auth()->id();
        
        // Get all unique products that have BBB selections (since BBB is product-specific)
        $bbbProducts = HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
            ->with('biayaBahanBaku')
            ->get()
            ->pluck('biayaBahanBaku.produk_id')
            ->filter()
            ->unique()
            ->values();

        // Since BTKL and BOP are not product-specific, we'll only use BBB products
        // or create a general HPP record if user has BTKL/BOP selections
        
        $hasAnySelections = HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)->exists() ||
                           HargaPokokProduksiBtkl::where('user_id', $user_id)->exists() ||
                           HargaPokokProduksiBop::where('user_id', $user_id)->exists();

        // If we have BBB products, use those
        if ($bbbProducts->isNotEmpty()) {
            $allProductIds = $bbbProducts;
        } 
        // If we have BTKL/BOP selections but no BBB, we need to show something
        // Let's get all products for this user as potential HPP candidates
        elseif ($hasAnySelections) {
            $allProductIds = \App\Models\Produk::where('user_id', $user_id)
                ->pluck('id')

                ->take(5); // Limit to first 5 products to avoid too many results
        } 
        else {
            $allProductIds = collect();
}


        // Get product details
        return $allProductIds->map(function($produk_id) {
            $produk = \App\Models\Produk::find($produk_id);
            if (!$produk) return null;
return [
                'id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'kode' => $produk->kode_produk,  // Fixed: use kode_produk instead of kode
                'satuan' => $produk->satuan->nama ?? '-',
                'stok' => $produk->stok,
                'harga_jual' => $produk->harga_jual,
            ];
        })->filter()->values();
    }

    private function calculateTotalBbb($selectedBbb)
    {
        $total = 0;
        foreach ($selectedBbb as $bbb) {
            if ($bbb->biayaBahanBaku) {
                $total += $bbb->biayaBahanBaku->subtotal;
            }
        }
        return $total;
    }

    private function calculateTotalBtkl($selectedBtkl)
    {
        $total = 0;
        foreach ($selectedBtkl as $btkl) {
            if ($btkl->prosesProduksi) {
                // Calculate total BTKL: tarif_per_produk × jumlah_pegawai
                $tarifPerProduk = $btkl->prosesProduksi->tarif_per_produk ?? 0;
                $jumlahPegawai = $btkl->prosesProduksi->jumlah_pegawai ?? 1;
                $tarif = $tarifPerProduk * $jumlahPegawai;
                $total += $tarif;
            }
        }
        return $total;
    }

    private function calculateTotalBop($selectedBop)
    {
        $total = 0;
        foreach ($selectedBop as $bop) {
            if ($bop->bopProses) {
                $total += $bop->bopProses->total_bop_per_produk ?? 0;
            }
        }
        return $total;
    }

    // API endpoints for data
    public function getAvailableBbb($produk_id)
    {
        // Optimized query with specific columns and minimal joins
        $biayaBahanBaku = \App\Models\BiayaBahanBaku::where('produk_id', $produk_id)
            ->where('user_id', auth()->id())
            ->with(['bahanBaku' => function($query) {
                $query->select('id', 'nama_bahan', 'saldo_awal'); // Don't use stok accessor (slow)
            }])
            ->select('id', 'bahan_baku_id', 'jumlah', 'satuan', 'harga_satuan', 'subtotal', 'keterangan')
            ->get();
            
        // Transform data to match expected format
        $transformedData = $biayaBahanBaku->map(function($item) {
            return [
                'id' => $item->id,
                'nama_bahan' => $item->bahanBaku->nama_bahan ?? 'Unknown',
                'jumlah' => $item->jumlah,
                'satuan' => $item->satuan,
                'harga_satuan' => $item->harga_satuan,
                'subtotal' => $item->subtotal,
                'stok' => $item->bahanBaku->saldo_awal ?? 0, // Use saldo_awal instead of stok accessor
                'keterangan' => $item->keterangan
            ];
        });
            
        return response()->json($transformedData);
    }

    public function getAvailableBtkl($produk_id)
    {
        // Optimized query with specific columns only
        $prosesProduksi = \App\Models\ProsesProduksi::where('user_id', auth()->id())
            ->select('id', 'nama_proses', 'tarif_per_produk', 'jumlah_pegawai', 'kode_proses')
            ->get();
            
        // Transform data to include calculated costs
        $transformedData = $prosesProduksi->map(function($item) {
            $tarifPerProduk = $item->tarif_per_produk ?? 0;
            $jumlahPegawai = $item->jumlah_pegawai ?? 1;
            $totalBiaya = $tarifPerProduk * $jumlahPegawai;
            
            return [
                'id' => $item->id,
                'nama_proses' => $item->nama_proses ?? 'Proses Produksi',
                'tarif_per_produk' => $tarifPerProduk,
                'biaya_per_produk' => $totalBiaya,
                'kode_proses' => $item->kode_proses,
                'jumlah_pegawai' => $jumlahPegawai
            ];
        });
            
        return response()->json($transformedData);
    }

    public function getAvailableBop()
    {
        // Optimized query with specific columns only
        $bopProses = \App\Models\BopProses::where('user_id', auth()->id())
            ->where('is_active', true)
            ->select('id', 'nama_bop_proses', 'komponen_bahan_pendukung', 'komponen_lainnya', 'total_bop_per_produk')
            ->get();
        
        // Transform data to match expected format
        $transformedData = $bopProses->map(function($item) {
            return [
                'id' => $item->id,
                'nama_bop_proses' => $item->nama_bop_proses ?? 'BOP Proses',
                'komponen_bahan_pendukung' => $item->komponen_bahan_pendukung ?? [],
                'komponen_lainnya' => $item->komponen_lainnya ?? [],
                'total_bop_per_produk' => $item->total_bop_per_produk ?? 0,
                'tarif' => $item->total_bop_per_produk ?? 0,
                'satuan' => 'Unit',
            ];
        });
        
        return response()->json($transformedData);
    }
}

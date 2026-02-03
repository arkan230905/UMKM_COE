<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\BomCalculationService;

class BomController extends Controller
{
    protected $bomCalculationService;

    public function __construct(BomCalculationService $bomCalculationService)
    {
        $this->middleware('auth');
        $this->bomCalculationService = $bomCalculationService;
    }

    public function index(Request $request)
    {
        // Get all products with their BOM data
        $query = Produk::with(['boms', 'bomJobCosting', 'satuan']);
        
        // Filter by product name
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
        }
        
        // Filter by BOM status
        if ($request->filled('status')) {
            if ($request->status == 'ada') {
                $query->whereHas('boms');
            } elseif ($request->status == 'belum') {
                $query->whereDoesntHave('boms');
            }
        }
        
        $produks = $query->orderBy('nama_produk')->paginate(15);
        
        // Add BTKL data to each product
        foreach ($produks as $produk) {
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $btklCount = 0;
            
            // Calculate actual biaya bahan from BOM details
            $bom = $produk->boms->first();
            if ($bom) {
                $bomDetails = \App\Models\BomDetail::where('bom_id', $bom->id)->get();
                $totalBiayaBahan = $bomDetails->sum('total_harga');
            }
            
            // Get BomJobCosting for this product
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            if ($bomJobCosting) {
                // Get BTKL data with biaya per produk (biaya_per_produk = tarif_per_jam ÷ kapasitas_per_jam)
                $bomJobBtkl = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                    ->join('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
                    ->join('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select(
                        'bom_job_btkl.*', 
                        'btkls.kode_proses',
                        'btkls.nama_btkl',
                        'btkls.tarif_per_jam', 
                        'btkls.kapasitas_per_jam',
                        'btkls.satuan',
                        'btkls.deskripsi_proses',
                        'jabatans.nama as nama_jabatan',
                        'jabatans.kategori'
                    )
                    ->get();
                
                $btklCount = $bomJobBtkl->count();
                
                // Use subtotal yang sudah dihitung dengan benar di database
                $totalBTKL = $bomJobBtkl->sum('subtotal');
                
                // Also add bahan pendukung to biaya bahan
                $bahanPendukung = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->sum('subtotal');
                $totalBiayaBahan += $bahanPendukung;
            }
            
            // Add data to product
            $produk->total_biaya_bahan = $totalBiayaBahan;
            $produk->total_btkl = $totalBTKL;
            $produk->btkl_count = $btklCount;
            $produk->has_btkl = $btklCount > 0;
            
            // Add BOP data
            $produk->total_bop = $bomJobCosting->total_bop ?? 0;
        }
        
        return view('master-data.bom.index', compact('produks'));
    }

    public function updateBOMCosts(Request $request, $produkId)
    {
        try {
            $validated = $request->validate([
                'total_bop' => 'required|numeric|min:0'
            ]);

            $produk = Produk::findOrFail($produkId);
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produkId)->first();
            
            if ($bomJobCosting) {
                $bomJobCosting->total_bop = $validated['total_bop'];
                $bomJobCosting->save();
            }

            // Recalculate BOM
            $bom = Bom::where('produk_id', $produkId)->first();
            if ($bom) {
                \App\Services\BomSyncService::recalculateBomCosts($bom);
            }

            return redirect()->route('master-data.bom.index')
                ->with('success', 'BOP berhasil diperbarui dan BOM dihitung ulang untuk ' . $produk->nama_produk);

        } catch (\Exception $e) {
            return redirect()->route('master-data.bom.index')
                ->with('error', 'Gagal memperbarui BOP: ' . $e->getMessage());
        }
    }

    public function updateBOP(Request $request)
    {
        try {
            $validated = $request->validate([
                'bom_job_costing_id' => 'required|exists:bom_job_costings,id',
                'produk_id' => 'required|exists:produks,id',
                'total_bop' => 'required|numeric|min:0'
            ]);

            $bomJobCosting = \App\Models\BomJobCosting::find($validated['bom_job_costing_id']);
            $bomJobCosting->total_bop = $validated['total_bop'];
            $bomJobCosting->save();

            // Recalculate BOM total
            $bom = Bom::where('produk_id', $validated['produk_id'])->first();
            if ($bom) {
                \App\Services\BomSyncService::recalculateBomCosts($bom);
                $newTotal = $bom->fresh()->total_biaya;
            } else {
                $newTotal = 0;
            }

            return response()->json([
                'success' => true,
                'message' => 'BOP berhasil diperbarui',
                'new_total' => $newTotal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui BOP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // Ambil data BOM dengan relasi yang diperlukan
            $bom = Bom::with([
                'produk',
                'details' => function($query) {
                    $query->with(['bahanBaku' => function($q) {
                        $q->with('satuan')->withDefault();
                    }]);
                },
                'proses' => function($query) {
                    $query->with(['prosesProduksi', 'bomProsesBops.komponenBop'])
                        ->orderBy('urutan');
                }
            ])->findOrFail($id);

            // Get BomJobCosting untuk data BTKL yang benar
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $bom->produk_id)
                ->first();

            // Get BTKL data dari bom_job_btkl dengan biaya per produk yang benar
            $btklData = [];
            if ($bomJobCosting) {
                $btklData = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                    ->join('proses_produksis', 'bom_job_btkl.proses_produksi_id', '=', 'proses_produksis.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select('bom_job_btkl.*', 'proses_produksis.tarif_btkl as tarif_per_jam', 'proses_produksis.kapasitas_per_jam')
                    ->get()
                    ->map(function($item) {
                        return [
                            'nama_proses' => $item->nama_proses,
                            'durasi_jam' => $item->durasi_jam,
                            'tarif_per_jam' => $item->tarif_per_jam,
                            'kapasitas_per_jam' => $item->kapasitas_per_jam,
                            'biaya_per_produk' => ($item->tarif_per_jam / ($item->kapasitas_per_jam ?? 1)) * $item->durasi_jam,
                            'subtotal' => $item->subtotal
                        ];
                    });
            }

            return view('master-data.bom.show', compact('bom', 'btklData'));
        } catch (\Exception $e) {
            \Log::error('Error in BomController@show: ' . $e->getMessage());
            return redirect()->route('master-data.bom.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan detail BOM: ' . $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            // Get products that don't have BOM yet
            $produkIdsWithBom = Bom::pluck('produk_id')->toArray();
            $produks = Produk::whereNotIn('id', $produkIdsWithBom)->get();
            
            if ($produks->isEmpty()) {
                return redirect()->route('master-data.bom.index')
                    ->with('info', 'Semua produk sudah memiliki BOM. Tidak ada produk yang bisa ditambahkan BOM-nya.');
            }
            
            return view('master-data.bom.create', compact('produks'));
            
        } catch (\Exception $e) {
            return redirect()->route('master-data.bom.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Basic implementation - can be expanded as needed
        try {
            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id|unique:boms,produk_id',
                'total_biaya' => 'required|numeric|min:0',
            ]);

            $bom = Bom::create([
                'produk_id' => $validated['produk_id'],
                'kode_bom' => 'BOM-' . str_pad($validated['produk_id'], 3, '0', STR_PAD_LEFT),
                'total_biaya' => $validated['total_biaya'],
                'catatan' => 'Bill of Materials untuk ' . Produk::find($validated['produk_id'])->nama_produk,
            ]);

            return redirect()->route('master-data.bom.show', $bom->id)
                ->with('success', 'BOM berhasil dibuat.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat BOM: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $bom = Bom::findOrFail($id);
            $bom->delete();

            return redirect()->route('master-data.bom.index')
                ->with('success', 'BOM berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus BOM: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        try {
            $bom = Bom::with(['produk', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuan'])->findOrFail($id);
            
            return view('master-data.bom.print', compact('bom'));
            
        } catch (\Exception $e) {
            return redirect()->route('master-data.bom.index')
                ->with('error', 'Terjadi kesalahan saat mencetak BOM: ' . $e->getMessage());
        }
    }

    // Add other methods as needed
}

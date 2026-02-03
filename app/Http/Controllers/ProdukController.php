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

class ProdukController extends Controller
{
    public function index()
    {
        // Get all products with real-time BOM costs
        $produks = Produk::select([
            'id', 
            'barcode',
            'nama_produk', 
            'deskripsi',
            'foto',
            'harga_bom',
            'harga_jual', 
            'margin_percent',
            'stok'
        ])->get();
        
        // Update harga_bom with real-time BOM calculation (sama seperti di BomController)
        foreach ($produks as $produk) {
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            
            // Calculate actual biaya bahan from BOM details
            $bom = \App\Models\Bom::where('produk_id', $produk->id)->first();
            if ($bom) {
                $bomDetails = \App\Models\BomDetail::where('bom_id', $bom->id)->get();
                $totalBiayaBahan = $bomDetails->sum('total_harga');
            }
            
            // Get BomJobCosting for this product
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            if ($bomJobCosting) {
                // Get ALL BTKL data (sama seperti di halaman detail BOM)
                $bomJobBtkl = \Illuminate\Support\Facades\DB::table('btkls')
                    ->join('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                    ->leftJoin('pegawais', 'jabatans.nama', '=', 'pegawais.jabatan')
                    ->select(
                        'btkls.id',
                        'btkls.kode_proses',
                        'btkls.nama_btkl',
                        'btkls.jabatan_id',
                        'btkls.tarif_per_jam',
                        'btkls.kapasitas_per_jam',
                        'btkls.satuan',
                        'btkls.deskripsi_proses',
                        'jabatans.nama as nama_jabatan',
                        'jabatans.kategori'
                    )
                    ->selectRaw('COUNT(pegawais.id) as jumlah_pegawai')
                    ->groupBy(
                        'btkls.id',
                        'btkls.kode_proses',
                        'btkls.nama_btkl',
                        'btkls.jabatan_id',
                        'btkls.tarif_per_jam',
                        'btkls.kapasitas_per_jam',
                        'btkls.satuan',
                        'btkls.deskripsi_proses',
                        'jabatans.nama',
                        'jabatans.kategori'
                    )
                    ->get();
                
                // Calculate total BTKL with real-time pegawai count
                foreach ($bomJobBtkl as $btkl) {
                    $tarifBtklTotal = $btkl->jumlah_pegawai * $btkl->tarif_per_jam;
                    $biayaPerProduk = $btkl->kapasitas_per_jam > 0 ? $tarifBtklTotal / $btkl->kapasitas_per_jam : 0;
                    $totalBTKL += $biayaPerProduk;
                }
                
                // Also add bahan pendukung to biaya bahan
                $bahanPendukung = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->sum('subtotal');
                $totalBiayaBahan += $bahanPendukung;
            }
            
            // Add BOP data
            $totalBOP = $bomJobCosting->total_bop ?? 0;
            
            // Calculate total BOM cost (sama seperti di BomController)
            $totalBiayaBOM = $totalBiayaBahan + $totalBTKL + $totalBOP;
            
            // Update harga_bom with real-time calculation
            $produk->harga_bom = $totalBiayaBOM;
            
            // Update database to sync
            $produk->save();
            
            // Add real-time stock calculation (sama seperti di StokController)
            $stockService = new \App\Services\StockService();
            $produk->stok_realtime = $stockService->getAvailableQty('product', $produk->id);
        }
        
        return view('master-data.produk.index', compact('produks'));
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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
}

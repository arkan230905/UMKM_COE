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
        // Get all products
        $produks = Produk::with(['bomJobCosting'])->get();
        
        // Calculate HPP using the same logic as HPP page (BomController@index)
        $hargaBom = [];
        foreach ($produks as $produk) {
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $totalBOP = 0;
            
            $bomJobCosting = $produk->bomJobCosting;
            
            if ($bomJobCosting) {
                // Use data from BomJobCosting for bahan
                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                
                // Calculate BTKL real-time from bom_job_btkl with correct formula
                $btklData = DB::table('bom_job_btkl')
                    ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
                    ->leftJoin('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select(
                        'bom_job_btkl.*',
                        'btkls.kapasitas_per_jam',
                        'jabatans.id as jabatan_id',
                        'jabatans.tarif as tarif_jabatan'
                    )
                    ->get();
                
                $totalBTKL = 0;
                foreach ($btklData as $btkl) {
                    if ($btkl->jabatan_id) {
                        // Get jumlah pegawai for this jabatan
                        $jumlahPegawai = DB::table('pegawais')
                            ->join('jabatans', 'pegawais.jabatan', '=', 'jabatans.nama')
                            ->where('jabatans.id', $btkl->jabatan_id)
                            ->count();
                        
                        // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                        $tarifBtkl = $jumlahPegawai * ($btkl->tarif_jabatan ?? 0);
                        
                        // Calculate biaya per produk: Tarif BTKL ÷ Kapasitas per Jam
                        $kapasitasPerJam = $btkl->kapasitas_per_jam ?? 1;
                        $biayaPerProduk = $kapasitasPerJam > 0 ? $tarifBtkl / $kapasitasPerJam : 0;
                        
                        $totalBTKL += $biayaPerProduk;
                    }
                }
                
                // Calculate BOP using the same logic as HPP page
                $bopData = DB::table('bom_job_bop')
                    ->where('bom_job_bop.bom_job_costing_id', $bomJobCosting->id)
                    ->select('bom_job_bop.*')
                    ->get();
                    
                $totalBOP = 0;
                if (!empty($bopData) && count($bopData) > 0) {
                    // Group BOP by process and sum the tariffs
                    $bopByProcess = [];
                    foreach ($bopData as $bop) {
                        $namaBiaya = strtolower($bop->nama_bop ?? '');
                        $prosesName = 'Umum';
                        
                        if (stripos($namaBiaya, 'penggorengan') !== false) {
                            $prosesName = 'Menggoreng';
                        } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                            $prosesName = 'Perbumbuan';
                        } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                            $prosesName = 'Packing';
                        }
                        
                        if (!isset($bopByProcess[$prosesName])) {
                            $bopByProcess[$prosesName] = 0;
                        }
                        $bopByProcess[$prosesName] += $bop->tarif;
                    }
                    
                    // Sum all BOP tariffs
                    foreach ($bopByProcess as $prosesName => $totalTarif) {
                        $totalBOP += $totalTarif;
                    }
                }
                
                // If BOP data is empty or incorrect, use standard BOP values
                if ($totalBOP == 0 || $totalBOP > 50000) {
                    $totalBOP = 1740 + 290 + 1160; // Total = 3.190
                }
            }
            
            // Calculate total HPP: Biaya Bahan + BTKL + BOP
            $totalBiayaHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
            $hargaBom[$produk->id] = $totalBiayaHPP;
            
            // Update harga_pokok in produks table if different
            if ($produk->harga_pokok != $totalBiayaHPP) {
                DB::table('produks')
                    ->where('id', $produk->id)
                    ->update([
                        'harga_pokok' => $totalBiayaHPP,
                        'updated_at' => now()
                    ]);
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
            'harga_jual' => 'required|string',
            'hpp' => 'nullable|numeric|min:0',
            'margin_percent' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        // Parse formatted harga_jual (remove dots) back to pure number
        $hargaJualFormatted = $request->input('harga_jual');
        $hargaJual = intval(str_replace('.', '', $hargaJualFormatted));
        
        // Debug: log the values
        \Log::info('ProdukController::update - Debug harga_jual');
        \Log::info('Raw harga_jual from form: ' . $hargaJualFormatted);
        \Log::info('Parsed harga_jual: ' . $hargaJual);
        \Log::info('HPP from request: ' . $request->input('hpp'));
        \Log::info('Margin percent from request: ' . $request->input('margin_percent'));

        $data = [
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga_jual' => $hargaJual,
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
        ];

        // Calculate margin_percent from harga_jual and hpp
        $hpp = $request->input('hpp', $produk->hpp ?? $produk->getActualHPP());
        
        if ($hpp > 0 && $hargaJual > 0) {
            $marginPercent = (($hargaJual - $hpp) / $hpp) * 100;
            $data['margin_percent'] = $marginPercent;
        } else {
            $data['margin_percent'] = 0;
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $produk->update($data);

        // Debug: log the stored values after update
        \Log::info('ProdukController::update - After update');
        \Log::info('Stored harga_jual in database: ' . $produk->harga_jual);
        \Log::info('Stored margin_percent in database: ' . $produk->margin_percent);
        \Log::info('Final data array: ' . json_encode($data));

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
        $query = Produk::with(['bomJobCosting'])
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

        // Calculate HPP and Harga Jual for each product (same logic as master-data/produk)
        foreach ($produks as $produk) {
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $totalBOP = 0;
            
            $bomJobCosting = $produk->bomJobCosting;
            
            if ($bomJobCosting) {
                // Use data from BomJobCosting for bahan
                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                
                // Calculate BTKL real-time from bom_job_btkl with correct formula
                $btklData = DB::table('bom_job_btkl')
                    ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
                    ->leftJoin('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select(
                        'bom_job_btkl.*',
                        'btkls.kapasitas_per_jam',
                        'jabatans.id as jabatan_id',
                        'jabatans.tarif as tarif_jabatan'
                    )
                    ->get();
                
                $totalBTKL = 0;
                foreach ($btklData as $btkl) {
                    if ($btkl->jabatan_id) {
                        // Get jumlah pegawai for this jabatan
                        $jumlahPegawai = DB::table('pegawais')
                            ->join('jabatans', 'pegawais.jabatan', '=', 'jabatans.nama')
                            ->where('jabatans.id', $btkl->jabatan_id)
                            ->count();
                        
                        // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                        $tarifBtkl = $jumlahPegawai * ($btkl->tarif_jabatan ?? 0);
                        
                        // Calculate biaya per produk: Tarif BTKL ÷ Kapasitas per Jam
                        $kapasitasPerJam = $btkl->kapasitas_per_jam ?? 1;
                        $biayaPerProduk = $kapasitasPerJam > 0 ? $tarifBtkl / $kapasitasPerJam : 0;
                        
                        $totalBTKL += $biayaPerProduk;
                    }
                }
                
                // Calculate BOP using the same logic as HPP page
                $bopData = DB::table('bom_job_bop')
                    ->where('bom_job_bop.bom_job_costing_id', $bomJobCosting->id)
                    ->select('bom_job_bop.*')
                    ->get();
                    
                $totalBOP = 0;
                if (!empty($bopData) && count($bopData) > 0) {
                    // Group BOP by process and sum the tariffs
                    $bopByProcess = [];
                    foreach ($bopData as $bop) {
                        $namaBiaya = strtolower($bop->nama_bop ?? '');
                        $prosesName = 'Umum';
                        
                        if (stripos($namaBiaya, 'penggorengan') !== false) {
                            $prosesName = 'Menggoreng';
                        } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                            $prosesName = 'Perbumbuan';
                        } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                            $prosesName = 'Packing';
                        }
                        
                        if (!isset($bopByProcess[$prosesName])) {
                            $bopByProcess[$prosesName] = 0;
                        }
                        $bopByProcess[$prosesName] += $bop->tarif;
                    }
                    
                    // Sum all BOP tariffs
                    foreach ($bopByProcess as $prosesName => $totalTarif) {
                        $totalBOP += $totalTarif;
                    }
                }
                
                // If BOP data is empty or incorrect, use standard BOP values
                if ($totalBOP == 0 || $totalBOP > 50000) {
                    $totalBOP = 1740 + 290 + 1160; // Total = 3.190
                }
            }
            
            // Calculate total HPP: Biaya Bahan + BTKL + BOP
            $totalBiayaHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
            
            // Get margin percentage (default 30%)
            $margin = (float) ($produk->margin_percent ?? 30);
            
            // Calculate selling price: HPP + (HPP * margin%)
            // Formula: Harga Jual = HPP * (1 + margin/100)
            $hargaJual = $totalBiayaHPP * (1 + ($margin / 100));
            
            // Set calculated harga_jual to the product object
            $produk->harga_jual = $hargaJual;
            
            // Update harga_pokok and harga_jual in database if different
            if ($produk->harga_pokok != $totalBiayaHPP || $produk->getOriginal('harga_jual') != $hargaJual) {
                DB::table('produks')
                    ->where('id', $produk->id)
                    ->update([
                        'harga_pokok' => $totalBiayaHPP,
                        'harga_jual' => $hargaJual,
                        'updated_at' => now()
                    ]);
            }
        }

        return view('catalog.index', compact('produks'));
    }
}

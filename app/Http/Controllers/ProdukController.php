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
        
        // Calculate HPP using ONLY data from BomJobCosting (same as harga-pokok-produksi page)
        $hargaBom = [];
        foreach ($produks as $produk) {
            $totalBiayaHPP = 0;
            
            $bomJobCosting = $produk->bomJobCosting;
            
            // Only use data if BomJobCosting exists AND has complete data
            // This matches the logic in BomController@index for harga-pokok-produksi page
            if ($bomJobCosting) {
                // Check if this product would appear in harga-pokok-produksi page
                // Complete means: has biaya bahan AND has BTKL AND has BOP
                $hasBiayaBahan = ($bomJobCosting->total_bbb > 0) || ($bomJobCosting->total_bahan_pendukung > 0);
                $hasBTKL = $bomJobCosting->total_btkl > 0;
                $hasBOP = $bomJobCosting->total_bop > 0;
                
                // Only calculate HPP if product has complete data (same as harga-pokok-produksi page)
                if ($hasBiayaBahan && $hasBTKL && $hasBOP) {
                    // Use stored values from BomJobCosting table
                    $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                    $totalBTKL = $bomJobCosting->total_btkl;
                    $totalBOP = $bomJobCosting->total_bop;
                    
                    // Calculate total HPP: Biaya Bahan + BTKL + BOP
                    $totalBiayaHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
                }
            }
            
            // If no complete BomJobCosting data, HPP = 0 (same as harga-pokok-produksi page)
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
            'harga_jual' => 'required|numeric|min:0',
            'hpp' => 'nullable|numeric|min:0',
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

        // Parse harga_jual from formatted string (remove dots)
        $hargaJual = (int) str_replace('.', '', $request->input('harga_jual'));

        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'harga_jual' => $hargaJual,
            'hpp' => $request->input('hpp', 0),
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
        // Handle photo removal request
        if ($request->has('remove_photo')) {
            if ($produk->foto) {
                // Delete old photo file
                $oldPhotoPath = 'public/' . $produk->foto;
                if (\Storage::exists($oldPhotoPath)) {
                    \Storage::delete($oldPhotoPath);
                }
                
                $produk->update(['foto' => null]);
                
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'message' => 'Foto produk berhasil dihapus.']);
                }
                
                return redirect()->back()->with('success', 'Foto produk berhasil dihapus.');
            }
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada foto untuk dihapus.']);
            }
            
            return redirect()->back()->with('error', 'Tidak ada foto untuk dihapus.');
        }

        // Handle photo-only update (for AJAX requests from kelola-catalog)
        if ($request->ajax() && $request->hasFile('foto') && !$request->has('nama_produk')) {
            $request->validate([
                'foto' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            ]);

            try {
                // Delete old photo if exists
                if ($produk->foto) {
                    $oldPhotoPath = 'public/' . $produk->foto;
                    if (\Storage::exists($oldPhotoPath)) {
                        \Storage::delete($oldPhotoPath);
                    }
                }

                // Store new photo
                $fotoPath = $request->file('foto')->store('produk', 'public');
                $produk->update(['foto' => $fotoPath]);

                return response()->json(['success' => true, 'message' => 'Foto produk berhasil diperbarui.']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Gagal mengupload foto: ' . $e->getMessage()]);
            }
        }

        // Regular update validation
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'harga_jual' => 'required|string',
            'hpp' => 'nullable|numeric|min:0',
            'hpp_calculated' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        // Parse formatted harga_jual (remove dots) back to pure number
        $hargaJualFormatted = $request->input('harga_jual');
        $hargaJual = intval(str_replace('.', '', $hargaJualFormatted));
        
        // Use calculated HPP from BomJobCosting if available, otherwise use the old HPP
        $hppCalculated = $request->input('hpp_calculated');
        $hppToUse = !empty($hppCalculated) && $hppCalculated > 0 ? $hppCalculated : $request->input('hpp');
        
        // Debug: log the values
        \Log::info('ProdukController::update - Debug harga_jual and HPP');
        \Log::info('Raw harga_jual from form: ' . $hargaJualFormatted);
        \Log::info('Parsed harga_jual: ' . $hargaJual);
        \Log::info('HPP calculated (from BomJobCosting): ' . $hppCalculated);
        \Log::info('HPP old (from produk): ' . $request->input('hpp'));
        \Log::info('HPP to use: ' . $hppToUse);

        $data = [
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga_jual' => $hargaJual,
            'hpp' => $hppToUse,
            'harga_bom' => $hppToUse, // Update harga_bom to match HPP
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
        ];

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($produk->foto) {
                $oldPhotoPath = 'public/' . $produk->foto;
                if (\Storage::exists($oldPhotoPath)) {
                    \Storage::delete($oldPhotoPath);
                }
            }
            
            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $produk->update($data);

        // Debug: log the stored values after update
        \Log::info('ProdukController::update - After update');
        \Log::info('Stored harga_jual in database: ' . $produk->harga_jual);
        \Log::info('Stored hpp in database: ' . $produk->hpp);
        \Log::info('Final data array: ' . json_encode($data));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Produk berhasil diperbarui.']);
        }

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
        // Get current logged-in company
        $company = null;
        if (auth()->check()) {
            $user = auth()->user();
            $company = \App\Models\Perusahaan::find($user->perusahaan_id);
        }

        // Get products with stock and pricing info for current company
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
                    $totalBOP = 0; // Set to 0 instead of 3.190
                }
            }
            
            // Calculate total HPP: Biaya Bahan + BTKL + BOP
            $totalBiayaHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
            
            // Get margin percentage (default 30%)
            $margin = (float) ($produk->margin_percent ?? 30);
            
            // Calculate selling price: HPP + (HPP * margin%)
            // Formula: Harga Jual = HPP * (1 + margin/100)
            $hargaJual = $totalBiayaHPP * (1 + ($margin / 100));
            
            // Gunakan harga_jual yang sudah ada di database, jangan override dengan calculated
            // Jika harga_jual kosong atau 0, maka gunakan calculated harga_jual
            if (empty($produk->harga_jual) || $produk->harga_jual == 0) {
                $produk->harga_jual = $hargaJual;
            }
            
            // Update harga_pokok saja, jangan update harga_jual yang sudah diset manual
            if ($produk->harga_pokok != $totalBiayaHPP) {
                DB::table('produks')
                    ->where('id', $produk->id)
                    ->update([
                        'harga_pokok' => $totalBiayaHPP,
                        'updated_at' => now()
                    ]);
            }
        }

        // Get catalog photos for hero slider
        $catalogPhotos = [];
        if ($company) {
            $catalogPhotos = $company->catalogPhotos()->active()->get();
        }

        return view('catalog.index', compact('produks', 'company', 'catalogPhotos'));
    }
}

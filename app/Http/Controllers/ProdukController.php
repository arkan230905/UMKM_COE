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
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $produks = Produk::where('user_id', auth()->id())
            ->get();
        
        // Calculate HPP from Harga Pokok Produksi (BBB + BTKL + BOP)
        $hargaBom = [];
        foreach ($produks as $produk) {
            // Use getActualHPP() method which gets HPP from harga-pokok-produksi
            $totalBiayaHPP = $produk->getActualHPP();
            $hargaBom[$produk->id] = $totalBiayaHPP;
        }
        
        return view('master-data.produk.index', compact('produks', 'hargaBom'));
    }

    public function katalogPelanggan()
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $produks = Produk::select([
            'id',
            'nama_produk',
            'foto',
            'deskripsi',
            'harga_jual',
            'stok',
        ])->where('user_id', auth()->id())
          ->whereNotNull('harga_jual')
          ->get();

        return view('pelanggan.produk.index', compact('produks'));
    }

    public function create()
    {
        return view('master-data.produk.create');
    }
    
    public function show($id)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::with(['boms.details.bahanBaku'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
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
            
            // Copy file to public/storage for web access
            $sourcePath = storage_path('app/public/' . $fotoPath);
            $targetPath = public_path('storage/' . $fotoPath);
            
            // Ensure target directory exists
            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            // Copy file for web access
            copy($sourcePath, $targetPath);
        }

        // Parse harga_jual from formatted string (remove dots)
        $hargaJual = (int) str_replace('.', '', $request->input('harga_jual'));

        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'harga_jual' => $hargaJual,
            'btkl_per_unit' => $request->input('btkl_per_unit'),
            'user_id' => auth()->id(), // 🔒 SECURITY: Add user_id
        ]);

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan. Silakan tambahkan BOM.');
    }

    public function edit(Produk $produk)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::where('user_id', auth()->id())->findOrFail($produk->id);
        
        return view('master-data.produk.edit', compact('produk'));
    }
    
    /**
     * Print barcode labels for a product
     */
    public function printBarcode(Produk $produk)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::where('user_id', auth()->id())->findOrFail($produk->id);
        
        return view('master-data.produk.print-barcode', compact('produk'));
    }
    
    /**
     * Print barcode labels for all products
     */
    public function printBarcodeAll()
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produks = Produk::where('user_id', auth()->id())
            ->whereNotNull('barcode')
            ->get();
        return view('master-data.produk.print-barcode-bulk', compact('produks'));
    }

    public function update(Request $request, Produk $produk)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::where('user_id', auth()->id())->findOrFail($produk->id);
        
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
            'btkl_per_unit' => $request->input('btkl_per_unit'),
        ];

        if ($request->hasFile('foto')) {
            // Debug: Log file upload
            \Log::info('ProdukController::update - Foto upload detected');
            \Log::info('Original filename: ' . $request->file('foto')->getClientOriginalName());
            \Log::info('File size: ' . $request->file('foto')->getSize());
            
            // Delete old photo if exists
            if ($produk->foto) {
                $oldPhotoPath = 'produk/' . basename($produk->foto);
                \Log::info('Deleting old photo: ' . $oldPhotoPath);
                
                // Try multiple path approaches
                $pathsToDelete = [
                    'public/' . $produk->foto,
                    'produk/' . basename($produk->foto),
                    $produk->foto
                ];
                
                foreach ($pathsToDelete as $path) {
                    if (\Storage::disk('public')->exists($path)) {
                        \Storage::disk('public')->delete($path);
                        \Log::info('Old photo deleted successfully: ' . $path);
                        break;
                    }
                }
            }
            
            // Store new photo with explicit disk
            $storedPath = $request->file('foto')->store('produk', 'public');
            $data['foto'] = $storedPath;
            
            \Log::info('New photo stored at: ' . $storedPath);
            \Log::info('Full storage path: ' . storage_path('app/public/' . $storedPath));
            \Log::info('Public path: ' . public_path('storage/' . $storedPath));
            
            // Copy file to public/storage for web access
            $sourcePath = storage_path('app/public/' . $storedPath);
            $targetPath = public_path('storage/' . $storedPath);
            
            // Ensure target directory exists
            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            // Copy file for web access
            if (copy($sourcePath, $targetPath)) {
                \Log::info('File copied to public/storage for web access: ' . $targetPath);
            } else {
                \Log::error('Failed to copy file to public/storage: ' . $targetPath);
            }
            
            // Verify file exists after storage
            $fullPath = storage_path('app/public/' . $storedPath);
            \Log::info('File exists after storage: ' . (file_exists($fullPath) ? 'YES' : 'NO'));
            \Log::info('Public file exists: ' . (file_exists($targetPath) ? 'YES' : 'NO'));
            
            // Also verify via Storage facade
            \Log::info('Storage exists check: ' . (\Storage::disk('public')->exists($storedPath) ? 'YES' : 'NO'));
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
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::where('user_id', auth()->id())->findOrFail($produk->id);
        
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
        $query = Produk::where('stok', '>=', 0); // Show all products including those with zero stock

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

        // Use existing harga_pokok and harga_jual from produks table
        foreach ($produks as $produk) {
            // Use existing harga_pokok from database
            $totalBiayaHPP = $produk->harga_pokok ?? 0;
            
            // Use existing harga_jual from database, or calculate if empty
            if (empty($produk->harga_jual) || $produk->harga_jual == 0) {
                // Get margin percentage (default 30%)
                $margin = 30; // Fixed margin since margin_percent column was removed
                
                // Calculate selling price: HPP + (HPP * margin%)
                $hargaJual = $totalBiayaHPP * (1 + ($margin / 100));
                $produk->harga_jual = $hargaJual;
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

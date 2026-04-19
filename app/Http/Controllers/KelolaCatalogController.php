<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KelolaCatalogController extends Controller
{
    /**
     * Display catalog management page
     */
    public function index(Request $request)
    {
        // Get current company
        $company = null;
        if (Auth::check()) {
            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);
        }

        // Get products with search and filters
        $query = Produk::with(['bomJobCosting']);

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_produk', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('barcode', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply stock filter
        if ($request->has('stock_filter') && $request->stock_filter !== 'all') {
            switch ($request->stock_filter) {
                case 'available':
                    $query->where('stok', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stok', '<=', 0);
                    break;
                case 'low_stock':
                    $query->where('stok', '>', 0)->where('stok', '<=', 10);
                    break;
            }
        }

        // Apply price filter
        if ($request->has('price_filter') && $request->price_filter !== 'all') {
            switch ($request->price_filter) {
                case 'under_10k':
                    $query->where('harga_jual', '<', 10000);
                    break;
                case '10k_50k':
                    $query->where('harga_jual', '>=', 10000)->where('harga_jual', '<=', 50000);
                    break;
                case '50k_100k':
                    $query->where('harga_jual', '>=', 50000)->where('harga_jual', '<=', 100000);
                    break;
                case 'over_100k':
                    $query->where('harga_jual', '>', 100000);
                    break;
            }
        }

        $produks = $query->orderBy('nama_produk', 'asc')->paginate(12);

        // Calculate HPP for each product
        foreach ($produks as $produk) {
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $totalBOP = 0;
            
            $bomJobCosting = $produk->bomJobCosting;
            
            if ($bomJobCosting) {
                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                $totalBOP = $bomJobCosting->total_bop > 0 && $bomJobCosting->total_bop <= 50000 ? $bomJobCosting->total_bop : 0;
            }
            
            $totalBiayaHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
            $produk->hpp_calculated = $totalBiayaHPP;
            
            // Calculate margin percentage
            if ($produk->harga_jual > 0) {
                $margin = (($produk->harga_jual - $totalBiayaHPP) / $produk->harga_jual) * 100;
                $produk->margin_percentage = round($margin, 2);
            } else {
                $produk->margin_percentage = 0;
            }
        }

        return view('kelola-catalog.index', compact('produks', 'company'));
    }

    /**
     * Show the form for editing catalog settings
     */
    public function settings()
    {
        $company = null;
        if (Auth::check()) {
            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);
        }

        return view('kelola-catalog.settings', compact('company'));
    }

    /**
     * Update catalog settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $company = Perusahaan::find($user->perusahaan_id);

        if (!$company) {
            return redirect()->back()->with('error', 'Perusahaan tidak ditemukan.');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'telepon' => 'required|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($company->foto && Storage::disk('public')->exists($company->foto)) {
                Storage::disk('public')->delete($company->foto);
            }

            $file = $request->file('foto');
            $filename = 'company_' . $company->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('company-photos', $filename, 'public');
            $company->foto = $path;
        }

        $company->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'email' => $request->email,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('kelola-catalog.settings')
            ->with('success', 'Pengaturan catalog berhasil diperbarui.');
    }

    /**
     * Toggle product visibility in catalog
     */
    public function toggleVisibility($id)
    {
        $produk = Produk::findOrFail($id);
        
        // Toggle visibility status
        $produk->show_in_catalog = !$produk->show_in_catalog;
        $produk->save();

        $status = $produk->show_in_catalog ? 'ditampilkan' : 'disembunyikan';
        
        return redirect()->back()->with('success', "Produk {$produk->nama_produk} berhasil {$status} di catalog.");
    }

    /**
     * Update multiple products visibility
     */
    public function bulkUpdateVisibility(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:produks,id',
            'action' => 'required|in:show,hide',
        ]);

        $productIds = $request->product_ids;
        $showInCatalog = $request->action === 'show';

        Produk::whereIn('id', $productIds)->update(['show_in_catalog' => $showInCatalog]);

        $count = count($productIds);
        $action = $showInCatalog ? 'ditampilkan' : 'disembunyikan';

        return redirect()->back()->with('success', "{$count} produk berhasil {$action} di catalog.");
    }

    /**
     * Update product catalog info
     */
    public function updateProductCatalog(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'deskripsi_catalog' => 'nullable|string|max:500',
            'show_in_catalog' => 'sometimes|boolean',
        ]);

        $produk->update([
            'deskripsi_catalog' => $request->deskripsi_catalog,
            'show_in_catalog' => $request->has('show_in_catalog') ? $request->show_in_catalog : $produk->show_in_catalog,
        ]);

        return redirect()->back()->with('success', 'Informasi catalog produk berhasil diperbarui.');
    }
}

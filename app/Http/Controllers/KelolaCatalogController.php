<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Perusahaan;
use App\Models\CatalogPhoto;
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

        // Get catalog photos
        $catalogPhotos = [];
        if ($company) {
            $catalogPhotos = $company->catalogPhotos()->active()->get();
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

        return view('kelola-catalog.index', compact('produks', 'company', 'catalogPhotos'));
    }

    /**
     * Display catalog preview with inline editing
     */
    public function preview(Request $request)
    {
        // Get current company
        $company = null;
        if (Auth::check()) {
            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);
        }

        // Get catalog photos
        $catalogPhotos = [];
        if ($company) {
            $catalogPhotos = $company->catalogPhotos()->active()->get();
        }

        // Get products for catalog (only visible ones)
        $query = Produk::with(['bomJobCosting'])->where('show_in_catalog', true);

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

        return view('kelola-catalog.preview', compact('produks', 'company', 'catalogPhotos'));
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
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Perusahaan tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Perusahaan tidak ditemukan.');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'telepon' => 'required|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'catalog_description' => 'nullable|string|max:1000',
        ]);

        try {
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
                'catalog_description' => $request->catalog_description,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Informasi perusahaan berhasil diperbarui.']);
            }

            return redirect()->route('kelola-catalog.settings')
                ->with('success', 'Pengaturan catalog berhasil diperbarui.');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

    /**
     * Show catalog photos management page
     */
    public function photos()
    {
        $company = null;
        if (Auth::check()) {
            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);
        }

        if (!$company) {
            return redirect()->route('kelola-catalog.index')->with('error', 'Perusahaan tidak ditemukan.');
        }

        $catalogPhotos = $company->catalogPhotos()->orderBy('urutan', 'asc')->get();

        return view('kelola-catalog.photos', compact('company', 'catalogPhotos'));
    }

    /**
     * Store new catalog photo
     */
    public function storePhoto(Request $request)
    {
        $user = Auth::user();
        $company = Perusahaan::find($user->perusahaan_id);

        if (!$company) {
            return redirect()->back()->with('error', 'Perusahaan tidak ditemukan.');
        }

        // Check if file is uploaded
        if (!$request->hasFile('foto')) {
            return redirect()->back()->with('error', 'Tidak ada file yang dipilih. Pastikan Anda memilih file foto.');
        }

        $file = $request->file('foto');
        
        // Check if file upload was successful
        if (!$file || !$file->isValid()) {
            $errorMsg = $file ? $file->getErrorMessage() : 'File object is null';
            return redirect()->back()->with('error', 'Upload file gagal: ' . $errorMsg);
        }

        // Validate file with 8MB limit
        try {
            $request->validate([
                'foto' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:8192', // 8MB limit
            ], [
                'foto.required' => 'Foto harus dipilih.',
                'foto.file' => 'File yang dipilih tidak valid.',
                'foto.image' => 'File harus berupa gambar.',
                'foto.mimes' => 'Format file harus JPG, PNG, GIF, atau WEBP.',
                'foto.max' => 'Ukuran file maksimal 8MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $filename = 'catalog_' . $company->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Compress image if it's too large (over 2MB)
            $compressedFile = $this->compressImage($file);
            
            if ($compressedFile) {
                // Use compressed file - save to produk folder (same as product photos)
                $path = $compressedFile->storeAs('produk', $filename, 'public');
            } else {
                // Use original file - save to produk folder (same as product photos)
                $path = $file->storeAs('produk', $filename, 'public');
            }

            // Get the highest urutan + 1
            $maxUrutan = CatalogPhoto::where('perusahaan_id', $company->id)->max('urutan') ?? 0;
            $urutan = $maxUrutan + 1;

            $photo = CatalogPhoto::create([
                'perusahaan_id' => $company->id,
                'judul' => null,
                'foto' => $path,
                'deskripsi' => null,
                'urutan' => $urutan,
                'is_active' => true,
            ]);

            return redirect()->back()->with('success', 'Foto catalog berhasil ditambahkan.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Compress image if needed
     */
    private function compressImage($file)
    {
        try {
            // Skip compression if file is already small enough
            if ($file->getSize() <= 1024 * 1024) { // 1MB
                return null; // Use original file
            }

            $image = null;
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Create image resource based on type
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($file->getPathname());
                    break;
                case 'png':
                    $image = imagecreatefrompng($file->getPathname());
                    break;
                case 'gif':
                    $image = imagecreatefromgif($file->getPathname());
                    break;
                default:
                    return null; // Unsupported format
            }

            if (!$image) {
                return null;
            }

            // Get original dimensions
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // Calculate new dimensions (max 1920x1080 for HD)
            $maxWidth = 1920;
            $maxHeight = 1080;
            
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            
            // Only resize if image is larger than max dimensions
            if ($ratio < 1) {
                $newWidth = intval($originalWidth * $ratio);
                $newHeight = intval($originalHeight * $ratio);
                
                // Create new image
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG and GIF
                if ($extension == 'png' || $extension == 'gif') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                    imagefill($newImage, 0, 0, $transparent);
                }
                
                // Resize image
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                
                // Create temporary file
                $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $extension;
                
                // Save compressed image
                switch ($extension) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($newImage, $tempPath, 85); // 85% quality
                        break;
                    case 'png':
                        imagepng($newImage, $tempPath, 6); // Compression level 6
                        break;
                    case 'gif':
                        imagegif($newImage, $tempPath);
                        break;
                }
                
                // Clean up memory
                imagedestroy($image);
                imagedestroy($newImage);
                
                // Create new UploadedFile object
                return new \Illuminate\Http\UploadedFile(
                    $tempPath,
                    $file->getClientOriginalName(),
                    $file->getClientMimeType(),
                    null,
                    true
                );
            }
            
            // Clean up memory
            imagedestroy($image);
            
            return null; // Use original file
            
        } catch (\Exception $e) {
            \Log::error('Image compression failed', ['error' => $e->getMessage()]);
            return null; // Use original file
        }
    }

    /**
     * Update catalog photo
     */
    public function updatePhoto(Request $request, $id)
    {
        $photo = CatalogPhoto::findOrFail($id);

        $request->validate([
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);

        $photo->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->has('is_active') ? $request->is_active : $photo->is_active,
        ]);

        return redirect()->back()->with('success', 'Foto catalog berhasil diperbarui.');
    }

    /**
     * Delete catalog photo
     */
    public function deletePhoto($id)
    {
        try {
            $photo = CatalogPhoto::findOrFail($id);

            // Delete photo file from storage (catalog-photos folder)
            if ($photo->foto) {
                if (Storage::disk('public')->exists($photo->foto)) {
                    Storage::disk('public')->delete($photo->foto);
                }
            }

            // Delete photo record
            $photo->delete();

            // Check if request is AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Foto catalog berhasil dihapus.'
                ]);
            }

            return redirect()->back()->with('success', 'Foto catalog berhasil dihapus.');
            
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Error deleting catalog photo: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Terjadi kesalahan saat menghapus foto: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus foto.');
        }
    }

    /**
     * Reorder catalog photos
     */
    public function reorderPhotos(Request $request)
    {
        $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'exists:catalog_photos,id',
        ]);

        $photoIds = $request->photo_ids;
        
        foreach ($photoIds as $index => $photoId) {
            CatalogPhoto::where('id', $photoId)->update(['urutan' => $index + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Urutan foto berhasil diperbarui.']);
    }

    /**
     * Update catalog settings including description and maps
     */
    public function updateCatalogSettings(Request $request)
    {
        // Debug: Log incoming data
        \Log::info('Update catalog settings request data:', $request->all());
        
        $user = Auth::user();
        $company = Perusahaan::find($user->perusahaan_id);

        if (!$company) {
            \Log::error('Company not found for user: ' . $user->id);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Perusahaan tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Perusahaan tidak ditemukan.');
        }

        // More flexible validation
        $rules = [
            'catalog_description' => 'nullable|string|max:1000',
            'maps_link' => 'nullable|string|max:1000', // Changed from url to string for flexibility
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];

        try {
            $validated = $request->validate($rules);
            \Log::info('Validated data:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Validation failed: ' . implode(', ', $e->errors())]);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            // Prepare update data
            $updateData = [
                'catalog_description' => $request->catalog_description,
                'maps_link' => $request->maps_link,
                'latitude' => $request->latitude ? floatval($request->latitude) : null,
                'longitude' => $request->longitude ? floatval($request->longitude) : null,
            ];
            
            \Log::info('Update data prepared:', $updateData);
            
            $company->update($updateData);
            
            \Log::info('Company updated successfully');

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Pengaturan peta berhasil diperbarui.']);
            }

            return redirect()->back()->with('success', 'Pengaturan catalog berhasil diperbarui.');
            
        } catch (\Exception $e) {
            \Log::error('Error updating catalog settings: ' . $e->getMessage());
            \Log::error('Exception trace: ' . $e->getTraceAsString());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update all company information (name, description, contact)
     */
    public function updateCompanyInfo(Request $request)
    {
        \Log::info('Update company info request data:', $request->all());
        
        $user = Auth::user();
        $company = Perusahaan::find($user->perusahaan_id);

        if (!$company) {
            \Log::error('Company not found for user: ' . $user->id);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Perusahaan tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Perusahaan tidak ditemukan.');
        }

        // Validation rules
        $rules = [
            'nama' => 'required|string|max:255',
            'catalog_description' => 'required|string|max:2000',
            'email' => 'required|email|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
        ];

        try {
            $validated = $request->validate($rules);
            \Log::info('Validated company info data:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal: ' . implode(', ', $e->errors())]);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            // Prepare update data
            $updateData = [
                'nama' => $request->nama,
                'catalog_description' => $request->catalog_description,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
            ];
            
            \Log::info('Company info update data prepared:', $updateData);
            
            $company->update($updateData);
            
            \Log::info('Company info updated successfully');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Informasi perusahaan berhasil diperbarui.']);
            }

            return redirect()->back()->with('success', 'Informasi perusahaan berhasil diperbarui.');
            
        } catch (\Exception $e) {
            \Log::error('Error updating company info: ' . $e->getMessage());
            \Log::error('Exception trace: ' . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the fixed form for company information update
     */
    public function fixedForm(Request $request)
    {
        // Get current company
        $user = Auth::user();
        $company = Perusahaan::find($user->perusahaan_id);

        if (!$company) {
            return redirect()->back()->with('error', 'Perusahaan tidak ditemukan.');
        }

        return view('fixed_update_form', compact('company'));
    }
}

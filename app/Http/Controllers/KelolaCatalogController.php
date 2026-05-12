<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Perusahaan;
use App\Models\CatalogPhoto;
use App\Models\CatalogSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
        $catalogSections = [];
        if (Auth::check()) {
            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);
        }

        // Get products for preview
        $produks = collect();
        if ($company) {
            $produks = Produk::with(['bomJobCosting'])
                ->where('show_in_catalog', true)
                ->orderBy('nama_produk', 'asc')
                ->get();

            // Load saved catalog sections
            $sections = \DB::table('catalog_sections')
                ->where('perusahaan_id', $company->id)
                ->orderBy('order')
                ->get();

            foreach ($sections as $section) {
                $catalogSections[$section->section_type] = json_decode($section->content, true);
            }
        }

        return view('kelola-catalog.index', compact('company', 'produks', 'catalogSections'));
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
            'catalog_description' => 'nullable|string|max:2000',
            'maps_link' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'background_type' => 'required|in:color,gradient,image',
            'background_color' => 'nullable|string|max:7',
            'gradient_color_1' => 'nullable|string|max:7',
            'gradient_color_2' => 'nullable|string|max:7',
            'gradient_direction' => 'nullable|string|max:50',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'background_opacity' => 'nullable|integer|min:0|max:100',
        ]);

        try {
            // Handle company logo upload
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

            // Handle background image upload
            if ($request->hasFile('background_image')) {
                // Delete old background image if exists
                if ($company->background_image && Storage::disk('public')->exists($company->background_image)) {
                    Storage::disk('public')->delete($company->background_image);
                }

                $file = $request->file('background_image');
                $filename = 'bg_' . $company->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('catalog-backgrounds', $filename, 'public');
                $company->background_image = $path;
            }

            // Update company data
            $company->update([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'catalog_description' => $request->catalog_description,
                'maps_link' => $request->maps_link,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'background_type' => $request->background_type,
                'background_color' => $request->background_color,
                'gradient_color_1' => $request->gradient_color_1,
                'gradient_color_2' => $request->gradient_color_2,
                'gradient_direction' => $request->gradient_direction ?? 'to right',
                'background_opacity' => $request->background_opacity ?? 50,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Semua pengaturan catalog berhasil diperbarui.']);
            }

            return redirect()->route('kelola-catalog.settings')
                ->with('success', 'Semua pengaturan catalog berhasil diperbarui.');
                
        } catch (\Exception $e) {
            \Log::error('Error updating catalog settings: ' . $e->getMessage());
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

    /**
     * Save all catalog sections
     */
    public function saveSections(Request $request)
    {
        try {
            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);

            if (!$company) {
                return response()->json(['success' => false, 'message' => 'Perusahaan tidak ditemukan.']);
            }

            $catalogData = $request->input('sections', []);
            \Log::info('Received catalog data:', $catalogData);

            // Begin transaction
            \DB::beginTransaction();

            // Handle cover photo upload if provided as base64
            if (isset($catalogData['cover']['cover_photo']) && !empty($catalogData['cover']['cover_photo'])) {
                $coverPhotoData = $catalogData['cover']['cover_photo'];
                
                // Check if it's a base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $coverPhotoData, $type)) {
                    $coverPhotoData = substr($coverPhotoData, strpos($coverPhotoData, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif
                    
                    $coverPhotoData = base64_decode($coverPhotoData);
                    
                    if ($coverPhotoData !== false) {
                        // Delete old cover photo if exists
                        if ($company->foto && Storage::disk('public')->exists($company->foto)) {
                            Storage::disk('public')->delete($company->foto);
                        }
                        
                        $filename = 'cover_' . $company->id . '_' . time() . '.' . $type;
                        $path = 'company-photos/' . $filename;
                        
                        Storage::disk('public')->put($path, $coverPhotoData);
                        $company->foto = $path;
                        $company->save();
                        
                        \Log::info('Cover photo saved', ['path' => $path]);
                    }
                }
            }

            // Handle team member photos
            if (isset($catalogData['team']['members']) && is_array($catalogData['team']['members'])) {
                $members = [];
                foreach ($catalogData['team']['members'] as $member) {
                    if (isset($member['photo']) && !empty($member['photo'])) {
                        $photoData = $member['photo'];
                        
                        // Check if it's a base64 image
                        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                            $photoData = substr($photoData, strpos($photoData, ',') + 1);
                            $type = strtolower($type[1]); // jpg, png, gif
                            
                            $photoData = base64_decode($photoData);
                            
                            if ($photoData !== false) {
                                $filename = 'team_' . $company->id . '_' . time() . '_' . uniqid() . '.' . $type;
                                $path = 'team-photos/' . $filename;
                                
                                Storage::disk('public')->put($path, $photoData);
                                
                                // Update member photo to storage path
                                $member['photo'] = asset('storage/' . $path);
                                
                                \Log::info('Team member photo saved', ['path' => $path]);
                            }
                        }
                    }
                    $members[] = $member;
                }
                
                // Update catalogData with new photo paths
                $catalogData['team']['members'] = $members;
            }

            // Delete all existing sections for this company
            $company->catalogSections()->delete();

            // Create sections from catalog data
            $sections = [];

            // Cover section
            if (isset($catalogData['cover'])) {
                $sections[] = [
                    'perusahaan_id' => $company->id,
                    'section_type' => 'cover',
                    'title' => 'Cover',
                    'content' => json_encode($catalogData['cover']),
                    'order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Team section
            if (isset($catalogData['team'])) {
                $sections[] = [
                    'perusahaan_id' => $company->id,
                    'section_type' => 'team',
                    'title' => $catalogData['team']['title'] ?? 'THE TEAM.',
                    'content' => json_encode($catalogData['team']),
                    'order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Products section
            if (isset($catalogData['products'])) {
                $sections[] = [
                    'perusahaan_id' => $company->id,
                    'section_type' => 'products',
                    'title' => $catalogData['products']['title'] ?? 'PRODUCT MATERIAL.',
                    'content' => json_encode($catalogData['products']),
                    'order' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Location section
            if (isset($catalogData['location'])) {
                $sections[] = [
                    'perusahaan_id' => $company->id,
                    'section_type' => 'location',
                    'title' => $catalogData['location']['title'] ?? 'LOKASI KAMI.',
                    'content' => json_encode($catalogData['location']),
                    'order' => 4,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insert all sections
            if (!empty($sections)) {
                \DB::table('catalog_sections')->insert($sections);
                \Log::info('Inserted catalog sections:', $sections);
            }

            // Update company basic info if provided in cover section
            if (isset($catalogData['cover'])) {
                $coverData = $catalogData['cover'];
                $updateData = [];
                
                if (isset($coverData['company_name']) && !empty($coverData['company_name'])) {
                    $updateData['nama'] = $coverData['company_name'];
                }
                
                if (isset($coverData['company_description']) && !empty($coverData['company_description'])) {
                    $updateData['catalog_description'] = $coverData['company_description'];
                }
                
                if (!empty($updateData)) {
                    $company->update($updateData);
                    \Log::info('Updated company cover data:', $updateData);
                }
            }

            // Update location info if provided
            if (isset($catalogData['location'])) {
                $locationData = $catalogData['location'];
                $updateData = [];
                
                if (isset($locationData['maps_link']) && !empty($locationData['maps_link'])) {
                    $updateData['maps_link'] = $locationData['maps_link'];
                }
                
                if (isset($locationData['phone']) && !empty($locationData['phone'])) {
                    $updateData['telepon'] = $locationData['phone'];
                }
                
                if (isset($locationData['email']) && !empty($locationData['email'])) {
                    $updateData['email'] = $locationData['email'];
                }
                
                if (isset($locationData['address']) && !empty($locationData['address'])) {
                    $updateData['alamat'] = $locationData['address'];
                }
                
                if (!empty($updateData)) {
                    $company->update($updateData);
                    \Log::info('Updated company location data:', $updateData);
                }
            }

            \DB::commit();
            \Log::info('Transaction committed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Semua data catalog berhasil tersimpan!'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error saving catalog sections: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Upload team member photo
     */
    public function uploadTeamPhoto(Request $request)
    {
        try {
            \Log::info('Upload team photo request received');
            
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            ], [
                'photo.required' => 'Foto harus dipilih.',
                'photo.image' => 'File harus berupa gambar.',
                'photo.mimes' => 'Format file harus JPG, PNG, GIF.',
                'photo.max' => 'Ukuran file maksimal 5MB.',
            ]);

            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan tidak ditemukan.'
                ], 404);
            }

            $file = $request->file('photo');
            $filename = 'team_' . $company->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store in team-photos directory
            $path = $file->storeAs('team-photos', $filename, 'public');
            
            \Log::info('Team photo uploaded successfully', ['path' => $path]);

            return response()->json([
                'success' => true,
                'photo_url' => asset('storage/' . $path),
                'photo_path' => $path,
                'message' => 'Foto anggota tim berhasil diupload.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error uploading team photo', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_map(fn($err) => implode(', ', $err), $e->errors()))
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error uploading team photo', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload cover photo
     */
    public function uploadCoverPhoto(Request $request)
    {
        try {
            \Log::info('Upload cover photo request received');
            
            $request->validate([
                'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            ], [
                'foto.required' => 'Foto harus dipilih.',
                'foto.image' => 'File harus berupa gambar.',
                'foto.mimes' => 'Format file harus JPG, PNG, GIF.',
                'foto.max' => 'Ukuran file maksimal 5MB.',
            ]);

            $user = Auth::user();
            $company = Perusahaan::find($user->perusahaan_id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan tidak ditemukan.'
                ], 404);
            }

            $file = $request->file('foto');
            
            // Delete old cover photo if exists
            if ($company->foto && Storage::disk('public')->exists($company->foto)) {
                Storage::disk('public')->delete($company->foto);
                \Log::info('Old cover photo deleted', ['path' => $company->foto]);
            }
            
            $filename = 'cover_' . $company->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store in company-photos directory
            $path = $file->storeAs('company-photos', $filename, 'public');
            
            // Update company foto field
            $company->update(['foto' => $path]);
            
            \Log::info('Cover photo uploaded and saved successfully', ['path' => $path]);

            return response()->json([
                'success' => true,
                'photo_url' => asset('storage/' . $path),
                'photo_path' => $path,
                'message' => 'Foto cover berhasil diupload dan tersimpan.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error uploading cover photo', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_map(fn($err) => implode(', ', $err), $e->errors()))
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error uploading cover photo', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto: ' . $e->getMessage()
            ], 500);
        }
    }
}

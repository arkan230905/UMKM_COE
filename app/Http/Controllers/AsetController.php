<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\JenisAset;
use App\Models\KategoriAset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AsetController extends Controller
{
    /**
     * Display a listing of the assets.
     */
    public function index(Request $request)
    {
        $query = Aset::with('kategori.jenisAset');

        // Filter by search term
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_aset', 'like', "%{$search}%")
                  ->orWhere('kode_aset', 'like', "%{$search}%");
            });
        }

        // Filter by jenis_aset
        if ($request->has('jenis_aset') && !empty($request->jenis_aset)) {
            $query->whereHas('kategori', function($q) use ($request) {
                $q->where('jenis_aset_id', $request->jenis_aset);
            });
        }

        // Filter by kategori_aset_id
        if ($request->has('kategori_aset_id') && !empty($request->kategori_aset_id)) {
            $query->where('kategori_aset_id', $request->kategori_aset_id);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Get paginated results
        $asets = $query->latest()->paginate(10)->withQueryString();
        
        // Get filter options
        $jenisAsets = JenisAset::with('kategories')->get();
        $kategoriAsets = $request->has('jenis_aset') 
            ? KategoriAset::where('jenis_aset_id', $request->jenis_aset)->get() 
            : collect();

        return view('master-data.aset.index', compact('asets', 'jenisAsets', 'kategoriAsets'));
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create()
    {
        $jenisAsets = JenisAset::with('kategories')->get();
        $kodeAset = Aset::generateKodeAset();
        
        return view('master-data.aset.create', compact('jenisAsets', 'kodeAset'));
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        // Validate the request - sesuaikan dengan nama field di form
        $validated = $request->validate([
            'kode_aset' => 'required|string|max:50|unique:asets,kode_aset',
            'nama_aset' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'harga_perolehan' => 'required|numeric|min:0',
            'acquisition_cost' => 'nullable|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'umur_manfaat' => 'required|integer|min:1|max:100',
            'tanggal_beli' => 'required|date',
            'tanggal_akuisisi' => 'nullable|date|after_or_equal:tanggal_beli',
            'status' => 'required|in:aktif,disewakan,dioperasikan,dihapus',
            'keterangan' => 'nullable|string',
            'depr_start_date' => 'nullable|date',
        ]);

        try {
            \Log::info('AsetController@store start', [
                'payload' => $request->except(['_token']),
            ]);
            DB::beginTransaction();
            
            // Get kategori aset by name - if not found, create it
            $kategoriAset = KategoriAset::where('nama', $request->kategori)->first();
            if (!$kategoriAset) {
                // Create new kategori if it doesn't exist
                $kategoriAset = KategoriAset::create([
                    'jenis_aset_id' => 1, // Default to Aset Tetap
                    'kode' => 'CUSTOM-' . strtoupper(str_replace(' ', '-', $request->kategori)),
                    'nama' => $request->kategori,
                    'umur_ekonomis' => 5,
                ]);
            }
            
            // Calculate total perolehan
            $hargaPerolehan = (float) $request->harga_perolehan;
            $biayaPerolehan = (float) ($request->acquisition_cost ?? 0);
            $totalPerolehan = $hargaPerolehan + $biayaPerolehan;
            
            // Calculate residual value (5% of total perolehan if not provided)
            $nilaiResidu = $request->filled('residual_value') 
                ? (float) $request->residual_value 
                : $totalPerolehan * 0.05;
                
            // Use provided umur_manfaat or get from kategori if not provided
            $umurManfaat = $request->umur_manfaat ?? $kategoriAset->umur_ekonomis;
            
            // Create the asset
            $aset = new Aset();
            $aset->kode_aset = $request->kode_aset;
            $aset->nama_aset = $request->nama_aset;
            $aset->kategori_aset_id = $kategoriAset->id; // Set kategori_aset_id dari kategori yang ditemukan/dibuat
            $aset->harga_perolehan = $hargaPerolehan;
            $aset->biaya_perolehan = $biayaPerolehan;
            $aset->nilai_residu = $nilaiResidu;
            $aset->umur_manfaat = $umurManfaat;
            $aset->tanggal_beli = $request->tanggal_beli;
            $aset->tanggal_akuisisi = $request->tanggal_akuisisi ?: $request->tanggal_beli;
            $aset->status = $request->status;
            $aset->keterangan = $request->keterangan;
            
            // Calculate depreciation
            $aset->hitungPenyusutan();
            
            // Save the asset
            $aset->save();
            
            DB::commit();
            \Log::info('AsetController@store success', ['aset_id' => $aset->id]);
            
            return redirect()->route('master-data.aset.index')
                ->with('success', 'Aset berhasil ditambahkan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('AsetController@store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified asset.
     */
    public function show(Aset $aset)
    {
        // Redirect to the simplified example-style Asset pages for a consistent UI
        return redirect()->route('aset.show', ['aset' => $aset->id]);
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Aset $aset)
    {
        $aset->load('kategori.jenisAset');
        $jenisAsets = JenisAset::with('kategories')->get();
        $kategoriAsets = KategoriAset::where('jenis_aset_id', $aset->kategori->jenis_aset_id)->get();
        
        return view('master-data.aset.edit', compact('aset', 'jenisAsets', 'kategoriAsets'));
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Aset $aset)
    {
        // Validate the request
        $validated = $request->validate([
            'kode_aset' => 'required|string|max:50|unique:asets,kode_aset,' . $aset->id,
            'nama_aset' => 'required|string|max:255',
            'kategori_aset_id' => 'required|exists:kategori_asets,id',
            'harga_perolehan' => 'required|numeric|min:0',
            'biaya_perolehan' => 'nullable|numeric|min:0',
            'nilai_residu' => 'nullable|numeric|min:0',
            'umur_manfaat' => 'required|integer|min:1|max:100',
            'tanggal_beli' => 'required|date',
            'tanggal_akuisisi' => 'required|date|after_or_equal:tanggal_beli',
            'status' => 'required|in:aktif,disewakan,dioperasikan,dihapus',
            'keterangan' => 'nullable|string',
            'depr_start_date' => 'nullable|date',
            'units_capacity_total' => 'nullable|integer',
        ]);

        $aset->update($validated);

        return redirect()->route('master-data.aset.index')->with('success', 'Aset berhasil diperbarui');
    }

    public function destroy(Aset $aset)
    {
        $aset->delete();
        return redirect()->route('master-data.aset.index')->with('success', 'Aset berhasil dihapus');
    }
}

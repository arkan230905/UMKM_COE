<?php

namespace App\Http\Controllers;

use App\Models\JenisAset;
use App\Models\KategoriAset;
use App\Models\Aset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $metodePenyusutan = [
            'garis_lurus' => 'Garis Lurus (Straight Line)',
            'saldo_menurun' => 'Saldo Menurun (Declining Balance)',
            'sum_of_years_digits' => 'Jumlah Angka Tahun (Sum of Years Digits)',
        ];
        
        return view('master-data.aset.create', compact('jenisAsets', 'kodeAset', 'metodePenyusutan'));
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        // Cek apakah kategori aset disusutkan
        $kategori = KategoriAset::find($request->kategori_aset_id);
        $disusutkan = $kategori ? $kategori->disusutkan : true;
        
        // Validation rules dasar
        $rules = [
            'nama_aset' => 'required|string|max:255',
            'kategori_aset_id' => 'required|exists:kategori_asets,id',
            'harga_perolehan' => 'required|numeric|min:0',
            'biaya_perolehan' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|date',
            'tanggal_akuisisi' => 'nullable|date|after_or_equal:tanggal_beli',
            'keterangan' => 'nullable|string',
        ];
        
        // Tambah validation untuk penyusutan jika aset disusutkan
        if ($disusutkan) {
            $rules['nilai_residu'] = 'required|numeric|min:0';
            $rules['umur_manfaat'] = 'required|integer|min:1|max:100';
            $rules['metode_penyusutan'] = 'required|in:garis_lurus,saldo_menurun,sum_of_years_digits';
            $rules['tarif_penyusutan'] = 'nullable|numeric|min:0|max:200';
            if (Schema::hasColumn('asets', 'bulan_mulai')) {
                $rules['bulan_mulai'] = 'nullable|integer|min:1|max:12';
            }
            if (Schema::hasColumn('asets', 'tanggal_perolehan')) {
                $rules['tanggal_perolehan'] = 'nullable|date';
            }
        }
        
        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();
            
            // Calculate total perolehan
            $hargaPerolehan = (float) $request->harga_perolehan;
            $biayaPerolehan = (float) $request->biaya_perolehan;
            $totalPerolehan = $hargaPerolehan + $biayaPerolehan;
            
            // Initialize penyusutan variables
            $penyusutanPerTahun = 0;
            $penyusutanPerBulan = 0;
            $nilaiResidu = 0;
            $umurManfaat = 0;
            $metodePenyusutan = null;
            
            // Calculate depreciation only if asset is depreciable
            if ($disusutkan) {
                $nilaiResidu = (float) $request->nilai_residu;
                $umurManfaat = (int) $request->umur_manfaat;
                $metodePenyusutan = $request->metode_penyusutan;
                
                $nilaiTerdepresiasi = $totalPerolehan - $nilaiResidu;
                
                if ($metodePenyusutan === 'garis_lurus') {
                    // Straight line method: tarif × harga perolehan
                    $tarif = $request->tarif_penyusutan ? ($request->tarif_penyusutan / 100) : (1 / $umurManfaat);
                    $penyusutanPerTahun = $totalPerolehan * $tarif;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                } elseif ($metodePenyusutan === 'saldo_menurun') {
                    // Declining balance method: tarif × harga perolehan
                    $tarif = $request->tarif_penyusutan ? ($request->tarif_penyusutan / 100) : (2 / $umurManfaat);
                    $penyusutanPerTahun = $totalPerolehan * $tarif;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                } else {
                    // Sum of years digits: tarif × harga perolehan
                    $tarif = $request->tarif_penyusutan ? ($request->tarif_penyusutan / 100) : ($umurManfaat / (($umurManfaat * ($umurManfaat + 1)) / 2));
                    $penyusutanPerTahun = $totalPerolehan * $tarif;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                }
            }
            
            // Create the asset
            $aset = new Aset();
            $aset->kode_aset = Aset::generateKodeAset();
            $aset->nama_aset = $request->nama_aset;
            $aset->kategori_aset_id = $request->kategori_aset_id;
            $aset->harga_perolehan = $hargaPerolehan;
            $aset->biaya_perolehan = $biayaPerolehan;
            $aset->nilai_residu = $nilaiResidu;
            $aset->umur_manfaat = $umurManfaat;
            $aset->metode_penyusutan = $metodePenyusutan;
            $aset->tarif_penyusutan = $request->tarif_penyusutan;
            if (Schema::hasColumn('asets', 'bulan_mulai')) {
                $aset->bulan_mulai = $request->bulan_mulai ?? 1;
            }
            if (Schema::hasColumn('asets', 'tanggal_perolehan')) {
                $aset->tanggal_perolehan = $request->tanggal_perolehan;
            }
            $aset->penyusutan_per_tahun = round($penyusutanPerTahun, 2);
            $aset->penyusutan_per_bulan = round($penyusutanPerBulan, 2);
            $aset->nilai_buku = $totalPerolehan;
            $aset->akumulasi_penyusutan = 0;
            $aset->tanggal_beli = $request->tanggal_beli;
            $aset->tanggal_akuisisi = $request->tanggal_akuisisi ?: $request->tanggal_beli;
            $aset->status = 'aktif';
            $aset->keterangan = $request->keterangan;
            $aset->updated_by = auth()->id();
            
            $aset->save();
            
            // Generate depreciation schedule jika aset disusutkan
            if ($disusutkan && $metodePenyusutan) {
                $depreciationService = new \App\Services\AssetDepreciationService();
                $depreciationService->computeAndPost($aset);
            }
            
            DB::commit();
            
            $successMessage = 'Aset berhasil ditambahkan';
            if ($disusutkan) {
                $successMessage .= ' dengan penyusutan per bulan: Rp ' . number_format($penyusutanPerBulan, 2) . ' dan per tahun: Rp ' . number_format($penyusutanPerTahun, 2);
            } else {
                $successMessage .= '. Aset ini tidak mengalami penyusutan.';
            }
            
            return redirect()->route('master-data.aset.index')
                ->with('success', $successMessage);
                
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
        // Load relasi kategori dan jenis aset
        $aset->load('kategori.jenisAset');
        
        // Load depreciation data dari database
        $depreciationData = \App\Models\AssetDepreciation::where('asset_id', $aset->id)
            ->orderBy('tahun')
            ->get();
        
        // Hitung total perolehan
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
        
        // Hitung penyusutan per tahun dan per bulan berdasarkan metode
        if ($aset->metode_penyusutan === 'garis_lurus') {
            // Metode garis lurus: (harga perolehan - nilai residu) / umur manfaat
            $nilaiDisusutkan = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
            $umurManfaat = (int)($aset->umur_manfaat ?? $aset->umur_ekonomis_tahun ?? 1);
            $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
        } else {
            // Metode lain: tarif penyusutan × harga perolehan
            $penyusutanPerTahun = $totalPerolehan * (($aset->tarif_penyusutan ?? 0) / 100);
        }
        
        $penyusutanPerBulan = $penyusutanPerTahun / 12;
        
        return view('master-data.aset.show', compact('aset', 'depreciationData', 'totalPerolehan', 'penyusutanPerTahun', 'penyusutanPerBulan'));
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Aset $aset)
    {
        $aset->load('kategori.jenisAset');
        $jenisAsets = JenisAset::with('kategories')->get();
        $kategoriAsets = KategoriAset::where('jenis_aset_id', $aset->kategori->jenis_aset_id)->get();
        $metodePenyusutan = [
            'garis_lurus' => 'Garis Lurus (Straight Line)',
            'saldo_menurun' => 'Saldo Menurun (Declining Balance)',
            'sum_of_years_digits' => 'Jumlah Angka Tahun (Sum of Years Digits)',
        ];
        
        return view('master-data.aset.edit', compact('aset', 'jenisAsets', 'kategoriAsets', 'metodePenyusutan'));
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Aset $aset)
    {
        // Cek apakah kategori aset disusutkan
        $kategori = KategoriAset::find($request->kategori_aset_id);
        $disusutkan = $kategori ? $kategori->disusutkan : true;
        
        // Validation rules dasar
        $rules = [
            'nama_aset' => 'required|string|max:255',
            'kategori_aset_id' => 'required|exists:kategori_asets,id',
            'harga_perolehan' => 'required|numeric|min:0',
            'biaya_perolehan' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|date',
            'tanggal_akuisisi' => 'nullable|date|after_or_equal:tanggal_beli',
            'keterangan' => 'nullable|string',
        ];
        
        // Tambah validation untuk penyusutan jika aset disusutkan
        if ($disusutkan) {
            $rules['nilai_residu'] = 'required|numeric|min:0';
            $rules['umur_manfaat'] = 'required|integer|min:1|max:100';
            $rules['metode_penyusutan'] = 'required|in:garis_lurus,saldo_menurun,sum_of_years_digits';
            $rules['tarif_penyusutan'] = 'nullable|numeric|min:0|max:200';
            $rules['bulan_mulai'] = 'nullable|integer|min:1|max:12';
            $rules['tanggal_perolehan'] = 'nullable|date';
        }
        
        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();
            
            // Calculate total perolehan
            $hargaPerolehan = (float) $request->harga_perolehan;
            $biayaPerolehan = (float) $request->biaya_perolehan;
            $totalPerolehan = $hargaPerolehan + $biayaPerolehan;
            
            // Initialize penyusutan variables
            $penyusutanPerTahun = 0;
            $penyusutanPerBulan = 0;
            $nilaiResidu = 0;
            $umurManfaat = 0;
            $metodePenyusutan = null;
            
            // Calculate depreciation only if asset is depreciable
            if ($disusutkan) {
                $nilaiResidu = (float) $request->nilai_residu;
                $umurManfaat = (int) $request->umur_manfaat;
                $metodePenyusutan = $request->metode_penyusutan;
                
                $nilaiTerdepresiasi = $totalPerolehan - $nilaiResidu;
                
                if ($metodePenyusutan === 'garis_lurus') {
                    // Straight line method: tarif × harga perolehan
                    $tarif = $request->tarif_penyusutan ? ($request->tarif_penyusutan / 100) : (1 / $umurManfaat);
                    $penyusutanPerTahun = $totalPerolehan * $tarif;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                } elseif ($metodePenyusutan === 'saldo_menurun') {
                    // Declining balance method: tarif × harga perolehan
                    $tarif = $request->tarif_penyusutan ? ($request->tarif_penyusutan / 100) : (2 / $umurManfaat);
                    $penyusutanPerTahun = $totalPerolehan * $tarif;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                } else {
                    // Sum of years digits: tarif × harga perolehan
                    $tarif = $request->tarif_penyusutan ? ($request->tarif_penyusutan / 100) : ($umurManfaat / (($umurManfaat * ($umurManfaat + 1)) / 2));
                    $penyusutanPerTahun = $totalPerolehan * $tarif;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                }
            }
            
            // Update the asset
            $aset->nama_aset = $request->nama_aset;
            $aset->kategori_aset_id = $request->kategori_aset_id;
            $aset->harga_perolehan = $hargaPerolehan;
            $aset->biaya_perolehan = $biayaPerolehan;
            $aset->nilai_residu = $nilaiResidu;
            $aset->umur_manfaat = $umurManfaat;
            $aset->metode_penyusutan = $metodePenyusutan;
            $aset->tarif_penyusutan = $request->tarif_penyusutan;
            $aset->bulan_mulai = $request->bulan_mulai ?? 1;
            $aset->tanggal_perolehan = $request->tanggal_perolehan;
            $aset->penyusutan_per_tahun = round($penyusutanPerTahun, 2);
            $aset->penyusutan_per_bulan = round($penyusutanPerBulan, 2);
            $aset->nilai_buku = $totalPerolehan - ($aset->akumulasi_penyusutan ?? 0);
            $aset->tanggal_beli = $request->tanggal_beli;
            $aset->tanggal_akuisisi = $request->tanggal_akuisisi ?: $request->tanggal_beli;
            $aset->keterangan = $request->keterangan;
            $aset->updated_by = auth()->id();
            
            $aset->save();
            
            // Generate depreciation schedule jika aset disusutkan
            if ($disusutkan && $metodePenyusutan) {
                $depreciationService = new \App\Services\AssetDepreciationService();
                $depreciationService->computeAndPost($aset);
            }
            
            DB::commit();
            
            $successMessage = 'Aset berhasil diperbarui';
            if ($disusutkan) {
                $successMessage .= ' dengan penyusutan per bulan: Rp ' . number_format($penyusutanPerBulan, 2) . ' dan per tahun: Rp ' . number_format($penyusutanPerTahun, 2);
            } else {
                $successMessage .= '. Aset ini tidak mengalami penyusutan.';
            }
            
            return redirect()->route('master-data.aset.index')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('AsetController@update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Aset $aset)
    {
        $aset->delete();
        return redirect()->route('master-data.aset.index')->with('success', 'Aset berhasil dihapus');
    }

    /**
     * Get kategori by jenis aset (AJAX)
     */
    public function getKategoriByJenis(Request $request)
    {
        $jenisId = $request->get('jenis_id');
        
        if (!$jenisId) {
            return response()->json([]);
        }
        
        $kategories = KategoriAset::where('jenis_aset_id', $jenisId)
            ->select('id', 'nama', 'disusutkan', 'umur_ekonomis', 'tarif_penyusutan')
            ->orderBy('nama')
            ->get();
        
        return response()->json($kategories);
    }
}

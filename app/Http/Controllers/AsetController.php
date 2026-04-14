<?php

namespace App\Http\Controllers;

use App\Models\JenisAset;
use App\Models\KategoriAset;
use App\Models\Aset;
use App\Models\Coa;
use App\Services\DepreciationCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AsetController extends Controller
{
    protected $depreciationService;
    
    public function __construct(DepreciationCalculationService $depreciationService)
    {
        $this->depreciationService = $depreciationService;
    }
    
    /**
     * Display a listing of the assets.
     */
    public function index(Request $request)
    {
        $query = Aset::with([
            'kategori.jenisAset',
            'assetCoa',
            'accumDepreciationCoa', 
            'expenseCoa'
        ]);

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

        // Get paginated results - oldest first by creation date (newest at bottom)
        $asets = $query->orderBy('created_at', 'asc')->paginate(10)->withQueryString();
        
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
        
        // COA data for dropdowns - with fallback to prevent undefined variables
        $coaAsets = Coa::where('tipe_akun', 'LIKE', '%Asset%')
            ->orWhere('tipe_akun', 'LIKE', '%Aset%')
            ->get();
            
        // If no asset accounts found, get all COA as fallback
        if ($coaAsets->isEmpty()) {
            $coaAsets = Coa::all();
        }
            
        $coaAkumulasi = Coa::where('nama_akun', 'LIKE', '%akumulasi%')
            ->orWhere('nama_akun', 'LIKE', '%Akumulasi%')
            ->get();
            
        $coaBeban = Coa::where('nama_akun', 'LIKE', '%penyusutan%')
            ->orWhere('nama_akun', 'LIKE', '%Penyusutan%')
            ->orWhere('nama_akun', 'LIKE', '%Beban%')
            ->get();
        
        return view('master-data.aset.create', compact(
            'jenisAsets', 
            'kodeAset', 
            'metodePenyusutan',
            'coaAsets',
            'coaAkumulasi', 
            'coaBeban'
        ));
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
            'asset_coa_id' => 'required|exists:coas,id',
            'accum_depr_coa_id' => 'nullable|exists:coas,id',
            'expense_coa_id' => 'nullable|exists:coas,id',
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
            
            // Save COA fields
            $aset->asset_coa_id = $request->asset_coa_id;
            $aset->accum_depr_coa_id = $request->accum_depr_coa_id;
            $aset->expense_coa_id = $request->expense_coa_id;
            
            $aset->save();
            
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
        
        // Hitung total perolehan
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
        
        // Generate jadwal penyusutan per bulan
        $depreciationData = $this->depreciationService->generateMonthlySchedule($aset);
        
        // Hitung penyusutan per tahun dan per bulan berdasarkan metode
        if ($aset->metode_penyusutan === 'garis_lurus') {
            // Metode garis lurus: (harga perolehan - nilai residu) / umur manfaat
            $nilaiDisusutkan = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
            $umurManfaat = (int)($aset->umur_manfaat ?? $aset->umur_ekonomis_tahun ?? 1);
            $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
        } elseif ($aset->metode_penyusutan === 'sum_of_years_digits') {
            // Metode jumlah angka tahun: gunakan tahun pertama (fraksi tertinggi)
            $nilaiDisusutkan = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
            $umurManfaat = (int)($aset->umur_manfaat ?? $aset->umur_ekonomis_tahun ?? 1);
            $sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2;
            $penyusutanPerTahun = ($nilaiDisusutkan * $umurManfaat) / $sumOfYears; // Tahun pertama
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
        // Debug langsung tanpa try-catch
        dd('Controller edit dipanggil', $aset->id, $aset->nama_aset);
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Aset $aset)
    {
        // Cek apakah kategori aset disusutkan
        $kategori = KategoriAset::find($request->kategori_aset_id);
        $disusutkan = $kategori ? $kategori->disusutkan : true;
        
        // Validation rules untuk semua field yang bisa diedit
        $rules = [
            'nama_aset' => 'required|string|max:255',
            'kategori_aset_id' => 'required|exists:kategori_asets,id',
            'harga_perolehan' => 'required|numeric|min:0',
            'biaya_perolehan' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|date',
            'tanggal_akuisisi' => 'nullable|date|after_or_equal:tanggal_beli',
            'keterangan' => 'nullable|string',
            'asset_coa_id' => 'required|exists:coas,id',
            'accum_depr_coa_id' => 'nullable|exists:coas,id',
            'expense_coa_id' => 'nullable|exists:coas,id',
        ];
        
        // Field penyusutan
        if ($disusutkan) {
            $depreciationRules = [
                'nilai_residu' => 'required|numeric|min:0',
                'umur_manfaat' => 'required|integer|min:1|max:100',
                'metode_penyusutan' => 'required|in:garis_lurus,saldo_menurun,sum_of_years_digits',
                'tarif_penyusutan' => 'nullable|numeric|min:0|max:200',
                'bulan_mulai' => 'nullable|integer|min:1|max:12',
                'tanggal_perolehan' => 'nullable|date',
            ];
            $rules = array_merge($rules, $depreciationRules);
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
                    // Straight line method: (Harga Perolehan - Nilai Residu) / Umur Manfaat
                    $penyusutanPerTahun = $nilaiTerdepresiasi / $umurManfaat;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                } elseif ($metodePenyusutan === 'saldo_menurun') {
                    // Declining balance method: tarif × harga perolehan
                    $tarif = $request->tarif_penyusutan ? ($request->tarif_penyusutan / 100) : (2 / $umurManfaat);
                    $penyusutanPerTahun = $totalPerolehan * $tarif;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                } else {
                    // Sum of years digits: (Nilai yang Disusutkan × Tahun Sisa) / Jumlah Angka Tahun
                    $sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2;
                    $penyusutanPerTahun = ($nilaiTerdepresiasi * $umurManfaat) / $sumOfYears;
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                }
            }
            
            // Update semua field
            $aset->nama_aset = $request->nama_aset;
            $aset->kategori_aset_id = $request->kategori_aset_id;
            $aset->harga_perolehan = $hargaPerolehan;
            $aset->biaya_perolehan = $biayaPerolehan;
            $aset->tanggal_beli = $request->tanggal_beli;
            $aset->tanggal_akuisisi = $request->tanggal_akuisisi;
            $aset->keterangan = $request->keterangan;
            $aset->updated_by = auth()->id();
            
            // Update COA fields
            $aset->asset_coa_id = $request->asset_coa_id;
            $aset->accum_depr_coa_id = $request->accum_depr_coa_id;
            $aset->expense_coa_id = $request->expense_coa_id;
            
            if ($disusutkan) {
                $aset->umur_manfaat = $umurManfaat;
                $aset->metode_penyusutan = $metodePenyusutan;
                $aset->tarif_penyusutan = $request->tarif_penyusutan;
                $aset->nilai_residu = $nilaiResidu;
                $aset->penyusutan_per_tahun = round($penyusutanPerTahun, 2);
                $aset->penyusutan_per_bulan = round($penyusutanPerBulan, 2);
                
                // Update optional fields jika ada
                if (Schema::hasColumn('asets', 'bulan_mulai')) {
                    $aset->bulan_mulai = $request->bulan_mulai ?? 1;
                }
                if (Schema::hasColumn('asets', 'tanggal_perolehan')) {
                    $aset->tanggal_perolehan = $request->tanggal_perolehan;
                }
            }
            
            $aset->save();
            
            DB::commit();
            
            return redirect()->route('master-data.aset.index')
                ->with('success', 'Aset berhasil diperbarui.');
                
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

    /**
     * Add new jenis aset via AJAX
     */
    public function addJenisAset(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:jenis_asets,nama',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        try {
            $jenisAset = JenisAset::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis aset berhasil ditambahkan',
                'data' => [
                    'id' => $jenisAset->id,
                    'nama' => $jenisAset->nama,
                    'deskripsi' => $jenisAset->deskripsi,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new kategori aset via AJAX
     */
    public function addKategoriAset(Request $request)
    {
        $request->validate([
            'jenis_aset_id' => 'required|exists:jenis_asets,id',
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:20|unique:kategori_asets,kode',
            'umur_ekonomis' => 'nullable|integer|min:0|max:100',
            'tarif_penyusutan' => 'nullable|numeric|min:0|max:100',
            'disusutkan' => 'boolean',
        ]);

        try {
            // Generate kode otomatis jika tidak diisi
            $kode = $request->kode;
            if (empty($kode)) {
                $jenisAset = JenisAset::find($request->jenis_aset_id);
                $prefix = '';
                if (str_contains($jenisAset->nama, 'Tetap')) {
                    $prefix = 'AT';
                } elseif (str_contains($jenisAset->nama, 'Tidak Tetap')) {
                    $prefix = 'ATT';
                } elseif (str_contains($jenisAset->nama, 'Tidak Berwujud')) {
                    $prefix = 'ATB';
                } else {
                    $prefix = 'A';
                }
                
                $lastKategori = KategoriAset::where('kode', 'LIKE', $prefix . '-%')
                    ->orderBy('kode', 'desc')
                    ->first();
                
                if ($lastKategori) {
                    $lastNumber = (int) substr($lastKategori->kode, strlen($prefix) + 1);
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }
                
                $kode = $prefix . '-' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
            }

            $kategoriAset = KategoriAset::create([
                'jenis_aset_id' => $request->jenis_aset_id,
                'kode' => $kode,
                'nama' => $request->nama,
                'umur_ekonomis' => $request->umur_ekonomis ?? 0,
                'tarif_penyusutan' => $request->tarif_penyusutan ?? 0,
                'disusutkan' => $request->disusutkan ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori aset berhasil ditambahkan',
                'data' => [
                    'id' => $kategoriAset->id,
                    'jenis_aset_id' => $kategoriAset->jenis_aset_id,
                    'kode' => $kategoriAset->kode,
                    'nama' => $kategoriAset->nama,
                    'umur_ekonomis' => $kategoriAset->umur_ekonomis,
                    'tarif_penyusutan' => $kategoriAset->tarif_penyusutan,
                    'disusutkan' => $kategoriAset->disusutkan,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}

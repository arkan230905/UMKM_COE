<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\JenisAset;
use App\Models\KategoriAset;
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
     * Show the form for editing the specified asset.
     */
    public function edit($id)
    {
        $aset = Aset::with('kategori.jenisAset')->findOrFail($id);
        $jenisAsets = JenisAset::with('kategories')->get();
        $metodePenyusutan = [
            'garis_lurus' => 'Garis Lurus (Straight Line)',
            'saldo_menurun' => 'Saldo Menurun (Declining Balance)',
            'sum_of_years_digits' => 'Jumlah Angka Tahun (Sum of Years Digits)',
        ];

        return view('master-data.aset.edit', compact('aset', 'jenisAsets', 'metodePenyusutan'));
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        // Cek apakah kategori aset disusutkan
        $kategori = KategoriAset::find($request->kategori_aset_id);
        $disusutkan = $this->kategoriHarusDisusutkan($kategori);
        
        // Validation rules dasar
        $rules = [
            'nama_aset' => 'required|string|max:255',
            'kategori_aset_id' => 'required|exists:kategori_asets,id',
            'harga_perolehan' => 'required|numeric|min:0',
            'biaya_perolehan' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|date',
            'tanggal_akuisisi' => 'nullable|date|after_or_equal:tanggal_beli',
        ];

        if ($this->asetHasColumn('keterangan')) {
            $rules['keterangan'] = 'nullable|string';
        }
        
        // Tambah validation untuk penyusutan jika aset disusutkan
        if ($disusutkan) {
            $rules['nilai_residu'] = 'required|numeric|min:0';
            $rules['umur_manfaat'] = 'required|integer|min:1|max:100';

            if ($this->asetHasColumn('metode_penyusutan')) {
                $rules['metode_penyusutan'] = 'required|in:garis_lurus,saldo_menurun,sum_of_years_digits';
            }

            if ($this->asetHasColumn('tarif_penyusutan')) {
                $rules['tarif_penyusutan'] = 'nullable|numeric|min:0|max:200';
            }

            if ($this->asetHasColumn('bulan_mulai')) {
                $rules['bulan_mulai'] = 'nullable|integer|min:1|max:12';
            }

            if ($this->asetHasColumn('tanggal_perolehan')) {
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
                if ($this->asetHasColumn('metode_penyusutan')) {
                    $metodePenyusutan = $request->metode_penyusutan ?: 'garis_lurus';
                }
                
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
            if ($disusutkan) {
                $aset->nilai_residu = $nilaiResidu;
                $aset->umur_manfaat = $umurManfaat;
                $this->assignIfColumnExists($aset, 'metode_penyusutan', $metodePenyusutan ?: 'garis_lurus');
                $this->assignIfColumnExists($aset, 'tarif_penyusutan', $request->tarif_penyusutan);
                $this->assignIfColumnExists($aset, 'bulan_mulai', $request->bulan_mulai ?? 1);
                $this->assignIfColumnExists($aset, 'tanggal_perolehan', $request->tanggal_perolehan);
            } else {
                $aset->nilai_residu = 0;
                $aset->umur_manfaat = 0;
                $this->assignIfColumnExists($aset, 'metode_penyusutan', null);
                $this->assignIfColumnExists($aset, 'tarif_penyusutan', null);
                $this->assignIfColumnExists($aset, 'bulan_mulai', null);
                $this->assignIfColumnExists($aset, 'tanggal_perolehan', $request->tanggal_beli);
            }

            $bulanMulaiFull = $request->input('bulan_mulai_full');
            $tanggalAkuisisi = $request->tanggal_akuisisi ?: $request->tanggal_beli;
            if ($bulanMulaiFull) {
                $tanggalAkuisisi = $bulanMulaiFull;
            }

            $this->assignIfColumnExists($aset, 'penyusutan_per_tahun', $disusutkan ? round($penyusutanPerTahun, 2) : 0);
            $this->assignIfColumnExists($aset, 'penyusutan_per_bulan', $disusutkan ? round($penyusutanPerBulan, 2) : 0);
            $this->assignIfColumnExists($aset, 'nilai_buku', $totalPerolehan);
            $this->assignIfColumnExists($aset, 'akumulasi_penyusutan', 0);
            $aset->tanggal_beli = $request->tanggal_beli;
            $aset->tanggal_akuisisi = $tanggalAkuisisi;
            $this->assignIfColumnExists($aset, 'status', 'aktif');
            $this->assignIfColumnExists($aset, 'keterangan', $request->keterangan);
            $aset->updated_by = auth()->id();
            
            $aset->save();
            
            // Generate depreciation schedule jika aset disusutkan
            if ($disusutkan && ($metodePenyusutan ?: true)) {
                $depreciationService = new \App\Services\AssetDepreciationService();
                $depreciationService->computeAndPost($aset);
                $aset->refresh();
                $aset->syncDepreciationFigures();
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

        $shouldDepreciate = $this->kategoriHarusDisusutkan($aset->kategori);

        if ($shouldDepreciate && empty($aset->metode_penyusutan) && $this->asetHasColumn('metode_penyusutan')) {
            $aset->metode_penyusutan = 'garis_lurus';
            $aset->save();
        }

        if ($shouldDepreciate && !\App\Models\AssetDepreciation::where('asset_id', $aset->id)->exists()) {
            $service = new \App\Services\AssetDepreciationService();
            $service->computeAndPost($aset);
            $aset->refresh();
        }

        // Load depreciation data dari database
        $depreciationData = \App\Models\AssetDepreciation::where('asset_id', $aset->id)
            ->orderBy('tahun')
            ->get();

        $monthlySchedule = [];

        if ($aset->metode_penyusutan === 'garis_lurus') {
            $depreciationService = new \App\Services\AssetDepreciationService();

            $basis = max(((float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0)) - (float)($aset->nilai_residu ?? 0), 0);
            $targetBook = round((float)($aset->nilai_residu ?? 0), 2);

            $needsRegenerate = $depreciationData->isEmpty();

            if (!$needsRegenerate && $basis > 0) {
                $lastRow = $depreciationData->last();
                if (!$lastRow) {
                    $needsRegenerate = true;
                } else {
                    $accDiff = abs((float)$lastRow->akumulasi_penyusutan - $basis);
                    $bookDiff = abs((float)$lastRow->nilai_buku_akhir - $targetBook);
                    if ($accDiff > 0.01 || $bookDiff > 0.01) {
                        $needsRegenerate = true;
                    }
                }
            }

            if ($needsRegenerate) {
                $depreciationService->computeAndPost($aset->fresh());
                $aset->refresh();
                $depreciationData = \App\Models\AssetDepreciation::where('asset_id', $aset->id)
                    ->orderBy('tahun')
                    ->get();
            }

            $monthlySchedule = $depreciationService->buildMonthlySchedule($aset, $depreciationData);
        }
        elseif ($aset->metode_penyusutan === 'saldo_menurun') {
            $monthlySchedule = $this->buildMonthlyScheduleFromAnnual($aset, $depreciationData);
        }

        $monthlyByYear = collect($monthlySchedule)->keyBy('year');
        $depreciationData = $depreciationData->map(function ($row) use ($monthlyByYear) {
            $year = (int) $row->tahun;
            $details = $monthlyByYear->get($year);

            if (!is_array($details)) {
                $details = null;
            }

            $breakdown = [];
            if (is_array($details) && isset($details['monthly_breakdown']) && is_array($details['monthly_breakdown'])) {
                $breakdown = $details['monthly_breakdown'];
            }

            $row->setAttribute('monthly_details', $details);
            $row->setAttribute('monthly_breakdown', $breakdown);

            return $row;
        });

        // Hitung total perolehan
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);

        // Hitung penyusutan per tahun dan per bulan berdasarkan metode
        $penyusutanPerTahun = 0;
        $penyusutanPerBulan = 0;

        if ($depreciationData->isNotEmpty()) {
            $penyusutanPerTahun = (float) $depreciationData->first()->beban_penyusutan;
            $penyusutanPerBulan = $penyusutanPerTahun / max(($depreciationData->first()->months_in_period ?? 12), 1);
        } else {
            if ($aset->metode_penyusutan === 'garis_lurus') {
                $nilaiDisusutkan = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
                $umurManfaat = (int)($aset->umur_manfaat ?? $aset->umur_ekonomis_tahun ?? 1);
                $penyusutanPerTahun = $umurManfaat > 0 ? $nilaiDisusutkan / $umurManfaat : 0;
            } else {
                $penyusutanPerTahun = $totalPerolehan * (($aset->tarif_penyusutan ?? 0) / 100);
            }

            $penyusutanPerBulan = $penyusutanPerTahun / 12;
        }

        return view('master-data.aset.show', compact('aset', 'depreciationData', 'totalPerolehan', 'penyusutanPerTahun', 'penyusutanPerBulan', 'monthlySchedule'));
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Aset $aset)
    {
        // Cek apakah kategori aset disusutkan
        $kategori = KategoriAset::find($request->kategori_aset_id);
        $disusutkan = $this->kategoriHarusDisusutkan($kategori);
        
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

            if ($disusutkan) {
                $aset->nilai_residu = $nilaiResidu;
                $aset->umur_manfaat = $umurManfaat;
                $this->assignIfColumnExists($aset, 'metode_penyusutan', $metodePenyusutan);
                $this->assignIfColumnExists($aset, 'tarif_penyusutan', $request->tarif_penyusutan);
                $this->assignIfColumnExists($aset, 'bulan_mulai', $request->bulan_mulai ?? 1);
                $this->assignIfColumnExists($aset, 'tanggal_perolehan', $request->tanggal_perolehan);
                $this->assignIfColumnExists($aset, 'penyusutan_per_tahun', round($penyusutanPerTahun, 2));
                $this->assignIfColumnExists($aset, 'penyusutan_per_bulan', round($penyusutanPerBulan, 2));
            } else {
                $aset->nilai_residu = 0;
                $aset->umur_manfaat = 0;
                $this->assignIfColumnExists($aset, 'metode_penyusutan', null);
                $this->assignIfColumnExists($aset, 'tarif_penyusutan', null);
                $this->assignIfColumnExists($aset, 'bulan_mulai', null);
                $this->assignIfColumnExists($aset, 'tanggal_perolehan', $request->tanggal_beli);
                $this->assignIfColumnExists($aset, 'penyusutan_per_tahun', 0);
                $this->assignIfColumnExists($aset, 'penyusutan_per_bulan', 0);
                $this->assignIfColumnExists($aset, 'akumulasi_penyusutan', 0);
            }

            $this->assignIfColumnExists($aset, 'keterangan', $request->keterangan);

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
        $jenisId = $request->integer('jenis_aset_id', $request->integer('jenis_id'));

        if (!$jenisId) {
            return response()->json([], 200);
        }

        try {
            $selectColumns = ['id', 'nama', 'kode', 'umur_ekonomis', 'tarif_penyusutan', 'jenis_aset_id'];
            if (Schema::hasColumn('kategori_asets', 'disusutkan')) {
                $selectColumns[] = 'disusutkan';
            }

            $kategories = KategoriAset::query()
                ->where('jenis_aset_id', $jenisId)
                ->select($selectColumns)
                ->orderBy('nama')
                ->get();

            return response()->json($kategories);
        } catch (\Throwable $th) {
            \Log::error('Gagal memuat kategori aset', [
                'jenis_aset_id' => $jenisId,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat memuat kategori aset.',
            ], 500);
        }
    }

    private function kategoriHarusDisusutkan(?KategoriAset $kategori): bool
    {
        if (!$kategori) {
            return true;
        }

        if (!is_null($kategori->disusutkan)) {
            return (bool) $kategori->disusutkan;
        }

        if (!is_null($kategori->umur_ekonomis)) {
            return (int) $kategori->umur_ekonomis > 0;
        }

        return true;
    }

    private function asetHasColumn(string $column): bool
    {
        static $cache = [];

        if (!array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('asets', $column);
        }

        return $cache[$column];
    }

    private function assignIfColumnExists(Aset $aset, string $column, $value): void
    {
        if ($this->asetHasColumn($column)) {
            $aset->{$column} = $value;
        }
    }

    private function buildMonthlyScheduleFromAnnual(Aset $aset, $annualRows)
    {
        $rows = collect($annualRows);
        if ($rows->isEmpty()) {
            return [];
        }

        $tanggalMulai = $aset->tanggal_perolehan
            ?? $aset->tanggal_akuisisi
            ?? $aset->tanggal_beli
            ?? now();

        $startDate = Carbon::parse($tanggalMulai);
        if ($startDate->day > 15) {
            $startDate = $startDate->copy()->addMonthNoOverflow()->startOfMonth();
        } else {
            $startDate = $startDate->copy()->startOfMonth();
        }

        $currentDate = $startDate->copy();
        $bookValue = (float) $aset->harga_perolehan + (float) ($aset->biaya_perolehan ?? 0);
        $residual = (float) ($aset->nilai_residu ?? 0);
        $accumulated = 0.0;
        $lifeMonths = max(1, (int) $aset->umur_manfaat * 12);
        $monthsRemaining = $lifeMonths;

        $schedule = [];

        foreach ($rows as $row) {
            $annualAmount = (float) $row->beban_penyusutan;
            $year = (int) $row->tahun;
            if ($monthsRemaining <= 0 || $annualAmount <= 0) {
                continue;
            }
            $monthLimit = max(1, 12 - $currentDate->month + 1);
            $monthsThisYear = ($schedule === [])
                ? min($monthsRemaining, $monthLimit)
                : min($monthsRemaining, max(1, 12 - $currentDate->month + 1));

            if ($row === $rows->last()) {
                $monthsThisYear = max(1, $monthsRemaining);
            }

            $monthsThisYear = max(1, min(12, $monthsThisYear));
            $monthsRemaining -= $monthsThisYear;

            $monthlyAmountBase = $annualAmount / $monthsThisYear;
            $yearMonths = [];
            $annualRemaining = round($annualAmount, 2);
            $yearAccumStart = round($accumulated, 2);
            $yearBookStart = round($bookValue, 2);

            for ($i = 0; $i < $monthsThisYear; $i++) {
                $isLastMonth = ($i === $monthsThisYear - 1);
                $amount = $isLastMonth ? $annualRemaining : round(min($annualRemaining, $monthlyAmountBase), 2);
                $amount = round($amount, 2);

                $accumulated = round($accumulated + $amount, 2);
                $bookValue = round(max($bookValue - $amount, $residual), 2);
                $annualRemaining = round($annualRemaining - $amount, 2);

                $yearMonths[] = [
                    'label' => $currentDate->format('F Y'),
                    'amount' => $amount,
                    'accum' => $accumulated,
                    'book' => $bookValue,
                ];

                $currentDate = $currentDate->copy()->addMonth();
            }

            $schedule[] = [
                'year' => $year,
                'months_in_period' => $monthsThisYear,
                'annual_depreciation' => round(array_sum(array_column($yearMonths, 'amount')), 2),
                'accum_start' => $yearAccumStart,
                'accum_end' => $accumulated,
                'book_start' => $yearBookStart,
                'book_end' => $bookValue,
                'monthly_breakdown' => $yearMonths,
            ];
        }

        return $schedule;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\JenisAset;
use App\Models\KategoriAset;
use App\Models\Aset;
use App\Models\Coa;
use App\Models\JournalEntry;
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
        
        // Update nilai buku real-time untuk setiap aset dan hitung data penyusutan
        $currentMonth = now()->format('Y-m');
        foreach ($asets as $aset) {
            // Update nilai buku berdasarkan bulan saat ini
            $aset->updateNilaiBukuRealTime();
            
            // Calculate monthly depreciation using CORRECT method for each asset
            $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
            
            if ($aset->umur_manfaat > 0 && $totalPerolehan > 0) {
                // Ambil langsung dari DB — sudah dihitung dengan rumus yang benar
                $penyusutanPerBulan = (float)($aset->getAttributes()['penyusutan_per_bulan'] ?? 0);

                // Fallback jika DB kosong
                if ($penyusutanPerBulan <= 0) {
                    $nilaiResidu  = (float)($aset->nilai_residu ?? 0);
                    $umur         = (int)$aset->umur_manfaat;
                    $tglMulai     = $aset->tanggal_akuisisi ?? $aset->tanggal_beli;
                    $start        = \Carbon\Carbon::parse($tglMulai);
                    if ($start->day > 15) $start = $start->addMonthNoOverflow()->startOfMonth();
                    $bulanTersisa = 13 - $start->month;

                    if ($aset->metode_penyusutan === 'saldo_menurun') {
                        $deprTahun    = $totalPerolehan * (2 / $umur);
                        $penyusutanPerBulan = ($deprTahun * ($bulanTersisa / 12)) / $bulanTersisa;
                    } elseif ($aset->metode_penyusutan === 'garis_lurus') {
                        $penyusutanPerBulan = ($totalPerolehan - $nilaiResidu) / ($umur * 12);
                    } else {
                        $sumOfYears = ($umur * ($umur + 1)) / 2;
                        $deprTahun  = (($totalPerolehan - $nilaiResidu) * $umur) / $sumOfYears;
                        $penyusutanPerBulan = ($deprTahun * ($bulanTersisa / 12)) / $bulanTersisa;
                    }
                }
            } else {
                $penyusutanPerBulan = 0;
            }
            
            $aset->monthly_depreciation = $penyusutanPerBulan;
            
            // Check if already posted this month
            $aset->is_posted_this_month = \App\Models\JurnalUmum::where('tanggal', now()->endOfMonth()->format('Y-m-d'))
                ->where('keterangan', 'LIKE', "%{$aset->nama_aset}%")
                ->where('keterangan', 'LIKE', '%Penyusutan%')
                ->exists();
        }
        
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
        $coaAsets = Coa::where('tipe_akun', 'LIKE', '%Aset%')
            ->orWhere('tipe_akun', 'LIKE', '%ASET%')
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
                $nilaiResidu     = (float) $request->nilai_residu;
                $umurManfaat     = (int) $request->umur_manfaat;
                $metodePenyusutan = $request->metode_penyusutan;

                // Tentukan bulan tersisa di tahun pertama
                $tglMulai = $request->tanggal_akuisisi ?: $request->tanggal_beli;
                $startDate = $tglMulai ? \Carbon\Carbon::parse($tglMulai) : now();
                if ($startDate->day > 15) $startDate = $startDate->addMonthNoOverflow()->startOfMonth();
                $bulanTersisa = 13 - $startDate->month; // Sep=9 → 4 bulan

                if ($metodePenyusutan === 'garis_lurus') {
                    $penyusutanPerTahun = ($totalPerolehan - $nilaiResidu) / $umurManfaat;
                    // Tahun pertama pro-rata, per bulan = depr_tahun_pertama / bulan_tersisa
                    $deprTahunPertama   = $penyusutanPerTahun * ($bulanTersisa / 12);
                    $penyusutanPerBulan = $deprTahunPertama / $bulanTersisa;

                } elseif ($metodePenyusutan === 'saldo_menurun') {
                    // DDB: tarif = 2/umur, beban tahunan penuh = book_value × tarif
                    $tarif              = 2 / $umurManfaat;
                    $penyusutanPerTahun = $totalPerolehan * $tarif; // tahun penuh tahun 1
                    // Tahun pertama pro-rata
                    $deprTahunPertama   = $penyusutanPerTahun * ($bulanTersisa / 12);
                    // Per bulan = depr_tahun_pertama / bulan_tersisa (konsisten)
                    $penyusutanPerBulan = $deprTahunPertama / $bulanTersisa;

                } else {
                    // Sum of years digits
                    $sumOfYears         = ($umurManfaat * ($umurManfaat + 1)) / 2;
                    $penyusutanPerTahun = (($totalPerolehan - $nilaiResidu) * $umurManfaat) / $sumOfYears;
                    $deprTahunPertama   = $penyusutanPerTahun * ($bulanTersisa / 12);
                    $penyusutanPerBulan = $deprTahunPertama / $bulanTersisa;
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
        $aset->load('kategori.jenisAset', 'assetCoa', 'accumDepreciationCoa', 'expenseCoa');
        
        // Update nilai buku berdasarkan bulan saat ini
        $aset->updateNilaiBukuRealTime();
        
        // Hitung total perolehan
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
        
        // Generate jadwal penyusutan per bulan
        $depreciationData = $this->depreciationService->generateMonthlySchedule($aset);
        
        // Hitung penyusutan per bulan dan per tahun yang benar berdasarkan metode
        $nilaiDisusutkan = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
        
        switch ($aset->metode_penyusutan) {
            case 'garis_lurus':
                $penyusutanPerTahun = $nilaiDisusutkan / $aset->umur_manfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                break;

            case 'saldo_menurun':
                // DDB: penyusutan per tahun = book_value_awal × (2/umur)
                // Per bulan = depr_tahun_pertama / bulan_tersisa (pro-rata, konsisten)
                $penyusutanPerTahun = $totalPerolehan * (2 / $aset->umur_manfaat);
                $penyusutanPerBulan = (float)($aset->getAttributes()['penyusutan_per_bulan'] ?? 0);
                if ($penyusutanPerBulan <= 0) {
                    $tglMulai  = $aset->tanggal_akuisisi ?? $aset->tanggal_beli;
                    $start     = \Carbon\Carbon::parse($tglMulai);
                    if ($start->day > 15) $start = $start->addMonthNoOverflow()->startOfMonth();
                    $bulanTersisa = 13 - $start->month;
                    $penyusutanPerBulan = ($penyusutanPerTahun * ($bulanTersisa / 12)) / $bulanTersisa;
                }
                break;

            case 'sum_of_years_digits':
                $sumOfYears = ($aset->umur_manfaat * ($aset->umur_manfaat + 1)) / 2;
                $penyusutanPerTahun = ($nilaiDisusutkan * $aset->umur_manfaat) / $sumOfYears;
                $penyusutanPerBulan = (float)($aset->getAttributes()['penyusutan_per_bulan'] ?? 0);
                if ($penyusutanPerBulan <= 0) {
                    $penyusutanPerBulan = $penyusutanPerTahun / 12;
                }
                break;

            default:
                $penyusutanPerTahun = $nilaiDisusutkan / $aset->umur_manfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                break;
        }
        
        // Log untuk debugging
        \Log::info("SHOW PAGE - Aset: {$aset->nama_aset} (ID: {$aset->id})");
        \Log::info("  Total Perolehan: Rp " . number_format($totalPerolehan, 0, ',', '.'));
        \Log::info("  Nilai Residu: Rp " . number_format($aset->nilai_residu ?? 0, 0, ',', '.'));
        \Log::info("  Nilai Disusutkan: Rp " . number_format($nilaiDisusutkan, 0, ',', '.'));
        \Log::info("  Umur Manfaat: {$aset->umur_manfaat} tahun");
        \Log::info("  Metode: {$aset->metode_penyusutan}");
        \Log::info("  Penyusutan/bulan (SHOW): Rp " . number_format($penyusutanPerBulan, 2, ',', '.'));
        \Log::info("  Akumulasi saat ini: Rp " . number_format($aset->akumulasi_penyusutan, 2, ',', '.'));
        \Log::info("  Nilai buku saat ini: Rp " . number_format($aset->nilai_buku, 2, ',', '.'));
        
        // Cek apakah aset sudah pernah diposting penyusutannya
        $sudahDiposting = JournalEntry::where('ref_type', 'depr')
            ->where('ref_id', $aset->id)
            ->exists();
        
        // Data summary untuk view
        $asetSummary = [
            'total_perolehan' => $totalPerolehan,
            'penyusutan_per_tahun' => $penyusutanPerTahun,
            'penyusutan_per_bulan' => $penyusutanPerBulan,
            'akumulasi_penyusutan' => $aset->akumulasi_penyusutan,
            'nilai_buku_saat_ini' => $aset->nilai_buku,
            'sudah_diposting' => $sudahDiposting
        ];
        
        return view('master-data.aset.show', compact('aset', 'depreciationData', 'totalPerolehan', 'penyusutanPerTahun', 'penyusutanPerBulan', 'asetSummary'));
    }

    /**
     * Hitung akumulasi penyusutan teoretis dari tanggal perolehan hingga tanggal tertentu
     */
    private function hitungAkumulasiPenyusutan($aset, $tanggalPerolehan, $tanggalSekarang)
    {
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);
        $umurManfaat = (int)($aset->umur_manfaat ?? 0);
        $metode = $aset->metode_penyusutan ?? 'garis_lurus';
        
        if ($totalPerolehan <= 0 || $umurManfaat <= 0 || $tanggalPerolehan >= $tanggalSekarang) {
            return 0;
        }
        
        $akumulasi = 0;
        $nilaiBuku = $totalPerolehan;
        $tahunMulai = $tanggalPerolehan->year;
        $bulanMulai = $tanggalPerolehan->month;
        $tahunAkhir = $tanggalSekarang->year;
        $bulanAkhir = $tanggalSekarang->month;
        
        switch ($metode) {
            case 'saldo_menurun':
                // Double Declining Balance Method - Hitung per bulan untuk akurasi
                $rate = 2 / $umurManfaat; // 40% untuk umur 5 tahun
                
                $currentDate = $tanggalPerolehan->copy();
                $bulanCount = 0;
                
                while ($currentDate <= $tanggalSekarang && $nilaiBuku > $nilaiResidu) {
                    $bulanCount++;
                    
                    // Hitung penyusutan bulanan berdasarkan nilai buku awal bulan
                    $penyusutanBulanan = min($nilaiBuku * $rate / 12, $nilaiBuku - $nilaiResidu);
                    
                    $akumulasi += $penyusutanBulanan;
                    $nilaiBuku -= $penyusutanBulanan;
                    
                    $currentDate->addMonth();
                    
                    // Jika sudah mencapai nilai residu, stop
                    if ($nilaiBuku <= $nilaiResidu) {
                        $akumulasi = $totalPerolehan - $nilaiResidu;
                        break;
                    }
                }
                break;
                
            case 'sum_of_years_digits':
                // Sum of Years Digits Method - Hitung per bulan
                $sumOfYears = $umurManfaat * ($umurManfaat + 1) / 2; // 15 untuk umur 5 tahun
                $tahunKe = 1;
                
                $currentDate = $tanggalPerolehan->copy();
                
                while ($currentDate <= $tanggalSekarang && $akumulasi < ($totalPerolehan - $nilaiResidu)) {
                    $sisaUmur = max(0, $umurManfaat - ($tahunKe - 1));
                    $penyusutanTahunan = ($totalPerolehan - $nilaiResidu) * $sisaUmur / $sumOfYears;
                    $penyusutanBulanan = $penyusutanTahunan / 12;
                    
                    // Cek apakah masih dalam tahun yang sama
                    if ($currentDate->month == 1) {
                        $tahunKe++;
                    }
                    
                    $akumulasi += $penyusutanBulanan;
                    $currentDate->addMonth();
                    
                    // Jika sudah mencapai nilai residu, stop
                    if ($akumulasi >= ($totalPerolehan - $nilaiResidu)) {
                        $akumulasi = $totalPerolehan - $nilaiResidu;
                        break;
                    }
                }
                break;
                
            case 'garis_lurus':
            default:
                // Straight Line Method - Hitung per bulan
                $totalPenyusutan = $totalPerolehan - $nilaiResidu;
                $penyusutanPerTahun = $totalPenyusutan / $umurManfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                
                // Hitung total bulan dari perolehan hingga sekarang
                $totalBulan = $tanggalSekarang->diffInMonths($tanggalPerolehan) + 1;
                $akumulasi = min($penyusutanPerBulan * $totalBulan, $totalPenyusutan);
                break;
        }
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Aset $aset)
    {
        // Cek apakah aset terkunci
        if ($aset->locked) {
            return redirect()->route('master-data.aset.index')
                ->with('error', 'Aset "' . $aset->nama_aset . '" terkunci dan tidak dapat diedit.');
        }
        
        $jenisAsets = JenisAset::with('kategories')->get();
        $metodePenyusutan = [
            'garis_lurus' => 'Garis Lurus (Straight Line)',
            'saldo_menurun' => 'Saldo Menurun (Declining Balance)',
            'sum_of_years_digits' => 'Jumlah Angka Tahun (Sum of Years Digits)',
        ];
        
        // COA data for dropdowns - with fallback to prevent undefined variables
        $coaAsets = Coa::where('tipe_akun', 'LIKE', '%Aset%')
            ->orWhere('tipe_akun', 'LIKE', '%ASET%')
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
        
        // Load relasi untuk aset yang akan diedit
        $aset->load('kategori.jenisAset', 'assetCoa', 'accumDepreciationCoa', 'expenseCoa');
        
        // Cek apakah aset sudah pernah diposting penyusutannya
        $sudahDiposting = JournalEntry::where('ref_type', 'depr')
            ->where('ref_id', $aset->id)
            ->exists();
        
        // Hitung data lengkap aset
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
        $nilaiDisusutkan = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
        
        // Hitung jadwal penyusutan jika aset disusutkan
        $jadwalPenyusutan = [];
        if ($aset->kategori && $aset->kategori->disusutkan && $aset->umur_manfaat > 0) {
            $jadwalPenyusutan = $this->generateDepreciationSchedule($aset, $totalPerolehan);
        }
        
        // Data summary aset
        $asetSummary = [
            'total_perolehan' => $totalPerolehan,
            'nilai_disusutkan' => $nilaiDisusutkan,
            'penyusutan_per_tahun' => $aset->penyusutan_per_tahun ?? 0,
            'penyusutan_per_bulan' => $aset->penyusutan_per_bulan ?? 0,
            'akumulasi_penyusutan' => $aset->akumulasi_penyusutan ?? 0,
            'nilai_buku_saat_ini' => $totalPerolehan - ($aset->akumulasi_penyusutan ?? 0),
            'sudah_diposting' => $sudahDiposting,
            'jadwal_penyusutan' => $jadwalPenyusutan
        ];
        
        return view('master-data.aset.edit', compact(
            'aset',
            'jenisAsets', 
            'metodePenyusutan',
            'coaAsets',
            'coaAkumulasi', 
            'coaBeban',
            'asetSummary'
        ));
    }
    
    /**
     * Generate depreciation schedule for asset
     */
    private function generateDepreciationSchedule($aset, $totalPerolehan)
    {
        $schedule    = [];
        $umurManfaat = (int)$aset->umur_manfaat;
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);

        if ($umurManfaat <= 0 || $totalPerolehan <= 0) return $schedule;

        // Tentukan bulan tersisa di tahun pertama
        $tanggalMulai = $aset->tanggal_akuisisi ?? $aset->tanggal_beli;
        $startDate    = $tanggalMulai ? \Carbon\Carbon::parse($tanggalMulai) : now();
        if ($startDate->day > 15) $startDate = $startDate->addMonthNoOverflow()->startOfMonth();
        $bulanTersisaTahunPertama = 13 - $startDate->month; // mis. Sep → 4

        $nilaiBuku   = $totalPerolehan;
        $totalAkumul = 0;
        $startYear   = $startDate->year; // tahun kalender mulai (mis. 2022)

        for ($tahun = 1; $tahun <= $umurManfaat; $tahun++) {
            // Jumlah bulan dalam segmen tahun ini
            $jmlBulan = ($tahun === 1) ? $bulanTersisaTahunPertama : 12;
            $jmlBulan = min($jmlBulan, $umurManfaat * 12 - (($tahun - 1) * 12));

            switch ($aset->metode_penyusutan) {
                case 'garis_lurus':
                    // Beban tahunan penuh, pro-rata tahun pertama
                    $deprYearFull = ($totalPerolehan - $nilaiResidu) / $umurManfaat;
                    $penyusutan   = ($tahun === 1)
                        ? $deprYearFull * ($bulanTersisaTahunPertama / 12)
                        : $deprYearFull;
                    break;

                case 'saldo_menurun':
                    // DDB: tarif = 2/umur, beban = book_value × tarif
                    $deprYearFull = $nilaiBuku * (2 / $umurManfaat);
                    $penyusutan   = ($tahun === 1)
                        ? $deprYearFull * ($bulanTersisaTahunPertama / 12)
                        : $deprYearFull;
                    break;

                case 'sum_of_years_digits':
                    $sumOfYears   = ($umurManfaat * ($umurManfaat + 1)) / 2;
                    $sisaTahun    = $umurManfaat - $tahun + 1;
                    $deprYearFull = (($totalPerolehan - $nilaiResidu) * $sisaTahun) / $sumOfYears;
                    $penyusutan   = ($tahun === 1)
                        ? $deprYearFull * ($bulanTersisaTahunPertama / 12)
                        : $deprYearFull;
                    break;

                default:
                    $penyusutan = 0;
            }

            // Jangan melebihi sisa yang bisa disusutkan
            $penyusutan = min($penyusutan, $nilaiBuku - $nilaiResidu);
            if ($penyusutan <= 0) break;

            $totalAkumul += $penyusutan;
            $nilaiBuku   -= $penyusutan;

            $schedule[] = [
                'tahun'       => $startYear + ($tahun - 1), // 2022, 2023, 2024, ...
                'penyusutan'  => round($penyusutan, 2),
                'akumulasi'   => round($totalAkumul, 2),
                'nilai_buku'  => round($nilaiBuku, 2),
                'depr_month'  => round($penyusutan / $jmlBulan, 2),
                'jumlah_bulan'=> $jmlBulan,
            ];

            if ($nilaiBuku <= $nilaiResidu) break;
        }

        return $schedule;
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Aset $aset)
    {
        // Cek apakah aset terkunci
        if ($aset->locked) {
            return redirect()->route('master-data.aset.index')
                ->with('error', 'Aset "' . $aset->nama_aset . '" terkunci dan tidak dapat diperbarui.');
        }
        
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
        
        // Normalize input data sebelum validasi
        $request->merge([
            'harga_perolehan' => $this->normalizeMoney($request->input('harga_perolehan')),
            'biaya_perolehan' => $this->normalizeMoney($request->input('biaya_perolehan')),
            'nilai_residu' => $this->normalizeMoney($request->input('nilai_residu')),
        ]);
        
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
                $nilaiResidu      = (float) $request->nilai_residu;
                $umurManfaat      = (int) $request->umur_manfaat;
                $metodePenyusutan = $request->metode_penyusutan;

                // Tentukan bulan tersisa di tahun pertama
                $tglMulai  = $request->tanggal_akuisisi ?: $request->tanggal_beli ?: $aset->tanggal_akuisisi ?: $aset->tanggal_beli;
                $startDate = $tglMulai ? \Carbon\Carbon::parse($tglMulai) : now();
                if ($startDate->day > 15) $startDate = $startDate->addMonthNoOverflow()->startOfMonth();
                $bulanTersisa = 13 - $startDate->month;

                if ($metodePenyusutan === 'garis_lurus') {
                    $penyusutanPerTahun = ($totalPerolehan - $nilaiResidu) / $umurManfaat;
                    $deprTahunPertama   = $penyusutanPerTahun * ($bulanTersisa / 12);
                    $penyusutanPerBulan = $deprTahunPertama / $bulanTersisa;

                } elseif ($metodePenyusutan === 'saldo_menurun') {
                    $penyusutanPerTahun = $totalPerolehan * (2 / $umurManfaat);
                    $deprTahunPertama   = $penyusutanPerTahun * ($bulanTersisa / 12);
                    $penyusutanPerBulan = $deprTahunPertama / $bulanTersisa;

                } else {
                    $sumOfYears         = ($umurManfaat * ($umurManfaat + 1)) / 2;
                    $penyusutanPerTahun = (($totalPerolehan - $nilaiResidu) * $umurManfaat) / $sumOfYears;
                    $deprTahunPertama   = $penyusutanPerTahun * ($bulanTersisa / 12);
                    $penyusutanPerBulan = $deprTahunPertama / $bulanTersisa;
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
        // Cek apakah aset terkunci
        if ($aset->locked) {
            return redirect()->route('master-data.aset.index')
                ->with('error', 'Aset "' . $aset->nama_aset . '" terkunci dan tidak dapat dihapus.');
        }
        
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
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:jenis_asets,nama',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

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
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'jenis_aset_id' => 'required|exists:jenis_asets,id',
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:20|unique:kategori_asets,kode',
            'umur_ekonomis' => 'nullable|integer|min:0|max:100',
            'tarif_penyusutan' => 'nullable|numeric|min:0|max:100',
            'disusutkan' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

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

            // Auto-calculate tarif penyusutan dari umur ekonomis jika tarif tidak diisi
            $umurEkonomis = $request->umur_ekonomis ?? 0;
            $tarifPenyusutan = $request->tarif_penyusutan ?? 0;
            if ($umurEkonomis > 0 && $tarifPenyusutan == 0) {
                $tarifPenyusutan = round(100 / $umurEkonomis, 2);
            }

            $disusutkan = $request->has('disusutkan') ? true : false;

            $kategoriAset = KategoriAset::create([
                'jenis_aset_id' => $request->jenis_aset_id,
                'kode' => $kode,
                'nama' => $request->nama,
                'umur_ekonomis' => $umurEkonomis,
                'tarif_penyusutan' => $tarifPenyusutan,
                'disusutkan' => $disusutkan,
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

    /**
     * Post individual asset depreciation for current month
     */
    public function postIndividualDepreciation(Request $request, Aset $aset)
    {
        try {
            $currentMonth = now()->format('Y-m');
            
            // Check if asset has required COA relationships
            if (!$aset->expense_coa_id || !$aset->accum_depr_coa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aset belum memiliki COA yang lengkap. Silakan lengkapi COA Beban Penyusutan dan COA Akumulasi Penyusutan terlebih dahulu.'
                ]);
            }
            
            // Check if depreciation already posted for this month
            $existingEntry = \App\Models\JurnalUmum::where('tanggal', now()->endOfMonth()->format('Y-m-d'))
                ->where('keterangan', 'LIKE', "%{$aset->nama_aset}%")
                ->where('keterangan', 'LIKE', '%Penyusutan%')
                ->first();
                
            if ($existingEntry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penyusutan untuk aset ini sudah diposting bulan ini'
                ]);
            }

            // Calculate monthly depreciation
            $monthlyDepreciation = $this->depreciationService->calculateCurrentMonthDepreciation($aset);
            
            if ($monthlyDepreciation <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada penyusutan untuk diposting (nilai 0 atau aset sudah habis disusutkan)'
                ]);
            }

            // Create journal entries
            $tanggal = now()->endOfMonth()->format('Y-m-d');
            $keterangan = "Penyusutan Aset {$aset->nama_aset} ({$aset->metode_penyusutan}) {$currentMonth}";

            // Debit: Beban Penyusutan
            \App\Models\JurnalUmum::create([
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'coa_id' => $aset->expense_coa_id,
                'debit' => $monthlyDepreciation,
                'kredit' => 0,
                'referensi' => $aset->kode_aset,
                'tipe_referensi' => 'depreciation'
            ]);

            // Credit: Akumulasi Penyusutan
            \App\Models\JurnalUmum::create([
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'coa_id' => $aset->accum_depr_coa_id,
                'debit' => 0,
                'kredit' => $monthlyDepreciation,
                'referensi' => $aset->kode_aset,
                'tipe_referensi' => 'depreciation'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penyusutan berhasil diposting',
                'amount' => number_format($monthlyDepreciation, 0, ',', '.')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error posting individual depreciation', [
                'aset_id' => $aset->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Normalize money format from Indonesian to database format
     */
    private function normalizeMoney($value): ?string
    {
        if (!$value) return $value;

        // Remove Rp, spaces, dots, and convert to decimal format
        $v = str_replace(['Rp', '.', ' '], ['', ''], $value);
        return $v;
    }
}

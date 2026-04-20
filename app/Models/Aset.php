<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\KategoriAset;

class Aset extends Model
{
    protected $table = 'asets';
    
    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori_aset_id',
        'harga_perolehan',
        'biaya_perolehan',
        'nilai_residu',
        'umur_manfaat',
        'penyusutan_per_tahun',
        'penyusutan_per_bulan',
        'nilai_buku',
        'tanggal_beli',
        'tanggal_akuisisi',
        'status',
        'metode_penyusutan',
        'tarif_penyusutan',
        'bulan_mulai',
        'tanggal_perolehan',
        'akumulasi_penyusutan',
        'keterangan',
        'updated_by',
        'locked',
        'asset_coa_id',
        'accum_depr_coa_id',
        'expense_coa_id'
    ];

    protected $casts = [
        'tanggal_beli' => 'date',
        'tanggal_akuisisi' => 'date',
        'tanggal_perolehan' => 'date',
        'harga_perolehan' => 'decimal:2',
        'biaya_perolehan' => 'decimal:2',
        'nilai_residu' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'akumulasi_penyusutan' => 'decimal:2',
        'penyusutan_per_tahun' => 'decimal:2',
        'penyusutan_per_bulan' => 'decimal:2',
        'umur_manfaat' => 'integer',
    ];

    /**
     * Relationship ke COA
     */
    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }

    /**
     * Relationship ke COA Aset
     */
    public function assetCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'asset_coa_id');
    }

    /**
     * Relationship ke COA Akumulasi Penyusutan
     */
    public function accumDepreciationCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'accum_depr_coa_id');
    }

    /**
     * Relationship ke COA Beban Penyusutan
     */
    public function expenseCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'expense_coa_id');
    }

    /**
     * Relationship ke KategoriAset
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_aset_id');
    }

    /**
     * Relationship ke User (created_by)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship ke User (updated_by)
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate kode aset otomatis
     * Format: AST-YYYYMM-XXXX
     */
    public static function generateKodeAset()
    {
        $prefix = 'AST-' . date('Ym') . '-';
        $lastAsset = self::where('kode_aset', 'like', $prefix . '%')
            ->orderBy('kode_aset', 'desc')
            ->first();

        $number = $lastAsset ? (int) str_replace($prefix, '', $lastAsset->kode_aset) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method untuk menangani event model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->kode_aset) {
                $model->kode_aset = self::generateKodeAset();
            }
            $model->metode_penyusutan = $model->metode_penyusutan ?? 'garis_lurus';
            $model->nilai_residu = $model->nilai_residu ?? 0;
            $model->nilai_buku = ($model->harga_perolehan ?? 0) + ($model->biaya_perolehan ?? 0);
            $model->akumulasi_penyusutan = $model->akumulasi_penyusutan ?? 0;
            $model->status = $model->status ?? 'aktif';
            if (Schema::hasColumn('asets', 'created_by')) {
                $model->created_by = $model->created_by ?? Auth::id();
            }
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    /**
     * Hitung penyusutan (untuk kompatibilitas dengan AsetController)
     */
    public function hitungPenyusutan()
    {
        // Calculate nilai_buku based on total perolehan (harga + biaya) and nilai_residu
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $this->nilai_buku = $total - (float)($this->nilai_residu ?? 0);
    }

    /**
     * Hitung beban penyusutan bulanan
     */
    public function hitungBebanPenyusutanBulanan(): float
    {
        $tahunan = $this->hitungBebanPenyusutanTahunan();
        return $tahunan / 12;
    }

    /**
     * Hitung beban penyusutan tahunan
     */
    public function hitungBebanPenyusutanTahunan(): float
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $tarif = $this->tarif_penyusutan ? ($this->tarif_penyusutan / 100) : 0;
        return $tarif > 0 ? ($total * $tarif) : 0.0;
    }

    /**
     * Hitung beban penyusutan tahun pertama (proporsional berdasarkan bulan perolehan)
     */
    public function hitungPenyusutanTahunPertama(): float
    {
        $tahunan = $this->hitungBebanPenyusutanTahunan();
        
        if ($tahunan <= 0) {
            return 0.0;
        }
        
        // Untuk metode garis lurus, tahun pertama selalu penuh
        if ($this->metode_penyusutan === 'garis_lurus') {
            return $tahunan;
        }
        
        // Ambil tanggal perolehan (tanggal_akuisisi atau tanggal_beli)
        $tanggalPerolehan = $this->tanggal_akuisisi ?? $this->tanggal_beli;
        
        // Jika ada bulan_mulai yang diset, gunakan itu
        if ($this->bulan_mulai && $this->bulan_mulai >= 1 && $this->bulan_mulai <= 12) {
            $bulanPerolehan = $this->bulan_mulai;
        } elseif ($tanggalPerolehan) {
            $tanggal = \Carbon\Carbon::parse($tanggalPerolehan);
            $bulanPerolehan = $tanggal->month;
        } else {
            return $tahunan; // Jika tidak ada tanggal atau bulan_mulai, gunakan tahunan penuh
        }
        
        // Hitung sisa bulan dari bulan perolehan sampai Desember
        $sisaBulan = 12 - $bulanPerolehan + 1; // +1 karena termasuk bulan perolehan
        
        // Proporsional penyusutan untuk tahun pertama
        return $tahunan * ($sisaBulan / 12);
    }

    /**
     * Cek apakah aset bisa dihapus
     */
    public function bisaDihapus(): bool
    {
        return $this->akumulasi_penyusutan == 0;
    }

    /**
     * Update nilai buku berdasarkan akumulasi penyusutan
     */
    public function updateNilaiBuku(): void
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $this->nilai_buku = $total - (float)($this->akumulasi_penyusutan ?? 0);
        $this->save();
    }

    /**
     * Hitung dan update nilai buku berdasarkan bulan saat ini
     */
    public function updateNilaiBukuRealTime(): void
    {
        $totalPerolehan = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($this->nilai_residu ?? 0);
        $umurManfaat = (int)($this->umur_manfaat ?? 0);
        
        if ($totalPerolehan <= 0 || $umurManfaat <= 0) {
            return;
        }
        
        // Hitung akumulasi penyusutan sampai bulan saat ini
        $akumulasiPenyusutan = $this->hitungAkumulasiPenyusutanSaatIni();
        
        // Update nilai buku
        $nilaiBukuSaatIni = $totalPerolehan - $akumulasiPenyusutan;
        
        $this->update([
            'akumulasi_penyusutan' => round($akumulasiPenyusutan, 2),
            'nilai_buku' => round($nilaiBukuSaatIni, 2),
        ]);
    }

    /**
     * Hitung akumulasi penyusutan sampai bulan saat ini
     */
    public function hitungAkumulasiPenyusutanSaatIni(): float
    {
        $totalPerolehan = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($this->nilai_residu ?? 0);
        $umurManfaat = (int)($this->umur_manfaat ?? 0);
        
        if ($totalPerolehan <= 0 || $umurManfaat <= 0) {
            return 0;
        }
        
        // Tentukan tanggal mulai penyusutan
        $tanggalPerolehan = Carbon::parse($this->tanggal_akuisisi ?? $this->tanggal_beli ?? $this->created_at);
        $tanggalSekarang = Carbon::now();
        
        // Aturan: jika tanggal > 15, mulai bulan berikutnya
        if ($tanggalPerolehan->day > 15) {
            $tanggalPerolehan->addMonth()->day(1);
        } else {
            $tanggalPerolehan->day(1);
        }
        
        // Hitung bulan yang sudah berlalu (tidak termasuk bulan ini karena belum selesai)
        $bulanBerlalu = $tanggalPerolehan->diffInMonths($tanggalSekarang->startOfMonth());
        
        // Batasi maksimal sesuai umur manfaat
        $maxBulan = $umurManfaat * 12;
        $bulanBerlalu = min($bulanBerlalu, $maxBulan);
        
        $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
        $akumulasiPenyusutan = 0;
        
        switch ($this->metode_penyusutan) {
            case 'garis_lurus':
                $penyusutanPerBulan = $nilaiDisusutkan / ($umurManfaat * 12);
                $akumulasiPenyusutan = $penyusutanPerBulan * $bulanBerlalu;
                break;
                
            case 'saldo_menurun':
                // Double Declining Balance - hitung per bulan dengan nilai buku yang menurun
                $nilaiBuku = $totalPerolehan;
                $rateTahunan = 2 / $umurManfaat; // Contoh: 2/5 = 0.4 = 40%
                $rateBulanan = $rateTahunan / 12;
                
                for ($i = 0; $i < $bulanBerlalu; $i++) {
                    $penyusutanBulanIni = $nilaiBuku * $rateBulanan;
                    
                    // Pastikan tidak melewati nilai residu
                    if ($nilaiBuku - $penyusutanBulanIni < $nilaiResidu) {
                        $penyusutanBulanIni = $nilaiBuku - $nilaiResidu;
                    }
                    
                    $akumulasiPenyusutan += $penyusutanBulanIni;
                    $nilaiBuku -= $penyusutanBulanIni;
                    
                    // Stop jika sudah mencapai nilai residu
                    if ($nilaiBuku <= $nilaiResidu) {
                        break;
                    }
                }
                break;
                
            case 'sum_of_years_digits':
                // Sum of Years Digits - hitung per tahun lalu distribusi per bulan
                $sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2;
                $tahunSelesai = floor($bulanBerlalu / 12);
                $bulanSisa = $bulanBerlalu % 12;
                
                // Hitung penyusutan untuk tahun-tahun yang sudah selesai
                for ($tahun = 1; $tahun <= $tahunSelesai; $tahun++) {
                    $sisaUmur = $umurManfaat - $tahun + 1;
                    $penyusutanTahunIni = ($nilaiDisusutkan * $sisaUmur) / $sumOfYears;
                    $akumulasiPenyusutan += $penyusutanTahunIni;
                }
                
                // Hitung penyusutan untuk bulan-bulan di tahun berjalan
                if ($bulanSisa > 0 && $tahunSelesai < $umurManfaat) {
                    $tahunBerjalan = $tahunSelesai + 1;
                    $sisaUmur = $umurManfaat - $tahunBerjalan + 1;
                    $penyusutanTahunBerjalan = ($nilaiDisusutkan * $sisaUmur) / $sumOfYears;
                    $penyusutanPerBulanTahunIni = $penyusutanTahunBerjalan / 12;
                    $akumulasiPenyusutan += $penyusutanPerBulanTahunIni * $bulanSisa;
                }
                break;
                
            default:
                $penyusutanPerBulan = $nilaiDisusutkan / ($umurManfaat * 12);
                $akumulasiPenyusutan = $penyusutanPerBulan * $bulanBerlalu;
                break;
        }
        
        // Pastikan tidak melebihi nilai yang bisa disusutkan
        return min($akumulasiPenyusutan, $nilaiDisusutkan);
    }

    /**
     * Hitung penyusutan per bulan untuk bulan saat ini berdasarkan metode
     */
    public function hitungPenyusutanPerBulanSaatIni(): float
    {
        $totalPerolehan = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($this->nilai_residu ?? 0);
        $umurManfaat = (int)($this->umur_manfaat ?? 0);
        
        if ($totalPerolehan <= 0 || $umurManfaat <= 0) {
            return 0;
        }
        
        $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
        
        // Tentukan bulan ke berapa dalam siklus penyusutan
        $tanggalPerolehan = Carbon::parse($this->tanggal_akuisisi ?? $this->tanggal_beli ?? $this->created_at);
        $tanggalSekarang = Carbon::now();
        
        if ($tanggalPerolehan->day > 15) {
            $tanggalPerolehan->addMonth()->day(1);
        } else {
            $tanggalPerolehan->day(1);
        }
        
        $bulanKe = $tanggalPerolehan->diffInMonths($tanggalSekarang->startOfMonth()) + 1;
        $tahunKe = ceil($bulanKe / 12);
        
        // Check if depreciation period has ended
        if ($bulanKe > ($umurManfaat * 12)) {
            return 0; // Fully depreciated
        }
        
        switch ($this->metode_penyusutan) {
            case 'garis_lurus':
                // Straight Line: Constant monthly depreciation
                return $nilaiDisusutkan / ($umurManfaat * 12);
                
            case 'saldo_menurun':
                // Double Declining Balance - berdasarkan nilai buku saat ini
                $rateTahunan = 2 / $umurManfaat;
                $rateBulanan = $rateTahunan / 12;
                
                // Get current book value (without calling this method to avoid recursion)
                $akumulasiSebelumnya = $this->hitungAkumulasiPenyusutanSaatIni();
                $nilaiBukuSaatIni = $totalPerolehan - $akumulasiSebelumnya;
                
                $penyusutanBulanIni = $nilaiBukuSaatIni * $rateBulanan;
                
                // Pastikan tidak melewati nilai residu
                if ($nilaiBukuSaatIni - $penyusutanBulanIni < $nilaiResidu) {
                    $penyusutanBulanIni = max(0, $nilaiBukuSaatIni - $nilaiResidu);
                }
                
                return max(0, $penyusutanBulanIni);
                
            case 'sum_of_years_digits':
                // Sum of Years Digits - berdasarkan tahun ke berapa
                if ($tahunKe > $umurManfaat) {
                    return 0; // Sudah habis disusutkan
                }
                
                $sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2;
                $sisaUmur = $umurManfaat - $tahunKe + 1;
                $penyusutanTahunIni = ($nilaiDisusutkan * $sisaUmur) / $sumOfYears;
                
                return $penyusutanTahunIni / 12;
                
            default:
                return $nilaiDisusutkan / ($umurManfaat * 12);
        }
    }

    /**
     * Get nilai buku real-time (accessor)
     */
    public function getNilaiBukuRealTimeAttribute(): float
    {
        $totalPerolehan = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $akumulasiPenyusutan = $this->hitungAkumulasiPenyusutanSaatIni();
        
        return $totalPerolehan - $akumulasiPenyusutan;
    }

    /**
     * Update penyusutan values based on current tarif
     */
    public function updatePenyusutanValues(): void
    {
        $this->penyusutan_per_tahun = $this->hitungBebanPenyusutanTahunan();
        $this->penyusutan_per_bulan = $this->hitungBebanPenyusutanBulanan();
        $this->save();
    }

    /**
     * Accessor: penyusutan per tahun (depreciation_per_year) ala contoh.
     */
    public function getDepreciationPerYearAttribute(): float
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $tarif = $this->tarif_penyusutan ? ($this->tarif_penyusutan / 100) : 0;
        return $tarif > 0 ? ($total * $tarif) : 0.0;
    }

    /**
     * Accessor untuk display penyusutan per tahun (selalu penuh)
     * Prioritas: nilai kolom DB jika ada, fallback ke kalkulasi dari tarif_penyusutan
     */
    public function getPenyusutanPerTahunAttribute(): float
    {
        $dbValue = (float)($this->attributes['penyusutan_per_tahun'] ?? 0);
        if ($dbValue > 0) {
            return $dbValue;
        }
        return $this->getDepreciationPerYearAttribute();
    }

    /**
     * Accessor untuk display penyusutan per bulan
     * Prioritas: nilai kolom DB jika ada, fallback ke kalkulasi dari tarif_penyusutan
     */
    public function getPenyusutanPerBulanAttribute(): float
    {
        $dbValue = (float)($this->attributes['penyusutan_per_bulan'] ?? 0);
        if ($dbValue > 0) {
            return $dbValue;
        }
        return $this->getPenyusutanPerTahunAttribute() / 12;
    }

    /**
     * Monthly schedule aggregated by year with monthly_breakdown, following the user's example.
     */
    public function calculateDepreciationSchedule(): array
    {
        $startDate = Carbon::parse($this->tanggal_akuisisi ?? $this->tanggal_beli ?? now());
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $residu = (float)($this->nilai_residu ?? 0);
        $umur = (int)($this->umur_manfaat ?? $this->umur_ekonomis_tahun ?? 0);
        if ($umur <= 0 || $total <= 0) return [];

        $perBulan = ($total - $residu) / ($umur * 12);
        $akumulasi = 0.0; $nilaiBuku = $total;
        $byYear = [];

        for ($m = 0; $m < $umur * 12; $m++) {
            $current = $startDate->copy()->addMonths($m);
            $year = $current->year;

            $akumulasi += $perBulan;
            $nilaiBuku -= $perBulan;

            if (!isset($byYear[$year])) {
                $byYear[$year] = [
                    'tahun' => $year,
                    'biaya_penyusutan' => 0,
                    'akumulasi_penyusutan' => 0,
                    'nilai_buku' => $total,
                    'start_month' => $current->copy(),
                    'end_month' => $current->copy(),
                    'monthly_breakdown' => [],
                ];
            }

            $byYear[$year]['monthly_breakdown'][] = [
                'month' => $current->format('F Y'),
                'biaya_penyusutan' => round($perBulan, 2),
                'akumulasi_penyusutan' => round($akumulasi, 2),
                'nilai_buku' => round(max($nilaiBuku, $residu), 2),
            ];

            $byYear[$year]['biaya_penyusutan'] += $perBulan;
            $byYear[$year]['akumulasi_penyusutan'] = $akumulasi;
            $byYear[$year]['nilai_buku'] = max($nilaiBuku, $residu);
            $byYear[$year]['end_month'] = $current->copy();
        }

        foreach ($byYear as &$row) {
            $row['biaya_penyusutan'] = round($row['biaya_penyusutan'], 2);
            $row['akumulasi_penyusutan'] = round($row['akumulasi_penyusutan'], 2);
            $row['nilai_buku'] = round($row['nilai_buku'], 2);
            $row['periode'] = $row['tahun'] . ' (' . $row['start_month']->format('F') . ' – ' . $row['end_month']->format('F') . ')';
            unset($row['start_month'], $row['end_month']);
        }

        return array_values($byYear);
    }

    /**
     * Generate jadwal penyusutan tahunan (garis lurus)
     */
    public function jadwalPenyusutan(): array
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $residu = (float)($this->nilai_residu ?? 0);
        $umur = (int)($this->umur_manfaat ?? $this->umur_ekonomis_tahun ?? 0);
        if ($umur <= 0 || $total <= 0) return [];

        $base = max($total - $residu, 0);
        $perTahun = $base / $umur;
        $start = $this->tanggal_akuisisi ?? $this->tanggal_beli ?? now()->toDateString();
        $startYear = Carbon::parse($start)->year;

        $acc = 0.0; $book = $total; $rows = [];
        for ($i = 0; $i < $umur; $i++) {
            $year = $startYear + $i;
            $dep = min($perTahun, max($book - $residu, 0));
            $acc += $dep; $book -= $dep;
            $rows[] = [
                'tahun' => $year,
                'beban_penyusutan' => round($dep, 2),
                'akumulasi_penyusutan' => round($acc, 2),
                'nilai_buku_akhir' => round($book, 2),
            ];
        }
        return $rows;
    }
}

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
        'locked'
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
        // Untuk Sum Of Years Digits, gunakan jadwal bulanan untuk mendapatkan nilai yang tepat
        if ($this->metode_penyusutan === 'sum_of_years_digits') {
            $tanggalPerolehan = $this->tanggal_akuisisi ?? $this->tanggal_beli ?? now();
            $startDate = Carbon::parse($tanggalPerolehan);
            
            $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
            $residu = (float)($this->nilai_residu ?? 0);
            $umur = (int)($this->umur_manfaat ?? 0);
            
            $jadwalBulanan = $this->jadwalBulananJumlahAngkaTahun($total, $residu, $umur, $startDate);
            
            // Kembalikan nilai bulanan pertama
            if (!empty($jadwalBulanan)) {
                return $jadwalBulanan[0]['biaya_penyusutan'];
            }
            
            return 0.0;
        }
        
        // Untuk metode lain, gunakan logika existing
        $tahunan = $this->hitungBebanPenyusutanTahunan();
        return $tahunan / 12;
    }

    /**
     * Hitung beban penyusutan tahunan berdasarkan metode
     */
    public function hitungBebanPenyusutanTahunan(): float
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $residu = (float)($this->nilai_residu ?? 0);
        $umur = (int)($this->umur_manfaat ?? 0);
        
        if ($umur <= 0 || $total <= 0) return 0.0;
        
        switch ($this->metode_penyusutan) {
            case 'garis_lurus':
                // (Harga Perolehan - Nilai Residu) / Umur Manfaat
                return ($total - $residu) / $umur;
                
            case 'saldo_menurun':
                // Double Declining Balance: (100% / Umur Manfaat) × 2 × Nilai Buku Awal
                $tarif = (2 / $umur) * 100;
                return $total * ($tarif / 100);
                
            case 'sum_of_years_digits':
                // Total angka tahun: n + (n-1) + ... + 1 = n(n+1)/2
                // Gunakan jadwal penyusutan untuk mendapatkan nilai yang tepat
                $tanggalPerolehan = $this->tanggal_akuisisi ?? $this->tanggal_beli ?? now();
                $startYear = Carbon::parse($tanggalPerolehan)->year;
                $startMonth = Carbon::parse($tanggalPerolehan)->month;
                
                $jadwal = $this->jadwalPenyusutanJumlahAngkaTahun($total, $residu, $umur, $startYear, $startMonth);
                
                // Cari beban penyusutan untuk periode pertama
                if (!empty($jadwal)) {
                    return $jadwal[0]['beban_penyusutan'];
                }
                
                return 0.0; // Jika tidak ditemukan
                
            default:
                $tarif = $this->tarif_penyusutan ? ($this->tarif_penyusutan / 100) : 0;
                return $tarif > 0 ? ($total * $tarif) : 0.0;
        }
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
        
        // Ambil tanggal perolehan (tanggal_akuisisi atau tanggal_beli)
        $tanggalPerolehan = $this->tanggal_akuisisi ?? $this->tanggal_beli;
        
        if (!$tanggalPerolehan) {
            return $tahunan; // Jika tidak ada tanggal, gunakan tahunan penuh
        }
        
        $tanggal = \Carbon\Carbon::parse($tanggalPerolehan);
        $bulanPerolehan = $tanggal->month;
        
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
        return $this->hitungBebanPenyusutanTahunan();
    }

    /**
     * Accessor untuk display penyusutan per tahun (selalu penuh)
     */
    public function getPenyusutanPerTahunAttribute(): float
    {
        return $this->getDepreciationPerYearAttribute();
    }

    /**
     * Accessor untuk display penyusutan per bulan
     */
    public function getPenyusutanPerBulanAttribute(): float
    {
        return $this->hitungBebanPenyusutanBulanan();
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
     * Generate jadwal penyusutan tahunan untuk semua metode
     */
    public function jadwalPenyusutan(): array
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $residu = (float)($this->nilai_residu ?? 0);
        $umur = (int)($this->umur_manfaat ?? 0);
        
        if ($umur <= 0 || $total <= 0) return [];
        
        $tanggalPerolehan = $this->tanggal_akuisisi ?? $this->tanggal_beli ?? now();
        $startYear = Carbon::parse($tanggalPerolehan)->year;
        $startMonth = Carbon::parse($tanggalPerolehan)->month;
        
        switch ($this->metode_penyusutan) {
            case 'garis_lurus':
                return $this->jadwalPenyusutanGarisLurus($total, $residu, $umur, $startYear, $startMonth);
                
            case 'saldo_menurun':
                return $this->jadwalPenyusutanSaldoMenurun($total, $residu, $umur, $startYear, $startMonth);
                
            case 'sum_of_years_digits':
                return $this->jadwalPenyusutanJumlahAngkaTahun($total, $residu, $umur, $startYear, $startMonth);
                
            default:
                return [];
        }
    }
    
    /**
     * Generate jadwal penyusutan per bulan (untuk modal detail)
     */
    public function jadwalPenyusutanPerBulan(): array
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $residu = (float)($this->nilai_residu ?? 0);
        $umur = (int)($this->umur_manfaat ?? 0);
        
        if ($umur <= 0 || $total <= 0) return [];
        
        $tanggalPerolehan = $this->tanggal_akuisisi ?? $this->tanggal_beli ?? now();
        $startDate = Carbon::parse($tanggalPerolehan);
        
        switch ($this->metode_penyusutan) {
            case 'garis_lurus':
                return $this->jadwalBulananGarisLurus($total, $residu, $umur, $startDate);
                
            case 'saldo_menurun':
                return $this->jadwalBulananSaldoMenurun($total, $residu, $umur, $startDate);
                
            case 'sum_of_years_digits':
                return $this->jadwalBulananJumlahAngkaTahun($total, $residu, $umur, $startDate);
                
            default:
                return [];
        }
    }
    
    /**
     * Jadwal bulanan metode Garis Lurus
     */
    private function jadwalBulananGarisLurus($total, $residu, $umur, $startDate): array
    {
        $penyusutanTahunan = ($total - $residu) / $umur;
        $penyusutanBulanan = $penyusutanTahunan / 12;
        $akumulasi = 0.0;
        $nilaiBuku = $total;
        $rows = [];
        
        $totalBulan = $umur * 12;
        
        for ($bulan = 0; $bulan < $totalBulan; $bulan++) {
            $currentDate = $startDate->copy()->addMonths($bulan);
            
            $penyusutan = $penyusutanBulanan;
            
            // Pastikan tidak melebihi sisa nilai yang bisa disusutkan
            $maxPenyusutan = $nilaiBuku - $residu;
            if ($penyusutan > $maxPenyusutan) {
                $penyusutan = $maxPenyusutan;
            }
            
            $akumulasi += $penyusutan;
            $nilaiBuku -= $penyusutan;
            
            $rows[] = [
                'periode' => $currentDate->format('F Y'),
                'biaya_penyusutan' => round($penyusutan, 2),
                'akumulasi_penyusutan' => round($akumulasi, 2),
                'nilai_buku' => round($nilaiBuku, 2),
            ];
            
            // Stop jika sudah mencapai nilai residu
            if ($nilaiBuku <= $residu) {
                break;
            }
        }
        
        return $rows;
    }
    
    /**
     * Jadwal bulanan metode Saldo Menurun Ganda
     */
    private function jadwalBulananSaldoMenurun($total, $residu, $umur, $startDate): array
    {
        $tarif = (2 / $umur) * 100; // Double declining rate
        $tarifBulanan = $tarif / 100;
        $akumulasi = 0.0;
        $nilaiBuku = $total;
        $rows = [];
        
        $totalBulan = $umur * 12;
        $startYear = $startDate->year;
        
        for ($bulan = 0; $bulan < $totalBulan; $bulan++) {
            $currentDate = $startDate->copy()->addMonths($bulan);
            $tahun = $currentDate->year;
            
            // Cek apakah ini adalah bulan terakhir
            $isLastMonth = ($bulan == $totalBulan - 1);
            $isFirstYear = ($tahun == $startYear);
            
            // Hitung penyusutan bulanan
            if ($isLastMonth) {
                // Bulan akhir: gunakan sisa nilai buku - nilai residu
                $penyusutan = $nilaiBuku - $residu;
            } elseif ($isFirstYear && $bulan == 0) {
                // Bulan pertama: gunakan tarif × nilai buku awal
                $penyusutan = $nilaiBuku * $tarifBulanan;
                
                // Pastikan tidak melebihi sisa nilai yang bisa disusutkan
                $maxPenyusutan = $nilaiBuku - $residu;
                if ($penyusutan > $maxPenyusutan) {
                    $penyusutan = $maxPenyusutan;
                }
            } else {
                // Bulan lain: gunakan tarif × nilai buku
                $penyusutan = $nilaiBuku * $tarifBulanan;
                
                // Pastikan tidak melebihi sisa nilai yang bisa disusutkan
                $maxPenyusutan = $nilaiBuku - $residu;
                if ($penyusutan > $maxPenyusutan) {
                    $penyusutan = $maxPenyusutan;
                }
            }
            
            $akumulasi += $penyusutan;
            $nilaiBuku -= $penyusutan;
            
            $rows[] = [
                'periode' => $currentDate->format('F Y'),
                'biaya_penyusutan' => round($penyusutan, 2),
                'akumulasi_penyusutan' => round($akumulasi, 2),
                'nilai_buku' => round($nilaiBuku, 2),
            ];
            
            // Stop jika sudah mencapai nilai residu
            if ($nilaiBuku <= $residu) {
                break;
            }
        }
        
        return $rows;
    }
    
    /**
     * Jadwal bulanan metode Jumlah Angka Tahun
     */
    private function jadwalBulananJumlahAngkaTahun($total, $residu, $umur, $startDate): array
    {
        $totalAngkaTahun = $umur * ($umur + 1) / 2;
        $nilaiDisusutkan = $total - $residu;
        $akumulasi = 0.0;
        $nilaiBuku = $total;
        $rows = [];
        
        $startMonth = $startDate->month;
        $startYear = $startDate->year;
        
        // Loop melalui setiap tahun fiskal aset (bukan tahun kalender)
        for ($tahunFiskal = 1; $tahunFiskal <= $umur; $tahunFiskal++) {
            $bobotTahun = $umur - $tahunFiskal + 1; // 5, 4, 3, 2, 1
            $penyusutanTahunanPenuh = $nilaiDisusutkan * ($bobotTahun / $totalAngkaTahun);
            
            // Tentukan pembagian tahun kalender untuk tahun fiskal ini
            if ($tahunFiskal == 1) {
                // Tahun fiskal pertama: mulai dari bulan perolehan
                $bulanPertama = 12 - $startMonth + 1; // sisa bulan di tahun pertama
                $bulanKedua = 12 - $bulanPertama; // bulan di tahun berikutnya
                
                // Bagian pertama di tahun perolehan
                $penyusutanBagian1 = $penyusutanTahunanPenuh * ($bulanPertama / 12);
                $penyusutanPerBulan1 = $penyusutanBagian1 / $bulanPertama;
                
                // Generate bulanan untuk bagian pertama
                for ($i = 0; $i < $bulanPertama; $i++) {
                    $currentDate = $startDate->copy()->addMonths($i);
                    
                    $akumulasi += $penyusutanPerBulan1;
                    $nilaiBuku -= $penyusutanPerBulan1;
                    
                    $rows[] = [
                        'periode' => $currentDate->format('F Y'),
                        'biaya_penyusutan' => round($penyusutanPerBulan1, 2),
                        'akumulasi_penyusutan' => round($akumulasi, 2),
                        'nilai_buku' => round($nilaiBuku, 2),
                    ];
                }
                
                // Bagian kedua di tahun berikutnya
                $penyusutanBagian2 = $penyusutanTahunanPenuh * ($bulanKedua / 12);
                $penyusutanPerBulan2 = $penyusutanBagian2 / $bulanKedua;
                
                // Generate bulanan untuk bagian kedua
                $startDateBagian2 = $startDate->copy()->addMonths($bulanPertama);
                for ($i = 0; $i < $bulanKedua; $i++) {
                    $currentDate = $startDateBagian2->copy()->addMonths($i);
                    
                    $akumulasi += $penyusutanPerBulan2;
                    $nilaiBuku -= $penyusutanPerBulan2;
                    
                    $rows[] = [
                        'periode' => $currentDate->format('F Y'),
                        'biaya_penyusutan' => round($penyusutanPerBulan2, 2),
                        'akumulasi_penyusutan' => round($akumulasi, 2),
                        'nilai_buku' => round($nilaiBuku, 2),
                    ];
                }
                
            } else {
                // Tahun fiskal berikutnya: 12 bulan penuh, dibagi 2 tahun kalender
                // Menggunakan pola Excel: 8 bulan di tahun pertama, 4 bulan di tahun kedua
                
                $tahunKalender1 = $startYear + $tahunFiskal - 1;
                $tahunKalender2 = $startYear + $tahunFiskal;
                
                // Bagian pertama: 8 bulan
                $penyusutanBagian1 = $penyusutanTahunanPenuh * (8 / 12);
                $penyusutanPerBulan1 = $penyusutanBagian1 / 8;
                
                // Generate bulanan untuk bagian pertama (8 bulan)
                $startDateBagian1 = Carbon::create($tahunKalender1, $startMonth, 1);
                for ($i = 0; $i < 8; $i++) {
                    $currentDate = $startDateBagian1->copy()->addMonths($i);
                    
                    $akumulasi += $penyusutanPerBulan1;
                    $nilaiBuku -= $penyusutanPerBulan1;
                    
                    $rows[] = [
                        'periode' => $currentDate->format('F Y'),
                        'biaya_penyusutan' => round($penyusutanPerBulan1, 2),
                        'akumulasi_penyusutan' => round($akumulasi, 2),
                        'nilai_buku' => round($nilaiBuku, 2),
                    ];
                }
                
                // Bagian kedua: 4 bulan
                $penyusutanBagian2 = $penyusutanTahunanPenuh * (4 / 12);
                $penyusutanPerBulan2 = $penyusutanBagian2 / 4;
                
                // Generate bulanan untuk bagian kedua (4 bulan)
                $startDateBagian2 = $startDateBagian1->copy()->addMonths(8);
                for ($i = 0; $i < 4; $i++) {
                    $currentDate = $startDateBagian2->copy()->addMonths($i);
                    
                    $akumulasi += $penyusutanPerBulan2;
                    $nilaiBuku -= $penyusutanPerBulan2;
                    
                    $rows[] = [
                        'periode' => $currentDate->format('F Y'),
                        'biaya_penyusutan' => round($penyusutanPerBulan2, 2),
                        'akumulasi_penyusutan' => round($akumulasi, 2),
                        'nilai_buku' => round($nilaiBuku, 2),
                    ];
                }
            }
            
            // Stop jika sudah mencapai nilai residu
            if ($nilaiBuku <= $residu) {
                break;
            }
        }
        
        return $rows;
    }
    
    /**
     * Jadwal penyusutan metode Garis Lurus
     */
    private function jadwalPenyusutanGarisLurus($total, $residu, $umur, $startYear, $startMonth): array
    {
        $penyusutanTahunan = ($total - $residu) / $umur;
        $penyusutanBulanan = $penyusutanTahunan / 12;
        
        $akumulasi = 0.0;
        $nilaiBuku = $total;
        $rows = [];
        
        // Hitung total bulan yang harus disusutkan
        $totalBulan = $umur * 12;
        
        // Generate per bulan dari tanggal mulai
        for ($bulan = 0; $bulan < $totalBulan; $bulan++) {
            $currentDate = Carbon::create($startYear, $startMonth, 1)->addMonths($bulan);
            $tahun = $currentDate->year;
            
            // Inisialisasi tahun jika belum ada
            if (!isset($rows[$tahun])) {
                $rows[$tahun] = [
                    'tahun' => $tahun,
                    'beban_penyusutan' => 0,
                    'akumulasi_penyusutan' => 0,
                    'nilai_buku_akhir' => $nilaiBuku,
                    'jumlah_bulan' => 0
                ];
            }
            
            // Hitung penyusutan bulanan
            $penyusutanBulanIni = $penyusutanBulanan;
            
            // Pastikan tidak melebihi sisa nilai yang bisa disusutkan
            $maxPenyusutan = $nilaiBuku - $residu;
            if ($penyusutanBulanIni > $maxPenyusutan) {
                $penyusutanBulanIni = $maxPenyusutan;
            }
            
            $akumulasi += $penyusutanBulanIni;
            $nilaiBuku -= $penyusutanBulanIni;
            
            $rows[$tahun]['beban_penyusutan'] += $penyusutanBulanIni;
            $rows[$tahun]['akumulasi_penyusutan'] = $akumulasi;
            $rows[$tahun]['nilai_buku_akhir'] = $nilaiBuku;
            $rows[$tahun]['jumlah_bulan']++;
            
            // Stop jika sudah mencapai nilai residu
            if ($nilaiBuku <= $residu) {
                break;
            }
        }
        
        // Konversi ke array indexed dan format
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'tahun' => $row['tahun'],
                'beban_penyusutan' => round($row['beban_penyusutan'], 2),
                'akumulasi_penyusutan' => round($row['akumulasi_penyusutan'], 2),
                'nilai_buku_akhir' => round($row['nilai_buku_akhir'], 2),
                'jumlah_bulan' => $row['jumlah_bulan']
            ];
        }
        
        return $result;
    }
    
    /**
     * Jadwal penyusutan metode Saldo Menurun Ganda
     */
    private function jadwalPenyusutanSaldoMenurun($total, $residu, $umur, $startYear, $startMonth): array
    {
        $tarif = (2 / $umur) * 100; // Double declining rate
        
        $akumulasi = 0.0;
        $nilaiBuku = $total;
        $rows = [];
        
        // Hitung total bulan yang harus disusutkan
        $totalBulan = $umur * 12;
        
        // Generate per bulan dari tanggal mulai
        for ($bulan = 0; $bulan < $totalBulan; $bulan++) {
            $currentDate = Carbon::create($startYear, $startMonth, 1)->addMonths($bulan);
            $tahun = $currentDate->year;
            
            // Inisialisasi tahun jika belum ada
            if (!isset($rows[$tahun])) {
                $rows[$tahun] = [
                    'tahun' => $tahun,
                    'beban_penyusutan' => 0,
                    'akumulasi_penyusutan' => 0,
                    'nilai_buku_akhir' => $nilaiBuku,
                    'jumlah_bulan' => 0,
                    'nilai_buku_awal_tahun' => $nilaiBuku,
                    'is_first_year' => ($tahun == $startYear),
                    'is_last_year' => false
                ];
            }
            
            // Hitung penyusutan bulanan
            $penyusutanBulanIni = 0;
            
            // Cek apakah ini adalah tahun terakhir (bulan terakhir dari total bulan)
            $isLastYear = ($bulan == $totalBulan - 1);
            
            if ($isLastYear) {
                // Tahun akhir: gunakan sisa nilai buku - nilai residu
                $sisaPenyusutan = $nilaiBuku - $residu;
                $penyusutanBulanIni = $sisaPenyusutan;
                $rows[$tahun]['is_last_year'] = true;
            } elseif ($rows[$tahun]['is_first_year']) {
                // Tahun pertama: gunakan tarif tahunan dibagi 12
                $penyusutanTahunan = $rows[$tahun]['nilai_buku_awal_tahun'] * ($tarif / 100);
                $penyusutanBulanIni = $penyusutanTahunan / 12;
                
                // Pastikan tidak melebihi sisa nilai yang bisa disusutkan
                $maxPenyusutan = $nilaiBuku - $residu;
                if ($penyusutanBulanIni > $maxPenyusutan) {
                    $penyusutanBulanIni = $maxPenyusutan;
                }
            } else {
                // Tahun penuh: gunakan tarif × nilai buku awal tahun
                if ($rows[$tahun]['jumlah_bulan'] == 0) {
                    // Awal tahun, gunakan nilai buku awal tahun
                    $penyusutanTahunan = $rows[$tahun]['nilai_buku_awal_tahun'] * ($tarif / 100);
                    $penyusutanBulanIni = $penyusutanTahunan / 12;
                } else {
                    // Bulan selanjutnya di tahun yang sama, gunakan akumulasi tahunan / 12
                    $targetTahunan = $rows[$tahun]['nilai_buku_awal_tahun'] * ($tarif / 100);
                    $sisaTahunan = $targetTahunan - $rows[$tahun]['beban_penyusutan'];
                    $sisaBulan = 12 - $rows[$tahun]['jumlah_bulan'];
                    $penyusutanBulanIni = $sisaTahunan / $sisaBulan;
                }
                
                // Pastikan tidak melebihi sisa nilai yang bisa disusutkan
                $maxPenyusutan = $nilaiBuku - $residu;
                if ($penyusutanBulanIni > $maxPenyusutan) {
                    $penyusutanBulanIni = $maxPenyusutan;
                }
            }
            
            $akumulasi += $penyusutanBulanIni;
            $nilaiBuku -= $penyusutanBulanIni;
            
            $rows[$tahun]['beban_penyusutan'] += $penyusutanBulanIni;
            $rows[$tahun]['akumulasi_penyusutan'] = $akumulasi;
            $rows[$tahun]['nilai_buku_akhir'] = $nilaiBuku;
            $rows[$tahun]['jumlah_bulan']++;
            
            // Stop jika sudah mencapai nilai residu
            if ($nilaiBuku <= $residu) {
                // Sesuaikan nilai buku akhir agar tepat residu jika terlewat
                $rows[$tahun]['nilai_buku_akhir'] = $residu;
                break;
            }
        }
        
        // Konversi ke array indexed dan format
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'tahun' => $row['tahun'],
                'beban_penyusutan' => round($row['beban_penyusutan'], 2),
                'akumulasi_penyusutan' => round($row['akumulasi_penyusutan'], 2),
                'nilai_buku_akhir' => round($row['nilai_buku_akhir'], 2),
                'jumlah_bulan' => $row['jumlah_bulan']
            ];
        }
        
        return $result;
    }
    
    /**
     * Jadwal penyusutan metode Jumlah Angka Tahun - Excel Style
     */
    private function jadwalPenyusutanJumlahAngkaTahun($total, $residu, $umur, $startYear, $startMonth): array
    {
        $totalAngkaTahun = $umur * ($umur + 1) / 2; // Untuk umur 4 tahun: 4+3+2+1 = 10
        $nilaiDisusutkan = $total - $residu;
        
        $akumulasi = 0.0;
        $nilaiBuku = $total;
        $rows = [];
        
        // Loop melalui setiap tahun fiskal aset
        for ($tahunFiskal = 1; $tahunFiskal <= $umur; $tahunFiskal++) {
            $bobotTahun = $umur - $tahunFiskal + 1; // 4, 3, 2, 1
            $penyusutanTahunanPenuh = $nilaiDisusutkan * ($bobotTahun / $totalAngkaTahun);
            
            if ($tahunFiskal == 1) {
                // Tahun fiskal pertama: mulai dari bulan perolehan
                $bulanPertama = 12 - $startMonth + 1; // sisa bulan di tahun pertama
                $bulanKedua = 12 - $bulanPertama; // bulan di tahun berikutnya
                
                // Bagian pertama di tahun perolehan
                $penyusutanBagian1 = $penyusutanTahunanPenuh * ($bulanPertama / 12);
                $tahunKalender1 = $startYear;
                
                // Bagian kedua di tahun berikutnya
                $penyusutanBagian2 = $penyusutanTahunanPenuh * ($bulanKedua / 12);
                $tahunKalender2 = $startYear + 1;
                
                // Tambahkan baris terpisah untuk setiap bagian
                $akumulasi += $penyusutanBagian1;
                $nilaiBuku -= $penyusutanBagian1;
                $rows[] = [
                    'tahun' => $tahunKalender1,
                    'periode' => $tahunKalender1 . '(' . $bulanPertama . ')',
                    'beban_penyusutan' => $penyusutanBagian1,
                    'akumulasi_penyusutan' => $akumulasi,
                    'nilai_buku_akhir' => $nilaiBuku,
                    'jumlah_bulan' => $bulanPertama
                ];
                
                $akumulasi += $penyusutanBagian2;
                $nilaiBuku -= $penyusutanBagian2;
                $rows[] = [
                    'tahun' => $tahunKalender2,
                    'periode' => $tahunKalender2 . '(' . $bulanKedua . ')',
                    'beban_penyusutan' => $penyusutanBagian2,
                    'akumulasi_penyusutan' => $akumulasi,
                    'nilai_buku_akhir' => $nilaiBuku,
                    'jumlah_bulan' => $bulanKedua
                ];
                
            } else {
                // Tahun fiskal berikutnya: 12 bulan penuh, dibagi 2 tahun kalender
                // Menggunakan pola Excel: 8 bulan di tahun pertama, 4 bulan di tahun kedua
                
                $tahunKalender1 = $startYear + $tahunFiskal - 1;
                $tahunKalender2 = $startYear + $tahunFiskal;
                
                // Bagian pertama: 8 bulan
                $penyusutanBagian1 = $penyusutanTahunanPenuh * (8 / 12);
                $akumulasi += $penyusutanBagian1;
                $nilaiBuku -= $penyusutanBagian1;
                $rows[] = [
                    'tahun' => $tahunKalender1,
                    'periode' => $tahunKalender1 . '(8)',
                    'beban_penyusutan' => $penyusutanBagian1,
                    'akumulasi_penyusutan' => $akumulasi,
                    'nilai_buku_akhir' => $nilaiBuku,
                    'jumlah_bulan' => 8
                ];
                
                // Bagian kedua: 4 bulan
                $penyusutanBagian2 = $penyusutanTahunanPenuh * (4 / 12);
                $akumulasi += $penyusutanBagian2;
                $nilaiBuku -= $penyusutanBagian2;
                $rows[] = [
                    'tahun' => $tahunKalender2,
                    'periode' => $tahunKalender2 . '(4)',
                    'beban_penyusutan' => $penyusutanBagian2,
                    'akumulasi_penyusutan' => $akumulasi,
                    'nilai_buku_akhir' => $nilaiBuku,
                    'jumlah_bulan' => 4
                ];
            }
            
            // Stop jika sudah mencapai nilai residu
            if ($nilaiBuku <= $residu) {
                break;
            }
        }
        
        // Format nilai
        foreach ($rows as &$row) {
            $row['beban_penyusutan'] = round($row['beban_penyusutan'], 2);
            $row['akumulasi_penyusutan'] = round($row['akumulasi_penyusutan'], 2);
            $row['nilai_buku_akhir'] = round($row['nilai_buku_akhir'], 2);
        }
        
        return $rows;
    }
}

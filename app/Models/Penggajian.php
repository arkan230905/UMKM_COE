<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JurnalUmum;

class Penggajian extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($penggajian) {
            // CRITICAL: Auto-set user_id untuk multi-tenant isolation
            if (empty($penggajian->user_id) && auth()->check()) {
                $penggajian->user_id = auth()->id();
            }
            
            if (empty($penggajian->status_pembayaran)) {
                $penggajian->status_pembayaran = 'belum_lunas';
            }

            // Unique validation: prevent duplicate payroll for same employee in same month/year
            $existing = self::where('pegawai_id', $penggajian->pegawai_id)
                ->where('periode_bulan', $penggajian->periode_bulan)
                ->where('periode_tahun', $penggajian->periode_tahun)
                ->exists();

            if ($existing) {
                throw new \Exception("Penggajian untuk pegawai ini pada periode {$penggajian->periode_bulan}/{$penggajian->periode_tahun} sudah ada.");
            }
        });

        // Auto-create journal entries when penggajian is marked as paid (lunas)
        static::updated(function ($penggajian) {
            // Check if status changed to 'lunas' and no journal entries exist yet
            if ($penggajian->status_pembayaran === 'lunas' &&
                $penggajian->getOriginal('status_pembayaran') !== 'lunas') {

                // Check if journal entries already exist
                $existingJournal = \App\Models\JurnalUmum::where('tipe_referensi', 'penggajian')
                    ->where('referensi', $penggajian->id)
                    ->where('user_id', auth()->id())
                    ->exists();

                if (!$existingJournal) {
                    static::createJournalEntries($penggajian);
                }
            }
        });
    }

    /**
     * Create journal entries for penggajian (auto-called when marked as paid)
     */
    public static function createJournalEntries($penggajian)
    {
        try {
            $pegawai = $penggajian->pegawai;
            if (!$pegawai) {
                throw new \Exception('Pegawai not found for penggajian ID ' . $penggajian->id);
            }

            // Get required COA accounts
            $coaBebanGaji = \App\Models\Coa::where('kode_akun', '52')->first(); // BTKL
            if (!$coaBebanGaji) {
                $coaBebanGaji = \App\Models\Coa::where('kode_akun', '54')->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
            }

            $coaKasBank = \App\Models\Coa::where('kode_akun', $penggajian->coa_kasbank)->first();

            if (!$coaBebanGaji) {
                throw new \Exception('COA Beban Gaji not found');
            }

            if (!$coaKasBank) {
                throw new \Exception('COA Kas/Bank not found for code: ' . $penggajian->coa_kasbank);
            }

            // Create journal entries using JournalService for proper balance validation
            $keterangan = "Penggajian {$pegawai->nama}";
            
            $lines = [
                [
                    'code' => $coaBebanGaji->kode_akun,
                    'debit' => $penggajian->total_gaji,
                    'credit' => 0,
                    'memo' => $keterangan
                ],
                [
                    'code' => $coaKasBank->kode_akun,
                    'debit' => 0,
                    'credit' => $penggajian->total_gaji,
                    'memo' => $keterangan
                ]
            ];

            $journalService = new \App\Services\JournalService();
            $journalService->postWithUser(
                $penggajian->tanggal_penggajian->format('Y-m-d'),
                'penggajian',
                $penggajian->id,
                $keterangan,
                $lines,
                auth()->id() ?? 1
            );

            \Log::info('Journal entries created for penggajian', [
                'penggajian_id' => $penggajian->id,
                'pegawai' => $pegawai->nama,
                'total_gaji' => $penggajian->total_gaji
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create journal entries for penggajian', [
                'penggajian_id' => $penggajian->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected $fillable = [
        'user_id',  // CRITICAL: multi-tenant isolation
        'pegawai_id',
        'periode_bulan',
        'periode_tahun',
        'total_hari_hadir',
        'total_alpha',
        'total_jam',
        'tanggal_penggajian',
        'coa_kasbank',
        'gaji_pokok',
        'tarif_per_jam',
        'tunjangan',
        'tunjangan_jabatan',
        'tunjangan_transport',
        'tunjangan_konsumsi',
        'total_tunjangan',
        'asuransi',
        'bonus',
        'potongan',
        'total_jam_kerja',
        'total_gaji',
        'status_pembayaran',
        'tanggal_dibayar',
        'metode_pembayaran',
        'keterangan',
        // NEW: Produk-based fields
        'produk_hari_1_5',
        'produk_hari_6_10',
        'produk_hari_11_20',
        'produk_hari_21_30',
        'total_produk_bulan',
        'tarif_produk',
        'status_posting',
        'tanggal_posting',
        'mode_input',
        'pembulatan_aktif',
        'pembulatan_step',
        'nominal_pembulatan',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'total_hari_hadir' => 'integer',
        'total_alpha' => 'integer',
        'total_jam' => 'decimal:2',
        'tanggal_penggajian' => 'date',
        'gaji_pokok' => 'decimal:2',
        'tarif_per_jam' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'tunjangan_jabatan' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_konsumsi' => 'decimal:2',
        'total_tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
        'bonus' => 'decimal:2',
        'potongan' => 'decimal:2',
        'total_jam_kerja' => 'decimal:2',
        'total_gaji' => 'decimal:2',
        'coa_kasbank' => 'string',
        'tanggal_dibayar' => 'date',
        // NEW: Produk-based casts
        'produk_hari_1_5' => 'integer',
        'produk_hari_6_10' => 'integer',
        'produk_hari_11_20' => 'integer',
        'produk_hari_21_30' => 'integer',
        'total_produk_bulan' => 'integer',
        'tarif_produk' => 'decimal:2',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * Cek apakah penggajian sudah dibayar
     */
    public function isPaid()
    {
        return $this->status_pembayaran === 'lunas';
    }

    /**
     * Calculate total produk from 4 periode
     */
    public function getTotalProdukBulanAttribute()
    {
        return ($this->produk_hari_1_5 ?? 0) + 
               ($this->produk_hari_6_10 ?? 0) + 
               ($this->produk_hari_11_20 ?? 0) + 
               ($this->produk_hari_21_30 ?? 0);
    }

    /**
     * Calculate gaji bruto based on produk (total_produk × tarif_produk)
     */
    public function getGajiBrutoAttribute()
    {
        $totalProduk = $this->getTotalProdukBulanAttribute();
        $tarifProduk = $this->tarif_produk ?? 0;
        return $totalProduk * $tarifProduk;
    }

    /**
     * Generate monthly payroll from attendance data
     *
     * @param int $bulan Periode bulan (1-12)
     * @param int $tahun Periode tahun
     * @param int|null $targetHariKerja Target hari kerja untuk evaluasi (opsional)
     * @return array Array of created Penggajian records
     */
    public static function generateFromPresensi($bulan, $tahun, $targetHariKerja = null)
    {

        $prefix = config('penggajian_journal.prefix_no_bukti', 'PGJ');
        $date = now()->format('Ymd');
        
        // Cari nomor urut terakhir hari ini
        $lastJournal = JurnalUmum::where('tipe_referensi', 'penggajian')
            ->where('keterangan', 'like', $prefix . '-' . $date . '%')
            ->where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->first();
// Get all presensi for the specified period using MONTH() and YEAR() functions
        $presensis = Presensi::whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun)
            ->get();

        // Group by pegawai_id
        $presensiByPegawai = $presensis->groupBy('pegawai_id');

        foreach ($presensiByPegawai as $pegawaiId => $pegawaiPresensis) {
            $pegawai = Pegawai::find($pegawaiId);
            if (!$pegawai) {
                continue;
            }

            // Calculate totals
            $totalHariHadir = $pegawaiPresensis->whereIn('status', ['Hadir', 'hadir'])->count();
            $totalAlpha = $pegawaiPresensis->whereIn('status', ['Alpha', 'alpha', 'absen'])->count();
            $totalJam = $pegawaiPresensis->sum('jumlah_jam');

            // Get tarif_per_jam from pegawai or jabatan
            $tarifPerJam = $pegawai->tarif_per_jam ?? $pegawai->getTarifPerJamFromJabatanAttribute() ?? 0;

            // Calculate gaji_pokok based on BTKL formula
            $gajiPokok = $totalJam * $tarifPerJam;

            // Get tunjangan from pegawai or jabatan
            $tunjanganJabatan = $pegawai->getTunjanganJabatanAttribute() ?? 0;
            $tunjanganTransport = $pegawai->getTunjanganTransportAttribute() ?? 0;
            $tunjanganKonsumsi = $pegawai->getTunjanganKonsumsiAttribute() ?? 0;
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;

            // Get asuransi from pegawai or jabatan
            $asuransi = $pegawai->getAsuransiFromJabatanAttribute() ?? $pegawai->asuransi ?? 0;

            // Calculate total_gaji (bonus and potongan default to 0)
            $bonus = 0;
            $potongan = 0;
            $totalGaji = $gajiPokok + $totalTunjangan + $bonus - $potongan;

            try {
                // Create penggajian record
                $penggajian = self::create([
                    'pegawai_id' => $pegawaiId,
                    'periode_bulan' => $bulan,
                    'periode_tahun' => $tahun,
                    'total_hari_hadir' => $totalHariHadir,
                    'total_alpha' => $totalAlpha,
                    'total_jam' => $totalJam,
                    'tanggal_penggajian' => now(),
                    'coa_kasbank' => '111', // Default Kas Bank
                    'gaji_pokok' => $gajiPokok,
                    'tarif_per_jam' => $tarifPerJam,
                    'tunjangan' => $totalTunjangan,
                    'tunjangan_jabatan' => $tunjanganJabatan,
                    'tunjangan_transport' => $tunjanganTransport,
                    'tunjangan_konsumsi' => $tunjanganKonsumsi,
                    'total_tunjangan' => $totalTunjangan,
                    'asuransi' => $asuransi,
                    'bonus' => $bonus,
                    'potongan' => $potongan,
                    'total_jam_kerja' => $totalJam,
                    'total_gaji' => $totalGaji,
                    'status_pembayaran' => 'belum_lunas',
                ]);

                $createdPayrolls[] = $penggajian;

                \Log::info('Payroll generated from presensi', [
                    'pegawai' => $pegawai->nama,
                    'periode' => "{$bulan}/{$tahun}",
                    'total_hari_hadir' => $totalHariHadir,
                    'total_jam' => $totalJam,
                    'gaji_pokok' => $gajiPokok,
                ]);

            } catch (\Exception $e) {
                \Log::error('Failed to generate payroll for pegawai', [
                    'pegawai_id' => $pegawaiId,
                    'error' => $e->getMessage(),
                ]);
                // Continue to next pegawai
            }
        }

        return $createdPayrolls;
    }

    /**
     * Get monthly statistics for a pegawai
     *
     * @param int $pegawaiId
     * @param int $bulan
     * @param int $tahun
     * @return array
     */
    public static function getMonthlyStats($pegawaiId, $bulan, $tahun)
    {
        // Use MONTH() and YEAR() SQL functions since presensis table doesn't have periode_bulan/periode_tahun columns
        $presensis = Presensi::where('pegawai_id', $pegawaiId)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun)
            ->get();

        $totalHariHadir = $presensis->whereIn('status', ['Hadir', 'hadir'])->count();
        $totalAlpha = $presensis->whereIn('status', ['Alpha', 'alpha', 'absen'])->count();
        $totalJam = $presensis->sum('jumlah_jam');

        $pegawai = Pegawai::find($pegawaiId);
        $tarifPerJam = $pegawai->tarif_per_jam ?? $pegawai->getTarifPerJamFromJabatanAttribute() ?? 0;
        $estimasiGaji = $totalJam * $tarifPerJam;

        return [
            'total_hari_hadir' => $totalHariHadir,
            'total_alpha' => $totalAlpha,
            'total_jam' => $totalJam,
            'tarif_per_jam' => $tarifPerJam,
            'estimasi_gaji' => $estimasiGaji,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapPresensiBulanan extends Model
{
    use HasFactory;

    protected $table = 'rekap_presensi_bulanan';

    protected $fillable = [
        'pegawai_id',
        'periode_bulan',
        'periode_tahun',
        'total_hari_hadir',
        'total_alpha',
        'total_masuk_saja',
        'total_jam_bulanan',
        'target_hari_kerja',
        'persentase_kehadiran',
        'estimasi_gaji',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'total_hari_hadir' => 'integer',
        'total_alpha' => 'integer',
        'total_masuk_saja' => 'integer',
        'total_jam_bulanan' => 'decimal:2',
        'target_hari_kerja' => 'integer',
        'persentase_kehadiran' => 'decimal:2',
        'estimasi_gaji' => 'decimal:2',
    ];

    /**
     * Relasi ke pegawai
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * Get nama bulan
     */
    public function getNamaBulanAttribute()
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $namaBulan[$this->periode_bulan] ?? '';
    }

    /**
     * Get periode label
     */
    public function getPeriodeLabelAttribute()
    {
        return $this->nama_bulan . ' ' . $this->periode_tahun;
    }

    /**
     * Generate or update rekap for specific pegawai and periode
     */
    public static function generateRekap($pegawaiId, $bulan, $tahun)
    {
        // Get all presensi for this periode
        $presensiList = Presensi::where('pegawai_id', $pegawaiId)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->get();

        // Calculate totals
        $totalHariHadir = $presensiList->where('status', 'Hadir')->count();
        $totalAlpha = $presensiList->where('status', 'Alpha')->count();
        $totalMasukSaja = $presensiList->where('status', 'Masuk Saja')->count();
        $totalJamBulanan = $presensiList->sum('jumlah_jam');

        // Get target hari kerja
        $targetHariKerja = KalenderKerja::getTargetHariKerja($bulan, $tahun);

        // Calculate persentase kehadiran
        $persentaseKehadiran = $targetHariKerja > 0 
            ? ($totalHariHadir / $targetHariKerja) * 100 
            : 0;

        // Get pegawai tarif
        $pegawai = Pegawai::find($pegawaiId);
        $tarifPerJam = $pegawai && $pegawai->jabatan 
            ? $pegawai->jabatan->tarif_btkl 
            : 0;

        // Calculate estimasi gaji
        $estimasiGaji = $totalJamBulanan * $tarifPerJam;

        // Update or create rekap
        return static::updateOrCreate(
            [
                'pegawai_id' => $pegawaiId,
                'periode_bulan' => $bulan,
                'periode_tahun' => $tahun,
            ],
            [
                'total_hari_hadir' => $totalHariHadir,
                'total_alpha' => $totalAlpha,
                'total_masuk_saja' => $totalMasukSaja,
                'total_jam_bulanan' => $totalJamBulanan,
                'target_hari_kerja' => $targetHariKerja,
                'persentase_kehadiran' => $persentaseKehadiran,
                'estimasi_gaji' => $estimasiGaji,
            ]
        );
    }

    /**
     * Generate rekap for all pegawai in specific periode
     */
    public static function generateRekapBulanan($bulan, $tahun)
    {
        $pegawaiList = Pegawai::where('status', 'aktif')->get();
        $results = [];

        foreach ($pegawaiList as $pegawai) {
            $results[] = static::generateRekap($pegawai->id, $bulan, $tahun);
        }

        return $results;
    }
}

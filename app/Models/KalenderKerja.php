<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KalenderKerja extends Model
{
    use HasFactory;

    protected $table = 'kalender_kerja';

    protected $fillable = [
        'bulan',
        'tahun',
        'target_hari_kerja',
        'keterangan',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'target_hari_kerja' => 'integer',
    ];

    /**
     * Get or create kalender kerja for specific month/year
     */
    public static function getOrCreateForPeriode($bulan, $tahun, $targetHariKerja = 26)
    {
        return static::firstOrCreate(
            [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ],
            [
                'target_hari_kerja' => $targetHariKerja,
                'keterangan' => 'Auto-generated',
            ]
        );
    }

    /**
     * Get target hari kerja for specific periode
     */
    public static function getTargetHariKerja($bulan, $tahun)
    {
        $kalender = static::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();
        
        return $kalender ? $kalender->target_hari_kerja : 26; // Default 26 hari
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
        
        return $namaBulan[$this->bulan] ?? '';
    }

    /**
     * Get periode label
     */
    public function getPeriodeLabelAttribute()
    {
        return $this->nama_bulan . ' ' . $this->tahun;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tgl_presensi',
        'jam_masuk',
        'jam_keluar',
        'status',
        'jumlah_jam',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($presensi) {
            if ($presensi->jam_masuk && $presensi->jam_keluar) {
                $jamMasuk = strtotime($presensi->jam_masuk);
                $jamKeluar = strtotime($presensi->jam_keluar);
                $presensi->jumlah_jam = round(($jamKeluar - $jamMasuk) / 3600, 2);
            }
        });

        static::updating(function ($presensi) {
            if ($presensi->jam_masuk && $presensi->jam_keluar) {
                $jamMasuk = strtotime($presensi->jam_masuk);
                $jamKeluar = strtotime($presensi->jam_keluar);
                $presensi->jumlah_jam = round(($jamKeluar - $jamMasuk) / 3600, 2);
            }
        });
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}

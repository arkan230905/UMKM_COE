<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penggajian extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tanggal_penggajian',
        'gaji_pokok',
        'tunjangan',
        'potongan',
        'total_jam_kerja',
        'total_gaji',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}

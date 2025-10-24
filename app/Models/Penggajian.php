<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penggajian extends Model
{
     use HasFactory;

    protected $table = 'penggajians';

    protected $fillable = [
        'pegawai_id', 
        'gaji_pokok', 
        'tunjangan', 
        'potongan', 
        'total_jam_kerja',
        'total_gaji', 
        'tanggal_penggajian'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}

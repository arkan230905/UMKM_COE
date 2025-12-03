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
        'coa_kasbank',
        'gaji_pokok',
        'tarif_per_jam',
        'tunjangan',
        'asuransi',
        'bonus',
        'potongan',
        'total_jam_kerja',
        'total_gaji',
    ];

    protected $casts = [
        'tanggal_penggajian' => 'date',
        'gaji_pokok' => 'decimal:2',
        'tarif_per_jam' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
        'bonus' => 'decimal:2',
        'potongan' => 'decimal:2',
        'total_jam_kerja' => 'decimal:2',
        'total_gaji' => 'decimal:2',
        'coa_kasbank' => 'string',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}

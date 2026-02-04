<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiWajah extends Model
{
    protected $table = 'verifikasi_wajah';
    
    protected $fillable = [
        'kode_pegawai',
        'foto_wajah',
        'encoding_wajah',
        'aktif',
        'tanggal_verifikasi'
    ];
    
    protected $casts = [
        'aktif' => 'boolean',
        'tanggal_verifikasi' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected $dates = [
        'tanggal_verifikasi',
        'created_at',
        'updated_at'
    ];
    
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'kode_pegawai', 'kode_pegawai');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    protected $table = 'presensis';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'pegawai_id',
        'tgl_presensi',
        'jam_masuk',
        'jam_keluar',
        'status',
        'jumlah_jam',
        'keterangan'
    ];

    protected $casts = [
        'tgl_presensi' => 'date',
        'jam_masuk' => 'string',
        'jam_keluar' => 'string',
        'jumlah_jam' => 'decimal:2'
    ];

    protected $dates = [
        'tgl_presensi',
        'created_at',
        'updated_at'
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('pegawai', function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nomor_induk_pegawai', 'like', "%{$search}%");
        })
        ->orWhere('status', 'like', "%{$search}%")
        ->orWhereDate('tgl_presensi', $search);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlasifikasiTunjangan extends Model
{
    protected $table = 'klasifikasi_tunjangans';

    protected $fillable = [
        'jabatan_id',
        'nama_tunjangan',
        'nilai_tunjangan',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'nilai_tunjangan' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }
}

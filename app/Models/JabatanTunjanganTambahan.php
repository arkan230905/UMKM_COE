<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanTunjanganTambahan extends Model
{
    protected $table = 'jabatan_tunjangan_tambahan';

    protected $fillable = [
        'jabatan_id',
        'nama',
        'nominal',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}

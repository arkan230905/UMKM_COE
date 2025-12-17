<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenggajianTunjanganTambahan extends Model
{
    protected $table = 'penggajian_tunjangan_tambahan';

    protected $fillable = [
        'penggajian_id',
        'nama',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function penggajian()
    {
        return $this->belongsTo(Penggajian::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenggajianBonusTambahan extends Model
{
    protected $table = 'penggajian_bonus_tambahan';

    protected $fillable = [
        'penggajian_id',
        'nama',
        'nominal',
    ];

    public function penggajian()
    {
        return $this->belongsTo(Penggajian::class);
    }
}

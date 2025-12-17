<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenggajianPotongan extends Model
{
    use HasFactory;

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Coa;

class Bop extends Model
{
    use HasFactory;

    protected $table = 'bops';

    protected $fillable = [
        'coa_id',
        'keterangan',
        'nominal',
        'tanggal',
    ];

    // Relasi ke COA
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }
}

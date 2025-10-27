<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bop extends Model
{
    use HasFactory;

    protected $table = 'bops'; // pastikan nama tabelnya bener, biasanya jamak (bukan 'bop')

  protected $fillable = [
    'coa_id',
    'keterangan',
    'nominal',
    'tanggal',
];


    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }
}

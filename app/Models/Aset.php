<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    protected $table = 'asets';
    protected $fillable = [
        'nama',
        'kategori',
        'jenis_aset',
        'harga',
        'tanggal_beli',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks'; // atau 'produk' sesuai DB-mu
    protected $fillable = [
        'nama',
        // kolom lain
    ];
}

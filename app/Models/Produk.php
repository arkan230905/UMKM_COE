<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';
    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'harga_jual', // nullable awalnya
        'btkl_default',
        'bop_default',
    ];

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }
}

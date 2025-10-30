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
        'margin_percent',
        'bopb_method',
        'bopb_rate',
        'labor_hours_per_unit',
        'btkl_per_unit',
    ];

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }
}

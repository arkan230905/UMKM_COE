<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_produk', 'harga_jual',
    ];

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }
}

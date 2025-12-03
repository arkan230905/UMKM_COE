<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriProduk extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_produks';
    protected $fillable = [
        'kode_kategori',
        'nama',
        'deskripsi'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the produks for the kategori.
     */
    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}

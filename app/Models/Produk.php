<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';
    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'deskripsi',
        'kategori_id',
        'satuan_id',
        'harga_jual',
        'harga_beli',
        'stok',
        'stok_minimum',
        'btkl_default',
        'bop_default',
        'margin_percent',
        'bopb_method',
        'bopb_rate',
        'labor_hours_per_unit',
        'btkl_per_unit',
    ];
    
    /**
     * Get the kategori that owns the Produk
     */
    /**
     * Get the kategori that owns the Produk
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id')
            ->withDefault([
                'nama' => 'Tidak Diketahui',
                'kode_kategori' => 'N/A'
            ]);
    }
    
    /**
     * Get the satuan that owns the Produk
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id')
            ->withDefault([
                'nama' => 'PCS',
                'kode_satuan' => 'PCS'
            ]);
    }

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }
}

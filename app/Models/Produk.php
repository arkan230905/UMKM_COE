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
        'barcode',
        'nama_produk',
        'foto',
        'deskripsi',
        'kategori_id',
        'satuan_id',
        'harga_jual',
        'harga_bom',
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
     * Generate barcode otomatis untuk produk baru
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($produk) {
            if (empty($produk->barcode)) {
                // Generate barcode format EAN-13: 8992XXXXXXXXX
                $lastId = static::max('id') ?? 0;
                $produk->barcode = '8992' . str_pad($lastId + 1, 9, '0', STR_PAD_LEFT);
            }
        });
    }
    
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

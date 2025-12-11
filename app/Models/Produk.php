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

    // Relasi ke reviews melalui order items
    public function reviews()
    {
        return $this->hasManyThrough(
            Review::class,
            OrderItem::class,
            'produk_id', // Foreign key di order_items
            'order_id', // Foreign key di reviews
            'id', // Local key di produk
            'id' // Local key di order_items
        );
    }

    // Method untuk menghitung rating rata-rata
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    // Method untuk menghitung total reviews
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }
}

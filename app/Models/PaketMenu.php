<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketMenu extends Model
{
    protected $fillable = [
        'nama_paket', 'harga_normal', 'harga_paket', 'diskon_persen', 'status', 'keterangan', 'produk_id',
    ];

    public function details()
    {
        return $this->hasMany(PaketMenuDetail::class);
    }

    /** Produk yang otomatis dibuat saat paket disimpan */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    // Auto-calculate diskon when saving
    protected static function booted()
    {
        static::saving(function ($paket) {
            if ($paket->harga_normal > 0) {
                $paket->diskon_persen = round((($paket->harga_normal - $paket->harga_paket) / $paket->harga_normal) * 100, 2);
            }
        });
    }
}

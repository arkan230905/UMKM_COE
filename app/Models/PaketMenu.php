<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketMenu extends Model
{
    protected $fillable = [
        'user_id', 'nama_paket', 'harga_normal', 'harga_paket', 'diskon_persen', 'status', 'keterangan', 'produk_id',
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
        // Auto-set user_id saat creating
        static::creating(function ($paket) {
            if (empty($paket->user_id) && auth()->check()) {
                $paket->user_id = auth()->id();
            }
        });

        static::saving(function ($paket) {
            if ($paket->harga_normal > 0) {
                $paket->diskon_persen = round((($paket->harga_normal - $paket->harga_paket) / $paket->harga_normal) * 100, 2);
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketMenu extends Model
{
    protected $fillable = [
        'user_id',  // 🔒 SECURITY: Add user_id for multi-tenant isolation
        'nama_paket', 'harga_normal', 'harga_paket', 'diskon_persen', 'status', 'keterangan', 'produk_id',
    ];

    /**
     * Boot the model and add global scope for multi-tenant isolation
     */
    protected static function booted()
    {
        // 🔒 SECURITY: Apply global scope untuk multi-tenant isolation
        static::addGlobalScope(new \App\Scopes\UserScope);

        static::creating(function ($paket) {
            // 🔒 SECURITY: Auto-fill user_id for multi-tenant isolation
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

    public function details()
    {
        return $this->hasMany(PaketMenuDetail::class);
    }

    /** Produk yang otomatis dibuat saat paket disimpan */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketMenu extends Model
{
    protected $fillable = [
        'nama_paket', 'harga_normal', 'harga_paket', 'diskon_persen', 'status', 'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(PaketMenuDetail::class);
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

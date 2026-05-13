<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketMenuDetail extends Model
{
    protected $fillable = ['paket_menu_id', 'produk_id', 'jumlah'];

    public function paketMenu()
    {
        return $this->belongsTo(PaketMenu::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}

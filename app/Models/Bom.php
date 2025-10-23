<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = ['produk_id'];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function details()
    {
        return $this->hasMany(BomDetail::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelians';

    protected $fillable = [
        'vendor_id',
        'tanggal',
        'payment_method',
        'total',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function details()
    {
        return $this->hasMany(PembelianDetail::class);
    }
}

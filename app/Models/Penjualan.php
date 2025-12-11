<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualans';

    protected $fillable = [
        'nomor_penjualan',
        'produk_id',
        'tanggal',
        'payment_method',
        'harga_satuan',
        'jumlah',
        'diskon_nominal',
        'total',
        'user_id',
        'order_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate nomor penjualan saat creating
        static::creating(function ($penjualan) {
            if (empty($penjualan->nomor_penjualan)) {
                $tanggal = $penjualan->tanggal ?? now();
                $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
                
                // Hitung jumlah penjualan hari ini
                $count = static::whereDate('tanggal', $tanggal)->count() + 1;
                
                // Format: PJ-YYYYMMDD-0001
                $penjualan->nomor_penjualan = 'PJ-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}

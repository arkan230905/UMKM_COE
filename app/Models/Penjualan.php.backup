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

    public function penjualanDetails()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    // Relasi ke retur (untuk mengecek status retur)
    public function returs()
    {
        return $this->hasMany(Retur::class, 'ref_id');
    }

    // Relasi ke retur penjualan
    public function returPenjualans()
    {
        return $this->hasMany(ReturPenjualan::class);
    }

    // Accessor untuk status retur
    public function getStatusReturAttribute()
    {
        $hasReturn = $this->returs()->exists();
        return $hasReturn ? 'Ada Retur' : 'Tidak Ada Retur';
    }

    // Method untuk menghitung total qty retur
    public function getTotalQtyReturAttribute()
    {
        return $this->returPenjualans()
            ->with('detailReturPenjualans')
            ->get()
            ->sum(function($retur) {
                return $retur->detailReturPenjualans->sum('qty_retur');
            });
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
                
                $penjualan->nomor_penjualan = 'SJ-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
        
        static::created(function ($penjualan) {
            // Create automatic journal entries
            \App\Services\JournalService::createJournalFromPenjualan($penjualan);
        });
        
        static::updated(function ($penjualan) {
            // Recreate journal entries if transaction is updated
            \App\Services\JournalService::createJournalFromPenjualan($penjualan);
        });
    }
}

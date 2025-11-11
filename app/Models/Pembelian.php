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
        'kode_pembelian',
        'tanggal',
        'total_harga',
        'terbayar',
        'sisa_pembayaran',
        'status',
        'payment_method',
        'keterangan'
    ];
    
    protected $dates = ['tanggal'];
    
    protected $appends = ['sisa_utang', 'status_pembayaran', 'total'];

    protected $casts = [
        'tanggal' => 'date',
        'total_harga' => 'decimal:2',
        'terbayar' => 'decimal:2',
        'sisa_pembayaran' => 'decimal:2'
    ];
    
    /**
     * Get the vendor that owns the pembelian.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    
    /**
     * Get the pembelian details for the pembelian.
     */
    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class);
    }
    
    /**
     * Get the pelunasan for the pembelian.
     */
    public function pelunasan()
    {
        return $this->hasMany(PelunasanUtang::class, 'pembelian_id');
    }
    
    /**
     * Get the ap settlements for the pembelian.
     */
    public function apSettlements()
    {
        return $this->hasMany(ApSettlement::class, 'pembelian_id');
    }
    
    /**
     * Get the total attribute (alias for total_harga).
     */
    public function getTotalAttribute()
    {
        return $this->attributes['total_harga'] ?? 0;
    }
    
    /**
     * Get the sisa utang attribute.
     */
    public function getSisaUtangAttribute()
    {
        // Gunakan sisa_pembayaran dari database jika ada, kalau tidak hitung dari total_harga - terbayar
        if (isset($this->attributes['sisa_pembayaran'])) {
            return max(0, $this->attributes['sisa_pembayaran']);
        }
        return max(0, ($this->total_harga ?? 0) - ($this->terbayar ?? 0));
    }
    
    /**
     * Get the status pembayaran attribute.
     */
    public function getStatusPembayaranAttribute()
    {
        if ($this->payment_method === 'cash' || $this->status === 'lunas') {
            return 'Lunas';
        }
        return 'Belum Lunas';
    }

    /**
     * Alias for pembelianDetails()
     */
    public function details()
    {
        return $this->pembelianDetails();
    }
    
    /**
     * Boot method untuk event handling
     */
    protected static function boot()
    {
        parent::boot();
        
        // Event setelah pembelian dibuat
        static::created(function ($pembelian) {
            \Log::info('Pembelian created', [
                'id' => $pembelian->id,
                'total' => $pembelian->total_harga,
            ]);
        });
    }
}

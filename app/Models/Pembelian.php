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
        'total',
        'terbayar',
        'status',
        'payment_method'
    ];
    
    protected $dates = ['tanggal'];
    
    protected $appends = ['sisa_utang', 'status_pembayaran'];

    protected $casts = [
        'tanggal' => 'date',
        'total' => 'decimal:2',
        'terbayar' => 'decimal:2'
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
     * Get the sisa utang attribute.
     */
    public function getSisaUtangAttribute()
    {
        return max(0, $this->total - $this->terbayar);
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
    /**
     * Alias for pembelianDetails()
     */
    public function details()
    {
        return $this->pembelianDetails();
    }
}

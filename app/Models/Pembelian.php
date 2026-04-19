<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembelians';

    protected $fillable = [
        'nomor_pembelian',
        'nomor_faktur',
        'vendor_id',
        'kode_pembelian',
        'tanggal',
        'subtotal',
        'biaya_kirim',
        'ppn_persen',
        'ppn_nominal',
        'total_harga',
        'terbayar',
        'sisa_pembayaran',
        'status',
        'payment_method',
        'keterangan',
        'bank_id'
    ];
    
    protected $dates = ['tanggal', 'deleted_at'];
    
    protected $appends = ['sisa_utang', 'status_pembayaran'];

    protected $casts = [
        'tanggal' => 'date',
        'subtotal' => 'float',
        'biaya_kirim' => 'float',
        'ppn_persen' => 'float',
        'ppn_nominal' => 'float',
        'total_harga' => 'float',
        'terbayar' => 'float',
        'sisa_pembayaran' => 'float'
    ];
    
    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::created(function ($pembelian) {
            // Create automatic journal entries
            \App\Services\JournalService::createJournalFromPembelian($pembelian);
        });
        
        static::updated(function ($pembelian) {
            // Recreate journal entries if transaction is updated
            \App\Services\JournalService::createJournalFromPembelian($pembelian);
        });
        
        static::deleting(function ($pembelian) {
            // Delete related pembelian details
            $pembelian->pembelianDetails()->delete();
            
            // Delete related AP settlements
            $pembelian->apSettlements()->delete();
            
            // Delete related pelunasan
            $pembelian->pelunasan()->delete();
            
            // Delete journal entries
            \App\Services\JournalService::deleteByRef('purchase', $pembelian->id);
            
            // Update stock layers - reverse the stock movements
            foreach ($pembelian->pembelianDetails as $detail) {
                // Create reverse stock movement
                \App\Models\StockMovement::create([
                    'item_type' => 'material',
                    'item_id' => $detail->bahan_baku_id ?? $detail->bahan_pendukung_id,
                    'direction' => 'in', // Reverse direction to add stock back
                    'qty' => $detail->jumlah,
                    'unit_cost' => $detail->harga_satuan,
                    'remaining_qty' => \App\Models\StockLayer::where('item_type', 'material')
                        ->where('item_id', $detail->bahan_baku_id ?? $detail->bahan_pendukung_id)
                        ->sum('remaining_qty') + $detail->jumlah,
                    'tanggal' => now(),
                    'keterangan' => 'Pembatalan pembelian #' . $pembelian->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Update stock layer
                $stockLayer = \App\Models\StockLayer::where('item_type', 'material')
                    ->where('item_id', $detail->bahan_baku_id ?? $detail->bahan_pendukung_id)
                    ->first();
                    
                if ($stockLayer) {
                    $stockLayer->remaining_qty += $detail->jumlah;
                    $stockLayer->save();
                }
            }
        });
    }
    
    /**
     * Get the vendor that owns the pembelian.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    
    /**
     * Get the kas/bank account for the pembelian.
     */
    public function kasBank()
    {
        return $this->belongsTo(Coa::class, 'bank_id');
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
     * Get the pembayaran for the pembelian (alias for pelunasan).
     */
    public function pembayaran()
    {
        return $this->pelunasan();
    }
    
    /**
     * Get the ap settlements for the pembelian.
     */
    public function apSettlements()
    {
        return $this->hasMany(ApSettlement::class, 'pembelian_id');
    }
    
    /**
     * Get the total dibayar attribute.
     */
    public function getTotalDibayarAttribute()
    {
        return $this->pelunasan()->sum('jumlah');
    }
    
    /**
     * Get the sisa utang attribute.
     */
    public function getSisaUtangAttribute()
    {
        // Calculate from total_harga - total payments
        $totalHarga = $this->total_harga ?? 0;
        $totalDibayar = $this->total_dibayar;
        return max(0, $totalHarga - $totalDibayar);
    }
    
    /**
     * Get the status pembayaran attribute.
     */
    public function getStatusPembayaranAttribute()
    {
        if ($this->payment_method === 'cash' || $this->payment_method === 'transfer') {
            return 'Lunas';
        }
        
        $totalDibayar = $this->total_dibayar;
        $totalHarga = $this->total_harga ?? 0;
        
        if ($totalDibayar == 0) {
            return 'Belum Bayar';
        } elseif ($totalDibayar < $totalHarga) {
            return 'Sebagian';
        } else {
            return 'Lunas';
        }
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
        
        // Auto-generate nomor pembelian saat creating
        static::creating(function ($pembelian) {
            if (empty($pembelian->nomor_pembelian)) {
                $tanggal = $pembelian->tanggal ?? now();
                $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
                
                // Hitung jumlah pembelian hari ini
                $count = static::whereDate('tanggal', $tanggal)->count() + 1;
                
                // Format: PB-YYYYMMDD-0001
                $pembelian->nomor_pembelian = 'PB-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
        
        // Event setelah pembelian dibuat
        static::created(function ($pembelian) {
            \Log::info('Pembelian created', [
                'id' => $pembelian->id,
                'nomor' => $pembelian->nomor_pembelian,
                'total' => $pembelian->total_harga,
            ]);
        });
    }
}

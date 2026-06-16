<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualans';

    protected $fillable = [
        'order_id',  // Link to orders table
        'user_id',  // CRITICAL: multi-tenant isolation
        'nomor_penjualan',
        'produk_id',
        'tanggal',
        'payment_method',
        'payment_status',  // Track payment confirmation
        'payment_confirmed_at',  // When payment was confirmed
        'harga_satuan',
        'jumlah',
        'diskon_nominal',
        'total',
        'biaya_ongkir',
        'biaya_ppn',
        'grand_total',
        'bukti_pembayaran',
        'catatan_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'payment_confirmed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getTanggalTransaksiAttribute()
    {
        if (!$this->tanggal) {
            return null;
        }

        $tanggal = $this->tanggal instanceof Carbon ? $this->tanggal->copy() : Carbon::parse($this->tanggal);
        $jam = $this->created_at ? $this->created_at->format('H:i:s') : '00:00:00';

        return Carbon::parse($tanggal->format('Y-m-d') . ' ' . $jam);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
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
            // CRITICAL: Auto-set user_id untuk multi-tenant isolation
            // ONLY set if user_id is not already explicitly set
            if (is_null($penjualan->user_id) && auth()->check()) {
                $penjualan->user_id = auth()->id();
            }
            
            if (empty($penjualan->nomor_penjualan)) {
                $tanggal = $penjualan->tanggal ?? now();
                $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
                
                // Generate unique nomor penjualan dengan retry mechanism
                $maxRetries = 10;
                $attempt = 0;
                
                do {
                    // Cari nomor terakhir untuk tanggal hari ini agar urutan direset per hari
                    $lastNumber = static::where('nomor_penjualan', 'like', 'SJ-' . $date . '-%')
                        ->orderBy('nomor_penjualan', 'desc')
                        ->value('nomor_penjualan');
                    
                    if ($lastNumber) {
                        // Extract nomor urut dari nomor terakhir
                        $parts = explode('-', $lastNumber);
                        $lastSequence = intval(end($parts));
                        $nextSequence = $lastSequence + 1;
                    } else {
                        $nextSequence = 1;
                    }
                    
                    // Format: SJ-YYYYMMDD-001
                    $nomorPenjualan = 'SJ-' . $date . '-' . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
                    
                    // Cek apakah nomor sudah ada (untuk memastikan unique)
                    $exists = static::where('nomor_penjualan', $nomorPenjualan)->exists();
                    
                    if (!$exists) {
                        $penjualan->nomor_penjualan = $nomorPenjualan;
                        break;
                    }
                    
                    $attempt++;
                    
                    // Jika sudah mencoba berkali-kali, tambahkan timestamp untuk memastikan unique
                    if ($attempt >= $maxRetries) {
                        $timestamp = now()->format('His'); // HHMMSS
                        $penjualan->nomor_penjualan = 'SJ-' . $date . '-' . $timestamp;
                        break;
                    }
                    
                } while ($attempt < $maxRetries);
            }
        });
        
        // Create journals when penjualan is created with payment_status = 'paid'
        static::created(function ($penjualan) {
            if ($penjualan->payment_status === 'paid') {
                try {
                    \App\Services\JournalService::createJournalFromPenjualan($penjualan);
                    \Log::info('Journal created successfully for penjualan (on creation)', [
                        'penjualan_id' => $penjualan->id,
                        'nomor_penjualan' => $penjualan->nomor_penjualan,
                        'payment_status' => $penjualan->payment_status
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create journal for penjualan on creation', [
                        'penjualan_id' => $penjualan->id,
                        'nomor_penjualan' => $penjualan->nomor_penjualan,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        });
        
        // Also handle updates when payment_status changes to 'paid'
        static::updated(function ($penjualan) {
            // Only create/update journals if payment_status changed to 'paid'
            if ($penjualan->wasChanged('payment_status') && $penjualan->payment_status === 'paid') {
                try {
                    \App\Services\JournalService::createJournalFromPenjualan($penjualan);
                    \Log::info('Journal created successfully for penjualan (payment confirmed)', [
                        'penjualan_id' => $penjualan->id,
                        'nomor_penjualan' => $penjualan->nomor_penjualan,
                        'payment_status' => $penjualan->payment_status
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create journal for penjualan', [
                        'penjualan_id' => $penjualan->id,
                        'nomor_penjualan' => $penjualan->nomor_penjualan,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        });
    }

    /**
     * Generate unique nomor penjualan to prevent duplicates
     */
    public static function generateUniqueNomorPenjualan($date)
    {
        // Use a while loop to ensure we get a unique number
        $maxAttempts = 100;
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            // Get the highest number for today
            $lastNomor = static::where('nomor_penjualan', 'like', 'SJ-' . $date . '-%')
                ->orderBy('nomor_penjualan', 'desc')
                ->value('nomor_penjualan');
            
            if ($lastNomor) {
                // Extract the number part and increment
                $lastNumber = (int) substr($lastNomor, -3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            $newNomor = 'SJ-' . $date . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            
            // Check if this number already exists (double-check)
            if (!static::where('nomor_penjualan', $newNomor)->exists()) {
                return $newNomor;
            }
            
            $attempts++;
        }
        
        // If we get here, something is seriously wrong
        throw new \Exception('Unable to generate unique nomor penjualan after ' . $maxAttempts . ' attempts');
    }

    public function getCatatanAttribute()
    {
        return $this->catatan_pembayaran;
    }

    public function setCatatanAttribute($value)
    {
        $this->attributes['catatan_pembayaran'] = $value;
    }
}

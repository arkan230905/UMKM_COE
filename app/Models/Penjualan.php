<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Penjualan extends Model
{
    use \App\Traits\HasUserScope;
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
        'approval_status', // Track online order approval
        'payment_confirmed_at',  // When payment was confirmed
        'coa_id',
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

    public function getIsOnlineAttribute()
    {
        return !is_null($this->order_id);
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
        return $this->belongsTo(Produk::class, 'produk_id')->withoutGlobalScopes();
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
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

    public function buktiPembayarans()
    {
        return $this->hasMany(BuktiPembayaran::class);
    }

    public function getAllBuktiPembayaranAttribute()
    {
        $allBukti = $this->buktiPembayarans ?? collect();
        
        if (!empty($this->bukti_pembayaran)) {
            // Check if it already exists in the collection to prevent duplicates
            $exists = $allBukti->contains('file_path', $this->bukti_pembayaran);
            
            if (!$exists) {
                $legacyBukti = new BuktiPembayaran([
                    'penjualan_id' => $this->id,
                    'file_path' => $this->bukti_pembayaran,
                    'keterangan' => $this->catatan_pembayaran ?? 'Bukti dari Pembayaran Utama',
                ]);
                $legacyBukti->id = 'legacy'; // flag for view
                $legacyBukti->created_at = $this->payment_confirmed_at ?? $this->created_at;
                
                $allBukti->prepend($legacyBukti);
            }
        }

        if ($this->order && !empty($this->order->bukti_pembayaran)) {
            $exists = $allBukti->contains('file_path', $this->order->bukti_pembayaran);
            if (!$exists) {
                $orderBukti = new BuktiPembayaran([
                    'penjualan_id' => $this->id,
                    'file_path' => $this->order->bukti_pembayaran,
                    'keterangan' => 'Bukti Pembayaran dari Pelanggan (Online)',
                ]);
                $orderBukti->id = 'order_bukti'; // flag for view
                $orderBukti->created_at = $this->order->updated_at;
                $allBukti->prepend($orderBukti);
            }
        }
        
        return $allBukti;
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
                    // CRITICAL: Filter by user_id untuk multi-tenant isolation
                    // Cari nomor terakhir untuk tanggal hari ini agar urutan direset per hari
                    $lastNumber = static::where('user_id', $penjualan->user_id)
                        ->where('nomor_penjualan', 'like', 'SJ-' . $date . '-%')
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
                    
                    // CRITICAL: Cek apakah nomor sudah ada untuk user ini (multi-tenant)
                    $exists = static::where('user_id', $penjualan->user_id)
                        ->where('nomor_penjualan', $nomorPenjualan)
                        ->exists();
                    
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
        
        // NOTE: Journal creation is handled explicitly in controllers
        // Event listeners are disabled to prevent duplicate journal entries
        // See: PenjualanController::confirmPayment, KasirController, OrderToSalesService, PelangganController
    }

    /**
     * Generate unique nomor penjualan to prevent duplicates (per user for multi-tenant)
     */
    public static function generateUniqueNomorPenjualan($date, $userId = null)
    {
        // Use authenticated user if not provided
        $userId = $userId ?? auth()->id();
        
        // Use a while loop to ensure we get a unique number
        $maxAttempts = 100;
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            // CRITICAL: Get the highest number for today FOR THIS USER
            $lastNomor = static::where('user_id', $userId)
                ->where('nomor_penjualan', 'like', 'SJ-' . $date . '-%')
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
            
            // CRITICAL: Check if this number already exists FOR THIS USER (double-check)
            if (!static::where('user_id', $userId)->where('nomor_penjualan', $newNomor)->exists()) {
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

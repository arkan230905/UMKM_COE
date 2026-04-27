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
        'nomor_penjualan',
        'produk_id',
        'tanggal',
        'payment_method',
        'harga_satuan',
        'jumlah',
        'diskon_nominal',
        'total',
        'biaya_ongkir',
        'biaya_ppn',
        'grand_total',
        'bukti_pembayaran',
        'catatan_pembayaran',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

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

    // Relasi ke bukti pembayaran
    public function buktiPembayaran()
    {
        return $this->hasMany(BuktiPembayaran::class);
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
                
                // Ambil nomor penjualan terakhir hari ini untuk menghindari duplikat
                $lastNomor = static::whereDate('tanggal', $tanggal)
                    ->where('nomor_penjualan', 'like', 'SJ-' . $date . '-%')
                    ->orderBy('nomor_penjualan', 'desc')
                    ->value('nomor_penjualan');
                
                if ($lastNomor) {
                    // Extract nomor urut dari nomor penjualan terakhir
                    $lastNumber = intval(substr($lastNomor, -3));
                    $count = $lastNumber + 1;
                } else {
                    $count = 1;
                }
                
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\UserScope;

class Pembelian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembelians';

    protected $fillable = [
        'user_id',  // CRITICAL: multi-tenant isolation
        'nomor_pembelian',
        'nomor_faktur',
        'bukti_faktur',  // File path untuk bukti faktur
        'vendor_id',
        'kode_pembelian',
        'tanggal',
        'subtotal',
        'biaya_kirim',
        'ppn_persen',
        'ppn_nominal',
        'total_harga',
        'dp',  // Down Payment untuk kredit
        'dp_payment_method_id',  // Akun pembayaran untuk DP
        'tanggal_jatuh_tempo',  // Tanggal jatuh tempo untuk kredit
        'terbayar',
        'sisa_pembayaran',
        'status',
        'payment_method',
        'keterangan',
        'bank_id'
    ];
    
    protected $dates = ['tanggal', 'tanggal_jatuh_tempo', 'deleted_at'];
    
    protected $appends = ['sisa_utang', 'status_pembayaran', 'total_refund'];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'subtotal' => 'float',
        'biaya_kirim' => 'float',
        'ppn_persen' => 'float',
        'ppn_nominal' => 'float',
        'total_harga' => 'float',
        'dp' => 'float',
        'terbayar' => 'float',
        'sisa_pembayaran' => 'float'
    ];
    
    /**
     * Boot the model.
     */
    protected static function booted()
    {
        // CRITICAL: Apply global scope untuk multi-tenant isolation
        static::addGlobalScope(new \App\Scopes\UserScope);
        
        static::created(function ($pembelian) {
            // Journal akan dibuat manual di controller setelah semua detail tersimpan
            // Tidak perlu auto-create di sini untuk menghindari jurnal dobel
        });
        
        static::updated(function ($pembelian) {
            // Journal akan diupdate manual di controller jika diperlukan
            // Tidak perlu auto-update di sini
        });
        
        static::deleting(function ($pembelian) {
            // Delete journal entries FIRST (before details are deleted)
            $journalService = new \App\Services\JournalService();
            $journalService->deleteByRef('purchase', $pembelian->id);
            

            // Delete related AP settlements
            $pembelian->apSettlements()->delete();
            
            // Delete related pelunasan
            $pembelian->pelunasan()->delete();
            
            // Delete journal entries menggunakan PembelianJournalService
            try {
                $journalService = new \App\Services\PembelianJournalService();
                $journalService->deleteExistingJournal($pembelian->id);
            } catch (\Exception $e) {
                \Log::error('Failed to delete journal for pembelian', [
                    'pembelian_id' => $pembelian->id,
                    'error' => $e->getMessage()
                ]);
            }
            
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
            
            // Delete related pembelian details LAST
            $pembelian->pembelianDetails()->delete();
            
            // Delete related AP settlements
            $pembelian->apSettlements()->delete();
            
            // Delete related pelunasan
            $pembelian->pelunasan()->delete();
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
     * Get the purchase returns for the pembelian.
     */
    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class, 'pembelian_id');
    }
    
    /**
     * Get total refund amount from approved returns (jenis_retur = refund)
     */
    public function getTotalRefundAttribute()
    {
        // Check if purchaseReturns is already loaded to avoid N+1
        if ($this->relationLoaded('purchaseReturns')) {
            return $this->purchaseReturns
                ->where('jenis_retur', 'refund')
                ->whereIn('status', ['disetujui', 'dikirim', 'selesai'])
                ->sum('total_return_amount');
        }
        
        // Fallback to query if relation not loaded
        return $this->purchaseReturns()
            ->where('jenis_retur', 'refund')
            ->whereIn('status', ['disetujui', 'dikirim', 'selesai'])
            ->sum('total_return_amount');
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
        // Get DP value (down payment)
        $dp = $this->dp ?? 0;
        
        // Get total from pelunasan
        if ($this->relationLoaded('pelunasan')) {
            $totalPelunasan = $this->pelunasan->sum('jumlah');
        } else {
            $totalPelunasan = $this->pelunasan()->sum('jumlah');
        }
        
        // Total dibayar = DP + Total Pelunasan
        return $dp + $totalPelunasan;
    }
    
    /**
     * Get the sisa utang attribute.
     * Formula: Total Harga - DP - Total Dibayar - Total Refund
     */
    public function getSisaUtangAttribute()
    {
        // Calculate from total_harga - total payments (which includes DP) - total refunds
        $totalHarga = $this->total_harga ?? 0;
        $totalDibayar = $this->total_dibayar; // Already includes DP from getTotalDibayarAttribute
        $totalRefund = $this->total_refund; // Refund reduces the debt
        
        // Sisa utang = Total - Dibayar (includes DP) - Refund
        $sisaUtang = $totalHarga - $totalDibayar - $totalRefund;
        
        return max(0, $sisaUtang); // Cannot be negative
    }
    
    /**
     * Sync pembelian payment status based on payment method and pelunasan records
     * Also considers refunds from returns
     */
    public function syncPaymentStatus()
    {
        // For cash/transfer payments, mark as lunas immediately
        if ($this->payment_method === 'cash' || $this->payment_method === 'transfer') {
            $this->terbayar = $this->total_harga;
            $this->sisa_pembayaran = 0;
            $this->status = 'lunas';
            return;
        }
        
        // For credit payments, calculate from pelunasan records and refunds
        $totalPelunasan = $this->pelunasan()->sum('jumlah');
        $totalRefund = $this->total_refund; // Refund reduces the debt
        
        $this->terbayar = $totalPelunasan;
        $this->sisa_pembayaran = ($this->total_harga ?? 0) - $totalPelunasan - $totalRefund;
        
        // If total paid + refund >= total price, mark as lunas
        if (($totalPelunasan + $totalRefund) >= $this->total_harga) {
            $this->status = 'lunas';
        } else {
            $this->status = 'belum_lunas';
        }
    }
    
    /**
     * Get the status pembayaran attribute.
     * Considers both payments and refunds
     */
    public function getStatusPembayaranAttribute()
    {
        // For cash/transfer, always lunas
        if ($this->payment_method === 'cash' || $this->payment_method === 'transfer') {
            return 'Lunas';
        }
        
        // For credit, check from pelunasan and refunds
        $totalDibayar = $this->total_dibayar;
        $totalRefund = $this->total_refund;
        $totalHarga = $this->total_harga ?? 0;
        
        $totalPaidAndRefund = $totalDibayar + $totalRefund;
        
        if ($totalPaidAndRefund == 0) {
            return 'Belum Bayar';
        } elseif ($totalPaidAndRefund < $totalHarga) {
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
            // CRITICAL: Auto-set user_id untuk multi-tenant isolation
            if (empty($pembelian->user_id) && auth()->check()) {
                $pembelian->user_id = auth()->id();
            }
            
            if (empty($pembelian->nomor_pembelian)) {
                $tanggal = $pembelian->tanggal ?? now();
                $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
                
                // Generate unique nomor pembelian dengan retry mechanism
                $maxRetries = 10;
                $attempt = 0;
                
                do {
                    // Cari nomor terakhir GLOBAL (semua tanggal) untuk urutan berurutan
                    // Ambil semua nomor dan cari yang tertinggi, abaikan duplikasi
                    $allNumbers = static::where('nomor_pembelian', 'like', 'PB-%')
                        ->where('nomor_pembelian', 'REGEXP', '^PB-[0-9]{8}-[0-9]{4}$')
                        ->pluck('nomor_pembelian')
                        ->toArray();
                    
                    $highestSequence = 0;
                    foreach ($allNumbers as $number) {
                        $parts = explode('-', $number);
                        if (count($parts) === 3) {
                            $sequence = intval(end($parts));
                            if ($sequence > $highestSequence) {
                                $highestSequence = $sequence;
                            }
                        }
                    }
                    
                    // Selalu gunakan urutan tertinggi + 1 untuk menghindari konflik
                    $nextSequence = $highestSequence + 1;
                    
                    // Format: PB-YYYYMMDD-0001 (urutan global berurutan)
                    $nomorPembelian = 'PB-' . $date . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
                    
                    // Cek apakah nomor sudah ada (untuk memastikan unique)
                    $exists = static::where('nomor_pembelian', $nomorPembelian)->exists();
                    
                    if (!$exists) {
                        $pembelian->nomor_pembelian = $nomorPembelian;
                        break;
                    }
                    
                    $attempt++;
                    
                    // Jika sudah mencoba berkali-kali, tambahkan timestamp untuk memastikan unique
                    if ($attempt >= $maxRetries) {
                        $timestamp = now()->format('His'); // HHMMSS
                        $pembelian->nomor_pembelian = 'PB-' . $date . '-' . $timestamp;
                        break;
                    }
                    
                } while ($attempt < $maxRetries);
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

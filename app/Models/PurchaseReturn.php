<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'return_number',
        'pembelian_id',
        'return_date',
        'reason',
        'jenis_retur',
        'notes',
        'total_return_amount',
        'status',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_return_amount' => 'decimal:2',
    ];

    // Status constants - Updated flow
    const STATUS_PENDING = 'pending';
    const STATUS_DISETUJUI = 'disetujui';  // Changed from acc_vendor
    const STATUS_DIKIRIM = 'dikirim';
    const STATUS_SELESAI = 'selesai';

    // Jenis retur constants
    const JENIS_TUKAR_BARANG = 'tukar_barang';
    const JENIS_REFUND = 'refund';

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    // Accessor for calculating total retur from items
    public function getTotalReturAttribute()
    {
        return $this->items->sum('subtotal');
    }

    // Accessor for calculated total (alias for total_retur)
    public function getCalculatedTotalAttribute()
    {
        return $this->total_retur;
    }

    // Accessor for PPN amount (11%)
    public function getPpnAmountAttribute()
    {
        return $this->total_retur * 0.11;
    }

    // Accessor for total with PPN
    public function getTotalWithPpnAttribute()
    {
        return $this->total_retur + $this->ppn_amount;
    }

    // Accessor for formatted total with PPN in Rupiah
    public function getTotalWithPpnFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total_with_ppn, 0, ',', '.');
    }

    // Accessor for formatted jenis retur display
    public function getJenisReturDisplayAttribute()
    {
        switch($this->jenis_retur) {
            case self::JENIS_REFUND:
                return 'Refund';
            case self::JENIS_TUKAR_BARANG:
                return 'Tukar Barang';
            default:
                return ucfirst(str_replace('_', ' ', $this->jenis_retur));
        }
    }

    // Accessor for status badge
    public function getStatusBadgeAttribute()
    {
        if ($this->jenis_retur === self::JENIS_TUKAR_BARANG) {
            switch($this->status) {
                case self::STATUS_PENDING:
                    return ['class' => 'bg-warning', 'text' => 'Pending'];
                case self::STATUS_DISETUJUI:
                    return ['class' => 'bg-primary', 'text' => 'Menunggu Kirim'];
                case self::STATUS_DIKIRIM:
                    return ['class' => 'bg-secondary', 'text' => 'Dikirim'];
                case self::STATUS_SELESAI:
                    return ['class' => 'bg-success', 'text' => 'Selesai'];
                default:
                    return ['class' => 'bg-secondary', 'text' => ucfirst($this->status)];
            }
        } else { // REFUND
            switch($this->status) {
                case self::STATUS_PENDING:
                    return ['class' => 'bg-warning', 'text' => 'Pending'];
                case self::STATUS_DISETUJUI:
                    return ['class' => 'bg-primary', 'text' => 'Menunggu Kirim'];
                case self::STATUS_DIKIRIM:
                    return ['class' => 'bg-secondary', 'text' => 'Dikirim'];
                case self::STATUS_SELESAI:
                    return ['class' => 'bg-success', 'text' => 'Selesai'];
                default:
                    return ['class' => 'bg-secondary', 'text' => ucfirst($this->status)];
            }
        }
    }

    // Get next status based on current status and jenis retur
    public function getNextStatusAttribute()
    {
        if ($this->jenis_retur === self::JENIS_TUKAR_BARANG) {
            switch($this->status) {
                case self::STATUS_PENDING:
                    return self::STATUS_DISETUJUI;
                case self::STATUS_DISETUJUI:
                    return self::STATUS_DIKIRIM;
                case self::STATUS_DIKIRIM:
                    return self::STATUS_SELESAI;
                default:
                    return null;
            }
        } else { // refund
            switch($this->status) {
                case self::STATUS_PENDING:
                    return self::STATUS_DISETUJUI;
                case self::STATUS_DISETUJUI:
                    return self::STATUS_DIKIRIM;
                case self::STATUS_DIKIRIM:
                    return self::STATUS_SELESAI;
                default:
                    return null;
            }
        }
    }

    // Get action button text and class
    public function getActionButtonAttribute()
    {
        if (!$this->next_status) {
            return null;
        }

        if ($this->jenis_retur === self::JENIS_TUKAR_BARANG) {
            switch($this->status) {
                case self::STATUS_PENDING:
                    return ['text' => 'ACC Vendor', 'class' => 'btn-primary'];
                case self::STATUS_DISETUJUI:
                    return ['text' => 'Kirim Barang', 'class' => 'btn-warning'];
                case self::STATUS_DIKIRIM:
                    return ['text' => 'Terima Barang', 'class' => 'btn-success'];
                default:
                    return null;
            }
        } else { // refund
            switch($this->status) {
                case self::STATUS_PENDING:
                    return ['text' => 'ACC Vendor', 'class' => 'btn-primary'];
                case self::STATUS_DISETUJUI:
                    return ['text' => 'Kirim Barang', 'class' => 'btn-warning'];
                case self::STATUS_DIKIRIM:
                    return ['text' => 'Terima Uang', 'class' => 'btn-success'];
                default:
                    return null;
            }
        }
    }

    // Check if status is final
    public function getIsCompletedAttribute()
    {
        return $this->status === self::STATUS_SELESAI;
    }

    // Accessor for formatted total retur in Rupiah
    public function getTotalReturFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total_retur, 0, ',', '.');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $date = now()->format('Ymd');
                $count = static::whereDate('created_at', today())->count() + 1;
                $return->return_number = 'PRTN-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            
            // Set default status
            if (empty($return->status)) {
                $return->status = self::STATUS_PENDING;
            }
        });
    }

    public function calculateTotals(): void
    {
        $this->total_return_amount = $this->items()->sum('subtotal');
        $this->save();
    }
}
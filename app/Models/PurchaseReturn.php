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

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_MENUNGGU_ACC = 'menunggu_acc';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DIKIRIM = 'dikirim';
    const STATUS_DIPROSES = 'diproses';
    const STATUS_DITERIMA = 'diterima';
    const STATUS_SELESAI = 'selesai';
    const STATUS_REFUND_SELESAI = 'refund_selesai';

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

    // Accessor for formatted jenis retur display
    public function getJenisReturDisplayAttribute()
    {
        return match($this->jenis_retur) {
            self::JENIS_REFUND => 'Refund',
            self::JENIS_TUKAR_BARANG => 'Tukar Barang',
            default => ucfirst(str_replace('_', ' ', $this->jenis_retur))
        };
    }

    // Accessor for status badge
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => ['class' => 'bg-warning', 'text' => 'Pending'],
            self::STATUS_MENUNGGU_ACC => ['class' => 'bg-warning', 'text' => 'Menunggu ACC'],
            self::STATUS_DISETUJUI => ['class' => 'bg-primary', 'text' => 'Disetujui'],
            self::STATUS_DIKIRIM => ['class' => 'bg-warning', 'text' => 'Dikirim'],
            self::STATUS_DIPROSES => ['class' => 'bg-secondary', 'text' => 'Diproses Vendor'],
            self::STATUS_DITERIMA => ['class' => 'bg-info', 'text' => 'Diterima Vendor'],
            self::STATUS_SELESAI => ['class' => 'bg-success', 'text' => 'Selesai'],
            self::STATUS_REFUND_SELESAI => ['class' => 'bg-success', 'text' => 'Refund Selesai'],
            default => ['class' => 'bg-secondary', 'text' => ucfirst($this->status)]
        };
    }

    // Get next status based on current status and jenis retur
    public function getNextStatusAttribute()
    {
        if ($this->jenis_retur === self::JENIS_TUKAR_BARANG) {
            return match($this->status) {
                self::STATUS_PENDING => self::STATUS_DISETUJUI,
                self::STATUS_MENUNGGU_ACC => self::STATUS_DISETUJUI,
                self::STATUS_DISETUJUI => self::STATUS_DIKIRIM,
                self::STATUS_DIKIRIM => self::STATUS_DIPROSES,
                self::STATUS_DIPROSES => self::STATUS_SELESAI,
                default => null
            };
        } else { // refund
            return match($this->status) {
                self::STATUS_PENDING => self::STATUS_DISETUJUI,
                self::STATUS_MENUNGGU_ACC => self::STATUS_DISETUJUI,
                self::STATUS_DISETUJUI => self::STATUS_DIKIRIM,
                self::STATUS_DIKIRIM => self::STATUS_DITERIMA,
                self::STATUS_DITERIMA => self::STATUS_REFUND_SELESAI,
                default => null
            };
        }
    }

    // Get action button text and class
    public function getActionButtonAttribute()
    {
        if (!$this->next_status) {
            return null;
        }

        if ($this->jenis_retur === self::JENIS_TUKAR_BARANG) {
            return match($this->status) {
                self::STATUS_PENDING => ['text' => 'ACC Vendor', 'class' => 'btn-primary'],
                self::STATUS_MENUNGGU_ACC => ['text' => 'ACC Vendor', 'class' => 'btn-primary'],
                self::STATUS_DISETUJUI => ['text' => 'Kirim Barang', 'class' => 'btn-warning'],
                self::STATUS_DIKIRIM => ['text' => 'Diproses Vendor', 'class' => 'btn-secondary'],
                self::STATUS_DIPROSES => ['text' => 'Barang Diterima', 'class' => 'btn-success'],
                default => null
            };
        } else { // refund
            return match($this->status) {
                self::STATUS_PENDING => ['text' => 'ACC Vendor', 'class' => 'btn-primary'],
                self::STATUS_MENUNGGU_ACC => ['text' => 'ACC Vendor', 'class' => 'btn-primary'],
                self::STATUS_DISETUJUI => ['text' => 'Kirim Barang', 'class' => 'btn-warning'],
                self::STATUS_DIKIRIM => ['text' => 'Vendor Terima', 'class' => 'btn-info'],
                self::STATUS_DITERIMA => ['text' => 'Terima Uang', 'class' => 'btn-success'],
                default => null
            };
        }
    }

    // Check if status is final
    public function getIsCompletedAttribute()
    {
        return in_array($this->status, [self::STATUS_SELESAI, self::STATUS_REFUND_SELESAI]);
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

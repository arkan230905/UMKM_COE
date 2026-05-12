<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'nomor_order',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'snap_token',
        'nama_penerima',
        'alamat_pengiriman',
        'telepon_penerima',
        'catatan',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateNomorOrder(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())->latest()->first();
        $number = $lastOrder ? (int)substr($lastOrder->nomor_order, -4) + 1 : 1;
        return 'ORD-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">Menunggu Pembayaran</span>',
            'paid' => '<span class="badge bg-success">Dibayar</span>',
            'processing' => '<span class="badge bg-info">Diproses</span>',
            'shipped' => '<span class="badge bg-primary">Dikirim</span>',
            'completed' => '<span class="badge bg-success">Selesai</span>',
            'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'qris' => 'QRIS',
            'va_bca' => 'BCA Virtual Account',
            'va_bni' => 'BNI Virtual Account',
            'va_bri' => 'BRI Virtual Account',
            'va_mandiri' => 'Mandiri Virtual Account',
            'cash' => 'Cash',
            'transfer' => 'Transfer Bank',
            default => '-',
        };
    }
}

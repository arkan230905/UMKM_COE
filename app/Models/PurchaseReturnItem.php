<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    use \App\Traits\HasUserScope;
    protected $fillable = [
        'user_id',
        'purchase_return_id',
        'pembelian_detail_id',
        'bahan_baku_id',
        'bahan_pendukung_id',
        'unit',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && !$model->user_id) {
                $model->user_id = auth()->id();
            }
        });
    }

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function pembelianDetail(): BelongsTo
    {
        return $this->belongsTo(PembelianDetail::class);
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function bahanPendukung(): BelongsTo
    {
        return $this->belongsTo(BahanPendukung::class);
    }
}

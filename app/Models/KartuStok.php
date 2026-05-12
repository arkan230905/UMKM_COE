<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuStok extends Model
{
    protected $table = 'kartu_stok';
    
    protected $fillable = [
        'tanggal',
        'item_id',
        'item_type',
        'qty_masuk',
        'qty_keluar',
        'keterangan',
        'ref_type',
        'ref_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty_masuk' => 'decimal:4',
        'qty_keluar' => 'decimal:4'
    ];

    // Item type constants
    const ITEM_TYPE_BAHAN_BAKU = 'bahan_baku';
    const ITEM_TYPE_BAHAN_PENDUKUNG = 'bahan_pendukung';

    // Reference type constants
    const REF_TYPE_PEMBELIAN = 'pembelian';
    const REF_TYPE_RETUR = 'retur';
    const REF_TYPE_PRODUKSI = 'produksi';
    const REF_TYPE_ADJUSTMENT = 'adjustment';
    const REF_TYPE_INITIAL = 'initial_stock';

    /**
     * Get the bahan baku if item_type is bahan_baku
     */
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'item_id')->where('item_type', self::ITEM_TYPE_BAHAN_BAKU);
    }

    /**
     * Get the bahan pendukung if item_type is bahan_pendukung
     */
    public function bahanPendukung(): BelongsTo
    {
        return $this->belongsTo(BahanPendukung::class, 'item_id')->where('item_type', self::ITEM_TYPE_BAHAN_PENDUKUNG);
    }

    /**
     * Get the item name based on item_type
     */
    public function getItemNameAttribute()
    {
        if ($this->item_type === self::ITEM_TYPE_BAHAN_BAKU) {
            return $this->bahanBaku?->nama_bahan ?? 'Bahan Baku (ID: ' . $this->item_id . ')';
        } elseif ($this->item_type === self::ITEM_TYPE_BAHAN_PENDUKUNG) {
            return $this->bahanPendukung?->nama_bahan ?? 'Bahan Pendukung (ID: ' . $this->item_id . ')';
        }
        return 'Unknown Item';
    }

    /**
     * Get running balance for this item up to this date
     */
    public function getRunningBalanceAttribute()
    {
        return static::where('item_id', $this->item_id)
            ->where('item_type', $this->item_type)
            ->where('tanggal', '<=', $this->tanggal)
            ->where('id', '<=', $this->id)
            ->selectRaw('SUM(COALESCE(qty_masuk, 0) - COALESCE(qty_keluar, 0)) as balance')
            ->value('balance') ?? 0;
    }

    /**
     * Scope for specific item
     */
    public function scopeForItem($query, $itemId, $itemType)
    {
        return $query->where('item_id', $itemId)->where('item_type', $itemType);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $query->where('tanggal', '>=', $startDate);
        if ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Create stock entry - ensures only one of qty_masuk or qty_keluar is filled
     */
    public static function createEntry(array $data)
    {
        // Validation: only one of qty_masuk or qty_keluar should be filled
        if (!empty($data['qty_masuk']) && !empty($data['qty_keluar'])) {
            throw new \InvalidArgumentException('Only one of qty_masuk or qty_keluar can be filled');
        }

        if (empty($data['qty_masuk']) && empty($data['qty_keluar'])) {
            throw new \InvalidArgumentException('Either qty_masuk or qty_keluar must be filled');
        }

        return static::create($data);
    }

    /**
     * Get stock balance for an item
     */
    public static function getStockBalance($itemId, $itemType, $asOfDate = null)
    {
        $query = static::where('item_id', $itemId)
            ->where('item_type', $itemType);
            
        if ($asOfDate) {
            $query->where('tanggal', '<=', $asOfDate);
        }

        return $query->selectRaw('SUM(COALESCE(qty_masuk, 0) - COALESCE(qty_keluar, 0)) as balance')
            ->value('balance') ?? 0;
    }
}
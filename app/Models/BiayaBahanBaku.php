<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaBahanBaku extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'biaya_bahan_baku';

    protected $fillable = [
        'user_id',
        'produk_id',
        'bahan_baku_id',
        'coa_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'subtotal',
        'keterangan'
    ];

    protected $casts = [
        'jumlah' => 'decimal:4',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Relationship to BahanBaku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    /**
     * Relationship to COA (Chart of Accounts)
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }

    /**
     * Relationship to HargaPokokProduksiBiayaBahanBaku
     */
    public function hargaPokokProduksiBiayaBahanBaku()
    {
        return $this->hasMany(HargaPokokProduksiBiayaBahanBaku::class, 'bom_job_bbb_id');
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Scope to filter by product
     */
    public function scopeByProduk($query, $produkId)
    {
        return $query->where('produk_id', $produkId);
    }

    /**
     * Get harga realtime dari master data bahan baku
     */
    public function getHargaRealtimeAttribute()
    {
        return $this->bahanBaku->harga_beli ?? $this->harga_satuan;
    }

    /**
     * Get subtotal realtime
     */
    public function getSubtotalRealtimeAttribute()
    {
        return $this->jumlah * $this->harga_realtime;
    }

    /**
     * Check apakah harga sudah berubah dibanding master data
     */
    public function isHargaOutdated()
    {
        $hargaMaster = $this->bahanBaku->harga_beli ?? 0;
        return abs($this->harga_satuan - $hargaMaster) > 0.01; // Toleransi 0.01
    }

    /**
     * Sync harga dari master data bahan baku
     */
    public function syncHargaFromMaster()
    {
        if ($this->bahanBaku) {
            $this->harga_satuan = $this->bahanBaku->harga_beli;
            $this->subtotal = $this->jumlah * $this->harga_satuan;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Boot method to auto-fill user_id and calculate subtotal
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Auto-fill user_id if not set
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
            
            // Auto-calculate subtotal
            $model->subtotal = $model->jumlah * $model->harga_satuan;
        });
        
        static::updating(function ($model) {
            // Auto-calculate subtotal when updating
            if ($model->isDirty(['jumlah', 'harga_satuan'])) {
                $model->subtotal = $model->jumlah * $model->harga_satuan;
            }
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBBB extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;
    protected $table = 'bom_job_bbb';
    protected $fillable = ['user_id', 'produk_id', 'bahan_baku_id', 'jumlah', 'satuan', 'harga_satuan', 'subtotal', 'keterangan'];
    protected $casts = ['jumlah' => 'decimal:4', 'harga_satuan' => 'decimal:2', 'subtotal' => 'decimal:2'];

    protected static function booted()
    {
        // DISABLED: Subtotal sudah dihitung dengan benar di BiayaBahanConversionService
        // Event saving ini malah menghitung ulang dengan salah karena harga_satuan yang disimpan
        // sudah harga konversi, bukan harga base
        // static::saving(function ($m) { $m->subtotal = $m->jumlah * $m->harga_satuan; });
        
        // Remove bomJobCosting references since we no longer have bom_job_costing_id
        // static::saved(function ($m) { $m->bomJobCosting?->recalculate(); });
        // static::deleted(function ($m) { $m->bomJobCosting?->recalculate(); });
    }
    
    public function bahanBaku() { 
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); 
    }
    
    public function produk() { 
        return $this->belongsTo(Produk::class, 'produk_id'); 
    }
}

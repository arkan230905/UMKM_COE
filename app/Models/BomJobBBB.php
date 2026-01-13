<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBBB extends Model
{
    use HasFactory;
    protected $table = 'bom_job_bbb';
    protected $fillable = ['bom_job_costing_id', 'bahan_baku_id', 'jumlah', 'satuan', 'harga_satuan', 'subtotal', 'keterangan'];
    protected $casts = ['jumlah' => 'decimal:4', 'harga_satuan' => 'decimal:2', 'subtotal' => 'decimal:2'];

    protected static function booted()
    {
        static::saving(function ($m) { $m->subtotal = $m->jumlah * $m->harga_satuan; });
        static::saved(function ($m) { $m->bomJobCosting?->recalculate(); });
        static::deleted(function ($m) { $m->bomJobCosting?->recalculate(); });
    }

    public function bomJobCosting() { return $this->belongsTo(BomJobCosting::class, 'bom_job_costing_id'); }
    public function bahanBaku() { return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); }
}

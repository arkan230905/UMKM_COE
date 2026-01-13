<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBTKL extends Model
{
    use HasFactory;
    protected $table = 'bom_job_btkl';
    protected $fillable = ['bom_job_costing_id', 'btkl_id', 'nama_proses', 'durasi_jam', 'tarif_per_jam', 'subtotal', 'keterangan'];
    protected $casts = ['durasi_jam' => 'decimal:4', 'tarif_per_jam' => 'decimal:2', 'subtotal' => 'decimal:2'];

    protected static function booted()
    {
        static::saving(function ($m) { $m->subtotal = $m->durasi_jam * $m->tarif_per_jam; });
        static::saved(function ($m) { $m->bomJobCosting?->recalculate(); });
        static::deleted(function ($m) { $m->bomJobCosting?->recalculate(); });
    }

    public function bomJobCosting() { return $this->belongsTo(BomJobCosting::class, 'bom_job_costing_id'); }
    public function btkl() { return $this->belongsTo(Btkl::class, 'btkl_id'); }
    
    // Legacy relationship for backward compatibility
    public function prosesProduksi() { return $this->belongsTo(ProsesProduksi::class, 'proses_produksi_id'); }
}

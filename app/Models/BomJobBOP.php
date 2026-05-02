<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBOP extends Model
{
    use HasFactory;
    protected $table = 'bom_job_bop';
    protected $fillable = ['bom_job_costing_id', 'bop_id', 'nama_bop', 'jumlah', 'tarif', 'subtotal', 'keterangan'];
    protected $casts = ['jumlah' => 'decimal:4', 'tarif' => 'decimal:2', 'subtotal' => 'decimal:2'];

    protected static function booted()
    {
        
        // ===== MULTI-TENANT ISOLATION =====
        // Auto-assign user_id saat creating
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        // Global scope untuk data isolation
        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
        // ===== END MULTI-TENANT ISOLATION =====
static::saving(function ($m) { 
            // Ensure subtotal is calculated correctly
            $jumlah = floatval($m->jumlah ?? 0);
            $tarif = floatval($m->tarif ?? 0);
            $m->subtotal = $jumlah * $tarif;
            
            // Log for debugging
            \Log::info("BomJobBOP saving - Jumlah: {$jumlah}, Tarif: {$tarif}, Subtotal: {$m->subtotal}");
        });
        static::saved(function ($m) { $m->bomJobCosting?->recalculate(); });
        static::deleted(function ($m) { $m->bomJobCosting?->recalculate(); });
    }

    public function bomJobCosting() { return $this->belongsTo(BomJobCosting::class, 'bom_job_costing_id'); }
    public function bop() { return $this->belongsTo(Bop::class, 'bop_id'); }
    public function bopProses() { return $this->belongsTo(\App\Models\BopProses::class, 'bop_id'); }
}

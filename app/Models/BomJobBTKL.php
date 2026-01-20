<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBTKL extends Model
{
    use HasFactory;
    protected $table = 'bom_job_btkl';
    protected $fillable = ['bom_job_costing_id', 'btkl_id', 'nama_proses', 'durasi_jam', 'tarif_per_jam', 'kapasitas_per_jam', 'subtotal', 'keterangan'];
    protected $casts = ['durasi_jam' => 'decimal:4', 'tarif_per_jam' => 'decimal:2', 'kapasitas_per_jam' => 'integer', 'subtotal' => 'decimal:2'];

    protected static function booted()
    {
        static::saving(function ($m) { 
            // Perhitungan BTKL yang lebih akurat:
            // Jika ada kapasitas_per_jam, hitung beban per produk
            if ($m->kapasitas_per_jam && $m->kapasitas_per_jam > 0) {
                // Beban per produk = (durasi_jam × tarif_per_jam) ÷ kapasitas_per_jam
                $totalBiayaPerJam = $m->durasi_jam * $m->tarif_per_jam;
                $m->subtotal = $totalBiayaPerJam / $m->kapasitas_per_jam;
            } else {
                // Fallback ke perhitungan lama (jika belum ada kapasitas)
                $m->subtotal = $m->durasi_jam * $m->tarif_per_jam;
            }
        });
        static::saved(function ($m) { $m->bomJobCosting?->recalculate(); });
        static::deleted(function ($m) { $m->bomJobCosting?->recalculate(); });
    }

    public function bomJobCosting() { return $this->belongsTo(BomJobCosting::class, 'bom_job_costing_id'); }
    public function btkl() { return $this->belongsTo(Btkl::class, 'btkl_id'); }
    
    // Legacy relationship for backward compatibility
    public function prosesProduksi() { return $this->belongsTo(ProsesProduksi::class, 'proses_produksi_id'); }
    
    /**
     * Get kapasitas per jam (dari input manual atau master BTKL)
     */
    public function getKapasitasPerJamAttribute()
    {
        if ($this->kapasitas_per_jam && $this->kapasitas_per_jam > 0) {
            return $this->kapasitas_per_jam;
        }
        
        // Ambil dari master BTKL jika ada
        if ($this->btkl && $this->btkl->kapasitas_per_jam > 0) {
            return $this->btkl->kapasitas_per_jam;
        }
        
        return 1; // Default kapasitas
    }
    
    /**
     * Get beban BTKL per produk
     */
    public function getBebanPerProdukAttribute()
    {
        $kapasitas = $this->getKapasitasPerJamAttribute();
        
        if ($kapasitas && $kapasitas > 0) {
            $totalBiayaPerJam = $this->durasi_jam * $this->tarif_per_jam;
            return $totalBiayaPerJam / $kapasitas;
        }
        
        return $this->subtotal; // Fallback
    }
    
    /**
     * Format beban per produk
     */
    public function getBebanPerProdukFormattedAttribute()
    {
        return 'Rp ' . number_format($this->getBebanPerProdukAttribute(), 0, ',', '.');
    }
}

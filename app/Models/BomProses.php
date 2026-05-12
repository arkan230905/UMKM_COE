<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomProses extends Model
{
    use HasFactory;

    protected $table = 'bom_proses';

    protected $fillable = [
        'bom_id',
        'proses_produksi_id',
        'urutan',
        'durasi',
        'satuan_durasi',
        'biaya_btkl',
        'biaya_bop',
        'catatan'
    ];

    protected $casts = [
        'durasi' => 'decimal:4',
        'biaya_btkl' => 'decimal:2',
        'biaya_bop' => 'decimal:2'
    ];

    /**
     * Boot method untuk auto-calculate biaya
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateBiaya();
        });
    }

    /**
     * Relasi ke BOM
     */
    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    /**
     * Relasi ke Proses Produksi
     */
    public function prosesProduksi()
    {
        return $this->belongsTo(ProsesProduksi::class, 'proses_produksi_id');
    }

    /**
     * Relasi ke detail BOP
     */
    public function bomProsesBops()
    {
        return $this->hasMany(BomProsesBop::class, 'bom_proses_id');
    }

    /**
     * Hitung biaya BTKL dan BOP
     * BTKL = durasi × tarif_btkl
     * BOP = sum of (kuantitas × tarif) dari semua komponen BOP
     */
    public function calculateBiaya()
    {
        // Hitung BTKL
        $proses = $this->prosesProduksi;
        if ($proses) {
            $this->biaya_btkl = $this->durasi * $proses->tarif_btkl;
        }

        // Hitung BOP dari detail
        $this->biaya_bop = $this->bomProsesBops->sum('total_biaya');
        
        return $this;
    }

    /**
     * Hitung ulang biaya BOP dari detail
     */
    public function recalculateBop()
    {
        $this->biaya_bop = $this->bomProsesBops()->sum('total_biaya');
        $this->saveQuietly(); // Save tanpa trigger event
        return $this;
    }

    /**
     * Get total biaya proses (BTKL + BOP)
     */
    public function getTotalBiayaProsesAttribute(): float
    {
        return $this->biaya_btkl + $this->biaya_bop;
    }

    /**
     * Load default BOP dari proses produksi
     */
    public function loadDefaultBop()
    {
        $proses = $this->prosesProduksi;
        if (!$proses) return;

        foreach ($proses->prosesBops as $prosesBop) {
            $this->bomProsesBops()->create([
                'komponen_bop_id' => $prosesBop->komponen_bop_id,
                'kuantitas' => $prosesBop->kuantitas_default * $this->durasi,
                'tarif' => $prosesBop->komponenBop->tarif_per_satuan,
                'total_biaya' => $prosesBop->kuantitas_default * $this->durasi * $prosesBop->komponenBop->tarif_per_satuan
            ]);
        }

        $this->recalculateBop();
    }
}

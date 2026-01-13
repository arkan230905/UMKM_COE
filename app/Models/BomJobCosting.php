<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobCosting extends Model
{
    use HasFactory;

    protected $table = 'bom_job_costings';

    protected $fillable = [
        'produk_id', 'jumlah_produk',
        'total_bbb', 'total_btkl', 'total_bahan_pendukung', 'total_bop',
        'total_hpp', 'hpp_per_unit'
    ];

    protected $casts = [
        'jumlah_produk' => 'integer',
        'total_bbb' => 'decimal:2',
        'total_btkl' => 'decimal:2',
        'total_bahan_pendukung' => 'decimal:2',
        'total_bop' => 'decimal:2',
        'total_hpp' => 'decimal:2',
        'hpp_per_unit' => 'decimal:2',
    ];

    // Relasi ke Produk (1 Produk = 1 BOM)
    public function produk() { return $this->belongsTo(Produk::class); }
    
    // Relasi detail
    public function detailBBB() { return $this->hasMany(BomJobBBB::class, 'bom_job_costing_id'); }
    public function detailBTKL() { return $this->hasMany(BomJobBTKL::class, 'bom_job_costing_id'); }
    public function detailBahanPendukung() { return $this->hasMany(BomJobBahanPendukung::class, 'bom_job_costing_id'); }
    public function detailBOP() { return $this->hasMany(BomJobBOP::class, 'bom_job_costing_id'); }

    /**
     * Hitung ulang semua total dan update HPP di produk
     */
    public function recalculate()
    {
        $this->total_bbb = $this->detailBBB()->sum('subtotal');
        $this->total_btkl = $this->detailBTKL()->sum('subtotal');
        $this->total_bahan_pendukung = $this->detailBahanPendukung()->sum('subtotal');
        $this->total_bop = $this->detailBOP()->sum('subtotal');
        
        $this->total_hpp = $this->total_bbb + $this->total_btkl + $this->total_bahan_pendukung + $this->total_bop;
        $this->hpp_per_unit = $this->jumlah_produk > 0 ? $this->total_hpp / $this->jumlah_produk : 0;
        
        $this->save();
        
        // Update HPP dan Biaya Bahan di produk
        if ($this->produk) {
            // Hitung total biaya bahan (BBB + Bahan Pendukung)
            $totalBiayaBahan = $this->total_bbb + $this->total_bahan_pendukung;
            $biayaBahanPerUnit = $this->jumlah_produk > 0 ? $totalBiayaBahan / $this->jumlah_produk : 0;
            
            // Debug logging
            \Log::info('=== BOM RECALCULATE DEBUG ===');
            \Log::info('Produk ID: ' . $this->produk->id);
            \Log::info('Total BBB: ' . $this->total_bbb);
            \Log::info('Total Bahan Pendukung: ' . $this->total_bahan_pendukung);
            \Log::info('Total Biaya Bahan: ' . $totalBiayaBahan);
            \Log::info('Jumlah Produk: ' . $this->jumlah_produk);
            \Log::info('Biaya Bahan Per Unit: ' . $biayaBahanPerUnit);
            \Log::info('HPP Per Unit: ' . $this->hpp_per_unit);
            
            $this->produk->update([
                'biaya_bahan' => $biayaBahanPerUnit  // Total biaya bahan per unit
            ]);
            
            \Log::info('Update successful - Biaya Bahan: ' . $this->produk->fresh()->biaya_bahan);
        }
        
        return $this;
    }
}

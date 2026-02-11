<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Support\UnitConverter;

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
     * Total HPP = BBB + Bahan Pendukung + BTKL + BOP
     */
    public function recalculate()
    {
        // Langsung sum dari subtotal yang sudah dihitung (jumlah x harga_satuan)
        $this->total_bbb = $this->detailBBB()->sum('subtotal');
        $this->total_btkl = $this->detailBTKL()->sum('subtotal');
        $this->total_bahan_pendukung = $this->detailBahanPendukung()->sum('subtotal');
        $this->total_bop = $this->detailBOP()->sum('subtotal');
        
        // Total HPP = BBB + Bahan Pendukung + BTKL + BOP
        $this->total_hpp = $this->total_bbb + $this->total_bahan_pendukung + $this->total_btkl + $this->total_bop;
        $this->hpp_per_unit = $this->jumlah_produk > 0 ? $this->total_hpp / $this->jumlah_produk : 0;
        
        $this->save();
        
        // Update HPP dan Biaya Bahan di produk (TANPA TRIGGER OBSERVER)
        if ($this->produk) {
            // Hitung total biaya bahan (BBB + Bahan Pendukung)
            $totalBiayaBahan = $this->total_bbb + $this->total_bahan_pendukung;
            $biayaBahanPerUnit = $this->jumlah_produk > 0 ? $totalBiayaBahan / $this->jumlah_produk : 0;
            
            // Update harga_bom dengan total HPP dan harga_jual dengan margin
            $margin = $this->produk->margin_percent ?? 30;
            $hargaJual = $this->hpp_per_unit * (1 + ($margin / 100));
            
            // Use DB::table to avoid triggering observers and prevent infinite loop
            DB::table('produks')
                ->where('id', $this->produk->id)
                ->update([
                    'biaya_bahan' => $biayaBahanPerUnit,
                    'harga_bom' => $this->hpp_per_unit,  // HPP per unit dari BOM Job Costing
                    'harga_jual' => $hargaJual,
                    'updated_at' => now()
                ]);
        }
        
        return $this;
    }
}

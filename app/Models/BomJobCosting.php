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
            // Hitung biaya bahan baku PER UNIT (HANYA BBB, tanpa bahan pendukung)
            $biayaBahanBakuPerUnit = $this->jumlah_produk > 0 ? $this->total_bbb / $this->jumlah_produk : 0;
            
            // Use DB::table to avoid triggering observers and prevent infinite loop
            // Hanya update HPP dan biaya bahan baku, jangan ubah harga_jual yang sudah diset manual
            DB::table('produks')
                ->where('id', $this->produk->id)
                ->update([
                    'biaya_bahan' => $biayaBahanBakuPerUnit,  // HANYA biaya bahan baku per unit
                    'harga_bom' => $this->total_hpp,  // Total HPP lengkap (BBB + Bahan Pendukung + BTKL + BOP)
                    'harga_pokok' => $this->total_hpp,  // Update harga_pokok untuk referensi HPP
                    // 'harga_jual' DIHAPUS agar tidak mengubah harga jual manual
                    'updated_at' => now()
                ]);
        }
        
        return $this;
    }
}

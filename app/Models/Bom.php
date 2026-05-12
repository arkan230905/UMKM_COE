<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'kode_bom',
        'total_biaya',
        'total_bbb',
        'total_hpp',
        'catatan',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'total_biaya' => 'decimal:2',
        'total_bbb' => 'decimal:2',
        'total_hpp' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::deleting(function ($model) {
            // Reset harga_bom di produk sebelum BOM dihapus
            if ($model->produk) {
                $model->produk->update([
                    'harga_bom' => 0,
                ]);
            }
        });
        
        static::deleted(function ($model) {
            // Hapus detail BOM terkait
            $model->details()->delete();
        });
    }

    /**
     * Get the produk that owns the BOM.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class)->withDefault();
    }
    
    /**
     * Get the details for the BOM (Bahan Baku / BBB).
     */
    public function details()
    {
        return $this->hasMany(BomDetail::class)->with(['bahanBaku' => function($query) {
            $query->with('satuan')->withDefault();
        }]);
    }
    
    /**
     * Get the production processes for the BOM (BTKL + BOP).
     */
    public function proses()
    {
        return $this->hasMany(BomProses::class, 'bom_id')->orderBy('urutan');
    }
    
    /**
     * Calculate total cost of BOM.
     */
    public function calculateTotalCost()
    {
        $total = 0;
        
        foreach ($this->details as $detail) {
            $total += $detail->harga_per_satuan * $detail->jumlah;
        }
        
        $this->total_biaya = $total;
        $this->save();
        
        return $total;
    }
    
    /**
     * Calculate total cost including BTKL and BOP using Process Costing method.
     * HPP = Total BBB + Total BTKL + Total BOP
     * Returns total biaya produksi (HPP)
     */
    public function hitungTotalBiaya()
    {
        // Total BBB (Biaya Bahan Baku) dari detail BOM
        $totalBBB = $this->details->sum('subtotal');
        $this->total_bbb = $totalBBB;
        
        // Cek apakah ada proses produksi yang didefinisikan
        $hasProses = $this->proses()->exists();
        
        $totalBTKL = 0;
        $totalBOP = 0;
        
        if ($hasProses) {
            // Process Costing: Hitung dari proses produksi
            $totalBTKL = $this->proses->sum('biaya_btkl');
            $totalBOP = $this->proses->sum('biaya_bop');
        } else {
            // Fallback: Gunakan persentase jika belum ada proses
            // Ini untuk backward compatibility dengan BOM lama
            $totalBTKL = $totalBBB * 0.6; // 60%
            $totalBOP = $totalBBB * 0.4; // 40%
        }
        
        // Total HPP = BBB + BTKL + BOP
        $this->total_hpp = $totalBBB + $totalBTKL + $totalBOP;
        $this->total_biaya = $this->total_hpp; // Untuk backward compatibility
        
        return $this->total_hpp;
    }
    
    /**
     * Update related product price.
     * Menggunakan metode Process Costing untuk menghitung HPP
     */
    public function updateProductPrice()
    {
        if ($this->produk) {
            // Hitung total biaya produksi (HPP) dari total_hpp
            $hpp = $this->total_hpp ?? 0;
            
            // Update harga_bom dengan HPP
            // Update harga_jual dengan HPP + margin
            $margin = $this->produk->margin_percent ?? 0;
            $hargaJual = $hpp * (1 + ($margin / 100));
            
            $this->produk->update([
                'harga_bom' => $hpp,  // HPP dari BOM
                'harga_jual' => $hargaJual  // Harga jual = HPP + margin
            ]);
        }
        return $this;
    }
}

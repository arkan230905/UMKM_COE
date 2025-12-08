<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'bahan_baku_id',
        'jumlah',
        'satuan_resep',
        'total_biaya',
        'total_bbb',
        'btkl_per_unit',
        'bop_rate',
        'bop_per_unit',
        'total_btkl',
        'total_bop',
        'total_hpp',
        'periode',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'jumlah' => 'decimal:4',
        'total_biaya' => 'decimal:2',
        'total_bbb' => 'decimal:2',
        'btkl_per_unit' => 'decimal:2',
        'bop_rate' => 'decimal:2',
        'bop_per_unit' => 'decimal:2',
        'total_btkl' => 'decimal:2',
        'total_bop' => 'decimal:2',
        'total_hpp' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->periode)) {
                $model->periode = now()->format('Y-m');
            }
            
            // Hitung total biaya secara otomatis saat menyimpan
            if ($model->isDirty(['total_biaya', 'total_btkl', 'total_bop'])) {
                $model->hitungTotalBiaya();
            }
        });
        
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
     * Get the main bahan baku for the BOM.
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id')->withDefault();
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
        $totalBBB = $this->details->sum('total_harga');
        $this->total_bbb = $totalBBB;
        
        // Cek apakah ada proses produksi yang didefinisikan
        $hasProses = $this->proses()->exists();
        
        if ($hasProses) {
            // Process Costing: Hitung dari proses produksi
            $this->total_btkl = $this->proses->sum('biaya_btkl');
            $this->total_bop = $this->proses->sum('biaya_bop');
        } else {
            // Fallback: Gunakan persentase jika belum ada proses
            // Ini untuk backward compatibility dengan BOM lama
            if (!$this->total_btkl) {
                $this->total_btkl = $totalBBB * 0.6; // 60%
            }
            if (!$this->total_bop) {
                $this->total_bop = $totalBBB * 0.4; // 40%
            }
        }
        
        // Total HPP = BBB + BTKL + BOP
        $this->total_hpp = $totalBBB + $this->total_btkl + $this->total_bop;
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
            // Hitung total biaya produksi (HPP) = Bahan Baku + BTKL + BOP
            $totalBahanBaku = $this->details->sum('total_harga');
            $hpp = $totalBahanBaku + $this->total_btkl + $this->total_bop;
            
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

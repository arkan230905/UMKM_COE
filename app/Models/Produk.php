<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';
    protected $fillable = [
        'kode_produk',
        'barcode',
        'nama_produk',
        'foto',
        'deskripsi',
        'kategori_id',
        'satuan_id',
        'harga_jual',
        'harga_bom',
        'harga_beli',
        'hpp',
        'stok',
        'stok_minimum',
        'btkl_default',
        'bop_default',
        'margin_percent',
        'bopb_method',
        'bopb_rate',
        'labor_hours_per_unit',
        'btkl_per_unit',
    ];
    
    /**
     * Generate barcode otomatis untuk produk baru
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($produk) {
            if (empty($produk->barcode)) {
                // Generate barcode format EAN-13: 8992XXXXXXXXX
                $lastId = static::max('id') ?? 0;
                $produk->barcode = '8992' . str_pad($lastId + 1, 9, '0', STR_PAD_LEFT);
            }
            
            // Auto-calculate harga_jual if not set
            if (empty($produk->harga_jual) && !empty($produk->margin_percent)) {
                $hpp = $produk->harga_bom ?? 0;
                $produk->harga_jual = $hpp + ($hpp * $produk->margin_percent / 100);
            }
        });
        
        static::updating(function ($produk) {
            // Auto-calculate harga_jual when margin_percent changes
            if ($produk->isDirty('margin_percent') || $produk->isDirty('harga_bom')) {
                $hpp = $produk->harga_bom ?? 0;
                $marginPercent = $produk->margin_percent ?? 0;
                $produk->harga_jual = $hpp + ($hpp * $marginPercent / 100);
            }
        });
    }
    
    /**
     * Get the kategori that owns the Produk
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id')
            ->withDefault([
                'nama' => 'Tidak Diketahui',
                'kode_kategori' => 'N/A'
            ]);
    }
    
    /**
     * Get the satuan that owns the Produk
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id')
            ->withDefault([
                'nama' => 'PCS',
                'kode_satuan' => 'PCS'
            ]);
    }

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }
    
    /**
     * Get the BomJobCosting for the Produk
     */
    public function bomJobCosting()
    {
        return $this->hasOne(BomJobCosting::class);
    }
    
    /**
     * Calculate harga jual based on HPP + margin
     */
    public function calculateHargaJual()
    {
        $hpp = $this->getActualHPP();
        $marginPercent = $this->margin_percent ?? 0;
        
        return $hpp + ($hpp * $marginPercent / 100);
    }
    
    /**
     * Get actual HPP based on production costs
     */
    public function getActualHPP($tanggalPenjualan = null)
    {
        // Ambil biaya produksi dari tabel produksi untuk produk ini
        $query = \App\Models\Produksi::where('produk_id', $this->id)
            ->where('status', 'completed')
            ->orderBy('id', 'desc')
            ->take(5); // Ambil 5 produksi terakhir
        
        // Jika ada tanggal penjualan, filter produksi sebelum tanggal tersebut
        if ($tanggalPenjualan) {
            $query->where('tanggal', '<=', $tanggalPenjualan);
        }
        
        $productionCosts = $query->get();
        
        if ($productionCosts->isEmpty()) {
            // Jika tidak ada data produksi, gunakan harga_bom sebagai fallback
            return $this->harga_bom ?? 0;
        }
        
        // Prioritaskan produksi yang memiliki total_biaya dan qty_produksi
        foreach ($productionCosts as $production) {
            // Gunakan total_biaya dan qty_produksi dari produksi jika ada
            if ($production->total_biaya > 0 && $production->qty_produksi > 0) {
                return $production->total_biaya / $production->qty_produksi;
            }
        }
        
        // Fallback ke perhitungan dari detail produksi
        $totalCost = 0;
        $totalQuantity = 0;
        $hasValidData = false;
        
        foreach ($productionCosts as $production) {
            // Ambil detail produksi
            $productionDetails = \App\Models\ProduksiDetail::where('produksi_id', $production->id)->get();
            
            foreach($productionDetails as $detail) {
                // Skip jika data tidak valid
                if (empty($detail->qty_konversi) || empty($detail->harga_satuan)) {
                    continue;
                }
                
                $hasValidData = true;
                
                // Gunakan subtotal dari detail produksi
                $totalCost += $detail->subtotal;
                $totalQuantity += $detail->qty_konversi;
            }
        }
        
        // Jika tidak ada data valid, gunakan harga_bom sebagai fallback
        if (!$hasValidData || $totalQuantity == 0) {
            return $this->harga_bom ?? 0;
        }
        
        // Hitung HPP per unit
        $hppPerUnit = $totalCost / $totalQuantity;
        
        return $hppPerUnit;
    }
    
    /**
     * Get HPP for specific sale date (FIFO method)
     */
    public function getHPPForSaleDate($tanggalPenjualan)
    {
        return $this->getActualHPP($tanggalPenjualan);
    }
}

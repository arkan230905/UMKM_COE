<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';
    protected $fillable = [
        'user_id',
        'kode_produk',
        'barcode',
        'nama_produk',
        'foto',
        'deskripsi',
        'kategori_id',
        'satuan_id',
        'harga_jual',
        'harga_bom',
        'stok',
        'is_unlimited_stok',
        'stok_minimum',
        'btkl_per_unit',
        'coa_persediaan_id',
        'harga_pokok',
    ];
    
    /**
     * Generate barcode otomatis untuk produk baru
     */
    protected static function boot()
    {
        parent::boot();
        
        // CRITICAL: Apply global scope untuk multi-tenant isolation
        static::addGlobalScope(new \App\Scopes\UserScope);
        
        static::creating(function ($produk) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($produk->user_id) && auth()->check()) {
                $produk->user_id = auth()->id();
            }
            
            if (empty($produk->barcode)) {
                // Generate barcode format EAN-13: 8992XXXXXXXXX
                $lastId = static::max('id') ?? 0;
                $produk->barcode = '8992' . str_pad($lastId + 1, 9, '0', STR_PAD_LEFT);
            }
            
            // 🔒 SECURITY: Generate kode_produk otomatis untuk multi-tenant
            if (empty($produk->kode_produk)) {
                $userId = auth()->id();
                $lastProduct = static::where('user_id', $userId)
                    ->orderBy('id', 'desc')
                    ->first();
                
                $sequence = $lastProduct ? ((int)str_replace(['PRD-', $userId . '-'], '', $lastProduct->kode_produk) + 1) : 1;
                $produk->kode_produk = 'PRD-' . $userId . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }
            
            // Auto-calculate harga_jual if not set
            if (empty($produk->harga_jual) && !empty($produk->margin_percent)) {
                $hpp = $produk->harga_bom ?? 0;
                $produk->harga_jual = $hpp + ($hpp * $produk->margin_percent / 100);
            }
        });
        
        static::updating(function ($produk) {
            // TEMPORARILY DISABLED for debugging
            \Log::info('MODEL UPDATING EVENT TRIGGERED for ' . $produk->nama_produk);
            \Log::info('Dirty fields: ' . json_encode($produk->getDirty()));
            \Log::info('harga_jual in model: ' . $produk->harga_jual);
            \Log::info('margin_percent in model: ' . $produk->margin_percent);
            
            // Only auto-calculate harga_jual if it's not explicitly set by user
            // and margin_percent changes, or if harga_bom changes and harga_jual is null
            /*
            if (($produk->isDirty('margin_percent') && !$produk->isDirty('harga_jual')) || 
                ($produk->isDirty('harga_bom') && is_null($produk->harga_jual))) {
                $hpp = $produk->harga_bom ?? 0;
                $marginPercent = $produk->margin_percent ?? 0;
                $produk->harga_jual = $hpp + ($hpp * $marginPercent / 100);
            }
            */
        });
    }
    
    /**
     * Get the kategori that owns the Produk
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id')
            ->withoutGlobalScopes()
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
     * DEPRECATED: BomJobCosting table has been removed
     */
    // public function bomJobCosting()
    // {
    //     return $this->hasOne(BomJobCosting::class);
    // }
    
    /**
     * Get biaya bahan baku for this product
     */
    public function biayaBahanBaku()
    {
        return $this->hasMany(BiayaBahanBaku::class);
    }
    
    /**
     * Check if product has HPP data
     */
    public function hasHppData()
    {
        // Check if product has BBB that is selected in HPP
        return $this->biayaBahanBaku()
            ->whereHas('hargaPokokProduksiBiayaBahanBaku', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->exists();
    }
    
    /**
     * Calculate harga jual based on HPP + margin
     */
    public function calculateHargaJual()
    {
        $hpp = $this->getActualHPP();
        $marginPercent = 0; // Fixed margin since margin_percent column was removed
        
        return $hpp + ($hpp * $marginPercent / 100);
    }
    
    /**
     * Get actual HPP based on Harga Pokok Produksi calculation
     * Priority: harga_pokok dari database > HPP calculation > production costs > fallback values
     */
    public function getActualHPP($tanggalPenjualan = null)
    {
        // PRIORITY 1: Use harga_pokok from database if it's already set and > 0
        if (!empty($this->harga_pokok) && $this->harga_pokok > 0) {
            return $this->harga_pokok;
        }
        
        // PRIORITY 2: Get from Harga Pokok Produksi (BBB + BTKL + BOP)
        $hppFromCalculation = $this->getHPPFromHargaPokokProduksi();
        if ($hppFromCalculation > 0) {
            return $hppFromCalculation;
        }
        
        // PRIORITY 3: Try to get from production costs
        $query = \App\Models\Produksi::where('produk_id', $this->id)
            ->where('status', 'completed')
            ->orderBy('id', 'desc')
            ->take(5); // Ambil 5 produksi terakhir
        
        // Jika ada tanggal penjualan, filter produksi sebelum tanggal tersebut
        if ($tanggalPenjualan) {
            $query->where('tanggal', '<=', $tanggalPenjualan);
        }
        
        $productionCosts = $query->get();
        
        if ($productionCosts->isNotEmpty()) {
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
                    $totalCost += $detail->subtotal ?? 0;
                    $totalQuantity += $detail->qty_konversi ?? 0;
                }
            }
            
            if ($hasValidData && $totalQuantity > 0) {
                return $totalCost / $totalQuantity;
            }
        }
        
        // PRIORITY 4: Fallback - return 0 if no cost data available
        return 0;
    }
    
    /**
     * Get HPP from Harga Pokok Produksi calculation (BBB + BTKL + BOP)
     * This is the MAIN source for HPP calculation
     * 
     * FIXED: Now correctly calculates HPP using the same logic as view:
     * 1. Getting BBB selected for this product ONLY (product-specific)
     * 2. Getting BTKL selected (user-wide, not product-specific)
     * 3. Getting BOP selected (user-wide, not product-specific)
     * 4. Returns total HPP = BBB + BTKL + BOP
     */
    private function getHPPFromHargaPokokProduksi()
    {
        $userId = $this->user_id ?? auth()->id();
        
        // Get BBB (Biaya Bahan Baku) for this product ONLY
        $selectedBbb = \App\Models\HargaPokokProduksiBiayaBahanBaku::where('user_id', $userId)
            ->whereHas('biayaBahanBaku', function($query) {
                $query->where('produk_id', $this->id);
            })
            ->with('biayaBahanBaku')
            ->get();
        
        $totalBbb = 0;
        foreach ($selectedBbb as $bbb) {
            if ($bbb->biayaBahanBaku) {
                $totalBbb += $bbb->biayaBahanBaku->subtotal ?? 0;
            }
        }
        
        // Get BTKL (Biaya Tenaga Kerja Langsung) - user-wide, not product-specific
        $selectedBtkl = \App\Models\HargaPokokProduksiBtkl::where('user_id', $userId)
            ->with('prosesProduksi')
            ->get();
        
        $totalBtkl = 0;
        foreach ($selectedBtkl as $btkl) {
            if ($btkl->prosesProduksi) {
                // Use tarif_btkl directly as biaya per produk
                $tarif = $btkl->prosesProduksi->tarif_btkl ?? 0;
                $totalBtkl += $tarif;
            }
        }
        
        // Get BOP (Biaya Overhead Pabrik) - user-wide, not product-specific
        $selectedBop = \App\Models\HargaPokokProduksiBop::where('user_id', $userId)
            ->with('bopProses')
            ->get();
        
        $totalBop = 0;
        foreach ($selectedBop as $bop) {
            if ($bop->bopProses) {
                $totalBop += $bop->bopProses->total_bop_per_produk ?? 0;
            }
        }
        
        // Total HPP = BBB + BTKL + BOP
        $totalHpp = $totalBbb + $totalBtkl + $totalBop;
        
        return $totalHpp;
    }
    
    /**
     * Get HPP for specific sale date (FIFO method)
     */
    public function getHPPForSaleDate($tanggalPenjualan)
    {
        return $this->getActualHPP($tanggalPenjualan);
    }
    
    /**
     * Get harga pokok attribute
     */
    public function getHargaPokokAttribute()
    {
        return $this->attributes['harga_pokok'] ?? 0;
    }
    
    /**
     * Sync stok field dengan StockLayer
     */
    public function syncStok()
    {
        $actualStock = StockLayer::where('item_type', 'product')
            ->where('item_id', $this->id)
            ->sum('remaining_qty');
            
        $this->update(['stok' => $actualStock]);
    }
    
    /**
     * Get actual stock from StockLayer
     */
    public function getActualStokAttribute()
    {
        return StockLayer::where('item_type', 'product')
            ->where('item_id', $this->id)
            ->sum('remaining_qty');
    }
    
    /**
     * Get the COA persediaan for the Produk
     */
    public function coaPersediaan()
    {
        return $this->belongsTo(Coa::class, 'coa_persediaan_id', 'kode_akun');
    }
    
    /**
     * Get the COA HPP for the Produk
     */
    public function coaHpp()
    {
        return $this->belongsTo(Coa::class, 'coa_hpp_id', 'kode_akun');
    }

    /**
     * Get the reviews for the Produk
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the favorites for the Produk
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get average rating
     */
    public function getRatingAttribute()
    {
        return number_format($this->reviews()->avg('rating') ?? 5.0, 1);
    }
}

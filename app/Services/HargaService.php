<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\PembelianDetail;
use App\Models\Pembelian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HargaService
{
    /**
     * Calculate proper weighted average for bahan baku
     */
    public function calculateWeightedAverage($bahanBakuId)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        if (!$bahanBaku) return 0;
        
        // Get all purchase history
        $pembelianDetails = PembelianDetail::where('bahan_baku_id', $bahanBakuId)
            ->join('pembelians', 'pembelian_details.pembelian_id', '=', 'pembelians.id')
            ->select(
                'pembelian_details.jumlah',
                'pembelian_details.satuan',
                'pembelian_details.harga_satuan',
                'pembelian_details.subtotal',
                'pembelians.tanggal'
            )
            ->orderBy('pembelians.tanggal', 'asc')
            ->get();
        
        if ($pembelianDetails->isEmpty()) {
            return $bahanBaku->harga_satuan ?? 0;
        }
        
        $totalQuantity = 0;
        $totalValue = 0;
        
        foreach ($pembelianDetails as $detail) {
            $totalQuantity += $detail->jumlah;
            $totalValue += $detail->subtotal;
        }
        
        return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
    }
    
    /**
     * Calculate proper weighted average for bahan pendukung
     */
    public function calculateWeightedAverageBahanPendukung($bahanPendukungId)
    {
        $bahanPendukung = BahanPendukung::find($bahanPendukungId);
        if (!$bahanPendukung) return 0;
        
        // Get all purchase history
        $pembelianDetails = PembelianDetail::where('bahan_pendukung_id', $bahanPendukungId)
            ->join('pembelians', 'pembelian_details.pembelian_id', '=', 'pembelians.id')
            ->select(
                'pembelian_details.jumlah',
                'pembelian_details.satuan',
                'pembelian_details.harga_satuan',
                'pembelian_details.subtotal',
                'pembelians.tanggal'
            )
            ->orderBy('pembelians.tanggal', 'asc')
            ->get();
        
        if ($pembelianDetails->isEmpty()) {
            return $bahanPendukung->harga_satuan ?? 0;
        }
        
        $totalQuantity = 0;
        $totalValue = 0;
        
        foreach ($pembelianDetails as $detail) {
            $totalQuantity += $detail->jumlah;
            $totalValue += $detail->subtotal;
        }
        
        return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
    }
    
    /**
     * Get detailed purchase history for bahan baku
     */
    public function getPurchaseHistory($bahanBakuId, $limit = 10)
    {
        return PembelianDetail::where('bahan_baku_id', $bahanBakuId)
            ->join('pembelians', 'pembelian_details.pembelian_id', '=', 'pembelians.id')
            ->select(
                'pembelians.tanggal',
                'pembelians.nomor_pembelian',
                'pembelian_details.jumlah',
                'pembelian_details.satuan',
                'pembelian_details.harga_satuan',
                'pembelian_details.subtotal'
            )
            ->orderBy('pembelians.tanggal', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'nomor_pembelian' => $item->nomor_pembelian,
                    'jumlah' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'subtotal' => $item->subtotal,
                    'harga_per_kg' => $this->convertToKg($item->satuan, $item->harga_satuan)
                ];
            });
    }
    
    /**
     * Get detailed purchase history for bahan pendukung
     */
    public function getPurchaseHistoryBahanPendukung($bahanPendukungId, $limit = 10)
    {
        return PembelianDetail::where('bahan_pendukung_id', $bahanPendukungId)
            ->join('pembelians', 'pembelian_details.pembelian_id', '=', 'pembelians.id')
            ->select(
                'pembelians.tanggal',
                'pembelians.nomor_pembelian',
                'pembelian_details.jumlah',
                'pembelian_details.satuan',
                'pembelian_details.harga_satuan',
                'pembelian_details.subtotal'
            )
            ->orderBy('pembelians.tanggal', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'nomor_pembelian' => $item->nomor_pembelian,
                    'jumlah' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'subtotal' => $item->subtotal,
                    'harga_per_kg' => $this->convertToKg($item->satuan, $item->harga_satuan)
                ];
            });
    }
    
    /**
     * Recalculate harga rata-rata based on all purchases
     */
    public function recalculateHargaRataRata($bahanBakuId)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        if (!$bahanBaku) return false;
        
        // Calculate proper weighted average
        $hargaRataRataBaru = $this->calculateWeightedAverage($bahanBakuId);
        
        // Update with proper calculation
        $bahanBaku->update([
            'harga_rata_rata' => $hargaRataRataBaru,
            'harga_satuan' => $hargaRataRataBaru // Keep harga_satuan consistent
        ]);
        
        Log::info('Harga rata-rata dihitung ulang', [
            'bahan_baku_id' => $bahanBakuId,
            'nama_bahan' => $bahanBaku->nama_bahan,
            'harga_rata_rata_lama' => $bahanBaku->harga_rata_rata,
            'harga_rata_rata_baru' => $hargaRataRataBaru,
            'metode' => 'Weighted Average dari semua pembelian'
        ]);
        
        return $hargaRataRataBaru;
    }
    
    /**
     * Recalculate harga rata-rata for bahan pendukung
     */
    public function recalculateHargaRataRataBahanPendukung($bahanPendukungId)
    {
        $bahanPendukung = BahanPendukung::find($bahanPendukungId);
        if (!$bahanPendukung) return false;
        
        // Calculate proper weighted average
        $hargaRataRataBaru = $this->calculateWeightedAverageBahanPendukung($bahanPendukungId);
        
        // Update with proper calculation
        $bahanPendukung->update([
            'harga_satuan' => $hargaRataRataBaru // Update harga_satuan
        ]);
        
        Log::info('Harga rata-rata bahan pendukung dihitung ulang', [
            'bahan_pendukung_id' => $bahanPendukungId,
            'nama_bahan' => $bahanPendukung->nama_bahan,
            'harga_satuan_lama' => $bahanPendukung->harga_satuan,
            'harga_satuan_baru' => $hargaRataRataBaru,
            'metode' => 'Weighted Average dari semua pembelian'
        ]);
        
        return $hargaRataRataBaru;
    }
    
    /**
     * Validate harga rata-rata consistency
     */
    public function validateHargaRataRata($bahanBakuId)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        if (!$bahanBaku) return null;
        
        $currentHarga = $bahanBaku->harga_rata_rata ?? $bahanBaku->harga_satuan ?? 0;
        $calculatedHarga = $this->calculateWeightedAverage($bahanBakuId);
        
        $difference = abs($currentHarga - $calculatedHarga);
        $isConsistent = $difference < 0.01; // Allow small rounding difference
        
        return [
            'bahan_baku_id' => $bahanBakuId,
            'nama_bahan' => $bahanBaku->nama_bahan,
            'harga_saat_ini' => $currentHarga,
            'harga_dihitung_ulang' => $calculatedHarga,
            'selisih' => $difference,
            'konsisten' => $isConsistent,
            'pesan' => $isConsistent ? '✅ Konsisten' : '❌ Tidak Konsisten'
        ];
    }
    
    /**
     * Validate harga rata-rata consistency for bahan pendukung
     */
    public function validateHargaRataRataBahanPendukung($bahanPendukungId)
    {
        $bahanPendukung = BahanPendukung::find($bahanPendukungId);
        if (!$bahanPendukung) return null;
        
        $currentHarga = $bahanPendukung->harga_satuan ?? 0;
        $calculatedHarga = $this->calculateWeightedAverageBahanPendukung($bahanPendukungId);
        
        $difference = abs($currentHarga - $calculatedHarga);
        $isConsistent = $difference < 0.01; // Allow small rounding difference
        
        return [
            'bahan_pendukung_id' => $bahanPendukungId,
            'nama_bahan' => $bahanPendukung->nama_bahan,
            'harga_saat_ini' => $currentHarga,
            'harga_dihitung_ulang' => $calculatedHarga,
            'selisih' => $difference,
            'konsisten' => $isConsistent,
            'pesan' => $isConsistent ? '✅ Konsisten' : '❌ Tidak Konsisten'
        ];
    }
    
    /**
     * Convert harga to kg for standardization
     */
    private function convertToKg($satuan, $harga)
    {
        // Normalize satuan
        $satuan = strtolower(trim($satuan));
        
        // Conversion factors to kg
        $factors = [
            'kg' => 1,
            'kilogram' => 1,
            'gram' => 0.001,
            'g' => 0.001,
            'liter' => 1,
            'ltr' => 1,
            'l' => 1,
            'mililiter' => 0.001,
            'ml' => 0.001,
            'pcs' => 1,
            'buah' => 1,
            'pack' => 1,
            'pak' => 1,
            'box' => 1,
            'botol' => 1,
            'dus' => 1
        ];
        
        $factor = $factors[$satuan] ?? 1;
        
        return $harga / $factor;
    }
}

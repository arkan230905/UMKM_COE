<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type','item_id','tanggal','direction','qty','satuan','unit_cost','total_cost','ref_type','ref_id','keterangan','manual_conversion_data'
    ];
    
    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::deleted(function ($movement) {
            // Find related pembelian detail
            $pembelianDetail = null;
            
            if ($movement->item_type === 'material') {
                // Try to find pembelian detail for bahan baku
                $pembelianDetail = \App\Models\PembelianDetail::where('bahan_baku_id', $movement->item_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                // Try to find pembelian detail for bahan pendukung
                if (!$pembelianDetail) {
                    $pembelianDetail = \App\Models\PembelianDetail::where('bahan_pendukung_id', $movement->item_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
                
                if ($pembelianDetail && $pembelianDetail->pembelian) {
                    $pembelian = $pembelianDetail->pembelian;
                    
                    // Update pembelian totals
                    $adjustmentAmount = $movement->qty * $movement->unit_cost;
                    $pembelian->total_harga = max(0, $pembelian->total_harga - $adjustmentAmount);
                    $pembelian->terbayar = max(0, $pembelian->terbayar - $adjustmentAmount);
                    $pembelian->sisa_pembayaran = max(0, $pembelian->sisa_pembayaran - $adjustmentAmount);
                    
                    // Update status if fully paid
                    if ($pembelian->sisa_pembayaran <= 0) {
                        $pembelian->status = 'lunas';
                        $pembelian->sisa_pembayaran = 0;
                    } else {
                        $pembelian->status = 'belum_lunas';
                    }
                    
                    $pembelian->save();
                    
                    // Create journal entry for stock adjustment
                    $journalService = new \App\Services\JournalService();
                    $journalEntries = [
                        ['code' => '5106', 'debit' => $adjustmentAmount, 'credit' => 0], // Dr Penyesuaian Persediaan
                        ['code' => '1101', 'debit' => 0, 'credit' => $adjustmentAmount], // Cr Persediaan
                    ];
                    
                    $journalService->post(
                        now(), 
                        'stock_adjustment', 
                        $pembelian->id, 
                        'Stock Adjustment for Purchase #' . $pembelian->id, 
                        $journalEntries
                    );
                }
            }
        });
    }
    
    /**
     * Get the related pembelian detail.
     */
    public function pembelianDetail()
    {
        return $this->belongsTo(PembelianDetail::class);
    }
}

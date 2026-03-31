<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_retur',
        'tanggal',
        'penjualan_id',
        'pelanggan_id',
        'jenis_retur',
        'total_retur',
        'ppn',
        'status',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_retur' => 'decimal:2',
        'ppn' => 'decimal:2'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }

    public function detailReturPenjualans()
    {
        return $this->hasMany(DetailReturPenjualan::class);
    }

    public function generateNomorRetur()
    {
        $date = now()->format('Ymd');
        $lastRetur = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRetur) {
            $lastNumber = (int) substr($lastRetur->nomor_retur, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'RET' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotalRetur()
    {
        $total = 0;
        
        foreach ($this->detailReturPenjualans as $detail) {
            $total += $detail->subtotal;
        }

        if ($this->jenis_retur === 'tukar_barang') {
            $this->total_retur = 0;
            $this->ppn = 0;
        } else {
            $this->ppn = $total * 0.11; // PPN 11%
            $this->total_retur = $total + $this->ppn;
        }

        $this->save();
    }

    public function processRetur()
    {
        switch ($this->jenis_retur) {
            case 'tukar_barang':
                $this->processTukarBarang();
                break;
            case 'refund':
                $this->processRefund();
                break;
            case 'kredit':
                $this->processKredit();
                break;
        }
    }

    private function processTukarBarang()
    {
        foreach ($this->detailReturPenjualans as $detail) {
            StockMovement::create([
                'item_type' => 'product',
                'item_id'   => $detail->produk_id,
                'tanggal'   => $this->tanggal,
                'direction' => 'in',
                'qty'       => $detail->qty_retur,
                'ref_type'  => 'retur_penjualan',
                'ref_id'    => $this->id,
            ]);
        }

        $this->status = 'selesai';
        $this->save();
    }

    private function processRefund()
    {
        JournalEntry::create([
            'tanggal'      => $this->tanggal,
            'keterangan'   => 'Refund Penjualan - ' . $this->nomor_retur,
            'total_debit'  => $this->total_retur,
            'total_kredit' => $this->total_retur,
        ]);

        foreach ($this->detailReturPenjualans as $detail) {
            StockMovement::create([
                'item_type' => 'product',
                'item_id'   => $detail->produk_id,
                'tanggal'   => $this->tanggal,
                'direction' => 'in',
                'qty'       => $detail->qty_retur,
                'ref_type'  => 'retur_penjualan',
                'ref_id'    => $this->id,
            ]);
        }

        $this->status = 'lunas';
        $this->save();
    }

    private function processKredit()
    {
        foreach ($this->detailReturPenjualans as $detail) {
            StockMovement::create([
                'item_type' => 'product',
                'item_id'   => $detail->produk_id,
                'tanggal'   => $this->tanggal,
                'direction' => 'in',
                'qty'       => $detail->qty_retur,
                'ref_type'  => 'retur_penjualan',
                'ref_id'    => $this->id,
            ]);
        }

        $this->status = 'belum_dibayar';
        $this->save();
    }
}

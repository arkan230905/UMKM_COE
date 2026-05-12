<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembelianDetail;
use App\Models\Pembelian;
use App\Models\BahanBaku;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Support\UnitConverter;

class StockBackfillPembelian extends Command
{
    protected $signature = 'stock:backfill-pembelian {--dry-run : Tampilkan rencana tanpa menulis data}';
    protected $description = 'Membuat StockLayer dan StockMovement IN untuk pembelian bahan baku yang belum tercatat ke kartu stok';

    public function handle(StockService $stock)
    {
        $dry = (bool)$this->option('dry-run');
        $converter = new UnitConverter();
        $count = 0; $skipped = 0;

        $details = PembelianDetail::with(['pembelian','bahanBaku'])->orderBy('id')->get();
        foreach ($details as $d) {
            $pembelian = $d->pembelian; $bahan = $d->bahanBaku;
            if (!$pembelian || !$bahan) { $skipped++; continue; }

            $tanggal = (string)($pembelian->tanggal ?? now()->toDateString());
            $satuanInput = (string)($d->satuan ?: $bahan->satuan);
            $qtyInput = (float)$d->jumlah;
            $subtotal = (float)$d->subtotal;
            $qtyBase = $converter->convert($qtyInput, $satuanInput, (string)$bahan->satuan);
            $unitCostBase = $qtyBase > 0 ? ($subtotal / $qtyBase) : (float)$d->harga_satuan;

            $exists = StockMovement::where('item_type','material')
                ->where('item_id',$bahan->id)
                ->where('ref_type','purchase')
                ->where('ref_id',$pembelian->id)
                ->exists();

            if ($exists) { $skipped++; continue; }

            if ($dry) {
                $this->line("DRY: add IN material#{$bahan->id} qty={$qtyBase} {$bahan->satuan} unit_cost={$unitCostBase} ref=purchase#{$pembelian->id} tanggal={$tanggal}");
            } else {
                $stock->addLayer('material', $bahan->id, $qtyBase, (string)$bahan->satuan, $unitCostBase, 'purchase', $pembelian->id, $tanggal);
            }
            $count++;
        }

        $this->info("Selesai. Ditambahkan: {$count}, dilewati: {$skipped}." . ($dry ? ' (DRY RUN)' : ''));
        return self::SUCCESS;
    }
}

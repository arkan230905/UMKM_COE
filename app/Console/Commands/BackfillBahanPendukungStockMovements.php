<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillBahanPendukungStockMovements extends Command
{
    protected $signature   = 'stok:backfill-bahan-pendukung';
    protected $description = 'Backfill stock movements untuk bahan pendukung dari produksi_bop_details';

    public function handle()
    {
        // Ambil semua bop_details yang kredit ke akun persediaan bahan pendukung (115x)
        $bopDetails = DB::table('produksi_bop_details')
            ->whereNotNull('coa_kredit_kode')
            ->where('coa_kredit_kode', 'like', '115%')
            ->get();

        $this->info("Ditemukan {$bopDetails->count()} bop_details dengan bahan pendukung.");

        $created = 0;
        $skipped = 0;

        foreach ($bopDetails as $bop) {
            // Cari bahan pendukung berdasarkan coa_persediaan_id
            // Prioritaskan yang nama_bahan mirip dengan nama_komponen
            $bpList = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $bop->coa_kredit_kode)
                ->get();

            if ($bpList->isEmpty()) {
                $this->warn("  Bahan pendukung tidak ditemukan untuk COA {$bop->coa_kredit_kode} ({$bop->nama_komponen})");
                $skipped++;
                continue;
            }

            // Cari yang nama_bahan paling mirip dengan nama_komponen
            $bp = null;
            $namaKomponen = strtolower($bop->nama_komponen);
            foreach ($bpList as $candidate) {
                if (stripos($namaKomponen, strtolower($candidate->nama_bahan)) !== false
                    || stripos(strtolower($candidate->nama_bahan), $namaKomponen) !== false) {
                    $bp = $candidate;
                    break;
                }
            }
            // Fallback: ambil yang pertama jika tidak ada yang cocok
            if (!$bp) $bp = $bpList->first();

            // Cek apakah sudah ada stock movement untuk produksi + bahan ini
            $exists = DB::table('stock_movements')
                ->where('item_type', 'support')
                ->where('item_id', $bp->id)
                ->where('ref_type', 'production')
                ->where('ref_id', $bop->produksi_id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Ambil tanggal produksi
            $produksi = DB::table('produksis')->find($bop->produksi_id);
            if (!$produksi) {
                $skipped++;
                continue;
            }

            // Hitung qty dalam satuan utama bahan pendukung
            // Rumus: qty = total_rupiah / harga_satuan_master
            // Contoh: Rp 120.000 / Rp 25.000 per Bungkus = 4.8 Bungkus Keju
            $hargaSatuan = (float) $bp->harga_satuan;
            $ratePerUnit = (float) $bop->rate_per_unit;
            $qty = $hargaSatuan > 0 ? round((float) $bop->total / $hargaSatuan, 4) : 0;

            DB::table('stock_movements')->insert([
                'item_type'  => 'support',
                'item_id'    => $bp->id,
                'tanggal'    => $produksi->tanggal,
                'direction'  => 'out',
                'qty'        => $qty,
                'satuan'     => null,
                'unit_cost'  => $hargaSatuan,
                'total_cost' => (float) $bop->total,
                'ref_type'   => 'production',
                'ref_id'     => $bop->produksi_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->line("  ✅ {$bp->nama_bahan} | Produksi #{$bop->produksi_id} | Qty: {$qty} (satuan utama) | Harga: Rp {$hargaSatuan} | Total: Rp " . number_format($bop->total, 0, ',', '.'));
            $created++;
        }

        $this->info("Selesai. Dibuat: {$created} | Dilewati: {$skipped}");
        return 0;
    }
}

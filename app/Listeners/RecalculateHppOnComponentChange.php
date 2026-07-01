<?php

namespace App\Listeners;

use App\Events\BiayaBahanBakuUpdated;
use App\Events\BtklUpdated;
use App\Events\BopUpdated;
use App\Models\HargaPokokProduksiBiayaBahanBaku;
use App\Models\HargaPokokProduksiBtkl;
use App\Models\HargaPokokProduksiBop;
use Illuminate\Support\Facades\Log;

class RecalculateHppOnComponentChange
{
    /**
     * Handle BiayaBahanBaku update event
     */
    public function handleBiayaBahanBaku(BiayaBahanBakuUpdated $event)
    {
        $biayaBahanBaku = $event->biayaBahanBaku;
        
        // Check if this BBB is used in any HPP
        $hppRecords = HargaPokokProduksiBiayaBahanBaku::where('bom_job_bbb_id', $biayaBahanBaku->id)->get();
        
        if ($hppRecords->isEmpty()) {
            return;
        }
        
        Log::info('HPP auto-update triggered by BiayaBahanBaku change', [
            'bbb_id' => $biayaBahanBaku->id,
            'produk_id' => $biayaBahanBaku->produk_id,
            'affected_hpp_records' => $hppRecords->count()
        ]);
        
        // Trigger cache clear or recalculation if needed
        // HPP will be recalculated on next view since we're using dynamic queries
    }

    /**
     * Handle BTKL (ProsesProduksi) update event
     */
    public function handleBtkl(BtklUpdated $event)
    {
        $prosesProduksi = $event->prosesProduksi;
        
        // Check if this BTKL is used in any HPP
        $hppRecords = HargaPokokProduksiBtkl::where('proses_produksis_id', $prosesProduksi->id)->get();
        
        if ($hppRecords->isEmpty()) {
            return;
        }
        
        Log::info('HPP auto-update triggered by BTKL change', [
            'proses_id' => $prosesProduksi->id,
            'affected_hpp_records' => $hppRecords->count(),
            'tarif_per_produk' => $prosesProduksi->tarif_per_produk,
            'jumlah_pegawai' => $prosesProduksi->jumlah_pegawai
        ]);
    }

    /**
     * Handle BOP update event
     */
    public function handleBop(BopUpdated $event)
    {
        $bopProses = $event->bopProses;
        
        // Check if this BOP is used in any HPP
        $hppRecords = HargaPokokProduksiBop::where('bop_proses_id', $bopProses->id)->get();
        
        if ($hppRecords->isEmpty()) {
            return;
        }
        
        Log::info('HPP auto-update triggered by BOP change', [
            'bop_id' => $bopProses->id,
            'produk_id' => $bopProses->produk_id,
            'affected_hpp_records' => $hppRecords->count(),
            'total_bop_per_produk' => $bopProses->total_bop_per_produk
        ]);
    }
}

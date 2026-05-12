<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KartuStok;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Services\StockService;

class LaporanKartuStokController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display stock report
     */
    public function index(Request $request)
    {
        $itemType = $request->get('item_type', 'bahan_baku'); // bahan_baku|bahan_pendukung
        $itemId = $request->get('item_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get items for dropdown
        $bahanBakus = BahanBaku::orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::orderBy('nama_bahan')->get();

        $stockReport = null;
        $selectedItem = null;

        if ($itemId) {
            // Get selected item
            if ($itemType === KartuStok::ITEM_TYPE_BAHAN_BAKU) {
                $selectedItem = BahanBaku::find($itemId);
            } elseif ($itemType === KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG) {
                $selectedItem = BahanPendukung::find($itemId);
            }

            if ($selectedItem) {
                // Get stock report
                $stockReport = $this->stockService->getStockReport($itemId, $itemType, $startDate, $endDate);
            }
        }

        return view('laporan.kartu-stok.index', compact(
            'itemType',
            'itemId',
            'startDate',
            'endDate',
            'bahanBakus',
            'bahanPendukungs',
            'stockReport',
            'selectedItem'
        ));
    }

    /**
     * Get stock summary for all items
     */
    public function summary(Request $request)
    {
        $itemType = $request->get('item_type', 'bahan_baku');
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        $items = [];
        
        if ($itemType === KartuStok::ITEM_TYPE_BAHAN_BAKU) {
            $bahanBakus = BahanBaku::orderBy('nama_bahan')->get();
            foreach ($bahanBakus as $item) {
                $stock = $this->stockService->getCurrentStock($item->id, $itemType);
                $items[] = [
                    'id' => $item->id,
                    'nama' => $item->nama_bahan,
                    'stok' => $stock,
                    'satuan' => $item->satuan->nama ?? 'unit'
                ];
            }
        } elseif ($itemType === KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG) {
            $bahanPendukungs = BahanPendukung::orderBy('nama_bahan')->get();
            foreach ($bahanPendukungs as $item) {
                $stock = $this->stockService->getCurrentStock($item->id, $itemType);
                $items[] = [
                    'id' => $item->id,
                    'nama' => $item->nama_bahan,
                    'stok' => $stock,
                    'satuan' => $item->satuanRelation->nama ?? 'unit'
                ];
            }
        }

        return view('laporan.kartu-stok.summary', compact('itemType', 'asOfDate', 'items'));
    }

    /**
     * Export stock report to Excel
     */
    public function export(Request $request)
    {
        $itemType = $request->get('item_type', 'bahan_baku');
        $itemId = $request->get('item_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$itemId) {
            return back()->with('error', 'Pilih item terlebih dahulu untuk export.');
        }

        // Get selected item
        if ($itemType === KartuStok::ITEM_TYPE_BAHAN_BAKU) {
            $selectedItem = BahanBaku::find($itemId);
        } else {
            $selectedItem = BahanPendukung::find($itemId);
        }

        if (!$selectedItem) {
            return back()->with('error', 'Item tidak ditemukan.');
        }

        // Get stock report
        $stockReport = $this->stockService->getStockReport($itemId, $itemType, $startDate, $endDate);

        $filename = 'kartu-stok-' . str_replace(' ', '-', strtolower($selectedItem->nama_bahan)) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function() use ($stockReport, $selectedItem) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'Tanggal', 'Keterangan', 'Masuk', 'Keluar', 'Saldo'
            ]);

            // Saldo awal
            if ($stockReport['saldo_awal'] != 0) {
                fputcsv($handle, [
                    '', 'Saldo Awal', '', '', number_format($stockReport['saldo_awal'], 2)
                ]);
            }

            // Data
            foreach ($stockReport['entries'] as $entry) {
                fputcsv($handle, [
                    $entry['tanggal'],
                    $entry['keterangan'],
                    $entry['qty_masuk'] > 0 ? number_format($entry['qty_masuk'], 2) : '',
                    $entry['qty_keluar'] > 0 ? number_format($entry['qty_keluar'], 2) : '',
                    number_format($entry['saldo'], 2)
                ]);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
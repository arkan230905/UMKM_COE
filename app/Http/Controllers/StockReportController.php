<?php

namespace App\Http\Controllers;

use App\Services\RealTimeStockService;
use App\Models\StockMovement;
use App\Models\StockLayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockReportController extends Controller
{
    protected $stockService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->stockService = new RealTimeStockService();
    }

    /**
     * Display real-time stock report
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all, bahan_baku, bahan_pendukung, produk
        $status = $request->get('status', 'all'); // all, aman, menipis, habis
        
        $stockReport = $this->stockService->getStockReport();
        
        // Filter by item type
        if ($filter !== 'all') {
            $stockReport = [$filter => $stockReport[$filter] ?? []];
        }
        
        // Filter by status
        if ($status !== 'all') {
            foreach ($stockReport as $type => $items) {
                $stockReport[$type] = array_filter($items, function($item) use ($status) {
                    return ($item['status'] ?? 'aman') === $status;
                });
            }
        }
        
        // Get summary statistics
        $summary = $this->getStockSummary();
        
        return view('laporan.stock-realtime', compact('stockReport', 'summary', 'filter', 'status'));
    }

    /**
     * Display stock movements for a specific item
     */
    public function movements(Request $request)
    {
        $itemType = $request->get('item_type');
        $itemId = $request->get('item_id');
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        
        if (!$itemType || !$itemId) {
            return redirect()->route('laporan.stock-realtime')
                ->with('error', 'Item type dan ID harus diisi');
        }
        
        // Get item details
        $itemDetails = $this->getItemDetails($itemType, $itemId);
        
        // Get stock movements
        $movements = StockMovement::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->whereBetween('tanggal', [$dateFrom, $dateTo])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        // Get current stock
        $currentStock = $this->stockService->getCurrentStock($itemType, $itemId);
        
        // Calculate running balance
        $runningBalance = $currentStock;
        foreach ($movements as $movement) {
            $movement->running_balance = $runningBalance;
            if ($movement->direction === 'in') {
                $runningBalance -= $movement->qty;
            } else {
                $runningBalance += $movement->qty;
            }
        }
        
        return view('laporan.stock-movements', compact(
            'movements', 'itemDetails', 'currentStock', 'itemType', 'itemId', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Get stock summary statistics
     */
    private function getStockSummary()
    {
        $summary = [
            'total_items' => 0,
            'items_aman' => 0,
            'items_menipis' => 0,
            'items_habis' => 0,
            'total_value' => 0
        ];
        
        // Count by status
        $stockLayers = StockLayer::select('item_type', 'item_id', 'remaining_qty', 'unit_cost')
            ->get()
            ->groupBy(['item_type', 'item_id']);
        
        foreach ($stockLayers as $itemType => $items) {
            foreach ($items as $itemId => $layers) {
                $totalQty = $layers->sum('remaining_qty');
                $avgCost = $layers->avg('unit_cost');
                
                $summary['total_items']++;
                $summary['total_value'] += $totalQty * $avgCost;
                
                // Get minimum stock for status calculation
                $minimumStock = $this->getMinimumStock($itemType, $itemId);
                
                if ($totalQty <= 0) {
                    $summary['items_habis']++;
                } elseif ($totalQty <= $minimumStock) {
                    $summary['items_menipis']++;
                } else {
                    $summary['items_aman']++;
                }
            }
        }
        
        return $summary;
    }

    /**
     * Get item details for display
     */
    private function getItemDetails($itemType, $itemId)
    {
        switch ($itemType) {
            case 'material':
                $item = \App\Models\BahanBaku::with('satuan')->find($itemId);
                return [
                    'nama' => $item->nama_bahan ?? 'Unknown',
                    'satuan' => $item->satuan->nama ?? 'Unit',
                    'stok_minimum' => $item->stok_minimum ?? 0
                ];
            case 'support':
                $item = \App\Models\BahanPendukung::with('satuan')->find($itemId);
                return [
                    'nama' => $item->nama_bahan ?? 'Unknown',
                    'satuan' => $item->satuan->nama ?? 'Unit',
                    'stok_minimum' => $item->stok_minimum ?? 0
                ];
            case 'product':
                $item = \App\Models\Produk::with('satuan')->find($itemId);
                return [
                    'nama' => $item->nama_produk ?? 'Unknown',
                    'satuan' => $item->satuan->nama ?? 'Unit',
                    'stok_minimum' => 0
                ];
            default:
                return [
                    'nama' => 'Unknown',
                    'satuan' => 'Unit',
                    'stok_minimum' => 0
                ];
        }
    }

    /**
     * Get minimum stock for an item
     */
    private function getMinimumStock($itemType, $itemId)
    {
        switch ($itemType) {
            case 'material':
                $item = \App\Models\BahanBaku::find($itemId);
                return $item->stok_minimum ?? 0;
            case 'support':
                $item = \App\Models\BahanPendukung::find($itemId);
                return $item->stok_minimum ?? 0;
            default:
                return 0;
        }
    }

    /**
     * API endpoint for real-time stock data
     */
    public function apiStockData(Request $request)
    {
        $itemType = $request->get('item_type');
        $itemId = $request->get('item_id');
        
        if ($itemType && $itemId) {
            // Get specific item stock
            $currentStock = $this->stockService->getCurrentStock($itemType, $itemId);
            $recentMovements = $this->stockService->getStockMovements($itemType, $itemId, 10);
            
            return response()->json([
                'current_stock' => $currentStock,
                'recent_movements' => $recentMovements
            ]);
        } else {
            // Get summary data
            $summary = $this->getStockSummary();
            return response()->json($summary);
        }
    }

    /**
     * Sync stock data (manual trigger)
     */
    public function syncStock(Request $request)
    {
        try {
            // This is a manual sync function that can be used to reconcile stock
            // In case of discrepancies between model stock and stock layers
            
            $itemType = $request->get('item_type');
            $itemId = $request->get('item_id');
            
            if ($itemType && $itemId) {
                // Sync specific item
                $realTimeStock = $this->stockService->getCurrentStock($itemType, $itemId);
                
                switch ($itemType) {
                    case 'material':
                        \App\Models\BahanBaku::where('id', $itemId)->update(['stok' => $realTimeStock]);
                        break;
                    case 'support':
                        \App\Models\BahanPendukung::where('id', $itemId)->update(['stok' => $realTimeStock]);
                        break;
                    case 'product':
                        \App\Models\Produk::where('id', $itemId)->update(['stok' => $realTimeStock]);
                        break;
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Stock synchronized successfully',
                    'new_stock' => $realTimeStock
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Item type and ID required'
                ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync stock: ' . $e->getMessage()
            ], 500);
        }
    }
}
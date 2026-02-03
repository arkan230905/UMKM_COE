<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Produk;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

class LaporanStokController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:pegawai_gudang');
    }

    public function index(Request $request)
    {
        $tipe = $request->get('tipe', 'product');
        $dariTanggal = $request->get('dari_tanggal');
        $sampaiTanggal = $request->get('sampai_tanggal');
        
        // Konversi format tanggal jika masih dd/mm/yyyy
        if ($dariTanggal) {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dariTanggal)) {
                $parts = explode('/', $dariTanggal);
                $dariTanggal = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }
        
        if ($sampaiTanggal) {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $sampaiTanggal)) {
                $parts = explode('/', $sampaiTanggal);
                $sampaiTanggal = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }
        
        // Default ke 30 hari ke belakang jika tidak ada filter tanggal
        if (!$dariTanggal) {
            $dariTanggal = now()->subDays(30)->format('Y-m-d');
        }
        if (!$sampaiTanggal) {
            $sampaiTanggal = now()->format('Y-m-d');
        }
        
        // Get stock movements untuk periode yang dipilih
        $query = StockMovement::whereBetween('tanggal', [$dariTanggal, $sampaiTanggal]);
        
        // Filter berdasarkan tipe
        if ($tipe === 'product') {
            $query->where('item_type', 'product');
        } elseif ($tipe === 'material') {
            $query->where('item_type', 'material');
        } elseif ($tipe === 'support') {
            $query->where('item_type', 'support');
        }
        
        $stockMovements = $query->orderBy('tanggal', 'asc')->get();
        
        // Group movements by item and calculate totals
        $laporanStok = [];
        $processedItems = [];
        
        foreach ($stockMovements as $movement) {
            $itemKey = $movement->item_type . '_' . $movement->item_id;
            
            if (!isset($processedItems[$itemKey])) {
                $processedItems[$itemKey] = [
                    'item_type' => $movement->item_type,
                    'item_id' => $movement->item_id,
                    'nama_item' => '',
                    'satuan' => '',
                    'harga_satuan' => 0,
                    'saldo_qty' => 0,
                    'saldo_nilai' => 0,
                    'masuk_qty' => 0,
                    'masuk_nilai' => 0,
                    'keluar_qty' => 0,
                    'keluar_nilai' => 0,
                    'movements' => []
                ];
            }
            
            // Add movement to item
            $processedItems[$itemKey]['movements'][] = [
                'tanggal' => $movement->tanggal,
                'referensi' => $movement->ref_type . '#' . $movement->ref_id,
                'direction' => $movement->direction,
                'qty' => $movement->qty,
                'nilai' => ($movement->unit_cost ?? 0) * $movement->qty,
            ];
            
            // Update totals
            if ($movement->direction === 'in') {
                $processedItems[$itemKey]['masuk_qty'] += $movement->qty;
                $processedItems[$itemKey]['masuk_nilai'] += ($movement->unit_cost ?? 0) * $movement->qty;
            } else {
                $processedItems[$itemKey]['keluar_qty'] += $movement->qty;
                $processedItems[$itemKey]['keluar_nilai'] += ($movement->unit_cost ?? 0) * $movement->qty;
            }
        }
        
        // Calculate final saldo for each item
        foreach ($processedItems as $key => &$item) {
            $item['saldo_qty'] = $item['masuk_qty'] - $item['keluar_qty'];
            $item['saldo_nilai'] = $item['masuk_nilai'] - $item['keluar_nilai'];
            
            // Get item details
            if ($item['item_type'] === 'product') {
                $produk = Produk::find($item['item_id']);
                if ($produk) {
                    $item['nama_item'] = $produk->nama_produk;
                    $item['satuan'] = optional($produk->satuanRelation)->nama ?? 'PCS';
                    $item['harga_satuan'] = $produk->harga_jual ?? 0;
                }
            } elseif ($item['item_type'] === 'material') {
                $bahanBaku = BahanBaku::find($item['item_id']);
                if ($bahanBaku) {
                    $item['nama_item'] = $bahanBaku->nama_bahan;
                    $item['satuan'] = optional($bahanBaku->satuanRelation)->nama ?? 'KG';
                    $item['harga_satuan'] = $bahanBaku->harga_satuan ?? 0;
                }
            } elseif ($item['item_type'] === 'support') {
                $bahanPendukung = BahanPendukung::find($item['item_id']);
                if ($bahanPendukung) {
                    $item['nama_item'] = $bahanPendukung->nama_bahan;
                    $item['satuan'] = optional($bahanPendukung->satuanRelation)->nama ?? 'LITER';
                    $item['harga_satuan'] = $bahanPendukung->harga_satuan ?? 0;
                }
            }
            
            // Sort movements by tanggal
            usort($item['movements'], function($a, $b) {
                return strtotime($a['tanggal']) <=> strtotime($b['tanggal']);
            });
        }
        
        // Convert to collection and sort by nama item
        $laporanStok = collect($processedItems)->sortBy('nama_item');
        
        return view('pegawai-gudang.laporan-stok.index', compact('laporanStok', 'tipe', 'dariTanggal', 'sampaiTanggal'));
    }
    
    public function detail(Request $request)
    {
        $itemType = $request->get('item_type');
        $itemId = $request->get('item_id');
        $dariTanggal = $request->get('dari_tanggal');
        $sampaiTanggal = $request->get('sampai_tanggal');
        
        // Konversi format tanggal jika masih dd/mm/yyyy
        if ($dariTanggal) {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dariTanggal)) {
                $parts = explode('/', $dariTanggal);
                $dariTanggal = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }
        
        if ($sampaiTanggal) {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $sampaiTanggal)) {
                $parts = explode('/', $sampaiTanggal);
                $sampaiTanggal = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }
        
        // Default ke 30 hari ke belakang jika tidak ada filter tanggal
        if (!$dariTanggal) {
            $dariTanggal = now()->subDays(30)->format('Y-m-d');
        }
        if (!$sampaiTanggal) {
            $sampaiTanggal = now()->format('Y-m-d');
        }
        
        // Get stock movements untuk item tertentu
        $stockMovements = StockMovement::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
            ->orderBy('tanggal', 'asc')
            ->get();
        
        // Get item details
        if ($itemType === 'product') {
            $item = Produk::find($itemId);
        } elseif ($itemType === 'material') {
            $item = BahanBaku::find($itemId);
        } elseif ($itemType === 'support') {
            $item = BahanPendukung::find($itemId);
        }
        
        // Calculate stock summary
        $masukQty = 0;
        $masukNilai = 0;
        $keluarQty = 0;
        $keluarNilai = 0;
        $saldoQty = 0;
        $saldoNilai = 0;
        
        foreach ($stockMovements as $movement) {
            if ($movement->direction === 'in') {
                $masukQty += $movement->qty;
                $masukNilai += ($movement->unit_cost ?? 0) * $movement->qty;
            } else {
                $keluarQty += $movement->qty;
                $keluarNilai += ($movement->unit_cost ?? 0) * $movement->qty;
            }
        }
        
        $saldoQty = $masukQty - $keluarQty;
        $saldoNilai = $masukNilai - $keluarNilai;
        
        return view('pegawai-gudang.laporan-stok.detail', compact(
            'item', 
            'stockMovements', 
            'masukQty', 
            'masukNilai', 
            'keluarQty', 
            'keluarNilai', 
            'saldoQty', 
            'saldoNilai',
            'tipe',
            'dariTanggal',
            'sampaiTanggal'
        ));
    }
}

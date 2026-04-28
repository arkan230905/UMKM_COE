<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Retur;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\ReturDetail;
use App\Services\StockService;
use App\Services\JournalService;
use App\Models\StockLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturController extends Controller
{
    public function index()
    {
        $returs = Retur::with(['details.produk'])->orderBy('id','desc')->get();
        return view('transaksi.retur.index', compact('returs'));
    }

    public function create()
    {
        $produks = Produk::all();
        $bahanBakus = \App\Models\BahanBaku::all();
        $pembelians = Pembelian::all();
        $penjualans = Penjualan::all();
        return view('transaksi.retur.create', compact('produks', 'bahanBakus', 'pembelians','penjualans'));
    }

    /**
     * Helper method untuk mendapatkan data retur dengan query yang konsisten
     */
    private function getRetursData()
    {
        // Clear model cache dan reconnect database untuk data fresh
        \App\Models\PurchaseReturn::clearBootedModels();
        \DB::reconnect();
        
        $returs = \App\Models\PurchaseReturn::with([
                'pembelian.vendor', 
                'items.bahanBaku', 
                'items.bahanPendukung'
            ])
            ->withSum('items as calculated_total', 'subtotal')
            ->oldest('created_at') // Changed from latest to oldest
            ->get();
            
        // Handle new retur session - pastikan retur baru ada di collection
        if (session('new_retur_created') && session('new_retur_id')) {
            $newReturId = session('new_retur_id');
            $newReturExists = $returs->where('id', $newReturId)->first();
            
            if (!$newReturExists) {
                $newRetur = \App\Models\PurchaseReturn::with([
                    'pembelian.vendor', 
                    'items.bahanBaku', 
                    'items.bahanPendukung'
                ])->withSum('items as calculated_total', 'subtotal')
                ->find($newReturId);
                
                if ($newRetur) {
                    $returs = $returs->prepend($newRetur);
                    \Log::info('Added missing new retur to collection:', ['retur_id' => $newReturId]);
                }
            }
        }
        
        \Log::info('Returs data loaded:', [
            'count' => $returs->count(),
            'session_new_retur' => session('new_retur_created'),
            'session_new_retur_id' => session('new_retur_id'),
            'new_retur_in_collection' => session('new_retur_id') ? 
                $returs->where('id', session('new_retur_id'))->count() > 0 : false
        ]);
        
        return $returs;
    }

    /**
     * Public method untuk digunakan oleh PembelianController
     */
    public function getRetursDataForPembelian()
    {
        return $this->getRetursData();
    }

    public function indexPembelian()
    {
        $returs = $this->getRetursData();
        return view('transaksi.retur-pembelian.index', compact('returs'));
    }

    public function indexPenjualan()
    {
        return redirect()->route('transaksi.penjualan.index');
    }

    public function createPenjualan(Request $request)
    {
        return redirect()->route('transaksi.penjualan.index');
    }

    public function createPembelian(Request $request)
    {
        \Log::info('Retur create form accessed:', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email ?? 'not authenticated',
            'pembelian_id' => $request->query('pembelian_id'),
            'session_id' => session()->getId(),
        ]);
        
        $pembelianId = $request->query('pembelian_id');
        
        if (!$pembelianId) {
            return redirect('/transaksi/pembelian?tab=retur')
                ->with('error', 'Silakan pilih pembelian yang ingin diretur dari daftar pembelian.');
        }
        
        $pembelian = Pembelian::with(['details.bahanBaku', 'details.bahanPendukung', 'vendor'])->findOrFail($pembelianId);
        
        \Log::info('Pembelian data loaded for retur:', [
            'pembelian_id' => $pembelian->id,
            'nomor_pembelian' => $pembelian->nomor_pembelian,
            'details_count' => $pembelian->details->count(),
        ]);
        
        return view('transaksi.retur-pembelian.create', compact('pembelian'));
    }

    public function storePenjualan(Request $request, StockService $stock, JournalService $journal)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'tanggal' => 'required|date',
            'alasan' => 'required|string',
            'kompensasi' => 'required|in:refund,credit,replace',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.produk_id' => 'required|exists:produks,id',
        ]);

        $items = collect($request->items)->filter(function($item) {
            return isset($item['qty']) && isset($item['selected']) && (float)$item['qty'] > 0;
        });

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.']);
        }

        $tanggalRetur = $request->tanggal;

        $penjualan = Penjualan::with(['details'])->findOrFail($request->penjualan_id);
        
        DB::transaction(function() use ($request, $penjualan, $items, $stock, $journal, $tanggalRetur) {
            $retur = Retur::create([
                'type' => 'sale',
                'ref_id' => $penjualan->id,
                'tanggal' => $tanggalRetur,
                'kompensasi' => $request->kompensasi,
                'status' => 'approved',
                'alasan' => $request->alasan,
                'memo' => $request->catatan,
            ]);

            $totalNominal = 0;
            $totalHpp = 0;

            foreach ($items as $itemData) {
                $detail = $penjualan->details()->where('produk_id', $itemData['produk_id'])->first();
                
                if (!$detail) continue;

                $qty = (float)$itemData['qty'];
                $actualHPP = $detail->produk->getHPPForSaleDate($penjualan->tanggal);
                $margin = ($detail->harga_satuan - $actualHPP) * $qty;
                $subtotal = $qty * $detail->harga_satuan;

                ReturDetail::create([
                    'retur_id' => $retur->id,
                    'produk_id' => $itemData['produk_id'],
                    'qty' => $qty,
                    'harga_satuan_asal' => $detail->harga_satuan,
                    'hpp_asal' => $actualHPP,
                    'margin' => $margin,
                    'subtotal' => $subtotal,
                ]);

                $stock->addLayer('product', (int)$itemData['produk_id'], $qty, 'pcs', $actualHPP, 'sale_return', (int)$retur->id, $tanggalRetur);

                $totalNominal += $subtotal;
                $totalHpp += $actualHPP * $qty;
            }

            $cashOrReceivable = $request->kompensasi === 'credit' ? '1102' : '1101';
            if ($totalNominal > 0) {
                $journal->post($tanggalRetur, 'sale_return', (int)$retur->id, 'Retur Penjualan', [
                    ['code' => '41', 'debit' => (float)$totalNominal, 'credit' => 0],
                    ['code' => $cashOrReceivable, 'debit' => 0, 'credit' => (float)$totalNominal],
                ]);
            }
            if ($totalHpp > 0) {
                $journal->post($tanggalRetur, 'sale_return_cogs', (int)$retur->id, 'Retur Penjualan - Pembalik HPP', [
                    ['code' => '1107', 'debit' => (float)$totalHpp, 'credit' => 0],
                    ['code' => '5001', 'debit' => 0, 'credit' => (float)$totalHpp],
                ]);
            }
        });

        return redirect()->route('transaksi.retur-penjualan.index')->with('success', 'Retur penjualan berhasil dibuat dan diposting.');
    }

    public function storePembelian(Request $request, StockService $stock, JournalService $journal)
    {
        \Log::info('=== RETUR FORM SUBMISSION START ===', [
            'request_data' => $request->all(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
            'method' => $request->method(),
            'url' => $request->url()
        ]);

        try {
            // Enhanced validation with better error messages
            $validatedData = $request->validate([
                'pembelian_id' => 'required|exists:pembelians,id',
                'alasan' => 'required|string|min:3|max:500',
                'jenis_retur' => 'required|in:tukar_barang,refund',
                'memo' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.pembelian_detail_id' => 'required|exists:pembelian_details,id',
                'items.*.qty' => 'required|numeric|min:0.01|max:999999',
                'items.*.satuan' => 'nullable|string|max:50',
                'items.*.harga_satuan' => 'nullable|numeric|min:0',
            ], [
                'pembelian_id.required' => 'ID Pembelian harus diisi',
                'pembelian_id.exists' => 'Data pembelian tidak ditemukan',
                'alasan.required' => 'Alasan retur harus diisi',
                'alasan.min' => 'Alasan retur minimal 3 karakter',
                'alasan.max' => 'Alasan retur maksimal 500 karakter',
                'jenis_retur.required' => 'Jenis retur harus dipilih',
                'jenis_retur.in' => 'Jenis retur tidak valid',
                'items.required' => 'Item retur harus diisi',
                'items.min' => 'Minimal harus ada 1 item retur',
                'items.*.pembelian_detail_id.required' => 'ID detail pembelian harus diisi',
                'items.*.pembelian_detail_id.exists' => 'Detail pembelian tidak ditemukan',
                'items.*.qty.required' => 'Quantity retur harus diisi',
                'items.*.qty.min' => 'Quantity retur minimal 0.01',
                'items.*.qty.max' => 'Quantity retur terlalu besar',
            ]);
            
            \Log::info('Validation passed', ['validated_data' => $validatedData]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Terjadi kesalahan validasi. Silakan periksa form Anda.');
        }

        // Load pembelian with all related data
        $pembelian = Pembelian::with([
            'details.bahanBaku', 
            'details.bahanPendukung',
            'vendor'
        ])->findOrFail($request->pembelian_id);
        
        \Log::info('Pembelian loaded', [
            'pembelian_id' => $pembelian->id,
            'vendor' => $pembelian->vendor->nama ?? 'Unknown',
            'details_count' => $pembelian->details->count()
        ]);
        
        // Filter and validate items
        $items = collect($request->items)->filter(function($item) {
            return isset($item['qty']) && (float)$item['qty'] > 0;
        });

        \Log::info('Items filtered', [
            'original_count' => count($request->items),
            'filtered_count' => $items->count(),
            'items' => $items->toArray()
        ]);

        if ($items->isEmpty()) {
            \Log::error('No valid items found');
            return redirect()->back()
                ->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.'])
                ->withInput();
        }

        // Comprehensive validation for each item
        $validationErrors = [];
        foreach ($items as $index => $itemData) {
            $detail = $pembelian->details()->where('id', $itemData['pembelian_detail_id'])->first();
            
            if (!$detail) {
                $validationErrors[] = "Detail pembelian tidak ditemukan untuk item #" . ($index + 1);
                continue;
            }

            $qtyRetur = (float)$itemData['qty'];
            
            // Check if qty retur exceeds purchased qty
            if ($qtyRetur > $detail->jumlah) {
                $itemName = $this->getItemName($detail);
                $validationErrors[] = "Qty retur ({$qtyRetur}) tidak boleh melebihi qty pembelian ({$detail->jumlah}) untuk item: {$itemName}";
                continue;
            }

            // For tukar_barang, check current stock availability
            if ($request->jenis_retur === 'tukar_barang') {
                $stockInfo = $this->getStockInfo($detail);
                $currentStock = $stock->getCurrentStock($stockInfo['item_id'], $stockInfo['item_type']);
                
                if ($currentStock < $qtyRetur) {
                    $itemName = $this->getItemName($detail);
                    $validationErrors[] = "Stok tidak mencukupi untuk retur tukar barang. Item: {$itemName}, Stok saat ini: {$currentStock}, Qty retur: {$qtyRetur}";
                }
            }
        }

        if (!empty($validationErrors)) {
            \Log::error('Item validation failed', ['errors' => $validationErrors]);
            return redirect()->back()
                ->withErrors(['items' => implode(' | ', $validationErrors)])
                ->withInput();
        }

        \Log::info('All validations passed, starting transaction');

        // Start database transaction
        DB::beginTransaction();
        
        try {
            // Generate return number
            $returnNumber = $this->generateReturnNumber();
            
            // Create PurchaseReturn record
            $purchaseReturn = \App\Models\PurchaseReturn::create([
                'return_number' => $returnNumber,
                'pembelian_id' => $pembelian->id,
                'return_date' => now()->format('Y-m-d'),
                'reason' => $request->alasan,
                'jenis_retur' => $request->jenis_retur,
                'notes' => $request->memo,
                'status' => \App\Models\PurchaseReturn::STATUS_PENDING,
                'total_return_amount' => 0, // Will be calculated below
            ]);

            \Log::info('PurchaseReturn created', [
                'id' => $purchaseReturn->id,
                'return_number' => $purchaseReturn->return_number,
                'jenis_retur' => $purchaseReturn->jenis_retur
            ]);

            $totalReturnAmount = 0;

            // Process each retur item
            foreach ($items as $itemData) {
                $detail = $pembelian->details()->where('id', $itemData['pembelian_detail_id'])->first();
                
                if (!$detail) continue;

                $qty = (float)$itemData['qty'];
                $unitPrice = (float)($detail->harga_satuan ?? 0);
                $subtotal = $qty * $unitPrice;

                // Get item information
                $stockInfo = $this->getStockInfo($detail);
                
                // Create PurchaseReturnItem
                $returnItem = \App\Models\PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'pembelian_detail_id' => $detail->id,
                    'bahan_baku_id' => $stockInfo['item_type'] === 'bahan_baku' ? $stockInfo['item_id'] : null,
                    'bahan_pendukung_id' => $stockInfo['item_type'] === 'bahan_pendukung' ? $stockInfo['item_id'] : null,
                    'unit' => $itemData['satuan'] ?? $detail->satuan_nama ?? 'kg',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                \Log::info('PurchaseReturnItem created', [
                    'item_id' => $returnItem->id,
                    'item_type' => $stockInfo['item_type'],
                    'item_name' => $this->getItemName($detail),
                    'quantity' => $qty,
                    'subtotal' => $subtotal
                ]);

                $totalReturnAmount += $subtotal;
                
                // Handle stock movements based on jenis_retur
                $this->handleStockMovement(
                    $purchaseReturn, 
                    $detail, 
                    $stockInfo, 
                    $qty, 
                    $request->jenis_retur,
                    $stock
                );
            }

            // Update total return amount
            $purchaseReturn->update(['total_return_amount' => $totalReturnAmount]);

            \Log::info('Total amount updated', [
                'return_id' => $purchaseReturn->id,
                'total_amount' => $totalReturnAmount
            ]);

            // Create journal entries if needed
            $this->createJournalEntries($purchaseReturn, $journal);

            DB::commit();

            \Log::info('=== RETUR SUCCESSFULLY SAVED ===', [
                'return_id' => $purchaseReturn->id,
                'return_number' => $purchaseReturn->return_number,
                'jenis_retur' => $purchaseReturn->jenis_retur,
                'total_amount' => $totalReturnAmount,
                'items_count' => $items->count()
            ]);

            // Create success message based on jenis_retur
            $successMessage = $this->getSuccessMessage($request->jenis_retur, $purchaseReturn->return_number);

            return redirect()->route('transaksi.pembelian.index', ['tab' => 'retur'])
                ->with([
                    'success' => $successMessage,
                    'new_retur_created' => true,
                    'new_retur_id' => $purchaseReturn->id
                ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== ERROR SAVING RETUR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memproses retur: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Get item name from pembelian detail
     */
    private function getItemName($detail)
    {
        if ($detail->bahan_baku_id && $detail->bahanBaku) {
            return $detail->bahanBaku->nama_bahan;
        } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
            return $detail->bahanPendukung->nama_bahan;
        }
        return 'Unknown Item';
    }

    /**
     * Get stock information from pembelian detail
     */
    private function getStockInfo($detail)
    {
        if ($detail->bahan_baku_id) {
            return [
                'item_type' => 'bahan_baku',
                'item_id' => $detail->bahan_baku_id,
                'item_name' => $detail->bahanBaku->nama_bahan ?? 'Unknown'
            ];
        } elseif ($detail->bahan_pendukung_id) {
            return [
                'item_type' => 'bahan_pendukung', 
                'item_id' => $detail->bahan_pendukung_id,
                'item_name' => $detail->bahanPendukung->nama_bahan ?? 'Unknown'
            ];
        }
        
        throw new \Exception('Item type tidak dikenali untuk detail ID: ' . $detail->id);
    }

    /**
     * Handle stock movement based on return type
     */
    private function handleStockMovement($purchaseReturn, $detail, $stockInfo, $qty, $jenisRetur, $stock)
    {
        $itemName = $stockInfo['item_name'];
        $returnDate = $purchaseReturn->return_date;
        
        if ($jenisRetur === 'refund') {
            // REFUND: Stock will be reduced when status changes to 'dikirim'
            // Do NOT reduce stock immediately when retur is created
            \Log::info("Stock movement prepared for REFUND", [
                'item_name' => $itemName,
                'item_type' => $stockInfo['item_type'],
                'qty_to_return' => $qty,
                'return_id' => $purchaseReturn->id,
                'note' => 'Stock will be reduced when retur is approved and sent to vendor'
            ]);
            
        } elseif ($jenisRetur === 'tukar_barang') {
            // TUKAR BARANG: Stock will be handled when status changes
            // For now, just log the intention
            \Log::info("Stock movement prepared for TUKAR BARANG", [
                'item_name' => $itemName,
                'item_type' => $stockInfo['item_type'],
                'qty_to_exchange' => $qty,
                'return_id' => $purchaseReturn->id,
                'note' => 'Stock will be reduced when retur is approved and sent'
            ]);
        }
    }

    /**
     * Create stock movement record
     */
    private function createStockMovement($itemId, $itemType, $tanggal, $qtyMasuk, $qtyKeluar, $keterangan, $refType, $refId)
    {
        // Insert ke kartu_stok untuk tracking (legacy)
        DB::table('kartu_stok')->insert([
            'item_id' => $itemId,
            'item_type' => $itemType,
            'tanggal' => $tanggal,
            'qty_masuk' => $qtyMasuk,
            'qty_keluar' => $qtyKeluar,
            'keterangan' => $keterangan,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Insert ke stock_movements untuk real-time stock calculation
        if ($qtyMasuk > 0 || $qtyKeluar > 0) {
            $qty = $qtyMasuk > 0 ? $qtyMasuk : $qtyKeluar;
            $direction = $qtyMasuk > 0 ? 'in' : 'out';
            
            // Map item_type untuk stock_movements
            $stockMovementItemType = match($itemType) {
                'bahan_baku' => 'material',
                'bahan_pendukung' => 'support',
                'produk' => 'product',
                default => $itemType
            };
            
            // Get unit and cost information
            $unit = 'unit';
            $unitCost = 0;
            
            if ($itemType === 'bahan_pendukung') {
                $bahanPendukung = \App\Models\BahanPendukung::find($itemId);
                if ($bahanPendukung) {
                    $unit = $bahanPendukung->satuanRelation->nama ?? 'unit';
                    $unitCost = $bahanPendukung->harga_satuan ?? 0;
                }
            } elseif ($itemType === 'bahan_baku') {
                $bahanBaku = \App\Models\BahanBaku::find($itemId);
                if ($bahanBaku) {
                    $unit = $bahanBaku->satuan->nama ?? 'unit';
                    $unitCost = $bahanBaku->harga_satuan ?? 0;
                }
            }
            
            \App\Models\StockMovement::create([
                'item_type' => $stockMovementItemType,
                'item_id' => $itemId,
                'direction' => $direction,
                'qty' => $qty,
                'satuan' => $unit,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost * $qty,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan
            ]);
            
            \Log::info("Stock movement created", [
                'item_type' => $stockMovementItemType,
                'item_id' => $itemId,
                'direction' => $direction,
                'qty' => $qty,
                'ref_type' => $refType,
                'ref_id' => $refId
            ]);
        }
    }

    /**
     * Update master stock table
     */
    private function updateMasterStock($itemType, $itemId, $qtyChange)
    {
        if ($itemType === 'bahan_baku') {
            DB::table('bahan_bakus')
                ->where('id', $itemId)
                ->increment('stok', $qtyChange);
        } elseif ($itemType === 'bahan_pendukung') {
            DB::table('bahan_pendukungs')
                ->where('id', $itemId)
                ->increment('stok', $qtyChange);
        }
    }

    /**
     * Generate unique return number
     */
    private function generateReturnNumber()
    {
        $prefix = 'RTR';
        $date = now()->format('Ymd');
        
        // Get the highest sequence number from ALL returns (not just today)
        $lastReturn = \App\Models\PurchaseReturn::where('return_number', 'like', 'RTR%')
            ->orderBy('return_number', 'desc')
            ->first();
        
        $nextSequence = 1;
        
        if ($lastReturn && $lastReturn->return_number) {
            // Extract the last 3 digits (sequence number) from the return number
            // Format: RTR20260425003 -> extract "003"
            $lastSequence = (int) substr($lastReturn->return_number, -3);
            $nextSequence = $lastSequence + 1;
        }
        
        return $prefix . $date . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get success message based on return type
     */
    private function getSuccessMessage($jenisRetur, $returnNumber)
    {
        if ($jenisRetur === 'refund') {
            return "Retur Refund #{$returnNumber} berhasil disimpan. Stok telah dikurangi dan menunggu persetujuan vendor untuk pengembalian uang.";
        } else {
            return "Retur Tukar Barang #{$returnNumber} berhasil disimpan. Menunggu persetujuan vendor untuk proses tukar barang.";
        }
    }

    /**
     * Create journal entries for accounting
     */
    private function createJournalEntries($purchaseReturn, $journal)
    {
        // This will be implemented based on your accounting requirements
        // For now, just log the intention
        \Log::info('Journal entries should be created', [
            'return_id' => $purchaseReturn->id,
            'jenis_retur' => $purchaseReturn->jenis_retur,
            'total_amount' => $purchaseReturn->total_return_amount
        ]);
    }

    /**
     * Update status retur dengan logika stock movement
     */
    public function updateStatusRetur(Request $request, $id, StockService $stock)
    {
        $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'items.bahanPendukung', 'pembelian'])->findOrFail($id);
        
        \Log::info("UpdateStatus called for retur ID: {$id}", [
            'current_status' => $retur->status,
            'jenis_retur' => $retur->jenis_retur,
            'requested_status' => $request->input('status')
        ]);
        
        $newStatus = $request->input('status');
        $oldStatus = $retur->status;

        // Validate status transition
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            \Log::error("Invalid status transition", [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'retur_id' => $id
            ]);
            return back()->with('error', 'Transisi status tidak valid.');
        }

        \Log::info("Status transition validated", [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'retur_id' => $id
        ]);

        DB::beginTransaction();
        
        try {
            // Handle stock movements based on status change and return type
            $this->handleStatusChangeStockMovement($retur, $oldStatus, $newStatus, $stock);
            
            // Update status
            $retur->update(['status' => $newStatus]);
            
            \Log::info("Status updated successfully", [
                'retur_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            // Create journal entries if needed
            $this->handleStatusChangeAccounting($retur, $oldStatus, $newStatus);
            
            DB::commit();
            
            $message = $this->getStatusChangeMessage($retur->jenis_retur, $oldStatus, $newStatus, $retur->return_number);
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating retur status', [
                'error' => $e->getMessage(),
                'retur_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Validate status transition
     */
    private function isValidStatusTransition($oldStatus, $newStatus)
    {
        $validTransitions = [
            'pending' => ['disetujui', 'ditolak'],
            'disetujui' => ['dikirim'],
            'dikirim' => ['selesai'],
            'ditolak' => [], // Final status
            'selesai' => [], // Final status
        ];
        
        return in_array($newStatus, $validTransitions[$oldStatus] ?? []);
    }

    /**
     * Handle stock movement when status changes
     */
    private function handleStatusChangeStockMovement($retur, $oldStatus, $newStatus, $stock)
    {
        foreach ($retur->items as $item) {
            $stockInfo = $this->getStockInfoFromItem($item);
            
            if ($retur->jenis_retur === 'tukar_barang') {
                $this->handleTukarBarangStockMovement($retur, $item, $stockInfo, $oldStatus, $newStatus);
            } elseif ($retur->jenis_retur === 'refund') {
                $this->handleRefundStockMovement($retur, $item, $stockInfo, $oldStatus, $newStatus);
            }
        }
    }

    /**
     * Handle stock movement for tukar barang
     */
    private function handleTukarBarangStockMovement($retur, $item, $stockInfo, $oldStatus, $newStatus)
    {
        if ($oldStatus === 'disetujui' && $newStatus === 'dikirim') {
            // Barang dikirim ke vendor - kurangi stock
            $this->createStockMovement(
                $stockInfo['item_id'],
                $stockInfo['item_type'],
                now()->format('Y-m-d'),
                null, // qty_masuk
                $item->quantity, // qty_keluar
                "Retur Tukar Barang - Dikirim #{$retur->return_number} - {$stockInfo['item_name']}",
                'retur_tukar_kirim',
                $retur->id
            );
            
            \Log::info("Stock reduced for TUKAR BARANG - DIKIRIM", [
                'item_name' => $stockInfo['item_name'],
                'qty_reduced' => $item->quantity,
                'return_id' => $retur->id
            ]);
            
        } elseif ($oldStatus === 'dikirim' && $newStatus === 'selesai') {
            // Barang pengganti diterima - tambah stock
            $this->createStockMovement(
                $stockInfo['item_id'],
                $stockInfo['item_type'],
                now()->format('Y-m-d'),
                $item->quantity, // qty_masuk
                null, // qty_keluar
                "Retur Tukar Barang - Barang Pengganti Diterima #{$retur->return_number} - {$stockInfo['item_name']}",
                'retur_tukar_terima',
                $retur->id
            );
            
            \Log::info("Stock increased for TUKAR BARANG - SELESAI", [
                'item_name' => $stockInfo['item_name'],
                'qty_added' => $item->quantity,
                'return_id' => $retur->id
            ]);
        }
    }

    /**
     * Handle stock movement for refund
     */
    private function handleRefundStockMovement($retur, $item, $stockInfo, $oldStatus, $newStatus)
    {
        if ($oldStatus === 'disetujui' && $newStatus === 'dikirim') {
            // Barang dikirim ke vendor untuk refund - kurangi stock
            $this->createStockMovement(
                $stockInfo['item_id'],
                $stockInfo['item_type'],
                now()->format('Y-m-d'),
                null, // qty_masuk
                $item->quantity, // qty_keluar
                "Retur Refund - Dikirim #{$retur->return_number} - {$stockInfo['item_name']}",
                'retur_refund_kirim',
                $retur->id
            );
            
            $this->updateMasterStock($stockInfo['item_type'], $stockInfo['item_id'], -$item->quantity);
            
            \Log::info("Stock reduced for REFUND - DIKIRIM", [
                'item_name' => $stockInfo['item_name'],
                'qty_reduced' => $item->quantity,
                'return_id' => $retur->id
            ]);
            
        } elseif ($newStatus === 'selesai') {
            \Log::info("Refund completed - no additional stock movement needed", [
                'item_name' => $stockInfo['item_name'],
                'qty_refunded' => $item->quantity,
                'return_id' => $retur->id,
                'note' => 'Stock was already reduced when goods were sent'
            ]);
        }
    }

    /**
     * Get stock info from return item
     */
    private function getStockInfoFromItem($item)
    {
        if ($item->bahan_baku_id) {
            return [
                'item_type' => 'bahan_baku',
                'item_id' => $item->bahan_baku_id,
                'item_name' => $item->bahanBaku->nama_bahan ?? 'Unknown'
            ];
        } elseif ($item->bahan_pendukung_id) {
            return [
                'item_type' => 'bahan_pendukung',
                'item_id' => $item->bahan_pendukung_id,
                'item_name' => $item->bahanPendukung->nama_bahan ?? 'Unknown'
            ];
        }
        
        throw new \Exception('Item type tidak dikenali untuk return item ID: ' . $item->id);
    }

    /**
     * Handle accounting entries for status changes
     */
    private function handleStatusChangeAccounting($retur, $oldStatus, $newStatus)
    {
        // Implement accounting logic based on your requirements
        \Log::info('Accounting entries for status change', [
            'return_id' => $retur->id,
            'jenis_retur' => $retur->jenis_retur,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'total_amount' => $retur->total_return_amount
        ]);
    }

    /**
     * Get status change message
     */
    private function getStatusChangeMessage($jenisRetur, $oldStatus, $newStatus, $returnNumber)
    {
        $messages = [
            'tukar_barang' => [
                'pending_to_disetujui' => "Retur Tukar Barang #{$returnNumber} telah disetujui vendor.",
                'disetujui_to_dikirim' => "Barang retur #{$returnNumber} telah dikirim ke vendor. Stok telah dikurangi.",
                'dikirim_to_selesai' => "Barang pengganti untuk retur #{$returnNumber} telah diterima. Stok telah ditambahkan.",
            ],
            'refund' => [
                'pending_to_disetujui' => "Retur Refund #{$returnNumber} telah disetujui vendor.",
                'disetujui_to_dikirim' => "Proses refund #{$returnNumber} sedang diproses vendor.",
                'dikirim_to_selesai' => "Refund #{$returnNumber} telah selesai. Uang telah dikembalikan.",
            ]
        ];
        
        $key = $oldStatus . '_to_' . $newStatus;
        return $messages[$jenisRetur][$key] ?? "Status retur #{$returnNumber} berhasil diubah ke {$newStatus}.";
    }

    /**
     * Quick action methods for easier status updates
     */
    public function approveRetur($id, StockService $stock)
    {
        $request = new Request(['status' => 'disetujui']);
        return $this->updateStatusRetur($request, $id, $stock);
    }

    public function rejectRetur($id)
    {
        $retur = \App\Models\PurchaseReturn::findOrFail($id);
        
        // For refund, we need to reverse the stock reduction
        if ($retur->jenis_retur === 'refund') {
            DB::beginTransaction();
            try {
                foreach ($retur->items as $item) {
                    $stockInfo = $this->getStockInfoFromItem($item);
                    
                    // Reverse stock reduction
                    $this->createStockMovement(
                        $stockInfo['item_id'],
                        $stockInfo['item_type'],
                        now()->format('Y-m-d'),
                        $item->quantity, // qty_masuk (restore stock)
                        null, // qty_keluar
                        "Retur Refund Ditolak - Restore Stock #{$retur->return_number} - {$stockInfo['item_name']}",
                        'retur_refund_rejected',
                        $retur->id
                    );
                    
                    $this->updateMasterStock($stockInfo['item_type'], $stockInfo['item_id'], $item->quantity);
                }
                
                $retur->update(['status' => 'ditolak']);
                DB::commit();
                
                return back()->with('success', "Retur #{$retur->return_number} ditolak. Stok telah dikembalikan.");
                
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Terjadi kesalahan saat menolak retur: ' . $e->getMessage());
            }
        } else {
            // For tukar_barang, no stock movement needed when rejected
            $retur->update(['status' => 'ditolak']);
            return back()->with('success', "Retur #{$retur->return_number} ditolak.");
        }
    }

    public function sendRetur($id, StockService $stock)
    {
        \Log::info("SendRetur called for ID: {$id}");
        
        try {
            $retur = \App\Models\PurchaseReturn::findOrFail($id);
            \Log::info("Retur found", ['current_status' => $retur->status, 'jenis_retur' => $retur->jenis_retur]);
            
            // Validate current status
            if ($retur->status !== 'disetujui') {
                $message = "Status retur harus 'Disetujui' untuk bisa dikirim. Status saat ini: " . $retur->status;
                \Log::warning($message, ['retur_id' => $id]);
                
                if (request()->wantsJson()) {
                    return response()->json(['message' => $message], 400);
                }
                return back()->with('error', $message);
            }
            
            $request = new Request(['status' => 'dikirim']);
            $result = $this->updateStatusRetur($request, $id, $stock);
            
            \Log::info("SendRetur completed successfully for ID: {$id}");
            
            // Handle JSON response for AJAX requests
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status retur berhasil diubah ke Dikirim',
                    'status' => 'dikirim'
                ]);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error("SendRetur failed for ID: {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Gagal mengirim retur: ' . $e->getMessage();
            
            if (request()->wantsJson()) {
                return response()->json(['message' => $errorMessage], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    public function completeRetur($id, StockService $stock)
    {
        $request = new Request(['status' => 'selesai']);
        return $this->updateStatusRetur($request, $id, $stock);
    }

    /**
     * Show detail retur pembelian
     */
    public function showPembelian($id)
    {
        $retur = \App\Models\PurchaseReturn::with([
            'pembelian.vendor',
            'items.bahanBaku',
            'items.bahanPendukung',
            'items.pembelianDetail'
        ])->findOrFail($id);
        
        return view('transaksi.retur-pembelian.show', compact('retur'));
    }

    public function post($id, StockService $stock, JournalService $journal)
    {
        $retur = Retur::with(['details.produk'])->findOrFail($id);
        if ($retur->status === 'posted') {
            return back()->with('error', 'Retur sudah diposting.');
        }

        $tanggal = $retur->tanggal;
        $totalNominal = 0;
        $totalHpp = 0;

        DB::transaction(function() use ($retur, $stock, $journal, $tanggal, &$totalNominal, &$totalHpp) {
            foreach ($retur->details as $d) {
                $qty = $d->qty;
                $avg = $d->hpp_asal;
                $prod = $d->produk;

                if ($retur->type === 'sale') {
                    $stock->addLayer('product', (int)$prod->id, $qty, 'pcs', (float)$avg, 'sale_return', (int)$retur->id, $tanggal);
                    $lineNominal = (float)($d->harga_satuan_asal ?? 0) * $qty;
                    $lineHpp = $avg * $qty;
                } else {
                    $stock->consume('material', (int)$d->bahan_baku_id, $qty, 'kg', 'purchase_return', (int)$retur->id, $tanggal);
                    $lineNominal = (float)($d->harga_satuan_asal ?? 0) * $qty;
                    $lineHpp = $avg * $qty;
                }

                $totalNominal += $lineNominal;
                $totalHpp += $lineHpp;
            }

            if ($retur->type === 'sale') {
                $cashOrReceivable = $retur->kompensasi === 'credit' ? '1102' : '1101';
                if ($totalNominal > 0) {
                    $journal->post($tanggal, 'sale_return', (int)$retur->id, 'Retur Penjualan', [
                        ['code' => '41', 'debit' => (float)$totalNominal, 'credit' => 0],
                        ['code' => $cashOrReceivable, 'debit' => 0, 'credit' => (float)$totalNominal],
                    ]);
                }
                if ($totalHpp > 0) {
                    $journal->post($tanggal, 'sale_return_cogs', (int)$retur->id, 'Retur Penjualan - Pembalik HPP', [
                        ['code' => '1107', 'debit' => (float)$totalHpp, 'credit' => 0],
                        ['code' => '5001', 'debit' => 0, 'credit' => (float)$totalHpp],
                    ]);
                }
            }
        });

        $retur->update(['status' => 'posted']);
        return back()->with('success', 'Retur berhasil diposting.');
    }

    public function showPenjualan($id)
    {
        return redirect()->route('transaksi.penjualan.index');
    }

    public function destroyPembelian($id)
    {
        // Use PurchaseReturn model instead of Retur
        $retur = \App\Models\PurchaseReturn::findOrFail($id);
        $retur->delete();
        return redirect('/transaksi/pembelian?tab=retur')->with('success', 'Data retur pembelian berhasil dihapus.');
    }

    public function destroyPenjualan($id)
    {
        $retur = Retur::findOrFail($id);
        if ($retur->type !== 'sale') {
            return back()->with('error', 'Ini bukan retur penjualan.');
        }
        $retur->delete();
        return redirect()->route('transaksi.retur-penjualan.index')->with('success', 'Data retur penjualan berhasil dihapus.');
    }

    public function proses($id)
    {
        try {
            DB::beginTransaction();
            
            $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'items.bahanPendukung', 'pembelian.details'])->findOrFail($id);
            
            // Pastikan belum diproses
            if ($retur->status !== 'pending') {
                return back()->with('error', 'Hanya retur dengan status pending yang dapat diproses.');
            }
            
            foreach ($retur->items as $item) {
                // Handle both bahan baku and bahan pendukung
                $material = null;
                $materialName = '';
                $materialType = '';
                
                if ($item->bahan_baku_id && $item->bahanBaku) {
                    $material = $item->bahanBaku;
                    $materialName = $material->nama_bahan;
                    $materialType = 'bahan_baku';
                } elseif ($item->bahan_pendukung_id && $item->bahanPendukung) {
                    $material = $item->bahanPendukung;
                    $materialName = $material->nama_bahan;
                    $materialType = 'bahan_pendukung';
                } else {
                    continue; // Skip if neither bahan baku nor bahan pendukung
                }
                
                // CRITICAL: Use converted quantity (qty_konversi) in base unit, NOT raw quantity
                // Find the original purchase detail to get the conversion factor
                $originalDetail = null;
                if ($retur->pembelian && $retur->pembelian->details) {
                    foreach ($retur->pembelian->details as $detail) {
                        if (($materialType === 'bahan_baku' && $detail->bahan_baku_id == $item->bahan_baku_id) ||
                            ($materialType === 'bahan_pendukung' && $detail->bahan_pendukung_id == $item->bahan_pendukung_id)) {
                            $originalDetail = $detail;
                            break;
                        }
                    }
                }
                
                // Calculate converted quantity (in base unit/satuan utama)
                $qtyRaw = (float) $item->quantity; // Raw input quantity
                $qtyConverted = $qtyRaw; // Default fallback
                
                if ($originalDetail && $originalDetail->faktor_konversi > 0) {
                    // Use the same conversion factor from original purchase
                    $qtyConverted = $qtyRaw * $originalDetail->faktor_konversi;
                    
                    \Log::info("RETUR STOCK UPDATE - Conversion for {$materialType} ID {$material->id}:", [
                        'nama_bahan' => $materialName,
                        'qty_raw' => $qtyRaw,
                        'satuan_pembelian' => $originalDetail->satuan ?? 'unknown',
                        'faktor_konversi' => $originalDetail->faktor_konversi,
                        'qty_converted' => $qtyConverted,
                        'satuan_utama' => $materialType === 'bahan_baku' ? 
                            ($material->satuan->nama ?? 'KG') : 
                            ($material->satuanRelation->nama ?? 'unit')
                    ]);
                } else {
                    // Fallback: assume raw quantity is already in base unit
                    \Log::warning("RETUR STOCK UPDATE - No conversion factor found, using raw quantity:", [
                        'material_type' => $materialType,
                        'material_id' => $material->id,
                        'qty_raw' => $qtyRaw
                    ]);
                }
                
                if ($qtyConverted <= 0) {
                    continue;
                }
                
                // Apply stock changes based on return type using CONVERTED quantities and helper function
                if ($retur->jenis_retur === 'refund') {
                    // REFUND → stok berkurang (barang dikembalikan ke vendor)
                    $updateSuccess = $material->updateStok($qtyConverted, 'out', "Return refund ID: {$retur->id}");
                    
                    if (!$updateSuccess) {
                        DB::rollBack();
                        return back()->with('error', "Stok {$materialName} tidak mencukupi untuk retur. Tersedia: {$material->stok}, Dibutuhkan: {$qtyConverted}");
                    }
                } elseif ($retur->jenis_retur === 'tukar_barang') {
                    // TUKAR BARANG → stok netral (barang dikembalikan lalu diganti barang baru)
                    // First remove old stock
                    $updateSuccess1 = $material->updateStok($qtyConverted, 'out', "Return exchange (old) ID: {$retur->id}");
                    if (!$updateSuccess1) {
                        DB::rollBack();
                        return back()->with('error', "Stok {$materialName} tidak mencukupi untuk retur tukar barang. Tersedia: {$material->stok}, Dibutuhkan: {$qtyConverted}");
                    }
                    
                    // Then add new stock
                    $updateSuccess2 = $material->updateStok($qtyConverted, 'in', "Return exchange (new) ID: {$retur->id}");
                    if (!$updateSuccess2) {
                        DB::rollBack();
                        return back()->with('error', "Gagal menambah stok baru untuk tukar barang {$materialName}");
                    }
                }
            }
            
            // Update status retur
            $retur->status = 'completed';
            $retur->save();
            
            DB::commit();
            
            return back()->with('success', 'Retur berhasil diselesaikan dan stok telah diperbarui menggunakan konversi satuan yang benar.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing return: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses retur: ' . $e->getMessage());
        }
    }

    /**
     * ACC Vendor - Approve return request
     */
    public function acc($id)
    {
        try {
            $retur = \App\Models\PurchaseReturn::findOrFail($id);
            
            if ($retur->status !== \App\Models\PurchaseReturn::STATUS_PENDING) {
                return back()->with('error', 'Status retur tidak valid untuk di-ACC.');
            }
            
            $retur->status = \App\Models\PurchaseReturn::STATUS_DISETUJUI;
            $retur->save();
            
            Log::info("Retur {$retur->id} di-ACC vendor");
            
            return redirect('/transaksi/pembelian?tab=retur')->with('success', 'Retur berhasil di-ACC vendor');
            
        } catch (\Exception $e) {
            Log::error('Error ACC retur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat ACC retur: ' . $e->getMessage());
        }
    }

    /**
     * Kirim Barang - Send goods to vendor (both refund & tukar_barang)
     */
    public function kirim($id)
    {
        try {
            $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku.coaPersediaan', 'items.bahanPendukung.coaPersediaan'])->findOrFail($id);
            
            if ($retur->status !== \App\Models\PurchaseReturn::STATUS_DISETUJUI) {
                return back()->with('error', 'Status retur tidak valid untuk dikirim.');
            }
            
            DB::beginTransaction();
            
            // Validate stock availability before processing (for ALL return types)
            foreach ($retur->items as $item) {
                if ($item->quantity <= 0) continue;
                
                $itemType = $item->bahan_baku_id ? 'bahan_baku' : 'bahan_pendukung';
                $itemId = $item->bahan_baku_id ?: $item->bahan_pendukung_id;
                
                if ($itemType && $itemId) {
                    // Check current stock
                    $currentStock = \App\Models\KartuStok::getStockBalance($itemId, $itemType);
                    
                    if ($currentStock < $item->quantity) {
                        $itemName = $item->bahanBaku->nama_bahan ?? $item->bahanPendukung->nama_bahan ?? 'Unknown';
                        throw new \Exception("Stok tidak mencukupi untuk {$itemName}. Stok saat ini: {$currentStock}, Qty retur: {$item->quantity}");
                    }
                }
            }
            
            // Store old status for stock movement handling
            $oldStatus = $retur->status;
            
            // Update status
            $retur->status = \App\Models\PurchaseReturn::STATUS_DIKIRIM;
            $retur->save();
            
            // Handle stock movement through the proper method
            // This will create kartu_stok entries and update master stock
            $stock = app(\App\Services\StockService::class);
            $this->handleStatusChangeStockMovement($retur, $oldStatus, $retur->status, $stock);
            
            DB::commit();
            
            $jenisText = $retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_REFUND ? 'refund' : 'tukar barang';
            Log::info("Retur {$jenisText} {$retur->id} barang dikirim ke vendor - stock movements recorded");
            
            return redirect('/transaksi/pembelian?tab=retur')->with('success', "Barang {$jenisText} berhasil dikirim ke vendor");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error kirim barang retur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat kirim barang: ' . $e->getMessage());
        }
    }

    /**
     * Terima Barang - Receive replacement goods (tukar_barang only)
     */
    public function terimaBarang($id)
    {
        try {
            $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku.coaPersediaan', 'items.bahanPendukung.coaPersediaan'])->findOrFail($id);
            
            if ($retur->jenis_retur !== \App\Models\PurchaseReturn::JENIS_TUKAR_BARANG) {
                return back()->with('error', 'Fungsi ini hanya untuk retur tukar barang.');
            }
            
            if ($retur->status !== \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
                return back()->with('error', 'Status retur tidak valid untuk terima barang.');
            }
            
            DB::beginTransaction();
            
            try {
                // Update status
                $retur->status = \App\Models\PurchaseReturn::STATUS_SELESAI;
                $retur->save();
                
                // Process stock addition for replacement goods - using exact quantities from retur data
                foreach ($retur->items as $item) {
                    if ($item->quantity <= 0) continue;
                    
                    $itemType = null;
                    $itemId = null;
                    $itemName = 'Unknown';
                    
                    if ($item->bahan_baku_id) {
                        $itemType = 'bahan_baku';
                        $itemId = $item->bahan_baku_id;
                        $itemName = $item->bahanBaku->nama_bahan ?? 'Unknown';
                    } elseif ($item->bahan_pendukung_id) {
                        $itemType = 'bahan_pendukung';
                        $itemId = $item->bahan_pendukung_id;
                        $itemName = $item->bahanPendukung->nama_bahan ?? 'Unknown';
                    }
                    
                    if ($itemType && $itemId) {
                        // Use exact quantity from purchase return item
                        $exactQuantity = $item->quantity;
                        
                        // Create kartu_stok entry (stock in) - using exact quantity from purchase return
                        DB::table('kartu_stok')->insert([
                            'item_id' => $itemId,
                            'item_type' => $itemType,
                            'tanggal' => now()->format('Y-m-d'),
                            'qty_masuk' => $exactQuantity, // Use exact quantity from purchase return
                            'qty_keluar' => null,
                            'keterangan' => "Retur Pembelian - Barang Pengganti #{$retur->return_number}",
                            'ref_type' => 'retur',
                            'ref_id' => $retur->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        // Update bahan_baku stock if applicable - using exact quantity
                        if ($itemType === 'bahan_baku') {
                            DB::table('bahan_bakus')
                                ->where('id', $itemId)
                                ->increment('stok', $exactQuantity);
                        }
                        
                        // Update bahan_pendukung stock if applicable - using exact quantity
                        if ($itemType === 'bahan_pendukung') {
                            DB::table('bahan_pendukungs')
                                ->where('id', $itemId)
                                ->increment('stok', $exactQuantity);
                        }
                        
                        Log::info("Stock added for replacement {$itemName}: +{$exactQuantity} (Return ID: {$retur->id}, Item ID: {$itemId})");
                    }
                }
                
                // Create journal entry for replacement goods received
                $journal = app(\App\Services\JournalService::class);
                $this->createReplacementGoodsJournal($retur, $journal);
                
                DB::commit();
                
                Log::info("Retur {$retur->id} barang pengganti diterima - all stock movements recorded with exact quantities and journal created");
                
                return redirect('/transaksi/pembelian?tab=retur')->with('success', 'Barang pengganti berhasil diterima dan jurnal telah dibuat');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error terima barang retur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat terima barang: ' . $e->getMessage());
        }
    }

    /**
     * Terima Refund - Receive refund money (refund only)
     */
    public function terimaRefund($id)
    {
        try {
            $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku.coaPersediaan', 'items.bahanPendukung.coaPersediaan', 'pembelian.kasBank'])->findOrFail($id);
            
            if ($retur->jenis_retur !== \App\Models\PurchaseReturn::JENIS_REFUND) {
                return back()->with('error', 'Fungsi ini hanya untuk retur refund.');
            }
            
            if ($retur->status !== \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
                return back()->with('error', 'Status retur tidak valid untuk terima refund.');
            }
            
            DB::beginTransaction();
            
            try {
                // Update status
                $retur->status = \App\Models\PurchaseReturn::STATUS_SELESAI;
                $retur->save();
                
                // Create journal entry for refund received
                $journal = app(\App\Services\JournalService::class);
                $this->createRefundReceivedJournal($retur, $journal);
                
                DB::commit();
                
                Log::info("Retur REFUND {$retur->id} completed with journal entry created");
                
                return redirect('/transaksi/pembelian?tab=retur')->with('success', 'Refund berhasil diterima dan jurnal telah dibuat');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error terima refund: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat terima refund: ' . $e->getMessage());
        }
    }
}
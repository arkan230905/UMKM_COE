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

    public function indexPembelian()
    {
        // FIXED: Query with eager loading and sum calculation for better performance
        $returs = \App\Models\PurchaseReturn::with(['pembelian', 'items'])
            ->withSum('items as calculated_total', 'subtotal')
            ->latest()
            ->get();
        
        \Log::info('Retur pembelian index - data count: ' . $returs->count());
        
        return view('transaksi.retur-pembelian.index', compact('returs'));
    }

    public function indexPenjualan()
    {
        $returs = Retur::where('type', 'sale')
            ->with(['details.produk', 'penjualan'])
            ->orderBy('id', 'desc')
            ->get();
        
        return view('transaksi.retur-penjualan.index', compact('returs'));
    }

    public function createPenjualan(Request $request)
    {
        $penjualanId = $request->query('penjualan_id');
        
        if (!$penjualanId) {
            return redirect()->route('transaksi.penjualan.index')
                ->with('error', 'Silakan pilih penjualan yang ingin diretur dari daftar penjualan.');
        }
        
        $penjualan = Penjualan::with(['details.produk'])->findOrFail($penjualanId);
        
        return view('transaksi.retur-penjualan.create', compact('penjualan'));
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
        \Log::info('Retur form submission received');

        // Validation
        $request->validate([
            'pembelian_id' => 'required|exists:pembelians,id',
            'alasan' => 'required|string',
            'jenis_retur' => 'required|in:tukar_barang,refund',
            'items' => 'required|array|min:1',
            'items.*.pembelian_detail_id' => 'required|exists:pembelian_details,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        $tanggalRetur = date('Y-m-d');
        $pembelian = Pembelian::with('details')->findOrFail($request->pembelian_id);
        
        $items = collect($request->items)->filter(function($item) {
            return isset($item['qty']) && (float)$item['qty'] > 0;
        });

        if ($items->isEmpty()) {
            return redirect('/transaksi/pembelian?tab=retur')->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.']);
        }

        // Validate return quantities don't exceed purchased quantities
        foreach ($items as $itemData) {
            $detail = $pembelian->details()->where('id', $itemData['pembelian_detail_id'])->first();
            if (!$detail) {
                return redirect('/transaksi/pembelian?tab=retur')->withErrors(['items' => 'Detail pembelian tidak ditemukan.']);
            }

            $qtyRetur = (float)$itemData['qty'];
            if ($qtyRetur > $detail->jumlah) {
                $itemName = $detail->bahanBaku->nama_bahan ?? $detail->bahanPendukung->nama_bahan ?? 'Unknown';
                return redirect('/transaksi/pembelian?tab=retur')->withErrors(['items' => "Qty retur tidak boleh melebihi qty pembelian untuk item {$itemName}"]);
            }

            // Check current stock availability
            $itemType = $detail->bahan_baku_id ? 'bahan_baku' : 'bahan_pendukung';
            $itemId = $detail->bahan_baku_id ?: $detail->bahan_pendukung_id;
            
            $currentStock = $stock->getCurrentStock($itemId, $itemType);
            if ($currentStock < $qtyRetur) {
                return redirect('/transaksi/pembelian?tab=retur')->withErrors(['items' => "Stok tidak mencukupi untuk retur. Stok saat ini: {$currentStock}, Qty retur: {$qtyRetur}"]);
            }
        }

        // Use proper transaction handling
        DB::beginTransaction();
        
        try {
            // Normalize jenis_retur value
            $jenisRetur = strtolower($request->jenis_retur);
            if (str_contains($jenisRetur, 'refund') || $jenisRetur === 'refund') {
                $jenisRetur = 'refund';
            } else {
                $jenisRetur = 'tukar_barang';
            }

            // Create PurchaseReturn record
            $purchaseReturn = \App\Models\PurchaseReturn::create([
                'pembelian_id' => $pembelian->id,
                'return_date' => $tanggalRetur,
                'reason' => $request->alasan,
                'jenis_retur' => $jenisRetur, // Use normalized value
                'notes' => $request->memo,
                'status' => \App\Models\PurchaseReturn::STATUS_PENDING, // Use constant
            ]);

            $totalNominal = 0;

            // Create retur items
            foreach ($items as $itemData) {
                $detail = $pembelian->details()->where('id', $itemData['pembelian_detail_id'])->first();
                
                if (!$detail) continue;

                $qty = (float)$itemData['qty'];
                $unitPrice = (float)($detail->harga_satuan ?? 0);
                $subtotal = $qty * $unitPrice;

                // Determine material type
                $isBahanBaku = !empty($detail->bahan_baku_id);
                $isBahanPendukung = !empty($detail->bahan_pendukung_id);
                
                // Create PurchaseReturnItem
                \App\Models\PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'pembelian_detail_id' => $detail->id,
                    'bahan_baku_id' => $isBahanBaku ? $detail->bahan_baku_id : null,
                    'bahan_pendukung_id' => $isBahanPendukung ? $detail->bahan_pendukung_id : null,
                    'unit' => $itemData['satuan'] ?? $detail->satuan_nama ?? 'kg',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $totalNominal += $subtotal;
                
                // IMMEDIATE STOCK REDUCTION FOR REFUND
                if ($jenisRetur === 'refund') {
                    $itemType = $isBahanBaku ? 'bahan_baku' : 'bahan_pendukung';
                    $itemId = $isBahanBaku ? $detail->bahan_baku_id : $detail->bahan_pendukung_id;
                    $itemName = $detail->bahanBaku->nama_bahan ?? $detail->bahanPendukung->nama_bahan ?? 'Unknown';
                    
                    // Create kartu_stok entry (stock out) - IMMEDIATE for refund
                    DB::table('kartu_stok')->insert([
                        'item_id' => $itemId,
                        'item_type' => $itemType,
                        'tanggal' => $tanggalRetur,
                        'qty_masuk' => null,
                        'qty_keluar' => $qty,
                        'keterangan' => "Retur Pembelian - Refund #{$purchaseReturn->return_number}",
                        'ref_type' => 'retur',
                        'ref_id' => $purchaseReturn->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Update bahan_baku stock immediately for refund
                    if ($itemType === 'bahan_baku') {
                        DB::table('bahan_bakus')
                            ->where('id', $itemId)
                            ->decrement('stok', $qty);
                    }
                    
                    Log::info("REFUND - Immediate stock reduction for {$itemName}: -{$qty} (Return ID: {$purchaseReturn->id})");
                }
            }

            // Update total return amount
            $purchaseReturn->total_return_amount = $totalNominal;
            $purchaseReturn->save();

            DB::commit();

            \Log::info('Retur pembelian successfully created', [
                'return_id' => $purchaseReturn->id,
                'jenis_retur' => $jenisRetur,
                'immediate_stock_reduction' => $jenisRetur === 'refund'
            ]);

            // Redirect to pembelian page with retur tab active
            return redirect('/transaksi/pembelian?tab=retur')->with('success', 'Retur berhasil disimpan' . ($jenisRetur === 'refund' ? ' dan stok telah dikurangi' : ''));
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving retur pembelian: ' . $e->getMessage());
            
            return redirect('/transaksi/pembelian?tab=retur')->withErrors(['error' => 'Terjadi kesalahan saat memproses retur: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id, StockService $stock, JournalService $journal)
    {
        \Log::info("UpdateStatus called for retur ID: {$id}");
        
        $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'items.bahanPendukung', 'pembelian'])->findOrFail($id);
        
        \Log::info("Current retur status: {$retur->status}, jenis: {$retur->jenis_retur}");
        
        // Validate that we can update to next status
        if (!$retur->next_status) {
            \Log::warning("No next status available for retur ID: {$id}");
            return back()->with('error', 'Status retur sudah final atau tidak dapat diubah.');
        }

        $oldStatus = $retur->status;
        $newStatus = $retur->next_status;
        
        \Log::info("Updating status from {$oldStatus} to {$newStatus}");

        DB::beginTransaction();
        
        try {
            // Update status
            $retur->status = $newStatus;
            $retur->save();
            
            \Log::info("Status updated successfully, now calling handleStatusChange");

            // Handle business logic based on status change
            $this->handleStatusChange($retur, $oldStatus, $newStatus, $stock, $journal);

            DB::commit();
            
            \Log::info("Transaction committed successfully");

            $statusText = $retur->status_badge['text'];
            return back()->with('success', "Status retur berhasil diubah menjadi: {$statusText}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating retur status: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage());
        }
    }

    private function handleStatusChange($retur, $oldStatus, $newStatus, $stock, $journal)
    {
        \Log::info("HandleStatusChange called: {$oldStatus} -> {$newStatus} for retur {$retur->id}");
        
        // Handle stock changes when goods are sent to vendor
        if ($newStatus === \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
            \Log::info("Processing return sent for retur {$retur->id}");
            
            // Reduce stock when goods are sent to vendor (both tukar_barang and refund)
            $stock->processReturnSent($retur->id);
            
            \Log::info("Stock processed, now creating journal entry");
            
            // Create journal entry for return sent
            $this->createReturnSentJournal($retur, $journal);
            
            \Log::info("Journal entry created for return sent");
        }

        // Handle completion for tukar_barang (replacement goods received)
        if ($retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_TUKAR_BARANG && 
            $newStatus === \App\Models\PurchaseReturn::STATUS_SELESAI) {
            
            \Log::info("Processing return completion for tukar_barang {$retur->id}");
            
            // Add stock when replacement goods are received
            $stock->processReturnCompleted($retur->id);
            
            // Create journal entry for replacement goods
            $this->createReplacementGoodsJournal($retur, $journal);
            
            \Log::info("Tukar barang completion processed");
        }

        // Handle completion for refund (money received)
        if ($retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_REFUND && 
            $newStatus === \App\Models\PurchaseReturn::STATUS_SELESAI) {
            
            \Log::info("Processing refund completion for retur {$retur->id}");
            
            // Create journal entry for refund received
            $this->createRefundReceivedJournal($retur, $journal);
            
            \Log::info("Refund completion processed");
        }
        
        \Log::info("HandleStatusChange completed");
    }

    /**
     * Create journal entry when goods are sent to vendor
     */
    private function createReturnSentJournal($retur, JournalService $journal)
    {
        $totalAmount = $retur->total_return_amount;
        
        // Hutang Usaha (Debit) - Persediaan (Kredit)
        $entries = [
            ['code' => '2101', 'debit' => $totalAmount, 'credit' => 0], // Hutang Usaha
            ['code' => '1150', 'debit' => 0, 'credit' => $totalAmount], // Persediaan
        ];

        $journal->post(
            $retur->return_date->format('Y-m-d'),
            'purchase_return_sent',
            $retur->id,
            "Retur Pembelian Dikirim #{$retur->return_number}",
            $entries
        );
    }

    /**
     * Create journal entry when replacement goods are received
     */
    private function createReplacementGoodsJournal($retur, JournalService $journal)
    {
        $totalAmount = $retur->total_return_amount;
        
        // Persediaan (Debit) - Hutang Usaha (Kredit)
        $entries = [
            ['code' => '1150', 'debit' => $totalAmount, 'credit' => 0], // Persediaan
            ['code' => '2101', 'debit' => 0, 'credit' => $totalAmount], // Hutang Usaha
        ];

        $journal->post(
            now()->format('Y-m-d'),
            'purchase_return_replacement',
            $retur->id,
            "Barang Pengganti Retur #{$retur->return_number}",
            $entries
        );
    }

    /**
     * Create journal entry when refund is received
     */
    private function createRefundReceivedJournal($retur, JournalService $journal)
    {
        $totalAmount = $retur->total_return_amount;
        
        // Get the bank/cash account from pembelian
        $kasBank = $retur->pembelian->kasBank ?? null;
        $kasBankCode = $kasBank ? $kasBank->kode_akun : '1101'; // Default to Kas
        
        // Kas/Bank (Debit) - Hutang Usaha (Kredit)
        $entries = [
            ['code' => $kasBankCode, 'debit' => $totalAmount, 'credit' => 0], // Kas/Bank
            ['code' => '2101', 'debit' => 0, 'credit' => $totalAmount], // Hutang Usaha
        ];

        $journal->post(
            now()->format('Y-m-d'),
            'purchase_return_refund',
            $retur->id,
            "Refund Retur #{$retur->return_number}",
            $entries
        );
    }



    private function recordStockMovement($itemType, $itemId, $tanggal, $direction, $qty, $satuan, $unitCost, $totalCost, $refType, $refId, $keterangan = '')
    {
        try {
            // Check for duplicate to prevent double insertion
            $existing = StockMovement::where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->where('ref_type', $refType)
                ->where('ref_id', $refId)
                ->where('direction', $direction)
                ->where('keterangan', $keterangan)
                ->first();
            
            if ($existing) {
                \Log::info('Stock movement already exists, skipping duplicate', [
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                    'direction' => $direction,
                    'existing_id' => $existing->id
                ]);
                return true;
            }

            StockMovement::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal,
                'direction' => $direction,
                'qty' => $qty,
                'satuan' => $satuan,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'keterangan' => $keterangan,
            ]);

            \Log::info('Stock movement recorded', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'direction' => $direction,
                'qty' => $qty,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'keterangan' => $keterangan
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to record stock movement: ' . $e->getMessage(), [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'direction' => $direction,
                'qty' => $qty,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'type' => 'required|in:sale,purchase',
            'alasan' => 'required|string',
            'kompensasi' => 'required|in:refund,credit,replace',
        ]);

        if ($request->type === 'sale') {
            foreach ($request->details as $detail) {
                if (!Produk::find($detail['produk_id'])) {
                    return back()->withErrors(['details' => 'Produk tidak ditemukan'])->withInput();
                }
            }
        } elseif ($request->type === 'purchase') {
            foreach ($request->details as $detail) {
                if (!\App\Models\BahanBaku::find($detail['bahan_baku_id'])) {
                    return back()->withErrors(['details' => 'Bahan baku tidak ditemukan'])->withInput();
                }
            }
        }

        $tanggalRetur = $request->tanggal;

        DB::transaction(function() use ($request, $tanggalRetur) {
            $retur = Retur::create([
                'type' => $request->type,
                'ref_id' => $request->ref_id,
                'tanggal' => $tanggalRetur,
                'kompensasi' => $request->kompensasi,
                'status' => 'draft',
                'alasan' => $request->alasan,
                'memo' => $request->memo,
                'details' => $request->details,
            ]);

            foreach ($request->details as $detail) {
                ReturDetail::create([
                    'retur_id' => $retur->id,
                    'produk_id' => $detail['produk_id'] ?? null,
                    'bahan_baku_id' => $detail['bahan_baku_id'] ?? null,
                    'qty' => $detail['qty'],
                    'harga_satuan_asal' => $detail['harga_satuan_asal'],
                    'hpp_asal' => $detail['hpp_asal'],
                    'margin' => $detail['margin'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        return redirect()->route('transaksi.retur.index')->with('success', 'Retur dibuat (Approved). Lakukan Posting untuk menjurnal dan stok.');
    }

    public function edit(Retur $retur)
    {
        $produks = Produk::all();
        $pembelians = Pembelian::all();
        return view('transaksi.retur.edit', compact('retur', 'produks', 'pembelians'));
    }

    public function update(Request $request, Retur $retur)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'alasan' => 'required|string',
            'kompensasi' => 'required|in:refund,credit,replace',
            'memo' => 'nullable|string',
        ]);

        $retur->update([
            'tanggal' => $request->tanggal,
            'alasan' => $request->alasan,
            'kompensasi' => $request->kompensasi,
            'memo' => $request->memo,
        ]);

        return redirect()->route('transaksi.retur.index')->with('success', 'Retur diperbarui.');
    }

    public function destroy(Retur $retur)
    {
        $retur->delete();
        return redirect()->route('transaksi.retur.index')->with('success', 'Data retur berhasil dihapus.');
    }

    public function approve($id)
    {
        $retur = Retur::findOrFail($id);
        if ($retur->status !== 'draft') {
            return back()->with('error', 'Hanya retur dengan status draft yang bisa di-approve.');
        }

        $retur->update(['status' => 'approved']);
        return back()->with('success', 'Retur di-approve.');
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

    public function showPembelian($id)
    {
        $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'pembelian'])->findOrFail($id);
        return view('transaksi.retur-pembelian.show', compact('retur'));
    }

    public function showPenjualan($id)
    {
        $retur = Retur::with(['details.produk', 'penjualan'])->findOrFail($id);
        return view('transaksi.retur-penjualan.show', compact('retur'));
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
            $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'items.bahanPendukung'])->findOrFail($id);
            
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
            
            // Update status
            $retur->status = \App\Models\PurchaseReturn::STATUS_DIKIRIM;
            $retur->save();
            
            // Process stock reduction for ALL return types (both REFUND and TUKAR_BARANG)
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
                    
                    // Create kartu_stok entry (stock out) - for BOTH refund and tukar_barang
                    $keterangan = $retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_REFUND 
                        ? "Retur Pembelian - Refund #{$retur->return_number}"
                        : "Retur Pembelian - Kirim Tukar Barang #{$retur->return_number}";
                        
                    DB::table('kartu_stok')->insert([
                        'item_id' => $itemId,
                        'item_type' => $itemType,
                        'tanggal' => now()->format('Y-m-d'),
                        'qty_masuk' => null,
                        'qty_keluar' => $exactQuantity,
                        'keterangan' => $keterangan,
                        'ref_type' => 'retur',
                        'ref_id' => $retur->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Update bahan_baku stock - for BOTH refund and tukar_barang
                    if ($itemType === 'bahan_baku') {
                        DB::table('bahan_bakus')
                            ->where('id', $itemId)
                            ->decrement('stok', $exactQuantity);
                    }
                    
                    // Update bahan_pendukung stock - for BOTH refund and tukar_barang
                    if ($itemType === 'bahan_pendukung') {
                        DB::table('bahan_pendukungs')
                            ->where('id', $itemId)
                            ->decrement('stok', $exactQuantity);
                    }
                    
                    $jenisText = $retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_REFUND ? 'REFUND' : 'TUKAR_BARANG';
                    Log::info("{$jenisText} - Stock reduced for {$itemName}: -{$exactQuantity} (Return ID: {$retur->id}, Item ID: {$itemId})");
                }
            }
            
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
            $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'items.bahanPendukung'])->findOrFail($id);
            
            if ($retur->jenis_retur !== \App\Models\PurchaseReturn::JENIS_TUKAR_BARANG) {
                return back()->with('error', 'Fungsi ini hanya untuk retur tukar barang.');
            }
            
            if ($retur->status !== \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
                return back()->with('error', 'Status retur tidak valid untuk terima barang.');
            }
            
            DB::beginTransaction();
            
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
            
            DB::commit();
            
            Log::info("Retur {$retur->id} barang pengganti diterima - all stock movements recorded with exact quantities");
            
            return redirect('/transaksi/pembelian?tab=retur')->with('success', 'Barang pengganti berhasil diterima');
            
        } catch (\Exception $e) {
            DB::rollBack();
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
            $retur = \App\Models\PurchaseReturn::findOrFail($id);
            
            if ($retur->jenis_retur !== \App\Models\PurchaseReturn::JENIS_REFUND) {
                return back()->with('error', 'Fungsi ini hanya untuk retur refund.');
            }
            
            if ($retur->status !== \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
                return back()->with('error', 'Status retur tidak valid untuk terima refund.');
            }
            
            // Update status (no additional stock changes for refund - already reduced when created)
            $retur->status = \App\Models\PurchaseReturn::STATUS_SELESAI;
            $retur->save();
            
            Log::info("Retur REFUND {$retur->id} completed - no additional stock changes (stock was reduced when retur was created)");
            
            return redirect('/transaksi/pembelian?tab=retur')->with('success', 'Refund berhasil diterima');
            
        } catch (\Exception $e) {
            Log::error('Error terima refund: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat terima refund: ' . $e->getMessage());
        }
    }
}
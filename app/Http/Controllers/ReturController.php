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
            return redirect()->route('transaksi.pembelian.index')
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
            return redirect()->route('transaksi.retur-pembelian.index')->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.']);
        }

        // Use proper transaction handling
        DB::beginTransaction();
        
        try {
            // Create PurchaseReturn record
            $purchaseReturn = \App\Models\PurchaseReturn::create([
                'pembelian_id' => $pembelian->id,
                'return_date' => $tanggalRetur,
                'reason' => $request->alasan,
                'jenis_retur' => $request->jenis_retur,
                'notes' => $request->memo,
                'status' => \App\Models\PurchaseReturn::STATUS_MENUNGGU_ACC, // Set default status
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
            }

            // Update total return amount
            $purchaseReturn->total_return_amount = $totalNominal;
            $purchaseReturn->save();

            DB::commit();

            \Log::info('Retur pembelian successfully created', ['return_id' => $purchaseReturn->id]);

            // Redirect to index with success message
            return redirect()->route('transaksi.retur-pembelian.index')->with('success', 'Retur berhasil disimpan');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving retur pembelian: ' . $e->getMessage());
            
            return redirect()->route('transaksi.retur-pembelian.index')->withErrors(['error' => 'Terjadi kesalahan saat memproses retur: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id, StockService $stock, JournalService $journal)
    {
        $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'pembelian'])->findOrFail($id);
        
        // Validate that we can update to next status
        if (!$retur->next_status) {
            return back()->with('error', 'Status retur sudah final atau tidak dapat diubah.');
        }

        $oldStatus = $retur->status;
        $newStatus = $retur->next_status;

        DB::beginTransaction();
        
        try {
            // Update status
            $retur->status = $newStatus;
            $retur->save();

            // Handle business logic based on status change
            $this->handleStatusChange($retur, $oldStatus, $newStatus, $stock, $journal);

            DB::commit();

            $statusText = $retur->status_badge['text'];
            return back()->with('success', "Status retur berhasil diubah menjadi: {$statusText}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating retur status: ' . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage());
        }
    }

    private function handleStatusChange($retur, $oldStatus, $newStatus, $stock, $journal)
    {
        // Handle stock changes
        if ($newStatus === \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
            // Kurangi stok saat barang dikirim
            foreach ($retur->items as $item) {
                if ($item->bahan_baku_id && $item->bahanBaku) {
                    $material = $item->bahanBaku;
                    $qty = $item->quantity;
                    
                    // Update stock (reduce)
                    $material->updateStok($qty, 'out', "Retur dikirim ID: {$retur->id}");
                    
                    // Record to stock_movements for audit trail
                    $this->recordStockMovement(
                        'material',
                        $item->bahan_baku_id,
                        $retur->return_date ?? now(),
                        'out',
                        $qty,
                        $item->unit ?? $material->satuan->nama ?? 'KG',
                        $item->unit_price ?? 0,
                        $item->subtotal ?? 0,
                        'purchase_return',
                        $retur->id,
                        "Retur Pembelian - Dikirim ke Vendor"
                    );
                }
                
                // Handle bahan pendukung if exists
                if ($item->bahan_pendukung_id && $item->bahanPendukung) {
                    $material = $item->bahanPendukung;
                    $qty = $item->quantity;
                    
                    // Update stock (reduce)
                    $material->updateStok($qty, 'out', "Retur dikirim ID: {$retur->id}");
                    
                    // Record to stock_movements for audit trail
                    $this->recordStockMovement(
                        'support', // Changed from 'material' to 'support' for bahan_pendukung
                        $item->bahan_pendukung_id,
                        $retur->return_date ?? now(),
                        'out',
                        $qty,
                        $item->unit ?? $material->satuan->nama ?? 'KG',
                        $item->unit_price ?? 0,
                        $item->subtotal ?? 0,
                        'purchase_return',
                        $retur->id,
                        "Retur Pembelian - Dikirim ke Vendor"
                    );
                }
            }
        }

        if ($retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_TUKAR_BARANG && 
            $newStatus === \App\Models\PurchaseReturn::STATUS_SELESAI) {
            // Tambah stok saat tukar barang selesai (barang baru diterima)
            foreach ($retur->items as $item) {
                if ($item->bahan_baku_id && $item->bahanBaku) {
                    $material = $item->bahanBaku;
                    $qty = $item->quantity;
                    
                    // Update stock (add new stock)
                    $material->updateStok($qty, 'in', "Tukar barang selesai ID: {$retur->id}");
                    
                    // Record to stock_movements for audit trail
                    $this->recordStockMovement(
                        'material',
                        $item->bahan_baku_id,
                        $retur->return_date ?? now(),
                        'in',
                        $qty,
                        $item->unit ?? $material->satuan->nama ?? 'KG',
                        $item->unit_price ?? 0,
                        $item->subtotal ?? 0,
                        'purchase_return',
                        $retur->id,
                        "Barang pengganti dari retur pembelian"
                    );
                }
                
                // Handle bahan pendukung if exists
                if ($item->bahan_pendukung_id && $item->bahanPendukung) {
                    $material = $item->bahanPendukung;
                    $qty = $item->quantity;
                    
                    // Update stock (add new stock)
                    $material->updateStok($qty, 'in', "Tukar barang selesai ID: {$retur->id}");
                    
                    // Record to stock_movements for audit trail
                    $this->recordStockMovement(
                        'support', // Changed from 'material' to 'support' for bahan_pendukung
                        $item->bahan_pendukung_id,
                        $retur->return_date ?? now(),
                        'in',
                        $qty,
                        $item->unit ?? $material->satuan->nama ?? 'KG',
                        $item->unit_price ?? 0,
                        $item->subtotal ?? 0,
                        'purchase_return',
                        $retur->id,
                        "Barang pengganti dari retur pembelian"
                    );
                }
            }
        }

        if ($retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_REFUND && 
            $newStatus === \App\Models\PurchaseReturn::STATUS_REFUND_SELESAI) {
            // Tambah kas saat refund selesai
            $totalRefund = $retur->total_return_amount;
            
            if ($totalRefund > 0) {
                // Create journal entry for refund received
                $journal->post(
                    date('Y-m-d'), 
                    'purchase_return_refund', 
                    $retur->id, 
                    "Refund retur pembelian #{$retur->return_number}", 
                    [
                        ['code' => '1101', 'debit' => $totalRefund, 'credit' => 0], // Kas
                        ['code' => '1141', 'debit' => 0, 'credit' => $totalRefund], // Persediaan (recovery)
                    ]
                );
            }
        }

        \Log::info('Retur status changed', [
            'retur_id' => $retur->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'jenis_retur' => $retur->jenis_retur
        ]);
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
        return redirect()->route('transaksi.retur-pembelian.index')->with('success', 'Data retur pembelian berhasil dihapus.');
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
}

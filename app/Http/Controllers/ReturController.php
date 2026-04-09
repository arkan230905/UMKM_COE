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
        // Use PurchaseReturn model instead of Retur for purchase returns
        $returs = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'pembelian'])
            ->orderBy('id', 'desc')
            ->get();
        
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
        $pembelianId = $request->query('pembelian_id');
        
        if (!$pembelianId) {
            return redirect()->route('transaksi.pembelian.index')
                ->with('error', 'Silakan pilih pembelian yang ingin diretur dari daftar pembelian.');
        }
        
        $pembelian = Pembelian::with(['details.bahanBaku', 'details.bahanPendukung', 'vendor'])->findOrFail($pembelianId);
        
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
        $request->validate([
            'pembelian_id' => 'required|exists:pembelians,id',
            'alasan' => 'required|string',
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
            return back()->withInput()->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.']);
        }

        try {
            DB::transaction(function() use ($request, $pembelian, $items, $stock, $journal, $tanggalRetur) {
            // Use PurchaseReturn model for purchase returns instead of generic Retur
            $purchaseReturn = \App\Models\PurchaseReturn::create([
                'pembelian_id' => $pembelian->id,
                'return_date' => $tanggalRetur,
                'reason' => $request->alasan,
                'jenis_retur' => $request->jenis_retur ?? 'refund',
                'notes' => $request->memo,
                'status' => 'pending',
            ]);

            $totalNominal = 0;
            $totalHpp = 0;

            foreach ($items as $itemData) {
                $detail = $pembelian->details()->where('id', $itemData['pembelian_detail_id'])->first();
                
                if (!$detail) continue;

                $qty = (float)$itemData['qty'];
                $avg = (float)($detail->harga_satuan ?? 0);

                // Determine if this is bahan baku or bahan pendukung
                $isBahanBaku = !empty($detail->bahan_baku_id);
                $isBahanPendukung = !empty($detail->bahan_pendukung_id);
                
                if (!$isBahanBaku && !$isBahanPendukung) {
                    continue; // Skip if neither bahan baku nor bahan pendukung
                }

                // NO STOCK CHANGES ON CREATE - Only validate that we have enough stock for future processing
                $material = null;
                $materialId = null;
                $materialName = '';
                
                if ($isBahanBaku) {
                    $material = $detail->bahanBaku;
                    $materialId = $detail->bahan_baku_id;
                    $materialName = $material->nama_bahan ?? 'ID: ' . $materialId;
                } else {
                    $material = $detail->bahanPendukung;
                    $materialId = $detail->bahan_pendukung_id;
                    $materialName = $material->nama_bahan ?? 'ID: ' . $materialId;
                }
                
                if (!$material) {
                    throw new \RuntimeException("Data material tidak ditemukan untuk ID: " . $materialId);
                }
                
                // Only validate stock availability for future processing (no actual stock change)
                $satuan = $itemData['satuan'] ?? $detail->satuan_nama ?? 'kg';
                $qtyInPrimaryUnit = $qty; // Default to input qty
                
                // For bahan baku, use existing conversion logic
                if ($isBahanBaku && method_exists($material, 'convertToKg')) {
                    $qtyInPrimaryUnit = $material->convertToKg($qty, $satuan);
                }
                
                // Check available stock for validation only (no consumption yet)
                $availableStock = $stock->getAvailableQty('material', (int)$materialId);
                
                if ($qtyInPrimaryUnit > $availableStock) {
                    $primaryUnit = $material->satuan->nama ?? 'unit';
                    
                    throw new \RuntimeException("Stok tidak mencukupi untuk retur '{$materialName}'. " .
                        "Dibutuhkan: {$qty} {$satuan} ({$qtyInPrimaryUnit} {$primaryUnit}), " .
                        "Tersedia: {$availableStock} {$primaryUnit}. " .
                        "Pastikan material tersedia di gudang sebelum melakukan retur.");
                }
                
                // NO STOCK CONSUMPTION HERE - Stock will be changed only when status becomes "selesai"
                $lineNominal = (float)($detail->harga_satuan ?? 0) * $qty;
                $lineHpp = $avg * $qty;

                // Create PurchaseReturnItem for both bahan baku and bahan pendukung
                \App\Models\PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'pembelian_detail_id' => $detail->id,
                    'bahan_baku_id' => $isBahanBaku ? $detail->bahan_baku_id : null,
                    'bahan_pendukung_id' => $isBahanPendukung ? $detail->bahan_pendukung_id : null,
                    'unit' => $satuan,
                    'quantity' => $qty,
                    'unit_price' => $detail->harga_satuan ?? 0,
                    'subtotal' => $lineNominal,
                ]);

                $totalNominal += $lineNominal;
                $totalHpp += $lineHpp;
            }

            // Update the total return amount in the PurchaseReturn record
            $purchaseReturn->total_return_amount = $totalNominal;
            $purchaseReturn->save();

            // For purchase returns, we don't need the sale-specific journal logic
            // The journal entries for purchase returns would be different and should be handled separately if needed
        });

        return redirect()->route('transaksi.retur-pembelian.index')->with('success', 'Retur pembelian berhasil dibuat dengan status pending.');
        
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat memproses retur: ' . $e->getMessage()]);
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

    /**
     * Update return status based on workflow
     */
    public function updateStatus($id, $status)
    {
        try {
            $retur = \App\Models\PurchaseReturn::findOrFail($id);
            
            // Validate allowed status transitions
            $allowedStatuses = [
                'pending',
                'menunggu_vendor',
                'disetujui_vendor', 
                'diproses_vendor',
                'barang_diterima',
                'barang_dikembalikan',
                'menunggu_pembayaran',
                'dana_diterima',
                'completed'
            ];
            
            if (!in_array($status, $allowedStatuses)) {
                return back()->with('error', 'Status tidak valid.');
            }
            
            // Log status change
            \Log::info("Return status update:", [
                'retur_id' => $id,
                'old_status' => $retur->status,
                'new_status' => $status,
                'jenis_retur' => $retur->jenis_retur
            ]);
            
            // Update status
            $retur->status = $status;
            $retur->save();
            
            // Handle stock updates for final statuses
            if (in_array($status, ['barang_diterima', 'dana_diterima'])) {
                // Call the existing proses method logic for stock updates
                return $this->processStockUpdate($retur);
            }
            
            // Success message based on status
            $messages = [
                'disetujui_vendor' => 'Status berhasil diubah: Vendor telah menyetujui retur.',
                'diproses_vendor' => 'Status berhasil diubah: Vendor sedang memproses barang.',
                'barang_diterima' => 'Status berhasil diubah: Barang pengganti telah diterima.',
                'barang_dikembalikan' => 'Status berhasil diubah: Barang telah dikembalikan ke vendor.',
                'menunggu_pembayaran' => 'Status berhasil diubah: Menunggu pembayaran refund dari vendor.',
                'dana_diterima' => 'Status berhasil diubah: Dana refund telah diterima.'
            ];
            
            $message = $messages[$status] ?? 'Status retur berhasil diperbarui.';
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error updating return status: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengubah status retur.');
        }
    }

    /**
     * Process stock update for completed returns
     */
    private function processStockUpdate($retur)
    {
        try {
            DB::beginTransaction();
            
            // Only process stock if not already completed
            if ($retur->status === 'completed') {
                return back()->with('success', 'Retur sudah selesai diproses sebelumnya.');
            }
            
            $retur = \App\Models\PurchaseReturn::with(['items.bahanBaku', 'items.bahanPendukung', 'pembelian.details'])->findOrFail($retur->id);
            
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
                    continue;
                }
                
                // Calculate converted quantity
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
                
                $qtyRaw = (float) $item->quantity;
                $qtyConverted = $originalDetail && $originalDetail->faktor_konversi > 0 
                    ? $qtyRaw * $originalDetail->faktor_konversi 
                    : $qtyRaw;
                
                if ($qtyConverted <= 0) continue;
                
                // Apply stock changes based on return type
                if ($retur->jenis_retur === 'refund') {
                    $updateSuccess = $material->updateStok($qtyConverted, 'out', "Return refund completed ID: {$retur->id}");
                    if (!$updateSuccess) {
                        DB::rollBack();
                        return back()->with('error', "Stok {$materialName} tidak mencukupi untuk retur.");
                    }
                } elseif ($retur->jenis_retur === 'tukar_barang') {
                    // For tukar barang, stock is neutral (old out, new in)
                    $material->updateStok($qtyConverted, 'out', "Return exchange (old) completed ID: {$retur->id}");
                    $material->updateStok($qtyConverted, 'in', "Return exchange (new) completed ID: {$retur->id}");
                }
            }
            
            // Mark as completed
            $retur->status = 'completed';
            $retur->save();
            
            DB::commit();
            
            return back()->with('success', 'Retur berhasil diselesaikan dan stok telah diperbarui.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing stock update: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses stok retur.');
        }
    }
}

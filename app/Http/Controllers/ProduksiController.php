<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Services\StockService;
use App\Services\JournalService;
use App\Support\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Produksi::with('produk');
        
        // Filter by tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        
        // Filter by produk
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $produksis = $query->orderBy('tanggal','desc')->paginate(10);
        
        // Get products for dropdown
        $produks = Produk::whereHas('boms', function($query) {
            $query->has('details');
        })->orderBy('nama_produk')->get();
        
        return view('transaksi.produksi.index', compact('produksis', 'produks'));
    }

    public function create()
    {
        // Hanya ambil produk yang sudah memiliki BOM dengan detail
        $produks = Produk::whereHas('boms', function($query) {
            $query->has('details');
        })->get();
        
        return view('transaksi.produksi.create', compact('produks'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'tanggal' => 'required|date',
            'qty_produksi' => 'required|numeric|min:0.0001',
        ]);

        // Guard: pastikan produk sudah memiliki BOM dan detail
        $bom = \App\Models\Bom::where('produk_id', $request->produk_id)
            ->withCount('details')
            ->first();
        if (!$bom || (int)($bom->details_count ?? 0) === 0) {
            return back()->withErrors([
                'bom' => 'Produk belum melewati perhitungan Bill Of Material. Silakan lakukan perhitungan Bill Of Material untuk produk tersebut.',
            ])->withInput();
        }

        return DB::transaction(function () use ($request, $stock, $journal) {
            $produk = Produk::findOrFail($request->produk_id);
            $qtyProd = (float)$request->qty_produksi;
            $tanggal = $request->tanggal;
            $converter = new UnitConverter();

            $bomItems = Bom::with('bahanBaku')->where('produk_id', $produk->id)->get();
            // Validasi stok cukup untuk setiap bahan baku (cek ke StockLayer via StockService)
            $shortages = [];
            $shortNames = [];
            foreach ($bomItems as $it) {
                $bahan = $it->bahanBaku;
                if (!$bahan) { continue; }
                $qtyPerUnit = (float)$it->jumlah;
                $satuanResep = $it->satuan_resep ?: $bahan->satuan;
                $qtyResepTotal = $qtyPerUnit * $qtyProd;
                $qtyBase = $converter->convert($qtyResepTotal, (string)$satuanResep, (string)$bahan->satuan);
                $available = (float) $stock->getAvailableQty('material', (int)$bahan->id);
                if ($available + 1e-9 < $qtyBase) {
                    $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh $qtyBase, tersedia " . (float)($available);
                    $shortNames[] = (string)($bahan->nama_bahan ?? $bahan->nama ?? 'Bahan');
                }
            }
            if (!empty($shortages)) {
                // Gabungkan pesan ringkas sesuai permintaan user
                $msg = 'Bahan baku '.implode(', ', $shortNames).' kurang untuk melakukan produksi produk.';
                return back()->withErrors([$msg])->withInput();
            }

            $totalBahan = 0.0;
            $fifoCostMaterials = 0.0;

            $produksi = Produksi::create([
                'produk_id' => $produk->id,
                'tanggal' => $tanggal,
                'qty_produksi' => $qtyProd,
            ]);

            foreach ($bomItems as $it) {
                $bahan = $it->bahanBaku;
                $qtyPerUnit = (float)$it->jumlah;
                $satuanResep = $it->satuan_resep ?: $bahan->satuan;
                $qtyResepTotal = $qtyPerUnit * $qtyProd;
                $qtyBase = $converter->convert($qtyResepTotal, (string)$satuanResep, (string)$bahan->satuan);
                $hargaSatuan = (float)($bahan->harga_satuan ?? 0);
                $subtotal = $hargaSatuan * $qtyBase;
                $totalBahan += $subtotal;

                // FIFO consume bahan (gunakan biaya FIFO untuk jurnal WIP)
                try {
                    $unitStr = (string)($bahan->satuan->kode ?? $bahan->satuan->nama ?? $bahan->satuan ?? 'pcs');
                    $fifoCost = $stock->consume('material', $bahan->id, $qtyBase, $unitStr, 'production', $produksi->id, $tanggal);
                } catch (\RuntimeException $e) {
                    return back()->withErrors(["Stok {$bahan->nama_bahan} tidak mencukupi untuk produksi. ".$e->getMessage()])->withInput();
                }
                $fifoCostMaterials += (float)$fifoCost;

                // Update stok bahan baku master
                $bahan->stok = (float)$bahan->stok - $qtyBase;
                $bahan->save();

                ProduksiDetail::create([
                    'produksi_id' => $produksi->id,
                    'bahan_baku_id' => $bahan->id,
                    'qty_resep' => $qtyResepTotal,
                    'satuan_resep' => $satuanResep,
                    'qty_konversi' => $qtyBase,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                ]);
            }

            // BTKL & BOP: gunakan default per-unit jika ada; jika tidak, hitung total dari persentase total bahan (tanpa dikali qty dua kali)
            $btklRate = (float) (config('app.btkl_percent') ?? 0.2);
            $bopRate  = (float) (config('app.bop_percent') ?? 0.1);
            if (!is_null($produk->btkl_default) || !is_null($produk->bop_default)) {
                $btklPerUnit = (float) ($produk->btkl_default ?? 0);
                $bopPerUnit  = (float) ($produk->bop_default ?? 0);
                $totalBTKL = $btklPerUnit * $qtyProd;
                $totalBOP  = $bopPerUnit  * $qtyProd;
            } else {
                $totalBTKL = $totalBahan * $btklRate; // total untuk seluruh batch
                $totalBOP  = $totalBahan * $bopRate;  // total untuk seluruh batch
            }
            $totalBiaya = $totalBahan + $totalBTKL + $totalBOP;

            $produksi->update([
                'total_bahan' => $totalBahan,
                'total_btkl' => $totalBTKL,
                'total_bop' => $totalBOP,
                'total_biaya' => $totalBiaya,
            ]);

            // Unit cost produk jadi
            $unitCostProduk = $totalBiaya / max($qtyProd, 1);

            // Tambahkan layer produk (IN)
            $stock->addLayer('product', $produk->id, $qtyProd, 'pcs', $unitCostProduk, 'production', $produksi->id, $tanggal);

            // Update stok produk (tanpa mengubah harga_jual — harga_jual mengikuti BOM + 30%)
            $produk->stok = (float)($produk->stok ?? 0) + $qtyProd;
            $produk->save();

            // === Posting Jurnal Produksi ===
            // 1) Konsumsi bahan: Dr WIP (1105) ; Cr Persediaan Bahan Baku (1104)
            if (($fifoCostMaterials ?? 0) > 0) {
                $journal->post($tanggal, 'production_material', (int)$produksi->id, 'Konsumsi bahan ke WIP', [
                    ['code' => '1105', 'debit' => (float)$fifoCostMaterials, 'credit' => 0],  // WIP
                    ['code' => '1104', 'debit' => 0, 'credit' => (float)$fifoCostMaterials],  // Persediaan Bahan Baku
                ]);
            }
            // 2) BTKL & BOP ke WIP
            $totalBTKLBOP = (float)$totalBTKL + (float)$totalBOP;
            if ($totalBTKLBOP > 0) {
                $lines = [
                    ['code' => '1105', 'debit' => $totalBTKLBOP, 'credit' => 0],  // WIP
                ];
                if ((float)$totalBTKL > 0) { $lines[] = ['code' => '2103', 'debit' => 0, 'credit' => (float)$totalBTKL]; }  // Hutang Gaji
                if ((float)$totalBOP  > 0) { $lines[] = ['code' => '2104', 'debit' => 0, 'credit' => (float)$totalBOP]; }  // Hutang BOP
                $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'BTKL/BOP ke WIP', $lines);
            }
            // 3) Selesai produksi: Dr Persediaan Barang Jadi (1107) ; Cr WIP (1105)
            if ((float)$totalBiaya > 0) {
                $journal->post($tanggal, 'production_finish', (int)$produksi->id, 'Selesai produksi', [
                    ['code' => '1107', 'debit' => (float)$totalBiaya, 'credit' => 0],  // Persediaan Barang Jadi
                    ['code' => '1105', 'debit' => 0, 'credit' => (float)$totalBiaya],  // WIP
                ]);
            }

            return redirect()->route('transaksi.produksi.show', $produksi->id)
                ->with('success', 'Produksi berhasil disimpan.');
        });
    }

    public function show($id)
    {
        $produksi = Produksi::with(['produk','details.bahanBaku'])->findOrFail($id);
        return view('transaksi.produksi.show', compact('produksi'));
    }

    public function destroy($id, JournalService $journal)
    {
        $produksi = Produksi::findOrFail($id);
        DB::transaction(function () use ($produksi, $journal) {
            // Hapus jurnal terkait produksi
            $journal->deleteByRef('production_material', (int)$produksi->id);
            $journal->deleteByRef('production_labor_overhead', (int)$produksi->id);
            $journal->deleteByRef('production_finish', (int)$produksi->id);

            // Hapus detail produksi
            \App\Models\ProduksiDetail::where('produksi_id', $produksi->id)->delete();

            // Hapus header produksi
            $produksi->delete();
        });

        return redirect()->route('transaksi.produksi.index')->with('success', 'Data produksi telah dihapus.');
    }
    
    /**
     * Tandai produksi sebagai selesai
     */
    public function complete($id)
    {
        $produksi = Produksi::findOrFail($id);
        
        if ($produksi->status === 'completed') {
            return redirect()->route('transaksi.produksi.index')->with('info', 'Produksi sudah ditandai selesai sebelumnya.');
        }
        
        $produksi->update(['status' => 'completed']);
        
        return redirect()->route('transaksi.produksi.index')->with('success', 'Produksi berhasil ditandai selesai!');
    }
}

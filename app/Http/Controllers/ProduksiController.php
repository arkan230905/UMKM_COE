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
    public function index()
    {
        $produksis = Produksi::with('produk')->orderBy('tanggal','desc')->paginate(10);
        return view('transaksi.produksi.index', compact('produksis'));
    }

    public function create()
    {
        $produks = Produk::all();
        return view('transaksi.produksi.create', compact('produks'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'tanggal' => 'required|date',
            'qty_produksi' => 'required|numeric|min:0.0001',
        ]);

        return DB::transaction(function () use ($request, $stock) {
            $produk = Produk::findOrFail($request->produk_id);
            $qtyProd = (float)$request->qty_produksi;
            $tanggal = $request->tanggal;
            $converter = new UnitConverter();

            $bomItems = Bom::with('bahanBaku')->where('produk_id', $produk->id)->get();
            // Validasi stok cukup untuk setiap bahan baku
            $shortages = [];
            foreach ($bomItems as $it) {
                $bahan = $it->bahanBaku;
                $qtyPerUnit = (float)$it->jumlah;
                $satuanResep = $it->satuan_resep ?: $bahan->satuan;
                $qtyResepTotal = $qtyPerUnit * $qtyProd;
                $qtyBase = $converter->convert($qtyResepTotal, (string)$satuanResep, (string)$bahan->satuan);
                if ((float)($bahan->stok ?? 0) < $qtyBase) {
                    $shortages[] = "Stok {$bahan->nama_bahan} kurang: butuh " . rtrim(rtrim(number_format($qtyBase,4,',','.'),'0'),',') . " {$bahan->satuan}, tersedia " . rtrim(rtrim(number_format((float)($bahan->stok ?? 0),4,',','.'),'0'),',') . " {$bahan->satuan}";
                }
            }
            if (!empty($shortages)) {
                return back()->withErrors($shortages)->withInput();
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
                $fifoCost = $stock->consume('material', $bahan->id, $qtyBase, (string)$bahan->satuan, 'production', $produksi->id, $tanggal);
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
            // 1) Konsumsi bahan: Dr WIP (122) ; Cr Persediaan Bahan Baku (121)
            if (($fifoCostMaterials ?? 0) > 0) {
                $journal->post($tanggal, 'production_material', (int)$produksi->id, 'Konsumsi bahan ke WIP', [
                    ['code' => '122', 'debit' => (float)$fifoCostMaterials, 'credit' => 0],
                    ['code' => '121', 'debit' => 0, 'credit' => (float)$fifoCostMaterials],
                ]);
            }
            // 2) BTKL & BOP ke WIP
            $totalBTKLBOP = (float)$totalBTKL + (float)$totalBOP;
            if ($totalBTKLBOP > 0) {
                $lines = [
                    ['code' => '122', 'debit' => $totalBTKLBOP, 'credit' => 0],
                ];
                if ((float)$totalBTKL > 0) { $lines[] = ['code' => '211', 'debit' => 0, 'credit' => (float)$totalBTKL]; }
                if ((float)$totalBOP  > 0) { $lines[] = ['code' => '212', 'debit' => 0, 'credit' => (float)$totalBOP]; }
                $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'BTKL/BOP ke WIP', $lines);
            }
            // 3) Selesai produksi: Dr Persediaan Barang Jadi (123) ; Cr WIP (122)
            if ((float)$totalBiaya > 0) {
                $journal->post($tanggal, 'production_finish', (int)$produksi->id, 'Selesai produksi', [
                    ['code' => '123', 'debit' => (float)$totalBiaya, 'credit' => 0],
                    ['code' => '122', 'debit' => 0, 'credit' => (float)$totalBiaya],
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
}

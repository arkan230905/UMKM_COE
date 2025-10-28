<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use Illuminate\Support\Facades\DB;
use App\Support\UnitConverter;

class BomController extends Controller
{
    public function index()
    {
        $produks = Produk::all();
        $selectedProductId = request('produk_id');
        return view('master-data.bom.index', compact('produks', 'selectedProductId'));
    }

    public function create()
    {
        $produks = Produk::all();
        $bahan_bakus = BahanBaku::all();
        return view('master-data.bom.create', compact('produks', 'bahan_bakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required',
            'bahan_baku_id' => 'required|array',
            'jumlah' => 'required|array',
            'satuan_resep' => 'nullable|array',
            'btkl' => 'nullable|numeric',
            'bop' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request) {
            $produk = Produk::findOrFail($request->produk_id);
            $total_bahan = 0;
            $converter = new UnitConverter();

            foreach ($request->bahan_baku_id as $i => $bahan_id) {
                $bahan = BahanBaku::findOrFail($bahan_id);
                $qtyResep = (float) ($request->jumlah[$i] ?? 0);
                $satuanResep = $request->satuan_resep[$i] ?? $bahan->satuan;

                // Konversi jumlah resep ke satuan dasar bahan (satuan harga)
                $qtyDalamSatuanBahan = $converter->convert($qtyResep, $satuanResep, $bahan->satuan);
                $subtotal = (float) $bahan->harga_satuan * (float) $qtyDalamSatuanBahan;
                $total_bahan += $subtotal;

                Bom::create([
                    'produk_id' => $produk->id,
                    'bahan_baku_id' => $bahan_id,
                    'jumlah' => $qtyResep,
                    'satuan_resep' => $satuanResep,
                    'total_biaya' => $subtotal,
                ]);
            }

            // Tambahkan BTKL & BOP (otomatis dari persentase total bahan jika tidak diisi)
            $btklRate = (float) (config('app.btkl_percent') ?? 0.2); // 20% default
            $bopRate  = (float) (config('app.bop_percent') ?? 0.1);  // 10% default

            $btkl_sum = $request->filled('btkl') ? (float)$request->btkl : ($total_bahan * $btklRate);
            $bop_sum  = $request->filled('bop')  ? (float)$request->bop  : ($total_bahan * $bopRate);
            $grand_total = $total_bahan + $btkl_sum + $bop_sum;

            // Set harga jual otomatis dari BOM + 30% keuntungan
            $harga_jual = $grand_total * 1.3;
            $produk->update([
                'harga_jual' => $harga_jual,
                'btkl_default' => $btkl_sum,
                'bop_default' => $bop_sum,
            ]);
        });

        return redirect()->route('master-data.bom.index', ['produk_id' => $request->produk_id])
                         ->with('success', 'BOM berhasil ditambahkan.');
    }

    public function view($produk_id)
    {
        $produk = Produk::findOrFail($produk_id);
        $items = Bom::with('bahanBaku')->where('produk_id', $produk_id)->get();

        $converter = new UnitConverter();
        $breakdown = [];
        $total_bahan = 0.0;

        foreach ($items as $it) {
            $bahan = $it->bahanBaku;
            if (!$bahan) { continue; }
            $qtyResep = (float) ($it->jumlah ?? 0);
            $satuanResep = $it->satuan_resep ?: $bahan->satuan;
            $satuanBahan = $bahan->satuan;
            $hargaSatuan = (float) ($bahan->harga_satuan ?? 0);
            $qtyBase = $converter->convert($qtyResep, (string)$satuanResep, (string)$satuanBahan);
            $desc = $converter->describe((string)$satuanResep, (string)$satuanBahan);
            $subtotal = $hargaSatuan * $qtyBase;
            $total_bahan += $subtotal;

            $breakdown[] = [
                'bom_id' => $it->id,
                'nama_bahan' => $bahan->nama_bahan,
                'qty_resep' => $qtyResep,
                'satuan_resep' => $satuanResep,
                'satuan_bahan' => $satuanBahan,
                'qty_konversi' => $qtyBase,
                'konversi_ket' => $desc,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
            ];
        }

        // Gunakan default BTKL/BOP dari produk jika tersedia
        $btkl_sum = (float) ($produk->btkl_default ?? 0);
        $bop_sum = (float) ($produk->bop_default ?? 0);
        $grand_total = $total_bahan + $btkl_sum + $bop_sum;

        return view('master-data.bom.partials.table', [
            'produk' => $produk,
            'items' => $items,
            'breakdown' => $breakdown,
            'total_bahan' => $total_bahan,
            'btkl_sum' => $btkl_sum,
            'bop_sum' => $bop_sum,
            'grand_total' => $grand_total,
        ]);
    }

    public function updateByProduk(Request $request, $produk_id)
    {
        $produk = Produk::findOrFail($produk_id);
        $rows = $request->input('rows', []); // rows[bom_id][jumlah], rows[bom_id][satuan_resep]
        $converter = new UnitConverter();
        $total_bahan = 0.0;

        foreach ($rows as $bomId => $row) {
            $bom = Bom::with('bahanBaku')->where('produk_id', $produk->id)->findOrFail($bomId);
            $qtyResep = (float) ($row['jumlah'] ?? 0);
            $satuanResep = $row['satuan_resep'] ?? $bom->bahanBaku->satuan;

            // Hitung ulang subtotal berdasarkan konversi ke satuan bahan
            $qtyBase = $converter->convert($qtyResep, (string)$satuanResep, (string)$bom->bahanBaku->satuan);
            $subtotal = (float) ($bom->bahanBaku->harga_satuan ?? 0) * (float) $qtyBase;
            $total_bahan += $subtotal;

            $bom->update([
                'jumlah' => $qtyResep,
                'satuan_resep' => $satuanResep,
                'total_biaya' => $subtotal,
            ]);
        }

        // BTKL/BOP: gunakan input jika ada, jika tidak pakai persentase default dari total bahan
        $btklRate = (float) (config('app.btkl_percent') ?? 0.2);
        $bopRate  = (float) (config('app.bop_percent') ?? 0.1);
        $btkl_sum = $request->filled('btkl') ? (float)$request->btkl : ($total_bahan * $btklRate);
        $bop_sum  = $request->filled('bop')  ? (float)$request->bop  : ($total_bahan * $bopRate);

        $grand_total = $total_bahan + $btkl_sum + $bop_sum;
        $harga_jual = $grand_total * 1.3;

        $produk->update([
            'harga_jual' => $harga_jual,
            'btkl_default' => $btkl_sum,
            'bop_default' => $bop_sum,
        ]);

        return redirect()->route('master-data.bom.index', ['produk_id' => $produk->id])
            ->with('success', 'BOM berhasil diperbarui.');
    }
}

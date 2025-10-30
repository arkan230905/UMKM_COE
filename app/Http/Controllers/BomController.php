<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    public function index()
    {
        $produks = Produk::all();
        $selectedProductId = request('produk_id');
        
        $query = Bom::with(['produk', 'details.bahanBaku.satuan']);
        
        if ($selectedProductId) {
            $query->where('produk_id', $selectedProductId);
        }
        
        $boms = $query->latest()->paginate(10);
            
        return view('master-data.bom.index', compact('boms', 'produks', 'selectedProductId'));
    }

    public function create()
    {
        // Ambil ID produk yang sudah memiliki BOM
        $produkIdsWithBom = Bom::pluck('produk_id')->toArray();
        
        // Ambil produk yang belum memiliki BOM
        $produks = Produk::whereNotIn('id', $produkIdsWithBom)->get();
        
        // Jika tidak ada produk yang bisa dibuat BOM-nya
        if ($produks->isEmpty()) {
            return redirect()->route('master-data.bom.index')
                ->with('info', 'Semua produk sudah memiliki BOM. Tidak ada produk yang bisa ditambahkan BOM-nya.');
        }
        
        $bahanBakus = BahanBaku::with('satuan')->get();
        
        // Debug data bahan baku
        \Log::info('Bahan Baku Data:', $bahanBakus->toArray());
        
        // Ambil semua satuan yang tersedia
        $satuans = \App\Models\Satuan::all();
        
        return view('master-data.bom.create', [
            'produks' => $produks,
            'bahanBakus' => $bahanBakus,
            'satuans' => $satuans
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id|unique:boms,produk_id',
            'kode_bom' => 'required|unique:boms,kode_bom',
            'details' => 'required|array|min:1',
            'details.*.bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'details.*.kuantitas' => 'required|numeric|min:0.01',
            'persentase_keuntungan' => 'required|numeric|min:0|max:1000',
            'catatan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            // Hitung total biaya
            $totalBiaya = 0;
            $details = [];

            foreach ($request->details as $detail) {
                $bahanBaku = BahanBaku::find($detail['bahan_baku_id']);
                $subtotal = $bahanBaku->harga_satuan * $detail['kuantitas'];
                
                $details[] = [
                    'bahan_baku_id' => $bahanBaku->id,
                    'kuantitas' => $detail['kuantitas'],
                    'harga_satuan' => $bahanBaku->harga_satuan,
                    'subtotal' => $subtotal,
                    'keterangan' => $detail['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $totalBiaya += $subtotal;
            }

            // Hitung harga jual
            $persentaseKeuntungan = $request->persentase_keuntungan;
            $keuntungan = $totalBiaya * ($persentaseKeuntungan / 100);
            $hargaJual = $totalBiaya + $keuntungan;

            // Simpan BOM
            $bom = new Bom();
            $bom->produk_id = $validated['produk_id'];
            $bom->kode_bom = $validated['kode_bom'];
            $bom->total_biaya = $totalBiaya;
            $bom->persentase_keuntungan = $persentaseKeuntungan;
            $bom->harga_jual = $hargaJual;
            $bom->catatan = $request->catatan;
            $bom->save();

            // Simpan detail BOM
            $bom->details()->createMany($details);

            // Update harga jual produk
            $produk = $bom->produk;
            $produk->update(['harga_jual' => $hargaJual]);

            DB::commit();

            return redirect()
                ->route('master-data.bom.show', $bom->id)
                ->with('success', 'BOM berhasil disimpan. Harga jual produk telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $bom = Bom::with(['produk', 'details.bahanBaku.satuan'])
            ->findOrFail($id);
            
        return view('master-data.bom.show', compact('bom'));
    }

    public function edit($id)
    {
        $bom = Bom::with(['details.bahanBaku.satuan', 'produk'])
            ->findOrFail($id);
            
        $bahanBakus = BahanBaku::with('satuan')->get();
        
        return view('master-data.bom.edit', compact('bom', 'bahanBakus'));
    }

    public function update(Request $request, $id)
    {
        $bom = Bom::findOrFail($id);
        
        $request->validate([
            'details' => 'required|array|min:1',
            'details.*.bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'details.*.kuantitas' => 'required|numeric|min:0.01',
            'persentase_keuntungan' => 'required|numeric|min:0|max:1000',
            'catatan' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            // Hapus detail lama
            $bom->details()->delete();

            // Hitung total biaya baru
            $totalBiaya = 0;
            $details = [];

            foreach ($request->details as $detail) {
                $bahanBaku = BahanBaku::find($detail['bahan_baku_id']);
                $subtotal = $bahanBaku->harga_satuan * $detail['kuantitas'];
                
                $details[] = [
                    'bahan_baku_id' => $bahanBaku->id,
                    'kuantitas' => $detail['kuantitas'],
                    'harga_satuan' => $bahanBaku->harga_satuan,
                    'subtotal' => $subtotal,
                    'keterangan' => $detail['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $totalBiaya += $subtotal;
            }

            // Hitung harga jual baru
            $persentaseKeuntungan = $request->persentase_keuntungan;
            $keuntungan = $totalBiaya * ($persentaseKeuntungan / 100);
            $hargaJual = $totalBiaya + $keuntungan;

            // Update BOM
            $bom->update([
                'total_biaya' => $totalBiaya,
                'persentase_keuntungan' => $persentaseKeuntungan,
                'harga_jual' => $hargaJual,
                'catatan' => $request->catatan
            ]);

            // Simpan detail baru
            $bom->details()->createMany($details);

            // Update harga jual produk
            $produk = $bom->produk;
            $produk->update(['harga_jual' => $hargaJual]);

            DB::commit();

            return redirect()
                ->route('master-data.bom.show', $bom->id)
                ->with('success', 'BOM berhasil diperbarui. Harga jual produk telah disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $bom = Bom::findOrFail($id);
        $produk_id = $bom->produk_id;
        
        DB::beginTransaction();
        
        try {
            $bom->details()->delete();
            $bom->delete();
            
            DB::commit();
            
            return redirect()
                ->route('master-data.produk.show', $produk_id)
                ->with('success', 'BOM berhasil dihapus.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal menghapus BOM: ' . $e->getMessage());
        }
    }
    
    public function generateKodeBom()
    {
        $prefix = 'BOM-' . date('Ym') . '-';
        $latest = Bom::where('kode_bom', 'like', $prefix . '%')
            ->orderBy('kode_bom', 'desc')
            ->first();
            
        $number = $latest ? (int) substr($latest->kode_bom, strlen($prefix)) + 1 : 1;
        
        return response()->json([
            'kode_bom' => $prefix . str_pad($number, 4, '0', STR_PAD_LEFT)
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

        // BTKL/BOPB: gunakan field produk jika ada, jika tidak pakai input/fallback persen dari total bahan
        $btklRate = (float) (config('app.btkl_percent') ?? 0.2);
        $bopRate  = (float) (config('app.bop_percent') ?? 0.1);

        if (!is_null($produk->btkl_per_unit)) {
            $btkl_sum = (float) $produk->btkl_per_unit;
        } else {
            $btkl_sum = $request->filled('btkl') ? (float)$request->btkl : ($total_bahan * $btklRate);
        }

        if ($produk->bopb_method && $produk->bopb_rate) {
            $method = strtolower($produk->bopb_method);
            if ($method === 'per_unit') {
                $bop_sum = (float) $produk->bopb_rate;
            } elseif ($method === 'per_hour') {
                $hours = (float) ($produk->labor_hours_per_unit ?? 0);
                $bop_sum = (float) $produk->bopb_rate * $hours;
            } else {
                $bop_sum = $total_bahan * $bopRate;
            }
        } else {
            $bop_sum  = $request->filled('bop')  ? (float)$request->bop  : ($total_bahan * $bopRate);
        }

        $grand_total = $total_bahan + $btkl_sum + $bop_sum;
        $margin = (float) ($produk->margin_percent ?? 0);
        $harga_jual = $grand_total * (1 + $margin/100);

        $produk->update([
            'harga_jual' => $harga_jual,
            'btkl_default' => $btkl_sum,
            'bop_default' => $bop_sum,
        ]);

        return redirect()->route('master-data.bom.index', ['produk_id' => $produk->id])
            ->with('success', 'BOM berhasil diperbarui.');
    }
}

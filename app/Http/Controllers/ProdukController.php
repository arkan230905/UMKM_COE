<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\PenjualanDetail;
use App\Models\Produksi;
use App\Models\Bom;
use App\Models\StockLayer;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Support\UnitConverter;

class ProdukController extends Controller
{
    public function index()
    {
        // Get all products with necessary columns
        $produks = Produk::select([
            'id', 
            'nama_produk', 
            'deskripsi',
            'foto',
            'harga_bom',
            'harga_jual', 
            'margin_percent',
            'stok'
        ])->get();
        
        // If any product has null harga_bom, calculate it from BOM
        if ($produks->contains('harga_bom', null)) {
            $produksWithBom = Produk::whereNull('harga_bom')
                ->with(['boms' => function($query) {
                    $query->select(['id', 'produk_id', 'bahan_baku_id', 'jumlah', 'satuan_resep']);
                }, 'boms.bahanBaku' => function($query) {
                    $query->select(['id', 'harga_satuan', 'satuan']);
                }])
                ->get();
            
            $converter = new UnitConverter();
            
            foreach ($produksWithBom as $produk) {
                $totalBiayaBahan = 0;
                
                // Calculate material costs from BOM
                foreach ($produk->boms as $bom) {
                    if (!$bom->bahanBaku) continue;
                    
                    $qtyResep = (float) $bom->jumlah;
                    $satuanResep = $bom->satuan_resep ?: $bom->bahanBaku->satuan;
                    $hargaSatuan = (float) $bom->bahanBaku->harga_satuan;
                    
                    try {
                        $qtyBase = $converter->convert($qtyResep, $satuanResep, $bom->bahanBaku->satuan);
                        $totalBiayaBahan += $hargaSatuan * $qtyBase;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                // Update the product's harga_bom
                $produk->update([
                    'harga_bom' => $totalBiayaBahan,
                    'harga_jual' => $produk->harga_jual ?? $totalBiayaBahan * (1 + (($produk->margin_percent ?? 30) / 100))
                ]);
                
                // Update the harga_bom in the collection
                if ($p = $produks->where('id', $produk->id)->first()) {
                    $p->harga_bom = $totalBiayaBahan;
                }
            }
        }
        
        // Prepare data for the view
        $hargaBom = $produks->pluck('harga_bom', 'id')->toArray();
        
        return view('master-data.produk.index', compact('produks', 'hargaBom'));
    }

    public function katalogPelanggan()
    {
        $produks = Produk::select([
            'id',
            'nama_produk',
            'foto',
            'deskripsi',
            'harga_jual',
        ])->whereNotNull('harga_jual')->get();

        return view('pelanggan.produk.index', compact('produks'));
    }

    public function create()
    {
        return view('master-data.produk.create');
    }
    
    public function show($id)
    {
        $produk = Produk::with(['boms.bahanBaku'])->findOrFail($id);
        
        // Hitung total biaya BOM
        $totalBiayaBom = 0;
        $converter = new UnitConverter();
        
        foreach ($produk->boms as $bom) {
            $bahanBaku = $bom->bahanBaku;
            if ($bahanBaku) {
                $qtyInKg = $converter->convert(
                    $bom->jumlah, 
                    $bom->satuan_resep ?? $bahanBaku->satuan, 
                    'kg'
                );
                $totalBiayaBom += $bahanBaku->harga_satuan * $qtyInKg;
            }
        }
        
        // Hitung harga jual berdasarkan margin
        $hargaJual = $totalBiayaBom * (1 + ($produk->margin_percent / 100));
        
        return view('master-data.produk.show', [
            'produk' => $produk,
            'totalBiayaBom' => $totalBiayaBom,
            'hargaJual' => $hargaJual
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'margin_percent' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('produk', 'public');
        }

        // Harga jual kosong dulu; akan dihitung dari BOM
        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'harga_jual' => null,
            'margin_percent' => $request->input('margin_percent'),
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
        ]);

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan. Silakan tambahkan BOM.');
    }

    public function edit(Produk $produk)
    {
        return view('master-data.produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'margin_percent' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'margin_percent' => $request->input('margin_percent'),
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Produk $produk)
    {
        // Cegah hapus jika masih dipakai transaksi atau master terkait
        $blockers = [];
        $pd = PenjualanDetail::where('produk_id', $produk->id)->count();
        if ($pd > 0) $blockers[] = "Dipakai di Penjualan (".$pd.")";
        $pr = Produksi::where('produk_id', $produk->id)->count();
        if ($pr > 0) $blockers[] = "Dipakai di Produksi (".$pr.")";
        $bm = Bom::where('produk_id', $produk->id)->count();
        if ($bm > 0) $blockers[] = "Memiliki BOM (".$bm.")";
        $sl = StockLayer::where('item_type','product')->where('item_id',$produk->id)->count();
        if ($sl > 0) $blockers[] = "Memiliki Stock Layer (".$sl.")";
        $sm = StockMovement::where('item_type','product')->where('item_id',$produk->id)->count();
        if ($sm > 0) $blockers[] = "Memiliki Mutasi Stok (".$sm.")";

        if (!empty($blockers)) {
            return redirect()->route('master-data.produk.index')
                ->with('error', 'Produk tidak dapat dihapus karena masih terkait: '.implode(', ', $blockers).'. Hapus/arsipkan transaksi terkait terlebih dahulu.');
        }

        $produk->delete();
        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil dihapus.');
    }
}

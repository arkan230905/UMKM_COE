<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Retur;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\BahanBaku;
use App\Models\Coa;
use App\Services\ReturService;

class ReturControllerNew extends Controller
{
    protected $returService;

    public function __construct(ReturService $returService)
    {
        $this->returService = $returService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Retur::with(['details', 'kompensasis', 'creator']);

        // Filter berdasarkan tipe
        if ($request->has('tipe_retur') && $request->tipe_retur != '') {
            $query->where('tipe_retur', $request->tipe_retur);
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        $returs = $query->orderBy('tanggal', 'desc')->paginate(20);

        return view('transaksi.retur.index', compact('returs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $produks = Produk::all();
        $bahanBakus = BahanBaku::all();
        $pembelians = Pembelian::where('status', 'lunas')->orWhere('status', 'belum_lunas')->get();
        $penjualans = Penjualan::where('status', 'lunas')->orWhere('status', 'belum_lunas')->get();
        $akunKas = Coa::where('kategori', 'Kas & Bank')->get();

        return view('transaksi.retur.create', compact('produks', 'bahanBakus', 'pembelians', 'penjualans', 'akunKas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'tipe_retur' => 'required|in:penjualan,pembelian',
            'tipe_kompensasi' => 'required|in:barang,uang',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:produk,bahan_baku',
            'items.*.item_id' => 'required|integer',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        try {
            $data = $request->all();

            // Proses berdasarkan tipe retur dan kompensasi
            if ($request->tipe_retur === 'penjualan') {
                if ($request->tipe_kompensasi === 'barang') {
                    $result = $this->returService->prosesReturPenjualanBarang($data);
                } else {
                    $result = $this->returService->prosesReturPenjualanUang($data);
                }
            } else {
                if ($request->tipe_kompensasi === 'barang') {
                    $result = $this->returService->prosesReturPembelianBarang($data);
                } else {
                    $result = $this->returService->prosesReturPembelianUang($data);
                }
            }

            if ($result['success']) {
                return redirect()->route('transaksi.retur.show', $result['retur']->id)
                    ->with('success', 'Retur berhasil diproses!');
            } else {
                return back()->withInput()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Retur $retur)
    {
        $retur->load(['details', 'kompensasis', 'jurnalEntries.journalLines.coa', 'creator']);

        return view('transaksi.retur.show', compact('retur'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Retur $retur)
    {
        // Hanya bisa edit jika status masih draft
        if ($retur->status !== 'draft') {
            return redirect()->route('transaksi.retur.show', $retur->id)
                ->with('error', 'Retur yang sudah diproses tidak dapat diedit');
        }

        $produks = Produk::all();
        $bahanBakus = BahanBaku::all();
        $akunKas = Coa::where('kategori', 'Kas & Bank')->get();

        return view('transaksi.retur.edit', compact('retur', 'produks', 'bahanBakus', 'akunKas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Retur $retur)
    {
        // Hanya bisa update jika status masih draft
        if ($retur->status !== 'draft') {
            return redirect()->route('transaksi.retur.show', $retur->id)
                ->with('error', 'Retur yang sudah diproses tidak dapat diubah');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string'
        ]);

        $retur->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('transaksi.retur.show', $retur->id)
            ->with('success', 'Retur berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Retur $retur)
    {
        // Hanya bisa delete jika status masih draft
        if ($retur->status !== 'draft') {
            return back()->with('error', 'Retur yang sudah diproses tidak dapat dihapus');
        }

        $retur->delete();

        return redirect()->route('transaksi.retur.index')
            ->with('success', 'Retur berhasil dihapus');
    }

    /**
     * Get data penjualan untuk retur
     */
    public function getPenjualanData($id)
    {
        $penjualan = Penjualan::with('details.produk')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $penjualan
        ]);
    }

    /**
     * Get data pembelian untuk retur
     */
    public function getPembelianData($id)
    {
        $pembelian = Pembelian::with('details.bahanBaku')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pembelian
        ]);
    }

    /**
     * Get data produk
     */
    public function getProdukData($id)
    {
        $produk = Produk::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'satuan' => $produk->satuan ?? 'pcs',
                'harga' => $produk->harga_jual ?? 0,
                'stok' => $produk->stok ?? 0
            ]
        ]);
    }

    /**
     * Get data bahan baku
     */
    public function getBahanBakuData($id)
    {
        $bahanBaku = BahanBaku::with('satuan')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $bahanBaku->id,
                'nama' => $bahanBaku->nama,
                'satuan' => $bahanBaku->satuan->nama ?? 'kg',
                'harga' => $bahanBaku->harga ?? 0,
                'stok' => $bahanBaku->stok ?? 0
            ]
        ]);
    }
}

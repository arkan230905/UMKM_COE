<?php

namespace App\Http\Controllers\PegawaiPembelian;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Satuan;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        // Ambil parameter tipe dari request (default: material)
        $tipe = request('tipe', 'material'); // material|bahan_pendukung
        
        // Initialize variables untuk stok tracking
        $saldoPerItem = [];
        
        try {
            // Query mutasi stok untuk tipe yang dipilih - SAMA SEPERTI LAPORAN CONTROLLER
            $movQ = \App\Models\StockMovement::query()->where('item_type', $tipe);
            
            // Build running saldo untuk setiap item - SAMA SEPERTI LAPORAN CONTROLLER
            $movements = $movQ->orderBy('tanggal', 'asc')
                             ->orderBy('id', 'asc')
                             ->get();
            
            // DEBUG: Log movements data
            \Log::info('StockMovement for ' . $tipe . ': ' . $movements->count() . ' records');
            foreach ($movements->take(3) as $m) {
                \Log::info('Movement: ID=' . $m->item_id . ', direction=' . $m->direction . ', qty=' . $m->qty . ', tanggal=' . $m->tanggal);
            }
            
            foreach ($movements as $m) {
                // Gunakan field 'direction' seperti di LaporanController
                $sign = $m->direction === 'in' ? 1 : -1;
                $saldoPerItem[$m->item_id] = ($saldoPerItem[$m->item_id] ?? 0) + ($sign * (float)$m->qty);
            }
            
            // DEBUG: Log calculated saldo
            \Log::info('Calculated saldo: ' . json_encode($saldoPerItem));
            
            // Jika tidak ada mutasi, gunakan stok dari master table - SAMA SEPERTI LAPORAN CONTROLLER
            // TANPA FILTER TANGGAL (karena pegawai pembelian tidak punya filter tanggal)
            if (empty($saldoPerItem)) {
                \Log::info('No movements found, using master table stok');
                if ($tipe == 'material') {
                    $materials = \App\Models\BahanBaku::with('satuan')->orderBy('nama_bahan', 'asc')->get();
                    foreach ($materials as $m) {
                        $saldoPerItem[$m->id] = (float)($m->stok ?? 0);
                        \Log::info('Master stok for ' . $m->nama_bahan . ': ' . $m->stok);
                    }
                } elseif ($tipe == 'bahan_pendukung') {
                    $bahanPendukungs = \App\Models\BahanPendukung::with('satuanRelation')->orderBy('nama_bahan', 'asc')->get();
                    foreach ($bahanPendukungs as $bp) {
                        $saldoPerItem[$bp->id] = (float)($bp->stok ?? 0);
                    }
                }
            }
            
            // SELALUUS GUNAKAN MASTER TABLE UNTUK DATA TERAKHIR - UNTUK KONSISTENSI DENGAN LAPORAN STOK
            if ($tipe == 'material') {
                $materials = \App\Models\BahanBaku::with('satuan')->orderBy('nama_bahan', 'asc')->get();
                foreach ($materials as $m) {
                    // Override dengan master table stok untuk data terupdate
                    $saldoPerItem[$m->id] = (float)($m->stok ?? 0);
                    \Log::info('Override with master stok for ' . $m->nama_bahan . ': ' . $m->stok);
                }
            } elseif ($tipe == 'bahan_pendukung') {
                $bahanPendukungs = \App\Models\BahanPendukung::with('satuanRelation')->orderBy('nama_bahan', 'asc')->get();
                foreach ($bahanPendukungs as $bp) {
                    // Override dengan master table stok untuk data terupdate
                    $saldoPerItem[$bp->id] = (float)($bp->stok ?? 0);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in pegawai pembelian stok: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memuat data stok: ' . $e->getMessage());
        }
        
        // Ambil data berdasarkan tipe dengan logic SAMA PERSIS dengan master-data
        if ($tipe == 'material') {
            $items = \App\Models\BahanBaku::with('satuan')->orderBy('nama_bahan', 'asc')->paginate(15);
            
            // Hitung harga rata-rata untuk setiap bahan baku (sama seperti master-data)
            $items->getCollection()->transform(function($item) use ($saldoPerItem) {
                // Sama seperti di laporan stok: $saldoPerItem[$m->id] ?? $m->stok ?? 0
                $item->current_stok = $saldoPerItem[$item->id] ?? $item->stok ?? 0;
                
                // Hitung harga rata-rata (sama seperti BahanBakuController)
                $averageHarga = $this->getAverageHargaSatuan($item->id);
                
                // Jika ada harga rata-rata, gunakan itu. Jika tidak, gunakan harga default
                if ($averageHarga > 0) {
                    $item->harga_satuan_display = $averageHarga;
                } else {
                    $item->harga_satuan_display = $item->harga_satuan;
                }
                
                \Log::info('Final stok for ' . $item->nama_bahan . ': ' . $item->current_stok);
                return $item;
            });
        } else {
            $items = \App\Models\BahanPendukung::with('satuanRelation')->orderBy('nama_bahan', 'asc')->paginate(15);
            
            // Hitung harga rata-rata untuk setiap bahan pendukung (sama seperti master-data)
            $items->getCollection()->transform(function($item) use ($saldoPerItem) {
                $item->current_stok = $saldoPerItem[$item->id] ?? $item->stok ?? 0;
                
                // Hitung harga rata-rata (sama seperti BahanPendukungController)
                $pembelianDetails = \App\Models\PembelianDetail::where('bahan_pendukung_id', $item->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                if ($pembelianDetails->count() > 0) {
                    // Calculate average price from pembelian details
                    $totalHarga = 0;
                    $totalQty = 0;
                    
                    foreach ($pembelianDetails as $detail) {
                        $totalHarga += ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                        $totalQty += ($detail->jumlah ?? 0);
                    }
                    
                    $avgPrice = $totalQty > 0 ? $totalHarga / $totalQty : 0;
                    $item->harga_satuan_display = $avgPrice;
                } else {
                    // Use default price if no pembelian details
                    $item->harga_satuan_display = $item->harga_satuan;
                }
                
                return $item;
            });
        }
        
        \Log::info('Returning to view with ' . $items->count() . ' items');
        return view('pegawai-pembelian.bahan-baku.index', compact('items', 'tipe'));
    }

    /**
     * Get average harga satuan untuk bahan baku (SAMA PERSIS dengan BahanBakuController)
     */
    private function getAverageHargaSatuan($bahanBakuId)
    {
        $bahanBaku = \App\Models\BahanBaku::findOrFail($bahanBakuId);
        
        // Ambil semua pembelian detail untuk bahan baku ini
        $details = \App\Models\PembelianDetail::where('bahan_baku_id', $bahanBakuId)
            ->with(['pembelian'])
            ->get();
        
        if ($details->isEmpty()) {
            return 0;
        }
        
        // Hitung total harga dan total quantity
        $totalHarga = 0;
        $totalQuantity = 0;
        
        foreach ($details as $detail) {
            $totalHarga += ($detail->harga_satuan ?? 0) * ($detail->jumlah ?? 0);
            $totalQuantity += ($detail->jumlah ?? 0);
        }
        
        // Hitung harga rata-rata
        $averageHarga = $totalQuantity > 0 ? $totalHarga / $totalQuantity : 0;
        
        return $averageHarga;
    }

    public function create()
    {
        $satuans = Satuan::all();
        return view('pegawai-pembelian.bahan-baku.create', compact('satuans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        BahanBaku::create($validated);

        return redirect()->route('pegawai-pembelian.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan!');
    }

    public function show($id)
    {
        $bahanBaku = BahanBaku::with('satuan')->findOrFail($id);
        return view('pegawai-pembelian.bahan-baku.show', compact('bahanBaku'));
    }

    public function edit($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $satuans = Satuan::all();
        return view('pegawai-pembelian.bahan-baku.edit', compact('bahanBaku', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->update($validated);

        return redirect()->route('pegawai-pembelian.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil diupdate!');
    }

    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->delete();

        return redirect()->route('pegawai-pembelian.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus!');
    }
}

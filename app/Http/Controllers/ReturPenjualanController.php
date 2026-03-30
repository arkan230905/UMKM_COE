<?php

namespace App\Http\Controllers;

use App\Models\ReturPenjualan;
use App\Models\DetailReturPenjualan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturPenjualanController extends Controller
{
    public function index()
    {
        $returPenjualans = ReturPenjualan::with(['penjualan', 'pelanggan', 'detailReturPenjualans.produk'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('transaksi.retur-penjualan.index', compact('returPenjualans'));
    }

    public function create()
    {
        $penjualans = Penjualan::with(['penjualanDetails.produk'])->get();
        $pelanggans = Pelanggan::all();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
            'kredit' => 'Kredit'
        ];

        $selectedPenjualanId = request('penjualan_id');

        return view('transaksi.retur-penjualan.create', compact('penjualans', 'pelanggans', 'jenisReturOptions', 'selectedPenjualanId'));
    }

    /**
     * Halaman detail retur dengan form input
     */
    public function detailRetur($penjualanId)
    {
        $penjualan = Penjualan::with(['penjualanDetails.produk'])->findOrFail($penjualanId);
        $pelanggans = Pelanggan::all();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
            'kredit' => 'Kredit'
        ];

        return view('transaksi.retur-penjualan.detail-retur', compact('penjualan', 'pelanggans', 'jenisReturOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'jenis_retur' => 'required|in:tukar_barang,refund,kredit',
            'tanggal' => 'required|date',
            'pelanggan_id' => 'required_if:jenis_retur,kredit|exists:pelanggans,id',
            'keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.penjualan_detail_id' => 'required|exists:penjualan_details,id',
            'details.*.qty_retur' => 'required|numeric|min:0.0001',
            'details.*.harga_barang' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Buat retur penjualan
            $returPenjualan = new ReturPenjualan();
            $returPenjualan->nomor_retur = $returPenjualan->generateNomorRetur();
            $returPenjualan->tanggal = $request->tanggal;
            $returPenjualan->penjualan_id = $request->penjualan_id;
            $returPenjualan->pelanggan_id = $request->jenis_retur === 'kredit' ? $request->pelanggan_id : null;
            $returPenjualan->jenis_retur = $request->jenis_retur;
            $returPenjualan->keterangan = $request->keterangan;
            $returPenjualan->save();

            // Buat detail retur penjualan
            foreach ($request->details as $detail) {
                $penjualanDetail = PenjualanDetail::find($detail['penjualan_detail_id']);
                
                // Validasi qty retur tidak melebihi qty penjualan
                if ($detail['qty_retur'] > $penjualanDetail->jumlah) {
                    throw new \Exception('Qty retur tidak boleh melebihi qty penjualan');
                }

                DetailReturPenjualan::create([
                    'retur_penjualan_id' => $returPenjualan->id,
                    'penjualan_detail_id' => $detail['penjualan_detail_id'],
                    'produk_id' => $penjualanDetail->produk_id,
                    'qty_retur' => $detail['qty_retur'],
                    'harga_barang' => $detail['harga_barang'],
                    'keterangan' => $detail['keterangan'] ?? null
                ]);
            }

            // Hitung total retur
            $returPenjualan->calculateTotalRetur();

            // Proses retur sesuai jenis
            $returPenjualan->processRetur();

            DB::commit();

            return redirect()->route('retur-penjualan.index')
                ->with('success', 'Retur penjualan berhasil dibuat dengan nomor: ' . $returPenjualan->nomor_retur);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(ReturPenjualan $returPenjualan)
    {
        $returPenjualan->load(['penjualan.penjualanDetails.produk', 'pelanggan', 'detailReturPenjualans.penjualanDetail.produk']);

        return view('transaksi.retur-penjualan.show', compact('returPenjualan'));
    }

    public function edit(ReturPenjualan $returPenjualan)
    {
        // Hanya retur dengan status 'belum_dibayar' yang bisa diedit
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('retur-penjualan.index')
                ->with('error', 'Retur tidak dapat diedit karena status sudah ' . $returPenjualan->status);
        }

        $returPenjualan->load(['detailReturPenjualans.penjualanDetail.produk']);
        $penjualans = Penjualan::with(['penjualanDetails.produk'])->get();
        $pelanggans = Pelanggan::all();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
            'kredit' => 'Kredit'
        ];

        return view('transaksi.retur-penjualan.edit', compact('returPenjualan', 'penjualans', 'pelanggans', 'jenisReturOptions'));
    }

    public function update(Request $request, ReturPenjualan $returPenjualan)
    {
        // Hanya retur dengan status 'belum_dibayar' yang bisa diedit
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('retur-penjualan.index')
                ->with('error', 'Retur tidak dapat diedit karena status sudah ' . $returPenjualan->status);
        }

        $request->validate([
            'jenis_retur' => 'required|in:tukar_barang,refund,kredit',
            'tanggal' => 'required|date',
            'pelanggan_id' => 'required_if:jenis_retur,kredit|exists:pelanggans,id',
            'keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.penjualan_detail_id' => 'required|exists:penjualan_details,id',
            'details.*.qty_retur' => 'required|numeric|min:0.0001',
            'details.*.harga_barang' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Update retur penjualan
            $returPenjualan->tanggal = $request->tanggal;
            $returPenjualan->pelanggan_id = $request->jenis_retur === 'kredit' ? $request->pelanggan_id : null;
            $returPenjualan->jenis_retur = $request->jenis_retur;
            $returPenjualan->keterangan = $request->keterangan;
            $returPenjualan->save();

            // Hapus detail lama
            $returPenjualan->detailReturPenjualans()->delete();

            // Buat detail baru
            foreach ($request->details as $detail) {
                $penjualanDetail = PenjualanDetail::find($detail['penjualan_detail_id']);
                
                // Validasi qty retur tidak melebihi qty penjualan
                if ($detail['qty_retur'] > $penjualanDetail->jumlah) {
                    throw new \Exception('Qty retur tidak boleh melebihi qty penjualan');
                }

                DetailReturPenjualan::create([
                    'retur_penjualan_id' => $returPenjualan->id,
                    'penjualan_detail_id' => $detail['penjualan_detail_id'],
                    'produk_id' => $penjualanDetail->produk_id,
                    'qty_retur' => $detail['qty_retur'],
                    'harga_barang' => $detail['harga_barang'],
                    'keterangan' => $detail['keterangan'] ?? null
                ]);
            }

            // Hitung total retur
            $returPenjualan->calculateTotalRetur();

            // Proses retur sesuai jenis
            $returPenjualan->processRetur();

            DB::commit();

            return redirect()->route('retur-penjualan.index')
                ->with('success', 'Retur penjualan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(ReturPenjualan $returPenjualan)
    {
        // Hanya retur dengan status 'belum_dibayar' yang bisa dihapus
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('retur-penjualan.index')
                ->with('error', 'Retur tidak dapat dihapus karena status sudah ' . $returPenjualan->status);
        }

        try {
            DB::beginTransaction();

            // Hapus detail retur
            $returPenjualan->detailReturPenjualans()->delete();
            
            // Hapus retur
            $returPenjualan->delete();

            DB::commit();

            return redirect()->route('retur-penjualan.index')
                ->with('success', 'Retur penjualan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('retur-penjualan.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getPenjualanDetails($penjualanId)
    {
        $penjualan = Penjualan::with(['penjualanDetails.produk'])->find($penjualanId);
        
        if (!$penjualan) {
            return response()->json(['error' => 'Penjualan tidak ditemukan'], 404);
        }

        $details = $penjualan->penjualanDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'produk_nama' => $detail->produk->nama_produk,
                'jumlah' => $detail->jumlah,
                'harga_satuan' => $detail->harga_satuan,
                'subtotal' => $detail->subtotal
            ];
        });

        return response()->json($details);
    }

    public function laporan()
    {
        $returPenjualans = ReturPenjualan::with(['penjualan', 'pelanggan', 'detailReturPenjualans.produk'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaksi.retur-penjualan.laporan', compact('returPenjualans'));
    }

    public function bayarKredit(ReturPenjualan $returPenjualan)
    {
        if ($returPenjualan->jenis_retur !== 'kredit') {
            return redirect()->route('retur-penjualan.index')
                ->with('error', 'Hanya retur kredit yang dapat dibayar');
        }

        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('retur-penjualan.index')
                ->with('error', 'Retur sudah dibayar');
        }

        try {
            DB::beginTransaction();

            // Update status menjadi lunas
            $returPenjualan->status = 'lunas';
            $returPenjualan->save();

            // Catat pembayaran ke jurnal
            JournalEntry::create([
                'tanggal' => now(),
                'keterangan' => 'Pelunasan Retur Penjualan - ' . $returPenjualan->nomor_retur,
                'total_debit' => $returPenjualan->total_retur,
                'total_kredit' => $returPenjualan->total_retur
            ]);

            DB::commit();

            return redirect()->route('retur-penjualan.index')
                ->with('success', 'Retur kredit berhasil dilunasi');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('retur-penjualan.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

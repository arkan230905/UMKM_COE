<?php

namespace App\Http\Controllers;

use App\Models\ReturPenjualan;
use App\Models\DetailReturPenjualan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\User;
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
        $pelanggans = User::where('role', 'pelanggan')->get();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
        ];

        $selectedPenjualanId = request('penjualan_id');

        return view('transaksi.retur-penjualan.create', compact('penjualans', 'pelanggans', 'jenisReturOptions', 'selectedPenjualanId'));
    }

    public function detailRetur($penjualanId)
    {
        $penjualan = Penjualan::with(['penjualanDetails.produk'])->findOrFail($penjualanId);
        $pelanggans = User::where('role', 'pelanggan')->get();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
        ];

        return view('transaksi.retur-penjualan.detail-retur', compact('penjualan', 'pelanggans', 'jenisReturOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'jenis_retur' => 'required|in:tukar_barang,refund',
            'tanggal' => 'required|date',
            'pelanggan_id' => 'nullable|exists:users,id',
            'keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.penjualan_detail_id' => 'required|exists:penjualan_details,id',
            'details.*.qty_retur' => 'required|numeric|min:0.0001',
            'details.*.harga_barang' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $returPenjualan = new ReturPenjualan();
            $returPenjualan->nomor_retur = $returPenjualan->generateNomorRetur();
            $returPenjualan->tanggal = $request->tanggal;
            $returPenjualan->penjualan_id = $request->penjualan_id;
            $returPenjualan->pelanggan_id = $request->pelanggan_id ?? null;
            $returPenjualan->jenis_retur = $request->jenis_retur;
            $returPenjualan->keterangan = $request->keterangan;
            $returPenjualan->save();

            foreach ($request->details as $detail) {
                $penjualanDetail = PenjualanDetail::find($detail['penjualan_detail_id']);

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

            $returPenjualan->calculateTotalRetur();
            $returPenjualan->processRetur();

            DB::commit();

            return redirect()->route('transaksi.retur-penjualan.index')
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
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('error', 'Retur tidak dapat diedit karena status sudah ' . $returPenjualan->status);
        }

        $returPenjualan->load(['detailReturPenjualans.penjualanDetail.produk']);
        $penjualans = Penjualan::with(['penjualanDetails.produk'])->get();
        $pelanggans = User::where('role', 'pelanggan')->get();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
        ];

        return view('transaksi.retur-penjualan.edit', compact('returPenjualan', 'penjualans', 'pelanggans', 'jenisReturOptions'));
    }

    public function update(Request $request, ReturPenjualan $returPenjualan)
    {
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('error', 'Retur tidak dapat diedit karena status sudah ' . $returPenjualan->status);
        }

        $request->validate([
            'jenis_retur' => 'required|in:tukar_barang,refund',
            'tanggal' => 'required|date',
            'pelanggan_id' => 'nullable|exists:users,id',
            'keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.penjualan_detail_id' => 'required|exists:penjualan_details,id',
            'details.*.qty_retur' => 'required|numeric|min:0.0001',
            'details.*.harga_barang' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $returPenjualan->tanggal = $request->tanggal;
            $returPenjualan->pelanggan_id = $request->pelanggan_id ?? null;
            $returPenjualan->jenis_retur = $request->jenis_retur;
            $returPenjualan->keterangan = $request->keterangan;
            $returPenjualan->save();

            $returPenjualan->detailReturPenjualans()->delete();

            foreach ($request->details as $detail) {
                $penjualanDetail = PenjualanDetail::find($detail['penjualan_detail_id']);

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

            $returPenjualan->calculateTotalRetur();
            $returPenjualan->processRetur();

            DB::commit();

            return redirect()->route('transaksi.retur-penjualan.index')
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
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('error', 'Retur tidak dapat dihapus karena status sudah ' . $returPenjualan->status);
        }

        try {
            DB::beginTransaction();

            $returPenjualan->detailReturPenjualans()->delete();
            $returPenjualan->delete();

            DB::commit();

            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('success', 'Retur penjualan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('transaksi.retur-penjualan.index')
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
}

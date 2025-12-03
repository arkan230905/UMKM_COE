<?php

namespace App\Http\Controllers\PegawaiPembelian;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelians = Pembelian::with('vendor')->latest('tanggal')->paginate(15);
        return view('pegawai-pembelian.pembelian.index', compact('pembelians'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::with('satuan')->get();
        return view('pegawai-pembelian.pembelian.create', compact('vendors', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'tanggal' => 'required|date',
            'payment_method' => 'required|in:cash,credit,transfer',
            'keterangan' => 'nullable|string',
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:0.01',
            'harga_satuan' => 'required|array',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Hitung total
            $totalHarga = 0;
            foreach ($request->bahan_baku_id as $index => $bahanBakuId) {
                $subtotal = $request->jumlah[$index] * $request->harga_satuan[$index];
                $totalHarga += $subtotal;
            }

            // Buat pembelian
            $pembelian = Pembelian::create([
                'vendor_id' => $validated['vendor_id'],
                'tanggal' => $validated['tanggal'],
                'total_harga' => $totalHarga,
                'payment_method' => $validated['payment_method'],
                'terbayar' => $validated['payment_method'] === 'cash' ? $totalHarga : 0,
                'sisa_pembayaran' => $validated['payment_method'] === 'cash' ? 0 : $totalHarga,
                'status' => $validated['payment_method'] === 'cash' ? 'lunas' : 'belum_lunas',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Buat detail pembelian dan update stok
            foreach ($request->bahan_baku_id as $index => $bahanBakuId) {
                $bahanBaku = BahanBaku::find($bahanBakuId);
                $jumlah = $request->jumlah[$index];
                $hargaSatuan = $request->harga_satuan[$index];
                $subtotal = $jumlah * $hargaSatuan;

                // Simpan detail
                PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'bahan_baku_id' => $bahanBakuId,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'satuan' => $bahanBaku->satuan->nama_satuan ?? null,
                ]);

                // Update stok bahan baku
                $bahanBaku->increment('stok', $jumlah);
            }

            DB::commit();

            return redirect()->route('pegawai-pembelian.pembelian.index')
                ->with('success', 'Pembelian berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat pembelian: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['vendor', 'pembelianDetails.bahanBaku'])->findOrFail($id);
        return view('pegawai-pembelian.pembelian.show', compact('pembelian'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::with('pembelianDetails')->findOrFail($id);

            // Kembalikan stok bahan baku
            foreach ($pembelian->pembelianDetails as $detail) {
                $bahanBaku = BahanBaku::find($detail->bahan_baku_id);
                if ($bahanBaku) {
                    $bahanBaku->decrement('stok', $detail->jumlah);
                }
            }

            // Hapus detail dan pembelian
            $pembelian->pembelianDetails()->delete();
            $pembelian->delete();

            DB::commit();

            return redirect()->route('pegawai-pembelian.pembelian.index')
                ->with('success', 'Pembelian berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pembelian: ' . $e->getMessage());
        }
    }
}

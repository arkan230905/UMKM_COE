<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\StockMovement;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use App\Models\Pembelian as PembelianModel;

class LaporanController extends Controller
{
    // === LAPORAN PEMBELIAN ===
    public function pembelian()
    {
        $pembelian = Pembelian::with('vendor')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('laporan.pembelian.index', compact('pembelian'));
    }

    // === LAPORAN PENJUALAN ===
    public function penjualan()
    {
        $penjualan = Penjualan::with('produk')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('laporan.penjualan.index', compact('penjualan'));
    }

    // === LAPORAN STOK ===
    public function laporanStok(Request $request)
    {
        $tipe = $request->get('tipe', 'material'); // material|product
        $from = $request->get('from');
        $to   = $request->get('to');
        $itemId = $request->get('item_id');

        // Daftar item untuk dropdown
        $materials = BahanBaku::orderBy('nama_bahan', 'asc')->get();
        $products  = Produk::orderBy('nama_produk', 'asc')->get();

        // Query mutasi dalam periode (untuk kartu stok spesifik item jika item dipilih)
        $movQ = StockMovement::query()->where('item_type', $tipe);
        if ($itemId) { $movQ->where('item_id', $itemId); }
        if ($from) { $movQ->whereDate('tanggal', '>=', $from); }
        if ($to)   { $movQ->whereDate('tanggal', '<=', $to); }
        $movements = $movQ->orderBy('tanggal', 'asc')->orderBy('id','asc')->get();

        // Saldo awal: akumulasi semua mutasi sebelum 'from'
        $saldoAwalQty = 0.0; $saldoAwalNilai = 0.0;
        if ($from && $itemId) {
            $before = StockMovement::where('item_type', $tipe)
                ->where('item_id', $itemId)
                ->whereDate('tanggal', '<', $from)
                ->orderBy('tanggal','asc')
                ->get();
            foreach ($before as $m) {
                if ($m->direction === 'in') {
                    $saldoAwalQty += (float)$m->qty;
                    $saldoAwalNilai += (float)($m->total_cost ?? 0);
                } else {
                    $saldoAwalQty -= (float)$m->qty;
                    $saldoAwalNilai -= (float)($m->total_cost ?? 0);
                }
            }
        }

        // Build running saldo untuk tampilan kartu stok
        $running = [];
        $qty = $saldoAwalQty; $nilai = $saldoAwalNilai;
        foreach ($movements as $m) {
            $inQty = 0; $inNilai = 0; $outQty = 0; $outNilai = 0;
            if ($m->direction === 'in') {
                $inQty = (float)$m->qty; $inNilai = (float)($m->total_cost ?? 0);
                $qty += $inQty; $nilai += $inNilai;
            } else {
                $outQty = (float)$m->qty; $outNilai = (float)($m->total_cost ?? 0);
                $qty -= $outQty; $nilai -= $outNilai;
            }
            $running[] = [
                'tanggal' => $m->tanggal,
                'ref' => ($m->ref_type.'#'.$m->ref_id),
                'in_qty' => $inQty,
                'in_nilai' => $inNilai,
                'out_qty' => $outQty,
                'out_nilai' => $outNilai,
                'saldo_qty' => $qty,
                'saldo_nilai' => $nilai,
                'satuan' => $m->satuan,
            ];
        }

        // Untuk tampilan lama (ringkasan saldo per item) bila item belum dipilih
        $saldoPerItem = [];
        if (!$itemId) {
            $allQ = StockMovement::where('item_type', $tipe);
            if ($from) { $allQ->whereDate('tanggal', '>=', $from); }
            if ($to)   { $allQ->whereDate('tanggal', '<=', $to); }
            $all = $allQ->get();
            foreach ($all as $m) {
                $sign = $m->direction === 'in' ? 1 : -1;
                $saldoPerItem[$m->item_id] = ($saldoPerItem[$m->item_id] ?? 0) + ($sign * (float)$m->qty);
            }
        }

        return view('laporan.stok.index', compact('tipe', 'from', 'to', 'itemId', 'movements', 'materials', 'products', 'saldoPerItem', 'saldoAwalQty', 'saldoAwalNilai', 'running'));
    }

    // === INVOICE PEMBELIAN (PRINTABLE) ===
    public function invoicePembelian($id)
    {
        $pembelian = PembelianModel::with(['vendor', 'details.bahanBaku'])->findOrFail($id);
        return view('laporan.pembelian.invoice', compact('pembelian'));
    }

    // === INVOICE PENJUALAN (PRINTABLE) ===
    public function invoicePenjualan($id)
    {
        $penjualan = Penjualan::with(['produk','details.produk'])->findOrFail($id);
        return view('laporan.penjualan.invoice', compact('penjualan'));
    }
}

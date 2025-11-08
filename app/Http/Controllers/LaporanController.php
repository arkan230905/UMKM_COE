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
    public function pembelian(Request $request)
    {
        $query = $this->getPembelianQuery($request);
        $pembelian = $query->paginate(15);
        $vendors = \App\Models\Vendor::all();

        return view('laporan.pembelian.index', compact('pembelian', 'vendors'));
    }

    // === LAPORAN STOK ===
    public function stok(Request $request)
    {
        try {
            // Query dasar untuk produk
            $query = Produk::query();
            
            // Filter berdasarkan stok minimum jika ada
            if ($request->has('min_stock') && $request->min_stock !== '') {
                $query->where('stok', '<=', (int)$request->min_stock);
            }
            
            // Ambil data produk dengan urutan nama produk
            $produk = $query->orderBy('nama_produk')->get();
            
            // Data dummy untuk kategori (karena tabel kategori_produks belum ada)
            $kategoris = collect([
                (object)['id' => 1, 'nama' => 'Makanan'],
                (object)['id' => 2, 'nama' => 'Minuman'],
                (object)['id' => 3, 'nama' => 'Snack']
            ]);
            
            return view('laporan.stok.index', [
                'produk' => $produk,
                'kategoris' => $kategoris
            ]);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in stok method: ' . $e->getMessage());
            
            // Return a simple response with the error message
            return response()->view('errors.500', [
                'message' => 'Terjadi kesalahan saat memuat data stok. Silakan coba lagi nanti.'
            ], 500);
        }
    }
    
    // === EKSPOR LAPORAN PEMBELIAN ===
    public function exportPembelian(Request $request)
    {
        $query = $this->getPembelianQuery($request);
        $pembelian = $query->get();
        $total = $pembelian->sum('total');
        
        $filename = 'laporan-pembelian-' . now()->format('Y-m-d') . '.xlsx';
        
        return response()->streamDownload(function() use ($pembelian, $total) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'No', 'No. Transaksi', 'Tanggal', 'Vendor', 
                'Keterangan', 'Total (Rp)'
            ]);
            
            // Data
            foreach ($pembelian as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $item->no_pembelian,
                    $item->tanggal->format('d/m/Y'),
                    $item->vendor->nama_vendor ?? '-',
                    $item->keterangan ?? '-',
                    number_format($item->total, 0, ',', '.')
                ]);
            }
            
            // Total
            fputcsv($handle, ['', '', '', '', 'TOTAL', number_format($total, 0, ',', '.')]);
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    // Helper method untuk query pembelian
    private function getPembelianQuery(Request $request)
    {
        $query = Pembelian::with(['vendor', 'details.bahanBaku'])
            ->orderBy('tanggal', 'desc');
            
        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }
        
        // Filter berdasarkan vendor
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        return $query;
    }

    // === LAPORAN PENJUALAN ===
    public function penjualan()
    {
        $penjualan = Penjualan::with('produk')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('laporan.penjualan.index', compact('penjualan'));
    }

    // === LAPORAN RETUR ===
    public function laporanRetur(Request $request)
    {
        $query = \App\Models\Retur::with(['penjualan', 'customer'])
            ->when($request->bulan, function($q) use ($request) {
                $bulan = \Carbon\Carbon::parse($request->bulan);
                return $q->whereYear('tanggal', $bulan->year)
                       ->whereMonth('tanggal', $bulan->month);
            })
            ->selectRaw('returs.*, (SELECT SUM(dr.jumlah * dr.harga) FROM detail_retur dr WHERE dr.retur_id = returs.id) as total_retur')
            ->latest();

        if ($request->has('export') && $request->export == 'pdf') {
            $returs = $query->get();
            $total = $returs->sum('total_retur');
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.retur.pdf', compact('returs', 'total'));
            return $pdf->download('laporan-retur-' . now()->format('Y-m-d') . '.pdf');
        }

        $returs = $query->paginate(15);
        $total = $query->get()->sum('total_retur');

        return view('laporan.retur.index', compact('returs', 'total'));
    }

    // === LAPORAN PENGAJIAN ===
    public function laporanPenggajian(Request $request)
    {
        $query = \App\Models\Penggajian::with(['pegawai', 'detailGaji'])
            ->when($request->bulan, function($q) use ($request) {
                $bulan = \Carbon\Carbon::parse($request->bulan);
                return $q->whereYear('periode', $bulan->year)
                       ->whereMonth('periode', $bulan->month);
            })
            ->latest();

        if ($request->has('export') && $request->export == 'pdf') {
            $penggajians = $query->get();
            $total = $penggajians->sum('total_gaji');
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.penggajian.pdf', compact('penggajians', 'total'));
            return $pdf->download('laporan-penggajian-' . now()->format('Y-m-d') . '.pdf');
        }

        $penggajians = $query->paginate(15);
        $total = $query->sum('total_gaji');

        return view('laporan.penggajian.index', compact('penggajians', 'total'));
    }

    // === LAPORAN PEMBAYARAN BEBAN ===
    public function laporanPembayaranBeban(Request $request)
    {
        $query = \App\Models\ExpensePayment::with(['coa'])
            ->when($request->bulan, function($q) use ($request) {
                $bulan = \Carbon\Carbon::parse($request->bulan);
                return $q->whereYear('tanggal', $bulan->year)
                       ->whereMonth('tanggal', $bulan->month);
            })
            ->latest();

        if ($request->has('export') && $request->export == 'pdf') {
            $pembayaranBeban = $query->get();
            $total = $pembayaranBeban->sum('nominal');
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pembayaran-beban.pdf', compact('pembayaranBeban', 'total'));
            return $pdf->download('laporan-pembayaran-beban-' . now()->format('Y-m-d') . '.pdf');
        }

        $pembayaranBeban = $query->paginate(15);
        $total = $query->sum('nominal');

        return view('laporan.pembayaran-beban.index', compact('pembayaranBeban', 'total'));
    }

    // === LAPORAN PELUNASAN UTANG ===
    public function laporanPelunasanUtang(Request $request)
    {
        $query = \App\Models\ApSettlement::with(['pembelian', 'vendor'])
            ->when($request->bulan, function($q) use ($request) {
                $bulan = \Carbon\Carbon::parse($request->bulan);
                return $q->whereYear('tanggal', $bulan->year)
                       ->whereMonth('tanggal', $bulan->month);
            })
            ->select('ap_settlements.*', 'pembelians.total_harga', 'pembelians.diskon', 'pembelians.ppn')
            ->join('pembelians', 'pembelians.id', '=', 'ap_settlements.pembelian_id')
            ->selectRaw('ap_settlements.*, (pembelians.total_harga + (pembelians.total_harga * pembelians.ppn / 100) - pembelians.diskon) as total_bayar')
            ->latest();

        if ($request->has('export') && $request->export == 'pdf') {
            $pelunasanUtang = $query->get();
            $total = $pelunasanUtang->sum('total_bayar');
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pelunasan-utang.pdf', compact('pelunasanUtang', 'total'));
            return $pdf->download('laporan-pelunasan-utang-' . now()->format('Y-m-d') . '.pdf');
        }

        $pelunasanUtang = $query->paginate(15);
        $total = $query->get()->sum('total_bayar');

        return view('laporan.pelunasan-utang.index', compact('pelunasanUtang', 'total'));
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

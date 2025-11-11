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
        
        // Calculate totals before pagination
        $totalPembelian = $query->sum('total_harga');
        $totalTransaksi = $query->count();
        
        $pembelian = $query->paginate(15);
        $vendors = \App\Models\Vendor::all();

        return view('laporan.pembelian.index', compact('pembelian', 'vendors', 'totalPembelian', 'totalTransaksi'));
    }

    // === LAPORAN STOK ===
    public function stok(Request $request)
    {
        $tipe = $request->get('tipe', 'material'); // material|product
        $from = $request->get('from');
        $to = $request->get('to');
        $itemId = $request->get('item_id');

        // Daftar item untuk dropdown
        $materials = BahanBaku::with('satuan')->orderBy('nama_bahan', 'asc')->get();
        $products = Produk::with('satuan')->orderBy('nama_produk', 'asc')->get();

        // Initialize variables
        $movements = collect();
        $saldoAwalQty = 0.0;
        $saldoAwalNilai = 0.0;
        $running = [];
        $saldoPerItem = [];

        try {
            // Query mutasi dalam periode (untuk kartu stok spesifik item jika item dipilih)
            $movQ = StockMovement::query()->where('item_type', $tipe);
            
            if ($itemId) { 
                $movQ->where('item_id', $itemId); 
                
                // Calculate initial balance for the selected item
                if ($from) {
                    $before = StockMovement::where('item_type', $tipe)
                        ->where('item_id', $itemId)
                        ->whereDate('tanggal', '<', $from)
                        ->orderBy('tanggal', 'asc')
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
            }
            
            if ($from) { 
                $movQ->whereDate('tanggal', '>=', $from); 
            }
            if ($to) {   
                $movQ->whereDate('tanggal', '<=', $to); 
            }
            
            $movements = $movQ->orderBy('tanggal', 'asc')
                             ->orderBy('id', 'asc')
                             ->get();

            // Build running saldo untuk tampilan kartu stok
            $qty = $saldoAwalQty; 
            $nilai = $saldoAwalNilai;
            
            foreach ($movements as $m) {
                $inQty = 0; $inNilai = 0; $outQty = 0; $outNilai = 0;
                if ($m->direction === 'in') {
                    $inQty = (float)$m->qty; 
                    $inNilai = (float)($m->total_cost ?? 0);
                    $qty += $inQty; 
                    $nilai += $inNilai;
                } else {
                    $outQty = (float)$m->qty; 
                    $outNilai = (float)($m->total_cost ?? 0);
                    $qty -= $outQty; 
                    $nilai -= $outNilai;
                }
                
                $running[] = [
                    'tanggal' => $m->tanggal,
                    'ref' => ($m->ref_type . '#' . $m->ref_id),
                    'in_qty' => $inQty,
                    'in_nilai' => $inNilai,
                    'out_qty' => $outQty,
                    'out_nilai' => $outNilai,
                    'saldo_qty' => $qty,
                    'saldo_nilai' => $nilai,
                    'satuan' => $m->satuan,
                ];
            }

            // Untuk tampilan ringkasan saldo per item bila item belum dipilih
            if (!$itemId) {
                $allQ = StockMovement::where('item_type', $tipe);
                if ($from) { 
                    $allQ->whereDate('tanggal', '>=', $from); 
                }
                if ($to) {   
                    $allQ->whereDate('tanggal', '<=', $to); 
                }
                
                $all = $allQ->get();
                
                // Hitung saldo per item dari awal sampai periode yang dipilih
                foreach ($all as $m) {
                    $sign = $m->direction === 'in' ? 1 : -1;
                    $saldoPerItem[$m->item_id] = ($saldoPerItem[$m->item_id] ?? 0) + ($sign * (float)$m->qty);
                }
                
                // Jika tidak ada filter tanggal, gunakan stok dari master table
                if (!$from && !$to) {
                    if ($tipe == 'material') {
                        foreach ($materials as $m) {
                            $saldoPerItem[$m->id] = (float)($m->stok ?? 0);
                        }
                    } else {
                        foreach ($products as $p) {
                            $saldoPerItem[$p->id] = (float)($p->stok ?? 0);
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in stok method: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memuat data stok: ' . $e->getMessage());
        }

        return view('laporan.stok.index', compact(
            'tipe', 
            'from', 
            'to', 
            'itemId', 
            'movements', 
            'materials', 
            'products', 
            'saldoPerItem', 
            'saldoAwalQty', 
            'saldoAwalNilai', 
            'running'
        ));
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
            $query->whereBetween('pembelians.tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }
        
        // Filter berdasarkan vendor
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('pembelians.vendor_id', $request->vendor_id);
        }
        
        return $query;
    }

    // === LAPORAN PENJUALAN ===
    public function penjualan()
    {
        $penjualan = Penjualan::with(['produk', 'details.produk'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('laporan.penjualan.index', compact('penjualan'));
    }

    // === LAPORAN RETUR ===
    public function laporanRetur(Request $request)
    {
        $query = \App\Models\Retur::with(['penjualan', 'details.produk'])
            ->when($request->bulan, function($q) use ($request) {
                $bulan = \Carbon\Carbon::parse($request->bulan);
                return $q->whereYear('tanggal', $bulan->year)
                       ->whereMonth('tanggal', $bulan->month);
            })
            ->latest();

        if ($request->has('export') && $request->export == 'pdf') {
            $returs = $query->get();
            $total = $returs->sum(function($retur) {
                return $retur->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga ?? 0);
                });
            });
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.retur.pdf', compact('returs', 'total'));
            return $pdf->download('laporan-retur-' . now()->format('Y-m-d') . '.pdf');
        }

        $returs = $query->paginate(15);
        $total = $returs->sum(function($retur) {
            return $retur->details->sum(function($detail) {
                return ($detail->jumlah ?? 0) * ($detail->harga ?? 0);
            });
        });

        return view('laporan.retur.index', compact('returs', 'total'));
    }

    // === LAPORAN PENGAJIAN ===
    public function laporanPenggajian(Request $request)
    {
        $query = \App\Models\Penggajian::with(['pegawai'])
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
        // Query untuk daftar pembelian kredit yang belum lunas
        $pembelianBelumLunas = \App\Models\Pembelian::with(['vendor', 'details.bahanBaku'])
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'lunas')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function($pembelian) {
                // Ambil total dari field total_harga
                $total = $pembelian->total_harga ?? 0;
                
                // Jika total 0, hitung dari detail pembelian
                if ($total == 0 && $pembelian->details->count() > 0) {
                    $total = $pembelian->details->sum(function($detail) {
                        return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                    });
                }
                
                // Ambil terbayar dari field terbayar
                $terbayar = $pembelian->terbayar ?? 0;
                
                // Hitung sisa utang
                $sisa = max(0, $total - $terbayar);
                
                // Format daftar item dengan bullet points
                $pembelian->items = $pembelian->details->map(function($detail) {
                    if ($detail->bahanBaku) {
                        $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                        return sprintf(
                            '• %s (%s %s) - Rp %s = Rp %s',
                            $detail->bahanBaku->nama_bahan,
                            number_format($detail->jumlah, 0, ',', '.'),
                            $detail->bahanBaku->satuan ?? 'unit',
                            number_format($detail->harga_satuan, 0, ',', '.'),
                            number_format($subtotal, 0, ',', '.')
                        );
                    }
                    return '';
                })->filter()->toArray();
                
                // Gabungkan semua item dengan newline
                $pembelian->items_formatted = implode("\n", $pembelian->items);
                
                // Simpan nilai numerik untuk perhitungan
                $pembelian->total_numerik = $total;
                $pembelian->terbayar_numerik = $terbayar;
                $pembelian->sisa_utang_numerik = $sisa;
                
                // Format untuk tampilan
                $pembelian->total_tagihan = 'Rp ' . number_format($total, 0, ',', '.');
                $pembelian->terbayar = 'Rp ' . number_format($terbayar, 0, ',', '.');
                $pembelian->sisa_utang = 'Rp ' . number_format($sisa, 0, ',', '.');
                
                return $pembelian;
            })
            ->filter(function($pembelian) {
                // Hanya tampilkan yang masih ada sisa utang
                return $pembelian->sisa_utang_numerik > 0;
            });

        // Query untuk riwayat pelunasan
        $query = \App\Models\ApSettlement::with(['pembelian.vendor', 'pembelian.details.bahanBaku'])
            ->when($request->bulan, function($q) use ($request) {
                $bulan = \Carbon\Carbon::parse($request->bulan);
                return $q->whereYear('tanggal', $bulan->year)
                       ->whereMonth('tanggal', $bulan->month);
            })
            ->orderBy('tanggal', 'desc');

        if ($request->has('export') && $request->export == 'pdf') {
            $pelunasanUtang = $query->get();
            $total = $pelunasanUtang->sum('dibayar_bersih');
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pelunasan-utang.pdf', [
                'pelunasanUtang' => $pelunasanUtang,
                'pembelianBelumLunas' => $pembelianBelumLunas,
                'total' => $total
            ]);
            return $pdf->download('laporan-pelunasan-utang-' . now()->format('Y-m-d') . '.pdf');
        }

        $pelunasanUtang = $query->paginate(15);
        $total = $pelunasanUtang->sum('dibayar_bersih');

        return view('laporan.pelunasan-utang.index', [
            'pelunasanUtang' => $pelunasanUtang,
            'pembelianBelumLunas' => $pembelianBelumLunas,
            'total' => $total
        ]);
    }

    // === LAPORAN ALIRAN KAS DAN BANK ===
    public function laporanAliranKas(Request $request)
    {
        // Ambil saldo awal kas dan bank dari COA
        $kas = \App\Models\Coa::where('kode_akun', '101')->first();
        $bank = \App\Models\Coa::where('kode_akun', '102')->first();
        
        $saldoAwalKas = $kas->saldo_awal ?? 0;
        $saldoAwalBank = $bank->saldo_awal ?? 0;
        
        // Filter tanggal
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        
        // Ambil semua transaksi dalam periode
        $transaksi = collect();
        
        // 1. Pendapatan dari Penjualan (Uang Masuk)
        $penjualans = \App\Models\Penjualan::whereBetween('tanggal', [$startDate, $endDate])
            ->where('payment_method', 'cash')
            ->get()
            ->map(function($p) {
                return [
                    'tanggal' => $p->tanggal,
                    'keterangan' => 'Pendapatan Penjualan #' . $p->id,
                    'uang_masuk' => $p->total_harga ?? 0,
                    'uang_keluar' => 0,
                    'jenis' => 'kas'
                ];
            });
        
        // 2. Pembayaran Beban (Uang Keluar)
        $bebans = \App\Models\ExpensePayment::whereBetween('tanggal', [$startDate, $endDate])
            ->with('coa')
            ->get()
            ->map(function($b) {
                return [
                    'tanggal' => $b->tanggal,
                    'keterangan' => 'Pembayaran Beban - ' . ($b->coa->nama_akun ?? 'Beban'),
                    'uang_masuk' => 0,
                    'uang_keluar' => $b->jumlah ?? 0,
                    'jenis' => 'kas'
                ];
            });
        
        // 3. Pelunasan Utang (Uang Keluar)
        $pelunasans = \App\Models\ApSettlement::whereBetween('tanggal', [$startDate, $endDate])
            ->with('pembelian.vendor')
            ->get()
            ->map(function($p) {
                return [
                    'tanggal' => $p->tanggal,
                    'keterangan' => 'Pelunasan Utang - ' . ($p->pembelian->vendor->nama ?? 'Vendor'),
                    'uang_masuk' => 0,
                    'uang_keluar' => $p->dibayar_bersih ?? 0,
                    'jenis' => $p->coa_kasbank == '102' ? 'bank' : 'kas'
                ];
            });
        
        // 4. Penggajian (Uang Keluar)
        $penggajians = \App\Models\Penggajian::whereBetween('periode', [$startDate, $endDate])
            ->with('pegawai')
            ->get()
            ->map(function($g) {
                return [
                    'tanggal' => $g->periode,
                    'keterangan' => 'Penggajian - ' . ($g->pegawai->nama ?? 'Pegawai'),
                    'uang_masuk' => 0,
                    'uang_keluar' => $g->total_gaji ?? 0,
                    'jenis' => 'kas'
                ];
            });
        
        // Gabungkan semua transaksi
        $transaksi = $transaksi->concat($penjualans)
            ->concat($bebans)
            ->concat($pelunasans)
            ->concat($penggajians)
            ->sortBy('tanggal')
            ->values();
        
        // Hitung total
        $totalMasuk = $transaksi->sum('uang_masuk');
        $totalKeluar = $transaksi->sum('uang_keluar');
        $saldoAkhir = $saldoAwalKas + $saldoAwalBank + $totalMasuk - $totalKeluar;
        
        return view('laporan.aliran-kas.index', compact(
            'transaksi', 
            'saldoAwalKas', 
            'saldoAwalBank', 
            'totalMasuk', 
            'totalKeluar', 
            'saldoAkhir',
            'startDate',
            'endDate'
        ));
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

    // Alias for invoicePenjualan
    public function invoice($id)
    {
        return $this->invoicePenjualan($id);
    }
}

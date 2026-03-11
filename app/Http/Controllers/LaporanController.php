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
        
        // Get all data for calculation
        $allPembelian = $query->get();
        
        // Calculate totals dengan logic yang sama dengan transaksi/pembelian
        $totalPembelian = $allPembelian->sum(function($p) {
            // Hitung total dari details untuk konsistensi (sama seperti admin)
            $totalPembelian = 0;
            if ($p->details && $p->details->count() > 0) {
                $totalPembelian = $p->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                });
            }
            
            // Jika ada total_harga di database, gunakan yang lebih besar (sama seperti admin)
            if ($p->total_harga > $totalPembelian) {
                $totalPembelian = $p->total_harga;
            }
            
            return $totalPembelian;
        });
        
        $totalTransaksi = $allPembelian->count();
        
        // Total pembelian (sesuai filter tanggal) - sudah dihitung di atas
        $totalPembelianFiltered = $totalPembelian;
        
        // Total pembelian tunai (cash) - sesuai filter tanggal dengan logic yang sama
        $pembelianTunaiQuery = Pembelian::with(['details'])
            ->where('payment_method', 'cash')
            ->when($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date, function($q) use ($request) {
                return $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            })
            ->when($request->has('vendor_id') && $request->vendor_id, function($q) use ($request) {
                return $q->where('vendor_id', $request->vendor_id);
            });
            
        $pembelianTunai = $pembelianTunaiQuery->get();
        $totalPembelianTunai = $pembelianTunai->sum(function($p) {
            // Logic yang sama dengan total pembelian
            $totalPembelian = 0;
            if ($p->details && $p->details->count() > 0) {
                $totalPembelian = $p->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                });
            }
            
            if ($p->total_harga > $totalPembelian) {
                $totalPembelian = $p->total_harga;
            }
            
            return $totalPembelian;
        });
        
        // Total pembelian yang belum lunas (credit dan status != lunas) - sesuai filter tanggal
        $pembelianBelumLunasQuery = Pembelian::with(['details'])
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'lunas')
            ->when($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date, function($q) use ($request) {
                return $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            })
            ->when($request->has('vendor_id') && $request->vendor_id, function($q) use ($request) {
                return $q->where('vendor_id', $request->vendor_id);
            });
            
        $pembelianBelumLunas = $pembelianBelumLunasQuery->get();
        $totalPembelianBelumLunas = $pembelianBelumLunas->sum(function($p) {
            // Gunakan sisa_pembayaran jika ada, kalau tidak hitung dari total - terbayar
            $sisaUtang = $p->sisa_pembayaran ?? 0;
            if ($sisaUtang == 0) {
                // Hitung total dengan logic yang sama
                $total = 0;
                if ($p->details && $p->details->count() > 0) {
                    $total = $p->details->sum(function($detail) {
                        return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                    });
                }
                
                if ($p->total_harga > $total) {
                    $total = $p->total_harga;
                }
                
                $sisaUtang = max(0, $total - ($p->terbayar ?? 0));
            }
            return $sisaUtang;
        });
        
        $pembelian = $query->paginate(15);
        $vendors = \App\Models\Vendor::all();

        return view('laporan.pembelian.index', compact(
            'pembelian', 
            'vendors', 
            'totalPembelian', 
            'totalTransaksi',
            'totalPembelianFiltered',
            'totalPembelianTunai',
            'totalPembelianBelumLunas'
        ));
    }

    // === LAPORAN STOK ===
    public function stok(Request $request)
    {
        $tipe = $request->get('tipe', 'material'); // material|product|bahan_pendukung
        $from = $request->get('from');
        $to = $request->get('to');
        $itemId = $request->get('item_id');

        // Daftar item untuk dropdown
        $materials = BahanBaku::with('satuan')->orderBy('nama_bahan', 'asc')->get();
        $products = Produk::with('satuan')->orderBy('nama_produk', 'asc')->get();
        $bahanPendukungs = \App\Models\BahanPendukung::with('satuanRelation')->orderBy('nama_bahan', 'asc')->get();

        // Initialize variables
        $movements = collect();
        $saldoAwalQty = 0.0;
        $saldoAwalNilai = 0.0;
        $running = [];
        $conversionData = [];
        $item = null;
        $availableSatuans = [];

        try {
            // Query mutasi dalam periode (untuk kartu stok spesifik item jika item dipilih)
            $movQ = StockMovement::query()->where('item_type', $tipe == 'bahan_pendukung' ? 'support' : $tipe);
            
            if ($itemId) { 
                $movQ->where('item_id', $itemId); 
                
                // Get item data and conversion ratios
                if ($tipe == 'material') {
                    $item = BahanBaku::find($itemId);
                } elseif ($tipe == 'product') {
                    $item = Produk::find($itemId);
                } elseif ($tipe == 'bahan_pendukung') {
                    $item = \App\Models\BahanPendukung::find($itemId);
                }
                
                // Prepare conversion data
                $conversionData = [
                    'primary' => [
                        'nama' => $item->satuanRelation->nama_satuan ?? '',
                        'konversi' => 1
                    ]
                ];
                
                // Prepare available satuans for dropdown
                $availableSatuans = [];
                $mainSatuan = $tipe == 'bahan_pendukung' ? $item->satuanRelation : $item->satuan;
                
                if ($mainSatuan) {
                    $availableSatuans[] = [
                        'id' => $mainSatuan->id,
                        'nama' => $mainSatuan->nama,
                        'is_primary' => true,
                        'conversion_to_primary' => 1
                    ];
                    
                    // Add sub satuan 1
                    if ($item->sub_satuan_1_id) {
                        $subSatuan1 = \App\Models\Satuan::find($item->sub_satuan_1_id);
                        if ($subSatuan1) {
                            $conversionRatio = 1;
                            if ($item->sub_satuan_1_nilai > 0) {
                                $conversionRatio = 1 / $item->sub_satuan_1_nilai;
                            } elseif ($item->sub_satuan_1_konversi > 0) {
                                $conversionRatio = $item->sub_satuan_1_konversi;
                            }
                            $availableSatuans[] = [
                                'id' => $subSatuan1->id,
                                'nama' => $subSatuan1->nama,
                                'is_primary' => false,
                                'conversion_to_primary' => $conversionRatio
                            ];
                        }
                    }
                    
                    // Add sub satuan 2
                    if ($item->sub_satuan_2_id) {
                        $subSatuan2 = \App\Models\Satuan::find($item->sub_satuan_2_id);
                        if ($subSatuan2) {
                            $conversionRatio = 1;
                            if ($item->sub_satuan_2_nilai > 0) {
                                $conversionRatio = 1 / $item->sub_satuan_2_nilai;
                            } elseif ($item->sub_satuan_2_konversi > 0) {
                                $conversionRatio = $item->sub_satuan_2_konversi;
                            }
                            $availableSatuans[] = [
                                'id' => $subSatuan2->id,
                                'nama' => $subSatuan2->nama,
                                'is_primary' => false,
                                'conversion_to_primary' => $conversionRatio
                            ];
                        }
                    }
                    
                    // Add sub satuan 3
                    if ($item->sub_satuan_3_id) {
                        $subSatuan3 = \App\Models\Satuan::find($item->sub_satuan_3_id);
                        if ($subSatuan3) {
                            $conversionRatio = 1;
                            if ($item->sub_satuan_3_nilai > 0) {
                                $conversionRatio = 1 / $item->sub_satuan_3_nilai;
                            } elseif ($item->sub_satuan_3_konversi > 0) {
                                $conversionRatio = $item->sub_satuan_3_konversi;
                            }
                            $availableSatuans[] = [
                                'id' => $subSatuan3->id,
                                'nama' => $subSatuan3->nama,
                                'is_primary' => false,
                                'conversion_to_primary' => $conversionRatio
                            ];
                        }
                    }
                }
                
                // Add sub units if they exist
                if ($item->sub_satuan_1_id && $item->sub_satuan_1) {
                    $conversionData['sub1'] = [
                        'nama' => $item->sub_satuan_1->nama_satuan,
                        'konversi' => $item->sub_satuan_1_nilai ?? 1
                    ];
                }
                if ($item->sub_satuan_2_id && $item->sub_satuan_2) {
                    $conversionData['sub2'] = [
                        'nama' => $item->sub_satuan_2->nama_satuan,
                        'konversi' => $item->sub_satuan_2_nilai ?? 1
                    ];
                }
                if ($item->sub_satuan_3_id && $item->sub_satuan_3) {
                    $conversionData['sub3'] = [
                        'nama' => $item->sub_satuan_3->nama_satuan,
                        'konversi' => $item->sub_satuan_3_nilai ?? 1
                    ];
                }
                
                // Start with 0 as initial stock (don't use master data)
                $saldoAwalQty = 0.0;
                $saldoAwalNilai = 0.0;
                
                // Calculate all movements before the selected date range
                if ($from) {
                    $before = StockMovement::where('item_type', $tipe == 'bahan_pendukung' ? 'support' : $tipe)
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
                    
                    // If no movements before date range, use master data as initial stock
                    if ($before->isEmpty() && $item->stok > 0) {
                        $saldoAwalQty = (float)($item->stok ?? 0);
                        $saldoAwalNilai = $saldoAwalQty * (float)($item->harga_satuan ?? 0);
                    }
                } else {
                    // If no date range, use master data
                    $saldoAwalQty = (float)($item->stok ?? 0);
                    $saldoAwalNilai = $saldoAwalQty * (float)($item->harga_satuan ?? 0);
                }
            }
            
            // If no date range specified, get all movements
            if (!$from && !$to) {
                $movements = $movQ->orderBy('tanggal', 'asc')
                                 ->orderBy('id', 'asc')
                                 ->get();
            } else {
                // Get movements within date range
                if ($from) { 
                    $movQ->whereDate('tanggal', '>=', $from); 
                }
                if ($to) {   
                    $movQ->whereDate('tanggal', '<=', $to); 
                }
                
                $movements = $movQ->orderBy('tanggal', 'asc')
                                 ->orderBy('id', 'asc')
                                 ->get();
            }

            // Build daily stock card
            $dailyStock = [];
            
            // Build daily stock card - show each transaction individually with monthly opening balance
                if ($movements->count() > 0) {
                    // Initialize running totals
                    $runningQty = $saldoAwalQty;
                    $runningNilai = $saldoAwalNilai;
                    
                    $previousMonth = null;
                    $currentMonth = null;
                    $processedMonths = [];
                    
                    // Group movements by month to identify opening balance dates
                    $monthlyMovements = [];
                    foreach ($movements as $m) {
                        $dateStr = is_string($m->tanggal) ? $m->tanggal : $m->tanggal->format('Y-m-d');
                        $monthKey = substr($dateStr, 0, 7); // Y-m format
                        if (!isset($monthlyMovements[$monthKey])) {
                            $monthlyMovements[$monthKey] = [];
                        }
                        $monthlyMovements[$monthKey][] = $m;
                    }
                    
                    // Sort months
                    ksort($monthlyMovements);
                    
                    // Process each month
                    foreach ($monthlyMovements as $monthKey => $monthMovements) {
                        // Add opening balance row for this month (except first month if no opening balance)
                        if ($runningQty > 0 || $runningNilai > 0) {
                            $firstDate = $monthKey . '-01'; // First day of month
                            $dailyStock[] = [
                                'tanggal' => $firstDate,
                                'saldo_awal_qty' => $runningQty,
                                'saldo_awal_nilai' => $runningNilai,
                                'pembelian_qty' => 0,
                                'pembelian_nilai' => 0,
                                'produksi_qty' => 0,
                                'produksi_nilai' => 0,
                                'saldo_akhir_qty' => $runningQty,
                                'saldo_akhir_nilai' => $runningNilai,
                                'ref_type' => 'opening_balance',
                                'ref_id' => '',
                                'is_opening_balance' => true
                            ];
                        }
                        
                        // Process individual movements in this month
                        foreach ($monthMovements as $m) {
                            $dateStr = is_string($m->tanggal) ? $m->tanggal : $m->tanggal->format('Y-m-d');
                            
                            // No opening balance for individual transactions
                            $saldoAwalQty = 0;
                            $saldoAwalNilai = 0;
                            
                            // Process individual movement
                            if ($m->direction === 'in') {
                                if ($m->ref_type === 'adjustment') {
                                    // Adjustment goes to pembelian column (it's adding stock)
                                    $dailyInQty = (float)$m->qty;
                                    $dailyInNilai = (float)($m->total_cost ?? 0);
                                    $dailyOutQty = 0;
                                    $dailyOutNilai = 0;
                                } elseif ($m->ref_type === 'production' && $tipe === 'product') {
                                    // Production IN for products goes to produksi column
                                    $dailyInQty = 0;
                                    $dailyInNilai = 0;
                                    $dailyOutQty = (float)$m->qty; // Use produksi column for production IN
                                    $dailyOutNilai = (float)($m->total_cost ?? 0);
                                } else {
                                    // Regular purchase goes to pembelian column
                                    $dailyInQty = (float)$m->qty;
                                    $dailyInNilai = (float)($m->total_cost ?? 0);
                                    $dailyOutQty = 0;
                                    $dailyOutNilai = 0;
                                }
                            } else {
                                $dailyInQty = 0;
                                $dailyInNilai = 0;
                                $dailyOutQty = (float)$m->qty;
                                $dailyOutNilai = (float)($m->total_cost ?? 0);
                            }
                            
                            // Update running totals
                            if ($m->ref_type === 'production' && $tipe === 'product') {
                                // Production IN movements should add to stock even though shown in produksi column
                                $runningQty += $dailyOutQty; // Production IN adds to stock
                                $runningNilai += $dailyOutNilai;
                            } else {
                                $runningQty += $dailyInQty - $dailyOutQty;
                                $runningNilai += $dailyInNilai - $dailyOutNilai;
                            }
                            
                            // Add to daily stock
                            $dailyStock[] = [
                                'tanggal' => $dateStr,
                                'saldo_awal_qty' => $saldoAwalQty,
                                'saldo_awal_nilai' => $saldoAwalNilai,
                                'pembelian_qty' => $dailyInQty,
                                'pembelian_nilai' => $dailyInNilai,
                                'produksi_qty' => $dailyOutQty,
                                'produksi_nilai' => $dailyOutNilai,
                                'saldo_akhir_qty' => $runningQty,
                                'saldo_akhir_nilai' => $runningNilai,
                                'ref_type' => $m->ref_type,
                                'ref_id' => $m->ref_id,
                                'is_opening_balance' => false
                            ];
                        }
                    }
                } else {
                // No movements, just show initial stock if there's any
                if ($saldoAwalQty > 0 || $saldoAwalNilai > 0) {
                    $dailyStock[] = [
                        'tanggal' => $from ?? now()->format('Y-m-d'),
                        'saldo_awal_qty' => $saldoAwalQty,
                        'saldo_awal_nilai' => $saldoAwalNilai,
                        'pembelian_qty' => 0,
                        'pembelian_nilai' => 0,
                        'produksi_qty' => 0,
                        'produksi_nilai' => 0,
                        'saldo_akhir_qty' => $saldoAwalQty,
                        'saldo_akhir_nilai' => $saldoAwalNilai,
                    ];
                }
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
                    } elseif ($tipe == 'product') {
                        foreach ($products as $p) {
                            $saldoPerItem[$p->id] = (float)($p->stok ?? 0);
                        }
                    } elseif ($tipe == 'bahan_pendukung') {
                        foreach ($bahanPendukungs as $bp) {
                            $saldoPerItem[$bp->id] = (float)($bp->stok ?? 0);
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
            'bahanPendukungs',
            'saldoAwalQty', 
            'saldoAwalNilai', 
            'running',
            'dailyStock',
            'conversionData',
            'item',
            'availableSatuans'
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
    
    // === EKSPOR LAPORAN PENJUALAN ===
    public function exportPenjualan(Request $request)
    {
        $query = $this->getPenjualanQuery($request);
        $penjualan = $query->get();
        $total = $penjualan->sum('total');
        
        $filename = 'laporan-penjualan-' . now()->format('Y-m-d') . '.xlsx';
        
        return response()->streamDownload(function() use ($penjualan, $total) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'No', 'No. Transaksi', 'Tanggal', 'Produk', 
                'Pembayaran', 'Total (Rp)'
            ]);
            
            // Data
            foreach ($penjualan as $index => $item) {
                $produk = '';
                if ($item->details && $item->details->count() > 0) {
                    $produk = $item->details->map(function($d) {
                        return $d->produk->nama_produk ?? 'Produk';
                    })->implode(', ');
                } else {
                    $produk = $item->produk->nama_produk ?? '-';
                }
                
                $payment = $item->payment_method === 'cash' ? 'Tunai' : ($item->payment_method === 'transfer' ? 'Transfer' : 'Kredit');
                
                fputcsv($handle, [
                    $index + 1,
                    $item->nomor_penjualan ?? '-',
                    $item->tanggal->format('d/m/Y'),
                    $produk,
                    $payment,
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
        $query = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuanRelation'])
            ->orderBy('tanggal', 'desc');
            
        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
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
    public function penjualan(Request $request)
    {
        $query = $this->getPenjualanQuery($request);
        
        // Get all data for calculation
        $allPenjualan = $query->get();
        
        // Calculate totals properly
        $totalPenjualan = $allPenjualan->sum(function($p) {
            $total = $p->total ?? 0;
            // Jika total = 0, hitung dari details
            if ($total == 0 && $p->details && $p->details->count() > 0) {
                $total = $p->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0);
                });
            }
            return $total;
        });
        
        $totalTransaksi = $allPenjualan->count();
        
        // Calculate additional statistics for the summary boxes
        
        // Total penjualan (sesuai filter tanggal)
        $totalPenjualanFiltered = $totalPenjualan; // Sudah sesuai filter dari query utama
        
        // Total penjualan tunai (cash) - sesuai filter tanggal
        $penjualanTunai = Penjualan::with(['details'])
            ->where('payment_method', 'cash')
            ->when($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date, function($q) use ($request) {
                return $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            })
            ->get();
            
        $totalPenjualanTunai = $penjualanTunai->sum(function($p) {
            $total = $p->total ?? 0;
            if ($total == 0 && $p->details && $p->details->count() > 0) {
                $total = $p->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0);
                });
            }
            return $total;
        });
        
        // Total penjualan kredit - sesuai filter tanggal
        $penjualanKredit = Penjualan::with(['details'])
            ->where('payment_method', 'credit')
            ->when($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date, function($q) use ($request) {
                return $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            })
            ->get();
            
        $totalPenjualanKredit = $penjualanKredit->sum(function($p) {
            $total = $p->total ?? 0;
            if ($total == 0 && $p->details && $p->details->count() > 0) {
                $total = $p->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0);
                });
            }
            return $total;
        });
        
        $penjualan = $query->paginate(15);

        return view('laporan.penjualan.index', compact(
            'penjualan', 
            'totalPenjualan', 
            'totalTransaksi',
            'totalPenjualanFiltered',
            'totalPenjualanTunai',
            'totalPenjualanKredit'
        ));
    }
    
    // Helper method untuk query penjualan
    private function getPenjualanQuery(Request $request)
    {
        $query = Penjualan::with(['produk', 'details.produk'])
            ->orderBy('tanggal', 'desc');
            
        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }
        
        // Filter berdasarkan payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        
        return $query;
    }

    // === LAPORAN RETUR ===
    public function laporanRetur(Request $request)
    {
        // Filter untuk Retur Penjualan
        $salesReturnQuery = \App\Models\Retur::with(['penjualan', 'details.produk'])
            ->where('type', 'sale')
            ->when($request->sales_start_date && $request->sales_end_date, function($q) use ($request) {
                return $q->whereBetween('tanggal', [$request->sales_start_date, $request->sales_end_date]);
            })
            ->orderBy('tanggal', 'desc');

        // Filter untuk Retur Pembelian  
        $purchaseReturnQuery = \App\Models\Retur::with(['pembelian.vendor', 'details.produk'])
            ->where('type', 'purchase')
            ->when($request->purchase_start_date && $request->purchase_end_date, function($q) use ($request) {
                return $q->whereBetween('tanggal', [$request->purchase_start_date, $request->purchase_end_date]);
            })
            ->orderBy('tanggal', 'desc');

        // Get data
        $salesReturns = $salesReturnQuery->paginate(10, ['*'], 'sales_page');
        $purchaseReturns = $purchaseReturnQuery->paginate(10, ['*'], 'purchase_page');

        // Calculate totals
        $totalSalesReturns = $salesReturnQuery->get()->sum(function($retur) {
            return $retur->details->sum(function($detail) {
                return ($detail->qty ?? 0) * ($detail->harga_satuan_asal ?? 0);
            });
        });
        $totalPurchaseReturns = $purchaseReturnQuery->get()->sum(function($retur) {
            return $retur->details->sum(function($detail) {
                return ($detail->qty ?? 0) * ($detail->harga_satuan_asal ?? 0);
            });
        });

        return view('laporan.retur.index', compact(
            'salesReturns', 
            'purchaseReturns', 
            'totalSalesReturns', 
            'totalPurchaseReturns'
        ));
    }

    // === LAPORAN PENGAJIAN ===
    public function laporanPenggajian(Request $request)
    {
        $query = \App\Models\Penggajian::with(['pegawai'])
            ->when($request->bulan, function($q) use ($request) {
                $bulan = \Carbon\Carbon::parse($request->bulan);
                return $q->whereYear('tanggal_penggajian', $bulan->year)
                       ->whereMonth('tanggal_penggajian', $bulan->month);
            })
            ->latest('tanggal_penggajian');

        if ($request->has('export') && $request->export == 'pdf') {
            $penggajians = $query->get();
            $total = $penggajians->sum('total_gaji');
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.penggajian.pdf', compact('penggajians', 'total'));
            return $pdf->download('laporan-penggajian-' . now()->format('Y-m-d') . '.pdf');
        }

        $penggajians = $query->get();
        $total = $penggajians->sum('total_gaji');

        return view('laporan.penggajian.index', compact('penggajians', 'total'));
    }

    // === LAPORAN PEMBAYARAN BEBAN ===
    public function laporanPembayaranBeban(Request $request)
    {
        // Get all active Beban Operasional master data
        $bebanOperasionalQuery = \App\Models\BebanOperasional::where('status', 'aktif')
            ->orderBy('kategori')
            ->orderBy('nama_beban');

        // Get the selected period or default to current month
        $selectedMonth = $request->bulan ? \Carbon\Carbon::parse($request->bulan) : now();
        
        // Get all beban operasional
        $bebanOperasional = $bebanOperasionalQuery->get();
        
        // Build the Budget vs Actual data
        $laporanData = collect([]);
        $totalBudget = 0;
        $totalAktual = 0;
        $totalSelisih = 0;
        
        foreach ($bebanOperasional as $beban) {
            // Get actual payments for this beban in the selected period
            $aktual = \App\Models\ExpensePayment::where('beban_operasional_id', $beban->id)
                ->whereYear('tanggal', $selectedMonth->year)
                ->whereMonth('tanggal', $selectedMonth->month)
                ->sum('nominal_pembayaran');
            
            $budget = $beban->budget_bulanan ?? 0;
            $selisih = $budget - $aktual;
            $status = $aktual > $budget ? 'Over Budget' : 'Aman';
            
            $laporanData->push((object) [
                'id' => $beban->id,
                'kategori' => $beban->kategori,
                'nama_beban' => $beban->nama_beban,
                'budget_bulanan' => $budget,
                'aktual_bulan_ini' => $aktual,
                'selisih' => $selisih,
                'status' => $status,
                'status_color' => $aktual > $budget ? 'danger' : 'success',
                'keterangan' => $beban->keterangan,
            ]);
            
            $totalBudget += $budget;
            $totalAktual += $aktual;
            $totalSelisih += $selisih;
        }
        
        // Summary data
        $summary = (object) [
            'total_budget' => $totalBudget,
            'total_aktual' => $totalAktual,
            'total_selisih' => $totalSelisih,
            'overall_status' => $totalAktual > $totalBudget ? 'Over Budget' : 'Aman',
            'overall_status_color' => $totalAktual > $totalBudget ? 'danger' : 'success',
        ];

        if ($request->has('export') && $request->export == 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pembayaran-beban.pdf', compact(
                'laporanData', 
                'summary', 
                'selectedMonth'
            ));
            return $pdf->download('laporan-pembayaran-beban-' . $selectedMonth->format('Y-m') . '.pdf');
        }

        return view('laporan.pembayaran-beban.index', compact(
            'laporanData', 
            'summary', 
            'selectedMonth'
        ));
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

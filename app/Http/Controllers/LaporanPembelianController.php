<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Vendor;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class LaporanPembelianController extends Controller
{
    public function index(Request $request)
    {
        // Handle pembelian data
        $query = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuanRelation']);
        
        // Filter pembelian
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        $pembelian = $query->oldest()->paginate(10, ['*'], 'pembelian_page');
        
        // Calculate totals for pembelian
        $totalQuery = Pembelian::query();
        if ($request->filled('start_date')) {
            $totalQuery->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $totalQuery->whereDate('tanggal', '<=', $request->end_date);
        }
        if ($request->filled('vendor_id')) {
            $totalQuery->where('vendor_id', $request->vendor_id);
        }
        
        $totalPembelianFiltered = $totalQuery->get()->sum(function($p) {
            if ($p->details && $p->details->count() > 0) {
                $totalPembelian = $p->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                });
            } else {
                $totalPembelian = $p->total_harga ?? 0;
            }
            return $totalPembelian;
        });
        
        $totalPembelianTunai = Pembelian::where('payment_method', 'cash')
            ->when($request->filled('start_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '<=', $request->end_date);
            })
            ->when($request->filled('vendor_id'), function($q) use ($request) {
                return $q->where('vendor_id', $request->vendor_id);
            })
            ->get()->sum(function($p) {
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
        
        $totalPembelianKredit = Pembelian::where('payment_method', 'credit')
            ->when($request->filled('start_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '<=', $request->end_date);
            })
            ->when($request->filled('vendor_id'), function($q) use ($request) {
                return $q->where('vendor_id', $request->vendor_id);
            })
            ->get()->sum(function($p) {
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
        
        $totalPembelianNonTunai = Pembelian::where('payment_method', 'transfer')
            ->when($request->filled('start_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '<=', $request->end_date);
            })
            ->when($request->filled('vendor_id'), function($q) use ($request) {
                return $q->where('vendor_id', $request->vendor_id);
            })
            ->get()->sum(function($p) {
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
        
        $totalPembelianBelumLunas = Pembelian::where('payment_method', 'credit')
            ->when($request->filled('start_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($q) use ($request) {
                return $q->whereDate('tanggal', '<=', $request->end_date);
            })
            ->when($request->filled('vendor_id'), function($q) use ($request) {
                return $q->where('vendor_id', $request->vendor_id);
            })
            ->get()->sum(function($p) {
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
                return $sisaUtang;
            });
        
        // Handle retur data
        $purchaseReturnQuery = PurchaseReturn::with(['pembelian.vendor', 'items.bahanBaku', 'items.bahanPendukung'])
            ->when($request->purchase_start_date && $request->purchase_end_date, function($q) use ($request) {
                return $q->whereBetween('return_date', [$request->purchase_start_date, $request->purchase_end_date]);
            })
            ->when($request->purchase_status, function($q) use ($request) {
                return $q->where('status', $request->purchase_status);
            })
            ->orderBy('return_date', 'asc');

        $purchaseReturns = $purchaseReturnQuery->paginate(15, ['*'], 'purchase_page');

        // Calculate totals for retur (including PPN)
        $totalPurchaseReturns = $purchaseReturnQuery->get()->sum(function($retur) {
            return $retur->total_with_ppn ?? 0;
        });
        
        $vendors = Vendor::orderBy('nama_vendor')->get();
        
        return view('laporan.pembelian.index', compact(
            'pembelian', 
            'vendors', 
            'totalPembelianFiltered',
            'totalPembelianTunai',
            'totalPembelianKredit',
            'totalPembelianNonTunai',
            'totalPembelianBelumLunas',
            'purchaseReturns',
            'totalPurchaseReturns'
        ));
    }
    
    public function export()
    {
        $pembelian = Pembelian::with(['vendor', 'pembelianDetails.bahanBaku'])
            ->oldest()
            ->get();
            
        $pdf = PDF::loadView('laporan.pembelian.export', compact('pembelian'));
        return $pdf->download('laporan-pembelian-' . date('Y-m-d') . '.pdf');
    }
    
    public function invoice(Pembelian $pembelian)
    {
        $pembelian->load(['vendor', 'pembelianDetails.bahanBaku']);
        
        $pdf = PDF::loadView('laporan.pembelian.invoice', compact('pembelian'));
        return $pdf->stream('invoice-pembelian-' . $pembelian->no_pembelian . '.pdf');
    }
}

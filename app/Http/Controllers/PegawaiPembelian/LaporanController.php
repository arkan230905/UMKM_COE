<?php

namespace App\Http\Controllers\PegawaiPembelian;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Vendor;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function pembelian(Request $request)
    {
        $query = $this->getPembelianQuery($request);
        
        // Get all data for calculation
        $allPembelian = $query->get();
        
        // Calculate totals dengan logic yang sama dengan laporan/pembelian
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
        
        $pembelians = $query->paginate(15);
        $vendors = Vendor::all();

        return view('pegawai-pembelian.laporan.pembelian', compact(
            'pembelians', 
            'vendors', 
            'totalPembelian', 
            'totalTransaksi',
            'totalPembelianFiltered',
            'totalPembelianTunai',
            'totalPembelianBelumLunas'
        ));
    }

    // Helper method untuk query pembelian - SAMA SEPERTI LAPORAN CONTROLLER
    private function getPembelianQuery(Request $request)
    {
        $query = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuanRelation'])
            ->orderBy('tanggal', 'desc');
            
        // Filter berdasarkan tanggal - SAMA SEPERTI LAPORAN CONTROLLER
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }
        
        // Filter berdasarkan vendor - SAMA SEPERTI LAPORAN CONTROLLER
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        return $query;
    }

    public function invoice($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku.satuan'])
            ->findOrFail($id);
        
        return view('pegawai-pembelian.laporan.invoice', compact('pembelian'));
    }

    public function retur(Request $request)
    {
        // Implementasi laporan retur untuk pegawai pembelian
        // Bisa ditambahkan nanti jika diperlukan
        
        return view('pegawai-pembelian.laporan.retur');
    }
}

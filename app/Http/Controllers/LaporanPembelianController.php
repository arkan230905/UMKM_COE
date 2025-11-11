<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class LaporanPembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with(['vendor', 'pembelianDetails.bahanBaku'])
            ->latest()
            ->paginate(10);
            
        $vendors = Vendor::all();
        
        return view('laporan.pembelian.index', compact('pembelian', 'vendors'));
    }
    
    public function export()
    {
        $pembelian = Pembelian::with(['vendor', 'pembelianDetails.bahanBaku'])
            ->latest()
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

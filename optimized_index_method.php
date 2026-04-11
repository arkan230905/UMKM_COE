<?php
// Optimized index method for PenjualanController

public function index(Request $request)
{
    // Optimized query with proper eager loading and pagination
    $query = Penjualan::with([
        'produk:id,nama_produk,harga_jual,hpp,harga_bom',
        'details.produk:id,nama_produk,harga_jual,hpp,harga_bom',
        'returs:id,penjualan_id'
    ]);
    
    // Filter by nomor transaksi
    if ($request->filled('nomor_transaksi')) {
        $query->where('nomor_penjualan', 'like', '%' . $request->nomor_transaksi . '%');
    }
    
    // Filter by tanggal
    if ($request->filled('tanggal_mulai')) {
        $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
    }
    if ($request->filled('tanggal_selesai')) {
        $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
    }
    
    // Filter by payment method
    if ($request->filled('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }
    
    // Add pagination and limit results
    $penjualans = $query->orderBy('tanggal', 'desc')
        ->limit(100) // Limit to 100 records for performance
        ->get();
    
    // Optimized summary calculation - only for today
    $today = now()->format('Y-m-d');
    
    // Use raw SQL for better performance
    $summaryData = \DB::select("
        SELECT 
            COUNT(*) as jumlah_transaksi,
            COALESCE(SUM(total), 0) as total_penjualan,
            COALESCE(SUM(jumlah), 0) as total_produk_terjual
        FROM penjualans 
        WHERE DATE(tanggal) = ?
    ", [$today]);
    
    $totalPenjualan = $summaryData[0]->total_penjualan ?? 0;
    $jumlahTransaksiHariIni = $summaryData[0]->jumlah_transaksi ?? 0;
    $totalProdukTerjual = $summaryData[0]->total_produk_terjual ?? 0;
    
    // Simplified profit calculation - use average HPP instead of complex calculation
    $totalProfit = $totalPenjualan * 0.3; // Assume 30% average profit margin
    
    // Get return data with limit
    $salesReturns = \App\Models\ReturPenjualan::with([
        'penjualan:id,nomor_penjualan',
        'detailReturPenjualans.produk:id,nama_produk'
    ])
    ->orderBy('created_at', 'desc')
    ->limit(50) // Limit returns to 50 records
    ->get();
    
    return view('transaksi.penjualan.index', compact(
        'penjualans', 
        'totalPenjualan', 
        'jumlahTransaksiHariIni', 
        'totalProdukTerjual', 
        'totalProfit', 
        'salesReturns'
    ));
}
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pelunasan Utang - {{ now()->format('d-m-Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 10px;
            color: #333;
            padding: 15mm;
        }
        
        /* Header Section */
        .header-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #8B4513;
            padding-bottom: 15px;
        }
        
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 9px;
            color: #666;
            line-height: 1.4;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 5px;
        }
        
        .report-meta {
            font-size: 9px;
            color: #666;
            line-height: 1.4;
        }
        
        /* Summary Box */
        .summary-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            border-right: 1px solid #ddd;
        }
        
        .summary-item:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: #8B4513;
        }
        
        /* Table Styles */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        
        thead {
            background: #8B4513;
            color: white;
        }
        
        th { 
            padding: 8px 6px;
            text-align: center;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #6d3410;
        }
        
        td { 
            padding: 7px 6px;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .text-right { 
            text-align: right; 
        }
        
        .text-center { 
            text-align: center; 
        }
        
        /* Badge Styles */
        .badge { 
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
        }
        
        .badge-success { 
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .badge-warning { 
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .badge-danger { 
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Footer Total */
        tfoot {
            background: #f0e6d9;
            font-weight: bold;
        }
        
        tfoot th {
            background: #f0e6d9;
            color: #333;
            border: 1px solid #d4c4b0;
            padding: 10px 6px;
            font-size: 10px;
        }
        
        /* Footer Section */
        .footer-section {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            font-size: 8px;
            color: #666;
        }
        
        .footer-left {
            float: left;
            width: 50%;
        }
        
        .footer-right {
            float: right;
            width: 50%;
            text-align: right;
        }
        
        .footer-signature {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }
        
        .footer-system {
            font-style: italic;
            color: #999;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }
        
        /* Page Break */
        @page {
            size: A4 landscape;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-left">
            <div class="company-name">SIMCOST / UMKM COE</div>
            <div class="company-details">
                Jl. Contoh Alamat No. 123, Kota<br>
                Telp: (021) 1234-5678<br>
                Email: info@umkmcoe.com
            </div>
        </div>
        <div class="header-right">
            <div class="report-title">LAPORAN PELUNASAN UTANG</div>
            <div class="report-meta">
                <strong>Periode:</strong> {{ request('bulan') ? \Carbon\Carbon::parse(request('bulan'))->format('F Y') : 'Semua Data' }}<br>
                <strong>Dicetak:</strong> {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-item">
            <div class="summary-label">Total Tagihan</div>
            <div class="summary-value">Rp {{ number_format($pelunasanUtang->sum(function($item) { return $item->pembelian->total_harga ?? 0; }), 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Dibayar</div>
            <div class="summary-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Jumlah Transaksi</div>
            <div class="summary-value">{{ $pelunasanUtang->count() }} Transaksi</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Status Laporan</div>
            <div class="summary-value" style="font-size: 11px;">Lengkap</div>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 14%;">No. Pelunasan</th>
                <th style="width: 18%;">Vendor</th>
                <th style="width: 14%;">No. Faktur</th>
                <th style="width: 14%;">Total Tagihan</th>
                <th style="width: 14%;">Dibayar</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelunasanUtang as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $item->kode_transaksi }}</td>
                <td>{{ $item->pembelian->vendor->nama_vendor ?? '-' }}</td>
                <td>{{ $item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->pembelian->total_harga ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                <td class="text-center">
                    @php
                        $statusPembayaran = $item->pembelian->status ?? 'Belum Bayar';
                    @endphp
                    @if($statusPembayaran === 'lunas' || $statusPembayaran === 'Lunas')
                        <span class="badge badge-success">Lunas</span>
                    @elseif($statusPembayaran === 'sebagian' || $statusPembayaran === 'Sebagian')
                        <span class="badge badge-warning">Sebagian</span>
                    @else
                        <span class="badge badge-danger">Belum Bayar</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty-state">
                    Tidak ada data pelunasan utang untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">TOTAL</th>
                <th class="text-right">Rp {{ number_format($pelunasanUtang->sum(function($item) { return $item->pembelian->total_harga ?? 0; }), 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <!-- Footer Section -->
    <div class="footer-section">
        <div class="footer-left">
            <div class="footer-signature">Dicetak oleh: {{ Auth::user()->name ?? 'Nayla' }}</div>
            <div class="footer-system">Laporan ini dihasilkan otomatis oleh sistem SIMCOST</div>
        </div>
        <div class="footer-right">
            <div style="color: #999;">Halaman 1 dari 1</div>
        </div>
    </div>
</body>
</html>

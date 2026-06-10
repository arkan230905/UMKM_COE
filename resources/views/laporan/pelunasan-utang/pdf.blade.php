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
            padding: 20mm 15mm;
            background: #fff;
        }
        
        /* Header Section */
        .header { 
            text-align: center; 
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #8B6A4E;
        }
        
        .header h1 { 
            font-size: 18px;
            font-weight: bold;
            color: #6B4F3A;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        
        .header-info {
            font-size: 9px;
            color: #666;
            line-height: 1.6;
        }
        
        .header-info p {
            margin: 3px 0;
        }
        
        /* Table Styles */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            font-size: 9px;
        }
        
        thead th {
            background-color: #F5EDE5;
            color: #6B4F3A;
            font-weight: bold;
            text-align: center;
            padding: 8px 6px;
            border: 1px solid #D4C4B0;
            font-size: 9px;
        }
        
        tbody td {
            padding: 7px 6px;
            border: 1px solid #E8E8E8;
            vertical-align: middle;
        }
        
        tbody tr:nth-child(even) {
            background-color: #FAFAFA;
        }
        
        tbody tr:hover {
            background-color: #F5F5F5;
        }
        
        /* Text Alignment */
        .text-center { 
            text-align: center; 
        }
        
        .text-right { 
            text-align: right; 
        }
        
        .text-left {
            text-align: left;
        }
        
        /* Badge Styles */
        .badge { 
            display: inline-block;
            padding: 3px 8px; 
            border-radius: 3px; 
            font-size: 8px;
            font-weight: 600;
        }
        
        .badge-success { 
            background-color: #D4EDDA; 
            color: #155724;
            border: 1px solid #C3E6CB;
        }
        
        .badge-warning { 
            background-color: #FFF3CD; 
            color: #856404;
            border: 1px solid #FFEAA7;
        }
        
        .badge-danger {
            background-color: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }
        
        /* Footer Total Section */
        .total-section {
            margin-top: 15px;
            padding: 12px 15px;
            background-color: #F5EDE5;
            border: 1px solid #D4C4B0;
            border-radius: 4px;
        }
        
        .total-section table {
            width: 100%;
            margin: 0;
        }
        
        .total-section td {
            border: none;
            padding: 5px 0;
            font-size: 10px;
        }
        
        .total-section .label {
            font-weight: bold;
            color: #6B4F3A;
            width: 70%;
            text-align: right;
            padding-right: 20px;
        }
        
        .total-section .value {
            font-weight: bold;
            color: #333;
            text-align: right;
            width: 30%;
        }
        
        /* Empty State */
        .empty-state {
            padding: 30px;
            text-align: center;
            color: #999;
            font-style: italic;
        }
        
        /* Print Optimization */
        @page {
            margin: 20mm 15mm;
        }
        
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN PELUNASAN UTANG</h1>
        <div class="header-info">
            <p><strong>Periode:</strong> {{ request('bulan') ? \Carbon\Carbon::parse(request('bulan'))->locale('id')->isoFormat('MMMM YYYY') : 'Semua Data' }}</p>
            <p><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y') }} &nbsp;&nbsp; <strong>Jam Cetak:</strong> {{ now()->format('H:i:s') }}</p>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 14%;">No. Pelunasan</th>
                <th style="width: 16%;">Vendor</th>
                <th style="width: 14%;">No. Faktur</th>
                <th style="width: 12%;">Total Tagihan</th>
                <th style="width: 10%;">Total Refund</th>
                <th style="width: 12%;">Dibayar</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTagihan = 0;
                $totalRefund = 0;
                $totalDibayar = 0;
            @endphp
            
            @forelse($pelunasanUtang as $item)
                @php
                    $totalTagihan += $item->pembelian->total_harga ?? 0;
                    $totalRefund += $item->pembelian->total_refund ?? 0;
                    $totalDibayar += $item->jumlah;
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $item->kode_transaksi }}</td>
                    <td class="text-left">{{ $item->pembelian->vendor->nama_vendor ?? '-' }}</td>
                    <td class="text-center">{{ $item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($item->pembelian->total_harga ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->pembelian->total_refund ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @php
                            $statusPembayaran = $item->pembelian->status_pembayaran;
                        @endphp
                        @if($statusPembayaran === 'Lunas')
                            <span class="badge badge-success">Lunas</span>
                        @elseif($statusPembayaran === 'Sebagian')
                            <span class="badge badge-warning">Sebagian</span>
                        @else
                            <span class="badge badge-danger">Belum Bayar</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty-state">
                        Tidak ada data pelunasan utang untuk periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total Section -->
    @if($pelunasanUtang->count() > 0)
    <div class="total-section">
        <table>
            <tr>
                <td class="label">Total Tagihan:</td>
                <td class="value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Refund:</td>
                <td class="value">Rp {{ number_format($totalRefund, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Dibayar:</td>
                <td class="value">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    @endif
</body>
</html>

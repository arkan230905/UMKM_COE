<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Komplit - {{ date('d F Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .table th, .table td { padding: .5rem; font-size: 10px; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .report-box { border: 0; box-shadow: none; margin: 0; padding: 15px; }
            .page-break { page-break-before: always; }
        }
        
        body {
            background: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
        }
        
        .report-box {
            max-width: 100%; 
            margin: 0 auto; 
            background: #fff; 
            padding: 20px; 
        }
        
        .report-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-weight: 700; 
            font-size: 18px; 
            color: #007bff;
            margin-bottom: 5px;
            text-align: center;
        }
        
        .report-date {
            font-size: 11px;
            color: #6c757d;
            text-align: center;
        }
        
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .summary-label {
            font-weight: 600;
        }
        
        .summary-value {
            font-weight: 700;
        }
        
        .section-title {
            font-weight: 700; 
            font-size: 14px; 
            color: #007bff;
            margin-bottom: 15px;
            margin-top: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        .table thead th {
            background: #007bff;
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 8px 5px;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 6px 5px;
            font-size: 10px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        
        .table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .table tbody tr:hover {
            background: #e9ecef;
        }
        
        .text-end {
            text-align: right !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .fw-bold {
            font-weight: 700 !important;
        }
        
        .text-primary {
            color: #007bff !important;
        }
        
        .text-success {
            color: #28a745 !important;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 8px;
            font-weight: 600;
            border-radius: 12px;
            text-transform: uppercase;
        }
        
        .badge-in {
            background: #28a745;
            color: white;
        }
        
        .badge-out {
            background: #dc3545;
            color: white;
        }
        
        .footer-info {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            font-size: 9px;
            color: #6c757d;
            text-align: center;
        }
        
        .stock-positive {
            color: #28a745;
            font-weight: 600;
        }
        
        .stock-zero {
            color: #6c757d;
        }
        
        .stock-negative {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="report-box">
    <!-- Header -->
    <div class="report-header">
        <div class="report-title">
            <i class="fas fa-boxes me-2"></i>LAPORAN STOK KOMPLIT
        </div>
        <div class="report-date">
            Semua Jenis Item (Bahan Baku, Bahan Pendukung, Produk) | 
            Periode: {{ date('d F Y') }}
        </div>
    </div>

    <!-- Summary -->
    <div class="summary-box">
        <div class="row">
            <div class="col-md-4">
                <h6 class="fw-bold mb-3 text-primary">RINGKASAN ITEM</h6>
                <div class="summary-item">
                    <span class="summary-label">Total Bahan Baku:</span>
                    <span class="summary-value">{{ $summary['total_bahan_baku_items'] }} item</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Bahan Pendukung:</span>
                    <span class="summary-value">{{ $summary['total_bahan_pendukung_items'] }} item</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Produk:</span>
                    <span class="summary-value">{{ $summary['total_produk_items'] }} item</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Semua Item:</span>
                    <span class="summary-value fw-bold">{{ $summary['total_bahan_baku_items'] + $summary['total_bahan_pendukung_items'] + $summary['total_produk_items'] }} item</span>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3 text-primary">RINGKASAN STOK</h6>
                <div class="summary-item">
                    <span class="summary-label">Total Stok Bahan Baku:</span>
                    <span class="summary-value">{{ number_format($summary['total_bahan_baku_stock'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Stok Bahan Pendukung:</span>
                    <span class="summary-value">{{ number_format($summary['total_bahan_pendukung_stock'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Stok Produk:</span>
                    <span class="summary-value">{{ number_format($summary['total_produk_stock'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Semua Stok:</span>
                    <span class="summary-value fw-bold">{{ number_format($summary['total_bahan_baku_stock'] + $summary['total_bahan_pendukung_stock'] + $summary['total_produk_stock'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3 text-primary">INFORMASI</h6>
                <div class="summary-item">
                    <span class="summary-label">Total Transaksi:</span>
                    <span class="summary-value">{{ $summary['total_all_movements'] }} transaksi</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Tanggal Cetak:</span>
                    <span class="summary-value">{{ now()->format('d F Y H:i') }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Dicetak Oleh:</span>
                    <span class="summary-value">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bahan Baku Section -->
    @if(isset($stockData['bahan_baku']) && count($stockData['bahan_baku']) > 0)
        <div class="section-title">
            <i class="fas fa-cube me-2"></i>BAHAN BAKU ({{ count($stockData['bahan_baku']) }} Item)
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:3%">#</th>
                        <th style="width:20%">Nama Bahan Baku</th>
                        <th style="width:10%">Satuan</th>
                        <th class="text-end" style="width:12%">Stok Masuk</th>
                        <th class="text-end" style="width:12%">Stok Keluar</th>
                        <th class="text-end" style="width:12%">Stok Akhir</th>
                        <th class="text-center" style="width:8%">Status</th>
                        <th class="text-center" style="width:10%">Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockData['bahan_baku'] as $i => $data)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td class="fw-medium">{{ $data['item']->nama_bahan }}</td>
                            <td>{{ $data['item']->satuan->nama ?? '-' }}</td>
                            <td class="text-end text-success">{{ number_format($data['stock_in'], 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($data['stock_out'], 2) }}</td>
                            <td class="text-end fw-bold">
                                <span class="{{ $data['current_stock'] > 0 ? 'stock-positive' : ($data['current_stock'] < 0 ? 'stock-negative' : 'stock-zero') }}">
                                    {{ number_format($data['current_stock'], 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($data['current_stock'] > 0)
                                    <span class="badge bg-success">Ada</span>
                                @elseif($data['current_stock'] < 0)
                                    <span class="badge bg-danger">Minus</span>
                                @else
                                    <span class="badge bg-secondary">Habis</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $data['movements']->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Bahan Pendukung Section -->
    @if(isset($stockData['bahan_pendukung']) && count($stockData['bahan_pendukung']) > 0)
        <div class="section-title">
            <i class="fas fa-tools me-2"></i>BAHAN PENDUKUNG ({{ count($stockData['bahan_pendukung']) }} Item)
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:3%">#</th>
                        <th style="width:20%">Nama Bahan Pendukung</th>
                        <th style="width:10%">Satuan</th>
                        <th class="text-end" style="width:12%">Stok Masuk</th>
                        <th class="text-end" style="width:12%">Stok Keluar</th>
                        <th class="text-end" style="width:12%">Stok Akhir</th>
                        <th class="text-center" style="width:8%">Status</th>
                        <th class="text-center" style="width:10%">Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockData['bahan_pendukung'] as $i => $data)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td class="fw-medium">{{ $data['item']->nama_bahan }}</td>
                            <td>{{ $data['item']->satuanRelation->nama ?? '-' }}</td>
                            <td class="text-end text-success">{{ number_format($data['stock_in'], 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($data['stock_out'], 2) }}</td>
                            <td class="text-end fw-bold">
                                <span class="{{ $data['current_stock'] > 0 ? 'stock-positive' : ($data['current_stock'] < 0 ? 'stock-negative' : 'stock-zero') }}">
                                    {{ number_format($data['current_stock'], 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($data['current_stock'] > 0)
                                    <span class="badge bg-success">Ada</span>
                                @elseif($data['current_stock'] < 0)
                                    <span class="badge bg-danger">Minus</span>
                                @else
                                    <span class="badge bg-secondary">Habis</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $data['movements']->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Produk Section -->
    @if(isset($stockData['produk']) && count($stockData['produk']) > 0)
        <div class="section-title">
            <i class="fas fa-box me-2"></i>PRODUK ({{ count($stockData['produk']) }} Item)
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:3%">#</th>
                        <th style="width:20%">Nama Produk</th>
                        <th style="width:10%">Satuan</th>
                        <th class="text-end" style="width:12%">Stok Masuk</th>
                        <th class="text-end" style="width:12%">Stok Keluar</th>
                        <th class="text-end" style="width:12%">Stok Akhir</th>
                        <th class="text-center" style="width:8%">Status</th>
                        <th class="text-center" style="width:10%">Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockData['produk'] as $i => $data)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td class="fw-medium">{{ $data['item']->nama_produk }}</td>
                            <td>{{ $data['item']->satuan->nama ?? '-' }}</td>
                            <td class="text-end text-success">{{ number_format($data['stock_in'], 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($data['stock_out'], 2) }}</td>
                            <td class="text-end fw-bold">
                                <span class="{{ $data['current_stock'] > 0 ? 'stock-positive' : ($data['current_stock'] < 0 ? 'stock-negative' : 'stock-zero') }}">
                                    {{ number_format($data['current_stock'], 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($data['current_stock'] > 0)
                                    <span class="badge bg-success">Ada</span>
                                @elseif($data['current_stock'] < 0)
                                    <span class="badge bg-danger">Minus</span>
                                @else
                                    <span class="badge bg-secondary">Habis</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $data['movements']->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer-info">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-2">
                    <i class="fas fa-building me-2"></i>
                    <strong>UMKM COE</strong>
                </div>
                <div class="small text-muted">
                    Sistem Manajemen UMKM Center of Excellence
                </div>
                <div class="small text-muted mt-1">
                    Laporan Stok Komplit - Semua Jenis Item
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <i class="fas fa-print me-2"></i>
                    Dicetak pada: {{ now()->format('d F Y H:i') }}
                </div>
                <div class="small text-muted">
                    Laporan ini sah dan telah diterbitkan oleh sistem
                </div>
                <div class="small text-muted">
                    Total {{ $summary['total_all_movements'] }} transaksi stok
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

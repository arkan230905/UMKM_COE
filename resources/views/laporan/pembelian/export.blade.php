<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian - {{ date('d F Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .table th, .table td { padding: .5rem; font-size: 11px; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .report-box { border: 0; box-shadow: none; margin: 0; padding: 15px; }
        }
        
        body {
            background: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
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
            font-size: 20px; 
            color: #007bff;
            margin-bottom: 5px;
            text-align: center;
        }
        
        .report-date {
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }
        
        .table thead th {
            background: #007bff;
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            padding: 8px 5px;
        }
        
        .table tbody td {
            padding: 8px 5px;
            font-size: 11px;
            vertical-align: middle;
        }
        
        .table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .grand-total-row {
            background: #007bff !important;
            color: white !important;
            font-weight: 700;
            font-size: 12px;
        }
        
        .grand-total-row th,
        .grand-total-row td {
            border: none;
            padding: 10px 5px;
        }
        
        .payment-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .payment-cash {
            background: #28a745;
            color: white;
        }
        
        .payment-transfer {
            background: #007bff;
            color: white;
        }
        
        .payment-credit {
            background: #ffc107;
            color: #212529;
        }
        
        .status-lunas {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }
        
        .status-belum {
            background: #ffc107;
            color: #212529;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }
        
        .footer-info {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            font-size: 10px;
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
            font-size: 11px;
        }
        
        .summary-label {
            font-weight: 600;
        }
        
        .summary-value {
            font-weight: 700;
        }
    </style>
</head>
<body>
<div class="report-box">
    <!-- Header -->
    <div class="report-header">
        <div class="report-title">
            <i class="fas fa-file-invoice me-2"></i>LAPORAN PEMBELIAN
        </div>
        <div class="report-date">
            Periode: {{ date('d F Y') }}
        </div>
    </div>

    <!-- Summary -->
    <div class="summary-box">
        @php
            $grandTotalAll = 0;
            $totalTunai = 0;
            $totalKredit = 0;
            $totalBelumLunas = 0;
        @endphp
        @foreach($pembelian as $p)
            @php
                $totalPembelian = 0;
                if ($p->details && $p->details->count() > 0) {
                    $totalPembelian = $p->details->sum(function($detail) {
                        return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                    });
                } else {
                    $totalPembelian = $p->total_harga ?? 0;
                }
                
                $grandTotalAll += $totalPembelian;
                
                if ($p->payment_method === 'cash') {
                    $totalTunai += $totalPembelian;
                } else {
                    $totalKredit += $totalPembelian;
                    $sisaUtang = max(0, $totalPembelian - ($p->terbayar ?? 0));
                    $totalBelumLunas += $sisaUtang;
                }
            @endphp
        @endforeach
        
        <div class="row">
            <div class="col-md-3">
                <div class="summary-item">
                    <span class="summary-label">Total Transaksi:</span>
                    <span class="summary-value">{{ $pembelian->count() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Grand Total:</span>
                    <span class="summary-value text-primary">Rp {{ number_format($grandTotalAll, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-item">
                    <span class="summary-label">Total Tunai:</span>
                    <span class="summary-value text-success">Rp {{ number_format($totalTunai, 0, ',', '.') }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Kredit:</span>
                    <span class="summary-value text-info">Rp {{ number_format($totalKredit, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-item">
                    <span class="summary-label">Terbayar:</span>
                    <span class="summary-value">Rp {{ number_format($totalKredit - $totalBelumLunas, 0, ',', '.') }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Sisa Utang:</span>
                    <span class="summary-value text-danger">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-item">
                    <span class="summary-label">Status Lunas:</span>
                    <span class="summary-value">{{ $pembelian->where('status', 'lunas')->count() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Status Belum:</span>
                    <span class="summary-value">{{ $pembelian->where('status', '!=', 'lunas')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th style="width:3%">#</th>
                    <th style="width:8%">Tanggal</th>
                    <th style="width:12%">No. Pembelian</th>
                    <th style="width:15%">Vendor</th>
                    <th style="width:8%">Metode</th>
                    <th style="width:10%">Status</th>
                    <th class="text-end" style="width:15%">Total Harga</th>
                    <th class="text-end" style="width:12%">Terbayar</th>
                    <th class="text-end" style="width:12%">Sisa</th>
                    <th style="width:5%">Detail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pembelian as $i => $p)
                    @php
                        $totalPembelian = 0;
                        if ($p->details && $p->details->count() > 0) {
                            $totalPembelian = $p->details->sum(function($detail) {
                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                            });
                        } else {
                            $totalPembelian = $p->total_harga ?? 0;
                        }
                        
                        $sisaPembayaran = max(0, $totalPembelian - ($p->terbayar ?? 0));
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ optional($p->tanggal)->format('d/m/Y') ?? '-' }}</td>
                        <td class="fw-medium">{{ $p->nomor_pembelian ?? 'PB-' . optional($p->tanggal)->format('Y') . '-' . str_pad($p->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $p->vendor->nama_vendor ?? '-' }}</td>
                        <td>
                            @if($p->payment_method === 'cash')
                                <span class="payment-badge payment-cash">Tunai</span>
                            @elseif($p->payment_method === 'transfer')
                                <span class="payment-badge payment-transfer">Transfer</span>
                            @else
                                <span class="payment-badge payment-credit">Kredit</span>
                            @endif
                        </td>
                        <td>
                            @if($p->status === 'lunas')
                                <span class="status-lunas">LUNAS</span>
                            @else
                                <span class="status-belum">BELUM</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($p->terbayar ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $p->details ? $p->details->count() : 0 }} item</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total-row">
                    <th colspan="6" class="text-end">GRAND TOTAL</th>
                    <th class="text-end">Rp {{ number_format($grandTotalAll, 0, ',', '.') }}</th>
                    <th class="text-end">Rp {{ number_format($totalKredit - $totalBelumLunas, 0, ',', '.') }}</th>
                    <th class="text-end">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</th>
                    <th class="text-center">{{ $pembelian->sum(function($p) { return $p->details ? $p->details->count() : 0; }) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

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
            </div>
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <i class="fas fa-print me-2"></i>
                    Dicetak pada: {{ now()->format('d F Y H:i') }}
                </div>
                <div class="small text-muted">
                    Laporan ini sah dan telah diterbitkan oleh sistem
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

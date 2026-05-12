<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-period {
            font-size: 11px;
            color: #666;
        }
        .summary-cards {
            margin-bottom: 20px;
        }
        .summary-card {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .summary-amount {
            font-size: 14px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: white;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-info {
            background-color: #17a2b8;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">MANUFAKTUR COE</div>
        <div class="report-title">LAPORAN PENJUALAN</div>
        <div class="report-period">
            @if($startDate && $endDate)
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            @else
                Semua Periode
            @endif
            @if($paymentMethod)
                | Metode: {{ $paymentMethod == 'cash' ? 'Tunai' : ($paymentMethod == 'transfer' ? 'Transfer' : 'Kredit') }}
            @endif
        </div>
    </div>

    <div class="summary-cards">
        <table width="100%">
            <tr>
                <td width="33%">
                    <div class="summary-card">
                        <div class="summary-title">Total Penjualan</div>
                        <div class="summary-amount">Rp {{ number_format($totalPenjualanFiltered, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td width="33%">
                    <div class="summary-card">
                        <div class="summary-title">Total Penjualan Tunai</div>
                        <div class="summary-amount">Rp {{ number_format($totalPenjualanTunai, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td width="33%">
                    <div class="summary-card">
                        <div class="summary-title">Total Penjualan Kredit</div>
                        <div class="summary-amount">Rp {{ number_format($totalPenjualanKredit, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No. Transaksi</th>
                <th width="12%">Tanggal</th>
                <th width="35%">Produk Terjual</th>
                <th width="12%">Pembayaran</th>
                <th width="13%" class="text-right">Total</th>
                <th width="8%" class="text-center">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan as $index => $p)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $p->nomor_penjualan ?? '-' }}</td>
                    <td>{{ optional($p->tanggal)->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        @if($p->details && $p->details->count() > 0)
                            @foreach($p->details as $detail)
                                <div>
                                    {{ $detail->produk->nama_produk ?? 'Produk' }}
                                    @if(($detail->diskon_nominal ?? 0) > 0)
                                        <span class="badge badge-warning">Diskon: Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            {{ $p->produk->nama_produk ?? '-' }}
                        @endif
                    </td>
                    <td>
                        @if($p->payment_method === 'cash')
                            <span class="badge badge-success">Tunai</span>
                        @elseif($p->payment_method === 'transfer')
                            <span class="badge badge-info">Transfer</span>
                        @else
                            <span class="badge badge-warning">Kredit</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @php
                            $totalPenjualanItem = $p->total ?? 0;
                            if ($totalPenjualanItem == 0 && $p->details && $p->details->count() > 0) {
                                $totalPenjualanItem = $p->details->sum(function($detail) {
                                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0);
                                });
                            }
                        @endphp
                        <strong>Rp {{ number_format($totalPenjualanItem, 0, ',', '.') }}</strong>
                    </td>
                    <td class="text-center">
                        @if($p->details && $p->details->count() > 0)
                            {{ $p->details->sum('jumlah') }} pcs
                        @else
                            {{ $p->jumlah ?? 0 }} pcs
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPenjualanFiltered, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div>Laporan dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</div>
        <div>© 2024 MANUFAKTUR COE - Sistem Informasi Manufaktur</div>
    </div>
</body>
</html>

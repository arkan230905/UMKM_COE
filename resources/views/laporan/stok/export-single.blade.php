<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok - {{ $summary['item_name'] ?? 'Item' }} | {{ date('d F Y') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            background: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .header p {
            margin: 3px 0;
            color: #666;
            font-size: 10px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin: 15px 0 8px 0;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7px;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 3px 2px;
            text-align: center;
            font-size: 7px;
            white-space: nowrap;
            overflow: hidden;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 6px;
        }
        
        .col-date { width: 7%; }
        .col-desc { width: 10%; }
        .col-qty { width: 5.5%; }
        .col-price { width: 6%; }
        .col-total { width: 6.5%; }
        
        .text-right {
            text-align: right;
        }
        
        .text-left {
            text-align: left;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN KARTU STOK - {{ strtoupper($summary['item_name'] ?? 'ITEM') }}</h1>
        <p>{{ $summary['item_type'] == 'material' ? 'Bahan Baku' : ($summary['item_type'] == 'product' ? 'Produk' : 'Bahan Pendukung') }}</p>
        <p>Periode: {{ date('d F Y') }}</p>
    </div>

    <!-- Stock Data for Each Unit -->
    @foreach($stockData as $unitData)
        <div class="section-title">
            Kartu Stok - {{ $unitData['itemName'] }} (Satuan {{ $unitData['unit']['name'] }})
        </div>
        
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="col-date">Tanggal</th>
                    <th rowspan="2" class="col-desc">Keterangan</th>
                    <th colspan="3">Stok Awal</th>
                    <th colspan="3">{{ $summary['item_type'] == 'product' ? 'Penjualan' : 'Pembelian' }}</th>
                    <th colspan="3">Produksi</th>
                    <th colspan="3">Total Stok</th>
                </tr>
                <tr>
                    <th class="col-qty">Qty</th>
                    <th class="col-price">Harga</th>
                    <th class="col-total">Total</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-price">Harga</th>
                    <th class="col-total">Total</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-price">Harga</th>
                    <th class="col-total">Total</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-price">Harga</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($unitData['dailyStock']) && count($unitData['dailyStock']) > 0)
                    @foreach($unitData['dailyStock'] as $row)
                        <tr>
                            <td>{{ $row['tanggal'] }}</td>
                            <td class="text-left">
                                @if(isset($row['ref_type']))
                                    @switch($row['ref_type'])
                                        @case('initial_stock')
                                            Stok Awal
                                            @break
                                        @case('purchase')
                                            Pembelian
                                            @break
                                        @case('retur')
                                            Retur Pembelian
                                            @break
                                        @case('retur_tukar_kirim')
                                            Retur Barang Keluar
                                            @break
                                        @case('retur_tukar_terima')
                                            Retur Barang Masuk
                                            @break
                                        @case('production')
                                            {{ $summary['item_type'] === 'product' ? 'Hasil Produksi' : 'Pemakaian Produksi' }}
                                            @break
                                        @case('sale')
                                            Penjualan
                                            @break
                                        @default
                                            {{ ucfirst(str_replace('_', ' ', $row['ref_type'])) }}
                                    @endswitch
                                @else
                                    Transaksi
                                @endif
                            </td>
                            
                            <!-- Stok Awal -->
                            <td class="text-right">
                                @if($row['saldo_awal_qty'] > 0)
                                    {{ number_format($row['saldo_awal_qty'], 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-right">
                                @if($row['saldo_awal_harga'] > 0)
                                    {{ number_format($row['saldo_awal_harga'], 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-right">
                                @if($row['saldo_awal_total'] > 0)
                                    {{ number_format($row['saldo_awal_total'], 0, ',', '.') }}
                                @endif
                            </td>
                            
                            <!-- Pembelian/Penjualan -->
                            @if($summary['item_type'] == 'product')
                                <td class="text-right">
                                    @if(isset($row['penjualan_qty']) && $row['penjualan_qty'] > 0)
                                        {{ number_format($row['penjualan_qty'], 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(isset($row['penjualan_harga']) && $row['penjualan_harga'] > 0)
                                        {{ number_format($row['penjualan_harga'], 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(isset($row['penjualan_total']) && $row['penjualan_total'] > 0)
                                        {{ number_format($row['penjualan_total'], 0, ',', '.') }}
                                    @endif
                                </td>
                            @else
                                <td class="text-right">
                                    @if($row['pembelian_qty'] != 0)
                                        @php
                                            $isReturMasuk = isset($row['ref_type']) && $row['ref_type'] === 'retur_tukar_terima';
                                        @endphp
                                        @if($row['pembelian_qty'] < 0)
                                            <span style="color: red;">-{{ number_format(abs($row['pembelian_qty']), 0, ',', '.') }}</span>
                                        @elseif($isReturMasuk)
                                            <span style="color: green;">+{{ number_format($row['pembelian_qty'], 0, ',', '.') }}</span>
                                        @else
                                            {{ number_format($row['pembelian_qty'], 0, ',', '.') }}
                                        @endif
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($row['pembelian_harga'] > 0)
                                        {{ number_format($row['pembelian_harga'], 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($row['pembelian_total'] != 0)
                                        @if($row['pembelian_total'] < 0)
                                            <span style="color: red;">-{{ number_format(abs($row['pembelian_total']), 0, ',', '.') }}</span>
                                        @else
                                            {{ number_format($row['pembelian_total'], 0, ',', '.') }}
                                        @endif
                                    @endif
                                </td>
                            @endif
                            
                            <!-- Produksi -->
                            <td class="text-right">
                                @if($row['produksi_qty'] > 0)
                                    {{ number_format($row['produksi_qty'], 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-right">
                                @if($row['produksi_harga'] > 0)
                                    {{ number_format($row['produksi_harga'], 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-right">
                                @if($row['produksi_total'] > 0)
                                    {{ number_format($row['produksi_total'], 0, ',', '.') }}
                                @endif
                            </td>
                            
                            <!-- Saldo Akhir -->
                            <td class="text-right" style="font-weight: bold;">
                                {{ number_format($row['saldo_akhir_qty'], 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($row['saldo_akhir_harga'], 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($row['saldo_akhir_total'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="14" style="text-align: center; color: #666;">Tidak ada data transaksi</td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <p><strong>UMKM COE - Sistem Manajemen UMKM Center of Excellence</strong></p>
        <p>Laporan Kartu Stok - {{ $summary['item_name'] ?? 'Item' }}</p>
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Pembelian - {{ $pembelian->nomor_pembelian }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .container {
            padding: 15px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .company-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
        }

        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 8px;
        }

        /* Info Section */
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-row {
            width: 100%;
            margin-bottom: 12px;
        }

        .info-left {
            width: 48%;
            float: left;
        }

        .info-right {
            width: 48%;
            float: right;
        }

        .info-group {
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .info-value {
            color: #333;
            padding-left: 8px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .items-table th {
            background-color: #34495e;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
            font-size: 10px;
        }

        .items-table td {
            padding: 6px;
            border: 1px solid #bdc3c7;
            vertical-align: top;
            font-size: 10px;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        /* Totals */
        .totals-section {
            width: 250px;
            float: right;
            margin-top: 15px;
        }

        .total-row {
            width: 100%;
            padding: 4px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .total-row::after {
            content: "";
            display: table;
            clear: both;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #2c3e50;
            border-bottom: 2px solid #2c3e50;
            background-color: #f8f9fa;
            padding: 8px 0;
        }

        .total-label {
            font-weight: bold;
            float: left;
        }

        .total-value {
            font-weight: bold;
            float: right;
        }

        /* Footer */
        .footer {
            clear: both;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .item-name {
            font-weight: bold;
        }

        .item-type {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-info">
                {{ $company['address'] }}<br>
                Telp: {{ $company['phone'] }} | Email: {{ $company['email'] }}
            </div>
            <div class="invoice-title">FAKTUR PEMBELIAN</div>
        </div>

        <!-- Info Section -->
        <div class="info-section clearfix">
            <div class="info-left">
                <div class="info-group">
                    <div class="info-label">No. Transaksi:</div>
                    <div class="info-value">{{ $pembelian->nomor_pembelian }}</div>
                </div>
                
                @if($pembelian->nomor_faktur)
                <div class="info-group">
                    <div class="info-label">No. Faktur:</div>
                    <div class="info-value">{{ $pembelian->nomor_faktur }}</div>
                </div>
                @endif
                
                <div class="info-group">
                    <div class="info-label">Tanggal:</div>
                    <div class="info-value">{{ $pembelian->tanggal->format('d F Y') }}</div>
                </div>
            </div>
            
            <div class="info-right">
                <div class="info-group">
                    <div class="info-label">Vendor:</div>
                    <div class="info-value">
                        <strong>{{ $pembelian->vendor->nama_vendor ?? '-' }}</strong><br>
                        @if($pembelian->vendor->alamat)
                            {{ $pembelian->vendor->alamat }}<br>
                        @endif
                        @if($pembelian->vendor->telepon)
                            Telp: {{ $pembelian->vendor->telepon }}
                        @endif
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Metode Pembayaran:</div>
                    <div class="info-value">
                        @php
                            $paymentMethod = $pembelian->payment_method ?? 'cash';
                            if ($paymentMethod === 'credit') {
                                echo 'Kredit';
                            } elseif ($paymentMethod === 'transfer') {
                                echo 'Transfer';
                            } else {
                                echo 'Tunai';
                            }
                        @endphp
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">No</th>
                    <th>Nama Barang</th>
                    <th class="text-center" style="width: 60px;">Qty</th>
                    <th class="text-center" style="width: 60px;">Satuan</th>
                    <th class="text-right" style="width: 80px;">Harga Satuan</th>
                    <th class="text-right" style="width: 90px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembelian->details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            @if($detail->bahan_baku_id && $detail->bahanBaku)
                                <div class="item-name">{{ $detail->bahanBaku->nama_bahan }}</div>
                                <div class="item-type">Bahan Baku</div>
                            @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                <div class="item-name">{{ $detail->bahanPendukung->nama_bahan }}</div>
                                <div class="item-type">Bahan Pendukung</div>
                            @else
                                <span style="color: #999;">Item tidak diketahui</span>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $detail->satuan_nama ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 15px; color: #999;">
                            Tidak ada item pembelian
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            
            @if($ppnNominal > 0)
            <div class="total-row">
                <span class="total-label">PPN ({{ $pembelian->ppn_persen ?? 0 }}%):</span>
                <span class="total-value">Rp {{ number_format($ppnNominal, 0, ',', '.') }}</span>
            </div>
            @endif
            
            @if($biayaKirim > 0)
            <div class="total-row">
                <span class="total-label">Biaya Kirim:</span>
                <span class="total-value">Rp {{ number_format($biayaKirim, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="total-row grand-total">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Faktur ini digenerate pada {{ now()->format('d F Y H:i:s') }}</p>
            <p>{{ $company['name'] }} - Sistem Manajemen UMKM</p>
        </div>
    </div>
</body>
</html>
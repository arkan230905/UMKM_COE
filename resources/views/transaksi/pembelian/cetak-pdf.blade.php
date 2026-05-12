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
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
            margin: 0;
            padding: 20px;
        }

        .invoice-container {
            max-width: 210mm;
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 0;
        }

        /* Print Settings */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .invoice-container {
                width: 100%;
                max-width: 100%;
                padding: 0;
            }
        }

        /* Header with date and invoice number */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 11px;
            color: #666;
        }

        .print-date {
            font-weight: normal;
        }

        .invoice-number {
            font-weight: normal;
        }

        /* Company Header */
        .company-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            letter-spacing: 2px;
        }

        .company-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 15px;
            letter-spacing: 1px;
        }

        /* Separator Line */
        .separator {
            height: 2px;
            background: #333;
            margin: 30px 0;
        }

        /* Info Section - Two Columns */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 100px;
        }

        .info-left, .info-right {
            flex: 1;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .info-value {
            color: #333;
            font-size: 12px;
            margin-left: 10px;
        }

        .vendor-name {
            font-weight: bold;
            color: #333;
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border: 1px solid #333;
        }

        .items-table th {
            background: #333;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #333;
            font-size: 12px;
        }

        .items-table th.text-left {
            text-align: left;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 12px 8px;
            border: 1px solid #333;
            vertical-align: top;
            font-size: 11px;
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

        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
        }

        .item-type {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }

        /* Totals Section - Right Aligned */
        .totals-section {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 12px;
        }

        .total-row.subtotal {
            border-bottom: none;
        }

        .total-row.ppn {
            border-bottom: none;
        }

        .total-row.grand-total {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            font-weight: bold;
            font-size: 13px;
            padding: 10px 0;
            margin-top: 5px;
        }

        .total-label {
            font-weight: bold;
        }

        .total-value {
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }

        /* Print adjustments */
        @media print {
            .info-section {
                display: block;
            }
            
            .info-left, .info-right {
                width: 48%;
                float: left;
                margin-right: 4%;
            }
            
            .info-right {
                margin-right: 0;
                float: right;
            }
            
            .totals-section {
                float: right;
                clear: both;
            }
            
            .footer {
                clear: both;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Top Header with Date and Invoice Number -->
        <div class="top-header">
            <div class="print-date">{{ now()->format('j/n/y, g:i A') }}</div>
            <div class="invoice-number">Faktur Pembelian - {{ $pembelian->nomor_pembelian }}</div>
        </div>

        <!-- Company Header -->
        <div class="company-header">
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-info">
                {{ $company['address'] }}<br>
                Telp: {{ $company['phone'] }} | Email: {{ $company['email'] }}
            </div>
            <div class="invoice-title">FAKTUR PEMBELIAN</div>
        </div>

        <!-- Separator -->
        <div class="separator"></div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-item">
                    <div class="info-label">No. Transaksi:</div>
                    <div class="info-value">{{ $pembelian->nomor_pembelian }}</div>
                </div>
                
                @if($pembelian->nomor_faktur)
                <div class="info-item">
                    <div class="info-label">No. Faktur:</div>
                    <div class="info-value">{{ $pembelian->nomor_faktur }}</div>
                </div>
                @endif
                
                <div class="info-item">
                    <div class="info-label">Tanggal:</div>
                    <div class="info-value">{{ $pembelian->tanggal->format('d F Y') }}</div>
                </div>
            </div>
            
            <div class="info-right">
                <div class="info-item">
                    <div class="info-label">Vendor:</div>
                    <div class="info-value">
                        <div class="vendor-name">{{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                        @if($pembelian->vendor->alamat)
                            {{ $pembelian->vendor->alamat }}<br>
                        @endif
                        @if($pembelian->vendor->telepon)
                            Telp: {{ $pembelian->vendor->telepon }}
                        @endif
                    </div>
                </div>
                
                <div class="info-item">
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
                    <th class="text-center" style="width: 40px;">No</th>
                    <th class="text-left">Nama Barang</th>
                    <th class="text-center" style="width: 60px;">Qty</th>
                    <th class="text-center" style="width: 80px;">Satuan</th>
                    <th class="text-right" style="width: 100px;">Harga Satuan</th>
                    <th class="text-right" style="width: 100px;">Subtotal</th>
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
                        <td colspan="6" class="text-center" style="padding: 20px; color: #999; font-style: italic;">
                            Tidak ada item pembelian
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row subtotal">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            
            @if($ppnNominal > 0)
            <div class="total-row ppn">
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
            Faktur ini dicetak pada {{ now()->format('d F Y H:i:s') }}<br>
            {{ $company['name'] }} - Sistem Manajemen UMKM
        </div>
    </div>
</body>
</html>
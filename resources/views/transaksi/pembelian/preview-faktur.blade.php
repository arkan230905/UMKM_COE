<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Faktur Pembelian - {{ $pembelian->nomor_pembelian }}</title>
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
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 210mm;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        /* Print Settings */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .container {
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                padding: 30px;
                border-radius: 0;
            }
            .print-buttons {
                display: none;
            }
        }

        /* Print Buttons */
        .print-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #34495e;
        }

        .btn-print {
            background: #27ae60;
        }

        .btn-print:hover {
            background: #2ecc71;
        }

        /* Top Header with date and invoice number */
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

        /* Info Section - Using Flexbox */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 60px;
        }

        .info-left, .info-right {
            flex: 1;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            padding-left: 15px;
            font-size: 14px;
            line-height: 1.4;
        }

        .vendor-name {
            font-weight: bold;
            font-size: 16px;
            color: #2c3e50;
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .items-table th {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 12px 10px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #2c3e50;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .items-table th.text-left {
            text-align: left;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: middle;
            font-size: 12px;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .items-table tbody tr:hover {
            background-color: #e8f4f8;
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
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .item-type {
            font-size: 10px;
            color: #666;
            font-style: italic;
            background: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }

        /* Totals Section */
        .totals-section {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
            border: 2px solid #2c3e50;
            border-radius: 5px;
            overflow: hidden;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
            border-bottom: 1px solid #ecf0f1;
            background: white;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-row.grand-total {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            font-weight: bold;
            font-size: 14px;
            padding: 12px 15px;
        }

        .total-label {
            font-weight: 600;
        }

        .total-value {
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .footer-line {
            margin-bottom: 5px;
        }

        .print-date {
            font-weight: bold;
            color: #2c3e50;
        }

        /* Responsive adjustments */
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
    <!-- Print Buttons -->
    <div class="print-buttons">
        <a href="javascript:window.print()" class="btn btn-print">🖨️ Print Faktur</a>
        <a href="{{ route('transaksi.pembelian.cetak-pdf', $pembelian->id) }}" class="btn">📄 Download PDF</a>
        <a href="{{ route('transaksi.pembelian.index') }}" class="btn">← Kembali ke Daftar</a>
    </div>

    <div class="container">
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
                        <div class="vendor-name">{{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
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
                                echo 'Transfer Bank';
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
                    <th class="text-center" style="width: 70px;">Qty</th>
                    <th class="text-center" style="width: 70px;">Satuan</th>
                    <th class="text-right" style="width: 100px;">Harga Satuan</th>
                    <th class="text-right" style="width: 110px;">Subtotal</th>
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
            <div class="footer-line print-date">Dicetak pada: {{ now()->format('d F Y, H:i:s') }}</div>
            <div class="footer-line">{{ $company['name'] }} - Sistem Manajemen UMKM</div>
            <div class="footer-line" style="margin-top: 10px; font-size: 10px;">
                Dokumen ini digenerate secara otomatis oleh sistem
            </div>
        </div>
    </div>
</body>
</html>
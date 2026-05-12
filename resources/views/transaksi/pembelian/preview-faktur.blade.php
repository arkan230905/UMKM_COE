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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }

        /* Print Buttons */
        .print-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-buttons .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s;
        }

        .print-buttons .btn:hover {
            background: #555;
        }

        .print-buttons .btn-print {
            background: #28a745;
        }

        .print-buttons .btn-print:hover {
            background: #218838;
        }

        /* Invoice Container */
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .company-details {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }

        .invoice-title-section {
            text-align: right;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .invoice-subtitle {
            font-size: 13px;
            color: #666;
        }

        /* Transaction Info - Two Columns */
        .transaction-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-column {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-row {
            display: flex;
            font-size: 14px;
        }

        .info-label {
            width: 120px;
            color: #666;
            font-weight: 500;
        }

        .info-value {
            flex: 1;
            color: #333;
            font-weight: 600;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background: #f8f9fa;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
            text-transform: uppercase;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .items-table tbody tr:hover {
            background: #f8f9fa;
        }

        .item-name {
            font-weight: 500;
            color: #333;
        }

        .item-type {
            font-size: 12px;
            color: #6c757d;
            margin-top: 2px;
        }

        /* Totals Section */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }

        .totals-box {
            width: 350px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .total-row.subtotal {
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
        }

        .total-row.grand-total {
            border-top: 2px solid #333;
            padding-top: 12px;
            margin-top: 8px;
            font-size: 16px;
            font-weight: bold;
        }

        .total-label {
            color: #666;
        }

        .total-value {
            font-weight: 600;
            color: #333;
        }

        .grand-total .total-label,
        .grand-total .total-value {
            color: #333;
            font-weight: bold;
        }

        /* Footer */
        .invoice-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #6c757d;
        }

        .footer-line {
            margin-bottom: 5px;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .invoice-container {
                max-width: 100%;
                box-shadow: none;
                padding: 20px;
            }

            .print-buttons {
                display: none;
            }

            .invoice-header {
                margin-bottom: 30px;
                padding-bottom: 15px;
            }

            .transaction-info {
                margin-bottom: 20px;
            }

            .items-table {
                margin-bottom: 20px;
            }

            .totals-section {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Buttons -->
    <div class="print-buttons">
        <a href="javascript:window.print()" class="btn btn-print">🖨️ Print Faktur</a>
        <a href="{{ route('transaksi.pembelian.cetak-pdf', $pembelian->id) }}" class="btn">📄 Download PDF</a>
        <a href="{{ route('transaksi.pembelian.index') }}" class="btn">← Kembali</a>
    </div>

    <!-- Invoice Container -->
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-details">
                    {{ $company['address'] }}<br>
                    Telp: {{ $company['phone'] }}<br>
                    Email: {{ $company['email'] }}
                </div>
            </div>
            <div class="invoice-title-section">
                <div class="invoice-title">FAKTUR PEMBELIAN</div>
                <div class="invoice-subtitle">Purchase Invoice</div>
            </div>
        </div>

        <!-- Transaction Info - Two Columns -->
        <div class="transaction-info">
            <div class="info-column">
                <div class="info-row">
                    <span class="info-label">No Transaksi</span>
                    <span class="info-value">{{ $pembelian->nomor_pembelian }}</span>
                </div>
                @if($pembelian->nomor_faktur)
                <div class="info-row">
                    <span class="info-label">No Faktur</span>
                    <span class="info-value">{{ $pembelian->nomor_faktur }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Tanggal</span>
                    <span class="info-value">{{ $pembelian->tanggal->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="info-column">
                <div class="info-row">
                    <span class="info-label">Vendor</span>
                    <span class="info-value">{{ $pembelian->vendor->nama_vendor ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Bayar</span>
                    <span class="info-value">
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
                    </span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 45%">Nama Barang</th>
                    <th class="text-center" style="width: 15%">Qty</th>
                    <th class="text-right" style="width: 17.5%">Harga</th>
                    <th class="text-right" style="width: 17.5%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembelian->details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="item-name">
                                @if($detail->bahan_baku_id && $detail->bahanBaku)
                                    {{ $detail->bahanBaku->nama_bahan }}
                                @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                    {{ $detail->bahanPendukung->nama_bahan }}
                                @else
                                    Item tidak diketahui
                                @endif
                            </div>
                            <div class="item-type">
                                @if($detail->bahan_baku_id)
                                    Bahan Baku
                                @elseif($detail->bahan_pendukung_id)
                                    Bahan Pendukung
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            {{ number_format($detail->jumlah, 0, ',', '.') }} {{ $detail->satuan_nama ?? 'pcs' }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada item pembelian</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row subtotal">
                    <span class="total-label">Subtotal</span>
                    <span class="total-value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                
                @if($ppnNominal > 0)
                <div class="total-row">
                    <span class="total-label">PPN {{ $pembelian->ppn_persen ?? 0 }}%</span>
                    <span class="total-value">Rp {{ number_format($ppnNominal, 0, ',', '.') }}</span>
                </div>
                @endif
                
                @if($biayaKirim > 0)
                <div class="total-row">
                    <span class="total-label">Biaya Kirim</span>
                    <span class="total-value">Rp {{ number_format($biayaKirim, 0, ',', '.') }}</span>
                </div>
                @endif
                
                <div class="total-row grand-total">
                    <span class="total-label">TOTAL</span>
                    <span class="total-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-line">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
            <div class="footer-line">Terima kasih atas kerja sama Anda</div>
        </div>
    </div>
</body>
</html>
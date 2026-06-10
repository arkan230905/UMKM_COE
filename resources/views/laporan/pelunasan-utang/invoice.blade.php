<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice Pelunasan Utang - {{ $pelunasanUtang->kode_transaksi }}</title>
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
            padding: 20mm 20mm;
            background: #fff;
        }
        
        /* Header Section */
        .header { 
            text-align: center; 
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #6B4F3A;
        }
        
        .header h1 { 
            font-size: 24px;
            font-weight: bold;
            color: #6B4F3A;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .header .invoice-number {
            font-size: 11px;
            color: #666;
            font-weight: 600;
        }
        
        /* Two Column Info Layout */
        .info-wrapper {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        
        .info-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .info-column.right {
            padding-right: 0;
            padding-left: 15px;
            border-left: 1px solid #E8E8E8;
        }
        
        .info-row {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #6B4F3A;
            display: inline-block;
            width: 40%;
            font-size: 10px;
        }
        
        .info-separator {
            display: inline-block;
            width: 5%;
            text-align: center;
        }
        
        .info-value {
            color: #333;
            display: inline-block;
            width: 52%;
            font-size: 10px;
        }
        
        /* Table Styles */
        table.detail-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0 15px 0;
            font-size: 10px;
        }
        
        table.detail-table thead th {
            background-color: #F5EDE5;
            color: #6B4F3A;
            font-weight: bold;
            text-align: center;
            padding: 10px 8px;
            border: 1px solid #D4C4B0;
        }
        
        table.detail-table tbody td {
            padding: 8px;
            border: 1px solid #E8E8E8;
            vertical-align: middle;
        }
        
        table.detail-table tbody tr:nth-child(even) {
            background-color: #FAFAFA;
        }
        
        /* Summary Box */
        .summary-box {
            background-color: #F8F6F3;
            padding: 15px 20px;
            margin: 15px 0;
            border: 1px solid #D4C4B0;
            border-radius: 4px;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .summary-row:last-child {
            margin-bottom: 0;
        }
        
        .summary-label {
            display: table-cell;
            text-align: right;
            padding-right: 30px;
            font-weight: 600;
            color: #6B4F3A;
            width: 70%;
        }
        
        .summary-value {
            display: table-cell;
            text-align: right;
            color: #333;
            font-weight: 600;
            width: 30%;
        }
        
        .summary-row.highlight {
            font-size: 11px;
            font-weight: bold;
            padding: 5px 0;
        }
        
        /* Footer Section */
        .footer-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #E8E8E8;
            display: table;
            width: 100%;
        }
        
        .footer-left {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
        }
        
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: middle;
            font-size: 9px;
            color: #666;
        }
        
        .footer-right .icon {
            display: inline-block;
            margin-right: 5px;
            font-weight: bold;
        }
        
        /* Status Badge */
        .badge { 
            display: inline-block;
            padding: 4px 12px; 
            border-radius: 4px; 
            font-size: 9px;
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
        
        /* Bottom Note */
        .bottom-note {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #E8E8E8;
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
        
        /* Text Alignment */
        .text-right { 
            text-align: right; 
        }
        
        .text-center { 
            text-align: center; 
        }
        
        .text-left {
            text-align: left;
        }
        
        /* Print Optimization */
        @page {
            margin: 15mm;
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
        <h1>INVOICE PELUNASAN UTANG</h1>
        <div class="invoice-number">{{ $pelunasanUtang->kode_transaksi }}</div>
    </div>

    <!-- Two Column Info Section -->
    <div class="info-wrapper">
        <!-- Left Column: Pelunasan Info -->
        <div class="info-column">
            <div class="info-row">
                <span class="info-label">Nomor Pelunasan</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ $pelunasanUtang->kode_transaksi }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pelunasan</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ $pelunasanUtang->tanggal->locale('id')->isoFormat('D MMMM YYYY') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nomor Faktur</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ $pelunasanUtang->pembelian->nomor_faktur ?? $pelunasanUtang->pembelian->nomor_pembelian ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Akun Kas</span>
                <span class="info-separator">:</span>
                <span class="info-value">
                    @if($pelunasanUtang->akunKas)
                        {{ $pelunasanUtang->akunKas->kode_akun }} - {{ $pelunasanUtang->akunKas->nama_akun }}
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>
        
        <!-- Right Column: Vendor Info -->
        <div class="info-column right">
            <div class="info-row">
                <span class="info-label">Vendor</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ $pelunasanUtang->pembelian->vendor->nama_vendor ?? '-' }}</span>
            </div>
            @if($pelunasanUtang->pembelian->vendor)
            <div class="info-row">
                <span class="info-label">Alamat</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ $pelunasanUtang->pembelian->vendor->alamat ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Telepon</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ $pelunasanUtang->pembelian->vendor->telepon ?? '-' }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Detail Pembelian Table -->
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 52%;" class="text-left">Item</th>
                <th style="width: 18%;">Jumlah</th>
                <th style="width: 12%;">Harga Satuan</th>
                <th style="width: 13%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelunasanUtang->pembelian->details as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left">
                    @if($detail->bahanBaku)
                        <strong>{{ $detail->bahanBaku->nama_bahan }}</strong>
                    @elseif($detail->bahanPendukung)
                        <strong>{{ $detail->bahanPendukung->nama_bahan }}</strong>
                    @else
                        Item {{ $index + 1 }}
                    @endif
                </td>
                <td class="text-center">
                    {{ number_format($detail->jumlah, 0, ',', '.') }} 
                    {{ $detail->satuan_nama ?? 'unit' }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-row">
            <div class="summary-label">Total Tagihan</div>
            <div class="summary-value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Total Refund</div>
            <div class="summary-value">Rp {{ number_format($totalRefund, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row highlight">
            <div class="summary-label">Total Dibayar</div>
            <div class="summary-value">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
        <div class="footer-left">
            <span style="font-size: 10px; color: #6B4F3A; font-weight: 600; margin-right: 10px;">Status Pembayaran</span>
            @if($sisaUtang == 0)
                <span class="badge badge-success">Lunas</span>
            @else
                <span class="badge badge-warning">Sebagian</span>
            @endif
        </div>
        <div class="footer-right">
            Tanggal Cetak : {{ now()->format('d/m/Y') }}
            &nbsp;&nbsp;&nbsp;
            Jam Cetak : {{ now()->format('H:i:s') }}
        </div>
    </div>

    <!-- Bottom Note -->
    <div class="bottom-note">
        Terima kasih atas kepercayaan Anda.
    </div>
</body>
</html>

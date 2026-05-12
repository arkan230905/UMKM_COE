<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembelian #{{ $pembelian->nomor_pembelian ?? 'PB-' . date('Y') . '-' . str_pad($pembelian->id, 3, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Header - 2 Columns */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #8B4513;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 6px;
        }

        .company-details {
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }

        .invoice-title-section {
            text-align: right;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 6px;
        }

        .invoice-meta {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-meta strong {
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .status-lunas {
            background: #d4edda;
            color: #155724;
        }

        .status-belum-lunas {
            background: #fff3cd;
            color: #856404;
        }

        /* Information Section - 2 Columns */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 14px;
            background: #fafafa;
        }

        .info-card-title {
            font-size: 13px;
            font-weight: 600;
            color: #8B4513;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-row {
            display: flex;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .info-label {
            width: 110px;
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
            margin-bottom: 20px;
        }

        .items-table thead {
            background: #8B4513;
            color: white;
        }

        .items-table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 12px;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table tbody tr:hover {
            background: #f8f9fa;
        }

        .item-name {
            font-weight: 500;
            color: #333;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .summary-box {
            width: 320px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 16px;
            font-size: 13px;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-row.subtotal {
            background: #f8f9fa;
        }

        .summary-row.grand-total {
            background: #8B4513;
            color: white;
            font-size: 15px;
            font-weight: bold;
            border-bottom: none;
        }

        .summary-label {
            font-weight: 500;
        }

        .summary-value {
            font-weight: 600;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #ddd;
            text-align: center;
        }

        .footer-note {
            font-size: 11px;
            color: #666;
            font-style: italic;
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
                padding: 12mm;
                margin: 0;
            }

            .items-table {
                page-break-inside: avoid;
            }

            .summary-section {
                page-break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header - 2 Columns -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-name">UMKM COE</div>
                <div class="company-details">
                    Jl. Contoh Alamat No. 123<br>
                    Kota, Provinsi 12345<br>
                    Telp: (021) 1234-5678<br>
                    Email: info@umkmcoe.com
                </div>
            </div>
            <div class="invoice-title-section">
                <div class="invoice-title">INVOICE PEMBELIAN</div>
                <div class="invoice-meta">
                    <strong>No. Invoice:</strong> {{ $pembelian->nomor_pembelian ?? 'PB-' . date('Y') . '-' . str_pad($pembelian->id, 3, '0', STR_PAD_LEFT) }}<br>
                    <strong>Tanggal:</strong> {{ optional($pembelian->tanggal)->format('d/m/Y') ?? date('d/m/Y') }}<br>
                    @if($pembelian->status === 'lunas')
                        <span class="status-badge status-lunas">Lunas</span>
                    @else
                        <span class="status-badge status-belum-lunas">Belum Lunas</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Information Section - 2 Columns -->
        <div class="info-section">
            <div class="info-card">
                <div class="info-card-title">Informasi Vendor</div>
                <div class="info-row">
                    <span class="info-label">Vendor</span>
                    <span class="info-value">{{ $pembelian->vendor->nama_vendor ?? '-' }}</span>
                </div>
                @if($pembelian->vendor->alamat)
                <div class="info-row">
                    <span class="info-label">Alamat</span>
                    <span class="info-value">{{ $pembelian->vendor->alamat }}</span>
                </div>
                @endif
                @if($pembelian->vendor->telepon)
                <div class="info-row">
                    <span class="info-label">Telepon</span>
                    <span class="info-value">{{ $pembelian->vendor->telepon }}</span>
                </div>
                @endif
            </div>
            <div class="info-card">
                <div class="info-card-title">Informasi Transaksi</div>
                @if($pembelian->nomor_faktur)
                <div class="info-row">
                    <span class="info-label">No. Faktur</span>
                    <span class="info-value">{{ $pembelian->nomor_faktur }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Metode Bayar</span>
                    <span class="info-value">
                        @if($pembelian->payment_method === 'cash')
                            Tunai
                        @elseif($pembelian->payment_method === 'transfer')
                            Transfer
                        @else
                            Kredit
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal</span>
                    <span class="info-value">{{ optional($pembelian->tanggal)->format('d F Y') ?? date('d F Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 40%">Nama Item</th>
                    <th class="text-center" style="width: 12%">Qty</th>
                    <th class="text-center" style="width: 10%">Satuan</th>
                    <th class="text-right" style="width: 16%">Harga Satuan</th>
                    <th class="text-right" style="width: 17%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                @endphp
                @foreach(($pembelian->details ?? []) as $i => $d)
                    @php
                        $itemName = 'Item';
                        $satuanName = 'unit';
                        
                        if ($d->bahanBaku) {
                            $itemName = $d->bahanBaku->nama_bahan;
                            $satuanName = $d->bahanBaku->satuan->nama ?? 'unit';
                        } elseif ($d->bahanPendukung) {
                            $itemName = $d->bahanPendukung->nama_bahan;
                            $satuanName = $d->bahanPendukung->satuanRelation->nama ?? 'unit';
                        }
                        
                        $itemSubtotal = ($d->jumlah ?? 0) * ($d->harga_satuan ?? 0);
                        $subtotal += $itemSubtotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td class="item-name">{{ $itemName }}</td>
                        <td class="text-center">{{ number_format($d->jumlah, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $satuanName }}</td>
                        <td class="text-right">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-box">
                <div class="summary-row subtotal">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if(isset($pembelian->ppn_persen) && $pembelian->ppn_persen > 0)
                <div class="summary-row">
                    <span class="summary-label">PPN {{ $pembelian->ppn_persen }}%</span>
                    <span class="summary-value">Rp {{ number_format($pembelian->ppn_nominal ?? 0, 0, ',', '.') }}</span>
                </div>
                @endif
                @if(isset($pembelian->biaya_kirim) && $pembelian->biaya_kirim > 0)
                <div class="summary-row">
                    <span class="summary-label">Biaya Kirim</span>
                    <span class="summary-value">Rp {{ number_format($pembelian->biaya_kirim, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="summary-row grand-total">
                    <span class="summary-label">GRAND TOTAL</span>
                    <span class="summary-value">Rp {{ number_format($pembelian->total_harga ?? $subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-note">
                Invoice ini dibuat otomatis oleh sistem SIMCOST
            </div>
        </div>
    </div>
</body>
</html>

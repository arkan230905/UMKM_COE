<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .struk-container {
            max-width: 280px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 15px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
            text-align: center;
        }
        .company-address, .company-phone {
            font-size: 11px;
            margin-bottom: 2px;
            text-align: center;
        }
        .struk-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .transaction-info {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .separator {
            border-top: 1px dashed #333;
            margin: 10px 0;
        }
        .product-item {
            margin-bottom: 8px;
        }
        .product-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .product-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .product-total {
            font-weight: bold;
            text-align: right;
        }
        .payment-info {
            margin-top: 15px;
        }
        .products {
            margin: 20px 0;
            border-top: 1px dashed #ddd;
            padding-top: 15px;
        }
        .product-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
            line-height: 1.6;
        }
        .product-name {
            flex: 1;
            padding-right: 10px;
        }
        .product-qty {
            width: 40px;
            text-align: center;
        }
        .product-price {
            width: 80px;
            text-align: right;
        }
        .product-total {
            width: 80px;
            text-align: right;
            font-weight: bold;
        }
        .summary {
            margin-top: 25px;
            border-top: 2px dashed #333;
            padding-top: 15px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            margin-top: 10px;
        }
        .summary-row span:first-child {
            text-align: left;
        }
        .summary-row span:last-child {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 10px;
            color: #666;
            line-height: 1.5;
        }
        @media print {
            body { padding: 0; }
            .struk-container { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="struk-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $dataPerusahaan->nama_perusahaan ?? 'MANUFAKTUR COE' }}</div>
            <div class="company-address">{{ $dataPerusahaan->alamat_perusahaan ?? 'Jl. Kebon No. 123' }}</div>
            <div class="company-phone">{{ $dataPerusahaan->telepon_perusahaan ?? 'Telp: 0812-3456-7890' }}</div>
        </div>

        <!-- Garis Pemisah -->
        <div class="separator"></div>
        
        <!-- Informasi Transaksi -->
        <div class="transaction-info">
            <div class="info-row">
                <span>No. Transaksi   :</span>
                <span>{{ $penjualan->nomor_penjualan ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span>Tanggal          :</span>
                <span>{{ optional($penjualan->tanggal)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span>Kasir            :</span>
                <span>TIM COE PROCESS COSTING</span>
            </div>
        </div>

        <!-- Products -->
        <div class="products">
            @php $totalItems = 0; @endphp
            @if($penjualan->details->count() > 0)
                @foreach($penjualan->details as $detail)
                    <div class="product-item">
                        <div class="product-name">{{ $detail->produk->nama_produk }}</div>
                        <div class="product-detail">
                            <span>{{ $detail->jumlah }} x {{ number_format($detail->harga_satuan, 0, ',', '.') }}</span>
                            <span class="product-total">Rp {{ number_format($detail->jumlah * $detail->harga_satuan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @php $totalItems += $detail->jumlah; @endphp
                @endforeach
            @else
                @if($penjualan->produk)
                <div class="product-item">
                    <div class="product-name">{{ $penjualan->produk->nama_produk }}</div>
                    <div class="product-detail">
                        <span>{{ ($penjualan->jumlah ?? 0) }} x {{ number_format($penjualan->harga_satuan ?? 0, 0, ',', '.') }}</span>
                        <span class="product-total">Rp {{ number_format(($penjualan->harga_satuan ?? 0) * ($penjualan->jumlah ?? 0), 0, ',', '.') }}</span>
                    </div>
                </div>
                @php $totalItems = $penjualan->jumlah ?? 0; @endphp
                @endif
            @endif
        </div>

        <!-- Garis Pemisah -->
        <div class="separator"></div>
        
        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal           </span>
                <span>Rp {{ number_format($penjualan->total + ($penjualan->diskon_nominal ?? 0), 0, ',', '.') }}</span>
            </div>
            @if($penjualan->diskon_nominal > 0)
            <div class="summary-row">
                <span>Diskon             </span>
                <span>-{{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="summary-row total">
                <span>TOTAL:             </span>
                <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Pembayaran -->
        <div class="payment-info">
            <div class="info-row">
                <span>Pembayaran:       </span>
                <span>{{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : (($penjualan->payment_method ?? 'cash') === 'transfer' ? 'Transfer' : 'Tunai') }}</span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div>Terima kasih atas kunjungan Anda!</div>
        </div>
    </div>
</body>
</html>

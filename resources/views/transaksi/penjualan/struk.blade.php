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
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
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
            margin-bottom: 5px;
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
            <div class="company-name">{{ $dataPerusahaan->nama_perusahaan ?? 'UMKM COE' }}</div>
            <div class="struk-title">STRUK PENJUALAN</div>
            <div class="info-row">
                <span>No: {{ 'SJ-' . optional($penjualan->tanggal)->format('ymd') . '-' . str_pad($penjualan->id, 3, '0', STR_PAD_LEFT) }}</span>
                <span>{{ optional($penjualan->tanggal)->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Transaction Info -->
        <div class="transaction-info">
            <div class="info-row">
                <span>Metode:</span>
                <span>{{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}</span>
            </div>
            @if($penjualan->pelanggan)
            <div class="info-row">
                <span>Pelanggan:</span>
                <span>{{ $penjualan->pelanggan->nama_pelanggan }}</span>
            </div>
            @endif
        </div>

        <!-- Products -->
        <div class="products">
            @php $totalItems = 0; @endphp
            @if($penjualan->details->count() > 0)
                @foreach($penjualan->details as $detail)
                    <div class="product-row">
                        <div class="product-name">{{ $detail->produk->nama_produk }}</div>
                        <div class="product-qty">{{ $detail->jumlah }}</div>
                        <div class="product-price">{{ number_format($detail->harga_satuan, 0, ',', '.') }}</div>
                        <div class="product-total">{{ number_format($detail->jumlah * $detail->harga_satuan, 0, ',', '.') }}</div>
                    </div>
                    @php $totalItems += $detail->jumlah; @endphp
                @endforeach
            @else
                @if($penjualan->produk)
                <div class="product-row">
                    <div class="product-name">{{ $penjualan->produk->nama_produk }}</div>
                    <div class="product-qty">{{ $penjualan->jumlah }}</div>
                    <div class="product-price">{{ number_format($penjualan->harga_satuan ?? 0, 0, ',', '.') }}</div>
                    <div class="product-total">{{ number_format(($penjualan->harga_satuan ?? 0) * ($penjualan->jumlah ?? 0), 0, ',', '.') }}</div>
                </div>
                @php $totalItems = $penjualan->jumlah ?? 0; @endphp
                @endif
            @endif
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>{{ number_format($penjualan->total + ($penjualan->diskon_nominal ?? 0), 0, ',', '.') }}</span>
            </div>
            @if($penjualan->diskon_nominal > 0)
            <div class="summary-row">
                <span>Diskon:</span>
                <span>-{{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="summary-row total">
                <span>TOTAL:</span>
                <span>{{ number_format($penjualan->total, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Total Item:</span>
                <span>{{ $totalItems }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Terima Kasih</div>
            <div>{{ $dataPerusahaan->alamat_perusahaan ?? '' }}</div>
            <div>Telp: {{ $dataPerusahaan->telepon_perusahaan ?? '' }}</div>
        </div>
    </div>
</body>
</html>

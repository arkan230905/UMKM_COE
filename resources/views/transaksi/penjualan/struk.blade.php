<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan</title>
    <style>
        body {
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            background: white;
            color: #000;
            line-height: 1.4;
        }
        .struk-container {
            max-width: 300px;
            margin: 0 auto;
            padding: 15px 10px;
            background: white;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            min-height: 400px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .company-address, .company-phone {
            font-size: 11px;
            margin-bottom: 3px;
            text-align: center;
            color: #000;
        }
        .transaction-info {
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
            padding: 2px 0;
        }
        .separator {
            border: none;
            height: 1px;
            background: repeating-linear-gradient(
                90deg,
                #000,
                #000 4px,
                transparent 4px,
                transparent 8px
            );
            margin: 15px 0;
            clear: both;
        }
        .product-item {
            margin-bottom: 10px;
            padding: 3px 0;
        }
        .product-name {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 12px;
            color: #000;
            line-height: 1.3;
        }
        .product-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            padding: 2px 0;
        }
        .product-total {
            font-weight: bold;
            text-align: right;
            color: #000;
            font-size: 11px;
        }
        .products {
            margin: 15px 0;
        }
        .summary {
            margin-top: 15px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
            padding: 2px 0;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 13px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            line-height: 1.5;
            color: #000;
            padding-top: 10px;
        }
        @media print {
            body { 
                padding: 5px;
                margin: 0;
                font-size: 10px;
            }
            .struk-container { 
                border: none;
                box-shadow: none;
                padding: 10px 5px;
                margin: 0;
                max-width: 280px;
            }
            .company-name {
                font-size: 16px;
            }
            .product-name {
                font-size: 11px;
            }
            .summary-row.total {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="struk-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $dataPerusahaan->nama ?? 'MANUFAKTUR COE' }}</div>
            <div class="company-address">{{ $dataPerusahaan->alamat ?? 'Jl. Kebon No. 123' }}</div>
            <div class="company-phone">{{ $dataPerusahaan->telepon ?? 'Telp: 0812-3456-7890' }}</div>
        </div>

        <!-- Garis Pemisah -->
        <div class="separator"></div>
        
        <!-- Informasi Transaksi -->
        <div class="transaction-info">
            <div class="info-row">
                <span>No. Transaksi : {{ $penjualan->nomor_penjualan ?? 'SJ-...' }}</span>
            </div>
            <div class="info-row">
                <span>Tanggal : {{ optional($penjualan->tanggal_transaksi)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span>Kasir : TIM COE PROCESS COSTING</span>
            </div>
        </div>

        <!-- Garis Pemisah -->
        <div class="separator"></div>

        <!-- Products -->
        <div class="products">
            @php $totalItems = 0; @endphp
            @if($penjualan->details->count() > 0)
                @foreach($penjualan->details as $detail)
                    <div class="product-item">
                        <div class="product-name">{{ $detail->produk->nama_produk }}</div>
                        <div class="product-detail">
                            <span>{{ number_format($detail->jumlah, 0, ',', '.') }} x {{ number_format($detail->harga_satuan, 0, ',', '.') }}</span>
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
                        <span>{{ number_format($penjualan->jumlah ?? 0, 0, ',', '.') }} x {{ number_format($penjualan->harga_satuan ?? 0, 0, ',', '.') }}</span>
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
            @php
                // Hitung subtotal dari detail produk (qty × harga satuan)
                $subtotal = 0;
                if ($penjualan->details->count() > 0) {
                    foreach ($penjualan->details as $detail) {
                        $subtotal += $detail->jumlah * $detail->harga_satuan;
                    }
                } elseif ($penjualan->produk) {
                    $subtotal = ($penjualan->jumlah ?? 0) * ($penjualan->harga_satuan ?? 0);
                }

                $biayaOngkir = $penjualan->biaya_ongkir ?? 0;
                $ppnAmount   = $penjualan->biaya_ppn ?? 0;

                // Grand total dari DB (sudah benar saat disimpan)
                // Fallback: hitung manual jika grand_total belum ada
                $grandTotal = ($penjualan->grand_total > 0)
                    ? $penjualan->grand_total
                    : ($subtotal + $biayaOngkir + $ppnAmount - ($penjualan->diskon_nominal ?? 0));

                $paymentLabel = match($penjualan->payment_method ?? 'cash') {
                    'transfer' => 'Transfer Bank',
                    'credit'   => 'Kredit',
                    default    => 'Tunai',
                };
            @endphp

            {{-- Subtotal Produk --}}
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            {{-- Biaya Ongkir --}}
            <div class="summary-row">
                <span>Ongkir</span>
                <span>Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</span>
            </div>

            {{-- PPN --}}
            <div class="summary-row">
                <span>PPN</span>
                <span>Rp {{ number_format($ppnAmount, 0, ',', '.') }}</span>
            </div>

            {{-- Diskon --}}
            @if(($penjualan->diskon_nominal ?? 0) > 0)
            <div class="summary-row">
                <span>Diskon</span>
                <span>- Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
            </div>
            @endif

            {{-- Total --}}
            <div class="summary-row total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>

            {{-- Metode Pembayaran --}}
            <div class="summary-row">
                <span>Pembayaran</span>
                <span>{{ $paymentLabel }}</span>
            </div>
        </div>

        <!-- Garis Pemisah -->
        <div class="separator"></div>
        
        <!-- Footer -->
        <div class="footer">
            <div>Terima kasih atas kunjungan Anda!</div>
            <div>Barang yang sudah dibeli tidak bisa dikembalikan</div>
        </div>
    </div>

    @php
    function formatCurrency($amount) {
        // Ensure amount is numeric and handle decimal places properly
        $amount = floatval($amount);
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    @endphp
</body>
</html>

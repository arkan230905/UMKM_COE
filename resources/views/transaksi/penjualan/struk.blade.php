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
                // Calculate subtotal (qty x harga satuan)
                $subtotal = 0;
                if($penjualan->details->count() > 0) {
                    foreach($penjualan->details as $detail) {
                        $subtotal += $detail->jumlah * $detail->harga_satuan;
                    }
                } elseif($penjualan->produk) {
                    $subtotal = ($penjualan->jumlah ?? 0) * ($penjualan->harga_satuan ?? 0);
                }
                
                // Additional costs
                $biayaOngkir = $penjualan->biaya_ongkir ?? 0;
                $biayaServis = $penjualan->biaya_servis ?? 0;
                
                // Calculate PPN (11%) - only on subtotal, not on additional costs
                $ppnRate = 0.11;
                $ppnAmount = $subtotal * $ppnRate;
                
                // Calculate total
                $grandTotal = $subtotal + $ppnAmount + $biayaOngkir + $biayaServis;
                
                // Apply discount if exists
                if($penjualan->diskon_nominal > 0) {
                    $grandTotal -= $penjualan->diskon_nominal;
                }
            @endphp
            
            <div class="summary-row">
                <span>Subtotal Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            
            @if($biayaOngkir > 0)
            <div class="summary-row">
                <span>Biaya Ongkir Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</span>
            </div>
            @endif
            
            @if($biayaServis > 0)
            <div class="summary-row">
                <span>Biaya Servis Rp {{ number_format($biayaServis, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="summary-row">
                <span>PPN (11%) Rp {{ number_format($ppnAmount, 0, ',', '.') }}</span>
            </div>
            
            @if($penjualan->diskon_nominal > 0)
            <div class="summary-row">
                <span>Diskon -{{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="summary-row total">
                <span>TOTAL: Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
            
            <div class="summary-row">
                <span>Pembayaran: {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : (($penjualan->payment_method ?? 'cash') === 'transfer' ? 'Transfer' : 'Tunai') }}</span>
            </div>
            
            <!-- Additional Payment Details -->
            @if($biayaOngkir > 0 || $biayaServis > 0)
            <div class="separator"></div>
            <div class="summary-row" style="font-size: 9px; margin-top: 8px;">
                <span style="text-align: left; width: 100%;">
                    @if($biayaOngkir > 0)
                    <div>Ongkir: Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</div>
                    @endif
                    @if($biayaServis > 0)
                    <div>Servis: Rp {{ number_format($biayaServis, 0, ',', '.') }}</div>
                    @endif
                    <div>PPN: Rp {{ number_format($ppnAmount, 0, ',', '.') }}</div>
                </span>
            </div>
            @endif
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

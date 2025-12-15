<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan {{ $penjualan->nomor_penjualan ?? ('#'.$penjualan->id) }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #111827;
            --accent: #7d5cff;
            --muted: #6b7280;
        }
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary);
        }
        .receipt-wrapper {
            max-width: 360px;
            margin: 32px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #7d5cff, #9c6bff);
            color: #fff;
            padding: 24px;
            text-align: center;
        }
        .receipt-header .brand {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.4px;
        }
        .receipt-body {
            padding: 20px 24px 16px;
        }
        .receipt-body .meta {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 16px;
        }
        .receipt-body .meta span {
            display: block;
            line-height: 1.4;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th,
        .items-table td {
            font-size: 0.85rem;
            padding: 6px 0;
        }
        .items-table th {
            font-weight: 600;
            color: var(--muted);
            border-bottom: 1px dashed #d1d5db;
        }
        .items-table tbody tr + tr td {
            border-top: 1px dashed #e5e7eb;
        }
        .totals {
            margin-top: 16px;
            font-size: 0.9rem;
        }
        .totals div {
            display: flex;
            justify-content: space-between;
            line-height: 1.8;
        }
        .totals .grand {
            font-weight: 700;
            font-size: 1rem;
        }
        .receipt-footer {
            padding: 16px 24px 24px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--muted);
        }
        .btn-print {
            margin: 12px auto 18px;
            display: block;
            width: calc(100% - 48px);
            border-radius: 999px;
            background: linear-gradient(135deg, #7d5cff, #9c6bff);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 0;
        }
        @media print {
            body { background: #fff; }
            .receipt-wrapper { box-shadow: none; margin: 0 auto; }
            .btn-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="receipt-wrapper" data-auto-align-currency="false">
    <div class="receipt-header">
        <div class="brand">{{ $companyName }}</div>
        @if($companyAddress)
            <div class="small">{{ $companyAddress }}</div>
        @endif
        @if($companyPhone)
            <div class="small">Telp: {{ $companyPhone }}</div>
        @endif
    </div>

    <button class="btn btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Struk</button>

    <div class="receipt-body">
        <div class="meta">
            <span><strong>No Transaksi:</strong> {{ $penjualan->nomor_penjualan ?? ('PJ-' . str_pad($penjualan->id, 4, '0', STR_PAD_LEFT)) }}</span>
            <span><strong>Tanggal:</strong> {{ optional($penjualan->tanggal)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</span>
            <span><strong>Pembayaran:</strong> {{ strtoupper($penjualan->payment_method ?? 'cash') }}</span>
            <span><strong>Kasir:</strong> {{ $penjualan->user->name ?? auth()->user()->name ?? 'Admin' }}</span>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Harga</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            @php
                $isMulti = $penjualan->details && $penjualan->details->count() > 0;
                $subtotal = 0;
                $totalDiskon = 0;
            @endphp
            @if($isMulti)
                @foreach($penjualan->details as $detail)
                    @php
                        $lineSubtotal = (float)$detail->jumlah * (float)$detail->harga_satuan;
                        $discountLine = (float)($detail->diskon_nominal ?? 0);
                        $lineTotal = max($lineSubtotal - $discountLine, 0);
                        $subtotal += $lineSubtotal;
                        $totalDiskon += $discountLine;
                    @endphp
                    <tr>
                        <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($detail->jumlah, 3, ',', '.'), '0'), ',') }}</td>
                        <td class="text-end">Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                @php
                    $qty = (float)($penjualan->jumlah ?? 1);
                    $harga = (float)($penjualan->harga_satuan ?? ($penjualan->total / max($qty, 1)));
                    $discount = (float)($penjualan->diskon_nominal ?? 0);
                    $lineTotal = max(($qty * $harga) - $discount, 0);
                    $subtotal = $qty * $harga;
                    $totalDiskon = $discount;
                @endphp
                <tr>
                    <td>{{ $penjualan->produk->nama_produk ?? '-' }}</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($qty, 3, ',', '.'), '0'), ',') }}</td>
                    <td class="text-end">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
                </tr>
            @endif
            </tbody>
        </table>

        <div class="totals">
            <div>
                <span>Subtotal</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div>
                <span>Diskon</span>
                <span>- Rp {{ number_format($totalDiskon, 0, ',', '.') }}</span>
            </div>
            <div class="grand">
                <span>Total Bayar</span>
                <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="receipt-footer">
        <div>Terima kasih sudah berbelanja!</div>
        <div class="mt-1">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
        <div class="mt-1">www.umkm-coe.com</div>
    </div>
</div>
</body>
</html>

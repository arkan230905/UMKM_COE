<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Penjualan #{{ $penjualan->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .table th, .table td { padding: .4rem .5rem; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        .invoice-box { max-width: 900px; margin: 24px auto; background: #fff; padding: 24px; border: 1px solid #ddd; border-radius: 8px; }
        .title { font-weight: 700; font-size: 20px; }
        .muted { color: #6c757d; }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="title">INVOICE PENJUALAN</div>
            <div class="muted">No: INVJ-{{ str_pad($penjualan->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="text-end">
            <button class="btn btn-sm btn-primary no-print" onclick="window.print()">Cetak / Simpan PDF</button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="fw-semibold">Transaksi</div>
            <div>
                @php $count = ($penjualan->details && $penjualan->details->count()>0) ? $penjualan->details->count() : 1; @endphp
                {{ $count > 1 ? 'Multi Item ('.$count.')' : ($penjualan->produk->nama_produk ?? '-') }}
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <div><span class="fw-semibold">Tanggal:</span> {{ optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal }}</div>
            <div><span class="fw-semibold">Total:</span> Rp {{ number_format($penjualan->total,0,',','.') }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Nama Produk</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Harga</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @if(($penjualan->details && $penjualan->details->count()>0))
                    @php $subtotalSum=0; $diskonSum=0; @endphp
                    @foreach($penjualan->details as $i => $d)
                        @php 
                            $sub = (float)($d->jumlah * $d->harga_satuan);
                            $disc = (float)($d->diskon_nominal ?? 0);
                            $line = max($sub - $disc, 0);
                            $subtotalSum += $sub; $diskonSum += $disc;
                        @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $d->produk->nama_produk ?? '-' }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</td>
                            <td class="text-end">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                            <td class="text-end">Rp {{ number_format($line,0,',','.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>1</td>
                        <td>{{ $penjualan->produk->nama_produk ?? '-' }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($penjualan->jumlah ?? 1,4,',','.'),'0'),',') }}</td>
                        <td class="text-end">Rp {{ number_format(($penjualan->harga_satuan ?? ($penjualan->total / max($penjualan->jumlah ?? 1,1))),0,',','.') }}</td>
                        <td class="text-end">Rp {{ number_format($penjualan->total,0,',','.') }}</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                @if(($penjualan->details && $penjualan->details->count()>0))
                    <tr>
                        <th colspan="4" class="text-end">Subtotal</th>
                        <th class="text-end">Rp {{ number_format($subtotalSum, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Diskon</th>
                        <th class="text-end">- Rp {{ number_format($diskonSum, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Grand Total</th>
                        <th class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</th>
                    </tr>
                @else
                    <tr>
                        <th colspan="4" class="text-end">Subtotal</th>
                        <th class="text-end">Rp {{ number_format(($penjualan->jumlah ?? 1) * ($penjualan->harga_satuan ?? ($penjualan->total / max($penjualan->jumlah ?? 1,1))), 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Diskon</th>
                        <th class="text-end">- Rp {{ number_format($penjualan->diskon_nominal ?? 0, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Grand Total</th>
                        <th class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</th>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>

    <div class="mt-4 small text-muted">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}
    </div>
</div>
</body>
</html>

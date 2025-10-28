<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembelian #{{ $pembelian->id }}</title>
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
            <div class="title">INVOICE PEMBELIAN</div>
            <div class="muted">No: INV-{{ str_pad($pembelian->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="text-end">
            <button class="btn btn-sm btn-primary no-print" onclick="window.print()">Cetak / Simpan PDF</button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="fw-semibold">Vendor</div>
            <div>{{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
        </div>
        <div class="col-md-6 text-md-end">
            <div><span class="fw-semibold">Tanggal:</span> {{ optional($pembelian->tanggal)->format('d-m-Y') ?? $pembelian->tanggal }}</div>
            <div><span class="fw-semibold">Total:</span> Rp {{ number_format($pembelian->total, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Nama Bahan</th>
                    <th class="text-end">Qty</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga / Satuan</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($pembelian->details ?? []) as $i => $d)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $d->bahanBaku->nama_bahan ?? '-' }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</td>
                        <td>{{ $d->satuan ?: ($d->bahanBaku->satuan ?? '-') }}</td>
                        <td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Grand Total</th>
                    <th class="text-end">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-4 small text-muted">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}
    </div>
</div>
</body>
</html>

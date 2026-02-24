<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembelian #{{ $pembelian->nomor_pembelian ?? 'PB-' . date('Y') . '-' . str_pad($pembelian->id, 3, '0', STR_PAD_LEFT) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .table th, .table td { padding: .5rem; font-size: 12px; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-box { border: 0; box-shadow: none; margin: 0; padding: 0; }
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .invoice-box {
            max-width: 900px; 
            margin: 24px auto; 
            background: #fff; 
            padding: 32px; 
            border: 1px solid #e9ecef; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-title {
            font-weight: 800; 
            font-size: 28px; 
            color: #007bff;
            margin-bottom: 8px;
        }
        
        .invoice-number {
            font-size: 16px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .company-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .table thead th {
            background: #007bff;
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .grand-total-row {
            background: #007bff !important;
            color: white !important;
            font-weight: 700;
            font-size: 16px;
        }
        
        .grand-total-row th,
        .grand-total-row td {
            border: none;
        }
        
        .payment-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .payment-cash {
            background: #28a745;
            color: white;
        }
        
        .payment-transfer {
            background: #007bff;
            color: white;
        }
        
        .payment-credit {
            background: #ffc107;
            color: #212529;
        }
        
        .footer-info {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #6c757d;
        }
        
        .btn-print {
            background: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn-print:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <!-- Header -->
    <div class="invoice-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="invoice-title">
                    <i class="fas fa-file-invoice me-2"></i>INVOICE PEMBELIAN
                </div>
                <div class="invoice-number">
                    No: {{ $pembelian->nomor_pembelian ?? 'PB-' . date('Y') . '-' . str_pad($pembelian->id, 3, '0', STR_PAD_LEFT) }}
                </div>
            </div>
            <div class="text-end no-print">
                <button class="btn btn-primary btn-print" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Company & Transaction Info -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="info-box">
                <h6 class="fw-bold mb-3"><i class="fas fa-store me-2"></i>Informasi Vendor</h6>
                <div class="mb-2"><strong>Vendor:</strong> {{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                @if($pembelian->vendor->alamat)
                    <div class="mb-2"><strong>Alamat:</strong> {{ $pembelian->vendor->alamat }}</div>
                @endif
                @if($pembelian->vendor->telepon)
                    <div><strong>Telepon:</strong> {{ $pembelian->vendor->telepon }}</div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Transaksi</h6>
                <div class="mb-2"><strong>Tanggal:</strong> {{ optional($pembelian->tanggal)->format('d F Y') ?? $pembelian->tanggal }}</div>
                <div class="mb-2"><strong>Metode Pembayaran:</strong> 
                    @if($pembelian->payment_method === 'cash')
                        <span class="payment-badge payment-cash">Tunai</span>
                    @elseif($pembelian->payment_method === 'transfer')
                        <span class="payment-badge payment-transfer">Transfer</span>
                    @else
                        <span class="payment-badge payment-credit">Kredit</span>
                    @endif
                </div>
                @if($pembelian->nomor_faktur)
                    <div><strong>No. Faktur:</strong> {{ $pembelian->nomor_faktur }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th>Nama Item</th>
                    <th class="text-end" style="width:12%">Qty</th>
                    <th style="width:10%">Satuan</th>
                    <th class="text-end" style="width:18%">Harga / Satuan</th>
                    <th class="text-end" style="width:20%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotal = 0;
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
                            $satuanName = $d->bahanPendukung->satuan->nama ?? 'unit';
                        }
                        
                        $grandTotal += $d->subtotal;
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td class="fw-medium">{{ $itemName }}</td>
                        <td class="text-end">{{ number_format($d->jumlah, 2, ',', '.') }}</td>
                        <td>{{ $satuanName }}</td>
                        <td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total-row">
                    <th colspan="5" class="text-end">GRAND TOTAL</th>
                    <th class="text-end">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="info-box">
                <h6 class="fw-bold mb-2"><i class="fas fa-calculator me-2"></i>Ringkasan Pembayaran</h6>
                <div class="d-flex justify-content-between mb-1">
                    <span>Total Harga:</span>
                    <span class="fw-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>
                @if($pembelian->payment_method === 'credit')
                    <div class="d-flex justify-content-between mb-1">
                        <span>Terbayar:</span>
                        <span class="fw-bold">Rp {{ number_format($pembelian->terbayar ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Sisa Pembayaran:</span>
                        <span class="fw-bold text-danger">Rp {{ number_format($pembelian->sisa_pembayaran ?? $grandTotal, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box">
                <h6 class="fw-bold mb-2"><i class="fas fa-check-circle me-2"></i>Status</h6>
                <div>
                    @if($pembelian->status === 'lunas')
                        <span class="badge bg-success fs-6">LUNAS</span>
                    @else
                        <span class="badge bg-warning fs-6">BELUM LUNAS</span>
                    @endif
                </div>
                @if($pembelian->keterangan)
                    <div class="mt-2">
                        <small class="text-muted">{{ $pembelian->keterangan }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-info">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-2">
                    <i class="fas fa-building me-2"></i>
                    <strong>UMKM COE</strong>
                </div>
                <div class="small text-muted">
                    Sistem Manajemen UMKM Center of Excellence
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <i class="fas fa-print me-2"></i>
                    Dicetak pada: {{ now()->format('d F Y H:i') }}
                </div>
                <div class="small text-muted">
                    Invoice ini sah dan telah diterbitkan oleh sistem
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

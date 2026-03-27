<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Pembayaran Beban - {{ $pembayaran->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        .amount-section {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #ddd;
            margin: 20px 0;
            text-align: center;
        }
        .amount-label {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>
    
    <div class="header">
        <div class="company-name">{{ config('app.name', 'UMKM System') }}</div>
        <div class="document-title">BUKTI PEMBAYARAN BEBAN</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">No. Transaksi:</div>
            <div class="info-value">PB-{{ str_pad($pembayaran->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal:</div>
            <div class="info-value">{{ $pembayaran->tanggal->format('d/m/Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Beban Operasional:</div>
            <div class="info-value">{{ $pembayaran->bebanOperasional->nama_beban ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kategori:</div>
            <div class="info-value">{{ $pembayaran->bebanOperasional->kategori ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Akun Beban:</div>
            <div class="info-value">{{ $pembayaran->coaBeban->kode_akun ?? '-' }} - {{ $pembayaran->coaBeban->nama_akun ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Metode Bayar:</div>
            <div class="info-value">{{ ucfirst($pembayaran->metode_bayar) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Akun Kas/Bank:</div>
            <div class="info-value">{{ $pembayaran->coaKasBank->kode_akun ?? '-' }} - {{ $pembayaran->coaKasBank->nama_akun ?? '-' }}</div>
        </div>
        @if($pembayaran->keterangan)
        <div class="info-row">
            <div class="info-label">Keterangan:</div>
            <div class="info-value">{{ $pembayaran->keterangan }}</div>
        </div>
        @endif
    </div>

    <div class="amount-section">
        <div class="amount-label">Nominal Pembayaran</div>
        <div class="amount-value">Rp {{ number_format($pembayaran->nominal_pembayaran, 0, ',', '.') }}</div>
    </div>

    <div class="footer">
        <div class="signature-box">
            <div>Dibuat Oleh</div>
            <div class="signature-line">
                {{ auth()->user()->name ?? 'Admin' }}
            </div>
        </div>
        <div class="signature-box">
            <div>Disetujui Oleh</div>
            <div class="signature-line">
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
            </div>
        </div>
        <div class="signature-box">
            <div>Penerima</div>
            <div class="signature-line">
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
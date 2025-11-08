<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bukti Pembayaran Beban - {{ $pembayaran->kode_transaksi }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0 0;
        }
        .info-box {
            margin-bottom: 20px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box th, .info-box td {
            padding: 5px;
            vertical-align: top;
        }
        .info-box th {
            text-align: left;
            width: 30%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f5f5f5;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mt-4 {
            margin-top: 1.5rem;
        }
        .signature {
            margin-top: 50px;
        }
        .signature div {
            float: left;
            width: 50%;
            text-align: center;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        @page {
            margin: 1cm;
        }
        @media print {
            body {
                font-size: 10pt;
            }
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUKTI PEMBAYARAN BEBAN</h1>
        <p>No. {{ $pembayaran->kode_transaksi }}</p>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <th>Tanggal</th>
                <td>:</td>
                <td>{{ $pembayaran->tanggal->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Akun Beban</th>
                <td>:</td>
                <td>{{ $pembayaran->coaBeban->kode }} - {{ $pembayaran->coaBeban->nama }}</td>
            </tr>
            <tr>
                <th>Akun Kas</th>
                <td>:</td>
                <td>{{ $pembayaran->coaKas->kode }} - {{ $pembayaran->coaKas->nama }}</td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td>:</td>
                <td>{{ $pembayaran->keterangan }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Pembayaran Beban {{ $pembayaran->keterangan }}</td>
                <td class="text-right">{{ format_rupiah($pembayaran->jumlah) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ format_rupiah($pembayaran->jumlah) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="info-box">
        <p><strong>Catatan:</strong> {{ $pembayaran->catatan ?? '-' }}</p>
    </div>

    <div class="signature clearfix">
        <div>
            <p>Dibuat oleh,</p>
            <br><br><br>
            <p><u>{{ $pembayaran->user->name ?? '-' }}</u></p>
            <p>{{ $pembayaran->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div>
            <p>Mengetahui,</p>
            <br><br><br>
            <p><u>_________________________</u></p>
            <p>(_________________________)</p>
        </div>
    </div>

    <div class="no-print mt-4" style="text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Cetak</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <script>
        window.onload = function() {
            // Auto print when the page loads
            setTimeout(function() {
                window.print();
            }, 500);
            
            // Close the window after print
            window.onafterprint = function() {
                // window.close();
            };
        };
    </script>
</body>
</html>

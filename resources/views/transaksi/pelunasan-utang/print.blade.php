<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cetak Pelunasan Utang #{{ $pelunasanUtang->kode_transaksi }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .invoice-info {
            margin-bottom: 20px;
            overflow: hidden;
        }
        .invoice-info .left {
            float: left;
            width: 50%;
        }
        .invoice-info .right {
            float: right;
            width: 40%;
            text-align: right;
        }
        .invoice-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        .invoice-info p {
            margin: 0 0 5px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
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
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        .signature p {
            margin: 50px 0 0;
        }
        .signature .line {
            display: inline-block;
            width: 200px;
            border-top: 1px solid #333;
            margin-top: 50px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .no-print {
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUKTI PELUNASAN UTANG</h1>
        <p>No. {{ $pelunasanUtang->kode_transaksi }}</p>
    </div>

    <div class="invoice-info">
        <div class="left">
            <h3>Kepada:</h3>
            <p><strong>{{ $pelunasanUtang->pembelian->vendor->nama }}</strong></p>
            <p>{{ $pelunasanUtang->pembelian->vendor->alamat }}</p>
            <p>Telp: {{ $pelunasanUtang->pembelian->vendor->telepon }}</p>
        </div>
        <div class="right">
            <p><strong>Tanggal:</strong> {{ $pelunasanUtang->tanggal->format('d/m/Y') }}</p>
            <p><strong>Status:</strong> {{ strtoupper($pelunasanUtang->status) }}</p>
        </div>
        <div style="clear: both;"></div>
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
                <td>1</td>
                <td>Pelunasan untuk Pembelian {{ $pelunasanUtang->pembelian->kode_pembelian }}</td>
                <td class="text-right">{{ format_rupiah($pelunasanUtang->jumlah) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ format_rupiah($pelunasanUtang->jumlah) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="invoice-info">
        <div class="left">
            <h3>Pembayaran Melalui:</h3>
            <p><strong>{{ $pelunasanUtang->akunKas->kode }} - {{ $pelunasanUtang->akunKas->nama }}</strong></p>
            
            @if($pelunasanUtang->keterangan)
            <div class="mt-4">
                <h3>Keterangan:</h3>
                <p>{{ $pelunasanUtang->keterangan }}</p>
            </div>
            @endif
        </div>
        <div class="right">
            <div class="signature">
                <p>Hormat Kami,</p>
                <div class="line"></div>
                <p>({{ auth()->user()->name }})</p>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="mt-4">
        <p><strong>Catatan:</strong> Dokumen ini dicetak secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>

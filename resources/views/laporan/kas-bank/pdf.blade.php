<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kas dan Bank</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #000; padding: 8px; }
        table th { background-color: #f0f0f0; font-weight: bold; text-align: left; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #e3f2fd; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KAS DAN BANK</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Kode Akun</th>
                <th style="width: 28%">Nama Akun</th>
                <th style="width: 15%" class="text-end">Saldo Awal</th>
                <th style="width: 15%" class="text-end">Transaksi Masuk</th>
                <th style="width: 15%" class="text-end">Transaksi Keluar</th>
                <th style="width: 15%" class="text-end">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataKasBank as $data)
            <tr>
                <td>{{ $data['kode_akun'] }}</td>
                <td>{{ $data['nama_akun'] }}</td>
                <td class="text-end">Rp {{ number_format($data['saldo_awal'], 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($data['transaksi_masuk'], 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($data['transaksi_keluar'], 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($data['saldo_akhir'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-end"><strong>TOTAL KAS DAN BANK:</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; font-size: 9px;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

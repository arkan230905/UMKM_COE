<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Neraca Saldo - {{ \Carbon\Carbon::parse($tahun . '-' . $bulan . '-01')->isoFormat('MMMM YYYY') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .period {
            font-size: 12px;
            margin-bottom: 10px;
        }
        .source-info {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .text-start {
            text-align: left;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .balance-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .balanced {
            color: #28a745;
        }
        .unbalanced {
            color: #dc3545;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        .amount {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name', 'UMKM COE') }}</div>
        <div class="report-title">NERACA SALDO</div>
        <div class="period">
            Periode: {{ \Carbon\Carbon::parse($tahun . '-' . $bulan . '-01')->isoFormat('MMMM YYYY') }}
        </div>
        <div class="source-info">
            Data diambil dari Buku Besar (Journal Lines) - Saldo akhir per akun
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Kode Akun</th>
                <th style="width: 40%">Nama Akun</th>
                <th style="width: 21.5%">Debit</th>
                <th style="width: 21.5%">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($neracaSaldoData['accounts'] as $account)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center"><strong>{{ $account['kode_akun'] }}</strong></td>
                    <td class="text-start">{{ $account['nama_akun'] }}</td>
                    <td class="text-end amount">
                        @if($account['debit'] > 0)
                            {{ number_format($account['debit'], 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end amount">
                        @if($account['kredit'] > 0)
                            {{ number_format($account['kredit'], 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-end"><strong>TOTAL</strong></td>
                <td class="text-end amount">
                    <strong>{{ number_format($neracaSaldoData['total_debit'], 0, ',', '.') }}</strong>
                </td>
                <td class="text-end amount">
                    <strong>{{ number_format($neracaSaldoData['total_kredit'], 0, ',', '.') }}</strong>
                </td>
            </tr>
            <tr class="balance-row">
                <td colspan="3" class="text-end"><strong>STATUS KESEIMBANGAN:</strong></td>
                <td colspan="2" class="text-center {{ $neracaSaldoData['is_balanced'] ? 'balanced' : 'unbalanced' }}">
                    @if($neracaSaldoData['is_balanced'])
                        <strong>✓ BALANCED</strong>
                    @else
                        <strong>✗ TIDAK SEIMBANG</strong>
                        <br>
                        <small>
                            Selisih: {{ number_format(abs($neracaSaldoData['total_debit'] - $neracaSaldoData['total_kredit']), 0, ',', '.') }}
                        </small>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div style="margin-top: 20px;">
            <strong>Keterangan:</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <li>Neraca saldo menunjukkan saldo akhir semua akun berdasarkan data buku besar</li>
                <li>Saldo akhir dihitung dari: Saldo awal + Mutasi debit - Mutasi kredit (untuk akun normal debit)</li>
                <li>Total debit harus sama dengan total kredit untuk memastikan keseimbangan pembukuan</li>
                <li>Akun dengan saldo 0 dan tanpa mutasi tidak ditampilkan</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px; text-align: right;">
            <div>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
            <div>Oleh: {{ auth()->user()->name ?? 'System' }}</div>
        </div>
    </div>
</body>
</html>
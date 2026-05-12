<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Neraca Saldo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 6px;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .section-header {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            border-radius: 3px;
            background-color: #ffc107;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>NERACA SALDO</h2>
        <p>
            Periode: {{ \Carbon\Carbon::parse($tahun.'-'.$bulan.'-01')->isoFormat('MMMM YYYY') }}
        </p>
    </div>

    @php
        // Group accounts by type
        $assetAccounts = [];
        $liabilityAccounts = [];
        $equityAccounts = [];
        $revenueAccounts = [];
        $expenseAccounts = [];

        foreach($coas as $coa) {
            $data = $totals[$coa->kode_akun] ?? ['saldo_awal' => 0, 'debit' => 0, 'kredit' => 0, 'saldo_akhir' => 0];
            $accountData = [
                'coa' => $coa,
                'data' => $data
            ];

            switch($coa->tipe_akun) {
                case 'Asset':
                    $assetAccounts[] = $accountData;
                    break;
                case 'Liability':
                    $liabilityAccounts[] = $accountData;
                    break;
                case 'Equity':
                    $equityAccounts[] = $accountData;
                    break;
                case 'Revenue':
                    $revenueAccounts[] = $accountData;
                    break;
                case 'Expense':
                    $expenseAccounts[] = $accountData;
                    break;
            }
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 10%">Kode Akun</th>
                <th style="width: 25%">Nama Akun</th>
                <th style="width: 15%" class="text-end">Saldo Awal</th>
                <th style="width: 15%" class="text-end">Debit</th>
                <th style="width: 15%" class="text-end">Kredit</th>
                <th style="width: 15%" class="text-end">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSaldoAwal = 0;
                $totalDebit = 0;
                $totalKredit = 0;
                $totalSaldoAkhir = 0;
                $rowNumber = 1;
            @endphp

            <!-- ASSETS -->
            <tr class="section-header">
                <td colspan="7">AKTIVA</td>
            </tr>
            @foreach($assetAccounts as $item)
                @php
                    $coa = $item['coa'];
                    $data = $item['data'];
                    $saldoAwal = $data['saldo_awal'];
                    $debit = $data['debit'];
                    $kredit = $data['kredit'];
                    $saldoAkhir = $data['saldo_akhir'];

                    $totalSaldoAwal += $saldoAwal;
                    $totalSaldoAkhir += $saldoAkhir;
                    $totalDebit += $debit;
                    $totalKredit += $kredit;
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td><strong>{{ $coa->kode_akun }}</strong></td>
                    <td>{{ $coa->nama_akun }}</td>
                    <td class="text-end">{{ $saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end"><strong>{{ $saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-' }}</strong></td>
                </tr>
            @endforeach

            <!-- LIABILITIES -->
            <tr class="section-header">
                <td colspan="7">PASIVA</td>
            </tr>
            @foreach($liabilityAccounts as $item)
                @php
                    $coa = $item['coa'];
                    $data = $item['data'];
                    $saldoAwal = $data['saldo_awal'];
                    $debit = $data['debit'];
                    $kredit = $data['kredit'];
                    $saldoAkhir = $data['saldo_akhir'];

                    $totalSaldoAwal -= $saldoAwal;
                    $totalSaldoAkhir -= $saldoAkhir;
                    $totalDebit += $debit;
                    $totalKredit += $kredit;
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td><strong>{{ $coa->kode_akun }}</strong></td>
                    <td>{{ $coa->nama_akun }}</td>
                    <td class="text-end">{{ $saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end"><strong>{{ $saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-' }}</strong></td>
                </tr>
            @endforeach

            <!-- EQUITY -->
            <tr class="section-header">
                <td colspan="7">EKUITAS</td>
            </tr>
            @foreach($equityAccounts as $item)
                @php
                    $coa = $item['coa'];
                    $data = $item['data'];
                    $saldoAwal = $data['saldo_awal'];
                    $debit = $data['debit'];
                    $kredit = $data['kredit'];
                    $saldoAkhir = $data['saldo_akhir'];

                    $totalSaldoAwal -= $saldoAwal;
                    $totalSaldoAkhir -= $saldoAkhir;
                    $totalDebit += $debit;
                    $totalKredit += $kredit;
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td><strong>{{ $coa->kode_akun }}</strong></td>
                    <td>{{ $coa->nama_akun }}</td>
                    <td class="text-end">{{ $saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end"><strong>{{ $saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-' }}</strong></td>
                </tr>
            @endforeach

            <!-- REVENUE -->
            <tr class="section-header">
                <td colspan="7">PENDAPATAN</td>
            </tr>
            @foreach($revenueAccounts as $item)
                @php
                    $coa = $item['coa'];
                    $data = $item['data'];
                    $saldoAwal = $data['saldo_awal'];
                    $debit = $data['debit'];
                    $kredit = $data['kredit'];
                    $saldoAkhir = $data['saldo_akhir'];

                    $totalSaldoAwal -= $saldoAwal;
                    $totalSaldoAkhir -= $saldoAkhir;
                    $totalDebit += $debit;
                    $totalKredit += $kredit;
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td><strong>{{ $coa->kode_akun }}</strong></td>
                    <td>{{ $coa->nama_akun }}</td>
                    <td class="text-end">{{ $saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end"><strong>{{ $saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-' }}</strong></td>
                </tr>
            @endforeach

            <!-- EXPENSES -->
            <tr class="section-header">
                <td colspan="7">BEBAN</td>
            </tr>
            @foreach($expenseAccounts as $item)
                @php
                    $coa = $item['coa'];
                    $data = $item['data'];
                    $saldoAwal = $data['saldo_awal'];
                    $debit = $data['debit'];
                    $kredit = $data['kredit'];
                    $saldoAkhir = $data['saldo_akhir'];

                    $totalSaldoAwal += $saldoAwal;
                    $totalSaldoAkhir += $saldoAkhir;
                    $totalDebit += $debit;
                    $totalKredit += $kredit;
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td><strong>{{ $coa->kode_akun }}</strong></td>
                    <td>
                        {{ $coa->nama_akun }}
                        @if($coa->kode_akun === '5101')
                            <span class="badge">HPP</span>
                        @endif
                    </td>
                    <td class="text-end">{{ $saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end"><strong>{{ $saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-' }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-end"><strong>TOTAL</strong></td>
                <td class="text-end"><strong>Rp {{ number_format(abs($totalSaldoAwal), 0, ',', '.') }}</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($totalKredit, 0, ',', '.') }}</strong></td>
                <td class="text-end"><strong>Rp {{ number_format(abs($totalSaldoAkhir), 0, ',', '.') }}</strong></td>
            </tr>
            <tr class="total-row">
                <td colspan="5" class="text-end"><strong>BALANCE CHECK:</strong></td>
                <td colspan="2" class="text-end"><strong>{{ $totalDebit - $totalKredit }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; font-size: 9px;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

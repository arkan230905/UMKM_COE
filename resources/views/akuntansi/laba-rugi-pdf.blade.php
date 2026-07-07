<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333333;
            background-color: #ffffff;
        }
        .page {
            background-color: white;
            padding: 30px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 35px;
            background-color: #5C3D2E;
            color: #ffffff;
            padding: 25px;
            border-radius: 4px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        }
        .report-period {
            font-size: 12px;
        }
        
        /* Section */
        .section-header {
            background-color: #8A6B48;
            color: white;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eeeeee;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        
        .account-name {
            width: 50%;
            font-weight: normal;
        }
        
        .account-code {
            width: 20%;
            color: #666666;
            font-size: 10px;
            text-align: center;
        }
        
        .amount {
            width: 30%;
            text-align: right;
            font-weight: bold;
        }
        
        .subtotal-row {
            background-color: #f5f5f5 !important;
            border-top: 1px solid #8A6B48;
            border-bottom: 1px solid #8A6B48;
            font-weight: bold;
        }
        .subtotal-row td {
            padding: 10px 12px;
        }
        
        .laba-kotor {
            background-color: #e6f2ff;
            border-left: 4px solid #3b82f6;
            font-weight: bold;
            font-size: 12px;
        }
        .laba-kotor td {
            padding: 12px;
        }
        
        .laba-bersih {
            background-color: #e6ffed;
            border-left: 4px solid #10b981;
            font-weight: bold;
            font-size: 13px;
        }
        .laba-bersih td {
            padding: 15px 12px;
        }
        
        .laba-bersih.negative {
            background-color: #ffe6e6;
            border-left: 4px solid #ef4444;
        }
        
        .note {
            font-size: 9px;
            color: #555555;
            font-style: italic;
            padding: 6px 10px;
            background-color: #fff9e6;
            border-left: 3px solid #f5c242;
            margin-bottom: 15px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #dddddd;
            text-align: center;
            font-size: 10px;
            color: #777777;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="company-name">UMKM COE</div>
            <div class="report-title">LAPORAN LABA RUGI</div>
            <div class="report-period">
                Periode: {{ $from ? \Carbon\Carbon::parse($from)->format('d M Y') : 'Awal' }} - {{ $to ? \Carbon\Carbon::parse($to)->format('d M Y') : 'Akhir' }}
            </div>
        </div>

        <!-- PENDAPATAN USAHA -->
        <div class="section-header">PENDAPATAN USAHA</div>
        <table class="data-table">
            <tbody>
                @forelse($pendapatan as $acc)
                    @php
                        $bal = $accountData[$acc->kode_akun]['saldo_akhir'] ?? 0;
                    @endphp
                    @if($bal != 0)
                    <tr>
                        <td class="account-name" colspan="2">{{ $acc->kode_akun }} - {{ $acc->nama_akun }}</td>
                        <td class="amount">Rp {{ number_format($bal, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #999; padding: 15px;">Belum ada data pendapatan</td>
                </tr>
                @endforelse
                
                <tr class="subtotal-row">
                    <td class="account-name">Total Pendapatan</td>
                    <td class="account-code"></td>
                    <td class="amount">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- HPP -->
        <div class="section-header">HPP (HARGA POKOK PENJUALAN)</div>
        <table class="data-table">
            <tbody>
                <!-- Akun Induk HPP -->
                <tr>
                    <td class="account-name" colspan="2">
                        {{ $hppCoa ? $hppCoa->kode_akun . ' - ' . $hppCoa->nama_akun : 'Harga Pokok Penjualan' }}
                    </td>
                    <td class="amount">Rp 0</td>
                </tr>
                
                @forelse($hppChildCoas as $childCoa)
                    @php
                        $hppData = $detailHppByAccount->firstWhere('coa.id', $childCoa->id);
                        $hppValue = $hppData ? $hppData['nilai'] : 0;
                    @endphp
                    <tr>
                        <td class="account-name" colspan="2" style="padding-left: 20px;">
                            ↳ {{ $childCoa->kode_akun }} - {{ $childCoa->nama_akun }}
                        </td>
                        <td class="amount">Rp {{ number_format($hppValue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #999; padding: 15px;">Belum ada transaksi HPP</td>
                </tr>
                @endforelse
                
                <tr class="subtotal-row">
                    <td class="account-name">Total HPP</td>
                    <td class="account-code"></td>
                    <td class="amount">Rp {{ number_format($totalHppDetail, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            Catatan: HPP berdasarkan penjualan produk pada periode yang dipilih.
        </div>

        <!-- LABA KOTOR -->
        <table>
            <tbody>
                <tr class="laba-kotor">
                    <td class="account-name">LABA KOTOR</td>
                    <td class="account-code"></td>
                    <td class="amount">
                        @if($labaKotor < 0)
                            <span style="color: #dc2626;">(Rp {{ number_format(abs($labaKotor), 0, ',', '.') }})</span>
                        @else
                            Rp {{ number_format($labaKotor, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- BEBAN USAHA -->
        <div class="section-header" style="margin-top: 25px;">BEBAN USAHA</div>
        <table class="data-table">
            <tbody>
                @php $sumExp = 0; @endphp
                @foreach($expense as $acc)
                    @php
                        $bal = $accountData[$acc->kode_akun]['saldo_akhir'] ?? 0;
                        $sumExp += $bal;
                    @endphp
                    @if($bal != 0)
                    <tr>
                        <td class="account-name" colspan="2">
                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                            @if(str_contains(strtolower($acc->nama_akun), 'gaji'))
                                <div style="font-size: 9px; color: #888; font-weight: normal; margin-top: 2px;">BTKTL (Gaji Admin, Supervisor)</div>
                            @endif
                        </td>
                        <td class="amount">Rp {{ number_format($bal, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                
                @if($sumExp == 0)
                <tr>
                    <td colspan="3" style="text-align: center; color: #999; padding: 15px;">Belum ada beban usaha</td>
                </tr>
                @endif
                
                <tr class="subtotal-row">
                    <td class="account-name">Total Beban Usaha</td>
                    <td class="account-code"></td>
                    <td class="amount">Rp {{ number_format($sumExp, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            Catatan: Gaji yang tercatat di beban usaha adalah BTKTL (gaji admin, dll), berbeda dengan BTKL di HPP (gaji pekerja produksi langsung).
        </div>

        <!-- LABA BERSIH -->
        <table>
            <tbody>
                <tr class="laba-bersih @if(($labaKotor - $sumExp) < 0)negative @endif">
                    <td class="account-name">LABA BERSIH</td>
                    <td class="account-code"></td>
                    <td class="amount">
                        @php $labaBersih = $labaKotor - $sumExp; @endphp
                        @if($labaBersih < 0)
                            <span style="color: #dc2626;">(Rp {{ number_format(abs($labaBersih), 0, ',', '.') }})</span>
                        @else
                            Rp {{ number_format($labaBersih, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <div>Dicetak pada: {{ now()->format('d M Y H:i:s') }}</div>
            <div style="margin-top: 5px;">© {{ date('Y') }} UMKM COE - Sistem Informasi Manufaktur</div>
        </div>
    </div>
</body>
</html>

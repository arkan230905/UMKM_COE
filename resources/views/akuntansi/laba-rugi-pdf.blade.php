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
            font-family: 'Calibri', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8f9fa;
        }
        .page {
            background-color: white;
            margin: 10px;
            padding: 30px;
            page-break-after: always;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 35px;
            background: linear-gradient(135deg, #8B7355 0%, #6a5844 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(139, 115, 85, 0.2);
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .report-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            border-bottom: 2px solid rgba(255,255,255,0.5);
            padding-bottom: 10px;
        }
        .report-period {
            font-size: 12px;
            opacity: 0.95;
        }
        
        /* Section */
        .section-header {
            background: linear-gradient(90deg, #8B7355 0%, #a0846b 100%);
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(139, 115, 85, 0.2);
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .account-name {
            width: 50%;
            font-weight: 500;
            color: #1f2937;
        }
        
        .account-code {
            width: 20%;
            color: #6b7280;
            font-size: 10px;
        }
        
        .amount {
            width: 30%;
            text-align: right;
            font-weight: 600;
            color: #1f2937;
        }
        
        .subtotal-row {
            background-color: #f0f9ff !important;
            border-top: 2px solid #8B7355;
            border-bottom: 2px solid #8B7355;
            font-weight: bold;
        }
        
        .total-row {
            background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 100%);
            border-top: 3px solid #8B7355;
            border-bottom: 3px solid #8B7355;
            font-weight: bold;
            font-size: 13px;
        }
        
        .section-subtitle {
            font-size: 10px;
            color: #6b7280;
            font-weight: 600;
            padding: 8px 12px;
            background-color: #f3f4f6;
            margin-bottom: 5px;
            border-left: 4px solid #8B7355;
        }
        
        .laba-kotor {
            background: linear-gradient(90deg, #dbeafe 0%, #e0e7ff 100%);
            border-left: 4px solid #3b82f6;
            font-weight: bold;
            font-size: 12px;
        }
        
        .laba-bersih {
            background: linear-gradient(90deg, #dcfce7 0%, #f0fdf4 100%);
            border-left: 4px solid #10b981;
            font-weight: bold;
            font-size: 13px;
        }
        
        .laba-bersih.negative {
            background: linear-gradient(90deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #ef4444;
        }
        
        .note {
            font-size: 9px;
            color: #6b7280;
            font-style: italic;
            padding: 8px 12px;
            background-color: #fffbeb;
            border-left: 3px solid #f59e0b;
            margin-bottom: 10px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
        
        .footer-line {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="company-name">📊 UMKM COE</div>
            <div class="report-title">LAPORAN LABA RUGI</div>
            <div class="report-period">
                Periode: {{ $from ? \Carbon\Carbon::parse($from)->format('d M Y') : 'Awal' }} - {{ $to ? \Carbon\Carbon::parse($to)->format('d M Y') : 'Akhir' }}
            </div>
        </div>

        <!-- PENDAPATAN USAHA -->
        <div class="section-header">💰 PENDAPATAN USAHA</div>
        <table class="data-table">
            <tbody>
                @php $sumRev = 0; @endphp
                @foreach($revenue as $acc)
                    @php
                        $q = \App\Models\JournalLine::where('coa_id',$acc->id)->with('entry');
                        if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                        if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                        $row = $q->selectRaw('COALESCE(SUM(credit - debit),0) as bal')->first();
                        $bal = (float)($row->bal ?? 0);
                        $sumRev += $bal;
                    @endphp
                    @if($bal != 0)
                    <tr>
                        <td class="account-name">{{ $acc->nama_akun }}</td>
                        <td class="account-code">{{ $acc->kode_akun }}</td>
                        <td class="amount">Rp {{ number_format($bal, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                
                @if($sumRev == 0)
                <tr>
                    <td colspan="3" style="text-align: center; color: #9ca3af; padding: 15px;">📭 Belum ada data penjualan</td>
                </tr>
                @endif
                
                <tr class="subtotal-row">
                    <td class="account-name">Total Pendapatan</td>
                    <td class="account-code"></td>
                    <td class="amount">Rp {{ number_format($sumRev, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- HPP -->
        <div class="section-header">📦 HPP (HARGA POKOK PENJUALAN)</div>
        <table class="data-table">
            <tbody>
                @php $sumHpp = 0; @endphp
                @foreach($hppAccounts as $acc)
                    @php
                        $q = \App\Models\JournalLine::where('coa_id',$acc->id)->with('entry');
                        if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                        if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                        $row = $q->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->first();
                        $bal = (float)($row->bal ?? 0);
                        $sumHpp += $bal;
                    @endphp
                    @if($bal != 0)
                    <tr>
                        <td class="account-name">{{ $acc->nama_akun }}</td>
                        <td class="account-code">{{ $acc->kode_akun }}</td>
                        <td class="amount">Rp {{ number_format($bal, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                
                @if($sumHpp == 0)
                <tr>
                    <td colspan="3" style="text-align: center; color: #9ca3af; padding: 15px;">📭 Belum ada transaksi HPP</td>
                </tr>
                @endif
                
                <tr class="subtotal-row">
                    <td class="account-name">Total HPP</td>
                    <td class="account-code"></td>
                    <td class="amount">Rp {{ number_format($sumHpp, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            👉 HPP berdasarkan jurnal penjualan yang telah dicatat. Termasuk Bahan Baku (BBB), Biaya Tenaga Kerja Langsung (BTKL), dan Biaya Overhead Produksi (BOP).
        </div>

        <!-- LABA KOTOR -->
        <table>
            <tbody>
                <tr class="laba-kotor">
                    <td class="account-name">📊 LABA KOTOR</td>
                    <td class="account-code"></td>
                    <td class="amount">
                        @php $labaKotor = $sumRev - $sumHpp; @endphp
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
        <div class="section-header" style="margin-top: 20px;">💸 BEBAN USAHA</div>
        <table class="data-table">
            <tbody>
                @php $sumExp = 0; @endphp
                @foreach($expense as $acc)
                    @php
                        $q = \App\Models\JournalLine::where('coa_id',$acc->id)->with('entry');
                        if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                        if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                        $row = $q->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->first();
                        $bal = (float)($row->bal ?? 0);
                        $sumExp += $bal;
                    @endphp
                    @if($bal != 0)
                    <tr>
                        <td class="account-name">
                            {{ $acc->nama_akun }}
                            @if(str_contains(strtolower($acc->nama_akun), 'gaji'))
                                <div style="font-size: 9px; color: #6b7280; font-weight: normal;">BTKTL (Gaji Admin, Supervisor)</div>
                            @endif
                        </td>
                        <td class="account-code">{{ $acc->kode_akun }}</td>
                        <td class="amount">Rp {{ number_format($bal, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                
                <tr class="subtotal-row">
                    <td class="account-name">Total Beban Usaha</td>
                    <td class="account-code"></td>
                    <td class="amount">Rp {{ number_format($sumExp, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            👉 Catatan: Gaji yang tercatat di beban usaha adalah BTKTL (gaji admin, supervisor, dll), berbeda dengan BTKL di HPP (gaji pekerja produksi langsung).
        </div>

        <!-- LABA BERSIH -->
        <table>
            <tbody>
                <tr class="laba-bersih @if(($labaKotor - $sumExp) < 0)negative @endif">
                    <td class="account-name">✅ LABA BERSIH</td>
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
            <div class="footer-line">Dicetak pada: {{ now()->format('d M Y H:i:s') }}</div>
            <div class="footer-line" style="margin-top: 8px;">© 2024 UMKM COE - Sistem Informasi Manufaktur</div>
        </div>
    </div>
</body>
</html>

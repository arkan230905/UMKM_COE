<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Besar - {{ $coa->kode_akun }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            background-color: white;
            padding: 20px;
        }
        @page { margin: 30px 40px; }
        
        /* Header Styling */
        .header-section {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-table { width: 100%; border: none; margin: 0; }
        .header-table td { border: none; padding: 0; }
        .company-info { text-align: left; }
        .company-name { font-size: 20px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; }
        .company-address { font-size: 10px; color: #555; margin-top: 5px; }
        .report-info { text-align: right; }
        .report-title { font-size: 18px; font-weight: bold; color: #333; text-transform: uppercase; margin-bottom: 5px; }
        .report-period { font-size: 11px; color: #666; }
        
        /* Section Titles */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px solid #1e3a8a;
            padding-bottom: 5px;
            margin-bottom: 10px;
            margin-top: 20px;
            text-transform: uppercase;
        }
        
        /* Summary Box */
        .summary-box {
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-box table { width: 100%; border-collapse: collapse; margin: 0; }
        .summary-box th, .summary-box td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .summary-box th { background-color: #f8f9fa; font-size: 10px; color: #555; text-transform: uppercase; }
        .summary-box td { font-size: 14px; font-weight: bold; color: #1e3a8a; }
        
        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 10px;
        }
        .data-table thead th {
            background-color: #1e3a8a;
            color: white;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .val-danger { color: #dc2626 !important; }
        .val-success { color: #16a34a !important; }
        .val-primary { color: #1e3a8a !important; }
        
        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .total-row td { border-top: 2px solid #94a3b8; }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <div class="header-section">
        <table class="header-table">
            <tr>
                <td class="company-info" width="60%">
                    <div class="company-name">{{ strtoupper($perusahaan->nama_perusahaan ?? 'UMKM COE') }}</div>
                    <div class="company-address">
                        {{ $perusahaan->alamat ?? 'Bandung, Jawa Barat' }}<br>
                        @if($perusahaan->no_telepon ?? false) Telp: {{ $perusahaan->no_telepon }} @endif
                    </div>
                </td>
                <td class="report-info" width="40%">
                    <div class="report-title">Buku Besar</div>
                    <div class="report-period">
                        @if($month && $year)
                            Periode: {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                        @else
                            Keseluruhan Transaksi
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="section-title">Informasi Akun</div>
    <div class="summary-box">
        <table>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Tipe Akun</th>
                <th style="text-align: right;">Saldo Awal</th>
                <th style="text-align: right;">Mutasi Debit</th>
                <th style="text-align: right;">Mutasi Kredit</th>
                <th style="text-align: right;">Saldo Akhir</th>
            </tr>
            <tr>
                <td>{{ $coa->kode_akun }}</td>
                <td>{{ $coa->nama_akun }}</td>
                <td>{{ $coa->tipe_akun }}</td>
                <td style="text-align: right;">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td style="text-align: right;" class="val-primary">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td style="text-align: right;" class="val-danger">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                <td style="text-align: right;" class="val-success">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Detail Transaksi -->
    <div class="section-title">Rincian Transaksi Jurnal</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="12%">Tanggal</th>
                <th width="40%">Deskripsi Transaksi</th>
                <th width="16%">Debit</th>
                <th width="16%">Kredit</th>
                <th width="16%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $saldo = (float)$saldoAwal;
            @endphp
            @forelse($lines as $e)
                @php
                    $saldo += ((float)($e->debit ?? 0) - (float)($e->kredit ?? 0));
                @endphp
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($e->tanggal ?? date('Y-m-d'))->format('d/m/Y') }}</td>
                    <td>{{ $e->keterangan ?? 'Tanpa keterangan' }}</td>
                    <td class="text-right">
                        @if(($e->debit ?? 0) > 0)
                            Rp {{ number_format($e->debit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if(($e->kredit ?? 0) > 0)
                            Rp {{ number_format($e->kredit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right {{ $saldo >= 0 ? 'val-primary' : 'val-danger' }}">
                        Rp {{ number_format($saldo, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #666;">
                        <em>Tidak ada transaksi jurnal untuk akun ini pada periode yang dipilih.</em>
                    </td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL SALDO AKHIR</td>
                <td class="text-right {{ $saldoAkhir >= 0 ? 'val-primary' : 'val-danger' }}">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada {{ date('d/m/Y H:i') }} oleh sistem SIMCOST &bull; Laporan Resmi
    </div>

</body>
</html>

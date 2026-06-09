<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Penyesuaian - {{ $periode->isoFormat('MMMM YYYY') }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 10px;
            color: #666;
        }
        
        .status-badge {
            text-align: center;
            margin: 15px 0;
            padding: 8px;
            background-color: {{ $isPosted ? '#d4edda' : '#fff3cd' }};
            border: 1px solid {{ $isPosted ? '#c3e6cb' : '#ffeaa7' }};
            border-radius: 5px;
            font-weight: bold;
            color: {{ $isPosted ? '#155724' : '#856404' }};
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        thead {
            background-color: #5a3a1a;
            color: white;
        }
        
        th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #5a3a1a;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        
        tbody tr:nth-child(4n-3),
        tbody tr:nth-child(4n-2) {
            background-color: #f9f9f9;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .fw-bold {
            font-weight: bold;
        }
        
        .text-muted {
            color: #666;
            font-size: 9px;
        }
        
        .fst-italic {
            font-style: italic;
            padding-left: 20px;
        }
        
        tfoot {
            background-color: #5a3a1a;
            color: white;
            font-weight: bold;
        }
        
        tfoot td {
            border: 1px solid #5a3a1a;
            padding: 10px 8px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        
        code {
            background-color: #f4f4f4;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>PT MANUFAKTUR COE</h2>
        <p>Periode: {{ $periode->isoFormat('MMMM YYYY') }}</p>
        <h3 style="margin: 10px 0 0 0; font-size: 14px;">Jurnal Penyesuaian</h3>
    </div>
    
    <!-- Status Badge -->
    <div class="status-badge">
        @if($isPosted)
            ✓ Sudah Diposting
        @else
            ⚠ Belum Diposting
        @endif
    </div>
    
    <!-- Journal Table -->
    @if(count($jurnalEntries) > 0)
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 12%;">TANGGAL</th>
                    <th style="width: 45%;">KETERANGAN</th>
                    <th class="text-center" style="width: 10%;">REF</th>
                    <th class="text-right" style="width: 16.5%;">DEBIT</th>
                    <th class="text-right" style="width: 16.5%;">KREDIT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jurnalEntries as $entry)
                    <!-- Debit Row -->
                    <tr>
                        <td class="text-center" rowspan="2" style="vertical-align: top;">
                            {{ \Carbon\Carbon::parse($entry['tanggal'])->format('d M Y') }}
                        </td>
                        <td>
                            <div class="fw-bold">{{ $entry['keterangan_debit'] }}</div>
                            <div class="text-muted">{{ $entry['kategori'] }}</div>
                        </td>
                        <td class="text-center">
                            <code>{{ $entry['ref_debit'] }}</code>
                        </td>
                        <td class="text-right fw-bold">
                            Rp {{ number_format($entry['debit'], 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                    <!-- Kredit Row -->
                    <tr>
                        <td class="fst-italic">
                            {{ $entry['keterangan_kredit'] }}
                        </td>
                        <td class="text-center">
                            <code>{{ $entry['ref_kredit'] }}</code>
                        </td>
                        <td></td>
                        <td class="text-right fw-bold">
                            Rp {{ number_format($entry['kredit'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-center fw-bold">TOTAL</td>
                    <td class="text-right">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #999;">
            <p>Tidak ada data penyusutan aset untuk periode ini</p>
        </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p>Dicetak pada: {{ now()->isoFormat('DD MMMM YYYY HH:mm') }}</p>
        <p>PT MANUFAKTUR COE</p>
    </div>
</body>
</html>

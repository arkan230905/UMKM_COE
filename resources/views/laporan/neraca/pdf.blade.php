<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Posisi Keuangan (Neraca)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 6px;
        }
        table th {
            background-color: #8B4513;
            color: white;
            font-weight: bold;
        }
        .section-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .subtotal {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .total {
            background-color: #8B4513;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .two-column {
            width: 100%;
        }
        .two-column td {
            width: 50%;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN POSISI KEUANGAN (NERACA)</h2>
        <p>Periode: {{ date('d/m/Y', strtotime($neraca['periode']['tanggal_awal'])) }} - {{ date('d/m/Y', strtotime($neraca['periode']['tanggal_akhir'])) }}</p>
    </div>

    <!-- Balance Status -->
    @if(!$neraca['neraca_seimbang'])
    <div class="alert alert-danger">
        <strong>PERINGATAN:</strong> Neraca tidak seimbang! 
        Selisih: Rp {{ number_format(abs($neraca['selisih']), 0, ',', '.') }}
    </div>
    @else
    <div class="alert alert-success">
        <strong>Neraca Seimbang</strong>
    </div>
    @endif

    <!-- Two Column Layout -->
    <table class="two-column">
        <tr>
            <td>
                <!-- ASET -->
                <table>
                    <thead>
                        <tr>
                            <th colspan="2" class="text-center">ASET</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aset Lancar -->
                        <tr class="section-header">
                            <td colspan="2">ASET LANCAR</td>
                        </tr>
                        @foreach($neraca['aset']['lancar'] as $item)
                        <tr>
                            <td>{{ $item['nama_akun'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="subtotal">
                            <td>Total Aset Lancar</td>
                            <td class="text-right">Rp {{ number_format($neraca['aset']['total_lancar'], 0, ',', '.') }}</td>
                        </tr>
                        
                        <!-- Aset Tidak Lancar -->
                        <tr class="section-header">
                            <td colspan="2">ASET TIDAK LANCAR</td>
                        </tr>
                        @foreach($neraca['aset']['tidak_lancar'] as $item)
                        <tr>
                            <td>{{ $item['nama_akun'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="subtotal">
                            <td>Total Aset Tidak Lancar</td>
                            <td class="text-right">Rp {{ number_format($neraca['aset']['total_tidak_lancar'], 0, ',', '.') }}</td>
                        </tr>
                        
                        <!-- Total Aset -->
                        <tr class="total">
                            <td>TOTAL ASET</td>
                            <td class="text-right">Rp {{ number_format($neraca['aset']['total_aset'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td>
                <!-- KEWAJIBAN DAN EKUITAS -->
                <table>
                    <thead>
                        <tr>
                            <th colspan="2" class="text-center">KEWAJIBAN DAN EKUITAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Kewajiban -->
                        <tr class="section-header">
                            <td colspan="2">KEWAJIBAN</td>
                        </tr>
                        @foreach($neraca['kewajiban']['detail'] as $item)
                        <tr>
                            <td>{{ $item['nama_akun'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="subtotal">
                            <td>Total Kewajiban</td>
                            <td class="text-right">Rp {{ number_format($neraca['kewajiban']['total'], 0, ',', '.') }}</td>
                        </tr>
                        
                        <!-- Ekuitas -->
                        <tr class="section-header">
                            <td colspan="2">EKUITAS</td>
                        </tr>
                        @foreach($neraca['ekuitas']['detail'] as $item)
                        <tr>
                            <td>{{ $item['nama_akun'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="subtotal">
                            <td>Total Ekuitas</td>
                            <td class="text-right">Rp {{ number_format($neraca['ekuitas']['total'], 0, ',', '.') }}</td>
                        </tr>
                        
                        <!-- Total Kewajiban dan Ekuitas -->
                        <tr class="total">
                            <td>TOTAL KEWAJIBAN DAN EKUITAS</td>
                            <td class="text-right">Rp {{ number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666;">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>

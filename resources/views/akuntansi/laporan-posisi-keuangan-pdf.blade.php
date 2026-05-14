<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Posisi Keuangan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            background-color: #f8f9fa;
            padding: 8px;
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
        }
        
        .subsection-header {
            font-weight: 600;
            padding: 5px 20px;
            background-color: #f1f3f4;
        }
        
        .account-item {
            padding: 3px 30px;
        }
        
        .account-code {
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .amount {
            text-align: right;
            padding-right: 10px;
        }
        
        .subtotal {
            font-weight: 600;
            padding: 5px 20px;
            border-bottom: 1px solid #000;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e9ecef;
            padding: 8px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        
        .balance-check {
            margin-top: 20px;
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
        
        .balance-check.warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }
        
        .spacing {
            height: 15px;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .text-success {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN POSISI KEUANGAN</h2>
        <p>
            Periode: {{ \Carbon\Carbon::parse($tahun.'-'.$bulan.'-01')->isoFormat('MMMM YYYY') }}
        </p>
    </div>

    <table>
        <tbody>
            <!-- ASET SECTION -->
            <tr>
                <td colspan="2" class="section-title">ASET</td>
                <td class="section-title amount"></td>
            </tr>
            
            <!-- ASET LANCAR -->
            <tr>
                <td colspan="2" class="subsection-header">ASET LANCAR</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($neraca['aset']['lancar'] as $item)
                <tr>
                    <td class="account-item">{{ $item['nama_akun'] }}</td>
                    <td class="account-code">{{ $item['kode_akun'] }}</td>
                    <td class="amount">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="subtotal">Jumlah Aset Lancar</td>
                <td class="subtotal amount">Rp {{ number_format($neraca['aset']['total_lancar'], 0, ',', '.') }}</td>
            </tr>
            
            <!-- ASET TIDAK LANCAR -->
            <tr>
                <td colspan="2" class="subsection-header">ASET TIDAK LANCAR</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($neraca['aset']['tidak_lancar'] as $item)
                <tr>
                    <td class="account-item">{{ $item['nama_akun'] }}</td>
                    <td class="account-code">{{ $item['kode_akun'] }}</td>
                    <td class="amount">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="subtotal">Jumlah Aset Tidak Lancar</td>
                <td class="subtotal amount">Rp {{ number_format($neraca['aset']['total_tidak_lancar'], 0, ',', '.') }}</td>
            </tr>
            
            <!-- TOTAL ASET -->
            <tr>
                <td colspan="2" class="total-row">JUMLAH ASET</td>
                <td class="total-row amount">Rp {{ number_format($neraca['aset']['total_aset'], 0, ',', '.') }}</td>
            </tr>
            
            <tr class="spacing">
                <td colspan="3">&nbsp;</td>
            </tr>
            
            <!-- KEWAJIBAN DAN EKUITAS SECTION -->
            <tr>
                <td colspan="2" class="section-title">KEWAJIBAN DAN EKUITAS</td>
                <td class="section-title amount"></td>
            </tr>
            
            <!-- KEWAJIBAN -->
            <tr>
                <td colspan="2" class="subsection-header">KEWAJIBAN</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($neraca['kewajiban']['detail'] as $item)
                <tr>
                    <td class="account-item">{{ $item['nama_akun'] }}</td>
                    <td class="account-code">{{ $item['kode_akun'] }}</td>
                    <td class="amount">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="subtotal">Jumlah Kewajiban</td>
                <td class="subtotal amount">Rp {{ number_format($neraca['kewajiban']['total'], 0, ',', '.') }}</td>
            </tr>
            
            <!-- EKUITAS -->
            <tr>
                <td colspan="2" class="subsection-header">EKUITAS / MODAL</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($neraca['ekuitas']['detail'] as $item)
                <tr>
                    <td class="account-item">{{ $item['nama_akun'] }}</td>
                    <td class="account-code">{{ $item['kode_akun'] }}</td>
                    <td class="amount">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            <!-- ✅ LABA/RUGI BERJALAN dari Laporan Laba Rugi -->
            @if(isset($neraca['laba_rugi_berjalan']))
            <tr>
                <td class="account-item">
                    {{ $neraca['laba_rugi_akun_nama'] ?? 'Laba/Rugi Berjalan' }}
                </td>
                <td class="account-code">-</td>
                <td class="amount">
                    @if($neraca['laba_rugi_berjalan'] < 0)
                        -Rp {{ number_format(abs($neraca['laba_rugi_berjalan']), 0, ',', '.') }}
                    @else
                        Rp {{ number_format($neraca['laba_rugi_berjalan'], 0, ',', '.') }}
                    @endif
                </td>
            </tr>
            @endif
            
            <tr>
                <td colspan="2" class="subtotal">Jumlah Ekuitas</td>
                <td class="subtotal amount">Rp {{ number_format($neraca['total_ekuitas_with_laba_rugi'] ?? $neraca['ekuitas']['total'], 0, ',', '.') }}</td>
            </tr>
            
            <!-- TOTAL KEWAJIBAN DAN EKUITAS -->
            <tr>
                <td colspan="2" class="total-row">JUMLAH KEWAJIBAN DAN EKUITAS</td>
                <td class="total-row amount">Rp {{ number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Balance Check -->
    <div class="balance-check {{ $neraca['neraca_seimbang'] ? '' : 'warning' }}">
        <strong>Cek Keseimbangan:</strong><br>
        Total Aset: Rp {{ number_format($neraca['aset']['total_aset'], 0, ',', '.') }}<br>
        Total Kewajiban + Ekuitas: Rp {{ number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') }}<br>
        @if($neraca['neraca_seimbang'])
            <strong>✓ SEIMBANG</strong>
        @else
            <strong>⚠ TIDAK SEIMBANG</strong><br>
            Selisih: Rp {{ number_format(abs($neraca['selisih']), 0, ',', '.') }}
        @endif
    </div>
    
    @if(isset($neraca['laba_rugi_berjalan']) && $neraca['laba_rugi_berjalan'] != 0)
    <div style="margin-top: 15px; padding: 10px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px;">
        <strong>Informasi {{ $neraca['laba_rugi_akun_nama'] }}:</strong><br>
        @if($neraca['laba_rugi_berjalan'] < 0)
            -Rp {{ number_format(abs($neraca['laba_rugi_berjalan']), 0, ',', '.') }}
        @else
            Rp {{ number_format($neraca['laba_rugi_berjalan'], 0, ',', '.') }}
        @endif
        <br>
        <small>Nilai ini diambil dari hasil akhir Laporan Laba Rugi periode yang sama</small>
    </div>
    @endif
</body>
</html>
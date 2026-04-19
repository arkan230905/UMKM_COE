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
            @foreach($asetLancar as $item)
                @php $saldo = $getFinalBalance($item); @endphp
                <tr>
                    <td class="account-item">{{ $item->nama_akun }}</td>
                    <td class="account-code">{{ $item->kode_akun }}</td>
                    <td class="amount">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="subtotal">Jumlah Aset Lancar</td>
                <td class="subtotal amount">Rp {{ number_format($totalAsetLancar, 0, ',', '.') }}</td>
            </tr>
            
            <!-- ASET TIDAK LANCAR -->
            <tr>
                <td colspan="2" class="subsection-header">ASET TIDAK LANCAR</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($asetTidakLancar as $item)
                @php $saldo = $getFinalBalance($item); @endphp
                <tr>
                    <td class="account-item">{{ $item->nama_akun }}</td>
                    <td class="account-code">{{ $item->kode_akun }}</td>
                    <td class="amount">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="subtotal">Jumlah Aset Tidak Lancar</td>
                <td class="subtotal amount">Rp {{ number_format($totalAsetTidakLancar, 0, ',', '.') }}</td>
            </tr>
            
            <!-- TOTAL ASET -->
            <tr>
                <td colspan="2" class="total-row">JUMLAH ASET</td>
                <td class="total-row amount">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
            </tr>
            
            <tr class="spacing">
                <td colspan="3">&nbsp;</td>
            </tr>
            
            <!-- KEWAJIBAN DAN EKUITAS SECTION -->
            <tr>
                <td colspan="2" class="section-title">KEWAJIBAN DAN EKUITAS</td>
                <td class="section-title amount"></td>
            </tr>
            
            <!-- KEWAJIBAN JANGKA PENDEK -->
            <tr>
                <td colspan="2" class="subsection-header">KEWAJIBAN JANGKA PENDEK</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($kewajibanPendek as $item)
                @php $saldo = $getFinalBalance($item); @endphp
                <tr>
                    <td class="account-item">{{ $item->nama_akun }}</td>
                    <td class="account-code">{{ $item->kode_akun }}</td>
                    <td class="amount">Rp {{ number_format($saldo > 0 ? $saldo : 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="subtotal">Jumlah Kewajiban Jangka Pendek</td>
                <td class="subtotal amount">Rp {{ number_format($totalKewajibanPendek, 0, ',', '.') }}</td>
            </tr>
            
            <!-- KEWAJIBAN JANGKA PANJANG -->
            <tr>
                <td colspan="2" class="subsection-header">KEWAJIBAN JANGKA PANJANG</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($kewajibanPanjang as $item)
                @php $saldo = $getFinalBalance($item); @endphp
                <tr>
                    <td class="account-item">{{ $item->nama_akun }}</td>
                    <td class="account-code">{{ $item->kode_akun }}</td>
                    <td class="amount">Rp {{ number_format($saldo > 0 ? $saldo : 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="subtotal">Jumlah Kewajiban Jangka Panjang</td>
                <td class="subtotal amount">Rp {{ number_format($totalKewajibanPanjang, 0, ',', '.') }}</td>
            </tr>
            
            <!-- EKUITAS -->
            <tr>
                <td colspan="2" class="subsection-header">EKUITAS / MODAL</td>
                <td class="subsection-header amount"></td>
            </tr>
            @foreach($ekuitas as $item)
                @php $saldo = $getFinalBalance($item); @endphp
                <tr>
                    <td class="account-item">{{ $item->nama_akun }}</td>
                    <td class="account-code">{{ $item->kode_akun }}</td>
                    <td class="amount">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @if($profitLoss != 0)
            <tr>
                <td class="account-item">Laba/Rugi Periode Berjalan</td>
                <td class="account-code">-</td>
                <td class="amount">Rp {{ number_format($profitLoss, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" class="subtotal">Jumlah Ekuitas</td>
                <td class="subtotal amount">Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</td>
            </tr>
            
            <!-- TOTAL KEWAJIBAN DAN EKUITAS -->
            <tr>
                <td colspan="2" class="total-row">JUMLAH KEWAJIBAN DAN EKUITAS</td>
                <td class="total-row amount">Rp {{ number_format($totalKewajibanEkuitas, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Balance Check -->
    <div class="balance-check {{ ($totalAset == $totalKewajibanEkuitas) ? '' : 'warning' }}">
        <strong>Cek Keseimbangan:</strong><br>
        Total Aset: Rp {{ number_format($totalAset, 0, ',', '.') }}<br>
        Total Kewajiban + Ekuitas: Rp {{ number_format($totalKewajibanEkuitas, 0, ',', '.') }}<br>
        @if($totalAset == $totalKewajibanEkuitas)
            <strong>✓ SEIMBANG</strong>
        @else
            <strong>⚠ TIDAK SEIMBANG</strong><br>
            Selisih: Rp {{ number_format($totalAset - $totalKewajibanEkuitas, 0, ',', '.') }}
        @endif
    </div>
    
    @if($profitLoss != 0)
    <div style="margin-top: 15px; padding: 10px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px;">
        <strong>Informasi Laba/Rugi Periode Berjalan:</strong><br>
        Rp {{ number_format($profitLoss, 0, ',', '.') }}<br>
        <small>Total Pendapatan: Rp {{ number_format($totalRevenue, 0, ',', '.') }} | Total Beban: Rp {{ number_format($totalExpense, 0, ',', '.') }}</small><br>
        <small>Laba/rugi ini sudah termasuk dalam total ekuitas di atas</small>
    </div>
    @endif
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Posisi Keuangan - {{ \Carbon\Carbon::create($tahun, $bulan, 1)->isoFormat('MMMM YYYY') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px;
            color: #000;
            padding: 20px;
            background: #fff;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
            position: relative;
        }
        
        .header h1 { 
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            letter-spacing: 1.5px;
        }
        
        .header .period {
            font-size: 10px;
            color: #555;
        }
        
        .header .meta-info {
            position: absolute;
            right: 0;
            top: 0;
            text-align: left;
            font-size: 9px;
            line-height: 1.6;
        }
        
        .summary-container {
            width: 100%;
            margin-bottom: 25px;
        }
        
        .summary-boxes {
            width: 100%;
            display: table;
            table-layout: fixed;
        }
        
        .summary-box {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            background: #F8F8F8;
            border: 1px solid #DDD;
            text-align: center;
            vertical-align: middle;
        }
        
        .summary-box + .summary-box {
            border-left: none;
        }
        
        .summary-box .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .summary-box .value {
            font-size: 13px;
            font-weight: bold;
            color: #000;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            padding: 6px 0;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .subsection-title {
            font-size: 9px;
            font-weight: bold;
            background: #F5F5F5;
            padding: 6px 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .item {
            padding: 3px 20px;
            font-size: 9px;
        }
        
        .item::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .item-name {
            float: left;
            width: 65%;
        }
        
        .item-value {
            float: right;
            width: 33%;
            text-align: right;
        }
        
        .subtotal {
            padding: 6px 10px;
            font-weight: bold;
            border-top: 1px solid #333;
            font-size: 10px;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        .subtotal::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .subtotal-name {
            float: left;
            width: 65%;
        }
        
        .subtotal-value {
            float: right;
            width: 33%;
            text-align: right;
        }
        
        .divider {
            border: none;
            border-top: 1px solid #333;
            margin: 8px 0;
        }
        
        .grand-total {
            padding: 8px 10px;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 10px;
            margin-top: 5px;
        }
        
        .grand-total::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .grand-total-name {
            float: left;
            width: 65%;
            text-transform: uppercase;
        }
        
        .grand-total-value {
            float: right;
            width: 33%;
            text-align: right;
        }
        
        .no-data {
            padding: 6px 20px;
            font-style: italic;
            color: #999;
            font-size: 9px;
        }
        
        .footer-note {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px dotted #BBB;
            text-align: center;
            font-size: 8px;
            color: #888;
            font-style: italic;
        }
        
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="meta-info">
            <div>Dicetak : {{ now()->format('d/m/Y') }}</div>
            <div>Jam &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ now()->format('H:i:s') }}</div>
        </div>
        <h1>LAPORAN POSISI KEUANGAN</h1>
        <div class="period">Per {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY') }}</div>
    </div>
    
    <div class="summary-container">
        <div class="summary-boxes">
            <div class="summary-box">
                <div class="label">TOTAL ASET</div>
                <div class="value">Rp {{ number_format($neraca['aset']['total_aset'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-box">
                <div class="label">TOTAL KEWAJIBAN</div>
                <div class="value">Rp {{ number_format($neraca['kewajiban']['total'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-box">
                <div class="label">TOTAL EKUITAS</div>
                <div class="value">Rp {{ number_format($neraca['total_ekuitas_with_laba_rugi'] ?? $neraca['ekuitas']['total'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    
    <!-- ASET SECTION -->
    <div class="section">
        <div class="section-title">ASET</div>
        
        <!-- ASET LANCAR -->
        <div class="subsection-title">ASET LANCAR</div>
        @forelse($neraca['aset']['lancar'] as $item)
        <div class="item">
            <span class="item-name">{{ $item['nama_akun'] }}</span>
            <span class="item-value">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="no-data">Tidak ada data</div>
        @endforelse
        
        <div class="subtotal">
            <span class="subtotal-name">Jumlah Aset Lancar</span>
            <span class="subtotal-value">Rp {{ number_format($neraca['aset']['total_lancar'], 0, ',', '.') }}</span>
        </div>
        
        <!-- ASET TIDAK LANCAR -->
        <div class="subsection-title">ASET TIDAK LANCAR</div>
        @forelse($neraca['aset']['tidak_lancar'] as $item)
        <div class="item">
            <span class="item-name">{{ $item['nama_akun'] }}</span>
            <span class="item-value">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="no-data">Tidak ada data</div>
        @endforelse
        
        <hr class="divider">
        
        <div class="grand-total">
            <span class="grand-total-name">Total Aset</span>
            <span class="grand-total-value">Rp {{ number_format($neraca['aset']['total_aset'], 0, ',', '.') }}</span>
        </div>
    </div>
    
    <!-- KEWAJIBAN SECTION -->
    <div class="section">
        <div class="section-title">KEWAJIBAN</div>
        
        @forelse($neraca['kewajiban']['detail'] as $item)
        <div class="item">
            <span class="item-name">{{ $item['nama_akun'] }}</span>
            <span class="item-value">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="no-data">Tidak ada data</div>
        @endforelse
        
        <hr class="divider">
        
        <div class="grand-total">
            <span class="grand-total-name">Total Kewajiban</span>
            <span class="grand-total-value">Rp {{ number_format($neraca['kewajiban']['total'], 0, ',', '.') }}</span>
        </div>
    </div>
    
    <!-- EKUITAS SECTION -->
    <div class="section">
        <div class="section-title">EKUITAS</div>
        
        @forelse($neraca['ekuitas']['detail'] as $item)
        <div class="item">
            <span class="item-name">{{ $item['nama_akun'] }}</span>
            <span class="item-value">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</span>
        </div>
        @empty
        @endforelse
        
        @if(isset($neraca['laba_rugi_berjalan']))
        <div class="item">
            <span class="item-name">{{ $neraca['laba_rugi_akun_nama'] }}</span>
            <span class="item-value">Rp {{ number_format($neraca['laba_rugi_berjalan'], 0, ',', '.') }}</span>
        </div>
        @endif
        
        <div class="subtotal">
            <span class="subtotal-name">Jumlah Ekuitas</span>
            <span class="subtotal-value">Rp {{ number_format($neraca['total_ekuitas_with_laba_rugi'] ?? $neraca['ekuitas']['total'], 0, ',', '.') }}</span>
        </div>
        
        <hr class="divider">
        
        <div class="grand-total">
            <span class="grand-total-name">Total Kewajiban dan Ekuitas</span>
            <span class="grand-total-value">Rp {{ number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') }}</span>
        </div>
    </div>

    @if(!$neraca['neraca_seimbang'])
    <div style="background-color: #FFF3CD; border: 1px solid #FFE69C; color: #856404; padding: 10px; margin-top: 15px; font-size: 9px;">
        <strong>Peringatan:</strong> Neraca tidak seimbang! Selisih: Rp {{ number_format(abs($neraca['selisih']), 0, ',', '.') }}
    </div>
    @endif

    <div class="footer-note">
        Catatan: Laporan ini dibuat secara otomatis oleh sistem.
    </div>
</body>
</html>

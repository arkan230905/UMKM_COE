<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $penggajian->pegawai->nama }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 14px;
            color: #666;
        }
        
        .employee-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        
        .employee-info-row {
            display: table-row;
        }
        
        .employee-info-cell {
            display: table-cell;
            padding: 8px 15px;
            border-bottom: 1px solid #ddd;
            width: 50%;
        }
        
        .employee-info-label {
            font-weight: bold;
            color: #555;
            width: 40%;
        }
        
        .employee-info-value {
            color: #333;
            font-weight: 500;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            background-color: #2c3e50;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        
        .section-content {
            margin-left: 15px;
        }
        
        .item-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        
        .item-label {
            display: table-cell;
            width: 70%;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .item-value {
            display: table-cell;
            width: 30%;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            text-align: right;
            font-weight: 500;
            font-size: 13px;
        }
        
        .item-detail {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
            border-collapse: collapse;
        }
        
        .total-label {
            display: table-cell;
            width: 70%;
            font-weight: bold;
            font-size: 14px;
        }
        
        .total-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        
        .net-salary {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 3px;
            margin: 25px 0;
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .net-salary-label {
            display: table-cell;
            width: 70%;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .net-salary-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        
        .signature-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 10px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>SLIP GAJI</h1>
            <p>Periode: {{ $penggajian->tanggal_penggajian->format('d F Y') }}</p>
        </div>

        <!-- Employee Information -->
        <div class="employee-info">
            <div class="employee-info-row">
                <div class="employee-info-cell">
                    <span class="employee-info-label">Nama Pegawai</span>
                </div>
                <div class="employee-info-cell">
                    <span class="employee-info-value">{{ $penggajian->pegawai->nama }}</span>
                </div>
            </div>
            <div class="employee-info-row">
                <div class="employee-info-cell">
                    <span class="employee-info-label">Kode Pegawai</span>
                </div>
                <div class="employee-info-cell">
                    <span class="employee-info-value">{{ $penggajian->pegawai->kode_pegawai ?? '-' }}</span>
                </div>
            </div>
            <div class="employee-info-row">
                <div class="employee-info-cell">
                    <span class="employee-info-label">Jabatan</span>
                </div>
                <div class="employee-info-cell">
                    <span class="employee-info-value">{{ $penggajian->pegawai->jabatan ?? '-' }}</span>
                </div>
            </div>
            <div class="employee-info-row">
                <div class="employee-info-cell">
                    <span class="employee-info-label">Jenis Pegawai</span>
                </div>
                <div class="employee-info-cell">
                    <span class="employee-info-value">
                        @if($slipData['jenis_pegawai'] === 'btkl')
                            BTKL (Borongan/Tarif)
                        @else
                            BTKTL (Tetap)
                        @endif
                    </span>
                </div>
            </div>
            <div class="employee-info-row">
                <div class="employee-info-cell">
                    <span class="employee-info-label">Bank</span>
                </div>
                <div class="employee-info-cell">
                    <span class="employee-info-value">{{ $penggajian->pegawai->bank ?? '-' }}</span>
                </div>
            </div>
            <div class="employee-info-row">
                <div class="employee-info-cell">
                    <span class="employee-info-label">Nomor Rekening</span>
                </div>
                <div class="employee-info-cell">
                    <span class="employee-info-value">{{ $penggajian->pegawai->nomor_rekening ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Pendapatan Section -->
        <div class="section">
            <div class="section-title">RINCIAN PENDAPATAN</div>
            <div class="section-content">
                @foreach($slipData['pendapatan'] as $item)
                    <div class="item-row">
                        <div class="item-label">
                            {{ $item['label'] }}
                            @if(isset($item['tarif']))
                                <div class="item-detail">
                                    Rp {{ number_format($item['tarif'], 0, ',', '.') }} × {{ $item['unit'] }}
                                </div>
                            @endif
                        </div>
                        <div class="item-value">Rp {{ number_format($item['nilai'], 0, ',', '.') }}</div>
                    </div>
                @endforeach
                
                <div class="total-row">
                    <div class="total-label">Total Pendapatan</div>
                    <div class="total-value">Rp {{ number_format($slipData['total_pendapatan'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Potongan Section -->
        @if(count($slipData['potongan']) > 0)
            <div class="section">
                <div class="section-title">RINCIAN POTONGAN</div>
                <div class="section-content">
                    @foreach($slipData['potongan'] as $item)
                        <div class="item-row">
                            <div class="item-label">{{ $item['label'] }}</div>
                            <div class="item-value">Rp {{ number_format($item['nilai'], 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                    
                    <div class="total-row">
                        <div class="total-label">Total Potongan</div>
                        <div class="total-value">Rp {{ number_format($slipData['total_potongan'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Net Salary -->
        <div class="net-salary">
            <div class="net-salary-label">GAJI BERSIH</div>
            <div class="net-salary-value">Rp {{ number_format($slipData['total_akhir'], 0, ',', '.') }}</div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-cell">
                <div>Pegawai</div>
                <div class="signature-line">{{ $penggajian->pegawai->nama }}</div>
            </div>
            <div class="signature-cell">
                <div>Pimpinan</div>
                <div class="signature-line">(...........................)</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Slip gaji ini dicetak pada: {{ now()->format('d F Y H:i:s') }}</p>
            <p>Dokumen ini merupakan bukti resmi pembayaran gaji karyawan.</p>
        </div>
    </div>
</body>
</html>

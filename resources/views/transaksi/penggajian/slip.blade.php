<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $penggajian->pegawai->nama }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #555;
        }
        .salary-details {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-table th,
        .salary-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .salary-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .salary-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .salary-table .total {
            font-weight: bold;
            background: #f8f9fa;
        }
        .status-section {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-lunas { background: #28a745; color: white; }
        .status-belum_lunas { background: #ffc107; color: black; }
        .status-dibatalkan { background: #dc3545; color: white; }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body { background: white; }
            .slip-container { 
                box-shadow: none; 
                padding: 20px;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">PT. MANUFAKTUR INDONESIA</div>
            <div class="document-title">SLIP GAJI</div>
        </div>

        <!-- Employee Information -->
        <div class="employee-info">
            <div>
                <div class="info-row">
                    <span class="info-label">Nama Pegawai:</span>
                    <span class="info-value">{{ $penggajian->pegawai->nama ?? 'Data tidak tersedia' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nomor Induk:</span>
                    <span class="info-value">{{ $penggajian->pegawai->nomor_induk_pegawai ?? 'Data tidak tersedia' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jabatan:</span>
                    <span class="info-value">{{ $penggajian->pegawai->jabatan ?? 'Data tidak tersedia' }}</span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="info-label">Jenis Pegawai:</span>
                    <span class="info-value">{{ strtoupper($penggajian->pegawai->jenis_pegawai ?? 'BTKTL') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Gajian:</span>
                    <span class="info-value">{{ $penggajian->tanggal_penggajian->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Cetak:</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Salary Details -->
        <div class="salary-details">
            <div class="section-title">Rincian Gaji</div>
            <table class="salary-table">
                <tr>
                    <th>Komponen</th>
                    <th class="amount">Jumlah</th>
                </tr>
                @php
                    $jenis = strtolower($penggajian->pegawai->jenis_pegawai ?? 'btktl');
                @endphp
                @if($jenis === 'btkl')
                <tr>
                    <td>Gaji Per Jam ({{ number_format($penggajian->total_jam_kerja ?? 0, 1) }} jam × Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }})</td>
                    <td class="amount">Rp {{ number_format(($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0), 0, ',', '.') }}</td>
                </tr>
                @else
                <tr>
                    <td>Gaji Pokok Bulanan</td>
                    <td class="amount">Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tunjangan</td>
                    <td class="amount">Rp {{ number_format($penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Asuransi / BPJS</td>
                    <td class="amount">Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Bonus</td>
                    <td class="amount">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Potongan</td>
                    <td class="amount">Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td>Total Gaji Bersih</td>
                    <td class="amount">Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Calculation Breakdown -->
        <div class="salary-details">
            <div class="section-title">Detail Perhitungan</div>
            <table class="salary-table">
                <tr>
                    <th>Perhitungan</th>
                    <th class="amount">Nilai</th>
                </tr>
                @if($jenis === 'btkl')
                <tr>
                    <td>Gaji Dasar ({{ number_format($penggajian->total_jam_kerja ?? 0, 1) }} jam × Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }})</td>
                    <td class="amount">Rp {{ number_format(($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0), 0, ',', '.') }}</td>
                </tr>
                @else
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="amount">Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td>+ Tunjangan</td>
                    <td class="amount">Rp {{ number_format($penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>+ Asuransi / BPJS</td>
                    <td class="amount">Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>+ Bonus</td>
                    <td class="amount">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>- Potongan</td>
                    <td class="amount">Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td>= Total Gaji Bersih</td>
                    <td class="amount">Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Status Section -->
        <div class="status-section">
            <div class="status-badge status-{{ $penggajian->status_pembayaran ?? 'belum_lunas' }}">
                {{ ucfirst($penggajian->status_pembayaran ?? 'Belum Lunas') }}
            </div>
            @if($penggajian->tanggal_dibayar)
                <div>
                    <strong>Tanggal Dibayar:</strong> {{ $penggajian->tanggal_dibayar->format('d/m/Y') }}
                </div>
                @if($penggajian->metode_pembayaran)
                    <div>
                        <strong>Metode Pembayaran:</strong> {{ ucfirst($penggajian->metode_pembayaran) }}
                    </div>
                @endif
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Dokumen ini dicetak secara otomatis dari sistem penggajian</div>
            <div>PT. MANUFAKTUR INDONESIA - {{ now()->year }}</div>
        </div>

        <!-- Action Buttons (No Print) -->
        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="fas fa-print me-2"></i>Cetak
            </button>
            <a href="{{ route('transaksi.penggajian.slip-pdf', $penggajian->id) }}" class="btn btn-success me-2">
                <i class="fas fa-download me-2"></i>Download PDF
            </a>
            <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</body>
</html>

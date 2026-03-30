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
            background: white;
            font-size: 12px;
        }
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 3px;
        }
        .document-title {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 3px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #555;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 3px;
            border-bottom: 1px solid #ddd;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .salary-table th,
        .salary-table td {
            padding: 6px;
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
            padding: 15px;
            background: #f8f9fa;
            border-radius: 3px;
            margin-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 11px;
        }
        .status-lunas { background: #28a745; color: white; }
        .status-belum_lunas { background: #ffc107; color: black; }
        .status-dibatalkan { background: #dc3545; color: white; }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 10px;
        }
        .total-section {
            background: #007bff;
            color: white;
            padding: 10px;
            border-radius: 3px;
            margin-top: 15px;
        }
        .total-section .amount {
            font-size: 14px;
            font-weight: bold;
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

        <!-- Calculation Breakdown -->
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

        <!-- Status Section -->
        <div class="status-section">
            <div class="status-badge status-{{ $penggajian->status_pembayaran ?? 'belum_lunas' }}">
                {{ ucfirst($penggajian->status_pembayaran ?? 'Belum Lunas') }}
            </div>
            @if($penggajian->tanggal_dibayar)
                <div style="margin-top: 5px;">
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
    </div>
</body>
</html>

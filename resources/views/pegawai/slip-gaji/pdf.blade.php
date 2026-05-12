<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $pegawai->nama }}</title>
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
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-salary {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .total-salary h3 {
            margin: 0;
            font-size: 28px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">UMKM COE</div>
            <div class="document-title">SLIP GAJI KARYAWAN</div>
            <small>Tanggal: {{ $penggajian->tanggal_penggajian->format('d F Y') }}</small>
        </div>

        <!-- Informasi Pegawai -->
        <div class="employee-info">
            <div>
                <div class="info-row">
                    <span class="info-label">Nama Pegawai:</span>
                    <span class="info-value">{{ $pegawai->nama }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kode Pegawai:</span>
                    <span class="info-value">{{ $pegawai->kode_pegawai }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jabatan:</span>
                    <span class="info-value">{{ $pegawai->jabatan ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jenis Pegawai:</span>
                    <span class="info-value">{{ strtoupper($jenis) }}</span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="info-label">Bank:</span>
                    <span class="info-value">{{ strtoupper($pegawai->bank ?? '-') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. Rekening:</span>
                    <span class="info-value">{{ $pegawai->nomor_rekening ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @if($penggajian->status_pembayaran === 'lunas')
                            <span class="badge badge-success">Lunas</span>
                        @elseif($penggajian->status_pembayaran === 'disetujui')
                            <span class="badge badge-info">Disetujui</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst($penggajian->status_pembayaran) }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Rincian Gaji -->
        <div class="salary-details">
            <div class="section-title">Rincian Gaji</div>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th style="text-align: right;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @if($jenis === 'btkl')
                        <tr>
                            <td>Tarif per Jam</td>
                            <td style="text-align: right;">Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Total Jam Kerja</td>
                            <td style="text-align: right;">{{ number_format($penggajian->total_jam_kerja ?? 0, 0) }} Jam</td>
                        </tr>
                    @else
                        <tr>
                            <td>Gaji Pokok</td>
                            <td style="text-align: right;">Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="2"><strong>Tunjangan:</strong></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px;">• Tunjangan Jabatan</td>
                        <td style="text-align: right;">Rp {{ number_format($penggajian->tunjangan_jabatan ?? $penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px;">• Tunjangan Transport</td>
                        <td style="text-align: right;">Rp {{ number_format($penggajian->tunjangan_transport ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px;">• Tunjangan Konsumsi</td>
                        <td style="text-align: right;">Rp {{ number_format($penggajian->tunjangan_konsumsi ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px; font-weight: bold;">Total Tunjangan</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($penggajian->total_tunjangan ?? $penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    
                    <tr>
                        <td>Asuransi / BPJS</td>
                        <td style="text-align: right;">Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Bonus</td>
                        <td style="text-align: right;">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan</td>
                        <td style="text-align: right; color: #dc3545;">Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Total Gaji -->
        <div class="total-salary">
            <h3>Total Gaji Diterima</h3>
            <h2>Rp {{ number_format($totalGajiHitung, 0, ',', '.') }}</h2>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini sah dan valid</p>
            <p>Dibuat pada: {{ $penggajian->created_at->format('d F Y H:i') }}</p>
            <p>UMKM COE</p>
        </div>
    </div>
</body>
</html>

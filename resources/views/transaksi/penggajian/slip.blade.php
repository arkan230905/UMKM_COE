<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $penggajian->pegawai->nama }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 50px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1a3557;
        }

        .company-name {
            font-size: 20px;
            font-weight: 600;
            color: #1a3557;
            margin-bottom: 15px;
        }

        .slip-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a3557;
            letter-spacing: 3px;
            margin-bottom: 8px;
        }

        .period-info {
            font-size: 12px;
            color: #999;
        }

        /* Employee Information Section */
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px 40px;
            margin-bottom: 40px;
            padding: 25px 0;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 11px;
            color: #999;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 13px;
            color: #333;
            font-weight: 500;
        }

        /* Salary Details Section */
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a3557;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .salary-table thead {
            background-color: #1a3557;
            color: white;
        }

        .salary-table th {
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .salary-table th:last-child {
            text-align: right;
        }

        .salary-table td {
            padding: 12px 15px;
            font-size: 13px;
            border-bottom: 1px solid #e8e8e8;
        }

        .salary-table td:last-child {
            text-align: right;
            font-weight: 500;
        }

        .salary-table tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .salary-table tbody tr:last-child {
            background-color: #e8f0f8;
            border-top: 2px solid #1a3557;
            border-bottom: 2px solid #1a3557;
            font-weight: 600;
        }

        .salary-table tbody tr:last-child td {
            padding: 14px 15px;
            font-size: 13px;
        }

        .amount {
            color: #1a3557;
        }

        /* Signature Section */
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-top: 60px;
            padding-top: 40px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-label {
            font-size: 12px;
            color: #333;
            margin-bottom: 60px;
            font-weight: 500;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 8px;
            font-size: 12px;
            color: #333;
            font-weight: 500;
        }

        /* Print Button */
        .print-button {
            text-align: center;
            margin-bottom: 25px;
        }

        .print-button button {
            padding: 10px 24px;
            background-color: #1a3557;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .print-button button:hover {
            background-color: #0f1f33;
        }

        /* Print Styles */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                max-width: 100%;
                padding: 40px;
            }

            .print-button {
                display: none;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button onclick="window.print()">
            🖨️ Cetak Slip Gaji
        </button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">PT. PERUSAHAAN ANDA</div>
            <div class="slip-title">SLIP GAJI</div>
            <div class="period-info">
                Periode: {{ ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$penggajian->periode_bulan] ?? 'N/A' }}
                {{ $penggajian->periode_tahun ?? date('Y') }}
            </div>
        </div>

        <!-- Employee Information -->
        <div class="employee-info">
            <div class="info-item">
                <div class="info-label">Nama Karyawan</div>
                <div class="info-value">{{ $penggajian->pegawai->nama }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Kode Pegawai</div>
                <div class="info-value">{{ $penggajian->pegawai->kode_pegawai ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Jabatan</div>
                <div class="info-value">{{ $penggajian->pegawai->jabatanRelasi->nama ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Departemen</div>
                <div class="info-value">{{ $penggajian->pegawai->departemen->nama_departemen ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Tanggal Penggajian</div>
                <div class="info-value">{{ $penggajian->tanggal_penggajian->format('d/m/Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Metode Pembayaran</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $penggajian->metode_pembayaran ?? '-')) }}</div>
            </div>
        </div>

        <!-- Salary Details -->
        <div class="section-title">Rincian Gaji</div>
        <table class="salary-table">
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Produksi</td>
                    <td class="amount">Rp {{ number_format($breakdown['gaji_pokok'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Jabatan</td>
                    <td class="amount">Rp {{ number_format($penggajian->tunjangan_jabatan ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Transport</td>
                    <td class="amount">Rp {{ number_format($penggajian->tunjangan_transport ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Konsumsi</td>
                    <td class="amount">Rp {{ number_format($penggajian->tunjangan_konsumsi ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Asuransi / BPJS</td>
                    <td class="amount">- Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Gaji Bersih</td>
                    <td class="amount">Rp {{ number_format($breakdown['total'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Disetujui Oleh</div>
                <div class="signature-line">
                    ___________________________
                </div>
                <div style="margin-top: 8px; font-size: 12px; color: #666;">Kepala Departemen</div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Diterima Oleh</div>
                <div class="signature-line">
                    ___________________________
                </div>
                <div style="margin-top: 8px; font-size: 12px; color: #666;">{{ $penggajian->pegawai->nama }}</div>
            </div>
        </div>
    </div>
</body>
</html>

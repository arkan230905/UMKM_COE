<!DOCTYPE html>
<html>
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
            padding: 20px;
            background: #f5f5f5;
        }

        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #000;
            padding: 0;
        }

        /* Header Perusahaan */
        .header {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: white;
            border-radius: 8px 8px 0 0;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .slip-main-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        .company-address {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 8px;
        }

        /* Judul Slip */
        .slip-title {
            background: #e8e8e8;
            text-align: center;
            padding: 12px;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            color: #333;
        }

        /* Info Pegawai Table */
        .employee-info {
            padding: 15px 20px;
        }

        .employee-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .employee-info td {
            padding: 5px 0;
            font-size: 14px;
        }

        .employee-info td:first-child {
            width: 150px;
        }

        .employee-info td:nth-child(2) {
            width: 20px;
            text-align: center;
        }

        .employee-info td:last-child {
            font-weight: 500;
        }

        /* Rincian Gaji Table */
        .salary-details {
            padding: 0 20px 20px 20px;
        }

        .salary-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .salary-details td {
            padding: 8px 10px;
            font-size: 14px;
        }

        .salary-details .label-col {
            width: 60%;
        }

        .salary-details .amount-col {
            width: 40%;
            text-align: right;
        }

        .salary-details .detail-text {
            font-size: 11px;
            color: #666;
            font-style: italic;
            padding-left: 20px;
        }

        /* Row Styles */
        .salary-details .subtotal-row td {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 10px;
        }

        .salary-details .total-row td {
            border-top: 3px double #000;
            font-weight: bold;
            font-size: 16px;
            padding-top: 12px;
            padding-bottom: 12px;
            background: #f9f9f9;
        }

        .salary-details .underline {
            text-decoration: underline;
        }

        /* Payment Method */
        .payment-method {
            padding: 10px 20px;
            font-size: 13px;
            color: #555;
            border-top: 1px solid #ddd;
        }

        /* Signature */
        .signature {
            padding: 30px 20px 20px 20px;
            text-align: right;
        }

        .signature-date {
            margin-bottom: 60px;
            font-size: 14px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 14px;
            text-decoration: underline;
        }

        .signature-title {
            font-size: 12px;
            color: #666;
        }

        /* Print Button */
        .print-button {
            text-align: center;
            margin: 20px 0;
        }

        .print-button button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }

        .print-button button:hover {
            background: #0056b3;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .slip-container {
                max-width: 100%;
                border: 2px solid #000;
            }

            .print-button {
                display: none;
            }

            .header {
                background: #2c5282 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }

            .signature-date {
                margin-bottom: 80px;
            }
        }
    </style>
</head>
<body>
    @php
        $bulanIndo = [
            '', 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
            'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
        ];
        $bulan = $bulanIndo[$penggajian->periode_bulan];
        $tahun = $penggajian->periode_tahun;
        
        // Get perusahaan data
        $perusahaan = auth()->user()->perusahaan ?? null;
        
        // Calculate totals
        $gajiProduksi = $penggajian->gaji_pokok ?? 0;
        $tunjanganTransport = $penggajian->tunjangan_transport ?? 0;
        $tunjanganTransportFull = $penggajian->tunjangan_transport_full ?? 0;
        $tunjanganKonsumsi = $penggajian->tunjangan_konsumsi ?? 0;
        $tunjanganKonsumsi_full = $penggajian->tunjangan_konsumsi_full ?? 0;
        $tunjanganJabatan = $penggajian->tunjangan_jabatan ?? 0;
        $totalTunjangan = $tunjanganTransport + $tunjanganKonsumsi + $tunjanganJabatan;
        // Prorata info
        $slipTotalAlpha = $penggajian->total_alpha ?? 0;
        $slipTotalHadir = $penggajian->total_hari_hadir ?? 0;
        $slipHariKerja = $slipTotalHadir + $slipTotalAlpha;
        
        $jumlahPendapatan = $gajiProduksi + $totalTunjangan;
        
        $asuransi = $penggajian->asuransi ?? 0;
        $potongan = $penggajian->potongan ?? 0;
        $jumlahPotongan = $asuransi + $potongan;
        
        $gajiDiterima = $penggajian->total_gaji ?? ($jumlahPendapatan - $jumlahPotongan);
        
        // Detail produksi
        $jumlahProduk = $penggajian->total_produk_bulan ?? 0;
        $tarifProduk = $penggajian->tarif_produk ?? 0;
        
        // Metode pembayaran
        $metodeBayar = $penggajian->metode_pembayaran ?? 'transfer_bank';
        if ($metodeBayar === 'kas' || $metodeBayar === 'cash') {
            $metodeBayarText = 'Tunai';
        } else {
            $metodeBayarText = ucwords(str_replace('_', ' ', $metodeBayar));
        }
    @endphp

    <div class="print-button">
        <button onclick="window.print()">
            🖨️ Cetak Slip Gaji
        </button>
    </div>

    <div class="slip-container">
        <!-- Header Perusahaan -->
        <div class="header">
            <div class="company-name">
                {{ $perusahaan->nama ?? 'PT. PERUSAHAAN ANDA' }}
            </div>
            <div class="slip-main-title">SLIP GAJI</div>
            <div class="company-address">
                Periode: {{ $bulan }} {{ $tahun }}
            </div>
        </div>

        <!-- Judul Slip Gaji -->
        <div class="slip-title" style="display: none;">
            SLIP GAJI {{ $bulan }} {{ $tahun }}
        </div>

        <!-- Informasi Pegawai -->
        <div class="employee-info">
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{ $penggajian->pegawai->nama }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $penggajian->pegawai->kode_pegawai ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Kualifikasi</td>
                    <td>:</td>
                    <td>{{ $penggajian->pegawai->jabatanRelasi->nama ?? $penggajian->pegawai->jabatan ?? 'Staff' }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>{{ strtoupper($penggajian->pegawai->kategori ?? $penggajian->pegawai->jenis_pegawai ?? '-') }}</td>
                </tr>
            </table>
        </div>

        <!-- Rincian Gaji -->
        <div class="salary-details">
            <table>
                <!-- Gaji Produksi -->
                <tr>
                    <td class="label-col">Gaji Produksi</td>
                    <td class="amount-col">{{ number_format($gajiProduksi, 0, ',', '.') }}</td>
                </tr>
                @if($jumlahProduk > 0 && $tarifProduk > 0)
                <tr>
                    <td class="detail-text" colspan="2">
                        {{ number_format($jumlahProduk, 0, ',', '.') }} produk × Rp {{ number_format($tarifProduk, 0, ',', '.') }}
                    </td>
                </tr>
                @endif

                <!-- Tunjangan -->
                @if($tunjanganTransport > 0)
                <tr>
                    <td class="label-col">Tunjangan Transport
                        @if($tunjanganTransportFull > 0 && $slipTotalAlpha > 0 && $slipHariKerja > 0)
                            <br><small style="color:#dc3545;font-size:0.7em;">{{ number_format($tunjanganTransportFull, 0, ',', '.') }} x ({{ $slipTotalHadir }}/{{ $slipHariKerja }} hari) -- dipotong karena {{ $slipTotalAlpha }} hari alpa</small>
                        @endif
                    </td>
                    <td class="amount-col underline">{{ number_format($tunjanganTransport, 0, ',', '.') }}+</td>
                </tr>
                @endif

                @if($tunjanganKonsumsi > 0)
                <tr>
                    <td class="label-col">Tunjangan Konsumsi
                        @if($tunjanganKonsumsi_full > 0 && $slipTotalAlpha > 0 && $slipHariKerja > 0)
                            <br><small style="color:#dc3545;font-size:0.7em;">{{ number_format($tunjanganKonsumsi_full, 0, ',', '.') }} x ({{ $slipTotalHadir }}/{{ $slipHariKerja }} hari) -- dipotong karena {{ $slipTotalAlpha }} hari alpa</small>
                        @endif
                    </td>
                    <td class="amount-col underline">{{ number_format($tunjanganKonsumsi, 0, ',', '.') }}+</td>
                </tr>
                @endif

                @if($tunjanganJabatan > 0)
                <tr>
                    <td class="label-col">Tunjangan Jabatan</td>
                    <td class="amount-col underline">{{ number_format($tunjanganJabatan, 0, ',', '.') }}+</td>
                </tr>
                @endif

                <!-- Jumlah Pendapatan -->
                <tr class="subtotal-row">
                    <td class="label-col">Jumlah Pendapatan</td>
                    <td class="amount-col">{{ number_format($jumlahPendapatan, 0, ',', '.') }}</td>
                </tr>

                <!-- Potongan -->
                @if($asuransi > 0)
                <tr>
                    <td class="label-col">BPJS / Asuransi</td>
                    <td class="amount-col underline">{{ number_format($asuransi, 0, ',', '.') }}+</td>
                </tr>
                @endif

                @if($potongan > 0)
                <tr>
                    <td class="label-col">Potongan Lainnya</td>
                    <td class="amount-col underline">{{ number_format($potongan, 0, ',', '.') }}+</td>
                </tr>
                @endif

                <!-- Jumlah Potongan -->
                @if($jumlahPotongan > 0)
                <tr class="subtotal-row">
                    <td class="label-col">Jumlah Potongan</td>
                    <td class="amount-col">{{ number_format($jumlahPotongan, 0, ',', '.') }}-</td>
                </tr>
                @endif

                <!-- Gaji Diterima -->
                <tr class="total-row">
                    <td class="label-col">Gaji Diterima</td>
                    <td class="amount-col">{{ number_format($gajiDiterima, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Metode Pembayaran -->
        <div class="payment-method">
            <div style="margin-bottom: 5px;">Metode Pembayaran: <strong>{{ $metodeBayarText }}</strong></div>
            @if(in_array($metodeBayar, ['transfer_bank', 'transfer', 'bank']))
            <div style="margin-top: 5px; padding: 10px; background: #f8f9fa; border: 1px dashed #ccc; border-radius: 4px;">
                <strong style="display: block; margin-bottom: 3px;">Informasi Rekening Tujuan:</strong>
                Bank: {{ $penggajian->pegawai->bank ?? '-' }} <br>
                No. Rekening: {{ $penggajian->pegawai->nomor_rekening ?? '-' }} <br>
                Atas Nama: {{ $penggajian->pegawai->nama_rekening ?? '-' }}
            </div>
            @endif
        </div>

        <!-- Tanda Tangan -->
        <div class="signature">
            <div class="signature-date">
                {{ $perusahaan->kota ?? 'Kota' }}, {{ now()->format('d F Y') }}
            </div>
            <div class="signature-name">
                {{ $perusahaan->nama_hr ?? 'Manajer HR' }}
            </div>
            <div class="signature-title">
                ({{ $perusahaan->jabatan_hr ?? 'Manajer HR' }})
            </div>
        </div>
    </div>
</body>
</html>

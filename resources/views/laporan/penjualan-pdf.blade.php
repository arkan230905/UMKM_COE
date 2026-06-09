<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            background-color: white;
            padding: 20px;
        }
        
        /* Header Styling */
        .header-container {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-container table {
            width: 100%;
            border: none;
            margin: 0;
        }
        .header-container td {
            border: none;
            padding: 0;
        }
        .company-info {
            text-align: left;
        }
        .report-info {
            text-align: right;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 10px;
            color: #555;
            margin-top: 5px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .report-period {
            font-size: 11px;
            color: #666;
        }
        
        /* Summary Section */
        .summary-box {
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .summary-box th, .summary-box td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-box th {
            background-color: #f8f9fa;
            font-size: 10px;
            color: #555;
            text-transform: uppercase;
        }
        .summary-box td {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .val-danger { color: #dc2626 !important; }
        .val-success { color: #16a34a !important; }
        
        /* Section Title */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px solid #1e3a8a;
            padding-bottom: 5px;
            margin-bottom: 10px;
            margin-top: 20px;
            text-transform: uppercase;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 10px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: white;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #94a3b8;
        }
        
        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }
        .bg-cash { background-color: #16a34a; }
        .bg-transfer { background-color: #2563eb; }
        .bg-credit { background-color: #dc2626; }
        .bg-default { background-color: #64748b; }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    @php
        $perusahaan = \App\Models\Perusahaan::where('user_id', auth()->id())->first();
        $namaPerusahaan = $perusahaan ? $perusahaan->nama : 'UMKM COE';
        $alamatPerusahaan = $perusahaan ? $perusahaan->alamat : 'Sistem Informasi Manufaktur';
        $teleponPerusahaan = $perusahaan ? $perusahaan->telepon : '';
    @endphp

    <!-- Header -->
    <div class="header-container">
        <table>
            <tr>
                <td class="company-info" width="60%">
                    <div class="company-name">{{ strtoupper($namaPerusahaan) }}</div>
                    <div class="company-address">
                        {{ $alamatPerusahaan }}<br>
                        @if($teleponPerusahaan) Telp: {{ $teleponPerusahaan }} @endif
                    </div>
                </td>
                <td class="report-info" width="40%">
                    <div class="report-title">Laporan Penjualan</div>
                    <div class="report-period">
                        Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') }}
                    </div>
                    @if($metodePembayaran)
                        <div class="report-period">
                            Metode: {{ strtoupper($metodePembayaran) }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="section-title">Ringkasan Keuangan</div>
    <div class="summary-box">
        <table>
            <tr>
                <th>Penjualan Produk</th>
                <th>Total Ongkir</th>
                <th>PPN (11%)</th>
                <th>Total Diskon</th>
                <th>Pendapatan Bersih</th>
            </tr>
            <tr>
                <td>Rp {{ number_format($summaryData['total_penjualan_produk'] ?? 0, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($summaryData['total_ongkir'] ?? 0, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($summaryData['total_ppn'] ?? 0, 0, ',', '.') }}</td>
                <td class="val-danger">-Rp {{ number_format($summaryData['total_diskon'] ?? 0, 0, ',', '.') }}</td>
                <td class="val-success">Rp {{ number_format($summaryData['total_pendapatan_bersih'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Detail Transaksi -->
    <div class="section-title">Detail Transaksi Penjualan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">No. Transaksi</th>
                <th width="10%">Tanggal</th>
                <th width="10%">Pembayaran</th>
                <th width="28%">Produk</th>
                <th width="8%">Qty</th>
                <th width="14%">Harga/Satuan</th>
                <th width="14%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penjualans as $key => $penjualan)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td class="text-center"><strong>{{ $penjualan->nomor_penjualan ?? '-' }}</strong></td>
                    <td class="text-center">{{ optional($penjualan->tanggal)->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-center">
                        @switch($penjualan->payment_method ?? '')
                            @case('cash')
                                <span class="badge bg-cash">TUNAI</span>
                                @break
                            @case('transfer')
                                <span class="badge bg-transfer">TRANSFER</span>
                                @break
                            @case('credit')
                                <span class="badge bg-credit">KREDIT</span>
                                @break
                            @default
                                <span class="badge bg-default">LAINNYA</span>
                        @endswitch
                    </td>
                    <td>
                        @php $detailCount = $penjualan->details->count(); @endphp
                        @if($detailCount > 1)
                            @foreach($penjualan->details as $d)
                                <div style="margin-bottom: 2px;">• {{ $d->produk->nama_produk ?? '-' }}</div>
                            @endforeach
                        @elseif($detailCount === 1)
                            {{ $penjualan->details[0]->produk->nama_produk ?? '-' }}
                        @else
                            {{ $penjualan->produk?->nama_produk ?? '-' }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($detailCount > 1)
                            <strong>{{ rtrim(rtrim(number_format($penjualan->details->sum('jumlah'), 2, ',', '.'), '0'), ',') }}</strong>
                        @elseif($detailCount === 1)
                            {{ rtrim(rtrim(number_format($penjualan->details[0]->jumlah ?? 0, 2, ',', '.'), '0'), ',') }}
                        @else
                            {{ rtrim(rtrim(number_format($penjualan->jumlah ?? 0, 2, ',', '.'), '0'), ',') }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($detailCount > 1)
                            @php $avgHarga = $penjualan->details->count() > 0 ? round($penjualan->details->sum(function($d) { return $d->harga_satuan * $d->jumlah; }) / $penjualan->details->sum('jumlah')) : 0; @endphp
                            Rp {{ number_format($avgHarga, 0, ',', '.') }}
                        @elseif($detailCount === 1)
                            Rp {{ number_format($penjualan->details[0]->harga_satuan ?? 0, 0, ',', '.') }}
                        @else
                            @php
                                $hdrHarga = $penjualan->harga_satuan;
                                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                }
                            @endphp
                            Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">
                        <strong>Rp {{ number_format($penjualan->total ?? 0, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #666;">
                        <em>Tidak ada data transaksi penjualan pada periode ini.</em>
                    </td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL PENJUALAN BERSIH</td>
                <td class="text-right val-success">Rp {{ number_format($summaryData['total_pendapatan_bersih'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Retur Section if data exists -->
    @if(($returData['total_retur'] ?? 0) > 0)
        <div class="section-title">Ringkasan Retur Penjualan</div>
        <div class="summary-box">
            <table>
                <tr>
                    <th>Total Transaksi Retur</th>
                    <th>Refund</th>
                    <th>Tukar Barang</th>
                    <th>Total Nilai Retur</th>
                </tr>
                <tr>
                    <td>{{ $returData['total_retur'] }}</td>
                    <td>{{ $returData['total_refund'] }}</td>
                    <td>{{ $returData['total_tukar_barang'] }}</td>
                    <td class="val-danger">Rp {{ number_format($returData['total_nilai_retur'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Dicetak oleh: {{ auth()->user()->name ?? 'Administrator' }} | 
        Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}<br>
        Dokumen ini dibuat otomatis oleh Sistem Informasi UMKM COE.
    </div>
</body>
</html>

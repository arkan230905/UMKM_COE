<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Calibri', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #2c3e50;
            background-color: #f8f9fa;
        }
        .page {
            background-color: white;
            margin: 10px;
            padding: 25px;
            page-break-after: always;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        /* Header Styling */
        .header {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #8B7355 0%, #6a5844 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(139, 115, 85, 0.2);
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .report-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            border-bottom: 2px solid rgba(255,255,255,0.5);
            padding-bottom: 8px;
        }
        .report-period {
            font-size: 11px;
            opacity: 0.95;
            margin: 3px 0;
        }
        
        /* Summary Section - Cards Style */
        .summary-section {
            margin-bottom: 25px;
        }
        .summary-header {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 3px solid #8B7355;
            display: inline-block;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        .summary-item {
            display: table-cell;
            width: 33.33%;
            padding: 12px;
            border-left: 4px solid #8B7355;
            background-color: #f8f9fa;
            margin-right: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .summary-item:nth-child(2) {
            border-left-color: #3b82f6;
        }
        .summary-item:nth-child(3) {
            border-left-color: #10b981;
        }
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }
        
        /* Section Title */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: white;
            background-color: #8B7355;
            margin-bottom: 12px;
            margin-top: 18px;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.summary-table {
            margin-top: 10px;
        }
        th {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            color: white;
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            border: 1px solid #e5e7eb;
            padding: 9px;
            font-size: 10px;
        }
        tbody tr {
            transition: background-color 0.2s;
        }
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .total-row {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            font-weight: bold;
            border-top: 2px solid #8B7355;
        }
        .total-row td {
            padding: 12px 9px;
            border-color: #8B7355;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-cash {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-transfer {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-credit {
            background-color: #fecaca;
            color: #7f1d1d;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
        .footer-line {
            margin: 3px 0;
        }
        
        .color-success { color: #10b981; font-weight: bold; }
        .color-danger { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="company-name">📊 UMKM COE</div>
            <div class="report-title">LAPORAN PENJUALAN</div>
            <div class="report-period">
                Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}
            </div>
            @if($metodePembayaran)
                <div class="report-period">
                    Metode: 
                    @switch($metodePembayaran)
                        @case('cash') 💵 Tunai @break
                        @case('transfer') 🏦 Transfer @break
                        @case('credit') 📝 Kredit @break
                    @endswitch
                </div>
            @endif
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="section-title">📈 RINGKASAN PENJUALAN</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">💰 Total Penjualan Produk</div>
                    <div class="summary-value">Rp {{ number_format($summaryData['total_penjualan_produk'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">🚚 Total Ongkir</div>
                    <div class="summary-value">Rp {{ number_format($summaryData['total_ongkir'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">📌 Total PPN (11%)</div>
                    <div class="summary-value">Rp {{ number_format($summaryData['total_ppn'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">💹 Total Pendapatan Kotor</div>
                    <div class="summary-value">Rp {{ number_format($summaryData['total_pendapatan_kotor'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">🏷️ Total Diskon</div>
                    <div class="summary-value color-danger">Rp {{ number_format($summaryData['total_diskon'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">✅ Total Pendapatan Bersih</div>
                    <div class="summary-value color-success">Rp {{ number_format($summaryData['total_pendapatan_bersih'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Detail Transaksi -->
        <div class="section-title">🛒 DETAIL TRANSAKSI PENJUALAN</div>
        <table>
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="11%">No. Transaksi</th>
                    <th width="10%">Tanggal</th>
                    <th width="10%">Pembayaran</th>
                    <th width="28%">Produk</th>
                    <th width="7%" class="text-right">Qty</th>
                    <th width="12%" class="text-right">Harga/Satuan</th>
                    <th width="14%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penjualans as $key => $penjualan)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td><strong>{{ $penjualan->nomor_penjualan ?? '-' }}</strong></td>
                        <td>{{ optional($penjualan->tanggal)->format('d-m-Y') ?? '-' }}</td>
                        <td class="text-center">
                            @switch($penjualan->payment_method ?? '')
                                @case('cash')
                                    <span class="badge badge-cash">Tunai</span>
                                    @break
                                @case('transfer')
                                    <span class="badge badge-transfer">Transfer</span>
                                    @break
                                @case('credit')
                                    <span class="badge badge-credit">Kredit</span>
                                    @break
                                @default
                                    <span class="badge" style="background: #f3f4f6; color: #6b7280;">Lainnya</span>
                            @endswitch
                        </td>
                        <td>
                            @php $detailCount = $penjualan->details->count(); @endphp
                            @if($detailCount > 1)
                                @foreach($penjualan->details as $d)
                                    <div style="margin-bottom: 3px;">{{ $d->produk->nama_produk ?? '-' }}</div>
                                @endforeach
                            @elseif($detailCount === 1)
                                {{ $penjualan->details[0]->produk->nama_produk ?? '-' }}
                            @else
                                {{ $penjualan->produk?->nama_produk ?? '-' }}
                            @endif
                        </td>
                        <td class="text-right">
                            @php $detailCount = $penjualan->details->count(); @endphp
                            @if($detailCount > 1)
                                <strong>{{ $penjualan->details->sum('jumlah') }}</strong>
                            @elseif($detailCount === 1)
                                {{ $penjualan->details[0]->jumlah ?? 0 }}
                            @else
                                {{ $penjualan->jumlah ?? 0 }}
                            @endif
                        </td>
                        <td class="text-right">
                            @php $detailCount = $penjualan->details->count(); @endphp
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
                        <td class="text-right bold">
                            Rp {{ number_format($penjualan->total ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #9ca3af;">📭 Tidak ada data transaksi penjualan</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="7" class="text-right">📊 TOTAL PENJUALAN BERSIH</td>
                    <td class="text-right" style="color: #10b981;">Rp {{ number_format($summaryData['total_pendapatan_bersih'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Retur Section if data exists -->
        @if($returData['total_retur'] > 0)
            <div class="section-title">↩️ RINGKASAN RETUR PENJUALAN</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Retur</div>
                    <div class="summary-value">{{ $returData['total_retur'] }} transaksi</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Nilai Retur</div>
                    <div class="summary-value color-danger">Rp {{ number_format($returData['total_nilai_retur'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Refund vs Tukar Barang</div>
                    <div class="summary-value">{{ $returData['total_refund'] }} | {{ $returData['total_tukar_barang'] }}</div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-line">Dicetak pada: {{ now()->format('d M Y H:i:s') }}</div>
            <div class="footer-line" style="margin-top: 8px;">© 2024 UMKM COE - Sistem Informasi Manufaktur</div>
        </div>
    </div>
</body>
</html>

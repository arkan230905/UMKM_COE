<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Harga Pokok Produksi - {{ $bom->produk->nama_produk }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4; margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .report-title { font-size: 16px; margin: 10px 0; font-weight: bold; }
        .table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; }
        .table th, .table td { padding: 6px 8px; border: 1px solid #dee2e6; }
        .table thead th { background-color: #f8f9fa; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .section-title { background-color: #e9ecef; padding: 8px; margin: 15px 0 10px 0; font-weight: bold; }
        .table-warning { background-color: #fff3cd; }
        .table-info { background-color: #cff4fc; }
        .table-success { background-color: #d1e7dd; }
        .small { font-size: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = public_path('Images/logo.png');
            $logoUrl = asset('Images/logo.png');
        @endphp
        @if(file_exists($logoPath))
            <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 60px;">
        @else
            <div class="company-name">UMKM COE</div>
        @endif
        <div class="report-title">LAPORAN HARGA POKOK PRODUKSI PER PRODUK - PROCESS COSTING</div>
        <div>Tanggal Cetak: {{ now()->format('d F Y H:i') }}</div>
    </div>

    <!-- Info Produk -->
    <table class="table" style="width: 50%;">
        <tr><th width="40%">Nama Produk</th><td>{{ $bom->produk->nama_produk }}</td></tr>
        <tr><th>Periode</th><td>{{ $bom->periode ?? '-' }}</td></tr>
        <tr><th>Tanggal Dibuat</th><td>{{ $bom->created_at->format('d F Y') }}</td></tr>
    </table>

    <!-- Section 1: BBB -->
    <div class="section-title">1. BIAYA BAHAN BAKU (BBB)</div>
    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Bahan Baku</th>
                <th width="12%" class="text-end">Jumlah</th>
                <th width="8%" class="text-center">Satuan</th>
                <th width="18%" class="text-end">Harga Satuan</th>
                <th width="18%" class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $totalBBB = 0; @endphp
            @foreach($bom->details as $detail)
                @php $totalBBB += $detail->total_harga; @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $detail->bahanBaku->nama_bahan ?? '-' }}</td>
                    <td class="text-end">{{ number_format($detail->jumlah, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $detail->satuan }}</td>
                    <td class="text-end">Rp {{ number_format($detail->harga_per_satuan, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="table-warning">
                <td colspan="5" class="text-end fw-bold">Total BBB</td>
                <td class="text-end fw-bold">Rp {{ number_format($totalBBB, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2: Proses Produksi -->
    <div class="section-title">2. PROSES PRODUKSI (BTKL + BOP)</div>
    @if($bom->proses && $bom->proses->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Proses</th>
                    <th width="12%" class="text-end">Durasi</th>
                    <th width="8%" class="text-center">Satuan</th>
                    <th width="16%" class="text-end">BTKL</th>
                    <th width="16%" class="text-end">BOP</th>
                    <th width="16%" class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalBTKL = 0; $totalBOP = 0; @endphp
                @foreach($bom->proses as $proses)
                    @php 
                        $totalBTKL += $proses->biaya_btkl;
                        $totalBOP += $proses->biaya_bop;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $proses->urutan }}</td>
                        <td>
                            {{ $proses->prosesProduksi->nama_proses ?? '-' }}
                            <div class="small">(Rp {{ number_format($proses->prosesProduksi->tarif_btkl ?? 0, 0, ',', '.') }}/{{ $proses->satuan_durasi }})</div>
                        </td>
                        <td class="text-end">{{ number_format($proses->durasi, 2, ',', '.') }}</td>
                        <td class="text-center">{{ $proses->satuan_durasi }}</td>
                        <td class="text-end">Rp {{ number_format($proses->biaya_btkl, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($proses->biaya_bop, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($proses->biaya_btkl + $proses->biaya_bop, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="table-info">
                    <td colspan="4" class="text-end fw-bold">Total BTKL</td>
                    <td class="text-end fw-bold">Rp {{ number_format($totalBTKL, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
                <tr class="table-info">
                    <td colspan="5" class="text-end fw-bold">Total BOP</td>
                    <td class="text-end fw-bold">Rp {{ number_format($totalBOP, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @else
        <p><em>Menggunakan perhitungan persentase: BTKL 60%, BOP 40%</em></p>
        <table class="table" style="width: 50%;">
            <tr><td>BTKL (60%)</td><td class="text-end">Rp {{ number_format($bom->total_btkl, 0, ',', '.') }}</td></tr>
            <tr><td>BOP (40%)</td><td class="text-end">Rp {{ number_format($bom->total_bop, 0, ',', '.') }}</td></tr>
        </table>
        @php $totalBTKL = $bom->total_btkl; $totalBOP = $bom->total_bop; @endphp
    @endif

    <!-- Section 3: Ringkasan HPP -->
    <div class="section-title">3. RINGKASAN HARGA POKOK PRODUKSI (HPP)</div>
    @php
        $hpp = $totalBBB + $totalBTKL + $totalBOP;
        $persenBBB = $hpp > 0 ? ($totalBBB / $hpp) * 100 : 0;
        $persenBTKL = $hpp > 0 ? ($totalBTKL / $hpp) * 100 : 0;
        $persenBOP = $hpp > 0 ? ($totalBOP / $hpp) * 100 : 0;
    @endphp
    <table class="table" style="width: 70%;">
        <tr>
            <td width="50%">Total Biaya Bahan Baku (BBB)</td>
            <td class="text-end">Rp {{ number_format($totalBBB, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($persenBBB, 1, ',', '.') }}%</td>
        </tr>
        <tr>
            <td>Total Biaya Tenaga Kerja Langsung (BTKL)</td>
            <td class="text-end">Rp {{ number_format($totalBTKL, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($persenBTKL, 1, ',', '.') }}%</td>
        </tr>
        <tr>
            <td>Total Biaya Overhead Pabrik (BOP)</td>
            <td class="text-end">Rp {{ number_format($totalBOP, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($persenBOP, 1, ',', '.') }}%</td>
        </tr>
        <tr class="table-success fw-bold">
            <td>HARGA POKOK PRODUKSI (HPP)</td>
            <td class="text-end">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
            <td class="text-end">100%</td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <div style="margin-top: 40px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <p>Mengetahui,</p>
                    <br><br><br>
                    <p>_________________________</p>
                    <p>Manager Produksi</p>
                </td>
                <td style="width: 50%; text-align: center;">
                    <p>{{ date('d F Y') }}</p>
                    <br><br><br>
                    <p>_________________________</p>
                    <p>Pembuat</p>
                </td>
            </tr>
        </table>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>

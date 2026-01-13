<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>HPP - {{ $produk->nama_produk }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .info-box { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .info-item { flex: 1; }
        .info-item strong { display: block; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .section-title { background-color: #333; color: white; padding: 8px; margin: 15px 0 0 0; font-weight: bold; }
        .section-title.bbb { background-color: #0d6efd; }
        .section-title.btkl { background-color: #198754; }
        .section-title.bp { background-color: #0dcaf0; }
        .section-title.bop { background-color: #ffc107; color: #333; }
        .total-row { background-color: #f8f9fa; }
        .grand-total { background-color: #0d6efd; color: white; }
        .hpp-unit { background-color: #198754; color: white; font-size: 14px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()">🖨️ Cetak</button>
        <button onclick="window.close()">✕ Tutup</button>
    </div>

    <div class="header">
        <h1>HARGA POKOK PRODUKSI (HPP)</h1>
        <p>{{ $produk->nama_produk }}</p>
    </div>

    <div class="info-box">
        <div class="info-item"><strong>Produk</strong>{{ $produk->nama_produk }}</div>
        <div class="info-item"><strong>Kode</strong>{{ $produk->kode_produk }}</div>
        <div class="info-item"><strong>Jumlah per Batch</strong>{{ number_format($bom->jumlah_produk) }} pcs</div>
        <div class="info-item"><strong>HPP/Unit</strong>Rp {{ number_format($bom->hpp_per_unit, 0, ',', '.') }}</div>
    </div>

    <div class="section-title bbb">1. Biaya Bahan Baku (BBB)</div>
    <table>
        <thead><tr><th>#</th><th>Bahan Baku</th><th class="text-center">Jumlah</th><th class="text-center">Satuan</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @forelse($bom->detailBBB as $i => $d)
            <tr><td>{{ $i+1 }}</td><td>{{ $d->bahanBaku->nama_bahan ?? '-' }}</td><td class="text-center">{{ number_format($d->jumlah, 2) }}</td><td class="text-center">{{ $d->satuan }}</td><td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td><td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td></tr>
            @empty<tr><td colspan="6" class="text-center">-</td></tr>@endforelse
        </tbody>
        <tfoot><tr class="total-row"><td colspan="5" class="text-end fw-bold">Total BBB</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bbb, 0, ',', '.') }}</td></tr></tfoot>
    </table>

    <div class="section-title btkl">2. Biaya Tenaga Kerja Langsung (BTKL)</div>
    <table>
        <thead><tr><th>#</th><th>Proses</th><th class="text-center">Durasi (Jam)</th><th class="text-end">Tarif/Jam</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @forelse($bom->detailBTKL as $i => $d)
            <tr><td>{{ $i+1 }}</td><td>{{ $d->nama_proses }}</td><td class="text-center">{{ number_format($d->durasi_jam, 2) }}</td><td class="text-end">Rp {{ number_format($d->tarif_per_jam, 0, ',', '.') }}</td><td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td></tr>
            @empty<tr><td colspan="5" class="text-center">-</td></tr>@endforelse
        </tbody>
        <tfoot><tr class="total-row"><td colspan="4" class="text-end fw-bold">Total BTKL</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_btkl, 0, ',', '.') }}</td></tr></tfoot>
    </table>

    <div class="section-title bp">3. Bahan Penolong</div>
    <table>
        <thead><tr><th>#</th><th>Bahan</th><th class="text-center">Jumlah</th><th class="text-center">Satuan</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @forelse($bom->detailBahanPendukung as $i => $d)
            <tr><td>{{ $i+1 }}</td><td>{{ $d->bahanPendukung->nama_bahan ?? '-' }}</td><td class="text-center">{{ number_format($d->jumlah, 2) }}</td><td class="text-center">{{ $d->satuan }}</td><td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td><td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td></tr>
            @empty<tr><td colspan="6" class="text-center">-</td></tr>@endforelse
        </tbody>
        <tfoot><tr class="total-row"><td colspan="5" class="text-end fw-bold">Total Bahan Penolong</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bahan_pendukung, 0, ',', '.') }}</td></tr></tfoot>
    </table>

    <div class="section-title bop">4. Biaya Overhead Pabrik (BOP)</div>
    <table>
        <thead><tr><th>#</th><th>Komponen</th><th class="text-center">Jumlah</th><th class="text-end">Tarif</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @forelse($bom->detailBOP as $i => $d)
            <tr><td>{{ $i+1 }}</td><td>{{ $d->nama_bop }}</td><td class="text-center">{{ number_format($d->jumlah, 2) }}</td><td class="text-end">Rp {{ number_format($d->tarif, 0, ',', '.') }}</td><td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td></tr>
            @empty<tr><td colspan="5" class="text-center">-</td></tr>@endforelse
        </tbody>
        <tfoot><tr class="total-row"><td colspan="4" class="text-end fw-bold">Total BOP</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bop, 0, ',', '.') }}</td></tr></tfoot>
    </table>

    <div class="section-title">RINGKASAN HPP</div>
    <table>
        <tr><td width="70%">Total BBB</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bbb, 0, ',', '.') }}</td></tr>
        <tr><td>Total BTKL</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_btkl, 0, ',', '.') }}</td></tr>
        <tr><td>Total Bahan Penolong</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bahan_pendukung, 0, ',', '.') }}</td></tr>
        <tr><td>Total BOP</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bop, 0, ',', '.') }}</td></tr>
        <tr class="grand-total"><td class="fw-bold">TOTAL HPP ({{ number_format($bom->jumlah_produk) }} pcs)</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_hpp, 0, ',', '.') }}</td></tr>
        <tr class="hpp-unit"><td class="fw-bold">HPP PER UNIT</td><td class="text-end fw-bold">Rp {{ number_format($bom->hpp_per_unit, 0, ',', '.') }}</td></tr>
    </table>

    <div class="footer"><p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p></div>
</body>
</html>

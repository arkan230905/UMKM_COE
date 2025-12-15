<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Retur {{ $retur->nomor_retur }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        h2 { margin: 0 0 4px 0; font-size: 18px; }
        h4 { margin: 16px 0 8px 0; font-size: 14px; }
        .header { text-align: center; margin-bottom: 18px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .meta-table th { text-align: left; padding: 6px 8px; background: #e2e8f0; width: 25%; }
        .meta-table td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items-table th, .items-table td { border: 1px solid #94a3b8; padding: 6px 8px; }
        .items-table th { background: #e2e8f0; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 24px; font-size: 11px; text-align: right; color: #4b5563; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Detail Retur</h2>
        <div>No. Retur: <strong>{{ $retur->nomor_retur }}</strong></div>
        <div>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <th>Tanggal</th>
            <td>{{ optional($retur->tanggal_retur)->format('d M Y') ?? '-' }}</td>
            <th>Referensi</th>
            <td>{{ $retur->resolveReferensiNomor() }}</td>
        </tr>
        <tr>
            <th>Customer / Vendor</th>
            <td>{{ $retur->resolveCustomerName() }}</td>
            <th>Jenis Retur</th>
            <td>{{ $retur->jenis_retur ? ucwords(str_replace(['_', '-'], ' ', $retur->jenis_retur)) : '-' }}</td>
        </tr>
        <tr>
            <th>Kompensasi</th>
            <td>
                @php
                    $kompensasiRaw = $retur->tipe_kompensasi ?? $retur->kompensasi;
                    $kompensasiLabel = match ($kompensasiRaw) {
                        'barang' => 'Tukar Barang',
                        'uang' => 'Refund Uang',
                        'credit' => 'Credit Note',
                        'refund' => 'Refund',
                        default => $kompensasiRaw ? ucfirst($kompensasiRaw) : '-' ,
                    };
                @endphp
                {{ $kompensasiLabel }}
            </td>
            <th>Status</th>
            <td>{{ $retur->status ? ucwords(str_replace(['_', '-'], ' ', $retur->status)) : '-' }}</td>
        </tr>
    </table>

    @if($retur->alasan)
        <h4>Alasan Retur</h4>
        <p>{{ $retur->alasan }}</p>
    @endif

    @if($retur->keterangan)
        <h4>Keterangan</h4>
        <p>{{ $retur->keterangan }}</p>
    @endif

    <h4>Item yang Diretur</h4>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($retur->details as $detail)
                <tr>
                    <td>{{ $detail->item_nama }}</td>
                    <td class="text-center">
                        {{ rtrim(rtrim(number_format($detail->qty_display, 3, ',', '.'), '0'), ',') ?: '0' }}
                    </td>
                    <td class="text-right">Rp {{ number_format($detail->harga_display, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->calculateSubtotal(), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada item retur.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Total Retur</th>
                <th class="text-right">Rp {{ number_format($retur->calculateTotalNilai(), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    @if($retur->kompensasis && $retur->kompensasis->count())
        <h4>Ringkasan Kompensasi</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($retur->kompensasis as $kompensasi)
                    <tr>
                        <td class="text-center">{{ ucfirst($kompensasi->tipe_kompensasi) }}</td>
                        <td>{{ $kompensasi->item_nama ?? '-' }}</td>
                        <td class="text-center">{{ $kompensasi->qty ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($kompensasi->nilai_kompensasi ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak oleh: {{ auth()->user()->name ?? '-' }}
    </div>
</body>
</html>

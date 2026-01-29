@extends('layouts.pegawai-pembelian')

@section('content')
<style>
    .invoice-header {
        border-bottom: 2px solid #000;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    .invoice-title {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
    }
    .invoice-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .invoice-table th,
    .invoice-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }
    .invoice-table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    .invoice-table .text-right {
        text-align: right;
    }
    .invoice-table .text-center {
        text-align: center;
    }
    .invoice-footer {
        margin-top: 50px;
        text-align: center;
        font-size: 12px;
        color: #666;
    }
    @media print {
        .no-print {
            display: none;
        }
        body {
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid">
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak Invoice
        </button>
        <a href="{{ route('pegawai-pembelian.laporan.pembelian') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="invoice-box">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-title">
                INVOICE PEMBELIAN
            </div>
            <div class="text-center">
                <strong>No. {{ $pembelian->nomor_pembelian ?? str_pad($pembelian->id, 6, '0', STR_PAD_LEFT) }}</strong>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div>
                <strong>Tanggal:</strong><br>
                {{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d F Y') }}
            </div>
            <div class="text-center">
                <strong>Vendor:</strong><br>
                {{ $pembelian->vendor->nama_vendor }}
            </div>
            <div class="text-end">
                <strong>Status:</strong><br>
                @if($pembelian->payment_method === 'cash' || $pembelian->status === 'lunas')
                    <span class="badge bg-success">Lunas</span>
                @else
                    <span class="badge bg-warning">Belum Lunas</span>
                @endif
            </div>
        </div>

        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @if($pembelian->details && $pembelian->details->count() > 0)
                    @foreach($pembelian->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($detail->tipe_item === 'bahan_baku' && $detail->bahanBaku)
                                {{ $detail->bahanBaku->nama_bahan }}
                            @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung)
                                {{ $detail->bahanPendukung->nama_bahan }}
                            @elseif($detail->tipe_item === 'bahan_baku' && $detail->bahan_baku_id && !$detail->bahanBaku)
                                Bahan Baku (ID: {{ $detail->bahan_baku_id }})
                            @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahan_pendukung_id && !$detail->bahanPendukung)
                                Bahan Pendukung (ID: {{ $detail->bahan_pendukung_id }})
                            @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                {{ $detail->bahanPendukung->nama_bahan }}
                            @elseif($detail->bahan_baku_id && $detail->bahanBaku)
                                {{ $detail->bahanBaku->nama_bahan }}
                            @elseif($detail->bahan_pendukung_id)
                                Bahan Pendukung (ID: {{ $detail->bahan_pendukung_id }})
                            @elseif($detail->bahan_baku_id)
                                Bahan Baku (ID: {{ $detail->bahan_baku_id }})
                            @else
                                Item
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($detail->jumlah ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($detail->tipe_item === 'bahan_baku' && $detail->bahanBaku)
                                {{ $detail->bahanBaku->satuan->nama ?? 'unit' }}
                            @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung)
                                {{ $detail->bahanPendukung->satuanRelation->nama ?? 'unit' }}
                            @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                {{ $detail->bahanPendukung->satuanRelation->nama ?? 'unit' }}
                            @elseif($detail->bahan_baku_id && $detail->bahanBaku)
                                {{ $detail->bahanBaku->satuan->nama ?? 'unit' }}
                            @else
                                {{ $detail->satuan ?? 'unit' }}
                            @endif
                        </td>
                        <td class="text-right">Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">
                            <em>Tidak ada detail pembelian</em>
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Total:</th>
                    <th class="text-right">
                        @php
                            $totalHarga = $pembelian->total_harga ?? 0;
                            // Jika total_harga = 0, hitung dari details
                            if ($totalHarga == 0 && $pembelian->details && $pembelian->details->count() > 0) {
                                $totalHarga = $pembelian->details->sum(function($detail) {
                                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                });
                            }
                        @endphp
                        <strong>Rp {{ number_format($totalHarga, 0, ',', '.') }}</strong>
                    </th>
                </tr>
            </tfoot>
        </table>

        <!-- Payment Info -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h6>Informasi Pembayaran</h6>
                <p><strong>Metode Pembayaran:</strong> 
                    @php
                        $paymentMethod = $pembelian->payment_method ?? 'cash';
                        if ($paymentMethod === 'credit') {
                            $paymentText = 'Kredit';
                        } elseif ($paymentMethod === 'transfer') {
                            $paymentText = 'Transfer';
                        } else {
                            $paymentText = 'Tunai';
                        }
                    @endphp
                    {{ $paymentText }}
                </p>
                <p><strong>Terbayar:</strong> Rp {{ number_format($pembelian->terbayar ?? 0, 0, ',', '.') }}</p>
                <p><strong>Sisa Pembayaran:</strong> Rp {{ number_format($pembelian->sisa_pembayaran ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="col-md-6">
                <h6>Keterangan</h6>
                <p>{{ $pembelian->keterangan ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Invoice Footer -->
    <div class="invoice-footer">
        <p>Terima kasih atas kepercayaan Anda</p>
        <p>Invoice ini sah dan telah dibuat pada {{ date('d F Y') }}</p>
    </div>
</div>
@endsection

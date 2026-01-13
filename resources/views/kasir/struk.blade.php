@extends('layouts.kasir')

@section('title', 'Struk Penjualan')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body" id="struk">
                    <div class="text-center mb-4">
                        <h4>{{ config('app.name') }}</h4>
                        <p class="mb-1">Struk Penjualan</p>
                        <p class="mb-0">{{ $penjualan->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>

                    <hr>

                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>No. Transaksi:</strong></td>
                            <td>{{ $penjualan->nomor_penjualan }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kasir:</strong></td>
                            <td>{{ $penjualan->kasir_nama }}</td>
                        </tr>
                        <tr>
                            <td><strong>Metode Bayar:</strong></td>
                            <td>{{ ucfirst($penjualan->payment_method) }}</td>
                        </tr>
                    </table>

                    <hr>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualan->details as $detail)
                                <tr>
                                    <td>{{ $detail->produk->nama_produk ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $detail->jumlah }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="3">TOTAL:</th>
                                <th class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Bayar:</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($penjualan->bayar, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Kembalian:</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <hr>

                    <div class="text-center">
                        <p class="mb-1">Terima kasih atas kunjungan Anda!</p>
                        <small class="text-muted">Barang yang sudah dibeli tidak dapat dikembalikan</small>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Cetak Struk
                    </button>
                    <a href="{{ route('kasir.pos') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Transaksi Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .card-footer,
    .navbar,
    .btn {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
    
    #struk {
        font-size: 12px;
    }
}
</style>
@endsection
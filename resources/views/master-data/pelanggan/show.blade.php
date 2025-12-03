@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">
            <i class="bi bi-person-circle"></i> Detail Pelanggan
        </h2>
        <a href="{{ route('master-data.pelanggan.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person"></i> Informasi Pelanggan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-dark">
                        <tr>
                            <td width="120"><strong>Nama:</strong></td>
                            <td>{{ $pelanggan->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $pelanggan->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td>{{ $pelanggan->username }}</td>
                        </tr>
                        <tr>
                            <td><strong>No. Telepon:</strong></td>
                            <td>{{ $pelanggan->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terdaftar:</strong></td>
                            <td>{{ $pelanggan->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Pesanan:</strong></td>
                            <td><span class="badge bg-info">{{ $pelanggan->orders->count() }} Pesanan</span></td>
                        </tr>
                    </table>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('master-data.pelanggan.edit', $pelanggan->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Data
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cart-check"></i> Riwayat Pesanan (10 Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    @if($pelanggan->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Nomor Order</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status Pembayaran</th>
                                    <th>Status Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pelanggan->orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->nomor_order }}</strong>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                        <span class="badge bg-success">Lunas</span>
                                        @elseif($order->payment_status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @else
                                        <span class="badge bg-danger">Gagal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->status === 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                        @elseif($order->status === 'processing')
                                        <span class="badge bg-info">Diproses</span>
                                        @elseif($order->status === 'shipped')
                                        <span class="badge bg-primary">Dikirim</span>
                                        @else
                                        <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                        <p class="text-muted mt-2">Belum ada pesanan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

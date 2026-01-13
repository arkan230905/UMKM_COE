@extends('layouts.gudang')

@section('title', 'Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pembelian</h1>
        <a href="{{ route('gudang.pembelian.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Pembelian
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Tanggal</th>
                            <th>Vendor</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembelians as $pembelian)
                            <tr>
                                <td>{{ $pembelian->nomor_pembelian }}</td>
                                <td>{{ $pembelian->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $pembelian->vendor->nama_vendor ?? 'N/A' }}</td>
                                <td>Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
                                <td>
                                    @if($pembelian->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($pembelian->status == 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $pembelian->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('gudang.pembelian.show', $pembelian->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data pembelian</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $pembelians->links() }}
        </div>
    </div>
</div>
@endsection
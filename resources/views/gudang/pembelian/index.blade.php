@extends('layouts.gudang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daftar Pembelian</h2>
    <a href="{{ route('gudang.pembelian.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Input Pembelian
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>No. Pembelian</th>
                    <th>Tanggal</th>
                    <th>Vendor</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembelians as $p)
                <tr>
                    <td><code>{{ $p->nomor_pembelian }}</code></td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $p->vendor->nama_vendor ?? $p->vendor->nama ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format($p->total_harga ?? $p->total ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge bg-success">{{ ucfirst($p->status) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('gudang.pembelian.show', $p->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $pembelians->links() }}
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-users me-2"></i>Pelanggan
        </h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Pelanggan
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>No. Telepon</th>
                            <th>Total Pesanan</th>
                            <th>Terdaftar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pelanggans as $pelanggan)
                            <tr>
                                <td class="text-center">{{ ($pelanggans->currentPage() - 1) * $pelanggans->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $pelanggan->name }}</div>
                                            <small class="text-muted">ID: {{ $pelanggan->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $pelanggan->email ?? '-' }}</td>
                                <td>{{ $pelanggan->username ?? '-' }}</td>
                                <td>{{ $pelanggan->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $pelanggan->orders_count ?? 0 }}</span>
                                </td>
                                <td>{{ $pelanggan->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.pelanggan.edit', $pelanggan->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.pelanggan.destroy', $pelanggan->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pelanggan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                {{ $pelanggans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

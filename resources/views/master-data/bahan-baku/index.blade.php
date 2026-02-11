@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2"></i>Bahan Baku
        </h2>
        <a href="{{ route('master-data.bahan-baku.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Bahan Baku
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Bahan Baku
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Satuan Utama</th>
                            <th class="text-end">Harga Satuan Utama</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanBaku as $bahan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-box text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $bahan->nama_bahan }}</div>
                                            <small class="text-muted">ID: {{ $bahan->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($bahan->satuan)
                                        <span class="badge bg-info">{{ $bahan->satuan->nama }}</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp {{ number_format($bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.bahan-baku.show', $bahan->id) }}" class="btn btn-outline-info" title="Detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <form action="{{ route('master-data.bahan-baku.destroy', $bahan->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus bahan baku \'{{ $bahan->nama_bahan }}\'?\n\nPerhatian: Data tidak dapat dihapus jika masih digunakan di BOM, Pembelian, atau Produksi.')" title="Hapus">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data bahan baku</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

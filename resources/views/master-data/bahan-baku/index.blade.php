@extends('layouts.app')

@section('title', 'Daftar Bahan Baku')

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
                            <th class="text-center">Nama Bahan</th>
                            <th class="text-center">Satuan Utama</th>
                            <th class="text-center">Harga Satuan Utama</th>
                            <th class="text-center">Stok Saat Ini</th>
                            <th class="text-center">Stok Minimum</th>
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
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($bahan->satuan)
                                        {{ $bahan->satuan->nama }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center fw-semibold">
                                    Rp {{ number_format($bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold text-primary">
                                        {{ rtrim(rtrim(number_format($bahan->stok_real_time ?? 0, 2, ',', '.'), '0'), ',') }}
                                    </div>
                                    <small class="text-muted">
                                        @if($bahan->satuan)
                                            {{ $bahan->satuan->nama }}
                                        @else
                                            Unit
                                        @endif
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold {{ ($bahan->stok_real_time ?? 0) <= ($bahan->stok_minimum ?? 0) ? 'text-danger' : 'text-success' }}">
                                        {{ rtrim(rtrim(number_format($bahan->stok_minimum ?? 0, 2, ',', '.'), '0'), ',') }}
                                    </div>
                                    <small class="text-muted">
                                        @if($bahan->satuan)
                                            {{ $bahan->satuan->nama }}
                                        @else
                                            Unit
                                        @endif
                                    </small>
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
                                <td colspan="6" class="text-center py-4">
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

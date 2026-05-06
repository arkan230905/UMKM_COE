@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Kategori Produk</h1>
            <p class="text-muted mb-0">Kelola kategori untuk produk</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Produk
            </a>
            <a href="{{ route('master-data.kategori-produk.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Kategori
            </a>
        </div>
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

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">NO</th>
                        <th width="15%">Kode Kategori</th>
                        <th width="25%">Nama Kategori</th>
                        <th width="30%">Deskripsi</th>
                        <th width="15%" class="text-center">Jumlah Produk</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $i => $k)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="badge bg-secondary">{{ $k->kode_kategori ?? '-' }}</span></td>
                        <td><strong>{{ $k->nama }}</strong></td>
                        <td>{{ $k->deskripsi ?? '-' }}</td>
                        <td class="text-center">
                            <span class="text-info fw-semibold">{{ $k->produks_count }} produk</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('master-data.kategori-produk.edit', $k->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($k->produks_count == 0)
                            <form action="{{ route('master-data.kategori-produk.destroy', $k->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada kategori</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

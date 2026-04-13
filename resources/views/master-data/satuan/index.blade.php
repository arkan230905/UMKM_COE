@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-end align-items-center mb-4">
        <div>
            <a href="{{ route('master-data.satuan.create') }}" class="btn" style="background-color: #8B735C !important; color: white !important; border: none !important; border-radius: 12px !important; padding: 12px 24px !important; font-weight: 600 !important; min-height: 44px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;">
                <i class="fas fa-plus me-2"></i>Tambah Satuan
            </a>
        </div>
    </div>

    <style>
        #tambah-satuan-btn {
            background-color: #8B735C !important;
            color: white !important;
            border: none !important;
        }
        #tambah-satuan-btn:hover {
            background-color: #A68D73 !important;
            color: white !important;
        }
    </style>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-1">
                <i class="fas fa-ruler me-2"></i>Daftar Satuan
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th class="text-center" style="width: 120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($satuans as $index => $item)
                        <tr>
                            <td class="text-center text-muted">{{ ($satuans->currentPage() - 1) * $satuans->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-ruler text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $item->kode }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->nama }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master-data.satuan.edit', $item->id) }}"
                                       class="btn btn-outline-primary"
                                       data-bs-toggle="tooltip"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('master-data.satuan.destroy', $item->id) }}" method="POST" class="d-inline delete-form" data-satuan-nama="{{ $item->nama }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger delete-btn"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-ruler display-6 d-block mb-2"></i>
                                    Tidak ada data satuan yang ditemukan.
                                </div>
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

@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #1b1b28; min-height: 100vh;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-box-seam me-2"></i> Data Produk
        </h2>
        <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Produk
        </a>
    </div>

    <!-- Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success text-dark fw-semibold shadow-sm mx-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Card Tabel -->
    <div class="card shadow-lg border-0 mx-3"
         style="background-color: #222232; border-radius: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
        <div class="card-body px-4 py-4">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0 custom-table">
                    <thead>
                        <tr>
                            <th class="ps-3 py-3">#</th>
                            <th>Nama Produk</th>
                            <th>Harga Jual</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                        <tr class="data-row">
                            <td class="ps-3 fw-bold text-light">{{ $produk->id }}</td>
                            <td class="fw-bold text-white">{{ $produk->nama_produk }}</td>
                            <td class="fw-semibold text-success">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('master-data.produk.edit', $produk->id) }}" 
                                   class="btn btn-sm btn-warning text-dark me-1 shadow-sm fw-semibold">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('master-data.produk.destroy', $produk->id) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger text-white shadow-sm fw-semibold">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada data produk
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Header tabel */
.custom-table thead th {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    font-size: 0.85rem;
    color: #e0e0ee;
    background: linear-gradient(180deg, #2a2a3a 0%, #232333 100%);
    border: none;
    padding: 14px 10px;
    border-radius: 12px;
}

/* Isi tabel */
.custom-table tbody td {
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    padding: 16px 14px !important;
    color: rgb(0, 0, 35) !important;
}

/* Baris tabel */
.data-row {
    background: linear-gradient(160deg, #242436, #1b1b2b) !important;
    border-radius: 14px !important;
    transition: all 0.25s ease !important;
    border-bottom: 2px solid rgba(255, 255, 255, 0.05); /* garis pemisah halus antar baris */
}

/* Hover efek lembut */
.data-row:hover {
    background: linear-gradient(160deg, #2f2f45, #23233a) !important;
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 15px rgba(0,0,0,0.4) !important;
}

/* Card tabel */
.card {
    background-color: #1b1b28 !important;
    border-radius: 18px !important;
    box-shadow: 0 8px 20px rgba(0,0,0,0.5) !important;
}

/* Tombol */
.btn {
    border-radius: 12px;
    font-weight: 600;
    transition: 0.2s ease;
}

.btn-warning {
    background-color: #f6c23e;
    border: none;
}

.btn-warning:hover {
    background-color: #e0ae2f;
}

.btn-danger {
    background-color: #e74a3b;
    border: none;
}

.btn-danger:hover {
    background-color: #c0392b;
}

/* Alert */
.alert {
    border-radius: 12px;
    font-size: 0.95rem;
}
</style>
@endsection

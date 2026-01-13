@extends('layouts.app')

@push('styles')
<style>
/* Bahan Pendukung page specific - BLACK text for kode and nama bahan */
.table tbody td:nth-child(1) {
    color: #333 !important; /* Kolom Kode */
    text-align: center !important;
    padding: 8px !important;
}
.table tbody td:nth-child(2) {
    color: #333 !important; /* Kolom Nama Bahan */
}
.table tbody td:nth-child(2) strong {
    color: #333 !important; /* Nama bahan yang bold */
}

/* Special styling for code column (1st column) - Purple rounded pill like bahan baku */
.table tbody td:nth-child(1) code {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: bold !important;
    padding: 8px 20px !important;
    border-radius: 25px !important;
    display: inline-block !important;
    min-width: 120px !important;
    text-align: center !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
    font-size: 14px !important;
    letter-spacing: 0.5px !important;
    border: none !important;
}

.table tbody tr:hover td:nth-child(1) code {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
    transform: scale(1.05) !important;
    transition: all 0.3s ease !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bahan Pendukung</h1>
        <a href="{{ route('master-data.bahan-pendukung.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Bahan Pendukung
        </a>
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
        <div class="card-header">
            <h5 class="mb-0">Daftar Bahan Pendukung</h5>
            <small class="text-muted">Bahan tidak langsung seperti gas, bumbu, minyak, listrik, dll</small>
        </div>
        <div class="card-body">
            <!-- Filter -->
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau kode..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('master-data.kategori-bahan-pendukung.index') }}" class="btn btn-outline-info w-100" title="Kelola Kategori">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover custom-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="ps-3 py-3" width="10%">Kode</th>
                            <th>Nama Bahan</th>
                            <th width="12%">Kategori</th>
                            <th class="text-end">Harga/Satuan</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanPendukungs as $bahan)
                            <tr>
                                <td><code>{{ $bahan->kode_bahan }}</code></td>
                                <td>
                                    <strong>{{ $bahan->nama_bahan }}</strong>
                                    @if($bahan->deskripsi)
                                        <br><small class="text-muted">{{ Str::limit($bahan->deskripsi, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $bahan->kategoriBahanPendukung->nama ?? ucfirst($bahan->kategori) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}<br>
                                    <small class="text-muted">per {{ $bahan->satuanRelation ? ($bahan->satuanRelation->kode . ' - ' . $bahan->satuanRelation->nama) : '-' }}</small>
                                </td>
                                <td class="text-center">
                                    {{ number_format($bahan->stok, 2) }} {{ $bahan->satuanRelation ? ($bahan->satuanRelation->kode . ' - ' . $bahan->satuanRelation->nama) : '-' }}<br>
                                    <small class="text-muted">Min: {{ number_format($bahan->stok_minimum, 2) }}</small>
                                </td>
                                <td class="text-center">
                                    @if($bahan->status_stok == 'Habis')
                                        <span class="badge bg-danger">Habis</span>
                                    @elseif($bahan->status_stok == 'Menipis')
                                        <span class="badge bg-warning">Menipis</span>
                                    @else
                                        <span class="badge bg-success">Aman</span>
                                    @endif
                                    <br>
                                    @if($bahan->is_active)
                                        <small class="text-success">Aktif</small>
                                    @else
                                        <small class="text-muted">Nonaktif</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('master-data.bahan-pendukung.edit', $bahan) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.bahan-pendukung.destroy', $bahan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus bahan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data bahan pendukung</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $bahanPendukungs->links() }}
        </div>
    </div>
</div>
@endsection

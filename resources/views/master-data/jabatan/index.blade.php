@extends('layouts.app')

@section('title', 'Kualifikasi Tenaga Kerja')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-briefcase me-2"></i>Kualifikasi Tenaga Kerja
        </h2>
        <a href="{{ route('master-data.kualifikasi-tenaga-kerja.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Kualifikasi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Kualifikasi Tenaga Kerja
            </h5>
            <form method="GET" class="row g-2 mt-3">
                <div class="col-md-3">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           class="form-control" 
                           placeholder="Cari nama kualifikasi...">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="btkl" {{ request('kategori') == 'btkl' ? 'selected' : '' }}>BTKL</option>
                        <option value="btktl" {{ request('kategori') == 'btktl' ? 'selected' : '' }}>BTKTL</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                    @if(request('search') || request('kategori'))
                        <a href="{{ route('master-data.kualifikasi-tenaga-kerja.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nama Kualifikasi</th>
                            <th>Kategori</th>
                            <th>Tunjangan</th>
                            <th>Asuransi</th>
                            <th>Gaji Pokok</th>
                            <th>Tarif/Jam</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jabatans as $i => $row)
                        <tr>
                            <td class="text-center">{{ ($jabatans->currentPage()-1)*$jabatans->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-briefcase text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $row->nama }}</div>
                                        <small class="text-muted">ID: {{ $row->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $row->kategori==='btkl'?'primary':'success' }}">
                                    {{ strtoupper($row->kategori) }}
                                </span>
                            </td>
                            <td class="fw-semibold">Rp {{ number_format($row->tunjangan,0,',','.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($row->asuransi,0,',','.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($row->gaji_pokok,0,',','.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($row->tarif_lembur,0,',','.') }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master-data.kualifikasi-tenaga-kerja.edit',$row->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('master-data.kualifikasi-tenaga-kerja.destroy',$row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kualifikasi ini?')">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data kualifikasi tenaga kerja</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($jabatans->hasPages())
        <div class="card-footer">
            {{ $jabatans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

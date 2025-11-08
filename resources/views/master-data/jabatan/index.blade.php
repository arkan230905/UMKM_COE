@extends('layouts.app')

@section('content')
<style>
    .card-footer nav svg { width: 14px; height: 14px; }
    .card-footer nav span, .card-footer nav a { font-size: 0.875rem; }
    .card-footer .pagination .page-link { padding: .25rem .5rem; }
</style>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-briefcase me-2"></i>Jabatan</h2>
        <a href="{{ route('master-data.jabatan.create') }}" class="btn btn-primary">Tambah Jabatan</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           class="form-control" 
                           placeholder="Cari nama jabatan..."
                           style="color: #000; background-color: #f8f9fa;">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select" onchange="this.form.submit()" style="color: #000; background-color: #f8f9fa;">
                        <option value="">Semua Kategori</option>
                        <option value="btkl" {{ request('kategori') == 'btkl' ? 'selected' : '' }}>BTKL</option>
                        <option value="btktl" {{ request('kategori') == 'btktl' ? 'selected' : '' }}>BTKTL</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    @if(request('search') || request('kategori'))
                        <a href="{{ route('master-data.jabatan.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Jabatan</th>
                            <th>Kategori</th>
                            <th>Tunjangan</th>
                            <th>Asuransi</th>
                            <th>Gaji</th>
                            <th>Tarif/Jam</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jabatans as $i => $row)
                        <tr>
                            <td>{{ ($jabatans->currentPage()-1)*$jabatans->perPage() + $loop->iteration }}</td>
                            <td>{{ $row->nama }}</td>
                            <td><span class="badge bg-{{ $row->kategori==='btkl'?'primary':'success' }}">{{ strtoupper($row->kategori) }}</span></td>
                            <td>Rp {{ number_format($row->tunjangan,0,',','.') }}</td>
                            <td>Rp {{ number_format($row->asuransi,0,',','.') }}</td>
                            <td>Rp {{ number_format($row->gaji,0,',','.') }}</td>
                            <td>Rp {{ number_format($row->tarif,0,',','.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('master-data.jabatan.edit',$row->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('master-data.jabatan.destroy',$row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jabatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($jabatans->hasPages())
        <div class="card-footer bg-white">
            {{ $jabatans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

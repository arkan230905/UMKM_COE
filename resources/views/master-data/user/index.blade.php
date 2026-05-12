@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-users me-2"></i>Manajemen User
        </h2>
        <a href="{{ route('master-data.user.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah User
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

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar User
            </h5>
            <form method="GET" class="row g-2 mt-3">
                <div class="col-md-4">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           class="form-control" 
                           placeholder="Cari nama user atau email...">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                        <option value="pelanggan" {{ request('role') == 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                        <option value="pegawai_pembelian" {{ request('role') == 'pegawai_pembelian' ? 'selected' : '' }}>Pegawai Gudang</option>
                        <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                    @if(request('search') || request('role'))
                        <a href="{{ route('master-data.user.index') }}" class="btn btn-outline-secondary">
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
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Perusahaan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $i => $row)
                        <tr>
                            <td class="text-center">{{ ($users->currentPage()-1)*$users->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $row->name }}</div>
                                        <small class="text-muted">ID: {{ $row->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $row->email }}</td>
                            <td>
                                <span class="badge bg-{{ $row->role === 'admin' ? 'danger' : ($row->role === 'owner' ? 'warning' : ($row->role === 'pegawai_pembelian' ? 'info' : ($row->role === 'kasir' ? 'success' : 'secondary')) }}">
                                    {{ ucwords($row->role) }}
                                </span>
                            </td>
                            <td>
                                @if($row->perusahaan_id)
                                    {{ $row->perusahaan->nama_perusahaan ?? 'Tidak Ada' }}
                                @else
                                    <span class="text-muted">Tidak Ada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row->email_verified_at)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-warning">Belum Aktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master-data.user.edit',$row->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('master-data.user.destroy',$row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
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
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data user</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>

@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Komponen BOP</h1>
        <a href="{{ route('master-data.komponen-bop.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Komponen
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
            <h5 class="mb-0">Daftar Komponen Biaya Overhead Pabrik (BOP)</h5>
            <small class="text-muted">Komponen biaya tidak langsung seperti listrik, gas, penyusutan mesin, dll</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="12%">Kode</th>
                            <th>Nama Komponen</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Tarif per Satuan</th>
                            <th class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($komponenBops as $komponen)
                            <tr>
                                <td><code>{{ $komponen->kode_komponen }}</code></td>
                                <td><strong>{{ $komponen->nama_komponen }}</strong></td>
                                <td class="text-center">{{ $komponen->satuan }}</td>
                                <td class="text-end">Rp {{ number_format($komponen->tarif_per_satuan, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($komponen->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('master-data.komponen-bop.edit', $komponen) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.komponen-bop.destroy', $komponen) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus komponen ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data komponen BOP</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $komponenBops->links() }}
        </div>
    </div>
</div>
@endsection

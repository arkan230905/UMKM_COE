@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs me-2"></i>BTKL
        </h2>
        <a href="{{ route('master-data.btkl.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah BTKL
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
                <i class="fas fa-list me-2"></i>Daftar BTKL (Biaya Tenaga Kerja Langsung)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Kode</th>
                            <th>Nama Proses</th>
                            <th class="text-end">Tarif BTKL</th>
                            <th class="text-center">Satuan</th>
                            <th>Komponen BOP Default</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prosesProduksis as $key => $proses)
                            <tr>
                                <td class="text-center">{{ ($prosesProduksis->currentPage() - 1) * $prosesProduksis->perPage() + $key + 1 }}</td>
                                <td><code>{{ $proses->kode_proses }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-cogs text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $proses->nama_proses }}</div>
                                            @if($proses->deskripsi)
                                                <small class="text-muted">{{ Str::limit($proses->deskripsi, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">Rp {{ number_format($proses->tarif_btkl, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $proses->satuan_btkl ?? '-' }}</td>
                                <td>
                                    @if($proses->prosesBops->count() > 0)
                                        @foreach($proses->prosesBops as $pb)
                                            <span class="badge bg-info me-1">
                                                {{ $pb->komponenBop->nama_komponen ?? '-' }} 
                                                ({{ $pb->kuantitas_default }} {{ $pb->komponenBop->satuan ?? '' }})
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($proses->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.btkl.edit', $proses) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.btkl.destroy', $proses) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus proses ini?')">
                                            @csrf
                                            @method('DELETE')
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
                                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data BTKL</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                {{ $prosesProduksis->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

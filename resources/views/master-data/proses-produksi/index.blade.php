@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Proses Produksi</h1>
        <a href="{{ route('master-data.proses-produksi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Proses
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
            <h5 class="mb-0">Daftar Proses Produksi (BTKL)</h5>
            <small class="text-muted">Setiap proses memiliki tarif Biaya Tenaga Kerja Langsung (BTKL) dan komponen BOP default</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="10%">Kode</th>
                            <th>Nama Proses</th>
                            <th class="text-end">Tarif BTKL</th>
                            <th class="text-center">Satuan</th>
                            <th>Komponen BOP Default</th>
                            <th class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prosesProduksis as $proses)
                            <tr>
                                <td><code>{{ $proses->kode_proses }}</code></td>
                                <td>
                                    <strong>{{ $proses->nama_proses }}</strong>
                                    @if($proses->deskripsi)
                                        <br><small class="text-muted">{{ Str::limit($proses->deskripsi, 50) }}</small>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($proses->tarif_btkl, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $proses->satuan_btkl }}</td>
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
                                    <div class="btn-group">
                                        <a href="{{ route('master-data.proses-produksi.edit', $proses) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.proses-produksi.destroy', $proses) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus proses ini?')">
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
                                <td colspan="7" class="text-center">Belum ada data proses produksi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $prosesProduksis->links() }}
        </div>
    </div>
</div>
@endsection

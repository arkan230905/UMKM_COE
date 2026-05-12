@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>BOP per Proses
        </h2>
        <div>
            <a href="{{ route('master-data.bop-proses.sync-kapasitas') }}" class="btn btn-warning me-2" 
               onclick="return confirm('Sync kapasitas dari BTKL untuk semua BOP?')">
                <i class="fas fa-sync me-2"></i>Sync Kapasitas
            </a>
            <a href="{{ route('master-data.bop-proses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah BOP Proses
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            {{ session('warning') }}
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar BOP (Biaya Overhead Pabrik) per Proses
                </h5>
                @if($prosesProduksis->total() > 0)
                    <small class="text-muted">Total: {{ $prosesProduksis->total() }} proses produksi</small>
                @endif
            </div>
            <div>
                @if($prosesProduksis->count() > 0)
                    <span class="badge bg-success">{{ $prosesProduksis->count() }} dari {{ $prosesProduksis->total() }}</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Kode Proses</th>
                            <th>Nama Proses</th>
                            <th class="text-end">Total BOP/Jam</th>
                            <th class="text-center">Kapasitas/Jam</th>
                            <th class="text-end">
                                BOP/Unit
                                <i class="fas fa-info-circle text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Dihitung dari: Total BOP per Jam ÷ Kapasitas per Jam"></i>
                            </th>
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
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-chart-pie text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $proses->nama_proses }}</div>
                                            @if($proses->deskripsi)
                                                <small class="text-muted">{{ Str::limit($proses->deskripsi, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    @if($proses->bopProses)
                                        <div class="fw-semibold text-warning">{{ $proses->bopProses->total_bop_per_jam_formatted }}</div>
                                        <small class="text-muted">per jam mesin</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $proses->kapasitas_per_jam ?? 0 }} unit/jam</span>
                                    @if($proses->bopProses && $proses->bopProses->kapasitas_per_jam != $proses->kapasitas_per_jam)
                                        <br><small class="text-danger">⚠️ Tidak sync</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($proses->bopProses && $proses->bopProses->bop_per_unit > 0)
                                        <div class="fw-semibold text-success">{{ $proses->bopProses->bop_per_unit_formatted }}</div>
                                        <small class="text-muted">per unit</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($proses->kapasitas_per_jam <= 0)
                                        <span class="badge bg-danger">Kapasitas Kosong</span>
                                    @elseif($proses->bopProses)
                                        @if($proses->bopProses->isConfigured())
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-warning">Belum Dikonfigurasi</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Belum Ada BOP</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if($proses->bopProses)
                                            <a href="{{ route('master-data.bop-proses.show', $proses->bopProses->id) }}" class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('master-data.bop-proses.edit', $proses->bopProses->id) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('master-data.bop-proses.destroy', $proses->bopProses->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus BOP proses ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            @if($proses->kapasitas_per_jam > 0)
                                                <a href="{{ route('master-data.bop-proses.create', ['proses_id' => $proses->id]) }}" class="btn btn-outline-success" title="Buat BOP">
                                                    <i class="fas fa-plus"></i> Buat BOP
                                                </a>
                                            @else
                                                <span class="text-muted small">Perlu kapasitas BTKL</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data proses produksi</p>
                                    <a href="{{ route('master-data.btkl.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Buat BTKL Terlebih Dahulu
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($prosesProduksis->count() > 0)
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="fw-bold text-primary">{{ $prosesProduksis->total() }}</div>
                                <small class="text-muted">Total Proses</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                @php
                                    $withBop = $prosesProduksis->filter(function($p) { return $p->bopProses; })->count();
                                @endphp
                                <div class="fw-bold text-success">{{ $withBop }}</div>
                                <small class="text-muted">Sudah Ada BOP</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                @php
                                    $withoutCapacity = $prosesProduksis->filter(function($p) { return $p->kapasitas_per_jam <= 0; })->count();
                                @endphp
                                <div class="fw-bold text-danger">{{ $withoutCapacity }}</div>
                                <small class="text-muted">Tanpa Kapasitas</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $avgBopPerUnit = $prosesProduksis->filter(function($p) { 
                                    return $p->bopProses && $p->bopProses->bop_per_unit > 0; 
                                })->avg(function($p) { 
                                    return $p->bopProses->bop_per_unit; 
                                });
                            @endphp
                            <div class="fw-bold text-warning">Rp {{ number_format($avgBopPerUnit ?? 0, 2, ',', '.') }}</div>
                            <small class="text-muted">Rata-rata BOP/Unit</small>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                @if($prosesProduksis->hasPages())
                    <div class="card-footer">
                        {{ $prosesProduksis->links() }}
                    </div>
                @endif
            @else
                <div class="card-footer">
                    <div class="text-center text-muted py-2">
                        <small>Belum ada data untuk ditampilkan</small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
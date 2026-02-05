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
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar BTKL (Biaya Tenaga Kerja Langsung)
                </h5>
                @if($prosesProduksis->total() > 0)
                    <small class="text-muted">Total: {{ $prosesProduksis->total() }} proses BTKL</small>
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
                            <th>Kode</th>
                            <th>Nama Proses</th>
                            <th>Jabatan BTKL</th>
                            <th class="text-end">Tarif BTKL/Jam</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Kapasitas/Jam</th>
                            <th class="text-end">
                                Biaya per Produk
                                <i class="fas fa-info-circle text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Dihitung dari: Tarif BTKL per Jam ÷ Kapasitas per Jam"></i>
                            </th>
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
                                <td>
                                    @if($proses->jabatan)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 p-1 me-2">
                                                <i class="fas fa-users text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $proses->jabatan->nama }}</div>
                                                <small class="text-muted">
                                                    {{ $proses->jabatan->pegawais->count() }} pegawai @ Rp {{ number_format($proses->jabatan->tarif, 0, ',', '.') }}/jam
                                                </small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="fw-semibold">Rp {{ number_format($proses->tarif_btkl, 0, ',', '.') }}</div>
                                    <small class="text-muted">per {{ $proses->satuan_btkl ?? 'jam' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $proses->satuan_btkl ?? 'jam' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $proses->kapasitas_per_jam ?? 0 }} unit/jam</span>
                                </td>
                                <td class="text-end" 
                                    data-biaya-per-produk="{{ format_number_clean($proses->biaya_per_produk) }}"
                                    data-tarif="{{ number_format($proses->tarif_btkl, 0, ',', '.') }}"
                                    data-kapasitas="{{ $proses->kapasitas_per_jam }}">
                                    @if($proses->biaya_per_produk > 0)
                                        <div class="fw-semibold text-success">{{ $proses->biaya_per_produk_formatted }}</div>
                                        <small class="text-muted">per unit</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.btkl.show', $proses) }}" class="btn btn-outline-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('master-data.btkl.edit', $proses) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.btkl.destroy', $proses) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus proses ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data BTKL</p>
                                    <a href="{{ route('master-data.btkl.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Tambah BTKL Pertama
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
                                    $avgTarif = $prosesProduksis->avg('tarif_btkl');
                                @endphp
                                <div class="fw-bold text-success">Rp {{ number_format($avgTarif, 0, ',', '.') }}</div>
                                <small class="text-muted">Rata-rata Tarif/Jam</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                @php
                                    $avgKapasitas = $prosesProduksis->avg('kapasitas_per_jam');
                                @endphp
                                <div class="fw-bold text-info">{{ number_format($avgKapasitas, 0, ',', '.') }}</div>
                                <small class="text-muted">Rata-rata Kapasitas/Jam</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $validProses = $prosesProduksis->filter(function($p) { 
                                    return $p->kapasitas_per_jam > 0; 
                                });
                                $avgBiayaPerUnit = $validProses->count() > 0 ? 
                                    $validProses->avg(function($p) { 
                                        return $p->tarif_btkl / $p->kapasitas_per_jam; 
                                    }) : 0;
                            @endphp
                            <div class="fw-bold text-warning">{{ format_rupiah_clean($avgBiayaPerUnit) }}</div>
                            <small class="text-muted">Rata-rata Biaya/Unit</small>
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

    // Add hover effect to show calculation details
    const biayaPerProdukCells = document.querySelectorAll('td[data-biaya-per-produk]');
    biayaPerProdukCells.forEach(function(cell) {
        const tarif = cell.dataset.tarif;
        const kapasitas = cell.dataset.kapasitas;
        const biaya = cell.dataset.biayaPerProduk;
        
        cell.setAttribute('title', `Perhitungan: Rp ${tarif} ÷ ${kapasitas} unit = Rp ${biaya}`);
        
        // Initialize tooltip for calculation
        new bootstrap.Tooltip(cell);
    });
});
</script>
@endsection

@extends('layouts.app')

@section('title', 'Daftar BTKL')

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
                            <th class="text-center" style="width: 8%">Kode</th>
                            <th style="width: 15%">Nama Proses</th>
                            <th style="width: 15%">Jabatan BTKL</th>
                            <th style="width: 10%">Jumlah Pegawai</th>
                            <th style="width: 12%">Tarif BTKL</th>
                            <th style="width: 8%">Satuan</th>
                            <th style="width: 12%">Kapasitas/Jam</th>
                            <th style="width: 12%">Biaya per Produk</th>
                            <th style="width: 15%">Deskripsi</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prosesProduksis as $proses)
                        <tr>
                            <td class="text-center">{{ $proses->kode_proses }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-gear-fill me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold">{{ $proses->nama_btkl ?? '-' }}</div>
                                        <small class="text-muted">Nama proses produksi</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-workspace me-2 text-info"></i>
                                    <div>
                                        <div class="fw-bold">{{ $proses->jabatan->nama ?? '-' }}</div>
                                        <small class="text-muted">{{ $proses->jabatan->kategori ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people-fill me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold text-primary">{{ $proses->jabatan->pegawais->count() ?? 0 }} orang</div>
                                        <small class="text-muted">Jabatan: {{ $proses->jabatan->nama ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-stack me-2 text-success"></i>
                                    <div>
                                        <span class="fw-bold text-success">{{ number_format($proses->tarif_btkl, 0, ',', '.') }}</span>
                                        <small class="text-muted d-block">per jam</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-info">{{ $proses->satuan ?? 'jam' }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-speedometer2 me-2 text-warning"></i>
                                    <div>
                                        <div class="fw-bold text-warning">{{ number_format($proses->kapasitas_per_jam) }} unit/jam</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-stack me-2 text-warning"></i>
                                    <div>
                                        <div class="fw-bold text-warning">{{ $proses->biaya_per_produk_formatted }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><small>{{ $proses->deskripsi_proses ?? '-' }}</small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('master-data.btkl.edit', $proses->id) }}" class="btn btn-sm btn-warning rounded-pill px-3">
                                        <i class="fas fa-edit me-1"></i>
                                        <span class="d-none d-md-inline">Edit</span>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $proses->id }}">
                                        <i class="fas fa-trash me-1"></i>
                                        <span class="d-none d-md-inline">Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
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
                <!-- Statistics Summary -->
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
                                    // Use tarif_btkl from database
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
                                // Use biaya_btkl_per_produk from database
                                $avgBiayaPerUnit = $prosesProduksis->avg('biaya_btkl_per_produk');
                            @endphp
                            <div class="fw-bold text-warning">Rp {{ number_format($avgBiayaPerUnit, 2, ',', '.') }}</div>
                            <small class="text-muted">Rata-rata Biaya/Unit</small>
                        </div>
                    </div>
                </div>
                
                <!-- Total Biaya Per Produk Summary - Paling Bawah -->
                <div class="card-footer bg-warning bg-opacity-10">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0 text-warning">
                                <i class="fas fa-calculator me-2"></i>Total Biaya Per Produk:
                            </h6>
                            <small class="text-muted">Jumlah semua biaya BTKL per unit produk</small>
                        </div>
                        <div class="col-md-4 text-end">
                            @php
                                // Use biaya_btkl_per_produk from database
                                $totalBiayaPerProduk = $prosesProduksis->sum('biaya_btkl_per_produk');
                            @endphp
                            <div class="display-6 fw-bold text-warning">Rp {{ number_format($totalBiayaPerProduk, 2, ',', '.') }}</div>
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

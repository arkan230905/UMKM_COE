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

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2 text-primary"></i>Daftar BTKL (Biaya Tenaga Kerja Langsung)
                </h5>
                <small class="text-muted">Total: {{ $prosesProduksis->total() }} proses produksi</small>
            </div>
            <span class="badge bg-soft-primary text-primary px-3">{{ $prosesProduksis->count() }} data ditampilkan</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 8%">Kode</th>
                            <th style="width: 25%">Nama Proses</th>
                            <th style="width: 20%">Jabatan BTKL</th>
                            <th class="text-center" style="width: 12%">Jumlah Pegawai</th>
                            <th style="width: 15%">Tarif BTKL (Per Produk)</th>
                            <th style="width: 15%">Total Biaya Produk</th>
                            <th class="text-center" style="width: 5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prosesProduksis as $proses)
                        @php
                            // Mengambil jumlah pegawai dari data proses produksi
                            $jmlPegawai = $proses->jumlah_pegawai ?? 0;
                            $tarifPerProduk = $proses->tarif_per_produk ?? 0;
                            // Rumus: Jumlah Pegawai x Tarif Per Produk
                            $totalBiayaUnit = $jmlPegawai * $tarifPerProduk;
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">{{ $proses->kode_proses }}</td>
                            <td>
                                <div class="fw-bold">{{ $proses->nama_proses ?? '-' }}</div>
                                <small class="text-muted">Proses Produksi</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-badge me-2 text-info"></i>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $proses->jabatan->nama ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info px-3">{{ $jmlPegawai }} Orang</span>
                            </td>
                            <td>
                                <div class="fw-bold text-success">
                                    Rp {{ number_format($tarifPerProduk, 0, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-warning" style="font-size: 1.1rem;">
                                    Rp {{ number_format($totalBiayaUnit, 0, ',', '.') }}
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">(Pegawai x Tarif)</small>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('master-data.btkl.edit', $proses->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $proses->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">Belum ada data BTKL</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($prosesProduksis->count() > 0)
        <div class="card-footer bg-white border-top-0 py-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-muted d-block mb-1">TOTAL PROSES</small>
                        <h4 class="mb-0 fw-bold text-primary">{{ $prosesProduksis->total() }}</h4>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="p-3 border rounded bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-warning fw-bold d-block mb-1">TOTAL BIAYA PER PRODUK (SEMUA PROSES)</small>
                            <p class="text-muted mb-0" style="font-size: 0.8rem;">Akumulasi seluruh biaya BTKL untuk satu unit produk</p>
                        </div>
                        <h2 class="mb-0 fw-bold text-warning">
                            @php
                                $totalAkhir = $prosesProduksis->sum(function($p) {
                                    return ($p->jumlah_pegawai ?? 0) * ($p->tarif_per_produk ?? 0);
                                });
                            @endphp
                            Rp {{ number_format($totalAkhir, 0, ',', '.') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($prosesProduksis->hasPages())
        <div class="card-footer bg-white">
            {{ $prosesProduksis->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    .bg-soft-primary { background-color: #e7f1ff; }
    .bg-info-subtle { background-color: #cff4fc; }
</style>
@endsection
@extends('layouts.app')

@section('title', 'Harga Pokok Produksi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-sitemap me-2"></i>Harga Pokok Produksi Per Produk
        </h2>
        <a href="{{ route('master-data.harga-pokok-produksi.create') }}" class="btn btn-primary">
            <i class="fas fa-calculator me-2"></i>Hitung Harga Pokok Produksi
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

    <!-- Search Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="fas fa-search me-2"></i>Pencarian Produk
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.harga-pokok-produksi.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="{{ request('nama_produk') }}" placeholder="Masukkan nama produk">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- HPP Records Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Harga Pokok Produksi
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;">Nama Produk</th>
                            <th style="width: 10%;">Kode</th>
                            <th style="width: 10%;">Satuan</th>
                            <th style="width: 10%;">Stok</th>
                            <th style="width: 15%;">Biaya Bahan Baku</th>
                            <th style="width: 15%;">BTKL</th>
                            <th style="width: 15%;">BOP</th>
                            <th style="width: 15%;">Total HPP</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hppRecords as $hppRecord)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-box text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $hppRecord['nama_produk'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $hppRecord['kode'] }}</span>
                                </td>
                                <td>
                                    {{ $hppRecord['satuan'] }}
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ number_format($hppRecord['stok'], 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">
                                        Rp {{ number_format(getTotalBbb($hppRecord['id']), 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-warning">
                                        Rp {{ number_format(getTotalBtkl($hppRecord['id']), 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-danger">
                                        Rp {{ number_format(getTotalBop($hppRecord['id']), 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">
                                        Rp {{ number_format(getTotalHpp($hppRecord['id']), 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('master-data.harga-pokok-produksi.show', $hppRecord['id']) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('master-data.harga-pokok-produksi.destroy', $hppRecord['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus HPP ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="border-0 p-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
                                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                        <p class="text-muted fs-5 mb-0">Belum ada Harga Pokok Produksi yang dibuat</p>
                                        <p class="text-muted">Silakan buat HPP baru untuk produk yang tersedia</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($hppRecords->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Menampilkan {{ $hppRecords->firstItem() }} sampai {{ $hppRecords->lastItem() }} dari {{ $hppRecords->total() }} data
                    </div>
                    <div>
                        {{ $hppRecords->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@php
function getTotalBbb($produkId) {
    $total = 0;
    $bbb = \App\Models\HargaPokokProduksiBiayaBahanBaku::where('user_id', auth()->id())
        ->whereHas('biayaBahanBaku', function($query) use ($produkId) {
            $query->where('produk_id', $produkId);
        })
        ->with('biayaBahanBaku')
        ->get();
    
    foreach ($bbb as $item) {
        if ($item->biayaBahanBaku) {
            $total += $item->biayaBahanBaku->subtotal;
        }
    }
    return $total;
}

function getTotalBtkl($produkId) {
    $total = 0;
    // Since BTKL is not product-specific, get all BTKL for user
    $btkl = \App\Models\HargaPokokProduksiBtkl::where('user_id', auth()->id())
        ->with('prosesProduksi')
        ->get();
    
    foreach ($btkl as $item) {
        if ($item->prosesProduksi) {
            // Calculate total BTKL: tarif_per_produk × jumlah_pegawai
            $tarifPerProduk = $item->prosesProduksi->tarif_per_produk ?? 0;
            $jumlahPegawai = $item->prosesProduksi->jumlah_pegawai ?? 1;
            $tarif = $tarifPerProduk * $jumlahPegawai;
            $total += $tarif;
        }
    }
    return $total;
}

function getTotalBop($produkId) {
    $total = 0;
    // Since BOP is not product-specific, get all BOP for user
    $bop = \App\Models\HargaPokokProduksiBop::where('user_id', auth()->id())
        ->with('bopProses')
        ->get();
    
    foreach ($bop as $item) {
        if ($item->bopProses) {
            $total += $item->bopProses->total_bop_per_produk ?? 0;
        }
    }
    return $total;
}

function getTotalHpp($produkId) {
    return getTotalBbb($produkId) + getTotalBtkl($produkId) + getTotalBop($produkId);
}
@endphp
@endsection

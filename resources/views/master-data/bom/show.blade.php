@extends('layouts.app')

@section('title', 'Detail Harga Pokok Produksi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-eye me-2"></i>Detail Harga Pokok Produksi
        </h2>
        <div>
            <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('master-data.harga-pokok-produksi.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Hitung HPP Baru
            </a>
        </div>
    </div>

<<<<<<< HEAD
    <!-- Informasi Dasar -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white border-bottom border-3 border-primary">
            <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Produk</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Nama Produk:</th>
                            <td>{{ $produk->nama_produk }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi:</th>
                            <td>{{ $produk->deskripsi ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat:</th>
                            <td>{{ $bomJobCosting?->created_at->format('d F Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- BIAYA BAHAN -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white border-bottom border-3 border-success">
            <h5 class="mb-0 fw-bold"><i class="fas fa-cube me-2"></i>Biaya Bahan</h5>
        </div>
        <div class="card-body">
            
            <!-- Bahan Baku -->
            <h6 class="text-success mb-3"><i class="fas fa-box"></i> Bahan Baku</h6>
            @if($detailBahanBaku && count($detailBahanBaku) > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-success">
                            <tr>
                                <th class="fw-bold"><i class="fas fa-leaf me-1"></i>Bahan Baku</th>
                                <th class="text-center fw-bold">Jumlah/Quantity</th>
                                <th class="text-center fw-bold">Satuan</th>
                                <th class="text-end fw-bold">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailBahanBaku as $bahan)
                                <tr>
                                    <td>{{ $bahan['nama_bahan'] }}</td>
                                    <td class="text-center">{{ number_format($bahan['qty'], 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $bahan['satuan'] }}</td>
                                    <td class="text-end">
                                        @if($bahan['subtotal'] > 0)
                                            Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Belum ada data bahan baku</div>
            @endif

            <!-- Total Biaya Bahan -->
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="card bg-light border-2">
                        <div class="card-body">
                            <h6 class="card-title text-primary fw-bold">
                                <i class="fas fa-calculator me-2"></i>Total Biaya Bahan
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="fw-semibold">Bahan Baku:</td>
                                    <td class="text-end data-value" id="total-bbb">
                                        @if($totalBBB > 0)
                                            Rp {{ number_format($totalBBB, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-top border-2 border-primary">
                                    <th class="fw-bold text-primary">SUBTOTAL:</th>
                                    <th class="text-end fw-bold text-primary fs-6 data-value" id="total-biaya-bahan">
                                        @if($totalBBB > 0)
                                            Rp {{ number_format($totalBBB, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </th>
                                </tr>
                            </table>
=======
    <!-- Product Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-box me-2"></i>Informasi Produk
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Nama Produk:</strong><br>
                            <span class="text-primary fs-5">{{ $produk->nama_produk }}</span>
                        </div>
                        <div class="col-md-2">
                            <strong>Kode:</strong><br>
                            {{ $produk->kode_produk ?? '-' }}
                        </div>
                        <div class="col-md-2">
                            <strong>Satuan:</strong><br>
                            {{ $produk->satuan->nama ?? '-' }}
                        </div>
                        <div class="col-md-2">
                            <strong>Stok:</strong><br>
                            {{ number_format($produk->stok, 0, ',', '.') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Harga Jual:</strong><br>
                            <span class="text-success fs-6">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</span>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HPP Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Ringkasan Harga Pokok Produksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-success mb-1">Rp {{ number_format($totalBbb, 0, ',', '.') }}</h4>
                                <small class="text-muted">Biaya Bahan Baku</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-warning mb-1">Rp {{ number_format($totalBtkl, 0, ',', '.') }}</h4>
                                <small class="text-muted">BTKL</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-danger mb-1">Rp {{ number_format($totalBop, 0, ',', '.') }}</h4>
                                <small class="text-muted">BOP</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-primary mb-1">Rp {{ number_format($totalHpp, 0, ',', '.') }}</h3>
                            <small class="text-muted"><strong>Total HPP</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Biaya Bahan Baku Detail -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cube me-2"></i>Detail Biaya Bahan Baku
                    </h6>
                </div>
                <div class="card-body">
                    @if($selectedBbb->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Bahan</th>
                                        <th>Jumlah</th>
                                        <th>Satuan</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedBbb as $index => $bbb)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $bbb->biayaBahanBaku->bahanBaku->nama_bahan ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ number_format($bbb->biayaBahanBaku->jumlah ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $bbb->biayaBahanBaku->satuan ?? '-' }}</td>
                                            <td>Rp {{ number_format($bbb->biayaBahanBaku->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                            <td><strong class="text-success">Rp {{ number_format($bbb->biayaBahanBaku->subtotal ?? 0, 0, ',', '.') }}</strong></td>
                                            <td>{{ $bbb->biayaBahanBaku->keterangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Biaya Bahan Baku:</th>
                                        <th class="text-success">Rp {{ number_format($totalBbb, 0, ',', '.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data biaya bahan baku</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- BTKL Detail -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Detail BTKL (Biaya Tenaga Kerja Langsung)
                    </h6>
                </div>
                <div class="card-body">
                    @if($selectedBtkl->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Proses</th>
                                        <th>Kode Proses</th>
                                        <th>Tarif per Jam</th>
                                        <th>Kapasitas per Jam</th>
                                        <th>Biaya per Produk</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedBtkl as $index => $btkl)
                                        @php
                                            $tarif = $btkl->prosesProduksi->tarif_btkl ?? 0;
                                            $kapasitas = $btkl->prosesProduksi->kapasitas_per_jam ?? 1;
                                            $biayaPerProduk = $kapasitas > 0 ? $tarif / $kapasitas : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $btkl->prosesProduksi->nama_proses ?? 'N/A' }}</strong></td>
                                            <td>{{ $btkl->prosesProduksi->kode_proses ?? '-' }}</td>
                                            <td>Rp {{ number_format($tarif, 0, ',', '.') }}</td>
                                            <td>{{ $kapasitas }} unit/jam</td>
                                            <td><strong class="text-warning">Rp {{ number_format($biayaPerProduk, 0, ',', '.') }}</strong></td>
                                            <td>{{ $btkl->prosesProduksi->deskripsi ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total BTKL:</th>
                                        <th class="text-warning">Rp {{ number_format($totalBtkl, 0, ',', '.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data BTKL</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- BOP Detail -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Detail BOP (Biaya Overhead Pabrik)
                    </h6>
                </div>
                <div class="card-body">
                    @if($selectedBop->count() > 0)
                        @foreach($selectedBop as $index => $bop)
                            @php
                                // komponen_bop might already be an array (Laravel casts it)
                                $komponenBop = $bop->bopProses->komponen_bop ?? [];
                                if (is_string($komponenBop)) {
                                    $komponenBop = json_decode($komponenBop, true) ?? [];
                                }
                                $bopName = $bop->bopProses->prosesProduksi->nama_proses ?? 'BOP Item';
                                $totalBopItem = $bop->bopProses->total_bop_per_produk ?? 0;
                            @endphp
                            
                            <div class="card mb-3 border-danger">
                                <div class="card-header bg-danger bg-opacity-10">
                                    <h6 class="mb-0 text-danger">
                                        <i class="fas fa-industry me-2"></i>{{ $index + 1 }}. {{ $bopName }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if(!empty($komponenBop) && is_array($komponenBop))
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="10%">No</th>
                                                        <th width="60%">Komponen BOP</th>
                                                        <th width="30%" class="text-end">Tarif per Jam</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($komponenBop as $idx => $komponen)
                                                        @php
                                                            $ratePerHour = $komponen['rate_per_hour'] ?? 0;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $idx + 1 }}</td>
                                                            <td>{{ $komponen['component'] ?? 'Unknown' }}</td>
                                                            <td class="text-end"><strong>Rp {{ number_format($ratePerHour, 0, ',', '.') }}</strong></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="2" class="text-end">Total BOP {{ $bopName }}:</th>
                                                        <th class="text-end text-danger">Rp {{ number_format($totalBopItem, 0, ',', '.') }}</th>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" class="text-muted small">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Kapasitas: {{ $kapasitas }} unit/jam
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Tidak ada detail komponen BOP. Total BOP: <strong>Rp {{ number_format($totalBopItem, 0, ',', '.') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        
                        <!-- Grand Total BOP -->
                        <div class="card border-danger">
                            <div class="card-body bg-danger bg-opacity-10">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-0 text-danger">
                                            <i class="fas fa-calculator me-2"></i>Total Keseluruhan BOP
                                        </h5>
                                        <small class="text-muted">Jumlah total dari semua komponen BOP</small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <h3 class="mb-0 text-danger fw-bold">Rp {{ number_format($totalBop, 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data BOP</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="mb-3">Aksi</h5>
                    <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-list me-2"></i>Lihat Semua HPP
                    </a>
                    <a href="{{ route('master-data.harga-pokok-produksi.create') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-2"></i>Hitung HPP Baru
                    </a>
                    <form action="{{ route('master-data.harga-pokok-produksi.destroy', $produk->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data HPP ini?')">
                            <i class="fas fa-trash me-2"></i>Hapus HPP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-end {
    border-right: 1px solid #dee2e6 !important;
}

.table th {
    font-weight: 600;
    font-size: 0.9rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    font-weight: 600;
}

.table-responsive {
    border-radius: 0.375rem;
}

.btn {
    border-radius: 0.375rem;
}
</style>
@endsection
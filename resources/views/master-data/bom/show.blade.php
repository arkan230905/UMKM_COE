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
                                        <th>Tarif per Produk</th>
                                        <th>Biaya per Produk</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedBtkl as $index => $btkl)
                                        @php
                                            $tarifPerProduk = $btkl->prosesProduksi->tarif_per_produk ?? 0;
                                            $jumlahPegawai = $btkl->prosesProduksi->jumlah_pegawai ?? 1;
                                            $tarif = $tarifPerProduk * $jumlahPegawai;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $btkl->prosesProduksi->nama_proses ?? 'N/A' }}</strong></td>
                                            <td>{{ $btkl->prosesProduksi->kode_proses ?? '-' }}</td>
                                            <td>Rp {{ number_format($tarif, 0, ',', '.') }}</td>
                                            <td><strong class="text-warning">Rp {{ number_format($tarif, 0, ',', '.') }}</strong></td>
                                            <td>{{ $btkl->prosesProduksi->deskripsi ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Total BTKL:</th>
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
                                // DEBUG: Check if bopProses exists
                                if (!$bop->bopProses) {
                                    \Log::error('BOP Show - Missing bopProses relation', [
                                        'bop_id' => $bop->id,
                                        'bop_proses_id' => $bop->bop_proses_id
                                    ]);
                                    continue; // Skip this iteration
                                }
                                
                                // Get komponen from new structure
                                $komponenBahanPendukung = $bop->bopProses->komponen_bahan_pendukung ?? [];
                                $komponenLainnya = $bop->bopProses->komponen_lainnya ?? [];
                                
                                // Ensure they are arrays (cast from JSON if needed)
                                if (is_string($komponenBahanPendukung)) {
                                    $komponenBahanPendukung = json_decode($komponenBahanPendukung, true) ?? [];
                                }
                                if (is_string($komponenLainnya)) {
                                    $komponenLainnya = json_decode($komponenLainnya, true) ?? [];
                                }
                                
                                // Ensure both are arrays even if null
                                if (!is_array($komponenBahanPendukung)) {
                                    $komponenBahanPendukung = [];
                                }
                                if (!is_array($komponenLainnya)) {
                                    $komponenLainnya = [];
                                }
                                
                                $bopName = $bop->bopProses->nama_bop_proses ?? 'BOP Item';
                                $totalBopItem = $bop->bopProses->total_bop_per_produk ?? 0;
                                
                                // Count total components
                                $totalKomponen = count($komponenBahanPendukung) + count($komponenLainnya);
                                
                                // DEBUG LOG
                                \Log::info('BOP Show - Processing BOP', [
                                    'index' => $index + 1,
                                    'bop_id' => $bop->id,
                                    'nama' => $bopName,
                                    'total_bop' => $totalBopItem,
                                    'bahan_pendukung_count' => count($komponenBahanPendukung),
                                    'lainnya_count' => count($komponenLainnya),
                                    'total_komponen' => $totalKomponen
                                ]);
                            @endphp
                            
                            <div class="card mb-3 border-danger">
                                <div class="card-header bg-danger bg-opacity-10">
                                    <h6 class="mb-0 text-danger">
                                        <i class="fas fa-industry me-2"></i>{{ $index + 1 }}. {{ $bopName }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    {{-- DEBUG INFO (can be removed later) --}}
                                    @if(config('app.debug'))
                                        <div class="alert alert-info alert-sm mb-3">
                                            <small>
                                                <strong>Debug Info:</strong> 
                                                Bahan Pendukung: {{ count($komponenBahanPendukung) }} items | 
                                                Lainnya: {{ count($komponenLainnya) }} items | 
                                                Total Komponen: {{ $totalKomponen }}
                                            </small>
                                        </div>
                                    @endif
                                    
                                    @if($totalKomponen > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="10%">No</th>
                                                        <th width="10%">Tipe</th>
                                                        <th width="50%">Komponen BOP</th>
                                                        <th width="30%" class="text-end">Tarif per Produk</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $no = 1; @endphp
                                                    
                                                    <!-- Bahan Pendukung -->
                                                    @if(!empty($komponenBahanPendukung))
                                                        @foreach($komponenBahanPendukung as $komponen)
                                                            @php
                                                                $nama = $komponen['nama'] ?? 'Unknown';
                                                                $total = $komponen['total'] ?? 0;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $no++ }}</td>
                                                                <td>
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-box me-1"></i>Bahan
                                                                    </span>
                                                                </td>
                                                                <td>{{ $nama }}</td>
                                                                <td class="text-end"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    
                                                    <!-- Lainnya -->
                                                    @if(!empty($komponenLainnya))
                                                        @foreach($komponenLainnya as $komponen)
                                                            @php
                                                                $nama = $komponen['nama_komponen'] ?? 'Unknown';
                                                                $nilai = $komponen['nilai_per_produk'] ?? 0;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $no++ }}</td>
                                                                <td>
                                                                    <span class="badge bg-primary">
                                                                        <i class="fas fa-tools me-1"></i>Lainnya
                                                                    </span>
                                                                </td>
                                                                <td>{{ $nama }}</td>
                                                                <td class="text-end"><strong>Rp {{ number_format($nilai, 0, ',', '.') }}</strong></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="3" class="text-end">Total BOP {{ $bopName }}:</th>
                                                        <th class="text-end text-danger">Rp {{ number_format($totalBopItem, 0, ',', '.') }}</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Tidak ada detail komponen BOP.</strong> 
                                            Total BOP: <strong>Rp {{ number_format($totalBopItem, 0, ',', '.') }}</strong>
                                            @if(config('app.debug'))
                                                <br><small class="text-muted">Debug: Bahan={{ count($komponenBahanPendukung) }}, Lainnya={{ count($komponenLainnya) }}</small>
                                            @endif
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
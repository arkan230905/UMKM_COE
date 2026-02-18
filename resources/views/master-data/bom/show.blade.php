@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Detail Harga Pokok Produksi: {{ $produk->nama_produk }}</h3>
        <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
                            <td>{{ $bomJobCosting->created_at->format('d F Y H:i') ?? '-' }}</td>
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

            <!-- Bahan Penolong/Pendukung -->
            <h6 class="text-warning mb-3"><i class="fas fa-flask"></i> Bahan Penolong</h6>
            @if($detailBahanPendukung && count($detailBahanPendukung) > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-warning">
                            <tr>
                                <th class="fw-bold"><i class="fas fa-tools me-1"></i>Bahan Penolong</th>
                                <th class="text-center fw-bold">Jumlah/Quantity</th>
                                <th class="text-center fw-bold">Satuan</th>
                                <th class="text-end fw-bold">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailBahanPendukung as $bahan)
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
                <div class="alert alert-info">Belum ada data bahan penolong</div>
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
                                    <td class="text-end">
                                        @if($totalBBB > 0)
                                            Rp {{ number_format($totalBBB, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Bahan Pendukung:</td>
                                    <td class="text-end">
                                        @if($totalBahanPendukung > 0)
                                            Rp {{ number_format($totalBahanPendukung, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-top border-2 border-primary">
                                    <th class="fw-bold text-primary">SUBTOTAL:</th>
                                    <th class="text-end fw-bold text-primary fs-6">
                                        @if($totalBiayaBahan > 0)
                                            Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BTKL -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white border-bottom border-3 border-info">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>BTKL (Biaya Tenaga Kerja Langsung)</h5>
        </div>
        <div class="card-body">
            @if($btklDataForDisplay && count($btklDataForDisplay) > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-info">
                            <tr>
                                <th class="fw-bold"><i class="fas fa-code me-1"></i>Kode</th>
                                <th class="fw-bold"><i class="fas fa-cogs me-1"></i>Nama Proses</th>
                                <th class="fw-bold"><i class="fas fa-user-tie me-1"></i>Jabatan BTKL</th>
                                <th class="text-center fw-bold"><i class="fas fa-users me-1"></i>Jumlah Pegawai</th>
                                <th class="text-end fw-bold"><i class="fas fa-money-bill me-1"></i>Tarif BTKL</th>
                                <th class="text-center fw-bold">Satuan</th>
                                <th class="text-center fw-bold"><i class="fas fa-tachometer-alt me-1"></i>Kapasitas/Jam</th>
                                <th class="text-end fw-bold"><i class="fas fa-calculator me-1"></i>Biaya per Produk</th>
                                <th class="fw-bold"><i class="fas fa-info-circle me-1"></i>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($btklDataForDisplay as $btkl)
                                <tr>
                                    <td>{{ $btkl['kode_proses'] ?? 'N/A' }}</td>
                                    <td>{{ $btkl['nama_proses'] ?? 'N/A' }}</td>
                                    <td>{{ $btkl['nama_jabatan'] ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $btkl['jumlah_pegawai'] ?? 0 }} orang</td>
                                    <td class="text-end">
                                        @if(($btkl['tarif_per_jam'] ?? 0) > 0)
                                            Rp {{ number_format($btkl['tarif_per_jam'] ?? 0, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">Jam</td>
                                    <td class="text-center">{{ number_format($btkl['kapasitas_per_jam'] ?? 0, 0, ',', '.') }} pcs</td>
                                    <td class="text-end">
                                        @if(($btkl['subtotal'] ?? 0) > 0)
                                            Rp {{ number_format($btkl['subtotal'] ?? 0, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>Proses {{ strtolower($btkl['nama_proses'] ?? '') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-warning">
                            <tr class="border-top border-2">
                                <th colspan="7" class="text-end fw-bold">Total Biaya Per Produk:</th>
                                <th class="text-end fw-bold fs-6">
                                    @if($totalBiayaBTKL > 0)
                                        Rp {{ number_format($totalBiayaBTKL, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Belum ada data BTKL</div>
            @endif
        </div>
    </div>

    <!-- BOP -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark border-bottom border-3 border-warning">
            <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2"></i>BOP (Biaya Overhead Pabrik)</h5>
        </div>
        <div class="card-body">
            @if($bopData && count($bopData) > 0)
                
                <!-- BOP per Proses -->
                @php
                    $prosesGroups = [];
                    foreach($bopData as $bop) {
                        $prosesName = $bop['nama_proses'] ?? 'Proses Umum';
                        if (!isset($prosesGroups[$prosesName])) {
                            $prosesGroups[$prosesName] = [];
                        }
                        $prosesGroups[$prosesName][] = $bop;
                    }
                @endphp

                @foreach($prosesGroups as $prosesName => $bopItems)
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-gear"></i> Proses {{ $prosesName }}
                        </h6>
                        
                        @php
                            $totalBopProses = 0;
                            $kapasitasPerJam = 0;
                            $btklPerJam = 0;
                            
                            // Ambil data dari BTKL yang sesuai dengan penanganan typo
                            foreach($btklDataForDisplay as $btkl) {
                                $namaProsesBtkl = $btkl['nama_proses'] ?? '';
                                
                                // Handle exact match first
                                if (stripos($namaProsesBtkl, $prosesName) !== false) {
                                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                                    $btklPerJam = $btkl['tarif_per_jam'] ?? 0;
                                    break;
                                }
                                
                                // Handle typo: "Permbumbuan" should match "Perbumbuan"
                                if ($prosesName === 'Perbumbuan' && stripos($namaProsesBtkl, 'Permbumbuan') !== false) {
                                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                                    $btklPerJam = $btkl['tarif_per_jam'] ?? 0;
                                    break;
                                }
                                
                                // Handle reverse case: if BTKL has "Perbumbuan" and we're looking for "Permbumbuan"
                                if ($prosesName === 'Permbumbuan' && stripos($namaProsesBtkl, 'Perbumbuan') !== false) {
                                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                                    $btklPerJam = $btkl['tarif_per_jam'] ?? 0;
                                    break;
                                }
                            }
                            
                            $btklPerPcs = $kapasitasPerJam > 0 ? $btklPerJam / $kapasitasPerJam : 0;
                        @endphp

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <small class="text-muted">Kapasitas</small>
                                        <h6>{{ number_format($kapasitasPerJam, 0, ',', '.') }} pcs/jam</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <small class="text-muted">BTKL/jam</small>
                                        <h6>
                                            @if($btklPerJam > 0)
                                                Rp {{ number_format($btklPerJam, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <small class="text-muted">BTKL/pcs</small>
                                        <h6>
                                            @if($btklPerPcs > 0)
                                                Rp {{ number_format($btklPerPcs, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-warning">
                                    <tr>
                                        <th class="fw-bold"><i class="fas fa-puzzle-piece me-1"></i>Komponen</th>
                                        <th class="text-end fw-bold"><i class="fas fa-money-bill-wave me-1"></i>Rp/Jam</th>
                                        <th class="fw-bold"><i class="fas fa-comment me-1"></i>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bopItems as $bop)
                                        @php
                                            $biayaPerJam = $bop['tarif'] ?? 0;
                                            $totalBopProses += $biayaPerJam;
                                        @endphp
                                        <tr>
                                            <td>{{ $bop['nama_komponen'] ?? 'Komponen BOP' }}</td>
                                            <td class="text-end">
                                                @if($biayaPerJam > 0)
                                                    Rp {{ number_format($biayaPerJam, 0, ',', '.') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $bop['keterangan'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-warning">
                                    @php
                                        $bopPerPcs = $kapasitasPerJam > 0 ? $totalBopProses / $kapasitasPerJam : 0;
                                        $biayaPerProduk = $bopPerPcs;
                                        $biayaPerJamTotal = $totalBopProses + $btklPerJam;
                                    @endphp
                                    <tr class="border-top">
                                        <th class="fw-bold">Total BOP/jam:</th>
                                        <th class="text-end fw-bold">
                                            @if($totalBopProses > 0)
                                                Rp {{ number_format($totalBopProses, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold">BOP/pcs:</th>
                                        <th class="text-end fw-bold">
                                            @if($bopPerPcs > 0)
                                                Rp {{ number_format($bopPerPcs, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold">Biaya/produk:</th>
                                        <th class="text-end fw-bold">
                                            @if($biayaPerProduk > 0)
                                                Rp {{ number_format($biayaPerProduk, 0, ',', '.') }} pcs
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="border-top border-2 bg-warning bg-opacity-50">
                                        <th class="fw-bold fs-6">Total Biaya/jam:</th>
                                        <th class="text-end fw-bold fs-6">
                                            @if($biayaPerJamTotal > 0)
                                                Rp {{ number_format($biayaPerJamTotal, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach

                <!-- Summary BOP -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="card bg-warning bg-opacity-25 border-warning border-2">
                            <div class="card-body">
                                <h6 class="card-title text-warning-emphasis fw-bold">
                                    <i class="fas fa-chart-line me-2"></i>Biaya Per Produk
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    @foreach($prosesGroups as $prosesName => $bopItems)
                                        @php
                                            $totalBopProses = 0;
                                            foreach($bopItems as $item) {
                                                $totalBopProses += $item['tarif'] ?? 0;
                                            }
                                            $kapasitasPerJam = 0;
                                            
                                            // Find matching BTKL capacity with typo handling
                                            foreach($btklDataForDisplay as $btkl) {
                                                $namaProsesBtkl = $btkl['nama_proses'] ?? '';
                                                
                                                // Handle exact match first
                                                if (stripos($namaProsesBtkl, $prosesName) !== false) {
                                                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                                                    break;
                                                }
                                                
                                                // Handle typo: "Permbumbuan" should match "Perbumbuan"
                                                if ($prosesName === 'Perbumbuan' && stripos($namaProsesBtkl, 'Permbumbuan') !== false) {
                                                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                                                    break;
                                                }
                                                
                                                // Handle reverse case: if BTKL has "Perbumbuan" and we're looking for "Permbumbuan"
                                                if ($prosesName === 'Permbumbuan' && stripos($namaProsesBtkl, 'Perbumbuan') !== false) {
                                                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                                                    break;
                                                }
                                            }
                                            
                                            $biayaPerProduk = $kapasitasPerJam > 0 ? $totalBopProses / $kapasitasPerJam : 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $prosesName }}:</td>
                                            <td class="text-end">
                                                @if($biayaPerProduk > 0)
                                                    Rp {{ number_format($biayaPerProduk, 0, ',', '.') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="border-top border-2 border-warning">
                                        <th class="fw-bold text-warning-emphasis">Total:</th>
                                        <th class="text-end fw-bold text-warning-emphasis fs-6">
                                            @if($totalBiayaBOP > 0)
                                                Rp {{ number_format($totalBiayaBOP, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-info">Belum ada data BOP</div>
            @endif
        </div>
    </div>

    <!-- TOTAL BOM -->
    <div class="card shadow-lg border-success border-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-calculator me-2"></i>Total Harga Pokok Produksi - {{ $produk->nama_produk }}
            </h5>
        </div>
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card bg-white border-2 border-success">
                        <div class="card-body">
                            <table class="table table-borderless table-lg mb-0">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="fw-bold fs-6 text-primary py-3">
                                            <i class="fas fa-boxes me-2"></i>Total Biaya Bahan:
                                        </td>
                                        <td class="text-end fw-bold fs-6 py-3">
                                            @if($totalBiayaBahan > 0)
                                                Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-bold fs-6 text-warning py-3">
                                            <i class="fas fa-users me-2"></i>Total Biaya BTKL:
                                        </td>
                                        <td class="text-end fw-bold fs-6 py-3">
                                            @if($totalBiayaBTKL > 0)
                                                Rp {{ number_format($totalBiayaBTKL, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-bold fs-6 text-info py-3">
                                            <i class="fas fa-cogs me-2"></i>Total Biaya BOP:
                                        </td>
                                        <td class="text-end fw-bold fs-6 py-3">
                                            @if($totalBiayaBOP > 0)
                                                Rp {{ number_format($totalBiayaBOP, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-top border-3 border-success bg-success bg-opacity-15">
                                        <th class="fw-bold fs-4 text-success py-4">
                                            <i class="fas fa-chart-bar me-2"></i>TOTAL HARGA POKOK PRODUKSI:
                                        </th>
                                        <th class="text-end fw-bold fs-4 text-success py-4">
                                            @if($totalBiayaBOM > 0)
                                                Rp {{ number_format($totalBiayaBOM, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Summary Info -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-success border-success border-2" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-5"></i>
                            <div>
                                <strong>Ringkasan Harga Pokok Produksi:</strong> 
                                Total biaya produksi untuk <strong>{{ $produk->nama_produk }}</strong> 
                                mencakup {{ count($detailBahanBaku) + count($detailBahanPendukung) }} item bahan, 
                                {{ count($btklDataForDisplay) }} proses BTKL, dan 
                                {{ count($bopData) }} komponen BOP.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
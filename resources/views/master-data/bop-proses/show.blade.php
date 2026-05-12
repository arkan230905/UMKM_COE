@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>Detail BOP Proses
        </h2>
        <div>
            <a href="{{ route('master-data.bop-proses.edit', $bopProses->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('master-data.bop-proses.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Detail Utama -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi BOP: {{ $bopProses->prosesProduksi->nama_proses }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Info Proses BTKL -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-cogs me-2"></i>Proses BTKL Terkait
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 40%">Kode Proses:</td>
                                    <td><code class="fs-6">{{ $bopProses->prosesProduksi->kode_proses }}</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Nama Proses:</td>
                                    <td>{{ $bopProses->prosesProduksi->nama_proses }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Kapasitas per Jam:</td>
                                    <td>
                                        <span class="fs-5 fw-bold text-info">{{ $bopProses->kapasitas_per_jam }}</span>
                                        <small class="text-muted">unit/jam</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 40%">Tarif BTKL:</td>
                                    <td>
                                        <span class="fs-6 fw-bold text-primary">Rp {{ number_format($bopProses->prosesProduksi->tarif_btkl, 0, ',', '.') }}</span>
                                        <small class="text-muted">per jam</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">BTKL per Unit:</td>
                                    <td>
                                        <span class="fs-6 fw-bold text-success">{{ $bopProses->prosesProduksi->biaya_per_produk_formatted }}</span>
                                        <small class="text-muted">per unit</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Status Sync:</td>
                                    <td>
                                        @if($bopProses->kapasitas_per_jam == $bopProses->prosesProduksi->kapasitas_per_jam)
                                            <span class="badge bg-success">Sync</span>
                                        @else
                                            <span class="badge bg-warning">Perlu Sync</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Komponen BOP -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-warning mb-3">
                                <i class="fas fa-list me-2"></i>Komponen BOP per Jam Mesin
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 60%">Listrik Mesin:</td>
                                    <td class="text-end">Rp {{ number_format($bopProses->listrik_per_jam, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Gas/BBM:</td>
                                    <td class="text-end">Rp {{ number_format($bopProses->gas_bbm_per_jam, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Penyusutan Mesin:</td>
                                    <td class="text-end">Rp {{ number_format($bopProses->penyusutan_mesin_per_jam, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 60%">Maintenance:</td>
                                    <td class="text-end">Rp {{ number_format($bopProses->maintenance_per_jam, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Gaji Mandor:</td>
                                    <td class="text-end">Rp {{ number_format($bopProses->gaji_mandor_per_jam, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Lain-lain:</td>
                                    <td class="text-end">Rp {{ number_format($bopProses->lain_lain_per_jam, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Total dan Perhitungan -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded bg-light">
                                <div class="fs-4 fw-bold text-warning">{{ $bopProses->total_bop_per_jam_formatted }}</div>
                                <small class="text-muted">Total BOP per Jam</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded bg-light">
                                <div class="fs-4 fw-bold text-info">{{ $bopProses->kapasitas_per_jam }} unit</div>
                                <small class="text-muted">Kapasitas per Jam</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded bg-light">
                                <div class="fs-4 fw-bold text-success">{{ $bopProses->bop_per_unit_formatted }}</div>
                                <small class="text-muted">BOP per Unit</small>
                            </div>
                        </div>
                    </div>

                    @if($bopProses->prosesProduksi->deskripsi)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold">Deskripsi Proses:</h6>
                                <p class="text-muted">{{ $bopProses->prosesProduksi->deskripsi }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Perhitungan & Analisis -->
        <div class="col-md-4">
            <!-- Perhitungan Detail -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Perhitungan BOP
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="fs-4 fw-bold text-warning">{{ $bopProses->total_bop_per_jam_formatted }}</div>
                            <small class="text-muted">Total BOP per Jam</small>
                        </div>
                        
                        <div class="mb-3">
                            <i class="fas fa-divide text-muted"></i>
                        </div>
                        
                        <div class="mb-3">
                            <div class="fs-4 fw-bold text-info">{{ $bopProses->kapasitas_per_jam }}</div>
                            <small class="text-muted">Unit per jam</small>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-0">
                            <div class="fs-3 fw-bold text-success">{{ $bopProses->bop_per_unit_formatted }}</div>
                            <small class="text-muted">BOP per unit produk</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Cost per Unit -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-coins me-2"></i>Total Cost per Unit
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td>BTKL per Unit:</td>
                            <td class="text-end">{{ $bopProses->prosesProduksi->biaya_per_produk_formatted }}</td>
                        </tr>
                        <tr>
                            <td>BOP per Unit:</td>
                            <td class="text-end">{{ $bopProses->bop_per_unit_formatted }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold">Total per Unit:</td>
                            <td class="text-end fw-bold text-primary">
                                Rp {{ number_format($bopProses->prosesProduksi->total_cost_per_unit, 2, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Simulasi Produksi -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Simulasi Produksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Simulasi untuk berapa unit?</label>
                        <input type="number" id="simulasiUnit" class="form-control" value="100" min="1">
                    </div>
                    
                    <div id="hasilSimulasi">
                        <div class="row text-center">
                            <div class="col-12 mb-2">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-primary" id="waktuDiperlukan">-</div>
                                    <small class="text-muted">Jam diperlukan</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-warning" id="totalBiayaBTKL">-</div>
                                    <small class="text-muted">Total BTKL</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-success" id="totalBiayaBOP">-</div>
                                    <small class="text-muted">Total BOP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const simulasiInput = document.getElementById('simulasiUnit');
    const kapasitasPerJam = {{ $bopProses->kapasitas_per_jam }};
    const btklPerUnit = {{ $bopProses->prosesProduksi->biaya_per_produk }};
    const bopPerUnit = {{ $bopProses->bop_per_unit }};
    
    function updateSimulasi() {
        const unit = parseInt(simulasiInput.value) || 0;
        
        if (kapasitasPerJam > 0 && unit > 0) {
            const waktuDiperlukan = (unit / kapasitasPerJam).toFixed(2);
            const totalBiayaBTKL = (unit * btklPerUnit);
            const totalBiayaBOP = (unit * bopPerUnit);
            
            document.getElementById('waktuDiperlukan').textContent = waktuDiperlukan;
            document.getElementById('totalBiayaBTKL').textContent = 'Rp ' + totalBiayaBTKL.toLocaleString('id-ID');
            document.getElementById('totalBiayaBOP').textContent = 'Rp ' + totalBiayaBOP.toLocaleString('id-ID');
        }
    }
    
    if (simulasiInput) {
        simulasiInput.addEventListener('input', updateSimulasi);
        updateSimulasi(); // Initial calculation
    }
});
</script>
@endsection
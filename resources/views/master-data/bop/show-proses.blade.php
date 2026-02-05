@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">
            <i class="fas fa-eye me-2"></i>Detail BOP Proses
        </h2>
        <div>
            <a href="{{ route('master-data.bop.edit-proses', $bopProses->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit BOP Komponen
            </a>
            <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informasi Proses BTKL -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Proses BTKL</h6>
                    <small>Data BTKL terkait dengan BOP proses produksi</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <strong>Kode Proses:</strong><br>
                            <code class="text-primary fs-6">{{ $bopProses->prosesProduksi->kode_proses }}</code>
                        </div>
                        <div class="col-6">
                            <strong>Nama Proses:</strong><br>
                            <span class="text-dark">{{ $bopProses->prosesProduksi->nama_proses }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Tarif BTKL:</strong><br>
                            <span class="text-success fw-bold">{{ format_rupiah_clean($bopProses->prosesProduksi->tarif_per_jam ?? 0) }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Kapasitas:</strong><br>
                            <span class="badge bg-info fs-6">{{ $bopProses->prosesProduksi->kapasitas_per_jam }} unit/jam</span>
                        </div>
                        <div class="col-6">
                            <strong>BTKL / pcs:</strong><br>
                            <span class="text-success">{{ format_rupiah_clean($bopProses->prosesProduksi->biaya_per_produk ?? 0) }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Deskripsi:</strong><br>
                            <span class="text-muted">{{ $bopProses->prosesProduksi->deskripsi ?? 'Proses penggorengan makanan' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan BOP -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Ringkasan BOP</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Total BOP per Jam:</strong>
                                <span class="fs-5 text-warning fw-bold">{{ format_rupiah_clean($bopProses->total_bop_per_jam) }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>BOP per Unit:</strong>
                                <span class="fs-5 text-success fw-bold">{{ format_rupiah_clean($bopProses->bop_per_unit) }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Efisiensi BOP:</strong>
                                <span class="text-muted">{{ number_format(($bopProses->prosesProduksi->kapasitas_per_jam ?? 0), 0) }} unit per jam</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Komponen BOP per Jam -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Detail Komponen BOP per Jam</h6>
        </div>
        <div class="card-body">
            @php
                $components = [
                    ['name' => 'Listrik Mesin', 'value' => $bopProses->listrik_per_jam, 'color' => 'primary'],
                    ['name' => 'Gas / BBM', 'value' => $bopProses->gas_bbm_per_jam, 'color' => 'danger'],
                    ['name' => 'Penyusutan Mesin', 'value' => $bopProses->penyusutan_mesin_per_jam, 'color' => 'secondary'],
                    ['name' => 'Maintenance', 'value' => $bopProses->maintenance_per_jam, 'color' => 'info'],
                    ['name' => 'Gaji Mandor', 'value' => $bopProses->gaji_mandor_per_jam, 'color' => 'success'],
                    ['name' => 'Lain-lain', 'value' => $bopProses->lain_lain_per_jam, 'color' => 'dark']
                ];
                $totalBop = $bopProses->total_bop_per_jam;
            @endphp

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Komponen BOP</th>
                            <th class="text-end">Biaya per Jam</th>
                            <th class="text-center">Persentase</th>
                            <th class="text-end">Biaya per Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($components as $component)
                            @php
                                $percentage = $totalBop > 0 ? ($component['value'] / $totalBop) * 100 : 0;
                                $biayaPerUnit = ($bopProses->prosesProduksi->kapasitas_per_jam ?? 0) > 0 ? $component['value'] / $bopProses->prosesProduksi->kapasitas_per_jam : 0;
                            @endphp
                            <tr>
                                <td>
                                    <i class="fas fa-circle text-{{ $component['color'] }} me-2"></i>
                                    {{ $component['name'] }}
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ format_rupiah_clean($component['value']) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $component['color'] }}" 
                                             role="progressbar" 
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ number_format($percentage, 1) }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="text-success">{{ format_rupiah_clean($biayaPerUnit) }}</span>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="table-warning">
                            <td><strong>Total BOP</strong></td>
                            <td class="text-end"><strong>{{ format_rupiah_clean($totalBop) }}</strong></td>
                            <td class="text-center"><strong>100%</strong></td>
                            <td class="text-end"><strong>{{ format_rupiah_clean($bopProses->bop_per_unit) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Simulasi Biaya Produksi -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Simulasi Biaya Produksi</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-center">
                        <h6>Simulasi Unit yang Diproduksi</h6>
                        <div class="fs-1 text-primary fw-bold">50</div>
                        <small class="text-muted">unit</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h6>Jam Kerja yang Dibutuhkan</h6>
                        <div class="fs-1 text-warning fw-bold">1 jam</div>
                        <small class="text-muted">{{ ($bopProses->prosesProduksi->kapasitas_per_jam ?? 0) }} unit per jam</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h6>Total Biaya BOP</h6>
                        <div class="fs-1 text-success fw-bold">{{ format_rupiah_clean($bopProses->total_bop_per_jam) }}</div>
                        <small class="text-muted">untuk 1 jam produksi</small>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <button class="btn btn-outline-primary" onclick="showSimulation()">
                    <i class="fas fa-play me-2"></i>Hitung Simulasi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showSimulation() {
    alert('Fitur simulasi akan segera tersedia');
}
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>Detail BOP Proses
        </h2>
        <div>
            <a href="{{ route('master-data.bop.edit-proses', $bopProses->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i>Edit BOP
            </a>
            <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Proses -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Informasi Proses BTKL
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-semibold">Kode Proses:</td>
                            <td><code>{{ $bopProses->prosesProduksi->kode_proses }}</code></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Nama Proses:</td>
                            <td>{{ $bopProses->prosesProduksi->nama_proses }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Tarif BTKL:</td>
                            <td>Rp {{ number_format($bopProses->prosesProduksi->tarif_per_jam, 0, ',', '.') }}/jam</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Kapasitas Normal:</td>
                            <td><span class="badge bg-info">{{ $bopProses->kapasitas_per_jam }} unit/jam</span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Satuan BTKL:</td>
                            <td>{{ $bopProses->prosesProduksi->satuan_btkl }}</td>
                        </tr>
                        @if($bopProses->prosesProduksi->deskripsi)
                        <tr>
                            <td class="fw-semibold">Deskripsi:</td>
                            <td>{{ $bopProses->prosesProduksi->deskripsi }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Ringkasan BOP -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Ringkasan BOP
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-muted">Total BOP per Jam</h6>
                                <h4 class="text-warning">{{ $bopProses->total_bop_per_jam_formatted }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-muted">BOP per Unit</h6>
                                <h4 class="text-success">{{ $bopProses->bop_per_unit_formatted }}</h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6 class="text-muted">Efisiensi BOP</h6>
                        <div class="progress mb-2">
                            @php
                                $efisiensi = $bopProses->efisiensi_bop;
                                $efisiensiPercent = min(100, $efisiensi * 10); // Scale for display
                            @endphp
                            <div class="progress-bar bg-info" style="width: {{ $efisiensiPercent }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($efisiensi, 2) }} unit per rupiah</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Komponen BOP -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Detail Komponen BOP per Jam
                    </h5>
                </div>
                <div class="card-body">
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
                                @php
                                    $komponenBop = [
                                        'Listrik Mesin' => $bopProses->listrik_per_jam,
                                        'Gas / BBM' => $bopProses->gas_bbm_per_jam,
                                        'Penyusutan Mesin' => $bopProses->penyusutan_mesin_per_jam,
                                        'Maintenance' => $bopProses->maintenance_per_jam,
                                        'Gaji Mandor' => $bopProses->gaji_mandor_per_jam,
                                        'Lain-lain' => $bopProses->lain_lain_per_jam
                                    ];
                                @endphp
                                
                                @foreach($komponenBop as $nama => $nilai)
                                    @if($nilai > 0)
                                        @php
                                            $persentase = $bopProses->total_bop_per_jam > 0 ? ($nilai / $bopProses->total_bop_per_jam) * 100 : 0;
                                            $biayaPerUnit = $bopProses->kapasitas_per_jam > 0 ? $nilai / $bopProses->kapasitas_per_jam : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>
                                                {{ $nama }}
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-semibold">Rp {{ number_format($nilai, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $persentase }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ number_format($persentase, 1) }}%</small>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-muted">Rp {{ number_format($biayaPerUnit, 2, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total BOP</th>
                                    <th class="text-end">{{ $bopProses->total_bop_per_jam_formatted }}</th>
                                    <th class="text-center">100%</th>
                                    <th class="text-end">{{ $bopProses->bop_per_unit_formatted }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simulasi Produksi -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Simulasi Biaya Produksi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Unit yang Diproduksi</label>
                            <input type="number" class="form-control" id="simulasiUnit" value="100" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jam Kerja yang Dibutuhkan</label>
                            <input type="text" class="form-control" id="simulasiJam" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Biaya BOP</label>
                            <input type="text" class="form-control" id="simulasiBiaya" readonly>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="hitungSimulasi()">
                            <i class="fas fa-calculator me-2"></i>Hitung Simulasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function hitungSimulasi() {
    const unit = parseInt(document.getElementById('simulasiUnit').value) || 0;
    const kapasitasPerJam = {{ $bopProses->kapasitas_per_jam }};
    const bopPerJam = {{ $bopProses->total_bop_per_jam }};
    
    if (unit > 0 && kapasitasPerJam > 0) {
        const jamKerja = unit / kapasitasPerJam;
        const totalBiaya = jamKerja * bopPerJam;
        
        document.getElementById('simulasiJam').value = jamKerja.toFixed(2) + ' jam';
        document.getElementById('simulasiBiaya').value = 'Rp ' + totalBiaya.toLocaleString('id-ID');
    }
}

// Auto calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    hitungSimulasi();
    
    // Auto calculate when input changes
    document.getElementById('simulasiUnit').addEventListener('input', hitungSimulasi);
});
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="fas fa-edit me-2"></i>Edit BOP Proses
    </h2>
    <div>
        <a href="{{ route('master-data.bop.show-proses', $bopProses->id) }}" class="btn btn-info">
            <i class="fas fa-eye me-2"></i>Lihat Detail
        </a>
        <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('master-data.bop.update-proses', $bopProses->id) }}" method="POST" id="editBopForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Informasi Proses -->
        <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #8B7355; color: white;">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Proses</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <strong class="text-muted">Kode Proses:</strong><br>
                                <span class="text-primary fw-bold">{{ $bopProses->prosesProduksi->kode_proses }}</span>
                            </div>
                            <div class="col-6">
                                <strong class="text-muted">Nama Proses:</strong><br>
                                <span class="text-dark fw-semibold">{{ $bopProses->prosesProduksi->nama_proses }}</span>
                            </div>
                            <div class="col-6">
                                <strong class="text-muted">Kapasitas:</strong><br>
                                <span class="badge bg-info fs-6">{{ $bopProses->prosesProduksi->kapasitas_per_jam }} unit/jam</span>
                            </div>
                            <div class="col-6">
                                <strong class="text-muted">Status:</strong><br>
                                <span class="badge bg-success">Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan BOP -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #8B7355; color: white;">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Ringkasan BOP</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <strong class="text-muted">Total BOP per Jam:</strong>
                                    <span class="fs-5 text-primary fw-bold" id="totalBopPerJam">Rp {{ number_format($bopProses->total_bop_per_jam, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <strong class="text-muted">BOP per Unit:</strong>
                                    <span class="fs-5 text-success fw-bold" id="bopPerUnit">Rp {{ number_format($bopProses->bop_per_unit, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Edit Komponen BOP -->
        <div class="card shadow-sm mt-4">
            <div class="card-header" style="background-color: #8B7355; color: white;">
                <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Komponen BOP per Jam</h6>
            </div>
            <div class="card-body">
                @php
                    // Get current component values
                    $komponenBop = $bopProses->getAttributes()['komponen_bop'] ?? [];
                    if (is_string($komponenBop)) {
                        $komponenBop = json_decode($komponenBop, true) ?? [];
                    }
                    
                    // Default values if not set
                    $listrik = $komponenBop['listrik_per_jam'] ?? $bopProses->listrik_per_jam ?? 0;
                    $gas = $komponenBop['gas_bbm_per_jam'] ?? $bopProses->gas_bbm_per_jam ?? 0;
                    $penyusutan = $komponenBop['penyusutan_mesin_per_jam'] ?? $bopProses->penyusutan_mesin_per_jam ?? 0;
                    $maintenance = $komponenBop['maintenance_per_jam'] ?? $bopProses->maintenance_per_jam ?? 0;
                    $gaji_mandor = $komponenBop['gaji_mandor_per_jam'] ?? $bopProses->gaji_mandor_per_jam ?? 0;
                    $lain_lain = $komponenBop['lain_lain_per_jam'] ?? $bopProses->lain_lain_per_jam ?? 0;
                @endphp

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="listrik_per_jam" class="form-label">
                            <i class="fas fa-bolt text-warning me-2"></i>Listrik Mesin per Jam
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="komponen_bop[0][rate_per_hour]" 
                                   id="listrik_per_jam" 
                                   class="form-control" 
                                   value="{{ old('komponen_bop.0.rate_per_hour', $listrik) }}"
                                   min="0" 
                                   step="1" 
                                   placeholder="5000"
                                   oninput="calculateTotal()">
                            <input type="hidden" name="komponen_bop[0][component]" value="Listrik Mesin">
                        </div>
                        <small class="text-muted">Biaya listrik untuk operasi mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="gas_bbm_per_jam" class="form-label">
                            <i class="fas fa-fire text-danger me-2"></i>Gas/BBM per Jam
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="komponen_bop[1][rate_per_hour]" 
                                   id="gas_bbm_per_jam" 
                                   class="form-control" 
                                   value="{{ old('komponen_bop.1.rate_per_hour', $gas) }}"
                                   min="0" 
                                   step="1" 
                                   placeholder="20000"
                                   oninput="calculateTotal()">
                            <input type="hidden" name="komponen_bop[1][component]" value="Gas / BBM">
                        </div>
                        <small class="text-muted">Biaya bahan bakar untuk operasi mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="penyusutan_mesin_per_jam" class="form-label">
                            <i class="fas fa-chart-line-down text-secondary me-2"></i>Penyusutan Mesin per Jam
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="komponen_bop[2][rate_per_hour]" 
                                   id="penyusutan_mesin_per_jam" 
                                   class="form-control" 
                                   value="{{ old('komponen_bop.2.rate_per_hour', $penyusutan) }}"
                                   min="0" 
                                   step="1" 
                                   placeholder="10000"
                                   oninput="calculateTotal()">
                            <input type="hidden" name="komponen_bop[2][component]" value="Penyusutan Mesin">
                        </div>
                        <small class="text-muted">Biaya penyusutan mesin per jam operasi</small>
                    </div>

                    <div class="col-md-6">
                        <label for="maintenance_per_jam" class="form-label">
                            <i class="fas fa-tools text-info me-2"></i>Maintenance per Jam
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="komponen_bop[3][rate_per_hour]" 
                                   id="maintenance_per_jam" 
                                   class="form-control" 
                                   value="{{ old('komponen_bop.3.rate_per_hour', $maintenance) }}"
                                   min="0" 
                                   step="1" 
                                   placeholder="5000"
            </div>
        </div>
    </div>
</form>

<script>
function calculateTotal() {
    const listrik = parseFloat(document.getElementById('listrik_per_jam').value) || 0;
    const gas = parseFloat(document.getElementById('gas_bbm_per_jam').value) || 0;
    const penyusutan = parseFloat(document.getElementById('penyusutan_mesin_per_jam').value) || 0;
    const maintenance = parseFloat(document.getElementById('maintenance_per_jam').value) || 0;
    const gaji_mandor = parseFloat(document.getElementById('gaji_mandor_per_jam').value) || 0;
    const lain_lain = parseFloat(document.getElementById('lain_lain_per_jam').value) || 0;
    
    const total = listrik + gas + penyusutan + maintenance + gaji_mandor + lain_lain;
    const kapasitas = {{ $bopProses->prosesProduksi->kapasitas_per_jam ?? 50 }};
    const bopPerUnit = kapasitas > 0 ? total / kapasitas : 0;
    
    document.getElementById('totalBopPerJam').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('bopPerUnit').textContent = 'Rp ' + Math.round(bopPerUnit).toLocaleString('id-ID');
}

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>
@endsection
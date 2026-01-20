@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>Edit BOP Proses
        </h2>
        <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>Form Edit BOP Proses
            </h5>
            <small class="text-muted">Edit komponen BOP per jam untuk proses: <strong>{{ $bopProses->prosesProduksi->nama_proses }}</strong></small>
        </div>
        <div class="card-body">
            <style>
                .form-floating > .form-control:focus ~ label,
                .form-floating > .form-control:not(:placeholder-shown) ~ label {
                    opacity: .65;
                    transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
                }
            </style>
            
            <form action="{{ route('master-data.bop.update-proses', $bopProses->id) }}" method="POST" id="editBopForm">
                @csrf
                @method('PUT')
                
                <!-- Info Proses BTKL (Read-only) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-cogs me-2"></i>Informasi Proses BTKL
                                </h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Kode Proses:</small>
                                        <div class="fw-semibold">{{ $bopProses->prosesProduksi->kode_proses }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Nama Proses:</small>
                                        <div class="fw-semibold">{{ $bopProses->prosesProduksi->nama_proses }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Tarif BTKL:</small>
                                        <div class="fw-semibold">Rp {{ number_format($bopProses->prosesProduksi->tarif_per_jam, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Kapasitas/Jam:</small>
                                        <div class="fw-semibold text-info" id="kapasitasInfo">{{ $bopProses->prosesProduksi->kapasitas_per_jam }} unit/jam</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Komponen BOP per Jam -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-3">
                            <i class="fas fa-list me-2"></i>Komponen BOP per Jam (Rp)
                        </h6>
                    </div>
                </div>

                <div class="row">
                    <!-- Listrik Mesin -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('listrik_per_jam') is-invalid @enderror" 
                                   id="listrik_per_jam" 
                                   name="listrik_per_jam" 
                                   value="{{ old('listrik_per_jam', $bopProses->listrik_per_jam) }}" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="listrik_per_jam">
                                <i class="fas fa-bolt text-warning me-1"></i>Listrik Mesin per Jam *
                            </label>
                            @error('listrik_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Gas/BBM -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('gas_bbm_per_jam') is-invalid @enderror" 
                                   id="gas_bbm_per_jam" 
                                   name="gas_bbm_per_jam" 
                                   value="{{ old('gas_bbm_per_jam', $bopProses->gas_bbm_per_jam) }}" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="gas_bbm_per_jam">
                                <i class="fas fa-fire text-danger me-1"></i>Gas / BBM per Jam *
                            </label>
                            @error('gas_bbm_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Penyusutan Mesin -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('penyusutan_mesin_per_jam') is-invalid @enderror" 
                                   id="penyusutan_mesin_per_jam" 
                                   name="penyusutan_mesin_per_jam" 
                                   value="{{ old('penyusutan_mesin_per_jam', $bopProses->penyusutan_mesin_per_jam) }}" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="penyusutan_mesin_per_jam">
                                <i class="fas fa-chart-line-down text-secondary me-1"></i>Penyusutan Mesin per Jam *
                            </label>
                            @error('penyusutan_mesin_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Maintenance -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('maintenance_per_jam') is-invalid @enderror" 
                                   id="maintenance_per_jam" 
                                   name="maintenance_per_jam" 
                                   value="{{ old('maintenance_per_jam', $bopProses->maintenance_per_jam) }}" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="maintenance_per_jam">
                                <i class="fas fa-tools text-primary me-1"></i>Maintenance per Jam *
                            </label>
                            @error('maintenance_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Gaji Mandor -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('gaji_mandor_per_jam') is-invalid @enderror" 
                                   id="gaji_mandor_per_jam" 
                                   name="gaji_mandor_per_jam" 
                                   value="{{ old('gaji_mandor_per_jam', $bopProses->gaji_mandor_per_jam) }}" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="gaji_mandor_per_jam">
                                <i class="fas fa-user-tie text-success me-1"></i>Gaji Mandor per Jam *
                            </label>
                            @error('gaji_mandor_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Lain-lain -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('lain_lain_per_jam') is-invalid @enderror" 
                                   id="lain_lain_per_jam" 
                                   name="lain_lain_per_jam" 
                                   value="{{ old('lain_lain_per_jam', $bopProses->lain_lain_per_jam) }}" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()">
                            <label for="lain_lain_per_jam">
                                <i class="fas fa-ellipsis-h text-muted me-1"></i>Lain-lain per Jam
                            </label>
                            @error('lain_lain_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Summary Perhitungan -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan BOP
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small>Total BOP per Jam:</small>
                                        <div class="h5" id="totalBopPerJam">Rp {{ number_format($bopProses->total_bop_per_jam, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small>Kapasitas per Jam:</small>
                                        <div class="h5" id="kapasitasPerJam">{{ $bopProses->kapasitas_per_jam }} unit</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small>BOP per Unit:</small>
                                        <div class="h5" id="bopPerUnit">Rp {{ number_format($bopProses->bop_per_unit, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update BOP Proses
                        </button>
                        <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculateTotal() {
    const listrik = parseFloat(document.getElementById('listrik_per_jam').value) || 0;
    const gas = parseFloat(document.getElementById('gas_bbm_per_jam').value) || 0;
    const penyusutan = parseFloat(document.getElementById('penyusutan_mesin_per_jam').value) || 0;
    const maintenance = parseFloat(document.getElementById('maintenance_per_jam').value) || 0;
    const mandor = parseFloat(document.getElementById('gaji_mandor_per_jam').value) || 0;
    const lainLain = parseFloat(document.getElementById('lain_lain_per_jam').value) || 0;
    
    const total = listrik + gas + penyusutan + maintenance + mandor + lainLain;
    const kapasitas = {{ $bopProses->prosesProduksi->kapasitas_per_jam }};
    const bopPerUnit = kapasitas > 0 ? total / kapasitas : 0;
    
    document.getElementById('totalBopPerJam').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('kapasitasPerJam').textContent = kapasitas + ' unit';
    document.getElementById('bopPerUnit').textContent = 'Rp ' + bopPerUnit.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>
@endsection
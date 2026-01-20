@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">
            <i class="fas fa-chart-pie me-2"></i>Edit BOP Proses
        </h2>
        <a href="{{ route('master-data.bop-terpadu.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP Terpadu
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body" style="color: white !important;">
            <style>
                .card-body input, .card-body select, .card-body textarea {
                    color: white !important;
                    background-color: rgba(0,0,0,0.8) !important;
                    border: 1px solid rgba(255,255,255,0.3) !important;
                }
                .card-body input::placeholder, .card-body textarea::placeholder {
                    color: rgba(255,255,255,0.7) !important;
                }
                .card-body .input-group-text {
                    color: white !important;
                    background-color: rgba(0,0,0,0.6) !important;
                    border-color: rgba(255,255,255,0.3) !important;
                }
                .card-body .form-control, .card-body .form-select {
                    border-color: rgba(255,255,255,0.3) !important;
                }
                .card-body .form-control:focus {
                    background-color: rgba(0,0,0,0.9) !important;
                    border-color: #007bff !important;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
                }
                .info-card {
                    background: rgba(0,123,255,0.1);
                    border: 1px solid rgba(0,123,255,0.3);
                    border-radius: 8px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
            </style>
            
            <form action="{{ route('master-data.bop-terpadu.update-proses', $bopProses->id) }}" method="POST" id="editBopForm">
                @csrf
                @method('PUT')
                
                <!-- Info Proses BTKL -->
                <div class="info-card mb-4">
                    <h6 class="text-info mb-3"><i class="fas fa-info-circle me-2"></i>Proses BTKL Terkait</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Kode Proses:</strong><br>
                            <code class="text-info">{{ $bopProses->prosesProduksi->kode_proses }}</code>
                        </div>
                        <div class="col-md-3">
                            <strong>Nama Proses:</strong><br>
                            <span class="text-info">{{ $bopProses->prosesProduksi->nama_proses }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Kapasitas per Jam:</strong><br>
                            <span class="text-info">{{ $bopProses->prosesProduksi->kapasitas_per_jam }}</span> unit/jam
                        </div>
                        <div class="col-md-3">
                            <strong>BTKL per Unit:</strong><br>
                            <span class="text-info">{{ $bopProses->prosesProduksi->biaya_per_produk_formatted }}</span>
                        </div>
                    </div>
                </div>

                <!-- Komponen BOP per Jam -->
                <div class="row g-3">
                    <div class="col-12">
                        <h5 class="text-white mb-3">
                            <i class="fas fa-cogs me-2"></i>Komponen BOP per Jam Mesin
                        </h5>
                        <small class="text-light">Masukkan biaya overhead pabrik per jam operasi mesin</small>
                    </div>

                    <div class="col-md-6">
                        <label for="listrik_per_jam" class="form-label text-white">Listrik Mesin per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="listrik_per_jam" 
                                   id="listrik_per_jam" 
                                   class="form-control bop-component @error('listrik_per_jam') is-invalid @enderror" 
                                   value="{{ old('listrik_per_jam', $bopProses->listrik_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="5000"
                                   required>
                        </div>
                        @error('listrik_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya listrik untuk operasi mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="gas_bbm_per_jam" class="form-label text-white">Gas/BBM per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="gas_bbm_per_jam" 
                                   id="gas_bbm_per_jam" 
                                   class="form-control bop-component @error('gas_bbm_per_jam') is-invalid @enderror" 
                                   value="{{ old('gas_bbm_per_jam', $bopProses->gas_bbm_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="3000"
                                   required>
                        </div>
                        @error('gas_bbm_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya bahan bakar untuk operasi mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="penyusutan_mesin_per_jam" class="form-label text-white">Penyusutan Mesin per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="penyusutan_mesin_per_jam" 
                                   id="penyusutan_mesin_per_jam" 
                                   class="form-control bop-component @error('penyusutan_mesin_per_jam') is-invalid @enderror" 
                                   value="{{ old('penyusutan_mesin_per_jam', $bopProses->penyusutan_mesin_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="2000"
                                   required>
                        </div>
                        @error('penyusutan_mesin_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Alokasi penyusutan mesin per jam operasi</small>
                    </div>

                    <div class="col-md-6">
                        <label for="maintenance_per_jam" class="form-label text-white">Maintenance per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="maintenance_per_jam" 
                                   id="maintenance_per_jam" 
                                   class="form-control bop-component @error('maintenance_per_jam') is-invalid @enderror" 
                                   value="{{ old('maintenance_per_jam', $bopProses->maintenance_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="1500"
                                   required>
                        </div>
                        @error('maintenance_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya perawatan dan maintenance mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="gaji_mandor_per_jam" class="form-label text-white">Gaji Mandor per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="gaji_mandor_per_jam" 
                                   id="gaji_mandor_per_jam" 
                                   class="form-control bop-component @error('gaji_mandor_per_jam') is-invalid @enderror" 
                                   value="{{ old('gaji_mandor_per_jam', $bopProses->gaji_mandor_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="4000"
                                   required>
                        </div>
                        @error('gaji_mandor_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Alokasi gaji mandor/supervisor per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="lain_lain_per_jam" class="form-label text-white">Lain-lain per Jam</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="lain_lain_per_jam" 
                                   id="lain_lain_per_jam" 
                                   class="form-control bop-component @error('lain_lain_per_jam') is-invalid @enderror" 
                                   value="{{ old('lain_lain_per_jam', $bopProses->lain_lain_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="1000">
                        </div>
                        @error('lain_lain_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya overhead lainnya per jam (opsional)</small>
                    </div>
                </div>

                <!-- Ringkasan Perhitungan -->
                <div class="info-card mt-4">
                    <h6 class="text-warning mb-3"><i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total BOP per Jam:</strong><br>
                            <span class="fs-5 text-warning">Rp <span id="totalBopPerJam">{{ number_format($bopProses->total_bop_per_jam, 0, ',', '.') }}</span></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Kapasitas per Jam:</strong><br>
                            <span class="fs-5 text-info">{{ $bopProses->kapasitas_per_jam }} unit</span>
                        </div>
                        <div class="col-md-4">
                            <strong>BOP per Unit:</strong><br>
                            <span class="fs-5 text-success">Rp <span id="bopPerUnit">{{ number_format($bopProses->bop_per_unit, 2, ',', '.') }}</span></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update BOP Proses
                    </button>
                    <a href="{{ route('master-data.bop-terpadu.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bopComponents = document.querySelectorAll('.bop-component');
    const kapasitas = {{ $bopProses->kapasitas_per_jam }};
    
    // Update calculation when BOP components change
    bopComponents.forEach(function(input) {
        input.addEventListener('input', updateCalculation);
    });
    
    function updateCalculation() {
        // Calculate total BOP per jam
        let totalBopPerJam = 0;
        bopComponents.forEach(function(input) {
            totalBopPerJam += parseFloat(input.value) || 0;
        });
        
        // Calculate BOP per unit
        const bopPerUnit = kapasitas > 0 ? totalBopPerJam / kapasitas : 0;
        
        // Update display
        document.getElementById('totalBopPerJam').textContent = totalBopPerJam.toLocaleString('id-ID');
        document.getElementById('bopPerUnit').textContent = bopPerUnit.toLocaleString('id-ID', {minimumFractionDigits: 2});
    }
    
    // Form submission
    const form = document.getElementById('editBopForm');
    const submitBtn = document.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});
</script>
@endsection
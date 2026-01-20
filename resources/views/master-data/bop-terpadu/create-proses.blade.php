@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">
            <i class="fas fa-chart-pie me-2"></i>Tambah BOP Proses
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
            
            <form action="{{ route('master-data.bop-terpadu.store-proses') }}" method="POST" id="createBopForm">
                @csrf
                
                <!-- Pilih Proses BTKL -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="proses_produksi_id" class="form-label text-white">Pilih Proses BTKL <span class="text-danger">*</span></label>
                        <select name="proses_produksi_id" id="proses_produksi_id" class="form-select @error('proses_produksi_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Proses BTKL --</option>
                            @foreach($availableProses as $proses)
                                <option value="{{ $proses->id }}" 
                                        data-kapasitas="{{ $proses->kapasitas_per_jam }}"
                                        data-tarif="{{ $proses->tarif_btkl }}"
                                        data-biaya-per-unit="{{ $proses->biaya_per_produk }}"
                                        {{ old('proses_produksi_id') == $proses->id ? 'selected' : '' }}>
                                    {{ $proses->kode_proses }} - {{ $proses->nama_proses }} 
                                    ({{ $proses->kapasitas_per_jam }} unit/jam)
                                </option>
                            @endforeach
                        </select>
                        @error('proses_produksi_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Hanya proses BTKL yang memiliki kapasitas per jam dan belum memiliki BOP</small>
                    </div>
                </div>

                <!-- Info Proses Terpilih -->
                <div id="prosesInfo" class="info-card" style="display: none;">
                    <h6 class="text-info mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Proses Terpilih</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Kapasitas per Jam:</strong><br>
                            <span id="infoKapasitas" class="text-info">-</span> unit/jam
                        </div>
                        <div class="col-md-4">
                            <strong>Tarif BTKL:</strong><br>
                            Rp <span id="infoTarif" class="text-info">-</span>/jam
                        </div>
                        <div class="col-md-4">
                            <strong>BTKL per Unit:</strong><br>
                            Rp <span id="infoBiayaPerUnit" class="text-info">-</span>/unit
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
                                   value="{{ old('listrik_per_jam', 0) }}"
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
                                   value="{{ old('gas_bbm_per_jam', 0) }}"
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
                                   value="{{ old('penyusutan_mesin_per_jam', 0) }}"
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
                                   value="{{ old('maintenance_per_jam', 0) }}"
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
                                   value="{{ old('gaji_mandor_per_jam', 0) }}"
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
                                   value="{{ old('lain_lain_per_jam', 0) }}"
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
                            <span class="fs-5 text-warning">Rp <span id="totalBopPerJam">0</span></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Kapasitas per Jam:</strong><br>
                            <span class="fs-5 text-info"><span id="kapasitasPerJam">0</span> unit</span>
                        </div>
                        <div class="col-md-4">
                            <strong>BOP per Unit:</strong><br>
                            <span class="fs-5 text-success">Rp <span id="bopPerUnit">0.00</span></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan BOP Proses
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
    const prosesSelect = document.getElementById('proses_produksi_id');
    const prosesInfo = document.getElementById('prosesInfo');
    const bopComponents = document.querySelectorAll('.bop-component');
    
    // Update info when process is selected
    prosesSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const kapasitas = selectedOption.dataset.kapasitas;
            const tarif = selectedOption.dataset.tarif;
            const biayaPerUnit = selectedOption.dataset.biayaPerUnit;
            
            document.getElementById('infoKapasitas').textContent = parseInt(kapasitas).toLocaleString('id-ID');
            document.getElementById('infoTarif').textContent = parseInt(tarif).toLocaleString('id-ID');
            document.getElementById('infoBiayaPerUnit').textContent = parseFloat(biayaPerUnit).toLocaleString('id-ID', {minimumFractionDigits: 2});
            document.getElementById('kapasitasPerJam').textContent = parseInt(kapasitas).toLocaleString('id-ID');
            
            prosesInfo.style.display = 'block';
            updateCalculation();
        } else {
            prosesInfo.style.display = 'none';
        }
    });
    
    // Update calculation when BOP components change
    bopComponents.forEach(function(input) {
        input.addEventListener('input', updateCalculation);
    });
    
    function updateCalculation() {
        const selectedOption = prosesSelect.options[prosesSelect.selectedIndex];
        
        if (!prosesSelect.value) return;
        
        const kapasitas = parseInt(selectedOption.dataset.kapasitas) || 0;
        
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
    const form = document.getElementById('createBopForm');
    const submitBtn = document.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
    
    // Initialize if there's old input
    if (prosesSelect.value) {
        prosesSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
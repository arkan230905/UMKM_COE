@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>Tambah BOP Proses
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
                <i class="fas fa-plus me-2"></i>Form Tambah BOP Proses
            </h5>
            <small class="text-muted">Input komponen BOP per jam untuk proses produksi</small>
        </div>
        <div class="card-body">
            <style>
                .form-floating > .form-control:focus ~ label,
                .form-floating > .form-control:not(:placeholder-shown) ~ label {
                    opacity: .65;
                    transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
                }
            </style>
            
            <form action="{{ route('master-data.bop.store-proses') }}" method="POST" id="createBopForm">
                @csrf
                
                <!-- Pilih Proses BTKL -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-cogs me-2"></i>Pilih Proses BTKL
                                </h6>
                                <div class="form-floating">
                                    <select class="form-select @error('proses_produksi_id') is-invalid @enderror" 
                                            id="proses_produksi_id" 
                                            name="proses_produksi_id' 
                                            required
                                            onchange="updateProsesInfo()">
                                        <option value="">Pilih Proses BTKL</option>
                                        @foreach($availableProses as $proses)
                                            <option value="{{ $proses->id }}" 
                                                    data-kode="{{ $proses->kode_proses }}"
                                                    data-nama="{{ $proses->nama_proses }}"
                                                    data-tarif="{{ $proses->tarif_per_jam }}"
                                                    data-kapasitas="{{ $proses->kapasitas_per_jam }}"
                                                    data-satuan="{{ $proses->satuan_btkl }}"
                                                    {{ old('proses_produksi_id') == $proses->id ? 'selected' : '' }}>
                                                {{ $proses->kode_proses }} - {{ $proses->nama_proses }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="proses_produksi_id">Proses BTKL *</label>
                                    @error('proses_produksi_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Info Proses -->
                                <div id="prosesInfo" class="mt-3" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Kode Proses:</small>
                                            <div class="fw-semibold" id="infokode">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Tarif BTKL:</small>
                                            <div class="fw-semibold" id="infotarif">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Kapasitas/Jam:</small>
                                            <div class="fw-semibold text-info" id="infokapasitas">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Satuan:</small>
                                            <div class="fw-semibold" id="infosatuan">-</div>
                                        </div>
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
                        <small class="text-muted">Pilih akun beban dan masukkan nominal per jam</small>
                    </div>
                </div>

                <!-- Dynamic BOP Components -->
                <div id="bopComponentsContainer">
                    @foreach($akunBeban as $index => $akun)
                        <div class="row mb-3 bop-component-row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="akun_beban[]" required>
                                        <option value="{{ $akun->kode_akun }}" selected>{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                                    </select>
                                    <label for="akun_beban_{{ $index }}">
                                        <i class="fas fa-coins text-warning me-1"></i>{{ $akun->nama_akun }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" 
                                           class="form-control @error('nominal_per_jam.' . $index) is-invalid @enderror" 
                                           id="nominal_per_jam_{{ $index }}" 
                                           name="nominal_per_jam[]" 
                                           value="{{ old('nominal_per_jam.' . $index, 0) }}" 
                                           min="0" 
                                           step="1000"
                                           placeholder="0"
                                           oninput="calculateTotal()">
                                    <label for="nominal_per_jam_{{ $index }}">
                                        <i class="fas fa-calculator text-primary me-1"></i>Nominal per Jam (Rp)
                                    </label>
                                    @error('nominal_per_jam.' . $index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Total Calculation -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="mb-0">Total BOP per Jam:</h6>
                                        <div class="fs-4 text-primary fw-bold" id="totalPerJam">Rp 0</div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-0">Budget (8 jam/shift):</h6>
                                        <div class="fs-4 text-success fw-bold" id="totalBudget">Rp 0</div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-0">Per Shift:</h6>
                                        <div class="text-muted">8 jam</div>
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
                            <i class="fas fa-save"></i> Simpan BOP Proses
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
function updateProsesInfo() {
    const select = document.getElementById('proses_produksi_id');
    const selectedOption = select.options[select.selectedIndex];
    const prosesInfo = document.getElementById('prosesInfo');
    
    if (selectedOption.value) {
        document.getElementById('infokode').textContent = selectedOption.dataset.kode;
        document.getElementById('infotarif').textContent = 'Rp ' + parseInt(selectedOption.dataset.tarif).toLocaleString('id-ID');
        document.getElementById('infokapasitas').textContent = selectedOption.dataset.kapasitas + ' unit/jam';
        document.getElementById('infosatuan').textContent = selectedOption.dataset.satuan;
        
        prosesInfo.style.display = 'block';
        calculateTotal();
    } else {
        prosesInfo.style.display = 'none';
    }
}

function calculateTotal() {
    // Get all nominal inputs
    const nominalInputs = document.querySelectorAll('input[name="nominal_per_jam[]"]');
    let total = 0;
    
    nominalInputs.forEach(function(input) {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    // Update displays
    document.getElementById('totalPerJam').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('totalBudget').textContent = 'Rp ' + (total * 8).toLocaleString('id-ID');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProsesInfo();
});
</script>
@endsection

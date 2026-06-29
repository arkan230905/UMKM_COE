@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h2 class="mb-4 text-white"><i class="fas fa-chart-pie me-2"></i>Tambah BOP Proses</h2>

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
            
            <form action="{{ route('master-data.bop-proses.store') }}" method="POST" id="createBopForm">
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

                <!-- Komponen BOP - 2 Bagian -->
                <div class="row g-3">
                    <div class="col-12">
                        <h5 class="text-white mb-3">
                            <i class="fas fa-cogs me-2"></i>Komponen BOP per Jam Mesin
                        </h5>
                        <small class="text-light">Masukkan biaya overhead pabrik per jam operasi mesin</small>
                    </div>

                    <!-- BAGIAN 1: BOP PROSES BAHAN PENDUKUNG -->
                    <div class="col-12 mt-4">
                        <div class="card" style="background: rgba(25, 135, 84, 0.1); border: 1px solid rgba(25, 135, 84, 0.3);">
                            <div class="card-header" style="background: rgba(25, 135, 84, 0.2); border-bottom: 1px solid rgba(25, 135, 84, 0.3);">
                                <h6 class="mb-0 text-success">
                                    <i class="fas fa-box me-2"></i>BOP Proses - Bahan Pendukung
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="komponenBahanContainer">
                                    <!-- Dynamic rows for bahan pendukung will be inserted here -->
                                </div>
                                
                                <button type="button" id="addBahanBtn" class="btn btn-success btn-sm mt-3">
                                    <i class="fas fa-plus"></i> Tambah Bahan Pendukung
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 2: BOP PROSES LAINNYA -->
                    <div class="col-12 mt-4">
                        <div class="card" style="background: rgba(0, 123, 255, 0.1); border: 1px solid rgba(0, 123, 255, 0.3);">
                            <div class="card-header" style="background: rgba(0, 123, 255, 0.2); border-bottom: 1px solid rgba(0, 123, 255, 0.3);">
                                <h6 class="mb-0 text-info">
                                    <i class="fas fa-tools me-2"></i>BOP Proses - Lainnya
                                </h6>
                                <small class="text-light">Komponen BOP lainnya (Listrik, Gas, Penyusutan, dll)</small>
                            </div>
                            <div class="card-body">
                                <div id="komponenLainContainer">
                                    <!-- Dynamic rows for lainnya will be inserted here -->
                                </div>
                                
                                <button type="button" id="addLainBtn" class="btn btn-info btn-sm mt-3">
                                    <i class="fas fa-plus"></i> Tambah Komponen Lain
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Perhitungan -->
                <div class="info-card mt-4">
                    <h6 class="text-warning mb-3"><i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Total BOP per Jam:</strong><br>
                            <span class="fs-5 text-warning">Rp <span id="totalBopPerJam">0</span></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Budget per Shift (8 jam):</strong><br>
                            <span class="fs-5 text-info">Rp <span id="budgetShift">0</span></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Kapasitas per Jam:</strong><br>
                            <span class="fs-5 text-info"><span id="kapasitasPerJam">0</span> unit</span>
                        </div>
                        <div class="col-md-3">
                            <strong>BOP per Unit:</strong><br>
                            <span class="fs-5 text-success">Rp <span id="bopPerUnit">0.00</span></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan BOP Proses
                    </button>
                    <a href="{{ route('master-data.bop-proses.index') }}" class="btn btn-secondary">
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
    const komponenBahanContainer = document.getElementById('komponenBahanContainer');
    const komponenLainContainer = document.getElementById('komponenLainContainer');
    const addBahanBtn = document.getElementById('addBahanBtn');
    const addLainBtn = document.getElementById('addLainBtn');
    
    // Bahan Pendukung dari server (filtered by user_id)
    const bahanPendukungs = @json($bahanPendukungs ?? []);
    
    // Static list of BOP components for "Lainnya"
    const komponenLainOptions = [
        'Listrik Mesin',
        'Gas / BBM',
        'Penyusutan Mesin',
        'Maintenance',
        'Air & Kebersihan',
        'Gaji Mandor',
        'Lain-lain'
    ];
    
    let bahanCount = 0;
    let lainCount = 0;
    
    // Add initial empty rows
    addBahanRow();
    addLainRow();
    
    // ===== BAGIAN 1: BAHAN PENDUKUNG =====
    function addBahanRow(bahanId = '', rate = '') {
        bahanCount++;
        const rowId = `bahan_${bahanCount}`;
        
        const rowHtml = `
            <div class="row g-3 mb-3 komponen-row" id="${rowId}">
                <div class="col-md-5">
                    <label class="form-label text-white">Bahan Pendukung</label>
                    <select name="komponen_bop[${rowId}][bahan_pendukung_id]" 
                            class="form-select bahan-select" 
                            data-row-id="${rowId}"
                            data-type="bahan">
                        <option value="">-- Pilih Bahan Pendukung --</option>
                        ${bahanPendukungs.map(bahan => 
                            `<option value="${bahan.id}" ${bahanId == bahan.id ? 'selected' : ''}>${bahan.nama_bahan}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-white">Nominal per Jam (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" 
                               name="komponen_bop[${rowId}][rate_per_hour]" 
                               class="form-control rate-input" 
                               data-row-id="${rowId}"
                               value="${rate}"
                               min="0" 
                               step="0.01" 
                               placeholder="0">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-white">&nbsp;</label><br>
                    <button type="button" class="btn btn-danger btn-sm w-100 delete-row" data-row-id="${rowId}">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        `;
        
        komponenBahanContainer.insertAdjacentHTML('beforeend', rowHtml);
        
        // Add event listeners
        const newRow = document.getElementById(rowId);
        const select = newRow.querySelector('.bahan-select');
        const input = newRow.querySelector('.rate-input');
        const deleteBtn = newRow.querySelector('.delete-row');
        
        select.addEventListener('change', updateCalculation);
        input.addEventListener('input', updateCalculation);
        deleteBtn.addEventListener('click', function() {
            deleteRow(rowId);
        });
        
        updateCalculation();
    }
    
    // ===== BAGIAN 2: LAINNYA =====
    function addLainRow(component = '', rate = '') {
        lainCount++;
        const rowId = `lain_${lainCount}`;
        
        const rowHtml = `
            <div class="row g-3 mb-3 komponen-row" id="${rowId}">
                <div class="col-md-5">
                    <label class="form-label text-white">Komponen BOP</label>
                    <select name="komponen_bop[${rowId}][component]" 
                            class="form-select lain-select" 
                            data-row-id="${rowId}"
                            data-type="lain">
                        <option value="">-- Pilih Komponen --</option>
                        ${komponenLainOptions.map(opt => 
                            `<option value="${opt}" ${component === opt ? 'selected' : ''}>${opt}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-white">Nominal per Jam (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" 
                               name="komponen_bop[${rowId}][rate_per_hour]" 
                               class="form-control rate-input" 
                               data-row-id="${rowId}"
                               value="${rate}"
                               min="0" 
                               step="0.01" 
                               placeholder="0">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-white">&nbsp;</label><br>
                    <button type="button" class="btn btn-danger btn-sm w-100 delete-row" data-row-id="${rowId}">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        `;
        
        komponenLainContainer.insertAdjacentHTML('beforeend', rowHtml);
        
        // Add event listeners
        const newRow = document.getElementById(rowId);
        const select = newRow.querySelector('.lain-select');
        const input = newRow.querySelector('.rate-input');
        const deleteBtn = newRow.querySelector('.delete-row');
        
        select.addEventListener('change', updateCalculation);
        input.addEventListener('input', updateCalculation);
        deleteBtn.addEventListener('click', function() {
            deleteRow(rowId);
        });
        
        updateCalculation();
    }
    
    // Delete row function
    function deleteRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            updateCalculation();
        }
    }
    
    // Add button events
    addBahanBtn.addEventListener('click', function() {
        addBahanRow();
    });
    
    addLainBtn.addEventListener('click', function() {
        addLainRow();
    });
    
    // Update info when process is selected
    prosesSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const kapasitas = selectedOption.dataset.kapasitas;
            const tarif = selectedOption.dataset.tarif;
            const biayaPerUnit = selectedOption.dataset.biayaPerUnit;
            
            document.getElementById('infoKapasitas').textContent = parseInt(kapasitas).toLocaleString('id-ID');
            document.getElementById('infoTarif').textContent = parseInt(tarif).toLocaleString('id-ID');
            document.getElementById('infoBiayaPerUnit').textContent = formatRupiahClean(parseFloat(biayaPerUnit));
            document.getElementById('kapasitasPerJam').textContent = parseInt(kapasitas).toLocaleString('id-ID');
            
            prosesInfo.style.display = 'block';
            updateCalculation();
        } else {
            prosesInfo.style.display = 'none';
        }
    });
    
    // Update calculation function
    function updateCalculation() {
        const selectedOption = prosesSelect.options[prosesSelect.selectedIndex];
        
        if (!prosesSelect.value) return;
        
        const kapasitas = parseInt(selectedOption.dataset.kapasitas) || 0;
        
        // Calculate total BOP per jam from ALL rate inputs (both sections)
        let totalBopPerJam = 0;
        const rateInputs = document.querySelectorAll('.rate-input');
        rateInputs.forEach(function(input) {
            totalBopPerJam += parseFloat(input.value) || 0;
        });
        
        // Calculate budget per shift (8 jam)
        const budgetShift = totalBopPerJam * 8;
        
        // Calculate BOP per unit
        const bopPerUnit = kapasitas > 0 ? totalBopPerJam / kapasitas : 0;
        
        // Update display
        document.getElementById('totalBopPerJam').textContent = totalBopPerJam.toLocaleString('id-ID');
        document.getElementById('budgetShift').textContent = budgetShift.toLocaleString('id-ID');
        document.getElementById('bopPerUnit').textContent = formatRupiahClean(bopPerUnit);
    }
    
    // Clean number formatting function
    function formatNumberClean(number) {
        if (number == Math.floor(number)) {
            return number.toLocaleString('id-ID');
        }
        let formatted = number.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        // Remove trailing zeros after decimal
        if (formatted.includes(',')) {
            formatted = formatted.replace(/,?0+$/, '');
        }
        return formatted;
    }
    
    // Clean rupiah formatting function  
    function formatRupiahClean(number) {
        return 'Rp ' + formatNumberClean(number);
    }
    
    // Form submission
    const form = document.getElementById('createBopForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        // Validate at least one component is filled
        const rateInputs = document.querySelectorAll('.rate-input');
        let hasValidComponent = false;
        
        rateInputs.forEach(function(input) {
            if (parseFloat(input.value) > 0) {
                hasValidComponent = true;
            }
        });
        
        if (!hasValidComponent) {
            e.preventDefault();
            alert('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            return;
        }
        
        // Check for duplicate bahan pendukung
        const selectedBahan = [];
        const bahanSelects = document.querySelectorAll('.bahan-select');
        let hasDuplicateBahan = false;
        
        bahanSelects.forEach(function(select) {
            const value = select.value;
            if (value && selectedBahan.includes(value)) {
                hasDuplicateBahan = true;
            } else if (value) {
                selectedBahan.push(value);
            }
        });
        
        if (hasDuplicateBahan) {
            e.preventDefault();
            alert('Bahan Pendukung tidak boleh duplikat.');
            return;
        }
        
        // Check for duplicate lainnya components
        const selectedLain = [];
        const lainSelects = document.querySelectorAll('.lain-select');
        let hasDuplicateLain = false;
        
        lainSelects.forEach(function(select) {
            const value = select.value;
            if (value && selectedLain.includes(value)) {
                hasDuplicateLain = true;
            } else if (value) {
                selectedLain.push(value);
            }
        });
        
        if (hasDuplicateLain) {
            e.preventDefault();
            alert('Komponen BOP Lainnya tidak boleh duplikat.');
            return;
        }
        
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
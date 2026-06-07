@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h2 class="mb-4 text-white"><i class="fas fa-chart-pie me-2"></i>Edit BOP Proses</h2>

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
            
            <form action="{{ route('master-data.bop-proses.update', $bopProses->id) }}" method="POST" id="editBopForm">
                @csrf
                @method('PATCH')
                
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
                                <small class="text-light">Pilih bahan pendukung dari database</small>
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
                            <span class="fs-5 text-warning">Rp <span id="totalBopPerJam">{{ number_format($bopProses->total_bop_per_jam, 0, ',', '.') }}</span></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Budget per Shift (8 jam):</strong><br>
                            <span class="fs-5 text-info">Rp <span id="budgetShift">{{ number_format($bopProses->budget ?? ($bopProses->total_bop_per_jam * 8), 0, ',', '.') }}</span></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Kapasitas per Jam:</strong><br>
                            <span class="fs-5 text-info">{{ $bopProses->kapasitas_per_jam }} unit</span>
                        </div>
                        <div class="col-md-3">
                            <strong>BOP per Unit:</strong><br>
                            <span class="fs-5 text-success">{{ format_rupiah_clean($bopProses->bop_per_unit) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update BOP Proses
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
    
    // Existing components from server
    const existingComponents = @json($bopProses->komponen_bop ?? []);
    const kapasitas = {{ $bopProses->kapasitas_per_jam }};
    
    let bahanCount = 0;
    let lainCount = 0;
    
    // Load existing components and categorize them
    if (existingComponents && existingComponents.length > 0) {
        existingComponents.forEach(function(component) {
            // Check if component is a bahan pendukung (has bahan_pendukung_id)
            if (component.bahan_pendukung_id) {
                addBahanRow(component.bahan_pendukung_id, component.rate_per_hour);
            } else {
                // It's a "lainnya" component
                addLainRow(component.component, component.rate_per_hour);
            }
        });
    }
    
    // If no existing components, add initial empty rows
    if (existingComponents.length === 0) {
        addBahanRow();
        addLainRow();
    }
    
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
                            data-type="bahan"
                            required>
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
                               placeholder="0"
                               required>
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
                            data-type="lain"
                            required>
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
                               placeholder="0"
                               required>
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
    
    // Update calculation function
    function updateCalculation() {
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
        
        // Update display with clean formatting
        document.getElementById('totalBopPerJam').textContent = formatNumberClean(totalBopPerJam);
        document.getElementById('budgetShift').textContent = formatNumberClean(budgetShift);
        
        // Update BOP per unit display if element exists
        const bopPerUnitElement = document.getElementById('bopPerUnit');
        if (bopPerUnitElement) {
            bopPerUnitElement.textContent = formatRupiahClean(bopPerUnit);
        }
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
    const form = document.getElementById('editBopForm');
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
});
</script>
@endsection
@extends('layouts.app')

@section('title', 'Edit BOP Proses')

@section('content')
<div class="container-fluid px-4 py-4">
    <h2 class="mb-4 text-white"><i class="fas fa-edit me-2"></i>Edit BOP Proses</h2>

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
                    border-radius: 10px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
                .bop-summary {
                    background: rgba(40,167,69,0.1);
                    border: 1px solid rgba(40,167,69,0.3);
                    border-radius: 10px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
            </style>
            
            <form action="{{ route('master-data.bop.update-proses-post', $bopProses->id) }}?v={{ time() }}" method="POST" id="editBopForm">
                @csrf
                
                <!-- Debug: Show form action -->
                <div style="display:none;">
                    Form Action: {{ route('master-data.bop.update-proses-post', $bopProses->id) }}
                    BOP ID: {{ $bopProses->id }}
                    Timestamp: {{ time() }}
                </div>
                
                <!-- Proses Produksi Info -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-industry me-2"></i>Proses Produksi</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-white-50">Nama Proses</label>
                            <div class="form-control bg-dark text-white">
                                {{ $bopProses->prosesProduksi->nama_proses }}
                            </div>
                            <small class="text-white-50">
                                Kapasitas {{ $bopProses->prosesProduksi->kapasitas_per_jam }} pcs/jam, 
                                Tarif Rp {{ formatNumberClean($bopProses->prosesProduksi->tarif_per_jam) }}/jam, 
                                BTKL per pcs Rp {{ formatNumberClean($bopProses->prosesProduksi->btkl_per_pcs) }}
                            </small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Kapasitas (pcs/jam)</label>
                            <input type="text" class="form-control bg-dark text-white" 
                                   value="{{ formatNumberClean($bopProses->prosesProduksi->kapasitas_per_jam) }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50">BTKL / Jam</label>
                            <input type="text" class="form-control bg-dark text-white" 
                                   value="{{ formatNumberClean($bopProses->prosesProduksi->tarif_per_jam) }}" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label text-white-50">BTKL / produk</label>
                            <input type="text" class="form-control bg-dark text-white" 
                                   id="btklPerProdukDisplay"
                                   value="{{ formatNumberClean($bopProses->prosesProduksi->btkl_per_pcs) }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Komponen BOP -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Komponen BOP</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Komponen</th>
                                    <th>Rp / produk</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Get current component values
                                    $komponenBop = $bopProses->komponen_bop ?? [];
                                    if (is_string($komponenBop)) {
                                        $komponenBop = json_decode($komponenBop, true) ?? [];
                                    }
                                    
                                    // Debug: Log komponen structure
                                    \Log::info('BOP Edit - Komponen BOP Structure:', [
                                        'type' => gettype($komponenBop),
                                        'data' => $komponenBop
                                    ]);
                                    
                                    // Create a map of component name => rate for easier lookup
                                    $komponenMap = [];
                                    foreach ($komponenBop as $komp) {
                                        if (isset($komp['component']) && isset($komp['rate_per_hour'])) {
                                            $komponenMap[$komp['component']] = $komp['rate_per_hour'];
                                        }
                                    }
                                    
                                    $components = [
                                        ['name' => 'Listrik Mixer', 'field' => 'listrik_per_jam', 'icon' => 'fas fa-bolt', 'color' => 'text-warning'],
                                        ['name' => 'Mesin Ringan', 'field' => 'gas_bbm_per_jam', 'icon' => 'fas fa-fire', 'color' => 'text-danger'],
                                        ['name' => 'Penyusutan Alat', 'field' => 'penyusutan_mesin_per_jam', 'icon' => 'fas fa-chart-line-down', 'color' => 'text-secondary'],
                                        ['name' => 'Drum / Mixer', 'field' => 'maintenance_per_jam', 'icon' => 'fas fa-tools', 'color' => 'text-info'],
                                        ['name' => 'Maintenace', 'field' => 'gaji_mandor_per_jam', 'icon' => 'fas fa-wrench', 'color' => 'text-primary'],
                                        ['name' => 'Rutin', 'field' => 'rutin_per_jam', 'icon' => 'fas fa-sync', 'color' => 'text-success'],
                                        ['name' => 'Kebersihan', 'field' => 'kebersihan_per_jam', 'icon' => 'fas fa-broom', 'color' => 'text-info']
                                    ];
                                @endphp
                                
                                @foreach($components as $index => $component)
                                @php
                                    // Get value from map by component name, fallback to index-based lookup
                                    $currentValue = $komponenMap[$component['name']] ?? 
                                                   ($komponenBop[$index]['rate_per_hour'] ?? 0);
                                @endphp
                                <tr>
                                    <td>
                                        <i class="{{ $component['icon'] }} {{ $component['color'] }} me-2"></i>
                                        {{ $component['name'] }}
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark text-white">Rp</span>
                                            <input type="number" 
                                                   name="komponen_bop[{{ $index }}][rate_per_hour]" 
                                                   id="{{ $component['field'] }}" 
                                                   class="form-control bg-dark text-white" 
                                                   value="{{ old('komponen_bop.' . $index . '.rate_per_hour', $currentValue) }}"
                                                   min="0" 
                                                   step="0.01" 
                                                   placeholder="0"
                                                   oninput="calculateTotal()">
                                            <input type="hidden" name="komponen_bop[{{ $index }}][component]" value="{{ $component['name'] }}">
                                        </div>
                                    </td>
                                    <td>
                                        @if($component['name'] == 'Listrik Mixer')
                                            <span class="text-white-50">Listrik</span>
                                        @elseif($component['name'] == 'Mesin Ringan')
                                            <span class="text-white-50">-</span>
                                        @elseif($component['name'] == 'Penyusutan Alat')
                                            <span class="text-white-50">-</span>
                                        @elseif($component['name'] == 'Drum / Mixer')
                                            <span class="text-white-50">-</span>
                                        @elseif($component['name'] == 'Maintenace')
                                            <span class="text-white-50">Rutin</span>
                                        @elseif($component['name'] == 'Rutin')
                                            <span class="text-white-50">Rutin</span>
                                        @elseif($component['name'] == 'Kebersihan')
                                            <span class="text-white-50">Rutin</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-light" onclick="resetComponent({{ $index }})">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Total Row -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-dark rounded">
                                <strong class="text-white-50">Total BOP / produk</strong>
                                <span class="fs-5 text-warning fw-bold" id="totalBopPerProduk">Rp {{ number_format($bopProses->bop_per_unit, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-dark rounded">
                                <strong class="text-white-50">BOP / produk</strong>
                                <span class="fs-5 text-info fw-bold" id="bopPerUnit">Rp {{ number_format($bopProses->bop_per_unit, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-dark rounded">
                                <strong class="text-white-50">Biaya / produk</strong>
                                <span class="fs-5 text-success fw-bold" id="biayaPerProduk">Rp {{ number_format($bopProses->biaya_per_produk, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="info-card">
                    <label for="keterangan" class="form-label text-white-50">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" 
                              class="form-control bg-dark text-white" 
                              placeholder="Tambahkan keterangan untuk BOP proses ini...">{{ old('keterangan', $bopProses->keterangan) }}</textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <div>
                        <button type="button" class="btn btn-warning me-2" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Debug: Log form data before submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editBopForm');
    
    // Debug: Log form action and method
    console.log('=== FORM INFO ===');
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);
    console.log('Form elements count:', form.elements.length);
    
    // Check for _method field
    const methodField = form.querySelector('input[name="_method"]');
    console.log('_method field:', methodField ? methodField.value : 'NOT FOUND');
    
    form.addEventListener('submit', function(e) {
        console.log('=== FORM SUBMISSION DEBUG ===');
        console.log('Submitting to:', form.action);
        console.log('Method:', form.method);
        
        // Get all komponen_bop inputs
        const komponenInputs = document.querySelectorAll('input[name^="komponen_bop"]');
        console.log('Total inputs found:', komponenInputs.length);
        
        // Build komponen array
        const komponenData = [];
        for (let i = 0; i < 7; i++) {
            const rateInput = document.querySelector(`input[name="komponen_bop[${i}][rate_per_hour]"]`);
            const componentInput = document.querySelector(`input[name="komponen_bop[${i}][component]"]`);
            
            if (rateInput && componentInput) {
                const data = {
                    index: i,
                    component: componentInput.value,
                    rate_per_hour: rateInput.value,
                    rate_float: parseFloat(rateInput.value) || 0
                };
                komponenData.push(data);
                console.log(`Component ${i}:`, data);
            }
        }
        
        // Check valid components (rate > 0)
        const validComponents = komponenData.filter(k => parseFloat(k.rate_per_hour) > 0);
        console.log('Valid components (rate > 0):', validComponents.length);
        console.log('Valid components data:', validComponents);
        
        if (validComponents.length === 0) {
            console.error('❌ NO VALID COMPONENTS! Form will fail validation.');
            alert('DEBUG: Tidak ada komponen dengan nilai > 0. Cek console (F12) untuk detail.');
            // Uncomment line below to prevent submission for debugging
            // e.preventDefault();
        } else {
            console.log('✅ Form has', validComponents.length, 'valid components');
        }
        
        console.log('=== END DEBUG ===');
    });
});

function calculateTotal() {
    // Get all component values - updated to include all 7 components
    const components = ['listrik_per_jam', 'gas_bbm_per_jam', 'penyusutan_mesin_per_jam', 'maintenance_per_jam', 'gaji_mandor_per_jam', 'rutin_per_jam', 'kebersihan_per_jam'];
    let total = 0;
    
    components.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            total += parseFloat(element.value) || 0;
        }
    });
    
    const kapasitas = {{ $bopProses->prosesProduksi->kapasitas_per_jam ?? 50 }};
    const bopPerUnit = kapasitas > 0 ? total / kapasitas : 0;
    
    // Hitung BTKL/produk secara realtime dari tarif per jam dan kapasitas
    // Ambil tarif per jam dari database yang sudah diupdate
    const tarifPerJam = {{ $bopProses->prosesProduksi->tarif_btkl ?? 48000 }};
    const btklPerPcs = kapasitas > 0 ? tarifPerJam / kapasitas : 0;
    
    const biayaPerProduk = bopPerUnit + btklPerPcs;
    
    // Update displays
    document.getElementById('totalBopPerProduk').textContent = 'Rp ' + total.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('bopPerUnit').textContent = 'Rp ' + bopPerUnit.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('biayaPerProduk').textContent = 'Rp ' + biayaPerProduk.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Update BTKL/produk display
    const btklDisplay = document.getElementById('btklPerProdukDisplay');
    if (btklDisplay) {
        btklDisplay.value = btklPerPcs.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

function resetComponent(index) {
    const inputs = document.querySelectorAll('input[name="komponen_bop[' + index + '][rate_per_hour]"]');
    inputs.forEach(input => {
        input.value = 0;
    });
    calculateTotal();
}

function resetForm() {
    if (confirm('Apakah Anda yakin ingin mereset semua nilai komponen BOP?')) {
        const inputs = document.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            if (input.id && input.id.includes('_per_jam')) {
                input.value = 0;
            }
        });
        calculateTotal();
    }
}

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    
    // Add event listeners to all component inputs
    const componentInputs = document.querySelectorAll('input[name*="komponen_bop"][type="number"]');
    componentInputs.forEach(input => {
        input.addEventListener('input', calculateTotal);
        input.addEventListener('change', calculateTotal);
    });
});

// Auto-format currency inputs
document.addEventListener('DOMContentLoaded', function() {
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value) || 0;
            this.value = value.toFixed(2);
        });
    });
});

// Auto-refresh BTKL data every 30 seconds
function refreshBtklData() {
    fetch(`/api/btkl/proses/{{ $bopProses->proses_produksi_id }}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display fields with fresh data
                const tarifDisplay = document.querySelector('input[readonly][value*="' + data.data.tarif_per_jam + '"]');
                if (tarifDisplay) {
                    tarifDisplay.value = data.data.tarif_per_jam.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
                
                // Update capacity display
                const capacityDisplay = document.querySelector('input[readonly][value="{{ $bopProses->prosesProduksi->kapasitas_per_jam }}"]');
                if (capacityDisplay) {
                    capacityDisplay.value = data.data.kapasitas_per_jam;
                }
                
                // Update BTKL per produk display
                const btklDisplay = document.getElementById('btklPerProdukDisplay');
                if (btklDisplay) {
                    const btklPerProduk = data.data.kapasitas_per_jam > 0 ? data.data.tarif_per_jam / data.data.kapasitas_per_jam : 0;
                    btklDisplay.value = btklPerProduk.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
                
                // Recalculate totals with fresh data
                calculateTotal();
                
                console.log('BTKL data refreshed successfully', data.data);
            }
        })
        .catch(error => {
            console.error('Failed to refresh BTKL data:', error);
        });
}

// Set up auto-refresh - DINONAKTIFKAN UNTUK PRESENTASI
/*
setInterval(refreshBtklData, 30000); // Refresh every 30 seconds

// Also refresh on page focus
window.addEventListener('focus', function() {
    refreshBtklData();
});
*/
</script>
@endsection
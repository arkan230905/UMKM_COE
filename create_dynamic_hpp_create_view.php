<?php

echo "=== CREATE DYNAMIC HPP CREATE VIEW ===\n\n";

echo "Creating simplified HPP create view with component selection only...\n";

$viewContent = '@extends(\'layouts.app\')

@section(\'title\', \'Hitung Harga Pokok Produksi\')

@section(\'content\')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>
            <i class="fas fa-calculator me-2"></i>Hitung Harga Pokok Produksi
        </h3>
        <a href="{{ route(\'master-data.harga-pokok-produksi.index\') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <form action="{{ route(\'master-data.harga-pokok-produksi.store\') }}" method="POST" id="hppForm">
        @csrf
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session(\'error\'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session(\'error\') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Step 1: Pilih Produk -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>1. Pilih Produk</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Produk yang Sudah Memiliki Biaya Bahan *</label>
                        <select name="produk_id" id="produk_id" class="form-select" required onchange="loadProdukData()">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $produk)
                                @php
                                    // Calculate biaya bahan from bom_job_bbb
                                    $biayaBahan = \App\Models\BomJobBBB::where(\'user_id\', auth()->id())
                                        ->where(\'produk_id\', $produk->id)
                                        ->sum(\'subtotal\');
                                @endphp
                                <option value="{{ $produk->id }}" 
                                        data-biaya-bahan="{{ $biayaBahan }}">
                                    {{ $produk->nama_produk }} 
                                    (Biaya Bahan: Rp {{ number_format($biayaBahan, 0, \',\', \'.\') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hanya produk yang sudah memiliki biaya bahan yang dapat dipilih</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Biaya Bahan</label>
                        <div class="form-control-plaintext bg-light p-3 rounded border">
                            <h4 class="mb-0 text-primary" id="displayBiayaBahan">Rp 0</h4>
                            <input type="hidden" name="biaya_bahan" id="biayaBahanInput" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Pilih Komponen HPP -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>2. Pilih Komponen HPP yang Digunakan</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Pilih komponen HPP yang akan digunakan untuk produk ini. Sistem akan menyimpan ID pilihan dan menampilkan detail sesuai komponen yang dipilih.
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <input type="checkbox" id="include_bbb" name="include_bbb" value="1" checked class="form-check-input me-2" onchange="updateComponentSelection()">
                                    <label for="include_bbb" class="form-check-label">Biaya Bahan Baku</label>
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">Menampilkan semua biaya bahan baku dari produk</p>
                                <small class="text-muted">Total: <span id="bbb-total">Rp 0</span></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <input type="checkbox" id="include_btkl" name="include_btkl" value="1" checked class="form-check-input me-2" onchange="updateComponentSelection()">
                                    <label for="include_btkl" class="form-check-label">Biaya Tenaga Kerja Langsung</label>
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">Pilih proses BTKL yang digunakan</p>
                                <small class="text-muted">Total: <span id="btkl-total">Rp 0</span></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <input type="checkbox" id="include_bop" name="include_bop" value="1" checked class="form-check-input me-2" onchange="updateComponentSelection()">
                                    <label for="include_bop" class="form-check-label">Biaya Overhead Pabrik</label>
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">Komponen BOP otomatis dari proses BTKL</p>
                                <small class="text-muted">Total: <span id="bop-total">Rp 0</span></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Detail Pilihan (Dinamis) -->
        <div id="componentDetails" style="display: none;">
            <!-- BBB Details -->
            <div class="card shadow-sm mb-4" id="bbbDetails" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Detail Biaya Bahan Baku</h5>
                </div>
                <div class="card-body">
                    <div id="bbbContent"></div>
                </div>
            </div>

            <!-- BTKL Details -->
            <div class="card shadow-sm mb-4" id="btklDetails" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Detail Proses BTKL yang Dipilih</h5>
                </div>
                <div class="card-body">
                    <div id="btklContent"></div>
                </div>
            </div>

            <!-- BOP Details -->
            <div class="card shadow-sm mb-4" id="bopDetails" style="display: none;">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-industry me-2"></i>Detail Komponen BOP (Otomatis)</h5>
                </div>
                <div class="card-body">
                    <div id="bopContent"></div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan HPP</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-primary text-white rounded">
                            <h6>Biaya Bahan</h6>
                            <h4 id="summaryBiayaBahan">Rp 0</h4>
                            <input type="hidden" name="total_bbb" id="totalBbbInput" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-success text-white rounded">
                            <h6>Total BTKL</h6>
                            <h4 id="summaryBtkl">Rp 0</h4>
                            <input type="hidden" name="total_btkl" id="totalBtklInput" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-warning text-dark rounded">
                            <h6>Total BOP</h6>
                            <h4 id="summaryBop">Rp 0</h4>
                            <input type="hidden" name="total_bop" id="totalBopInput" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-dark text-white rounded">
                            <h6>Total HPP</h6>
                            <h4 id="summaryTotal">Rp 0</h4>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-lg btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan HPP
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let produkData = {};
let selectedBbbIds = [];
let selectedBtklIds = [];
let selectedBopIds = [];

function loadProdukData() {
    const produkId = document.getElementById(\'produk_id\').value;
    const option = document.querySelector(`#produk_id option[value="${produkId}"]`);
    
    if (option) {
        const biayaBahan = parseFloat(option.dataset.biayaBahan) || 0;
        document.getElementById(\'displayBiayaBahan\').textContent = `Rp ${numberFormat(biayaBahan)}`;
        document.getElementById(\'biayaBahanInput\').value = biayaBahan;
        document.getElementById(\'totalBbbInput\').value = biayaBahan;
        document.getElementById(\'bbb-total\').textContent = `Rp ${numberFormat(biayaBahan)}`;
        document.getElementById(\'summaryBiayaBahan\').textContent = `Rp ${numberFormat(biayaBahan)}`;
        
        // Load BBB data for this product
        loadBBBData(produkId);
        
        // Load BTKL data
        loadBTKLData(produkId);
        
        updateComponentSelection();
    }
}

function loadBBBData(produkId) {
    fetch(`/api/bbb-data/${produkId}`)
        .then(response => response.json())
        .then(data => {
            displayBBBDetails(data);
        })
        .catch(error => console.error(\'Error loading BBB data:\', error));
}

function loadBTKLData(produkId) {
    fetch(`/api/btkl-data/${produkId}`)
        .then(response => response.json())
        .then(data => {
            displayBTKLDetails(data);
        })
        .catch(error => console.error(\'Error loading BTKL data:\', error));
}

function displayBBBDetails(bbbData) {
    const content = bbbData.map((bbb, index) => `
        <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
            <div>
                <input type="checkbox" name="selected_bbb_ids[]" value="${bbb.id}" 
                       class="form-check-input me-2 bbb-checkbox" checked
                       onchange="updateBBBSelection()">
                <strong>${bbb.nama_bahan}</strong>
                <small class="text-muted d-block">${bbb.jumlah} ${bbb.satuan} @ Rp ${numberFormat(bbb.harga_satuan)}</small>
            </div>
            <div class="text-end">
                <strong>Rp ${numberFormat(bbb.subtotal)}</strong>
            </div>
        </div>
    `).join(\'\');
    
    document.getElementById(\'bbbContent\').innerHTML = content;
}

function displayBTKLDetails(btklData) {
    const content = btklData.map((btkl, index) => `
        <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
            <div>
                <input type="checkbox" name="selected_btkl_ids[]" value="${btkl.id}" 
                       class="form-check-input me-2 btkl-checkbox" checked
                       data-btkl="${btkl.btkl_per_produk}" data-bop="${btkl.bop_per_produk}"
                       onchange="updateBTKLSelection()">
                <strong>${btkl.nama_proses}</strong>
                <small class="text-muted d-block">${btkl.nama_jabatan} - ${btkl.kode_proses}</small>
            </div>
            <div class="text-end">
                <div>BTKL: <strong>Rp ${numberFormat(btkl.btkl_per_produk)}</strong></div>
                <div>BOP: <strong>Rp ${numberFormat(btkl.bop_per_produk)}</strong></div>
            </div>
        </div>
    `).join(\'\');
    
    document.getElementById(\'btklContent\').innerHTML = content;
}

function updateBBBSelection() {
    const checkboxes = document.querySelectorAll(\'.bbb-checkbox:checked\');
    selectedBbbIds = Array.from(checkboxes).map(cb => cb.value);
    
    // Update BBB total
    const biayaBahan = parseFloat(document.getElementById(\'biayaBahanInput\').value) || 0;
    document.getElementById(\'bbb-total\').textContent = `Rp ${numberFormat(biayaBahan)}`;
    document.getElementById(\'summaryBiayaBahan\').textContent = `Rp ${numberFormat(biayaBahan)}`;
    
    updateSummary();
}

function updateBTKLSelection() {
    const checkboxes = document.querySelectorAll(\'.btkl-checkbox:checked\');
    selectedBtklIds = Array.from(checkboxes).map(cb => cb.value);
    
    // Calculate BTKL and BOP totals
    let totalBtkl = 0;
    let totalBop = 0;
    
    checkboxes.forEach(checkbox => {
        totalBtkl += parseFloat(checkbox.dataset.btkl) || 0;
        totalBop += parseFloat(checkbox.dataset.bop) || 0;
    });
    
    document.getElementById(\'btkl-total\').textContent = `Rp ${numberFormat(totalBtkl)}`;
    document.getElementById(\'bop-total\').textContent = `Rp ${numberFormat(totalBop)}`;
    document.getElementById(\'summaryBtkl\').textContent = `Rp ${numberFormat(totalBtkl)}`;
    document.getElementById(\'summaryBop\').textContent = `Rp ${numberFormat(totalBop)}`;
    document.getElementById(\'totalBtklInput\').value = totalBtkl;
    document.getElementById(\'totalBopInput\').value = totalBop;
    
    updateSummary();
}

function updateComponentSelection() {
    const includeBbb = document.getElementById(\'include_bbb\').checked;
    const includeBtkl = document.getElementById(\'include_btkl\').checked;
    const includeBop = document.getElementById(\'include_bop\').checked;
    
    // Show/hide component details
    document.getElementById(\'bbbDetails\').style.display = includeBbb ? \'block\' : \'none\';
    document.getElementById(\'btklDetails\').style.display = includeBtkl ? \'block\' : \'none\';
    document.getElementById(\'bopDetails\').style.display = includeBop ? \'block\' : \'none\';
    document.getElementById(\'componentDetails\').style.display = (includeBbb || includeBtkl || includeBop) ? \'block\' : \'none\';
    
    // Update summary
    updateSummary();
}

function updateSummary() {
    const includeBbb = document.getElementById(\'include_bbb\').checked;
    const includeBtkl = document.getElementById(\'include_btkl\').checked;
    const includeBop = document.getElementById(\'include_bop\').checked;
    
    let totalHpp = 0;
    
    if (includeBbb) {
        totalHpp += parseFloat(document.getElementById(\'totalBbbInput\').value) || 0;
    }
    if (includeBtkl) {
        totalHpp += parseFloat(document.getElementById(\'totalBtklInput\').value) || 0;
    }
    if (includeBop) {
        totalHpp += parseFloat(document.getElementById(\'totalBopInput\').value) || 0;
    }
    
    document.getElementById(\'summaryTotal\').textContent = `Rp ${numberFormat(totalHpp)}`;
}

function numberFormat(num) {
    return new Intl.NumberFormat(\'id-ID\').format(num);
}

// Initialize on page load
document.addEventListener(\'DOMContentLoaded\', function() {
    updateComponentSelection();
});
</script>
@endsection';

// Create the new view file
$viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
file_put_contents($viewFile, $viewContent);

echo "✅ Created dynamic HPP create view at: $viewFile\n";
echo "✅ Features:\n";
echo "  - Component selection checkboxes\n";
echo "  - Dynamic detail display based on selection\n";
echo "  - Real-time calculation\n";
echo "  - Selected ID storage\n";
echo "  - Clean and simple interface\n\n";

echo "=== DYNAMIC HPP CREATE VIEW CREATED ===\n";

<?php

echo "=== CREATE SIMPLIFIED HPP CREATE VIEW ===\n\n";

echo "Creating simplified HPP create view with direct ID selection...\n";

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
                    <div class="col-md-12">
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
                        <small class="text-muted">Produk diambil dari database bom_job_bbb</small>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-control-plaintext bg-light p-3 rounded border">
                            <h6 class="mb-2">Biaya Bahan Baku</h6>
                            <h4 class="mb-0 text-primary" id="displayBiayaBahan">Rp 0</h4>
                            <input type="hidden" name="biaya_bahan" id="biayaBahanInput" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Pilih Proses BTKL yang Digunakan -->
        <div class="card shadow-sm mb-4" id="btklSection" style="display: none;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>2. Pilih Proses BTKL yang Digunakan</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Pilih proses BTKL yang akan digunakan untuk produk ini. Data diambil dari database proses_produksis.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">Pilih</th>
                                <th width="15%">Kode</th>
                                <th width="25%">Nama Proses</th>
                                <th width="20%">Jabatan</th>
                                <th width="15%">Tarif BTKL/Jam</th>
                                <th width="10%">BTKL/pcs</th>
                                <th width="10%">BOP/pcs</th>
                            </tr>
                        </thead>
                        <tbody id="btklTableBody">
                            <!-- BTKL data will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Step 3: Detail Komponen BOP (Otomatis) -->
        <div class="card shadow-sm mb-4" id="bopSection" style="display: none;">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-industry me-2"></i>3. Detail Komponen BOP (Otomatis)</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Komponen BOP ditampilkan otomatis berdasarkan proses BTKL yang dipilih. Data diambil dari database bop_proses.
                </div>
                
                <div id="bopDetailContent">
                    <!-- BOP details will be loaded here via JavaScript -->
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
let selectedBtklIds = [];
let btklData = [];

function loadProdukData() {
    const produkId = document.getElementById(\'produk_id\').value;
    const option = document.querySelector(`#produk_id option[value="${produkId}"]`);
    
    if (option) {
        const biayaBahan = parseFloat(option.dataset.biayaBahan) || 0;
        document.getElementById(\'displayBiayaBahan\').textContent = `Rp ${numberFormat(biayaBahan)}`;
        document.getElementById(\'biayaBahanInput\').value = biayaBahan;
        document.getElementById(\'summaryBiayaBahan\').textContent = `Rp ${numberFormat(biayaBahan)}`;
        
        // Load BTKL data
        loadBTKLData(produkId);
        
        // Show BTKL section
        document.getElementById(\'btklSection\').style.display = \'block\';
        
        updateSummary();
    } else {
        // Hide sections if no product selected
        document.getElementById(\'btklSection\').style.display = \'none\';
        document.getElementById(\'bopSection\').style.display = \'none\';
        resetSummary();
    }
}

function loadBTKLData(produkId) {
    fetch(`/api/btkl-data/${produkId}`)
        .then(response => response.json())
        .then(data => {
            btklData = data;
            displayBTKLTable(data);
        })
        .catch(error => console.error(\'Error loading BTKL data:\', error));
}

function displayBTKLTable(data) {
    const tbody = document.getElementById(\'btklTableBody\');
    
    if (data.length === 0) {
        tbody.innerHTML = \'<tr><td colspan="7" class="text-center">Tidak ada data BTKL tersedia</td></tr>\';
        return;
    }
    
    tbody.innerHTML = data.map((btkl, index) => `
        <tr>
            <td class="text-center">
                <input type="checkbox" 
                       class="form-check-input btkl-checkbox" 
                       name="selected_btkl_ids[]" 
                       value="${btkl.id}"
                       data-btkl="${btkl.btkl_per_produk}"
                       data-bop="${btkl.bop_per_produk}"
                       data-nama="${btkl.nama_proses}"
                       onchange="updateBTKLSelection()">
            </td>
            <td>${btkl.kode_proses}</td>
            <td><strong>${btkl.nama_proses}</strong></td>
            <td>${btkl.nama_jabatan}</td>
            <td>Rp ${numberFormat(btkl.tarif_btkl)}</td>
            <td class="text-success fw-semibold">Rp ${numberFormat(btkl.btkl_per_produk)}</td>
            <td class="text-warning fw-semibold">Rp ${numberFormat(btkl.bop_per_produk)}</td>
        </tr>
    `).join(\'\');
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
    
    document.getElementById(\'summaryBtkl\').textContent = `Rp ${numberFormat(totalBtkl)}`;
    document.getElementById(\'summaryBop\').textContent = `Rp ${numberFormat(totalBop)}`;
    document.getElementById(\'totalBtklInput\').value = totalBtkl;
    document.getElementById(\'totalBopInput\').value = totalBop;
    
    // Load BOP details for selected BTKL
    if (selectedBtklIds.length > 0) {
        loadBOPDetails(selectedBtklIds);
        document.getElementById(\'bopSection\').style.display = \'block\';
    } else {
        document.getElementById(\'bopSection\').style.display = \'none\';
    }
    
    updateSummary();
}

function loadBOPDetails(btklIds) {
    // Load BOP details from bop_proses table for selected BTKL IDs
    fetch(`/api/bop-details/${btklIds.join(\',\')}`)
        .then(response => response.json())
        .then(data => {
            displayBOPDetails(data);
        })
        .catch(error => {
            console.error(\'Error loading BOP details:\', error);
            document.getElementById(\'bopDetailContent\').innerHTML = \'<p class="text-muted">Tidak ada data BOP tersedia</p>\';
        });
}

function displayBOPDetails(bopData) {
    const content = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Nama Komponen</th>
                        <th>Jumlah</th>
                        <th>Tarif</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${bopData.map(bop => `
                        <tr>
                            <td><strong>${bop.nama_komponen}</strong></td>
                            <td>${bop.jumlah}</td>
                            <td>Rp ${numberFormat(bop.tarif)}</td>
                            <td>Rp ${numberFormat(bop.total)}</td>
                        </tr>
                    `).join(\'\')}
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById(\'bopDetailContent\').innerHTML = content;
}

function updateSummary() {
    const biayaBahan = parseFloat(document.getElementById(\'biayaBahanInput\').value) || 0;
    const totalBtkl = parseFloat(document.getElementById(\'totalBtklInput\').value) || 0;
    const totalBop = parseFloat(document.getElementById(\'totalBopInput\').value) || 0;
    
    const totalHpp = biayaBahan + totalBtkl + totalBop;
    
    document.getElementById(\'summaryTotal\').textContent = `Rp ${numberFormat(totalHpp)}`;
}

function resetSummary() {
    document.getElementById(\'summaryBiayaBahan\').textContent = \'Rp 0\';
    document.getElementById(\'summaryBtkl\').textContent = \'Rp 0\';
    document.getElementById(\'summaryBop\').textContent = \'Rp 0\';
    document.getElementById(\'summaryTotal\').textContent = \'Rp 0\';
    document.getElementById(\'biayaBahanInput\').value = 0;
    document.getElementById(\'totalBtklInput\').value = 0;
    document.getElementById(\'totalBopInput\').value = 0;
}

function numberFormat(num) {
    return new Intl.NumberFormat(\'id-ID\').format(num);
}

// Initialize on page load
document.addEventListener(\'DOMContentLoaded\', function() {
    resetSummary();
});
</script>
@endsection';

// Create the new view file
$viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
file_put_contents($viewFile, $viewContent);

echo "✅ Created simplified HPP create view at: $viewFile\n";
echo "✅ Features:\n";
echo "  - Direct product selection from bom_job_bbb\n";
echo "  - BTKL selection from proses_produksis table\n";
echo "  - Automatic BOP from bop_proses table\n";
echo "  - ID-based storage in bom_job_costings\n";
echo "  - Clean, simple interface\n\n";

echo "=== SIMPLIFIED HPP CREATE VIEW CREATED ===\n";

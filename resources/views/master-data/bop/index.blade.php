@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>BOP (Biaya Overhead Pabrik)
        </h2>
        <button id="addButton" class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>Tambah BOP Proses
        </button>
    </div>

    <!-- Tab Switch -->
    <div class="mb-4">
        <div class="btn-group w-100" role="group">
            <button type="button" class="btn btn-outline-primary active" id="bopProsesTab" onclick="switchToBopProses()">
                <i class="fas fa-industry me-2"></i>BOP Proses
            </button>
            <button type="button" class="btn btn-outline-primary" id="bebanOperasionalTab" onclick="switchToBebanOperasional()">
                <i class="fas fa-chart-line me-2"></i>Beban Operasional
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="bopProsesContent" class="content-section" style="display: block;">
        <!-- BOP Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-wide">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">No</th>
                                <th style="width: 30%">Nama Proses</th>
                                <th style="width: 20%">BOP / produk</th>
                                <th style="width: 25%">Biaya/Produk</th>
                                <th style="width: 20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bopProses as $index => $bop)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-gear-fill me-2 text-primary opacity-50"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $bop->prosesProduksi->nama_proses ?? '-' }}</div>
                                            <small class="text-muted">Proses</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-box-seam me-2 text-warning opacity-50"></i>
                                        <div>
                                            <div class="fw-semibold text-warning">Rp {{ number_format($bop->bop_per_unit, 2, ',', '.') }}</div>
                                            <small class="text-muted">Per produk</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-stack me-2 text-success opacity-50"></i>
                                        <div>
                                            <div class="fw-bold text-success">{{ $bop->biaya_per_produk_formatted }}</div>
                                            <small class="text-muted">Total biaya</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" 
                                               class="btn btn-sm btn-info text-white rounded-pill px-3"
                                               onclick="viewBopDetail({{ $bop->id }})"
                                               data-bs-toggle="tooltip" 
                                               title="Lihat Detail">
                                            <i class="bi bi-eye me-1"></i>
                                            <span class="d-none d-md-inline">Lihat</span>
                                        </button>
                                        <button type="button" 
                                               class="btn btn-sm btn-warning text-white rounded-pill px-3"
                                               onclick="editBopProses({{ $bop->id }})"
                                               data-bs-toggle="tooltip" 
                                               title="Edit BOP">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            <span class="d-none d-md-inline">Edit</span>
                                        </button>
                                        <button type="button" 
                                               class="btn btn-sm btn-danger text-white rounded-pill px-3"
                                               data-bs-toggle="modal" 
                                               data-bs-target="#deleteModal{{ $bop->id }}"
                                               data-bs-toggle="tooltip" 
                                               title="Hapus BOP">
                                            <i class="bi bi-trash3 me-1"></i>
                                            <span class="d-none d-md-inline">Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
        
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        <p>Belum ada data BOP</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBopProsesModal">
                                            <i class="bi bi-plus me-2"></i>Tambah BOP Pertama
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                            
                            <!-- Total Row - Total Biaya / produk -->
                            @if($bopProses->count() > 0)
                            <tr class="table-active fw-bold">
                                <td colspan="4" class="text-end">
                                    <span class="text-muted">Total Biaya / produk:</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-end">
                                        @php
                                            $totalBiayaPerProduk = $bopProses->sum('biaya_per_produk');
                                        @endphp
                                        <span class="fw-bold text-success fs-6">Rp {{ number_format($totalBiayaPerProduk, 2, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td>-</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Beban Operasional Content -->
    <div id="bebanOperasionalContent" class="content-section" style="display: none;">
        <!-- Filter Section -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="filterKategori" class="form-label">Kategori</label>
                        <select class="form-select" id="filterKategori" onchange="filterBebanOperasional()">
                            <option value="">Semua Kategori</option>
                            <option value="Administrasi">Administrasi</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Utilitas">Utilitas</option>
                            <option value="Distribusi">Distribusi</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus" onchange="filterBebanOperasional()">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="filterSearch" class="form-label">Cari Beban</label>
                        <input type="text" class="form-control" id="filterSearch" placeholder="Nama beban..." onkeyup="filterBebanOperasional()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Beban Operasional Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-wide">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">No</th>
                                <th style="width: 15%">Kategori</th>
                                <th style="width: 25%">Nama Beban</th>
                                <th style="width: 15%" class="text-end">Budget Bulanan</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 30%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bebanOperasionalTableBody">
                            <!-- Data will be loaded via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="text-muted">
                            <i class="bi bi-info-circle me-2"></i>Master Data Beban Operasional
                        </h6>
                        <p class="mb-0 text-muted">
                            Halaman ini mengelola daftar jenis beban operasional yang akan digunakan sebagai referensi pada transaksi pembayaran beban.
                            Data master ini tidak berisi nominal transaksi aktual.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modals -->
@forelse($bopProses as $bop)
<div class="modal fade" id="deleteModal{{ $bop->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $bop->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel{{ $bop->id }}">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-trash3 text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-danger fw-bold">Apakah Anda yakin?</h6>
                    <p class="text-muted mb-0">Data BOP untuk proses <strong>"{{ $bop->prosesProduksi->nama_proses ?? 'Tidak Diketahui' }}"</strong> akan dihapus secara permanen.</p>
                </div>
                
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-warning me-2"></i>
                        <div>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-1 small">
                                <li>Proses: {{ $bop->prosesProduksi->nama_proses ?? 'Tidak Diketahui' }}</li>
                                <li>BOP / produk: Rp {{ number_format($bop->bop_per_unit, 2, ',', '.') }}</li>
                                                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </button>
                <form action="{{ route('master-data.bop.destroy-proses', $bop->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash3 me-1"></i>Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
@endforelse

<!-- Modals -->
@include('master-data.bop.modals')

@push('scripts')
<script>
// Store BTKL data for auto-fill functionality
const btklData = @json($btklData);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Setup auto-fill for BTKL fields
    setupBtklAutoFill();
});

function setupBtklAutoFill() {
    // Event listener for process selection in add modal
    const processSelect = document.getElementById('proses_produksi_id');
    if (processSelect) {
        processSelect.addEventListener('change', function() {
            const selectedProsesId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const btklInfo = document.getElementById('btklInfo');
            const btklInfoText = document.getElementById('btklInfoText');
            
            if (selectedProsesId) {
                // Get process data from option attributes
                const kapasitas = parseFloat(selectedOption.getAttribute('data-kapasitas')) || 0;
                const tarif = parseFloat(selectedOption.getAttribute('data-tarif')) || 0;
                const biayaPerUnit = parseFloat(selectedOption.getAttribute('data-biaya-per-unit')) || 0;
                const prosesNama = selectedOption.getAttribute('data-nama') || '';
                
                // Fill BTKL fields with data from ProsesProduksi
                document.getElementById('kapasitas').value = kapasitas;
                document.getElementById('btkl_per_jam').value = tarif;
                document.getElementById('btkl_per_pcs').value = biayaPerUnit.toFixed(2);
                
                // Show info if data is available
                if (kapasitas > 0 && tarif > 0) {
                    btklInfo.classList.remove('d-none');
                    btklInfo.classList.remove('alert-warning');
                    btklInfo.classList.add('alert-info');
                    btklInfoText.textContent = `Data BTKL tersedia: Kapasitas ${kapasitas} pcs/jam, Tarif Rp ${tarif.toLocaleString('id-ID')}/jam, BTKL per pcs Rp ${biayaPerUnit.toFixed(2)}`;
                } else {
                    btklInfo.classList.remove('d-none');
                    btklInfo.classList.remove('alert-info');
                    btklInfo.classList.add('alert-warning');
                    btklInfoText.textContent = 'Data BTKL untuk proses ini belum lengkap. Silakan lengkapi data BTKL terlebih dahulu.';
                }
                
                // Trigger calculation after BTKL data is filled
                calculateBopSummary();
            } else {
                // Clear all fields
                document.getElementById('kapasitas').value = '';
                document.getElementById('btkl_per_jam').value = '';
                document.getElementById('btkl_per_pcs').value = '';
                
                // Hide info
                btklInfo.classList.add('d-none');
                btklInfo.classList.remove('alert-warning');
                btklInfo.classList.add('alert-info');
                
                // Clear calculations
                clearBopSummary();
            }
        });
    }
    
    // Setup event listeners for component rate changes
    setupComponentRateListeners();
}

// Add component row function
function addKomponenRow() {
    const tbody = document.getElementById('komponenRows');
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td><input type="text" name="komponen_name[]" class="form-control" placeholder="Nama komponen"></td>
        <td><input type="number" name="komponen_rate[]" class="form-control komponen-rate" min="0" step="0.01" placeholder="0" onchange="calculateBopSummary()"></td>
        <td><input type="text" name="komponen_desc[]" class="form-control" placeholder="Keterangan"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">Hapus</button></td>
    `;
    
    tbody.appendChild(newRow);
    
    // Setup listener for new row
    const rateInput = newRow.querySelector('.komponen-rate');
    if (rateInput) {
        rateInput.addEventListener('input', calculateBopSummary);
    }
    
    // Recalculate after adding row
    calculateBopSummary();
}

// Remove row function
function removeRow(button) {
    const row = button.closest('tr');
    const tbody = row.parentNode;
    
    // Don't remove if it's the last row
    if (tbody.children.length > 1) {
        row.remove();
        calculateBopSummary();
    } else {
        // Clear the last row instead of removing it
        row.querySelectorAll('input').forEach(input => {
            if (input.type === 'number') {
                input.value = '0';
            } else {
                input.value = '';
            }
        });
        calculateBopSummary();
    }
}

// Setup component rate listeners
function setupComponentRateListeners() {
    const rateInputs = document.querySelectorAll('.komponen-rate');
    rateInputs.forEach(input => {
        input.addEventListener('input', calculateBopSummary);
    });
}

// Calculate BOP summary based on per-product formula
function calculateBopSummary() {
    // Get BTKL values
    const kapasitas = parseFloat(document.getElementById('kapasitas').value) || 0;
    const btklPerJam = parseFloat(document.getElementById('btkl_per_jam').value) || 0;
    const btklPerPcs = parseFloat(document.getElementById('btkl_per_pcs').value) || 0;
    
    // Calculate Total BOP per produk from components (direct sum, no division)
    const componentRates = document.querySelectorAll('.komponen-rate');
    let totalBopPerProduk = 0;
    componentRates.forEach(input => {
        totalBopPerProduk += parseFloat(input.value) || 0;
    });
    
    // BOP per produk = Total BOP per produk (same value, no division by capacity)
    const bopPerProduk = totalBopPerProduk;
    
    // Calculate Biaya per produk: BTKL per produk + BOP per produk
    const biayaPerProduk = btklPerPcs + bopPerProduk;
    
    // Update readonly fields
    const totalBopInput = document.getElementById('total_bop_per_jam');
    if (totalBopInput) {
        totalBopInput.value = totalBopPerProduk.toFixed(2);
    }
    
    // Update BOP per produk field
    const bopPerPcsInput = document.getElementById('bop_per_pcs');
    if (bopPerPcsInput) {
        bopPerPcsInput.value = bopPerProduk.toFixed(2);
    }
    
    // Update Biaya per produk field
    const biayaPerProdukInput = document.getElementById('biaya_per_produk');
    if (biayaPerProdukInput) {
        biayaPerProdukInput.value = biayaPerProduk.toFixed(2);
    }
}

// Clear BOP summary
function clearBopSummary() {
    const fields = ['total_bop_per_jam', 'bop_per_pcs', 'biaya_per_produk'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = '0';
        }
    });
}

function viewBopDetail(id) {
    // Load BOP detail modal content
    fetch(`/master-data/bop/show-proses-modal/${id}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('detailBopContent').innerHTML = data;
            const modal = new bootstrap.Modal(document.getElementById('detailBopModal'));
            modal.show();
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function editBopProses(id) {
    // Load BOP Proses data
    fetch(`/master-data/bop/get-proses/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.bop) {
                const bop = data.bop;
                
                // Check if all required elements exist
                const requiredElements = [
                    'editBopProsesId',
                    'editProsesProduksiId',
                    'editNamaProses',
                    'editKapasitas',
                    'editBtklPerJam',
                    'editBtklPerPcs',
                    'editKeteranganProses'
                ];
                
                const missingElements = requiredElements.filter(id => !document.getElementById(id));
                if (missingElements.length > 0) {
                    console.error('Missing elements:', missingElements);
                    alert('Error: Beberapa elemen form tidak ditemukan. Silakan refresh halaman.');
                    return;
                }
                
                // Set hidden ID
                document.getElementById('editBopProsesId').value = bop.id;
                document.getElementById('editProsesProduksiId').value = bop.proses_produksi_id;
                
                // Set nama proses (readonly)
                document.getElementById('editNamaProses').value = bop.proses_produksi?.nama_proses || '';
                
                // Set BTKL data
                const kapasitas = bop.kapasitas_per_jam || 0;
                const tarif = bop.proses_produksi?.tarif_btkl || 0;
                const btklPerPcs = bop.proses_produksi?.biaya_per_produk || (kapasitas > 0 ? tarif / kapasitas : 0);
                
                document.getElementById('editKapasitas').value = kapasitas;
                document.getElementById('editBtklPerJam').value = tarif;
                document.getElementById('editBtklPerPcs').value = btklPerPcs.toFixed(2);
                
                // Show BTKL info
                const editBtklInfoText = document.getElementById('editBtklInfoText');
                if (editBtklInfoText) {
                    editBtklInfoText.textContent = `Kapasitas ${kapasitas} pcs/jam, Tarif Rp ${tarif.toLocaleString('id-ID')}/jam, BTKL per pcs Rp ${btklPerPcs.toFixed(2)}`;
                }
                
                // Load components
                loadEditComponents(bop);
                
                // Set keterangan
                document.getElementById('editKeteranganProses').value = bop.keterangan || '';
                
                // Calculate summary
                calculateEditBopSummary();
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editBopProsesModal'));
                modal.show();
            } else {
                alert('Gagal memuat data BOP');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        });
}

// Load components for edit modal
function loadEditComponents(bop) {
    const editKomponenRows = document.getElementById('editKomponenRows');
    
    if (!editKomponenRows) {
        console.error('editKomponenRows element not found');
        return;
    }
    
    editKomponenRows.innerHTML = '';
    
    // Load components from bop.komponen_bop if exists
    let components = [];
    if (bop.komponen_bop) {
        if (typeof bop.komponen_bop === 'string') {
            try {
                components = JSON.parse(bop.komponen_bop);
            } catch (e) {
                components = [];
            }
        } else if (Array.isArray(bop.komponen_bop)) {
            components = bop.komponen_bop;
        }
    }
    
    // If no components, create empty row
    if (components.length === 0) {
        components = [{component: '', rate_per_hour: 0, description: ''}];
    }
    
    // Add component rows
    components.forEach(comp => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="edit_komponen_name[]" class="form-control" value="${comp.component || comp.nama_komponen || ''}" placeholder="Nama komponen"></td>
            <td><input type="number" name="edit_komponen_rate[]" class="form-control edit-komponen-rate" min="0" step="0.01" value="${comp.rate_per_hour || comp.rp_per_jam || 0}" placeholder="0" onchange="calculateEditBopSummary()"></td>
            <td><input type="text" name="edit_komponen_desc[]" class="form-control" value="${comp.description || comp.keterangan || ''}" placeholder="Keterangan"></td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeEditRow(this)">Hapus</button></td>
        `;
        editKomponenRows.appendChild(row);
    });
    
    // Setup listeners for edit components
    setupEditComponentListeners();
}

// Add component row for edit modal
function addEditKomponenRow() {
    const tbody = document.getElementById('editKomponenRows');
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td><input type="text" name="edit_komponen_name[]" class="form-control" placeholder="Nama komponen"></td>
        <td><input type="number" name="edit_komponen_rate[]" class="form-control edit-komponen-rate" min="0" step="0.01" placeholder="0" onchange="calculateEditBopSummary()"></td>
        <td><input type="text" name="edit_komponen_desc[]" class="form-control" placeholder="Keterangan"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeEditRow(this)">Hapus</button></td>
    `;
    
    tbody.appendChild(newRow);
    
    // Setup listener for new row
    const rateInput = newRow.querySelector('.edit-komponen-rate');
    if (rateInput) {
        rateInput.addEventListener('input', calculateEditBopSummary);
    }
    
    // Recalculate after adding row
    calculateEditBopSummary();
}

// Remove row for edit modal
function removeEditRow(button) {
    const row = button.closest('tr');
    const tbody = row.parentNode;
    
    // Don't remove if it's the last row
    if (tbody.children.length > 1) {
        row.remove();
        calculateEditBopSummary();
    } else {
        // Clear the last row instead of removing it
        row.querySelectorAll('input').forEach(input => {
            if (input.type === 'number') {
                input.value = '0';
            } else {
                input.value = '';
            }
        });
        calculateEditBopSummary();
    }
}

// Setup edit component listeners
function setupEditComponentListeners() {
    const rateInputs = document.querySelectorAll('.edit-komponen-rate');
    rateInputs.forEach(input => {
        input.addEventListener('input', calculateEditBopSummary);
    });
}

// Calculate BOP summary for edit modal based on per-product formula
function calculateEditBopSummary() {
    // Get BTKL values from edit modal
    const kapasitas = parseFloat(document.getElementById('editKapasitas').value) || 0;
    const btklPerJam = parseFloat(document.getElementById('editBtklPerJam').value) || 0;
    const btklPerPcs = parseFloat(document.getElementById('editBtklPerPcs').value) || 0;
    
    // Calculate Total BOP per produk from components (direct sum, no division)
    const componentRates = document.querySelectorAll('.edit-komponen-rate');
    let totalBopPerProduk = 0;
    componentRates.forEach(input => {
        totalBopPerProduk += parseFloat(input.value) || 0;
    });
    
    // BOP per produk = Total BOP per produk (same value, no division by capacity)
    const bopPerProduk = totalBopPerProduk;
    
    // Calculate Biaya per produk: BTKL per produk + BOP per produk
    const biayaPerProduk = btklPerPcs + bopPerProduk;
    
    // Update readonly fields in edit modal
    const editTotalBopInput = document.getElementById('editTotalBopPerJam');
    if (editTotalBopInput) {
        editTotalBopInput.value = totalBopPerProduk.toFixed(2);
    }
    
    const editBopPerPcsInput = document.getElementById('editBopPerPcs');
    if (editBopPerPcsInput) {
        editBopPerPcsInput.value = bopPerProduk.toFixed(2);
    }
    
    const editBiayaPerProdukInput = document.getElementById('editBiayaPerProduk');
    if (editBiayaPerProdukInput) {
        editBiayaPerProdukInput.value = biayaPerProduk.toFixed(2);
    }
}

// Save edited BOP
function saveEditedBop() {
    const form = document.getElementById('editBopProsesForm');
    const bopId = document.getElementById('editBopProsesId').value;
    
    if (!bopId) {
        alert('ID BOP tidak ditemukan');
        return;
    }
    
    // Create FormData
    const formData = new FormData(form);
    
    // Submit via AJAX
    formData.append('_method', 'PUT'); // Add method override for PUT request
    
    fetch(`/master-data/bop/update-proses-simple/${bopId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editBopProsesModal'));
            modal.hide();
            
            // Show success message
            alert(data.message);
            
            // Reload page to show updated data
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    });
}

// Simple tab switching functions
// Simple tab switching functions
function switchToBopProses() {
    // Hide beban operasional
    document.getElementById('bebanOperasionalContent').style.display = 'none';
    document.getElementById('bebanOperasionalTab').classList.remove('active');
    
    // Show BOP proses
    document.getElementById('bopProsesContent').style.display = 'block';
    document.getElementById('bopProsesTab').classList.add('active');
    
    // Update button
    document.getElementById('addButton').innerHTML = '<i class="fas fa-plus me-2"></i>Tambah BOP Proses';
    document.getElementById('addButton').onclick = function() { openAddModal(); };
}

function switchToBebanOperasional() {
    // Hide BOP proses
    document.getElementById('bopProsesContent').style.display = 'none';
    document.getElementById('bopProsesTab').classList.remove('active');
    
    // Show beban operasional
    document.getElementById('bebanOperasionalContent').style.display = 'block';
    document.getElementById('bebanOperasionalTab').classList.add('active');
    
    // Update button
    document.getElementById('addButton').innerHTML = '<i class="fas fa-plus me-2"></i>Tambah Beban Operasional';
    document.getElementById('addButton').onclick = function() { openBebanOperasionalModal(); };
    
    // Load data
    loadBebanOperasionalData();
}

// Open BOP Proses modal (existing functionality)
function openAddModal() {
    const modal = new bootstrap.Modal(document.getElementById('addBopProsesModal'));
    modal.show();
}

// Open Beban Operasional modal
function openBebanOperasionalModal() {
    const modal = new bootstrap.Modal(document.getElementById('addBebanOperasionalModal'));
    modal.show();
}

// Load Beban Operasional data
function loadBebanOperasionalData() {
    const kategori = document.getElementById('filterKategori') ? document.getElementById('filterKategori').value : '';
    const status = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : '';
    const search = document.getElementById('filterSearch') ? document.getElementById('filterSearch').value : '';
    
    const params = new URLSearchParams();
    if (kategori) params.append('kategori', kategori);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    // Show loading state
    const tbody = document.getElementById('bebanOperasionalTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-spinner fa-spin display-4 d-block mb-2"></i>
                        <p>Memuat data...</p>
                    </div>
                </td>
            </tr>
        `;
    }
    
    const url = '/master-data/bop/beban-operasional/data';
    const fullUrl = params.toString() ? `${url}?${params.toString()}` : url;
    
    fetch(fullUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderBebanOperasionalTable(data.data);
            } else {
                showAlert('danger', data.message || 'Gagal memuat data');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>
                                    <p>Gagal memuat data</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat memuat data: ' + error.message);
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>
                                <p>Terjadi kesalahan saat memuat data</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
}

// Render Beban Operasional table
function renderBebanOperasionalTable(data) {
    const tbody = document.getElementById('bebanOperasionalTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                        <p>Belum ada data Beban Operasional</p>
                        <button class="btn btn-primary" onclick="openBebanOperasionalModal()">
                            <i class="bi bi-plus me-2"></i>Tambah Beban Operasional Pertama
                        </button>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map((item, index) => `
        <tr>
            <td class="text-center">
                ${index + 1}
            </td>
            <td>
                <span class="badge bg-secondary">${item.kategori || '-'}</span>
            </td>
            <td>
                <div class="fw-semibold">${item.nama_beban}</div>
            </td>
            <td class="text-end">
                <div class="fw-bold text-info">${item.budget_bulanan_formatted || '-'}</div>
            </td>
            <td>${item.status_badge}</td>
            <td>
                <div class="d-flex gap-1">
                    <button type="button" 
                           class="btn btn-sm btn-warning text-white rounded-pill px-3"
                           onclick="editBebanOperasional(${item.id})"
                           data-bs-toggle="tooltip" 
                           title="Edit Beban Operasional">
                        <i class="bi bi-pencil-square me-1"></i>
                        <span class="d-none d-md-inline">Edit</span>
                    </button>
                    <button type="button" 
                           class="btn btn-sm btn-danger text-white rounded-pill px-3"
                           onclick="deleteBebanOperasional(${item.id})"
                           data-bs-toggle="tooltip" 
                           title="Hapus Beban Operasional">
                        <i class="bi bi-trash3 me-1"></i>
                        <span class="d-none d-md-inline">Hapus</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Update Beban Operasional summary (not used for master data)
function updateBebanOperasionalSummary(totalPerPeriode, totalPerKategori) {
    // Summary tidak diperlukan untuk master data
    // Function ini tetap ada untuk compatibility
}

// Helper function to format number as Indonesian Rupiah
function formatRupiah(amount) {
    if (amount === null || amount === undefined || amount === 0) {
        return '0';
    }
    return new Intl.NumberFormat('id-ID').format(amount);
}

// Filter Beban Operasional
function filterBebanOperasional() {
    loadBebanOperasionalData();
}

// Edit Beban Operasional
function editBebanOperasional(id) {
    fetch(`{{ route('master-data.bop.beban-operasional.get', ':id') }}`.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.data;
                document.getElementById('editBebanOperasionalId').value = item.id;
                document.getElementById('editKategori').value = item.kategori || '';
                document.getElementById('editNamaBeban').value = item.nama_beban;
                document.getElementById('editBudgetBulanan').value = item.budget_bulanan || '';
                document.getElementById('editKeterangan').value = item.keterangan || '';
                document.getElementById('editStatus').value = item.status || 'aktif';
                
                const modal = new bootstrap.Modal(document.getElementById('editBebanOperasionalModal'));
                modal.show();
                
                // Clear field errors when user starts typing
                clearFieldErrorsOnInput();
            } else {
                showAlert('danger', data.message || 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat memuat data');
        });
}

// Clear field errors when user interacts with form fields
function clearFieldErrorsOnInput() {
    const formFields = ['editNamaBeban', 'editBudgetBulanan', 'editKeterangan', 'editStatus'];
    
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const errorDiv = this.parentNode.querySelector('.field-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            });
            
            field.addEventListener('change', function() {
                this.classList.remove('is-invalid');
                const errorDiv = this.parentNode.querySelector('.field-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            });
        }
    });
}

// Delete Beban Operasional
function deleteBebanOperasional(id) {
    if (confirm('Apakah Anda yakin ingin menghapus Beban Operasional ini?')) {
        fetch(`{{ route('master-data.bop.beban-operasional.delete', ':id') }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                
                // Update URL to stay on Beban Operasional tab
                const url = new URL(window.location);
                url.searchParams.set('tab', 'beban-operasional');
                window.history.replaceState({}, '', url);
                
                // Reload data
                loadBebanOperasionalData();
            } else {
                showAlert('danger', data.message || 'Gagal menghapus data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat menghapus data');
        });
    }
}

// Save Beban Operasional (Add)
function saveBebanOperasional() {
    event.preventDefault(); // Prevent default form submission
    
    console.log('saveBebanOperasional called');
    
    const form = document.getElementById('addBebanOperasionalForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (!form) {
        console.error('Form not found!');
        return;
    }
    
    // Disable submit button to prevent double submission
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
    
    const formData = new FormData(form);
    console.log('FormData prepared:', Object.fromEntries(formData));
    
    fetch('{{ route('master-data.bop.beban-operasional.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('saveBebanOperasional response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('saveBebanOperasional response data:', data);
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addBebanOperasionalModal')).hide();
            form.reset();
            
            // Update URL to stay on Beban Operasional tab
            const url = new URL(window.location);
            url.searchParams.set('tab', 'beban-operasional');
            window.history.replaceState({}, '', url);
            
            // Reload data
            setTimeout(() => {
                loadBebanOperasionalData();
            }, 500);
        } else {
            if (data.errors) {
                // Show validation errors
                let errorMessage = 'Validasi gagal:<br>';
                Object.entries(data.errors).forEach(([field, errors]) => {
                    errorMessage += `${errors.join('<br>')}<br>`;
                });
                showAlert('danger', errorMessage);
            } else {
                showAlert('danger', data.message || 'Gagal menyimpan data');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menyimpan data: ' + error.message);
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Simpan';
    });
}

// Update Beban Operasional (Edit)
function updateBebanOperasional() {
    event.preventDefault(); // Prevent default form submission
    
    const form = document.getElementById('editBebanOperasionalForm');
    const submitButton = form.querySelector('button[type="submit"]');
    const id = document.getElementById('editBebanOperasionalId').value;
    
    // Disable submit button to prevent double submission
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memperbarui...';
    
    const formData = new FormData(form);
    
    // Add CSRF token to form data if not already present
    if (!formData.has('_token')) {
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    }
    
    fetch(`{{ route('master-data.bop.beban-operasional.update', ':id') }}`.replace(':id', id), {
        method: 'POST', // Use POST with _method=PUT for better compatibility
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.log('Error response text:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editBebanOperasionalModal')).hide();
            
            // Update URL to stay on Beban Operasional tab
            const url = new URL(window.location);
            url.searchParams.set('tab', 'beban-operasional');
            window.history.replaceState({}, '', url);
            
            // Reload data
            setTimeout(() => {
                loadBebanOperasionalData();
            }, 300);
        } else {
            if (data.errors) {
                // Clear previous errors
                document.querySelectorAll('.field-error').forEach(el => el.remove());
                
                let errorMessage = 'Validasi gagal:<br>';
                let hasFieldErrors = false;
                
                Object.entries(data.errors).forEach(([field, errors]) => {
                    errorMessage += `${errors.join('<br>')}<br>`;
                    
                    // Show error below field
                    const fieldElement = document.getElementById('edit' + field.charAt(0).toUpperCase() + field.slice(1));
                    if (fieldElement) {
                        hasFieldErrors = true;
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'field-error text-danger small mt-1';
                        errorDiv.textContent = errors[0];
                        fieldElement.parentNode.appendChild(errorDiv);
                        fieldElement.classList.add('is-invalid');
                    }
                });
                
                if (hasFieldErrors) {
                    errorMessage += '<br>Silakan perbaiki field yang ditandai merah.';
                }
                
                showAlert('danger', errorMessage);
            } else {
                showAlert('danger', data.message || 'Gagal memperbarui data');
            }
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        
        // Try to extract meaningful error message
        let errorMessage = 'Terjadi kesalahan saat memperbarui data';
        
        if (error.message.includes('422')) {
            errorMessage = 'Validasi gagal. Silakan periksa kembali input Anda.';
        } else if (error.message.includes('404')) {
            errorMessage = 'Data tidak ditemukan.';
        } else if (error.message.includes('403')) {
            errorMessage = 'Anda tidak memiliki izin untuk mengubah data ini.';
        } else if (error.message.includes('500')) {
            errorMessage = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
        } else if (error.message.includes('HTTP')) {
            // Extract HTTP status and details
            errorMessage = error.message;
        }
        
        showAlert('danger', errorMessage);
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Update';
    });
}

// Show alert helper
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Setup auto-fill for BTKL fields
    setupBtklAutoFill();
    
    // Check URL parameter for tab state
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    // Set initial tab state
    if (tabParam === 'beban-operasional') {
        switchToBebanOperasional();
    } else {
        switchToBopProses();
    }
    
    // Handle edit BOP Proses form submit
    document.getElementById('editBopProsesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const bopId = document.getElementById('editBopProsesId').value;
        
        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
        
        fetch(`/master-data/bop/update-proses-simple/${bopId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editBopProsesModal')).hide();
                
                // Show success message
                alert(data.message || 'BOP Proses berhasil diperbarui');
                
                // Reload page
                window.location.reload();
            } else {
                alert(data.message || 'Gagal memperbarui BOP Proses');
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>

<!-- Add Beban Operasional Modal -->
<div class="modal fade" id="addBebanOperasionalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Beban Operasional
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBebanOperasionalForm" method="POST" action="javascript:void(0);" onsubmit="saveBebanOperasional(); return false;">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addKategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" id="addKategori" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Administrasi">Administrasi</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Utilitas">Utilitas</option>
                            <option value="Distribusi">Distribusi</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addNamaBeban" class="form-label">Nama Beban <span class="text-danger">*</span></label>
                        <input type="text" name="nama_beban" id="addNamaBeban" class="form-control" placeholder="Contoh: Gaji Karyawan" required>
                    </div>
                    <div class="mb-3">
                        <label for="addBudgetBulanan" class="form-label">Budget Bulanan</label>
                        <input type="number" name="budget_bulanan" id="addBudgetBulanan" class="form-control" placeholder="0" min="0" step="0.01">
                        <small class="form-text text-muted">Anggaran bulanan untuk beban ini. Bukan nominal pembayaran aktual.</small>
                    </div>
                    <div class="mb-3">
                        <label for="addKeterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="addKeterangan" class="form-control" rows="2" placeholder="Opsional"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="addStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="addStatus" class="form-select" required>
                            <option value="aktif" selected>Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Beban Operasional Modal -->
<div class="modal fade" id="editBebanOperasionalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Beban Operasional
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBebanOperasionalForm" method="POST" action="javascript:void(0);" onsubmit="updateBebanOperasional(); return false;">
                @csrf
                @method('PUT')
                <input type="hidden" id="editBebanOperasionalId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editKategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" id="editKategori" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Administrasi">Administrasi</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Utilitas">Utilitas</option>
                            <option value="Distribusi">Distribusi</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editNamaBeban" class="form-label">Nama Beban <span class="text-danger">*</span></label>
                        <input type="text" name="nama_beban" id="editNamaBeban" class="form-control" placeholder="Contoh: Gaji Karyawan" required>
                    </div>
                    <div class="mb-3">
                        <label for="editBudgetBulanan" class="form-label">Budget Bulanan</label>
                        <input type="number" name="budget_bulanan" id="editBudgetBulanan" class="form-control" placeholder="0" min="0" step="0.01">
                        <small class="form-text text-muted">Anggaran bulanan untuk beban ini. Bukan nominal pembayaran aktual.</small>
                    </div>
                    <div class="mb-3">
                        <label for="editKeterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="editKeterangan" class="form-control" rows="2" placeholder="Opsional"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="editStatus" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endpush
@endsection

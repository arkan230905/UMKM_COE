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
            <button type="button" class="btn btn-outline-primary active" id="bopProsesTab" onclick="switchTab('bop-proses')">
                <i class="fas fa-industry me-2"></i>BOP Proses
            </button>
            <button type="button" class="btn btn-outline-primary" id="bebanOperasionalTab" onclick="switchTab('beban-operasional')">
                <i class="fas fa-chart-line me-2"></i>Beban Operasional
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="bopProsesContent" class="tab-content" style="display: block;">
        <!-- BOP Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-wide">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">No</th>
                                <th style="width: 25%">Nama Proses</th>
                                <th style="width: 15%">BOP/Jam</th>
                                <th style="width: 15%">BOP/pcs</th>
                                <th style="width: 20%">Biaya/Produk</th>
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
                                        <i class="bi bi-clock-fill me-2 text-info opacity-50"></i>
                                        <div>
                                            <div class="fw-semibold text-info">{{ $bop->total_bop_per_jam_formatted }}</div>
                                            <small class="text-muted">Per jam</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-box-seam me-2 text-warning opacity-50"></i>
                                        <div>
                                            <div class="fw-semibold text-warning">Rp {{ number_format($bop->bop_per_unit, 2, ',', '.') }}</div>
                                            <small class="text-muted">Per pcs</small>
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
                                <td colspan="6" class="text-center py-4">
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
                            
                            <!-- Total Row - Only for Biaya/Produk -->
                            @if($bopProses->count() > 0)
                            <tr class="table-active fw-bold">
                                <td colspan="4" class="text-end">
                                    <span class="text-muted">Total Biaya/Produk:</span>
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
    <div id="bebanOperasionalContent" class="tab-content" style="display: none;">
        <!-- Filter Section -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus" onchange="filterBebanOperasional()">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-4">
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
                                <th style="width: 30%">Nama Beban</th>
                                <th style="width: 20%" class="text-end">Budget Bulanan</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 20%">Aksi</th>
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
                                <li>BOP/Jam: {{ $bop->total_bop_per_jam_formatted }}</li>
                                <li>BOP/pcs: Rp {{ number_format($bop->bop_per_unit, 2, ',', '.') }}</li>
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
                const prosesNama = selectedOption.getAttribute('data-nama') || '';
                const jabatan = selectedOption.getAttribute('data-jabatan') || '';
                
                // Fill BTKL fields with data from ProsesProduksi
                document.getElementById('kapasitas').value = kapasitas;
                document.getElementById('btkl_per_jam').value = tarif;
                
                // Calculate BTKL per pcs
                const btklPerPcs = kapasitas > 0 ? tarif / kapasitas : 0;
                document.getElementById('btkl_per_pcs').value = btklPerPcs.toFixed(2);
                
                // Set fields as readonly (auto-generated)
                document.getElementById('kapasitas').readOnly = true;
                document.getElementById('btkl_per_jam').readOnly = true;
                document.getElementById('btkl_per_pcs').readOnly = true;
                
                // Show info if data is available
                if (kapasitas > 0 && tarif > 0) {
                    btklInfo.classList.remove('d-none');
                    btklInfoText.textContent = `Data BTKL tersedia: Kapasitas ${kapasitas} pcs/jam, Tarif Rp ${tarif.toLocaleString('id-ID')}/jam (${jabatan})`;
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
                document.getElementById('kapasitas').readOnly = false;
                document.getElementById('btkl_per_jam').readOnly = false;
                document.getElementById('btkl_per_pcs').readOnly = false;
                
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

// Calculate BOP summary based on new formula
function calculateBopSummary() {
    // Get BTKL values
    const kapasitas = parseFloat(document.getElementById('kapasitas').value) || 0;
    const btklPerJam = parseFloat(document.getElementById('btkl_per_jam').value) || 0;
    
    // Calculate BTKL per pcs
    const btklPerPcs = kapasitas > 0 ? btklPerJam / kapasitas : 0;
    
    // Calculate Total BOP per jam from components
    const componentRates = document.querySelectorAll('.komponen-rate');
    let totalBopPerJam = 0;
    componentRates.forEach(input => {
        totalBopPerJam += parseFloat(input.value) || 0;
    });
    
    // Calculate BOP per pcs
    const bopPerPcs = kapasitas > 0 ? totalBopPerJam / kapasitas : 0;
    
    // Calculate Biaya per produk
    const biayaPerProduk = btklPerPcs + bopPerPcs;
    
    // Calculate Biaya per jam
    const biayaPerJam = btklPerJam + totalBopPerJam;
    
    // Update readonly fields
    const totalBopInput = document.getElementById('total_bop_per_jam');
    if (totalBopInput) {
        totalBopInput.value = totalBopPerJam.toFixed(2);
    }
    
    // Update BOP per pcs field
    const bopPerPcsInput = document.getElementById('bop_per_pcs');
    if (bopPerPcsInput) {
        bopPerPcsInput.value = bopPerPcs.toFixed(2);
    }
    
    // Update Biaya per produk field
    const biayaPerProdukInput = document.getElementById('biaya_per_produk');
    if (biayaPerProdukInput) {
        biayaPerProdukInput.value = biayaPerProduk.toFixed(2);
    }
    
    // Update Biaya per jam field
    const biayaPerJamInput = document.getElementById('biaya_per_jam');
    if (biayaPerJamInput) {
        biayaPerJamInput.value = biayaPerJam.toFixed(2);
    }
}

// Clear BOP summary
function clearBopSummary() {
    const fields = ['total_bop_per_jam', 'bop_per_pcs', 'biaya_per_produk', 'biaya_per_jam'];
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
    // Load edit modal content or show existing modal
    fetch(`/master-data/bop/edit-proses-modal/${id}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('editBopContent').innerHTML = data;
            const modal = new bootstrap.Modal(document.getElementById('editBopModal'));
            modal.show();
            
            // Setup auto-fill for edit modal after content is loaded
            setTimeout(() => setupEditModalAutoFill(id), 100);
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function setupEditModalAutoFill(bopId) {
    // Find the BOP data and setup edit modal fields
    fetch(`/master-data/bop/get-proses/${bopId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.bop) {
                const bop = data.bop;
                const prosesId = bop.proses_produksi_id;
                
                // Find the process option in edit modal
                const editProcessSelect = document.getElementById('editProsesProduksiId');
                if (editProcessSelect) {
                    // Set the selected process
                    editProcessSelect.value = prosesId;
                    
                    // Get the selected option data
                    const selectedOption = editProcessSelect.options[editProcessSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        const kapasitas = parseFloat(selectedOption.getAttribute('data-kapasitas')) || 0;
                        const tarif = parseFloat(selectedOption.getAttribute('data-tarif')) || 0;
                        
                        // Fill edit modal BTKL fields
                        const editKapasitas = document.getElementById('editKapasitas');
                        const editBtklPerJam = document.getElementById('editBtklPerJam');
                        const editBtklPerPcs = document.getElementById('editBtklPerPcs');
                        
                        if (editKapasitas) {
                            editKapasitas.value = kapasitas;
                            editKapasitas.readOnly = true;
                        }
                        
                        if (editBtklPerJam) {
                            editBtklPerJam.value = tarif;
                            editBtklPerJam.readOnly = true;
                        }
                        
                        if (editBtklPerPcs) {
                            const btklPerPcs = kapasitas > 0 ? tarif / kapasitas : 0;
                            editBtklPerPcs.value = btklPerPcs.toFixed(2);
                            editBtklPerPcs.readOnly = true;
                        }
                        
                        // Show info for edit modal
                        const editBtklInfo = document.getElementById('editBtklInfo');
                        const editBtklInfoText = document.getElementById('editBtklInfoText');
                        const jabatan = selectedOption.getAttribute('data-jabatan') || '';
                        
                        if (editBtklInfo && editBtklInfoText) {
                            if (kapasitas > 0 && tarif > 0) {
                                editBtklInfo.classList.remove('d-none');
                                editBtklInfoText.textContent = `Data BTKL tersedia: Kapasitas ${kapasitas} pcs/jam, Tarif Rp ${tarif.toLocaleString('id-ID')}/jam (${jabatan})`;
                            } else {
                                editBtklInfo.classList.remove('d-none');
                                editBtklInfo.classList.remove('alert-info');
                                editBtklInfo.classList.add('alert-warning');
                                editBtklInfoText.textContent = 'Data BTKL untuk proses ini belum lengkap.';
                            }
                        }
                    }
                }
                
                // Load existing components
                loadEditComponents(bop);
                
                // Trigger calculation for edit modal
                calculateEditBopSummary();
            }
        })
        .catch(error => {
            console.error('Error loading BOP data for edit:', error);
        });
}

// Load components for edit modal
function loadEditComponents(bop) {
    const editKomponenRows = document.getElementById('editKomponenRows');
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

// Calculate BOP summary for edit modal
function calculateEditBopSummary() {
    // Get BTKL values from edit modal
    const kapasitas = parseFloat(document.getElementById('editKapasitas').value) || 0;
    const btklPerJam = parseFloat(document.getElementById('editBtklPerJam').value) || 0;
    
    // Calculate BTKL per pcs
    const btklPerPcs = kapasitas > 0 ? btklPerJam / kapasitas : 0;
    
    // Calculate Total BOP per jam from components
    const componentRates = document.querySelectorAll('.edit-komponen-rate');
    let totalBopPerJam = 0;
    componentRates.forEach(input => {
        totalBopPerJam += parseFloat(input.value) || 0;
    });
    
    // Calculate BOP per pcs
    const bopPerPcs = kapasitas > 0 ? totalBopPerJam / kapasitas : 0;
    
    // Calculate Biaya per produk
    const biayaPerProduk = btklPerPcs + bopPerPcs;
    
    // Calculate Biaya per jam
    const biayaPerJam = btklPerJam + totalBopPerJam;
    
    // Update readonly fields in edit modal
    const editTotalBopInput = document.getElementById('editTotalBopPerJam');
    if (editTotalBopInput) {
        editTotalBopInput.value = totalBopPerJam.toFixed(2);
    }
    
    const editBopPerPcsInput = document.getElementById('editBopPerPcs');
    if (editBopPerPcsInput) {
        editBopPerPcsInput.value = bopPerPcs.toFixed(2);
    }
    
    const editBiayaPerProdukInput = document.getElementById('editBiayaPerProduk');
    if (editBiayaPerProdukInput) {
        editBiayaPerProdukInput.value = biayaPerProduk.toFixed(2);
    }
    
    const editBiayaPerJamInput = document.getElementById('editBiayaPerJam');
    if (editBiayaPerJamInput) {
        editBiayaPerJamInput.value = biayaPerJam.toFixed(2);
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

// Tab switching functionality
function switchTab(tab) {
    const bopProsesTab = document.getElementById('bopProsesTab');
    const bebanOperasionalTab = document.getElementById('bebanOperasionalTab');
    const bopProsesContent = document.getElementById('bopProsesContent');
    const bebanOperasionalContent = document.getElementById('bebanOperasionalContent');
    const addButton = document.getElementById('addButton');

    if (tab === 'bop-proses') {
        // Show BOP Proses tab
        bopProsesTab.classList.add('active');
        bebanOperasionalTab.classList.remove('active');
        bopProsesContent.style.display = 'block';
        bebanOperasionalContent.style.display = 'none';
        
        // Update add button
        addButton.innerHTML = '<i class="fas fa-plus me-2"></i>Tambah BOP Proses';
        addButton.setAttribute('onclick', "openAddModal()");
        
        // Update URL without page reload (clean URL)
        const url = new URL(window.location);
        // Clear all existing search params
        url.searchParams.delete('tab');
        url.searchParams.delete('tanggal');
        url.searchParams.delete('kategori');
        url.searchParams.delete('nama_beban');
        url.searchParams.delete('nominal');
        url.searchParams.delete('keterangan');
        window.history.replaceState({}, '', url);
        
    } else if (tab === 'beban-operasional') {
        // Show Beban Operasional tab
        bebanOperasionalTab.classList.add('active');
        bopProsesTab.classList.remove('active');
        bebanOperasionalContent.style.display = 'block';
        bopProsesContent.style.display = 'none';
        
        // Update add button
        addButton.innerHTML = '<i class="fas fa-plus me-2"></i>Tambah Beban Operasional';
        addButton.setAttribute('onclick', "openBebanOperasionalModal()");
        
        // Update URL without page reload (clean URL)
        const url = new URL(window.location);
        // Clear all existing search params except tab
        const tabValue = url.searchParams.get('tab');
        url.searchParams.delete('tab');
        url.searchParams.delete('tanggal');
        url.searchParams.delete('kategori');
        url.searchParams.delete('nama_beban');
        url.searchParams.delete('nominal');
        url.searchParams.delete('keterangan');
        url.searchParams.set('tab', 'beban-operasional');
        window.history.replaceState({}, '', url);
        
        // Load Beban Operasional data
        loadBebanOperasionalData();
    }
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
    const kategori = document.getElementById('filterKategori').value;
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('filterSearch').value;
    
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
    
    fetch(`{{ route('master-data.bop.beban-operasional.data') }}?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderBebanOperasionalTable(data.data);
                // Summary tidak diperlukan untuk master data
            } else {
                showAlert('danger', data.message || 'Gagal memuat data');
                // Show empty state on error
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
            // Show empty state on error
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
            <td>${item.kategori}</td>
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
                document.getElementById('editKategori').value = item.kategori;
                document.getElementById('editNamaBeban').value = item.nama_beban;
                document.getElementById('editBudgetBulanan').value = item.budget_bulanan || '';
                document.getElementById('editKeterangan').value = item.keterangan || '';
                document.getElementById('editStatus').value = item.status || 'aktif';
                
                const modal = new bootstrap.Modal(document.getElementById('editBebanOperasionalModal'));
                modal.show();
            } else {
                showAlert('danger', data.message || 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat memuat data');
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
    
    fetch(`{{ route('master-data.bop.beban-operasional.update', ':id') }}`.replace(':id', id), {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
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
                let errorMessage = 'Validasi gagal:<br>';
                Object.entries(data.errors).forEach(([field, errors]) => {
                    errorMessage += `${errors.join('<br>')}<br>`;
                });
                showAlert('danger', errorMessage);
            } else {
                showAlert('danger', data.message || 'Gagal memperbarui data');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat memperbarui data: ' + error.message);
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
    // Check URL parameter for tab state
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    // Clean URL from unwanted query parameters (form fields)
    const url = new URL(window.location);
    let hasUnwantedParams = false;
    
    // Check for form field parameters and clean them
    ['tanggal', 'kategori', 'nama_beban', 'nominal', 'keterangan'].forEach(param => {
        if (url.searchParams.has(param)) {
            url.searchParams.delete(param);
            hasUnwantedParams = true;
        }
    });
    
    // Update URL if we cleaned unwanted parameters
    if (hasUnwantedParams) {
        if (tabParam === 'beban-operasional') {
            url.searchParams.set('tab', 'beban-operasional');
        }
        window.history.replaceState({}, '', url);
    }
    
    // Set initial tab state based on URL parameter or default to BOP Proses
    if (tabParam === 'beban-operasional') {
        switchTab('beban-operasional');
    } else {
        switchTab('bop-proses');
    }
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

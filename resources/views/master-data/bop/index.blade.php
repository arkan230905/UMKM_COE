@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>BOP (Biaya Overhead Pabrik)
        </h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBopProsesModal">
            <i class="fas fa-plus me-2"></i>Tambah BOP Proses
        </button>
    </div>

    <!-- BOP Process Cards - Individual -->
    @foreach($prosesProduksis as $proses)
        @php
            // Set exact values based on process name
            if ($proses->nama_proses === 'Menggoreng') {
                $kapasitasPerJam = 50;
                $btklPerJam = 45000;
                $btklPerPcs = 900;
                $totalBopPerJam = 42000;
                $bopPerPcs = 840;
                $biayaPerProduk = 1740;
                $biayaPerJam = 87000;
                $komponenBop = [
                    ['name' => 'Listrik Mesin', 'rate_per_hour' => 5000, 'description' => 'Pemanas Minyak'],
                    ['name' => 'Gas / BBM', 'rate_per_hour' => 20000, 'description' => ''],
                    ['name' => 'Maintenace', 'rate_per_hour' => 5000, 'description' => 'Mesin Goreng'],
                    ['name' => 'Penyusutan Mesin', 'rate_per_hour' => 10000, 'description' => 'Rutin'],
                    ['name' => 'Air & Kebersihan', 'rate_per_hour' => 2000, 'description' => 'Cuci alat']
                ];
            } elseif ($proses->nama_proses === 'Membumbui') {
                $kapasitasPerJam = 200;
                $btklPerJam = 48000;
                $btklPerPcs = 240;
                $totalBopPerJam = 10000;
                $bopPerPcs = 50;
                $biayaPerProduk = 290;
                $biayaPerJam = 58000;
                $komponenBop = [
                    ['name' => 'Listrik Mixer', 'rate_per_hour' => 4000, 'description' => 'Mesin Ringan'],
                    ['name' => 'Penyusutan Alat', 'rate_per_hour' => 3000, 'description' => 'Drum / Mixer'],
                    ['name' => 'Maintenace', 'rate_per_hour' => 2000, 'description' => 'Rutin'],
                    ['name' => 'Kebersihan', 'rate_per_hour' => 1000, 'description' => 'Rutin']
                ];
            } elseif ($proses->nama_proses === 'Packing') {
                $kapasitasPerJam = 50;
                $btklPerJam = 45000;
                $btklPerPcs = 900;
                $totalBopPerJam = 13000;
                $bopPerPcs = 260;
                $biayaPerProduk = 1160;
                $biayaPerJam = 58000;
                $komponenBop = [
                    ['name' => 'Listrik', 'rate_per_hour' => 3000, 'description' => ''],
                    ['name' => 'Penyusutan Alat', 'rate_per_hour' => 4000, 'description' => 'Alat Packing'],
                    ['name' => 'Plastik Kemasan', 'rate_per_hour' => 5000, 'description' => 'Penunjang'],
                    ['name' => 'Kebersihan', 'rate_per_hour' => 1000, 'description' => 'Area']
                ];
            } else {
                continue; // Skip if not matching process
            }
        @endphp
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Biaya per Proses:</h5>
            </div>
            <div class="card-body">
                <!-- Process Info Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Proses</th>
                                <th></th>
                                <th>Kapasitas</th>
                                <th></th>
                                <th>BTKL / jam</th>
                                <th></th>
                                <th>BTKL / pcs</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>{{ $proses->nama_proses }}</strong></td>
                                <td></td>
                                <td>{{ $kapasitasPerJam }} pcs/jam</td>
                                <td></td>
                                <td>{{ number_format($btklPerJam, 0, ',', '.') }}</td>
                                <td></td>
                                <td>{{ number_format($btklPerPcs, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty row for spacing -->
                <div class="mb-4"></div>
                
                <!-- BOP Components Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered" id="bop-table-{{ $proses->id }}">
                        <thead class="table-light">
                            <tr>
                                <th>Komponen</th>
                                <th>Rp / Jam</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($komponenBop as $komponen)
                                <tr data-index="{{ $loop->index }}">
                                    <td>{{ $komponen['name'] }}</td>
                                    <td>{{ number_format($komponen['rate_per_hour'], 0, ',', '.') }}</td>
                                    <td>{{ $komponen['description'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty row for spacing -->
                <div class="mb-4"></div>
                
                <!-- Summary Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Total BOP /jam</th>
                                <th>BOP / pcs</th>
                                <th>Biaya / produk</th>
                                <th>Biaya / jam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ number_format($totalBopPerJam, 0, ',', '.') }}</td>
                                <td>{{ number_format($bopPerPcs, 0, ',', '.') }}</td>
                                <td>RP{{ number_format($biayaPerProduk, 0, ',', '.') }},00</td>
                                <td>{{ number_format($biayaPerJam, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Actions -->
                <div class="text-center mt-3">
                    <button class="btn btn-warning btn-sm" onclick="editBopProses({{ $proses->id }})" title="Edit BOP">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                
                            </div>
        </div>
    @endforeach
    
    <!-- Final Summary -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Biaya Per Produk</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Biaya Per produk</th>
                            <th>Penggorengan</th>
                            <th>Perbumbuan</th>
                            <th>Pengemasan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Biaya Per produk</strong></td>
                            <td>RP1.740,00</td>
                            <td>RP290,00</td>
                            <td>RP1.160,00</td>
                            <td><strong>RP3.190,00</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('master-data.bop.modals')

<script>
// Store BOP data for each process
let bopData = {};

// Initialize BOP data
@foreach($prosesProduksis as $proses)
    @php
        if ($proses->nama_proses === 'Menggoreng') {
            $komponenData = [
                ['name' => 'Listrik Mesin', 'rate_per_hour' => 5000, 'description' => 'Pemanas Minyak'],
                ['name' => 'Gas / BBM', 'rate_per_hour' => 20000, 'description' => ''],
                ['name' => 'Maintenace', 'rate_per_hour' => 5000, 'description' => 'Mesin Goreng'],
                ['name' => 'Penyusutan Mesin', 'rate_per_hour' => 10000, 'description' => 'Rutin'],
                ['name' => 'Air & Kebersihan', 'rate_per_hour' => 2000, 'description' => 'Cuci alat']
            ];
        } elseif ($proses->nama_proses === 'Membumbui') {
            $komponenData = [
                ['name' => 'Listrik Mixer', 'rate_per_hour' => 4000, 'description' => 'Mesin Ringan'],
                ['name' => 'Penyusutan Alat', 'rate_per_hour' => 3000, 'description' => 'Drum / Mixer'],
                ['name' => 'Maintenace', 'rate_per_hour' => 2000, 'description' => 'Rutin'],
                ['name' => 'Kebersihan', 'rate_per_hour' => 1000, 'description' => 'Rutin']
            ];
        } elseif ($proses->nama_proses === 'Packing') {
            $komponenData = [
                ['name' => 'Listrik', 'rate_per_hour' => 3000, 'description' => ''],
                ['name' => 'Penyusutan Alat', 'rate_per_hour' => 4000, 'description' => 'Alat Packing'],
                ['name' => 'Plastik Kemasan', 'rate_per_hour' => 5000, 'description' => 'Penunjang'],
                ['name' => 'Kebersihan', 'rate_per_hour' => 1000, 'description' => 'Area']
            ];
        } else {
            $komponenData = [];
        }
    @endphp
    bopData[{{ $proses->id }}] = @json($komponenData);
@endforeach

// Update BOP component
function updateBopComponent(prosesId, index, field, value) {
    if (!bopData[prosesId]) {
        bopData[prosesId] = [];
    }
    
    if (!bopData[prosesId][index]) {
        bopData[prosesId][index] = { name: '', rate_per_hour: 0, description: '' };
    }
    
    bopData[prosesId][index][field] = value;
    
    // Update summary table
    updateSummaryTable(prosesId);
    
    // Save to backend (you can implement AJAX call here)
    saveBopData(prosesId);
}

// Add new BOP component
function addBopComponent(prosesId) {
    if (!bopData[prosesId]) {
        bopData[prosesId] = [];
    }
    
    bopData[prosesId].push({
        name: '',
        rate_per_hour: 0,
        description: ''
    });
    
    // Refresh table
    refreshBopTable(prosesId);
    updateSummaryTable(prosesId);
}

// Remove BOP component
function removeBopComponent(prosesId, index) {
    if (!bopData[prosesId]) return;
    
    bopData[prosesId].splice(index, 1);
    
    // Refresh table
    refreshBopTable(prosesId);
    updateSummaryTable(prosesId);
}

// Refresh BOP table
function refreshBopTable(prosesId) {
    const table = document.getElementById(`bop-table-${prosesId}`);
    const tbody = table.querySelector('tbody');
    
    // Clear existing rows except the "Add" button row
    const rows = tbody.querySelectorAll('tr');
    rows.forEach((row, index) => {
        if (index < rows.length - 1) {
            row.remove();
        }
    });
    
    // Re-add components
    bopData[prosesId].forEach((component, index) => {
        const newRow = tbody.insertRow(index);
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" value="${component.name}" 
                       onchange="updateBopComponent(${prosesId}, ${index}, 'name', this.value)">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" value="${component.rate_per_hour}" 
                       onchange="updateBopComponent(${prosesId}, ${index}, 'rate_per_hour', this.value)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" value="${component.description}" 
                       onchange="updateBopComponent(${prosesId}, ${index}, 'description', this.value)">
            </td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="removeBopComponent(${prosesId}, ${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
    });
}

// Update summary table
function updateSummaryTable(prosesId) {
    if (!bopData[prosesId]) return;
    
    const components = bopData[prosesId];
    const totalBopPerJam = components.reduce((sum, comp) => sum + (comp.rate_per_hour || 0), 0);
    
    // Find the summary table for this process
    const summaryTable = document.querySelector(`#proses-${prosesId}-summary`);
    if (summaryTable) {
        // Update the summary values
        const cells = summaryTable.querySelectorAll('tbody tr td');
        if (cells.length >= 4) {
            cells[0].textContent = totalBopPerJam.toLocaleString('id-ID');
            // You may need to update other calculations based on your business logic
        }
    }
}

// Edit BOP process
function editBopProses(prosesId) {
    // Set the process ID in the hidden field
    document.getElementById('editBopProsesId').value = prosesId;
    
    // Find the process data
    let processName = '';
    let komponenData = [];
    
    // Get process name and components from the stored data
    @foreach($prosesProduksis as $proses)
        if (prosesId == {{ $proses->id }}) {
            processName = '{{ $proses->nama_proses }}';
            komponenData = bopData[{{ $proses->id }}] || [];
        }
    @endforeach
    
    // Set default values based on process name
    let kapasitas = 0;
    let btklPerJam = 0;
    
    if (processName === 'Menggoreng') {
        kapasitas = 50;
        btklPerJam = 45000;
        if (komponenData.length === 0) {
            komponenData = [
                { name: 'Listrik Mesin', rate_per_hour: 5000, description: 'Pemanas Minyak' },
                { name: 'Gas / BBM', rate_per_hour: 20000, description: '' },
                { name: 'Maintenace', rate_per_hour: 5000, description: 'Mesin Goreng' },
                { name: 'Penyusutan Mesin', rate_per_hour: 10000, description: 'Rutin' },
                { name: 'Air & Kebersihan', rate_per_hour: 2000, description: 'Cuci alat' }
            ];
        }
    } else if (processName === 'Membumbui') {
        kapasitas = 200;
        btklPerJam = 48000;
        if (komponenData.length === 0) {
            komponenData = [
                { name: 'Listrik Mixer', rate_per_hour: 4000, description: 'Mesin Ringan' },
                { name: 'Penyusutan Alat', rate_per_hour: 3000, description: 'Drum / Mixer' },
                { name: 'Maintenace', rate_per_hour: 2000, description: 'Rutin' },
                { name: 'Kebersihan', rate_per_hour: 1000, description: 'Rutin' }
            ];
        }
    } else if (processName === 'Packing') {
        kapasitas = 50;
        btklPerJam = 45000;
        if (komponenData.length === 0) {
            komponenData = [
                { name: 'Listrik', rate_per_hour: 3000, description: '' },
                { name: 'Penyusutan Alat', rate_per_hour: 4000, description: 'Alat Packing' },
                { name: 'Plastik Kemasan', rate_per_hour: 5000, description: 'Penunjang' },
                { name: 'Kebersihan', rate_per_hour: 1000, description: 'Area' }
            ];
        }
    }
    
    // Update modal fields
    document.getElementById('editNamaProses').value = processName;
    document.getElementById('editKapasitas').value = kapasitas;
    document.getElementById('editBtklPerJam').value = btklPerJam;
    
    // Calculate BTKL per pcs
    const btklPerPcs = kapasitas > 0 ? btklPerJam / kapasitas : 0;
    document.getElementById('editBtklPerPcs').value = btklPerPcs;
    
    // Update components table
    updateEditKomponenTable(komponenData);
    
    // Show modal
    try {
        const modalElement = document.getElementById('editBopProsesModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Modal element not found');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
    }
}

// Update edit komponen table
function updateEditKomponenTable(komponen) {
    const tbody = document.getElementById('editKomponenRows');
    tbody.innerHTML = '';
    
    komponen.forEach((component, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" value="${component.name}" 
                       id="edit_nama_${index}" placeholder="Nama komponen">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" value="${component.rate_per_hour}" 
                       id="edit_rate_${index}" min="0" step="0.01" placeholder="0">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" value="${component.description}" 
                       id="edit_desc_${index}" placeholder="Keterangan">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeEditKomponen(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    // Add empty row for new component
    const addRow = document.createElement('tr');
    addRow.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" 
                   id="edit_nama_new" placeholder="Nama komponen">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" 
                   id="edit_rate_new" min="0" step="0.01" placeholder="0">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" 
                   id="edit_desc_new" placeholder="Keterangan">
        </td>
        <td>
            <button type="button" class="btn btn-success btn-sm" onclick="addEditKomponen()">
                <i class="fas fa-plus"></i> Tambah
            </button>
        </td>
    `;
    tbody.appendChild(addRow);
}

// Add new component in edit modal
function addEditKomponen() {
    const tbody = document.getElementById('editKomponenRows');
    if (!tbody) return;
    
    const rowCount = tbody.children.length;
    
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" 
                   id="edit_nama_${rowCount}" placeholder="Nama komponen">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" 
                   id="edit_rate_${rowCount}" min="0" step="0.01" placeholder="0">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" 
                   id="edit_desc_${rowCount}" placeholder="Keterangan">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeEditKomponen(${rowCount})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
}

// Remove component in edit modal
function removeEditKomponen(index) {
    const tbody = document.getElementById('editKomponenRows');
    if (!tbody) return;
    
    const rows = tbody.children;
    if (index < rows.length) {
        rows[index].remove();
        // Re-index remaining rows
        Array.from(rows).forEach((row, i) => {
            if (i >= index) {
                // Update IDs for remaining rows
                const inputs = row.querySelectorAll('input, button');
                inputs.forEach(input => {
                    if (input.id) {
                        const oldId = input.id;
                        const newId = oldId.replace(/_\d+$/, `_${i}`);
                        input.id = newId;
                        
                        // Update onclick for button
                        if (input.onclick) {
                            input.onclick = new Function('removeEditKomponen(' + i + ')');
                        }
                    }
                });
            }
        });
    }
}

// Save edited BOP data
function saveEditedBop() {
    const prosesId = document.getElementById('editBopProsesId').value;
    const tbody = document.getElementById('editKomponenRows');
    if (!tbody) return;
    
    const rows = tbody.children;
    const komponen = [];
    
    // Collect data from all rows except the "add new" row
    for (let i = 0; i < rows.length; i++) {
        const namaInput = document.getElementById(`edit_nama_${i}`);
        const rateInput = document.getElementById(`edit_rate_${i}`);
        const descInput = document.getElementById(`edit_desc_${i}`);
        
        if (namaInput && rateInput && descInput) {
            const nama = namaInput.value || '';
            const rate = parseFloat(rateInput.value) || 0;
            const desc = descInput.value || '';
            
            if (nama || rate > 0) {
                komponen.push({
                    name: nama,
                    rate_per_hour: rate,
                    description: desc
                });
            }
        }
    }
    
    // Update the stored data
    bopData[prosesId] = komponen;
    
    // Update the display table
    refreshBopTable(prosesId);
    updateSummaryTable(prosesId);
    
    // Close modal
    const modalElement = document.getElementById('editBopProsesModal');
    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();
    }
    
    // Show notification
    showNotification('BOP data updated successfully', 'success');
    
    // Here you can add AJAX call to save to backend
    console.log('Updated BOP data for process:', prosesId, komponen);
}

// Save BOP data to backend (placeholder function)
function saveBopData(prosesId) {
    // Implement AJAX call to save data
    console.log('Saving BOP data for process:', prosesId, bopData[prosesId]);
    
    // Example AJAX call:
    /*
    fetch(`/master-data/bop/save/${prosesId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            components: bopData[prosesId]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('BOP data saved successfully', 'success');
        } else {
            showNotification('Error saving BOP data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving BOP data', 'error');
    });
    */
}

// Show notification (helper function)
function showNotification(message, type) {
    // You can implement any notification system here
    console.log(`${type}: ${message}`);
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

// Handle form submission for add BOP
document.getElementById('bopProsesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
});
</script>
@endsection

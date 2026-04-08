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

    <!-- BOP Process Cards -->
    @foreach($prosesProduksis as $index => $proses)
        @php
            $bop = $proses->bopProses;
            $kapasitasPerJam = $proses->kapasitas_per_jam ?? 0;
            $hasBop = $bop !== null;
            
            // Get BOP data
            if ($hasBop) {
                $bopPerJam = $bop->total_bop_per_jam ?? 0;
                $budget = $bop->budget ?? 0;
                $aktual = $bop->aktual ?? 0;
                $btklPerJam = $bop->btkl_per_jam ?? 0;
                $btklPerPcs = $kapasitasPerJam > 0 ? $btklPerJam / $kapasitasPerJam : 0;
                
                // Get komponen BOP
                $komponenBop = !empty($bop->komponen_bop) ? json_decode($bop->komponen_bop, true) : [];
                $totalBopPerJam = is_array($komponenBop) ? array_sum(array_column($komponenBop, 'rate_per_hour')) : $bopPerJam;
                $bopPerPcs = $kapasitasPerJam > 0 ? $totalBopPerJam / $kapasitasPerJam : 0;
                $biayaPerProduk = $btklPerPcs + $bopPerPcs;
                $biayaPerJam = $btklPerJam + $totalBopPerJam;
            } else {
                $bopPerJam = 0;
                $budget = 0;
                $aktual = 0;
                $btklPerJam = 0;
                $btklPerPcs = 0;
                $bopPerPcs = 0;
                $biayaPerProduk = 0;
                $biayaPerJam = 0;
                $komponenBop = [];
                $totalBopPerJam = 0;
            }
            
            // Create 3-column layout
            $isThirdColumn = ($index + 1) % 3 == 0;
        @endphp
        
        @if($index % 3 == 0)
            <div class="row">
        @endif
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">{{ $proses->nama_proses }}</h6>
                    <small>{{ $proses->kode_proses }}</small>
                </div>
                <div class="card-body">
                    <!-- Process Info -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Kapasitas</strong>
                            <div class="text-primary">{{ $kapasitasPerJam }} pcs/jam</div>
                        </div>
                        <div class="col-6">
                            <strong>BTKL / jam</strong>
                            <div class="text-info">{{ number_format($btklPerJam, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>BTKL / pcs</strong>
                            <div class="text-success">{{ number_format($btklPerPcs, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-6">
                            <strong>Total BOP / produk</strong>
                            <div class="text-warning">{{ number_format($totalBopPerJam, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>BOP / produk</strong>
                            <div class="text-danger">{{ number_format($bopPerPcs, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-6">
                            <strong>Biaya / produk</strong>
                            <div class="text-dark fw-bold">{{ number_format($biayaPerProduk, 0, ',', '.') }} pcs</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Biaya / jam</strong>
                            <div class="text-primary fw-bold">{{ number_format($biayaPerJam, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    
                    <!-- BOP Components -->
                    @if(!empty($komponenBop))
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Komponen BOP</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Komponen</th>
                                            <th class="text-end">Rp / Jam</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($komponenBop as $komponen)
                                            <tr>
                                                <td>{{ $komponen['name'] ?? '' }}</td>
                                                <td class="text-end">{{ number_format($komponen['rate_per_hour'] ?? 0, 0, ',', '.') }}</td>
                                                <td>{{ $komponen['description'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-primary fw-bold">
                                            <td>Total BOP / produk</td>
                                            <td class="text-end">{{ number_format($totalBopPerJam, 0, ',', '.') }}</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="text-center">
                        @if($hasBop)
                            <div class="btn-group">
                                <button class="btn btn-outline-info btn-sm" onclick="viewBopDetail({{ $bop->id }})" title="Detail BOP">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <button class="btn btn-outline-warning btn-sm" onclick="editBopProses({{ $bop->id }})" title="Edit BOP">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @else
                            <button class="btn btn-primary btn-sm" onclick="addBopProses({{ $proses->id }})" title="Setup BOP">
                                <i class="fas fa-plus"></i> Setup BOP
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        @if($isThirdColumn)
            </div>
        @endif
    @endforeach
    
    @if(count($prosesProduksis) % 3 != 0)
            </div>
    @endif
    
    <!-- Summary Section -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">Biaya Per Produk</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($prosesProduksis as $proses)
                    @php
                        $bop = $proses->bopProses;
                        $kapasitasPerJam = $proses->kapasitas_per_jam ?? 0;
                        
                        if ($bop) {
                            $btklPerJam = $bop->btkl_per_jam ?? 0;
                            $btklPerPcs = $kapasitasPerJam > 0 ? $btklPerJam / $kapasitasPerJam : 0;
                            $komponenBop = !empty($bop->komponen_bop) ? json_decode($bop->komponen_bop, true) : [];
                            $totalBopPerJam = is_array($komponenBop) ? array_sum(array_column($komponenBop, 'rate_per_hour')) : ($bop->total_bop_per_jam ?? 0);
                            $bopPerPcs = $kapasitasPerJam > 0 ? $totalBopPerJam / $kapasitasPerJam : 0;
                            $biayaPerProduk = $btklPerPcs + $bopPerPcs;
                        } else {
                            $biayaPerProduk = 0;
                        }
                    @endphp
                    
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h6 class="text-primary">{{ $proses->nama_proses }}</h6>
                            <div class="h4 text-success fw-bold">RP{{ number_format($biayaPerProduk, 2, ',', '.') }}</div>
                        </div>
                    </div>
                @endforeach
                
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded bg-light">
                        <h6 class="text-dark">Total</h6>
                        @php
                            $totalBiayaPerProduk = 0;
                            foreach($prosesProduksis as $proses) {
                                $bop = $proses->bopProses;
                                $kapasitasPerJam = $proses->kapasitas_per_jam ?? 0;
                                
                                if ($bop) {
                                    $btklPerJam = $bop->btkl_per_jam ?? 0;
                                    $btklPerPcs = $kapasitasPerJam > 0 ? $btklPerJam / $kapasitasPerJam : 0;
                                    $komponenBop = !empty($bop->komponen_bop) ? json_decode($bop->komponen_bop, true) : [];
                                    $totalBopPerJam = is_array($komponenBop) ? array_sum(array_column($komponenBop, 'rate_per_hour')) : ($bop->total_bop_per_jam ?? 0);
                                    $bopPerPcs = $kapasitasPerJam > 0 ? $totalBopPerJam / $kapasitasPerJam : 0;
                                    $totalBiayaPerProduk += $btklPerPcs + $bopPerPcs;
                                }
                            }
                        @endphp
                        <div class="h4 text-primary fw-bold">RP{{ number_format($totalBiayaPerProduk, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('master-data.bop.modals')

<script>
function addBopProses(prosesId) {
    // Set proses ID and show modal
    document.querySelector('select[name="proses_produksi_id"]').value = prosesId;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addBopProsesModal'));
    modal.show();
}

function editBopProses(id) {
    // Load BOP Proses data dan tampilkan di modal edit
    fetch(`/master-data/bop/get-proses/${id}`)
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(html => {
                    throw new Error('Server returned HTML instead of JSON. Possible server error.');
                });
            }
        })
        .then(data => {
            if (data.success) {
                const bop = data.bop;
                
                // Isi form edit dengan data existing
                document.getElementById('editBopProsesId').value = bop.id;
                document.getElementById('editNamaProses').value = bop.proses_produksi.nama_proses;
                document.getElementById('editBudgetProses').value = bop.budget;
                document.getElementById('editTotalBopPerJam').value = bop.total_bop_per_jam;
                document.getElementById('editAktualProses').value = bop.aktual || 0;
                document.getElementById('editKeteranganProses').value = bop.keterangan || '';
                
                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('editBopProsesModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Edit BOP Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function updateBopProses() {
    const form = document.getElementById('editBopProsesForm');
    const formData = new FormData(form);
    const id = formData.get('id');
    
    fetch(`/master-data/bop/update-proses-simple/${id}`, {
        method: 'PUT',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, it's probably an HTML error page
            return response.text().then(html => {
                throw new Error('Server returned HTML instead of JSON. Possible server error.');
            });
        }
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Update BOP Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
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

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
    <div class="row">
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
            @endphp
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">{{ $proses->nama_proses }}</h6>
                    </div>
                    <div class="card-body">
                        <!-- Process Info Table -->
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td><strong>Proses</strong></td>
                                    <td><strong>Kapasitas</strong></td>
                                    <td><strong>BTKL / jam</strong></td>
                                    <td><strong>BTKL / pcs</strong></td>
                                </tr>
                                <tr>
                                    <td>{{ $proses->nama_proses }}</td>
                                    <td>{{ $kapasitasPerJam }} pcs/jam</td>
                                    <td>{{ number_format($btklPerJam, 0, ',', '.') }}</td>
                                    <td>{{ number_format($btklPerPcs, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- BOP Components Table -->
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td><strong>Komponen</strong></td>
                                    <td><strong>Rp / Jam</strong></td>
                                    <td><strong>Keterangan</strong></td>
                                    <td><strong>Komponen</strong></td>
                                    <td><strong>Rp / Jam</strong></td>
                                    <td><strong>Keterangan</strong></td>
                                </tr>
                                
                                @if(!empty($komponenBop))
                                    @php
                                        $komponenCount = count($komponenBop);
                                        $halfCount = ceil($komponenCount / 2);
                                        $leftKomponen = array_slice($komponenBop, 0, $halfCount);
                                        $rightKomponen = array_slice($komponenBop, $halfCount);
                                    @endphp
                                    
                                    @for($i = 0; $i < max($halfCount, count($rightKomponen)); $i++)
                                        <tr>
                                            <td>{{ $leftKomponen[$i]['name'] ?? '' }}</td>
                                            <td>{{ number_format($leftKomponen[$i]['rate_per_hour'] ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $leftKomponen[$i]['description'] ?? '' }}</td>
                                            <td>{{ $rightKomponen[$i]['name'] ?? '' }}</td>
                                            <td>{{ number_format($rightKomponen[$i]['rate_per_hour'] ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $rightKomponen[$i]['description'] ?? '' }}</td>
                                        </tr>
                                    @endfor
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada komponen BOP</td>
                                    </tr>
                                @endif
                                
                                <tr class="table-primary fw-bold">
                                    <td>Total BOP /jam</td>
                                    <td>{{ number_format($totalBopPerJam, 0, ',', '.') }}</td>
                                    <td></td>
                                    <td>Total BOP /jam</td>
                                    <td>{{ number_format($totalBopPerJam, 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Summary Table -->
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td><strong>BOP / pcs</strong></td>
                                    <td><strong>Biaya / produk</strong></td>
                                    <td><strong>Biaya / jam</strong></td>
                                    <td><strong>BOP / pcs</strong></td>
                                    <td><strong>Biaya / produk</strong></td>
                                    <td><strong>Biaya / jam</strong></td>
                                </tr>
                                <tr>
                                    <td>{{ number_format($bopPerPcs, 0, ',', '.') }}</td>
                                    <td>{{ number_format($biayaPerProduk, 0, ',', '.') }} pcs</td>
                                    <td>{{ number_format($biayaPerJam, 0, ',', '.') }}</td>
                                    <td>{{ number_format($bopPerPcs, 0, ',', '.') }}</td>
                                    <td>{{ number_format($biayaPerProduk, 0, ',', '.') }} pcs</td>
                                    <td>{{ number_format($biayaPerJam, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        
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
        @endforeach
    </div>
    
    <!-- Summary Section -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">Biaya Per Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr class="table-light">
                        <td><strong>Biaya Per produk</strong></td>
                        <td><strong>Penggorengan</strong></td>
                        <td><strong>Perbumbuan</strong></td>
                        <td><strong>Pengemasan</strong></td>
                        <td><strong>Total</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Biaya Per produk</strong></td>
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
                            <td>RP{{ number_format($biayaPerProduk, 2, ',', '.') }}</td>
                        @endforeach
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
                        <td><strong>RP{{ number_format($totalBiayaPerProduk, 2, ',', '.') }}</strong></td>
                    </tr>
                </table>
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

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>BOP (Biaya Overhead Pabrik)
        </h2>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="bopTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="proses-tab" data-bs-toggle="tab" data-bs-target="#proses" type="button" role="tab">
                <i class="fas fa-cogs me-2"></i>BOP per Proses
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="lainnya-tab" data-bs-toggle="tab" data-bs-target="#lainnya" type="button" role="tab">
                <i class="fas fa-list me-2"></i>BOP Lainnya
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="bopTabContent">
        
        <!-- Tab 1: BOP per Proses -->
        <div class="tab-pane fade show active" id="proses" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>BOP per Proses Produksi
                    </h5>
                    <small class="text-muted">Budget dan aktual BOP berdasarkan proses produksi</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Proses</th>
                                    <th class="text-center">Kapasitas / Jam</th>
                                    <th class="text-end">Biaya / Jam (Rp)</th>
                                    <th class="text-end">Biaya / Produk (Rp/pcs)</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prosesProduksis as $proses)
                                    @php
                                        $bop = $proses->bopProses;
                                        $btklPerJam = $proses->tarif_btkl ?? 0;
                                        $kapasitasPerJam = $proses->kapasitas_per_jam ?? 0;
                                        $hasBop = $bop !== null;
                                        
                                        // Get BOP per jam from new structure (total_bop_per_jam field)
                                        if ($hasBop) {
                                            $bopPerJam = $bop->total_bop_per_jam ?? 0;
                                            
                                            // Fallback: calculate from JSON if total_bop_per_jam is null
                                            if ($bopPerJam == 0 && !empty($bop->komponen_bop)) {
                                                $komponenBop = is_array($bop->komponen_bop) ? $bop->komponen_bop : json_decode($bop->komponen_bop, true);
                                                if (is_array($komponenBop)) {
                                                    $bopPerJam = array_sum(array_column($komponenBop, 'rate_per_hour'));
                                                }
                                            }
                                        } else {
                                            $bopPerJam = 0;
                                        }
                                        
                                        // Calculate process costing values
                                        $biayaPerJam = $btklPerJam + $bopPerJam;
                                        $biayaPerProduk = $kapasitasPerJam > 0 ? $biayaPerJam / $kapasitasPerJam : 0;
                                        
                                        // Define status variables
                                        if ($hasBop) {
                                            $statusClass = 'success';
                                            $statusText = 'Sudah Setup';
                                        } else {
                                            $statusClass = 'secondary';
                                            $statusText = 'Belum Setup';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $proses->nama_proses }}</div>
                                            <small class="text-muted">{{ $proses->kode_proses }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $kapasitasPerJam }} unit/jam</span>
                                        </td>
                                        <td class="text-end">
                                            @if($hasBop)
                                                <span class="fw-semibold text-primary">Rp {{ number_format($biayaPerJam, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($hasBop)
                                                <span class="fw-semibold text-success">Rp {{ number_format($biayaPerProduk, 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($hasBop)
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('master-data.bop.show-proses', $bop->id) }}" class="btn btn-outline-info" title="Detail BOP">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('master-data.bop.edit-proses', $bop->id) }}" class="btn btn-outline-primary" title="Edit BOP">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ route('master-data.bop.create-proses', ['proses_id' => $proses->id]) }}" class="btn btn-outline-success btn-sm" title="Setup BOP">
                                                    <i class="fas fa-plus"></i> Setup
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada proses BTKL</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: BOP Lainnya -->
        <div class="tab-pane fade" id="lainnya" role="tabpanel">
            <div class="row mb-4">
                <div class="col-12">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBopLainnyaModal">
                        <i class="fas fa-plus me-2"></i>Tambah BOP Lainnya
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>BOP Lainnya (Akun Beban)
                    </h5>
                    <small class="text-muted">Budget dan aktual BOP dari akun beban (kode 5)</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama BOP</th>
                                    <th class="text-end">Budget BOP</th>
                                    <th class="text-center">Kuantitas/Jam</th>
                                    <th class="text-end">Biaya/Jam</th>
                                    <th class="text-end">Aktual</th>
                                    <th class="text-end">Selisih</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bopLainnya as $bop)
                                    @php
                                        $budget = $bop->budget ?? 0;
                                        $aktual = $bop->aktual ?? 0;
                                        $selisih = $budget - $aktual;
                                        $statusClass = $selisih >= 0 ? 'success' : 'danger';
                                        $statusText = $selisih >= 0 ? 'Under Budget' : 'Over Budget';
                                        $biayaPerJam = $bop->kuantitas_per_jam > 0 ? $budget / $bop->kuantitas_per_jam : 0;
                                        $hasData = $bop->id !== null && $budget > 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $bop->nama_akun }}</div>
                                            <small class="text-muted">{{ $bop->kode_akun }}</small>
                                        </td>
                                        <td class="text-end">
                                            @if($hasData)
                                                <span class="fw-semibold">Rp {{ number_format($budget, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($hasData)
                                                <span class="badge bg-info">{{ $bop->kuantitas_per_jam ?? 0 }} unit/jam</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($hasData)
                                                <span class="text-warning">Rp {{ number_format($biayaPerJam, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($hasData)
                                                <span class="fw-semibold">Rp {{ number_format($aktual, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($hasData)
                                                <span class="fw-semibold text-{{ $statusClass }}">
                                                    Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                                                    @if($selisih < 0) (Over) @endif
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($hasData)
                                                <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                            @else
                                                <span class="badge bg-secondary">Belum Setup</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($hasData)
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="editBopLainnya({{ $bop->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteBopLainnya({{ $bop->id }})" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button class="btn btn-outline-success btn-sm" onclick="setupBopLainnya('{{ $bop->kode_akun }}', '{{ $bop->nama_akun }}')" title="Setup BOP">
                                                    <i class="fas fa-plus"></i> Setup
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-list fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada akun beban (kode 5) di COA</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Tambah BOP Lainnya -->
<div class="modal fade" id="addBopLainnyaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah BOP Lainnya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bopLainnyaForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Akun Beban *</label>
                                <select class="form-select" name="kode_akun" required>
                                    <option value="">Pilih Akun Beban</option>
                                    @foreach($akunBeban as $akun)
                                        <option value="{{ $akun->kode_akun }}">{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Budget BOP *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="budget" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kuantitas per Jam *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="kuantitas_per_jam" min="1" required>
                                    <span class="input-group-text">unit/jam</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Periode *</label>
                                <input type="month" class="form-control" name="periode" value="{{ date('Y-m') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveBopLainnya()">
                    <i class="fas fa-save me-2"></i>Simpan BOP
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit BOP Lainnya -->
<div class="modal fade" id="editBopLainnyaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit BOP Lainnya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBopLainnyaForm">
                    <input type="hidden" name="id" id="editBopId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Akun Beban *</label>
                                <select class="form-select" name="kode_akun" id="editKodeAkun" required>
                                    <option value="">Pilih Akun Beban</option>
                                    @foreach($akunBeban as $akun)
                                        <option value="{{ $akun->kode_akun }}">{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Budget BOP *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="budget" id="editBudget" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kuantitas per Jam *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="kuantitas_per_jam" id="editKuantitas" min="1" required>
                                    <span class="input-group-text">unit/jam</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Periode *</label>
                                <input type="month" class="form-control" name="periode" id="editPeriode" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="editKeterangan" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateBopLainnya()">
                    <i class="fas fa-save me-2"></i>Update BOP
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function saveBopLainnya() {
    const form = document.getElementById('bopLainnyaForm');
    const formData = new FormData(form);
    
    fetch('/master-data/bop/store-lainnya', {
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
}

function setupBopLainnya(kodeAkun, namaAkun) {
    // Pre-fill modal with account data
    document.querySelector('select[name="kode_akun"]').value = kodeAkun;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addBopLainnyaModal'));
    modal.show();
}

function editBopLainnya(id) {
    // Load BOP Lainnya data dan tampilkan di modal edit
    fetch(`/master-data/bop/get-lainnya/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const bop = data.bop;
                
                // Isi form edit dengan data existing
                document.getElementById('editBopId').value = bop.id;
                document.getElementById('editKodeAkun').value = bop.kode_akun;
                document.getElementById('editBudget').value = bop.budget;
                document.getElementById('editKuantitas').value = bop.kuantitas_per_jam;
                document.getElementById('editPeriode').value = bop.periode;
                document.getElementById('editKeterangan').value = bop.keterangan || '';
                
                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('editBopLainnyaModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function updateBopLainnya() {
    const form = document.getElementById('editBopLainnyaForm');
    const formData = new FormData(form);
    const id = formData.get('id');
    
    fetch(`/master-data/bop/update-lainnya/${id}`, {
        method: 'PUT',
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
}

function deleteBopLainnya(id) {
    if (confirm('Yakin ingin menghapus BOP ini?')) {
        fetch('/master-data/bop/destroy-lainnya/' + id, {
            method: 'DELETE',
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
    }
}
</script>
@endsection
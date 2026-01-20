@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>BOP (Biaya Overhead Pabrik) Terpadu
        </h2>
        <div>
            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#budgetModal">
                <i class="fas fa-calculator me-2"></i>Set Budget BOP
            </button>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#aktualModal">
                <i class="fas fa-edit me-2"></i>Input Aktual
            </button>
            <a href="{{ route('master-data.bop-terpadu.create-proses') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah BOP Proses
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="bopTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="proses-tab" data-bs-toggle="tab" data-bs-target="#proses" type="button" role="tab">
                <i class="fas fa-cogs me-2"></i>BOP per Proses
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="budget-tab" data-bs-toggle="tab" data-bs-target="#budget" type="button" role="tab">
                <i class="fas fa-calculator me-2"></i>Budget vs Aktual
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="analisis-tab" data-bs-toggle="tab" data-bs-target="#analisis" type="button" role="tab">
                <i class="fas fa-chart-line me-2"></i>Analisis Variance
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
                    <small class="text-muted">Standard cost BOP berdasarkan jam mesin per proses</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Proses</th>
                                    <th>Nama Proses</th>
                                    <th class="text-end">BOP/Jam</th>
                                    <th class="text-center">Kapasitas/Jam</th>
                                    <th class="text-end">BOP/Unit</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prosesProduksis as $proses)
                                    <tr>
                                        <td><code>{{ $proses->kode_proses }}</code></td>
                                        <td>
                                            <div class="fw-semibold">{{ $proses->nama_proses }}</div>
                                            @if($proses->deskripsi)
                                                <small class="text-muted">{{ Str::limit($proses->deskripsi, 40) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($proses->bopProses)
                                                <span class="fw-semibold text-warning">{{ $proses->bopProses->total_bop_per_jam_formatted }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $proses->kapasitas_per_jam ?? 0 }} unit/jam</span>
                                        </td>
                                        <td class="text-end">
                                            @if($proses->bopProses && $proses->bopProses->bop_per_unit > 0)
                                                <span class="fw-semibold text-success">{{ $proses->bopProses->bop_per_unit_formatted }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($proses->kapasitas_per_jam <= 0)
                                                <span class="badge bg-danger">No Capacity</span>
                                            @elseif($proses->bopProses)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">No BOP</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                @if($proses->bopProses)
                                                    <a href="{{ route('master-data.bop-terpadu.show-proses', $proses->bopProses->id) }}" class="btn btn-outline-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('master-data.bop-terpadu.edit-proses', $proses->bopProses->id) }}" class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    @if($proses->kapasitas_per_jam > 0)
                                                        <a href="{{ route('master-data.bop-terpadu.create-proses', ['proses_id' => $proses->id]) }}" class="btn btn-outline-success btn-sm" title="Buat BOP">
                                                            <i class="fas fa-plus"></i> BOP
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada proses produksi</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Budget vs Aktual -->
        <div class="tab-pane fade" id="budget" role="tabpanel">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Total Budget BOP</h6>
                                    <h4>Rp {{ number_format($totalBudget ?? 0, 0, ',', '.') }}</h4>
                                </div>
                                <i class="fas fa-calculator fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Total Aktual BOP</h6>
                                    <h4>Rp {{ number_format($totalAktual ?? 0, 0, ',', '.') }}</h4>
                                </div>
                                <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Variance</h6>
                                    @php $variance = ($totalBudget ?? 0) - ($totalAktual ?? 0); @endphp
                                    <h4>Rp {{ number_format($variance, 0, ',', '.') }}</h4>
                                </div>
                                <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Variance %</h6>
                                    @php 
                                        $variancePercent = ($totalBudget ?? 0) > 0 ? (($variance / $totalBudget) * 100) : 0;
                                    @endphp
                                    <h4>{{ number_format($variancePercent, 1) }}%</h4>
                                </div>
                                <i class="fas fa-percentage fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Detail Budget vs Aktual BOP
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Akun</th>
                                    <th>Nama Akun BOP</th>
                                    <th class="text-end">Budget</th>
                                    <th class="text-end">Aktual</th>
                                    <th class="text-end">Variance</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bopBudgets as $bop)
                                    @php
                                        $variance = $bop->budget - ($bop->aktual ?? 0);
                                        $variancePercent = $bop->budget > 0 ? (($variance / $bop->budget) * 100) : 0;
                                        $statusClass = $variance >= 0 ? 'success' : 'danger';
                                        $statusText = $variance >= 0 ? 'Under Budget' : 'Over Budget';
                                    @endphp
                                    <tr>
                                        <td><code>{{ $bop->kode_akun }}</code></td>
                                        <td>{{ $bop->nama_akun }}</td>
                                        <td class="text-end">Rp {{ number_format($bop->budget, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($bop->aktual ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <span class="text-{{ $statusClass }}">
                                                Rp {{ number_format($variance, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-{{ $statusClass }}">
                                                {{ number_format($variancePercent, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada budget BOP yang ditetapkan</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Analisis Variance -->
        <div class="tab-pane fade" id="analisis" role="tabpanel">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Grafik Budget vs Aktual
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="bopChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Alert Variance
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($bopBudgets as $bop)
                                @php
                                    $variance = $bop->budget - ($bop->aktual ?? 0);
                                    $variancePercent = $bop->budget > 0 ? abs(($variance / $bop->budget) * 100) : 0;
                                @endphp
                                @if($variancePercent > 10)
                                    <div class="alert alert-{{ $variance >= 0 ? 'warning' : 'danger' }} alert-sm">
                                        <strong>{{ $bop->nama_akun }}</strong><br>
                                        Variance: {{ number_format($variancePercent, 1) }}%
                                        @if($variance < 0)
                                            <br><small>Over budget Rp {{ number_format(abs($variance), 0, ',', '.') }}</small>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Set Budget -->
<div class="modal fade" id="budgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Budget BOP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="budgetForm">
                    <div class="mb-3">
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" name="periode" value="{{ date('Y-m') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Akun BOP</label>
                        <select class="form-select" name="kode_akun" required>
                            <option value="">Pilih Akun BOP</option>
                            @foreach($akunBop as $akun)
                                <option value="{{ $akun->kode_akun }}">{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="budget" min="0" step="1000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveBudget()">Simpan Budget</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Aktual -->
<div class="modal fade" id="aktualModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Aktual BOP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="aktualForm">
                    <div class="mb-3">
                        <label class="form-label">Akun BOP</label>
                        <select class="form-select" name="bop_id" required>
                            <option value="">Pilih Akun BOP</option>
                            @foreach($bopBudgets as $bop)
                                <option value="{{ $bop->id }}">{{ $bop->nama_akun }} (Budget: Rp {{ number_format($bop->budget, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Aktual Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="aktual" min="0" step="1000" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="saveAktual()">Simpan Aktual</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Chart
    const ctx = document.getElementById('bopChart').getContext('2d');
    const bopData = @json($bopBudgets);
    
    const labels = bopData.map(item => item.nama_akun);
    const budgetData = bopData.map(item => item.budget);
    const aktualData = bopData.map(item => item.aktual || 0);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Budget',
                data: budgetData,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Aktual',
                data: aktualData,
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});

function saveBudget() {
    const form = document.getElementById('budgetForm');
    const formData = new FormData(form);
    
    fetch('{{ route("master-data.bop-terpadu.store-budget") }}', {
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
    });
}

function saveAktual() {
    const form = document.getElementById('aktualForm');
    const formData = new FormData(form);
    
    fetch('{{ route("master-data.bop-terpadu.store-aktual") }}', {
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
    });
}

function createBopProses(prosesId) {
    window.location.href = '{{ route("master-data.bop-terpadu.create-proses") }}?proses_id=' + prosesId;
}

function editBopProses(bopId) {
    window.location.href = '{{ route("master-data.bop-terpadu.edit-proses") }}/' + bopId;
}

function showBopDetail(bopId) {
    // Implement detail modal or redirect
    window.location.href = '{{ route("master-data.bop-terpadu.show-proses") }}/' + bopId;
}
</script>
@endsection
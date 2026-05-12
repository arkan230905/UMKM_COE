@extends('layouts.app')

@section('title', 'Validasi Harga Rata-Rata')

@push('styles')
<style>
.harga-card {
    transition: all 0.3s ease;
}

.harga-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.status-consistent {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.status-inconsistent {
    background: linear-gradient(135deg, #dc3545 0%, #dc2626 100%);
    color: white;
}

.difference-badge {
    font-size: 0.8rem;
    font-weight: bold;
}

.history-item {
    border-left: 3px solid #007bff;
    padding-left: 1rem;
    margin-bottom: 1rem;
}

.history-item:hover {
    background-color: #f8f9fa;
}

.bahan-baku-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.bahan-pendukung-section {
    background: #e3f2fd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.section-title {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 15px;
    color: #495057;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calculator me-2"></i>Validasi Harga Rata-Rata
        </h2>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="recalculateAll()">
                <i class="fas fa-sync-alt me-1"></i>Hitung Ulang Semua
            </button>
            <button type="button" class="btn btn-info" onclick="validateAll()">
                <i class="fas fa-check-circle me-1"></i>Validasi Semua
            </button>
            <button type="button" class="btn btn-secondary" onclick="refreshData()">
                <i class="fas fa-redo me-1"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card harga-card">
                <div class="card-body text-center">
                    <h5 class="text-primary">{{ $bahanBakus->count() }}</h5>
                    <small class="text-muted">Total Bahan Baku</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card harga-card">
                <div class="card-body text-center">
                    <h5 class="text-success" id="consistentCount">0</h5>
                    <small class="text-muted">Konsisten</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card harga-card">
                <div class="card-body text-center">
                    <h5 class="text-danger" id="inconsistentCount">0</h5>
                    <small class="text-muted">Tidak Konsisten</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card harga-card">
                <div class="card-body text-center">
                    <h5 class="text-warning" id="avgDifference">0</h5>
                    <small class="text-muted">Rata-rata Selisih</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bahan Baku Section -->
    <div class="bahan-baku-section">
        <h3 class="section-title">
            <i class="fas fa-cube me-2"></i>Bahan Baku
        </h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Harga Saat Ini</th>
                        <th>Harga Dihitung Ulang</th>
                        <th>Selisih</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="bahanBakuTable">
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bahan Pendukung Section -->
    <div class="bahan-pendukung-section">
        <h3 class="section-title">
            <i class="fas fa-tools me-2"></i>Bahan Pendukung
        </h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Harga Saat Ini</th>
                        <th>Harga Dihitung Ulang</th>
                        <th>Selisih</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="bahanPendukungTable">
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Purchase History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Riwayat Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="historyContent">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Memuat data...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let allResults = [];

// Load initial data
document.addEventListener('DOMContentLoaded', function() {
    refreshData();
});

function refreshData() {
    fetch('/master-data/harga/validate-all')
        .then(response => response.json())
        .then(data => {
            allResults = data.results || [];
            updateSummaryCards(data.summary || {});
            updateResultsTable();
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Gagal memuat data');
        });
}

function recalculateAll() {
    showAlert('info', 'Sedang menghitung ulang harga rata-rata...');
    
    fetch('/master-data/harga/recalculate-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Perhitungan ulang selesai! ${data.summary.success_count} berhasil, ${data.summary.error_count} gagal`);
            refreshData();
        } else {
            showAlert('danger', data.message || 'Gagal menghitung ulang');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan');
    });
}

function validateAll() {
    showAlert('info', 'Sedang memvalidasi harga rata-rata...');
    
    fetch('/master-data/harga/validate-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Validasi selesai! ${data.summary.consistent_count} konsisten, ${data.summary.inconsistent_count} tidak konsisten`);
            updateResultsTable();
            updateSummaryCards(data.summary);
        } else {
            showAlert('danger', data.message || 'Gagal memvalidasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan');
    });
}

function updateSummaryCards(summary) {
    document.getElementById('consistentCount').textContent = summary.bahan_baku_consistent || 0;
    document.getElementById('inconsistentCount').textContent = summary.bahan_pendukung_consistent || 0;
    
    const totalConsistent = summary.bahan_baku_consistent + summary.bahan_pendukung_consistent;
    const totalInconsistent = summary.inconsistent_count;
    
    document.getElementById('consistentCount').textContent = summary.bahan_baku_consistent || 0;
    document.getElementById('inconsistentCount').textContent = summary.bahan_pendukung_consistent || 0;
    document.getElementById('avgDifference').textContent = summary.avg_difference || 0;
}

function updateResultsTable() {
    // Update Bahan Baku table
    const bahanBakuTable = document.getElementById('bahanBakuTable');
    bahanBakuTable.innerHTML = '';
    
    // Filter bahan baku results
    const bahanBakuResults = allResults.filter(r => r.tipe === 'bahan_baku');
    
    bahanBakuResults.forEach(result => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${result.id}</td>
            <td>${result.nama}</td>
            <td>Rp ${formatNumber(result.harga_saat_ini)}</td>
            <td>Rp ${formatNumber(result.harga_dihitung_ulang)}</td>
            <td>Rp ${formatNumber(result.selisih)}</td>
            <td>
                <span class="badge ${result.konsisten ? 'bg-success' : 'bg-danger'}">
                    ${result.pesan}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-info" onclick="showHistory(${result.id}, 'bahan_baku')">
                    <i class="fas fa-history"></i>
                </button>
                ${!result.konsisten ? `
                <button class="btn btn-sm btn-warning" onclick="recalculateItem(${result.id}, 'bahan_baku')">
                    <i class="fas fa-sync"></i>
                </button>
                ` : ''}
            </td>
        `;
        bahanBakuTable.appendChild(row);
    });
    
    // Update Bahan Pendukung table
    const bahanPendukungTable = document.getElementById('bahanPendukungTable');
    bahanPendukungTable.innerHTML = '';
    
    // Filter bahan pendukung results
    const bahanPendukungResults = allResults.filter(r => r.tipe === 'bahan_pendukung');
    
    bahanPendukungResults.forEach(result => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${result.id}</td>
            <td>${result.nama}</td>
            <td>Rp ${formatNumber(result.harga_saat_ini)}</td>
            <td>Rp ${formatNumber(result.harga_dihitung_ulang)}</td>
            <td>Rp ${formatNumber(result.selisih)}</td>
            <td>
                <span class="badge ${result.konsisten ? 'bg-success' : 'bg-danger'}">
                    ${result.pesan}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-info" onclick="showHistory(${result.id}, 'bahan_pendukung')">
                    <i class="fas fa-history"></i>
                </button>
                ${!result.konsisten ? `
                <button class="btn btn-sm btn-warning" onclick="recalculateItem(${result.id}, 'bahan_pendukung')">
                    <i class="fas fa-sync"></i>
                </button>
                ` : ''}
            </td>
        `;
        bahanPendukungTable.appendChild(row);
    });
}

function showHistory(id, type) {
    document.getElementById('historyContent').innerHTML = `
        <div class="text-center text-muted">
            <i class="fas fa-spinner fa-spin"></i> Memuat riwayat pembelian...
        </div>
    `;
    
    fetch(`/master-data/harga/purchase-history-${type}/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr>';
                html += '<th>Tanggal</th><th>No. Pembelian</th><th>Jumlah</th><th>Satuan</th><th>Harga/Satuan</th><th>Subtotal</th><th>Harga/kg</th></tr></thead><tbody>';
                
                data.data.history.forEach(item => {
                    html += '<tr class="history-item">';
                    html += `<td>${item['tanggal']}</td>`;
                    html += `<td>${item['nomor_pembelian'] || '-'}</td>`;
                    html += `<td>${formatNumber(item['jumlah'])}</td>`;
                    html += `<td>${item['satuan']}</td>`;
                    html += `<td>Rp ${formatNumber(item['harga_satuan'])}</td>`;
                    html += `<td>Rp ${formatNumber(item['subtotal'])}</td>`;
                    html += `<td>Rp ${formatNumber(item['harga_per_kg'])}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                document.getElementById('historyContent').innerHTML = html;
                
                const modal = new bootstrap.Modal(document.getElementById('historyModal'));
                modal.show();
            } else {
                document.getElementById('historyContent').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('historyContent').innerHTML = '<div class="alert alert-danger">Gagal memuat riwayat pembelian</div>';
        });
}

function recalculateItem(id, type) {
    showAlert('info', 'Menghitung ulang harga untuk item ini...');
    
    // Implementasi untuk recalculate single item
    refreshData();
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

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
</script>
@endsection

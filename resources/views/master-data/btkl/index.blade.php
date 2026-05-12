@extends('layouts.app')

@section('title', 'Daftar BTKL')

@push('styles')
<style>
.btkl-page { 
    background: #FFFFFF; 
    min-height: 100vh; 
    padding: 24px; 
    font-family: 'Inter', 'Segoe UI', sans-serif; 
}

.btkl-container { 
    max-width: 1400px; 
    margin: 0 auto; 
}

.btkl-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 24px; 
    padding: 20px 24px; 
    background: white; 
    border-radius: 16px; 
    box-shadow: 0 4px 20px rgba(139, 69, 19, 0.08); 
}

.btkl-header-left h2 { 
    font-size: 24px; 
    font-weight: 700; 
    color: #8B4513; 
    margin: 0; 
    display: flex; 
    align-items: center; 
    gap: 12px; 
}

.btkl-header-left p { 
    font-size: 14px; 
    color: #A0826D; 
    margin: 4px 0 0 0; 
}

.btkl-card { 
    background: white; 
    border-radius: 16px; 
    box-shadow: 0 4px 20px rgba(139, 69, 19, 0.08); 
    overflow: hidden; 
    border: 1px solid rgba(255, 255, 255, 0.8);
}

.btkl-table { 
    background: white; 
    border-radius: 0; 
}

.btkl-table thead th { 
    background: linear-gradient(135deg, #F5E6D3 0%, #E8D5C4 100%); 
    color: #8B4513; 
    font-weight: 600; 
    font-size: 12px; 
    text-transform: uppercase; 
    letter-spacing: 0.5px; 
    padding: 16px 12px; 
    border: none; 
    white-space: nowrap;
}

.btkl-table tbody tr { 
    transition: all 0.2s ease; 
    border-bottom: 1px solid #F5E6D3;
}

.btkl-table tbody tr:hover { 
    background: linear-gradient(90deg, #FFF8F0 0%, #F5E6D3 100%); 
    transform: translateY(-1px); 
    box-shadow: 0 2px 8px rgba(139, 69, 19, 0.06);
}

.btkl-table tbody td { 
    padding: 16px 12px; 
    vertical-align: middle; 
    border: none; 
    font-size: 14px;
    color: #5D4037;
}

.badge-custom { 
    padding: 6px 12px; 
    border-radius: 20px; 
    font-size: 11px; 
    font-weight: 600; 
    display: inline-flex; 
    align-items: center; 
    gap: 6px;
}

.badge-kode { 
    background: linear-gradient(135deg, #F5E6D3 0%, #E8D5C4 100%); 
    color: #8B4513; 
}

.badge-satuan { 
    background: linear-gradient(135deg, #FAF0E6 0%, #F5E6D3 100%); 
    color: #A0826D; 
}

.icon-wrapper { 
    display: flex; 
    align-items: center; 
    gap: 8px; 
}

.icon-custom { 
    width: 32px; 
    height: 32px; 
    border-radius: 8px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 14px;
}

.icon-gear { 
    background: linear-gradient(135deg, #F5E6D3 0%, #E8D5C4 100%); 
    color: #8B4513; 
}

.icon-person { 
    background: linear-gradient(135deg, #FFF8F0 0%, #F5E6D3 100%); 
    color: #8B4513; 
}

.icon-people { 
    background: linear-gradient(135deg, #FAF0E6 0%, #F5E6D3 100%); 
    color: #A0826D; 
}

.icon-cash { 
    background: linear-gradient(135deg, #F5E6D3 0%, #E8D5C4 100%); 
    color: #8B4513; 
}

.icon-warning { 
    background: linear-gradient(135deg, #FFF8F0 0%, #F5E6D3 100%); 
    color: #8B4513; 
}

.btn-elegant { 
    border-radius: 8px; 
    font-weight: 500; 
    font-size: 13px; 
    padding: 8px 16px; 
    transition: all 0.2s ease; 
    border: none;
}

.btn-primary-elegant { 
    background: linear-gradient(135deg, #8B4513 0%, #A0826D 100%); 
    color: white; 
    box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
}

.btn-primary-elegant:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 6px 20px rgba(139, 69, 19, 0.4);
}

.btn-warning-elegant { 
    background: linear-gradient(135deg, #F5E6D3 0%, #E8D5C4 100%); 
    color: #8B4513; 
}

.btn-danger-elegant { 
    background: linear-gradient(135deg, #D2691E 0%, #8B4513 100%); 
    color: white; 
}

.modal-header-elegant { 
    background: linear-gradient(135deg, #8B4513 0%, #A0826D 100%); 
    color: white; 
    border-radius: 16px 16px 0 0;
}

.text-primary-custom { color: #8B4513; }
.text-success-custom { color: #A0826D; }
.text-warning-custom { color: #D2691E; }
.text-danger-custom { color: #8B4513; }
</style>
@endpush

@section('content')
<div class="btkl-page">
    <div class="btkl-container">
        <div class="btkl-header">
            <div class="btkl-header-left">
                <h2>
                    <div class="icon-custom icon-gear">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    Daftar Proses Produksi (BTKL)
                </h2>
                <p>Kelola data proses produksi dan tenaga kerja langsung</p>
            </div>
            <a href="{{ route('master-data.btkl.create') }}" class="btn btn-elegant btn-primary-elegant">
                <i class="bi bi-plus-lg me-2"></i> Tambah Proses
            </a>
        </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0" style="z-index: 9999; margin: 20px; min-width: 300px;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0" style="z-index: 9999; margin: 20px; min-width: 300px;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4B382A 0%, #6B4E3A 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div style="background: rgba(255, 255, 255, 0.2); color: white; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-white-50">Total Proses</h6>
                            <h3 class="mb-0">{{ $btkls->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4B382A 0%, #6B4E3A 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div style="background: rgba(255, 255, 255, 0.2); color: white; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-white-50">Total Tarif</h6>
                            <h3 class="mb-0">Rp {{ number_format($btkls->sum('tarif_btkl'), 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4B382A 0%, #6B4E3A 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div style="background: rgba(255, 255, 255, 0.2); color: white; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-white-50">Rata-rata Tarif/Jam</h6>
                            <h3 class="mb-0">Rp {{ number_format($btkls->avg('tarif_btkl'), 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4B382A 0%, #6B4E3A 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div style="background: rgba(255, 255, 255, 0.2); color: white; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-white-50">Rata-rata Biaya/Unit</h6>
                            <h3 class="mb-0">Rp {{ number_format($btkls->avg('tarif_btkl'), 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="btkl-card">
        <div class="table-responsive">
            <table class="table btkl-table align-middle mb-0">
                <thead class="btkl-table-thead">
                    <tr>
                        <th style="width: 20%">Nama Proses</th>
                        <th style="width: 20%">Jabatan BTKL</th>
                        <th style="width: 15%">Jumlah Pegawai</th>
                        <th style="width: 20%">Biaya Per Produk</th>
                        <th style="width: 20%">Deskripsi</th>
                        <th style="width: 5%">Aksi</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($btkls as $btkl)
                        <tr>
                            <td>
                                <div class="icon-wrapper">
                                    <div class="icon-custom icon-gear">
                                        <i class="bi bi-gear-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $btkl->nama_btkl ?? '-' }}</div>
                                        <small class="text-muted">Nama proses produksi</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="icon-wrapper">
                                    <div class="icon-custom icon-person">
                                        <i class="bi bi-person-workspace"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-custom">{{ $btkl->jabatan->nama ?? '-' }}</div>
                                        <small class="text-muted">{{ $btkl->jabatan->kategori ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="icon-wrapper">
                                    <div class="icon-custom icon-people">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-custom">{{ $btkl->jabatan->pegawais->count() ?? 0 }} orang</div>
                                        <small class="text-muted">Jabatan: {{ $btkl->jabatan->nama ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="icon-wrapper">
                                    <div class="icon-custom icon-warning">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-warning-custom">Rp {{ number_format($btkl->prosesProduksi->biaya_btkl_per_produk ?? 0, 2, ",", ".") }}</div>
                                        <small class="text-muted">Rp {{ number_format($btkl->kapasitas_per_jam > 0 ? $btkl->tarif_btkl / $btkl->kapasitas_per_jam : 0, 2, ",", ".") }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>{{ $btkl->deskripsi_proses ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('master-data.btkl.edit', $btkl->id) }}" 
                                       class="btn btn-sm btn-elegant btn-warning-elegant rounded-pill px-3"
                                       data-bs-toggle="tooltip" 
                                       title="Edit BTKL">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <span class="d-none d-md-inline">Edit</span>
                                    </a>
                                    <button type="button" 
                                           class="btn btn-sm btn-elegant btn-danger-elegant rounded-pill px-3"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#deleteModal{{ $btkl->id }}"
                                           data-bs-toggle="tooltip" 
                                           title="Hapus BTKL">
                                        <i class="bi bi-trash3 me-1"></i>
                                        <span class="d-none d-md-inline">Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-center">
                                    <div class="icon-custom icon-warning" style="width: 64px; height: 64px; margin: 0 auto 16px;">
                                        <i class="bi bi-inbox" style="font-size: 28px;"></i>
                                    </div>
                                    <h4 class="text-muted mb-3">Belum ada data proses produksi</h4>
                                    <p class="text-muted mb-4">Mulai dengan menambahkan proses produksi pertama Anda</p>
                                    <a href="{{ route('master-data.btkl.create') }}" class="btn btn-elegant btn-primary-elegant">
                                        <i class="bi bi-plus-lg me-2"></i> Tambah Proses Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    </div>

<!-- Delete Modals -->
@forelse($btkls as $btkl)
<div class="modal fade" id="deleteModal{{ $btkl->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $btkl->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header modal-header-elegant">
                <h5 class="modal-title" id="deleteModalLabel{{ $btkl->id }}">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background: linear-gradient(135deg, #FFFEF7 0%, #FFF8E1 100%);">
                <div class="text-center mb-4">
                    <div class="icon-custom icon-warning" style="width: 80px; height: 80px; margin: 0 auto 20px; background: linear-gradient(135deg, #FEF3C7 0%, #FBBF24 100%);">
                        <i class="bi bi-trash3" style="font-size: 2.5rem; color: #92400E;"></i>
                    </div>
                    <h5 class="text-danger-custom fw-bold mb-3">Apakah Anda yakin ingin menghapus data ini?</h5>
                    <p class="text-muted mb-0" style="font-size: 14px;">Data BTKL untuk proses <strong class="text-primary-custom">{{ $btkl->jabatan->nama ?? 'Tidak Diketahui' }}</strong> akan dihapus secara permanen dan tidak dapat dikembalikan.</p>
                </div>
                
                <div class="alert alert-warning" style="background: linear-gradient(135deg, #FEF3C7 0%, #FFF8E1 100%); border: 1px solid #FBBF24; border-radius: 12px;">
                    <div class="d-flex align-items-center">
                        <div class="icon-custom icon-warning" style="width: 32px; height: 32px; background: linear-gradient(135deg, #FEF3C7 0%, #FBBF24 100%);">
                            <i class="bi bi-info-circle-fill" style="font-size: 16px; color: #92400E;"></i>
                        </div>
                        <div>
                            <strong class="text-warning-custom">Informasi Data:</strong>
                            <ul class="mb-0 mt-2" style="font-size: 13px; line-height: 1.6;">
                                <li><strong>Kode Proses:</strong> <code style="background: #F3F4F6; padding: 2px 6px; border-radius: 4px;">{{ $btkl->kode_proses }}</code></li>
                                <li><strong>Tarif BTKL:</strong> <span class="text-success-custom fw-bold">{{ $btkl->tarif_per_jam_formatted }}</span></li>
                                <li><strong>Kapasitas:</strong> <span class="text-primary-custom fw-bold">{{ number_format($btkl->kapasitas_per_jam) }} pcs/jam</span></li>
                                <li><strong>Biaya/Produk:</strong> <span class="text-warning-custom fw-bold">{{ $btkl->biaya_per_produk_formatted }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: linear-gradient(135deg, #FFFEF7 0%, #FFF8E1 100%); border-top: 1px solid #F3F4F6;">
                <button type="button" class="btn btn-elegant rounded-pill px-4" data-bs-dismiss="modal" style="background: white; color: #718096; border: 1px solid #E5E7EB;">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </button>
                <form action="{{ route('master-data.btkl.destroy', $btkl->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-elegant btn-danger-elegant rounded-pill px-4">
                        <i class="bi bi-trash3 me-2"></i>Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
@endforelse

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    tooltipList.forEach(function (tooltip) {
        tooltip.show();
    });
});

// Realtime update functionality
function refreshBTKLData() {
    fetch('/master-data/harga-pokok-produksi/calculate/1')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update BTKL data in the table
                updateBTKLTable(data.data.btkl);
                
                // Trigger storage event for other tabs
                localStorage.setItem('btkl_updated', Date.now());
                
                // Send message to other windows
                window.postMessage({
                    type: 'data_updated',
                    source: 'btkl'
                }, '*');
                
                // Show success notification
                showNotification('Data BTKL berhasil diperbarui', 'success');
            }
        })
        .catch(error => {
            console.error('Error refreshing BTKL data:', error);
            showNotification('Gagal memperbarui data BTKL', 'error');
        });
}

// Function to update BTKL table
function updateBTKLTable(btklData) {
    if (!btklData) return;
    
    // Update each row in the table
    btklData.forEach((btkl, index) => {
        const row = document.querySelector(`tr:has(td:contains("${btkl.kode_proses}"))`);
        if (!row) return;
        
        // Update cells
        const kodeCell = row.querySelector('td:nth-child(1)');
        const namaCell = row.querySelector('td:nth-child(2)');
        const jabatanCell = row.querySelector('td:nth-child(3)');
        const tarifCell = row.querySelector('td:nth-child(5)');
        const kapasitasCell = row.querySelector('td:nth-child(7)');
        const biayaCell = row.querySelector('td:nth-child(8)');
        
        if (kodeCell) kodeCell.innerHTML = `<span class="badge bg-secondary">${btkl.kode_proses}</span>`;
        if (namaCell) namaCell.innerHTML = `<div class="d-flex align-items-center"><i class="bi bi-gear-fill me-2 text-primary"></i><div><div class="fw-bold">${btkl.nama_proses}</div><small class="text-muted">Nama proses produksi</small></div></div></div>`;
        if (jabatanCell) jabatanCell.innerHTML = `<div class="d-flex align-items-center"><i class="bi bi-person-workspace me-2 text-info"></i><div><div class="fw-bold">${btkl.nama_jabatan}</div><small class="text-muted">${btkl.kategori || ''}</small></div></div></div>`;
        if (tarifCell) tarifCell.innerHTML = `<span class="fw-bold text-success">${formatNumber(btkl.tarif_per_jam)}/jam</span>`;
        if (kapasitasCell) kapasitasCell.innerHTML = `<span class="fw-bold">${formatNumber(btkl.kapasitas_per_jam)} pcs</span>`;
        if (biayaCell) biayaCell.innerHTML = `<div class="d-flex align-items-center"><i class="bi bi-cash-stack me-2 text-warning"></i><div><div class="fw-bold text-warning">${formatNumber(btkl.biaya_per_produk)}</div><small class="text-muted">Rp ${formatNumber(btkl.tarif_per_jam / btkl.kapasitas_per_jam, 2, ",", ".")}</small></div></div></div>`;
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0`;
    notification.style.zIndex = '9999';
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Format number function
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Listen for storage events - DINONAKTIFKAN UNTUK PRESENTASI
/*
window.addEventListener('storage', function(e) {
    if (e.key === 'btkl_updated') {
        refreshBTKLData();
    }
});

// Listen for custom events from other pages
window.addEventListener('message', function(event) {
    if (event.data.type === 'data_updated') {
        if (event.data.source === 'bom' || event.data.source === 'bop') {
            refreshBTKLData();
        }
    }
});

// Auto-refresh every 30 seconds
setInterval(function() {
    refreshBTKLData();
}, 30000);
*/
</script>
@endpush
@endsection

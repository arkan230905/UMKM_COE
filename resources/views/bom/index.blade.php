@extends('layouts.app')

@section('title', 'Bill of Materials (BOM)')

@push('styles')
<style>
.btn-icon {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-icon:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.btn-view {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    border: none;
    color: white;
}

.btn-edit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    color: white;
}

.btn-add {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    border: none;
    color: white;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-list-ul me-2"></i>Bill of Materials (BOM)
        </h2>
        <div class="btn-group">
            <a href="{{ route('master-data.bom.create') }}" class="btn btn-icon btn-add">
                <i class="fas fa-plus me-2"></i>Tambah BOM Baru
            </a>
            <button class="btn btn-icon btn-edit" onclick="updateBomCosts()">
                <i class="fas fa-sync me-2"></i>Update Biaya BOM
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-primary">{{ count($bomData) }}</h5>
                    <small class="text-muted">Total BOM Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-info">{{ $produks->count() }}</h5>
                    <small class="text-muted">Total Produk</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-success">{{ collect($bomData)->where('status', 'active')->count() }}</h5>
                    <small class="text-muted">Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-warning">Rp {{ number_format(collect($bomData)->sum('subtotal'), 0, ',', '.') }}</h5>
                    <small class="text-muted">Total Nilai</small>
                </div>
            </div>
        </div>
    </div>

    <!-- BOM Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list-ul me-2"></i>Daftar BOM
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Nama Bahan</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Harga/Satuan</th>
                            <th>Subtotal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bomData as $item)
                            <tr>
                                <td>{{ $item['produk'] }}</td>
                                <td>{{ $item['nama_bahan'] }}</td>
                                <td>{{ number_format($item['jumlah'], 2, ',', '.') }}</td>
                                <td>{{ $item['satuan_pembelian'] }}</td>
                                <td>Rp {{ number_format($item['harga_satuan_pembelian'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $item['status'] === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item['status'] === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-icon btn-view" onclick="viewBom({{ $item['id'] }})" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-edit" onclick="editBom({{ $item['id'] }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-delete" onclick="deleteBom({{ $item['id'] }})" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="fas fa-inbox me-2"></i>Belum ada data BOM
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function viewBom(id) {
    window.open('{{ route('master-data.bom.show', ':id') }}'.replace(':id', id), '_blank');
}

function editBom(id) {
    window.location.href = '{{ route('master-data.bom.edit', ':id') }}'.replace(':id', id);
}

function deleteBom(id) {
    if (confirm('Apakah Anda yakin ingin menghapus BOM ini?')) {
        fetch('{{ route('master-data.bom.destroy', ':id') }}'.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'BOM berhasil dihapus!');
                location.reload();
            } else {
                showAlert('danger', data.message || 'Gagal menghapus BOM');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan');
        });
    }
}

function updateBomCosts() {
    if (confirm('Apakah Anda yakin ingin memperbarui biaya BOM berdasarkan harga bahan terbaru?')) {
        showAlert('info', 'Sedang memperbarui biaya BOM...');
        
        fetch('{{ route('master-data.bom.updateCosts') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('danger', data.message || 'Gagal memperbarui biaya BOM');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan');
        });
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
    alertDiv.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endsection

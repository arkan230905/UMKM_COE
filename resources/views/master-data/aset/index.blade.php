@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Daftar Aset</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('master-data.aset.create') }}" class="btn btn-primary">Tambah Aset</a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master-data.aset.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="jenis_aset" class="form-label">Jenis Aset</label>
                    <select name="jenis_aset" id="jenis_aset" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Jenis --</option>
                        @foreach($jenisAsets as $jenis)
                            <option value="{{ $jenis->id }}" {{ request('jenis_aset') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="kategori_aset_id" class="form-label">Kategori Aset</label>
                    <select name="kategori_aset_id" id="kategori_aset_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        @if(request('jenis_aset') && $kategoriAsets->count() > 0)
                            @foreach($kategoriAsets as $kategori)
                                <option value="{{ $kategori->id }}" {{ request('kategori_aset_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="dijual" {{ request('status') == 'dijual' ? 'selected' : '' }}>Dijual</option>
                        <option value="hilang" {{ request('status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                        <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari aset..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        @if(request()->has('jenis_aset') || request()->has('kategori') || request()->has('status') || request()->has('search'))
                            <a href="{{ route('master-data.aset.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="custom-table-header">
                        <tr>
                            <th>No</th>
                            <th>Kode Aset</th>
                            <th>Nama Aset</th>
                            <th>Jenis Aset</th>
                            <th>Kategori</th>
                            <th>Harga Perolehan (Rp)</th>
                            <th>Tanggal Beli</th>
                            <th>Metode Penyusutan</th>
                            <th>Penyusutan Bulan Ini</th>
                            <th>Status Posting</th>
                            <th>COA Aset</th>
                            <th>COA Akumulasi Penyusutan</th>
                            <th>COA Beban Penyusutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asets as $key => $aset)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $aset->kode_aset }}</td>
                                <td>{{ $aset->nama_aset }}</td>
                                <td>{{ $aset->kategori->jenisAset->nama ?? '-' }}</td>
                                <td>{{ $aset->kategori->nama ?? '-' }}</td>
                                <td class="text-end">{{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                                <td>{{ is_string($aset->tanggal_beli) ? \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') : $aset->tanggal_beli->format('d/m/Y') }}</td>
                                <td>{{ $aset->metode_penyusutan ? ucfirst(str_replace('_', ' ', $aset->metode_penyusutan)) : '-' }}</td>
                                <td class="text-end">
                                    @if(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0)
                                        @if($aset->expense_coa_id && $aset->accum_depr_coa_id)
                                            <span class="text-success fw-bold">Rp {{ number_format($aset->monthly_depreciation, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-warning fw-bold" title="COA belum lengkap">
                                                Rp {{ number_format($aset->monthly_depreciation, 0, ',', '.') }}
                                                <i class="fas fa-exclamation-triangle ms-1"></i>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(isset($aset->is_posted_this_month) && $aset->is_posted_this_month)
                                        <span class="badge bg-success">Sudah Posting</span>
                                    @else
                                        <span class="badge bg-warning">Belum Posting</span>
                                    @endif
                                </td>
                                <td>
                                    @if($aset->assetCoa)
                                        <small class="text-muted">{{ $aset->assetCoa->kode_akun }}</small><br>
                                        {{ $aset->assetCoa->nama_akun }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($aset->accumDepreciationCoa)
                                        <small class="text-muted">{{ $aset->accumDepreciationCoa->kode_akun }}</small><br>
                                        {{ $aset->accumDepreciationCoa->nama_akun }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($aset->expenseCoa)
                                        <small class="text-muted">{{ $aset->expenseCoa->kode_akun }}</small><br>
                                        {{ $aset->expenseCoa->nama_akun }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeClass = [
                                            'aktif' => 'bg-success',
                                            'disewakan' => 'bg-info',
                                            'dioperasikan' => 'bg-primary',
                                            'dihapus' => 'bg-danger'
                                        ][$aset->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($aset->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group-vertical" role="group">
                                        <!-- Regular action buttons -->
                                        <div class="btn-group mb-1" role="group">
                                            <a href="{{ route('master-data.aset.edit', $aset->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('master-data.aset.destroy', $aset->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('master-data.aset.show', $aset->id) }}" class="btn btn-sm btn-secondary" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                        
                                        <!-- Individual depreciation posting button -->
                                        @if(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0 && !$aset->is_posted_this_month)
                                            @if($aset->expense_coa_id && $aset->accum_depr_coa_id)
                                                <button type="button" 
                                                        class="btn btn-sm btn-success post-depreciation-btn" 
                                                        data-aset-id="{{ $aset->id }}"
                                                        data-aset-name="{{ $aset->nama_aset }}"
                                                        data-amount="{{ number_format($aset->monthly_depreciation, 0, ',', '.') }}"
                                                        title="Posting Penyusutan">
                                                    <i class="fas fa-calculator"></i> Posting
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning" 
                                                        disabled 
                                                        title="COA belum lengkap - silakan edit aset untuk melengkapi COA">
                                                    <i class="fas fa-exclamation-triangle"></i> COA?
                                                </button>
                                            @endif
                                        @elseif($aset->is_posted_this_month)
                                            <button type="button" class="btn btn-sm btn-outline-success" disabled title="Sudah diposting">
                                                <i class="fas fa-check"></i> Posted
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Tidak ada penyusutan">
                                                <i class="fas fa-minus"></i> N/A
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-4">Tidak ada data aset</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($asets->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $asets->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th {
        white-space: nowrap;
        font-size: 0.875rem;
    }
    .table td {
        font-size: 0.875rem;
        vertical-align: middle;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        transition: all 0.15s ease-in-out;
    }
    
    /* Custom table header with brown color matching theme */
    .custom-table-header {
        background-color: #7D6347 !important;
        color: white !important;
    }
    
    .custom-table-header th {
        background-color: #7D6347 !important;
        color: white !important;
        border-color: #8B6F47 !important;
        font-weight: 600;
        padding: 0.75rem 0.5rem;
    }
    
    /* COA columns styling */
    .table td:nth-child(11),
    .table td:nth-child(12), 
    .table td:nth-child(13) {
        min-width: 180px;
        max-width: 200px;
        font-size: 0.8rem;
        line-height: 1.2;
    }
    
    .table th:nth-child(11),
    .table th:nth-child(12),
    .table th:nth-child(13) {
        min-width: 180px;
        text-align: center;
    }
    
    /* New depreciation columns styling */
    .table th:nth-child(9),
    .table th:nth-child(10) {
        min-width: 120px;
        text-align: center;
    }
    
    .table td:nth-child(9),
    .table td:nth-child(10) {
        min-width: 120px;
        font-size: 0.85rem;
    }
    
    /* Action column styling */
    .table th:nth-child(15),
    .table td:nth-child(15) {
        min-width: 140px;
    }
    
    /* Standardized button styling */
    .btn-group .btn-sm {
        min-width: 35px;
        height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 1px;
    }
    
    .btn-group .btn-sm i {
        font-size: 0.75rem;
    }
    
    /* Primary button (Edit) - Brown theme */
    .btn-primary {
        background-color: #7D6347;
        border-color: #7D6347;
    }
    
    .btn-primary:hover {
        background-color: #8B6F47;
        border-color: #8B6F47;
    }
    
    /* Danger button (Delete) - Red but consistent style */
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-danger:hover {
        background-color: #c82333;
        border-color: #c82333;
    }
    
    /* Secondary button (View) - Gray/light brown */
    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    /* Make table more compact for additional columns */
    .table-responsive {
        overflow-x: auto;
    }
    
    .table td, .table th {
        padding: 0.5rem 0.4rem;
    }
    
    /* COA text styling */
    .table td small {
        display: block;
        color: #6c757d;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
    // Format mata uang
    document.addEventListener('DOMContentLoaded', function() {
        // Format input harga
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        };

        // Format harga saat halaman dimuat
        document.querySelectorAll('.harga-format').forEach(element => {
            if (element.textContent.trim() !== '') {
                element.textContent = formatRupiah(parseInt(element.textContent));
            }
        });

        // Handle individual depreciation posting
        document.querySelectorAll('.post-depreciation-btn').forEach(button => {
            button.addEventListener('click', function() {
                const asetId = this.dataset.asetId;
                const asetName = this.dataset.asetName;
                const amount = this.dataset.amount;
                
                if (confirm(`Apakah Anda yakin ingin memposting penyusutan untuk aset "${asetName}" sebesar Rp ${amount}?`)) {
                    // Disable button and show loading
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                    
                    // Send AJAX request
                    fetch(`/master-data/aset/${asetId}/post-depreciation`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            alert(`✅ ${data.message}\nJumlah: Rp ${data.amount}`);
                            
                            // Update button to show posted status
                            this.className = 'btn btn-sm btn-outline-success';
                            this.innerHTML = '<i class="fas fa-check"></i> Posted';
                            this.title = 'Sudah diposting';
                            
                            // Update status badge in the same row
                            const row = this.closest('tr');
                            const statusCell = row.querySelector('td:nth-child(10)');
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="badge bg-success">Sudah Posting</span>';
                            }
                        } else {
                            // Show error message
                            alert(`❌ ${data.message}`);
                            
                            // Re-enable button
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-calculator"></i> Posting';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Terjadi kesalahan saat memposting penyusutan');
                        
                        // Re-enable button
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-calculator"></i> Posting';
                    });
                }
            });
        });
    });
</script>
@endpush

@endsection


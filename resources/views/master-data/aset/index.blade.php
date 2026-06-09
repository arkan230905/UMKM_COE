@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
<div class="container-fluid">
    <!-- HEADER HALAMAN -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-briefcase me-2"></i>Daftar Aset
        </h2>
        <a href="{{ route('master-data.aset.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Aset
        </a>
    </div>

    <!-- FILTER SECTION -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Aset
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('master-data.aset.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Jenis Aset</label>
                        <select name="jenis_aset" class="form-select">
                            <option value="">-- Semua Jenis --</option>
                            @foreach($jenisAsets as $jenis)
                                <option value="{{ $jenis->id }}" {{ request('jenis_aset') == $jenis->id ? 'selected' : '' }}>
                                    {{ $jenis->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kategori Aset</label>
                        <select name="kategori_aset_id" class="form-select">
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
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            <option value="dijual" {{ request('status') == 'dijual' ? 'selected' : '' }}>Dijual</option>
                            <option value="hilang" {{ request('status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                            <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cari Aset</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('master-data.aset.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DAFTAR ASET -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Aset
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1400px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th class="nowrap">Kode Aset</th>
                            <th class="nowrap">Nama Aset</th>
                            <th class="nowrap">Jenis Aset</th>
                            <th class="nowrap">Kategori</th>
                            <th class="nowrap text-end">Harga Perolehan</th>
                            <th class="nowrap text-center">Tanggal Beli</th>
                            <th class="nowrap">Metode Penyusutan</th>
                            <th class="nowrap text-end">Penyusutan Bulan Ini</th>
                            <th class="nowrap text-center">Status Posting</th>
                            <th class="nowrap">COA Aset</th>
                            <th class="nowrap">COA Akumulasi Penyusutan</th>
                            <th class="nowrap">COA Beban Penyusutan</th>
                            <th class="nowrap text-center">Status</th>
                            <th class="text-center" style="width: 200px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asets as $key => $aset)
                        <tr>
                            <td class="text-center">{{ ($asets->currentPage()-1)*$asets->perPage()+$key+1 }}</td>
                            <td class="nowrap fw-semibold">{{ $aset->kode_aset }}</td>
                            <td class="nowrap">{{ $aset->nama_aset }}</td>
                            <td class="nowrap">{{ $aset->kategori->jenisAset->nama ?? '-' }}</td>
                            <td class="nowrap">{{ $aset->kategori->nama ?? '-' }}</td>
                            <td class="nowrap text-end">Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                            <td class="nowrap text-center">
                                {{ $aset->tanggal_beli ? \Carbon\Carbon::parse($aset->tanggal_beli)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="nowrap">
                                @php
                                    $metodeLabel = [
                                        'garis_lurus'        => 'Garis Lurus',
                                        'saldo_menurun'      => 'Saldo Menurun',
                                        'sum_of_years_digits'=> 'Sum of Years Digits',
                                    ][$aset->metode_penyusutan] ?? ucfirst(str_replace('_',' ',$aset->metode_penyusutan ?? '-'));
                                @endphp
                                {{ $metodeLabel }}
                            </td>
                            <td class="nowrap text-end fw-semibold">
                                @if(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0)
                                    Rp {{ number_format($aset->monthly_depreciation, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="nowrap text-center">
                                @if(isset($aset->is_posted_this_month) && $aset->is_posted_this_month)
                                    <span class="badge bg-success">Sudah Posting</span>
                                @else
                                    <span class="badge bg-warning">Belum Posting</span>
                                @endif
                            </td>
                            <td class="nowrap">
                                @if($aset->assetCoa)
                                    <div class="small">
                                        <div class="fw-semibold">{{ $aset->assetCoa->kode_akun }}</div>
                                        <div class="text-muted">{{ $aset->assetCoa->nama_akun }}</div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="nowrap">
                                @if($aset->accumDepreciationCoa)
                                    <div class="small">
                                        <div class="fw-semibold">{{ $aset->accumDepreciationCoa->kode_akun }}</div>
                                        <div class="text-muted">{{ $aset->accumDepreciationCoa->nama_akun }}</div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="nowrap">
                                @if($aset->expenseCoa)
                                    <div class="small">
                                        <div class="fw-semibold">{{ $aset->expenseCoa->kode_akun }}</div>
                                        <div class="text-muted">{{ $aset->expenseCoa->nama_akun }}</div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="nowrap text-center">
                                @php
                                    $statusBadgeClass = [
                                        'aktif' => 'bg-success',
                                        'nonaktif' => 'bg-danger',
                                        'disewakan' => 'bg-success',
                                        'dioperasikan' => 'bg-success',
                                        'dihapus' => 'bg-danger'
                                    ][$aset->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $statusBadgeClass }}">
                                    {{ ucfirst($aset->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-grid" style="grid-template-columns: repeat(2, 1fr); gap: 5px;">
                                    <!-- Row 1: Detail | Edit -->
                                    <a href="{{ route('master-data.aset.show', $aset->id) }}" class="btn btn-sm btn-outline-success" title="Lihat Detail">
                                        Detail
                                    </a>
                                    <a href="{{ route('master-data.aset.edit', $aset->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Aset">
                                        Edit
                                    </a>
                                    
                                    <!-- Row 2: Posting | Hapus -->
                                    @if(isset($aset->is_posted_this_month) && $aset->is_posted_this_month)
                                        <button class="btn btn-sm btn-success" disabled title="Sudah Posted">
                                            <i class="fas fa-check me-1"></i>Posted
                                        </button>
                                    @elseif(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0 && $aset->expense_coa_id && $aset->accum_depr_coa_id)
                                        <button type="button" class="btn btn-sm btn-outline-info post-depreciation-btn"
                                            data-aset-id="{{ $aset->id }}"
                                            data-aset-name="{{ $aset->nama_aset }}"
                                            data-amount="{{ number_format($aset->monthly_depreciation, 0, ',', '.') }}"
                                            title="Posting Penyusutan">
                                            <i class="fas fa-file-invoice me-1"></i>Posting
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak tersedia">
                                            <i class="fas fa-minus me-1"></i>N/A
                                        </button>
                                    @endif
                                    
                                    <form action="{{ route('master-data.aset.destroy', $aset->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('Yakin ingin hapus aset ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Hapus Aset">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data aset</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($asets->hasPages())
    <div class="mt-3">
        {{ $asets->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.post-depreciation-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.asetId;
            const name   = this.dataset.asetName;
            const amount = this.dataset.amount;

            if (!confirm(`Posting penyusutan untuk "${name}" sebesar Rp ${amount}?`)) return;

            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/master-data/aset/${id}/post-depreciation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    localStorage.setItem('flash_msg', data.message);
                    localStorage.setItem('flash_type', 'success');
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(() => {
                alert('❌ Terjadi kesalahan');
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
    });
});
</script>
@endpush

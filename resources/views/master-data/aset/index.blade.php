@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
<div class="container-fluid" style="background-color: #f0ebe4; padding: 20px;">
    <!-- HEADER HALAMAN -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color: #1a1a2e;">
            <i class="fas fa-briefcase me-2"></i>Daftar Aset
        </h2>
        <a href="{{ route('master-data.aset.create') }}" class="btn" style="background-color: #5a3a1a; color: white; border-radius: 6px; padding: 10px 20px; font-weight: 500;">
            <i class="fas fa-plus me-2"></i>Tambah Aset
        </a>
    </div>

    <!-- FILTER SECTION -->
    <div class="card mb-4" style="background-color: #ffffff; border: 1px solid #e0d8ce; border-radius: 8px;">
        <div style="background-color: #5a3a1a; color: white; padding: 12px 20px; border-radius: 8px 8px 0 0;">
            <h6 class="mb-0" style="font-size: 14px; font-weight: 600;">
                <i class="fas fa-filter me-2"></i>Filter Aset
            </h6>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form method="GET" action="{{ route('master-data.aset.index') }}">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 600; color: #1a1a2e;">Jenis Aset</label>
                        <select name="jenis_aset" class="form-select" style="border: 1px solid #e0d8ce; border-radius: 6px;">
                            <option value="">-- Semua Jenis --</option>
                            @foreach($jenisAsets as $jenis)
                                <option value="{{ $jenis->id }}" {{ request('jenis_aset') == $jenis->id ? 'selected' : '' }}>
                                    {{ $jenis->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 600; color: #1a1a2e;">Kategori Aset</label>
                        <select name="kategori_aset_id" class="form-select" style="border: 1px solid #e0d8ce; border-radius: 6px;">
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
                        <label class="form-label" style="font-size: 13px; font-weight: 600; color: #1a1a2e;">Status</label>
                        <select name="status" class="form-select" style="border: 1px solid #e0d8ce; border-radius: 6px;">
                            <option value="">-- Semua Status --</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            <option value="dijual" {{ request('status') == 'dijual' ? 'selected' : '' }}>Dijual</option>
                            <option value="hilang" {{ request('status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                            <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 600; color: #1a1a2e;">Cari Aset</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama..." value="{{ request('search') }}" style="border: 1px solid #e0d8ce; border-radius: 6px;">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn" style="background-color: #5a3a1a; color: white; border-radius: 6px; padding: 8px 16px; font-weight: 500; border: none;">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                    <a href="{{ route('master-data.aset.index') }}" class="btn btn-outline-secondary" style="border-radius: 6px; padding: 8px 16px;">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DAFTAR ASET -->
    <div class="card" style="background-color: #ffffff; border: 1px solid #e0d8ce; border-radius: 8px;">
        <div style="background-color: #5a3a1a; color: white; padding: 12px 20px; border-radius: 8px 8px 0 0;">
            <h6 class="mb-0" style="font-size: 14px; font-weight: 600;">
                <i class="fas fa-list me-2"></i>Daftar Aset
            </h6>
        </div>
        <div class="card-body p-0">
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="table table-hover mb-0" style="background-color: #ffffff;">
                    <thead>
                        <tr style="background-color: #5a3a1a; color: white;">
                            <th class="text-center" style="width: 50px; padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">No</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Kode Aset</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Nama Aset</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Jenis Aset</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Kategori</th>
                            <th class="text-end" style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Harga Perolehan (Rp)</th>
                            <th class="text-center" style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Tanggal Beli</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Metode Penyusutan</th>
                            <th class="text-end" style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Penyusutan Bulan Ini</th>
                            <th class="text-center" style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Status Posting</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">COA Aset</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">COA Akumulasi Penyusutan</th>
                            <th style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">COA Beban Penyusutan</th>
                            <th class="text-center" style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Status</th>
                            <th class="text-center" style="padding: 10px; white-space: nowrap; font-size: 11px; font-weight: 600;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asets as $key => $aset)
                        <tr style="border-bottom: 1px solid #f0e8e0;">
                            <td class="text-center" style="padding: 10px; white-space: nowrap;">{{ ($asets->currentPage()-1)*$asets->perPage()+$key+1 }}</td>
                            <td style="padding: 10px; white-space: nowrap; font-family: inherit; font-size: 12px;">{{ $aset->kode_aset }}</td>
                            <td style="padding: 10px; white-space: nowrap; font-weight: 500;">{{ $aset->nama_aset }}</td>
                            <td style="padding: 10px; white-space: nowrap;">{{ $aset->kategori->jenisAset->nama ?? '-' }}</td>
                            <td style="padding: 10px; white-space: nowrap;">{{ $aset->kategori->nama ?? '-' }}</td>
                            <td class="text-end" style="padding: 10px; white-space: nowrap;">Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                            <td class="text-center" style="padding: 10px; white-space: nowrap;">
                                {{ $aset->tanggal_beli ? \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') : '-' }}
                            </td>
                            <td style="padding: 10px; white-space: nowrap;">
                                @php
                                    $metodeLabel = [
                                        'garis_lurus'        => 'Garis Lurus',
                                        'saldo_menurun'      => 'Saldo Menurun',
                                        'sum_of_years_digits'=> 'Sum of Years Digits',
                                    ][$aset->metode_penyusutan] ?? ucfirst(str_replace('_',' ',$aset->metode_penyusutan ?? '-'));
                                @endphp
                                {{ $metodeLabel }}
                            </td>
                            <td class="text-end" style="padding: 10px; white-space: nowrap; color: #1a0e04; font-weight: bold;">
                                @if(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0)
                                    Rp {{ number_format($aset->monthly_depreciation, 0, ',', '.') }}
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td class="text-center" style="padding: 10px; white-space: nowrap;">
                                @if(isset($aset->is_posted_this_month) && $aset->is_posted_this_month)
                                    <span class="badge" style="background-color: #d4edda; color: #1a5c2a; padding: 6px 10px; border-radius: 4px; font-size: 11px;">Sudah Posting</span>
                                @else
                                    <span class="badge" style="background-color: #fff3cc; color: #7a5200; padding: 6px 10px; border-radius: 4px; font-size: 11px;">Belum Posting</span>
                                @endif
                            </td>
                            <td style="padding: 10px; white-space: nowrap; font-size: 12px;">
                                @if($aset->assetCoa)
                                    <div style="line-height: 1.3;">
                                        <div style="font-weight: 600; font-size: 11px;">{{ $aset->assetCoa->kode_akun }}</div>
                                        <div style="color: #666; font-size: 11px;">{{ $aset->assetCoa->nama_akun }}</div>
                                    </div>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td style="padding: 10px; white-space: nowrap; font-size: 12px;">
                                @if($aset->accumDepreciationCoa)
                                    <div style="line-height: 1.3;">
                                        <div style="font-weight: 600; font-size: 11px;">{{ $aset->accumDepreciationCoa->kode_akun }}</div>
                                        <div style="color: #666; font-size: 11px;">{{ $aset->accumDepreciationCoa->nama_akun }}</div>
                                    </div>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td style="padding: 10px; white-space: nowrap; font-size: 12px;">
                                @if($aset->expenseCoa)
                                    <div style="line-height: 1.3;">
                                        <div style="font-weight: 600; font-size: 11px;">{{ $aset->expenseCoa->kode_akun }}</div>
                                        <div style="color: #666; font-size: 11px;">{{ $aset->expenseCoa->nama_akun }}</div>
                                    </div>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td class="text-center" style="padding: 10px; white-space: nowrap;">
                                @php
                                    $statusBadgeClass = [
                                        'aktif' => ['bg' => '#d4edda', 'text' => '#1a5c2a'],
                                        'nonaktif' => ['bg' => '#fdeaea', 'text' => '#aa2222'],
                                        'disewakan' => ['bg' => '#d4edda', 'text' => '#1a5c2a'],
                                        'dioperasikan' => ['bg' => '#d4edda', 'text' => '#1a5c2a'],
                                        'dihapus' => ['bg' => '#fdeaea', 'text' => '#aa2222']
                                    ][$aset->status] ?? ['bg' => '#f0f0f0', 'text' => '#666'];
                                @endphp
                                <span class="badge" style="background-color: {{ $statusBadgeClass['bg'] }}; color: {{ $statusBadgeClass['text'] }}; padding: 6px 10px; border-radius: 4px; font-size: 11px;">
                                    {{ ucfirst($aset->status) }}
                                </span>
                            </td>
                            <td class="text-center" style="padding: 10px; white-space: nowrap;">
                                <div style="display: flex; flex-direction: row; align-items: center; gap: 5px; justify-content: center;">
                                    <a href="{{ route('master-data.aset.show', $aset->id) }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: #e6f5ec; color: #1a7a40; border: 1px solid #a8dfc0; border-radius: 7px; text-decoration: none; font-size: 14px;" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('master-data.aset.edit', $aset->id) }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: #eef4fb; color: #1a5aaa; border: 1px solid #b5cef0; border-radius: 7px; text-decoration: none; font-size: 14px;" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('master-data.aset.destroy', $aset->id) }}" method="POST" style="display: inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: #fdeaea; color: #aa2222; border: 1px solid #f0b5b5; border-radius: 7px; font-size: 14px; cursor: pointer;" title="Hapus" onclick="return confirm('Hapus aset ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @if(isset($aset->is_posted_this_month) && $aset->is_posted_this_month)
                                        <button style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: #d4edda; color: #1a5c2a; border: 1px solid #a8dfc0; border-radius: 7px; font-size: 14px; cursor: not-allowed;" disabled title="Sudah Posted">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @elseif(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0 && $aset->expense_coa_id && $aset->accum_depr_coa_id)
                                        <button type="button" class="post-depreciation-btn" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: #faf3e0; color: #7a5200; border: 1px solid #e8d08a; border-radius: 7px; font-size: 14px; cursor: pointer;"
                                            data-aset-id="{{ $aset->id }}"
                                            data-aset-name="{{ $aset->nama_aset }}"
                                            data-amount="{{ number_format($aset->monthly_depreciation, 0, ',', '.') }}"
                                            title="Posting Penyusutan">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    @else
                                        <button style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: #f0f0f0; color: #999; border: 1px solid #ddd; border-radius: 7px; font-size: 14px; cursor: not-allowed;" disabled title="Tidak tersedia">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center py-5" style="color: #999;">
                                <i class="fas fa-box-open" style="font-size: 2rem; opacity: 0.4; display: block; margin-bottom: 10px;"></i>
                                <p>Tidak ada data aset</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($asets->hasPages())
            <div style="padding: 15px 20px; border-top: 1px solid #e0d8ce;">
                {{ $asets->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>

    <!-- FOOTER -->
    <div class="row mt-4 mb-3">
        <div class="col-md-6">
            <p class="text-muted small mb-0" style="font-size: 12px;">© 2025 SIMCOST - All rights reserved.</p>
        </div>
        <div class="col-md-6 text-end">
            <p class="text-muted small mb-0" style="font-size: 12px;">Versi 1.0.0</p>
        </div>
    </div>
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
                    this.innerHTML = '<i class="fas fa-file-invoice"></i>';
                }
            })
            .catch(() => {
                alert('❌ Terjadi kesalahan');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-file-invoice"></i>';
            });
        });
    });
});
</script>
@endpush

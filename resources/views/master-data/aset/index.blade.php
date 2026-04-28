@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
<div class="container mt-4">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0 fw-bold">Daftar Aset</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('master-data.aset.create') }}" class="btn btn-primary">Tambah Aset</a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master-data.aset.index') }}" class="row g-3 align-items-end">
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
                        <option value="aktif"    {{ request('status') == 'aktif'    ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="dijual"   {{ request('status') == 'dijual'   ? 'selected' : '' }}>Dijual</option>
                        <option value="hilang"   {{ request('status') == 'hilang'   ? 'selected' : '' }}>Hilang</option>
                        <option value="rusak"    {{ request('status') == 'rusak'    ? 'selected' : '' }}>Rusak</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari aset..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        @if(request()->anyFilled(['jenis_aset','kategori_aset_id','status','search']))
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="custom-table-header">
                        <tr>
                            <th class="text-center" style="width:45px">No</th>
                            <th>Kode Aset</th>
                            <th>Nama Aset</th>
                            <th>Jenis Aset</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga Perolehan (Rp)</th>
                            <th class="text-center">Tanggal Beli</th>
                            <th>Metode Penyusutan</th>
                            <th class="text-end">Penyusutan Bulan Ini</th>
                            <th class="text-center">Status Posting</th>
                            <th>COA Aset</th>
                            <th>COA Akumulasi Penyusutan</th>
                            <th>COA Beban Penyusutan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asets as $key => $aset)
                            <tr>
                                <td class="text-center">{{ ($asets->currentPage()-1)*$asets->perPage()+$key+1 }}</td>
                                <td><span class="kode-aset">{{ $aset->kode_aset }}</span></td>
                                <td class="fw-semibold">{{ $aset->nama_aset }}</td>
                                <td>{{ $aset->kategori->jenisAset->nama ?? '-' }}</td>
                                <td>{{ $aset->kategori->nama ?? '-' }}</td>
                                <td class="text-end">{{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    {{ $aset->tanggal_beli ? \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    @php
                                        $metodeLabel = [
                                            'garis_lurus'        => 'Garis Lurus',
                                            'saldo_menurun'      => 'Saldo Menurun',
                                            'sum_of_years_digits'=> 'Sum of Years Digits',
                                        ][$aset->metode_penyusutan] ?? ucfirst(str_replace('_',' ',$aset->metode_penyusutan ?? '-'));
                                    @endphp
                                    {{ $metodeLabel }}
                                </td>
                                <td class="text-end">
                                    @if(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0)
                                        @if($aset->expense_coa_id && $aset->accum_depr_coa_id)
                                            <span class="text-success fw-bold">Rp {{ number_format($aset->monthly_depreciation, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-warning fw-bold" title="COA belum lengkap">
                                                Rp {{ number_format($aset->monthly_depreciation, 0, ',', '.') }}
                                                <i class="fas fa-exclamation-triangle ms-1" style="font-size:.7rem"></i>
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
                                        <span class="badge bg-warning text-dark">Belum Posting</span>
                                    @endif
                                </td>
                                {{-- COA Aset --}}
                                <td>
                                    @if($aset->assetCoa)
                                        <div class="coa-cell">
                                            <span class="coa-kode">{{ $aset->assetCoa->kode_akun }}</span>
                                            <span class="coa-nama">{{ $aset->assetCoa->nama_akun }}</span>
                                        </div>
                                    @else <span class="text-muted">-</span> @endif
                                </td>
                                {{-- COA Akumulasi --}}
                                <td>
                                    @if($aset->accumDepreciationCoa)
                                        <div class="coa-cell">
                                            <span class="coa-kode">{{ $aset->accumDepreciationCoa->kode_akun }}</span>
                                            <span class="coa-nama">{{ $aset->accumDepreciationCoa->nama_akun }}</span>
                                        </div>
                                    @else <span class="text-muted">-</span> @endif
                                </td>
                                {{-- COA Beban --}}
                                <td>
                                    @if($aset->expenseCoa)
                                        <div class="coa-cell">
                                            <span class="coa-kode">{{ $aset->expenseCoa->kode_akun }}</span>
                                            <span class="coa-nama">{{ $aset->expenseCoa->nama_akun }}</span>
                                        </div>
                                    @else <span class="text-muted">-</span> @endif
                                </td>
                                {{-- Status --}}
                                <td class="text-center">
                                    @php
                                        $badgeClass = ['aktif'=>'bg-success','disewakan'=>'bg-info','dioperasikan'=>'bg-primary','dihapus'=>'bg-danger'][$aset->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($aset->status) }}</span>
                                </td>
                                {{-- Aksi --}}
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('master-data.aset.show', $aset->id) }}" class="btn btn-sm btn-secondary" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('master-data.aset.edit', $aset->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('master-data.aset.destroy', $aset->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus aset ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @if(isset($aset->is_posted_this_month) && $aset->is_posted_this_month)
                                            <button class="btn btn-sm btn-outline-success" disabled>
                                                <i class="fas fa-check"></i> Posted
                                            </button>
                                        @elseif(isset($aset->monthly_depreciation) && $aset->monthly_depreciation > 0 && $aset->expense_coa_id && $aset->accum_depr_coa_id)
                                            <button type="button" class="btn btn-sm btn-success post-depreciation-btn"
                                                data-aset-id="{{ $aset->id }}"
                                                data-aset-name="{{ $aset->nama_aset }}"
                                                data-amount="{{ number_format($aset->monthly_depreciation, 0, ',', '.') }}"
                                                title="Posting Penyusutan">
                                                <i class="fas fa-calculator"></i> Posting
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak tersedia">
                                                <i class="fas fa-minus"></i> N/A
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                                    Tidak ada data aset
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($asets->hasPages())
                <div class="px-3 py-2 border-top">
                    {{ $asets->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Header tabel coklat */
    .custom-table-header th {
        background-color: #7D6347 !important;
        color: #fff !important;
        font-weight: 600;
        font-size: .82rem;
        padding: 10px 12px;
        white-space: nowrap;
        vertical-align: middle;
        border: none;
    }

    /* Body tabel */
    .table tbody td {
        font-size: .83rem;
        padding: 10px 12px;
        vertical-align: middle;
    }

    /* Kode aset */
    .kode-aset {
        font-size: .83rem;
        color: #212529;
        font-weight: 500;
    }

    /* COA cell — kode dan nama sejajar rapi */
    .coa-cell {
        display: flex;
        flex-direction: column;
        line-height: 1.4;
        min-width: 150px;
    }
    .coa-kode {
        font-size: .78rem;
        font-weight: 600;
        color: #212529;
    }
    .coa-nama {
        font-size: .8rem;
        color: #212529;
    }

    /* Tombol primary coklat */
    .btn-primary {
        background-color: #7D6347;
        border-color: #7D6347;
    }
    .btn-primary:hover {
        background-color: #5c4733;
        border-color: #5c4733;
    }

    /* Ukuran tombol aksi seragam */
    .btn-sm {
        min-width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        padding: 0 8px;
    }
</style>
@endpush

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
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proses...';

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
                    this.innerHTML = '<i class="fas fa-calculator"></i> Posting';
                }
            })
            .catch(() => {
                alert('❌ Terjadi kesalahan');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-calculator"></i> Posting';
            });
        });
    });
});
</script>
@endpush

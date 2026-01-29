@extends('layouts.pegawai-pembelian')

@section('content')
<style>
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
    border-radius: 6px;
}

.btn-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-icon::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.3s ease, height 0.3s ease;
}

.btn-icon:hover::before {
    width: 100%;
    height: 100%;
}

.btn-icon i {
    font-size: 14px;
    z-index: 1;
    position: relative;
}

.btn-info.btn-icon:hover {
    background: linear-gradient(135deg, #17a2b8, #138496);
    border-color: #138496;
}

.btn-warning.btn-icon:hover {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    border-color: #e0a800;
}

.btn-danger.btn-icon:hover {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border-color: #c82333;
}

.btn-icon:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Professional button styling */
.btn-icon {
    font-weight: 600;
    font-size: 0;
    letter-spacing: 0;
    width: 35px;
    height: 35px;
    padding: 0;
    border: none;
    text-transform: uppercase;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    text-decoration: none;
    color: transparent;
    cursor: pointer;
    overflow: hidden;
    position: relative;
    vertical-align: middle;
    margin: 0;
    box-sizing: border-box;
    float: left;
}

.btn-icon:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.btn-icon:active {
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-info.btn-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.btn-info.btn-icon:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

.btn-warning.btn-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.btn-warning.btn-icon:hover {
    background: linear-gradient(135deg, #e879f9 0%, #ef4444 100%);
}

.btn-danger.btn-icon {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.btn-danger.btn-icon:hover {
    background: linear-gradient(135deg, #f8557c 0%, #fbbf24 100%);
}

/* Modern SVG Icons - Same styling for all */
.btn-icon svg {
    width: 18px;
    height: 18px;
    fill: white;
    pointer-events: none;
    z-index: 1;
    flex-shrink: 0;
}

/* Form button styling - EXACTLY same as links */
.btn-icon[type="submit"] {
    font-weight: 600;
    font-size: 0;
    letter-spacing: 0;
    width: 35px;
    height: 35px;
    padding: 0;
    border: none;
    text-transform: uppercase;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    text-decoration: none;
    color: transparent;
    cursor: pointer;
    overflow: hidden;
    position: relative;
    vertical-align: middle;
    margin: 0;
    box-sizing: border-box;
    float: left;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.btn-icon[type="submit"]:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    background: linear-gradient(135deg, #f8557c 0%, #fbbf24 100%);
}

.btn-icon[type="submit"]:active {
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Button group styling */
.btn-group {
    display: inline-flex;
    vertical-align: middle;
    font-size: 0;
    line-height: 0;
    white-space: nowrap;
}

.btn-group .btn + .btn,
.btn-group .btn + .btn-group,
.btn-group .btn-group + .btn,
.btn-group .btn-group + .btn-group {
    margin-left: -1px;
}

.btn-group > .btn:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.btn-group > .btn:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

/* Remove default button styles */
.btn-icon[type="submit"]:focus {
    outline: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-icon[type="submit"]:focus:hover {
    outline: none;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* Hide text completely */
.btn-icon span {
    display: none;
}
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-shopping-cart"></i> Daftar Pembelian
            </h2>
            <p class="text-muted mb-0">Kelola transaksi pembelian bahan baku</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pegawai-pembelian.pembelian.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat Pembelian Baru
            </a>
            <!-- Filter Button -->
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="{{ route('pegawai-pembelian.pembelian.index') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Vendor</label>
                                <select name="vendor_id" class="form-select">
                                    <option value="">Semua Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->nama_vendor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                    <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Urutkan</label>
                                <select name="urutkan" class="form-select">
                                    <option value="terbaru">Terbaru</option>
                                    <option value="terlama" {{ request('urutkan') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($pembelians->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nomor Pembelian</th>
                            <th>Tanggal</th>
                            <th>Vendor</th>
                            <th>Item Dibeli</th>
                            <th class="text-end">Total Harga</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pembelians as $index => $pembelian)
                        <tr>
                            <td class="text-center">{{ $pembelians->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $pembelian->nomor_pembelian ?? 'PB-' . str_pad($pembelian->id, 5, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $pembelian->vendor->nama_vendor ?? '-' }}</td>
                            <td>
                                @if($pembelian->details && $pembelian->details->count() > 0)
                                <small>
                                @foreach($pembelian->details as $detail)
                                    <div class="mb-1">
                                        @if($detail->bahan_baku_id && $detail->bahanBaku)
                                            • <span class="badge bg-primary">BB</span> {{ $detail->bahanBaku->nama_bahan }} 
                                            ({{ number_format($detail->jumlah, 0, '.', '') }} {{ $detail->bahanBaku->satuan->nama ?? 'unit' }})
                                        @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                            • <span class="badge bg-info">BP</span> {{ $detail->bahanPendukung->nama_bahan }} 
                                            ({{ number_format($detail->jumlah, 0, '.', '') }} {{ $detail->bahanPendukung->satuanRelation->nama ?? 'unit' }})
                                        @else
                                            • -
                                        @endif
                                        @ Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                        = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                            </td>
                            <td class="text-end">
                                @php
                                    // Hitung total dari details untuk konsistensi (sama seperti admin)
                                    $totalPembelian = 0;
                                    if ($pembelian->details && $pembelian->details->count() > 0) {
                                        $totalPembelian = $pembelian->details->sum(function($detail) {
                                            return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                        });
                                    }
                                    
                                    // Jika ada total_harga di database, gunakan yang lebih besar (sama seperti admin)
                                    if ($pembelian->total_harga > $totalPembelian) {
                                        $totalPembelian = $pembelian->total_harga;
                                    }
                                @endphp
                                <span class="fw-semibold">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @php
                                    $paymentMethod = $pembelian->payment_method ?? 'cash';
                                    if ($paymentMethod === 'credit') {
                                        $badgeClass = 'bg-warning';
                                        $paymentText = 'Kredit';
                                    } elseif ($paymentMethod === 'transfer') {
                                        $badgeClass = 'bg-info';
                                        $paymentText = 'Transfer';
                                    } else {
                                        $badgeClass = 'bg-success';
                                        $paymentText = 'Tunai';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $paymentText }}
                                </span>
                            </td>
                            <td>
                                @php
                                    // Logic status sama dengan admin - cek apakah ada retur
                                    $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $pembelian->id)->exists();
                                    
                                    if ($hasRetur) {
                                        $statusText = 'Ada Retur';
                                        $statusBadgeClass = 'bg-warning';
                                    } else {
                                        $statusText = 'Tidak Ada Retur';
                                        $statusBadgeClass = 'bg-success';
                                    }
                                @endphp
                                <span class="badge {{ $statusBadgeClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('pegawai-pembelian.pembelian.show', $pembelian->id) }}" 
                                       class="btn btn-info btn-icon" title="Lihat Detail">
                                        <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                        <span>Detail</span>
                                    </a>
                                    <a href="{{ route('pegawai-pembelian.pembelian.edit', $pembelian->id) }}" 
                                       class="btn btn-warning btn-icon" title="Ubah Data">
                                        <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </a>
                                    <form action="{{ route('pegawai-pembelian.pembelian.destroy', $pembelian->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon" title="Hapus Data" onclick="return confirm('Apakah Anda yakin ingin menghapus pembelian ini?')">
                                            <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $pembelians->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc;"></i>
                <p class="text-muted mt-3">Belum ada data pembelian</p>
                <a href="{{ route('pegawai-pembelian.pembelian.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Buat Pembelian Baru
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

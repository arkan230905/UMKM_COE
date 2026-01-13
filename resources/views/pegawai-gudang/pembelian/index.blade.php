@extends('layouts.pegawai-gudang')

@section('title', 'Daftar Pembelian')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .search-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background: white;
    }
    
    .search-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .search-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: none;
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
    }
    
    .modern-table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        background: white;
    }
    
    .modern-table .table {
        margin-bottom: 0;
    }
    
    .modern-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1.2rem 1rem;
        position: relative;
    }
    
    .modern-table thead th::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    }
    
    .modern-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .modern-table tbody tr:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
    }
    
    .modern-table tbody td {
        padding: 1.2rem 1rem;
        vertical-align: middle;
        border: none;
    }
    
    .item-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .item-card:hover {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-color: #667eea;
        transform: translateX(5px);
    }
    
    .badge-modern {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-bb {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
    }
    
    .badge-bp {
        background: linear-gradient(45deg, #f093fb, #f5576c);
        color: white;
    }
    
    .btn-action {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .btn-action::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-action:hover::before {
        left: 100%;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .btn-primary-modern {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
    }
    
    .btn-info-modern {
        background: linear-gradient(45deg, #4facfe, #00f2fe);
        color: white;
    }
    
    .btn-warning-modern {
        background: linear-gradient(45deg, #f093fb, #f5576c);
        color: white;
    }
    
    .btn-success-modern {
        background: linear-gradient(45deg, #43e97b, #38f9d7);
        color: white;
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #43e97b);
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .pagination-modern .page-link {
        border: none;
        border-radius: 8px;
        margin: 0 2px;
        padding: 0.75rem 1rem;
        color: #667eea;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .pagination-modern .page-link:hover {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .pagination-modern .page-item.active .page-link {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .form-control-modern {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        background: white;
        color: #333 !important;
    }

    .form-control-modern::placeholder {
        color: #333 !important;
        font-weight: 500;
        opacity: 0.8;
    }
    
    /* Force all table text to be black - comprehensive approach */
    .table td,
    .table th,
    .table td *,
    .table th *,
    .table tbody tr td,
    .table tbody tr th,
    .table td span,
    .table td div,
    .table td p,
    .table td strong,
    .table td small,
    .table td em,
    .table td i,
    .table td b,
    .table th span,
    .table th div,
    .table th p,
    .table th strong,
    .table th small,
    .table th em,
    .table th i,
    .table th b,
    .table-responsive *,
    .modern-table *,
    .table * {
        color: #000 !important;
    }
    
    /* Remove striped pattern and make all rows white */
    .table-striped tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }
    
    .table-striped tbody tr:nth-child(even) {
        background-color: #ffffff !important;
    }
    
    .table-striped tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    
    /* Force black text on hover too */
    .table-striped tbody tr:hover *,
    .table-striped tbody tr:hover td,
    .table-striped tbody tr:hover th {
        color: #000 !important;
    }
    
    /* Exception: Keep badges with their original colors */
    .table td .badge,
    .modern-table .badge {
        color: inherit !important;
    }
    
    .retur-info {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .modern-table {
            font-size: 0.875rem;
        }
        
        .item-card {
            padding: 0.5rem;
        }
        
        .btn-action {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Modern Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-shopping-cart me-3"></i>
                    Daftar Pembelian
                </h1>
                <p class="mb-0 opacity-90">
                    Kelola dan pantau semua transaksi pembelian bahan baku dan pendukung
                </p>
                <small class="opacity-75">
                    <i class="fas fa-calendar me-2"></i>{{ date('l, d F Y') }}
                </small>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('pegawai-gudang.pembelian.create') }}" class="btn btn-lg btn-action" style="background: linear-gradient(45deg, #4a148c, #6a1b9a); color: white !important; font-weight: 600; border: none; box-shadow: 0 5px 15px rgba(74,20,140,0.4);">
                    <i class="fas fa-plus me-2"></i>Tambah Pembelian
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search and Filter Card -->
    <div class="search-card mb-4">
        <div class="card-header">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('pegawai-gudang.pembelian.index') }}" class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control form-control-modern" 
                               placeholder="Cari pembelian..." 
                               value="{{ request('search') }}"
                               style="color: #333 !important; font-weight: 500;">
                        <button class="btn btn-primary-modern btn-action" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="{{ route('pegawai-gudang.pembelian.index') }}">
                        <select name="payment_method" class="form-select form-control-modern" onchange="this.form.submit()">
                            <option value="">Semua Pembayaran</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Kredit</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-5">
                    <div class="stats-card">
                        <div class="row text-center">
                            <div class="col-4">
                                <h5 class="mb-1" style="color: #ff6b6b; font-weight: 800; text-shadow: 0 2px 4px rgba(255,107,107,0.3);">{{ $pembelians->total() }}</h5>
                                <small style="color: #ff6b6b; font-weight: 600;">Total</small>
                            </div>
                            <div class="col-4">
                                <h5 class="mb-1" style="color: #4ecdc4; font-weight: 800; text-shadow: 0 2px 4px rgba(78,205,196,0.3);">
                                    {{ $pembelians->where('payment_method', '!=', 'credit')->count() }}
                                </h5>
                                <small style="color: #4ecdc4; font-weight: 600;">Lunas</small>
                            </div>
                            <div class="col-4">
                                <h5 class="mb-1" style="color: #ffa726; font-weight: 800; text-shadow: 0 2px 4px rgba(255,167,38,0.3);">
                                    {{ $pembelians->where('payment_method', 'credit')->count() }}
                                </h5>
                                <small style="color: #ffa726; font-weight: 600;">Kredit</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Data Table -->
    <div class="modern-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px">#</th>
                        <th style="width: 140px">Nomor Transaksi</th>
                        <th style="width: 100px">Tanggal</th>
                        <th style="width: 150px">Vendor</th>
                        <th style="width: 250px">Item Dibeli</th>
                        <th style="width: 100px">Pembayaran</th>
                        <th style="width: 130px">Total Harga</th>
                        <th style="width: 180px">Retur</th>
                        <th style="width: 180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $index => $pembelian)
                    <tr>
                        <td class="text-center">
                            <span class="badge badge-modern" style="background: linear-gradient(45deg, #6c757d, #495057); color: white;">
                                {{ ($pembelians->currentPage() - 1) * $pembelians->perPage() + $loop->iteration }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-modern" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white !important; font-weight: 600; padding: 0.6rem 1rem;">
                                {{ $pembelian->nomor_pembelian ?? 'PB-' . str_pad($pembelian->id, 6, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-muted me-2"></i>
                                <span class="fw-medium" style="color: #000 !important;">{{ $pembelian->tanggal->format('d-m-Y') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-building text-white"></i>
                                </div>
                                <span class="fw-medium" style="color: #000 !important;">{{ $pembelian->vendor->nama_vendor ?? '-' }}</span>
                            </div>
                        </td>
                        <td style="max-width: 250px;">
                            @if($pembelian->details && $pembelian->details->count() > 0)
                                <div style="max-height: 120px; overflow-y: auto; font-size: 0.8em;">
                                @foreach($pembelian->details as $detail)
                                    @php
                                        $namaItem = '-';
                                        $satuanNama = $detail->satuan ?? 'unit';
                                        if ($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung) {
                                            $namaItem = $detail->bahanPendukung->nama_bahan;
                                            if ($detail->bahanPendukung->satuanRelation) {
                                                $satuanNama = $detail->bahanPendukung->satuanRelation->nama ?? $detail->bahanPendukung->satuanRelation->kode ?? $satuanNama;
                                            }
                                        } elseif ($detail->bahanBaku) {
                                            $namaItem = $detail->bahanBaku->nama_bahan;
                                            if ($detail->bahanBaku->satuanRelation) {
                                                $satuanNama = $detail->bahanBaku->satuanRelation->nama ?? $detail->bahanBaku->satuanRelation->kode ?? $satuanNama;
                                            }
                                        }
                                    @endphp
                                    <div class="item-card mb-1 p-1" style="font-size: 0.75em;">
                                        @if($detail->tipe_item === 'bahan_pendukung')
                                            <span class="badge badge-bp" style="font-size: 0.6em;">
                                                <i class="fas fa-tools me-1"></i>BP
                                            </span>
                                        @else
                                            <span class="badge badge-bb" style="font-size: 0.6em;">
                                                <i class="fas fa-boxes me-1"></i>BB
                                            </span>
                                        @endif
                                        <span class="fw-medium ms-1" style="word-break: break-word; color: #000 !important;">{{ Str::limit($namaItem, 20) }}</span>
                                        <div class="text-muted" style="font-size: 0.7em; line-height: 1.1; color: #000 !important;">
                                            {{ number_format($detail->jumlah ?? 0, 0, ',', '.') }} {{ $satuanNama }} × 
                                            Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            @else
                                <div class="text-muted text-center py-2" style="font-size: 0.8em;">
                                    <i class="fas fa-inbox me-2"></i>Tidak ada item
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-modern {{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'bg-warning text-dark' : 'bg-success' }}">
                                <i class="fas {{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'fa-credit-card' : 'fa-money-bill' }} me-1"></i>
                                {{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $totalPembelian = $pembelian->total;
                                if ($totalPembelian == 0 && $pembelian->details && $pembelian->details->count() > 0) {
                                    $totalPembelian = $pembelian->details->sum(function($detail) {
                                        return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                    });
                                }
                            @endphp
                            <div class="text-center">
                                <h6 class="text-success mb-0" style="color: #000 !important;">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Rp {{ number_format($totalPembelian, 0, ',', '.') }}
                                </h6>
                            </div>
                        </td>
                        <td>
                            @php
                                $returCount = \App\Models\Retur::where('type', 'purchase')
                                    ->where('pembelian_id', $pembelian->id)
                                    ->count();
                                $totalRetur = \App\Models\Retur::where('type', 'purchase')
                                    ->where('pembelian_id', $pembelian->id)
                                    ->sum('jumlah');
                            @endphp
                            @if($returCount > 0)
                                <div class="retur-info">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-undo me-1"></i>{{ $returCount }} Retur
                                        </span>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="color: #000 !important;">
                                        <i class="fas fa-money-bill me-1"></i>
                                        Rp {{ number_format($totalRetur, 0, ',', '.') }}
                                    </small>
                                </div>
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-minus-circle me-1"></i>
                                    <small style="color: #000 !important;">Tidak Ada</small>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('pegawai-gudang.pembelian.show', $pembelian->id) }}" 
                                   class="btn btn-info-modern btn-action btn-sm" 
                                   data-bs-toggle="tooltip" 
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-success-modern btn-action btn-sm" 
                                        onclick="window.print()" 
                                        data-bs-toggle="tooltip" 
                                        title="Cetak">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <h5 class="mt-3 mb-2">Belum Ada Data Pembelian</h5>
                                <p class="text-muted mb-4">Mulai tambahkan pembelian bahan baku dan pendukung untuk melihat data di sini</p>
                                <a href="{{ route('pegawai-gudang.pembelian.create') }}" class="btn btn-action" style="background: linear-gradient(45deg, #4a148c, #6a1b9a); color: white !important; font-weight: 600; border: none; box-shadow: 0 5px 15px rgba(74,20,140,0.4);">
                                    <i class="fas fa-plus me-2"></i>Tambah Pembelian Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pembelians->hasPages())
        <div class="card-footer bg-white border-top-0" style="border-radius: 0 0 15px 15px;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    @if($pembelians->total() > 0)
                        Menampilkan {{ ($pembelians->currentPage() - 1) * $pembelians->perPage() + 1 }} - 
                        {{ min($pembelians->currentPage() * $pembelians->perPage(), $pembelians->total()) }} 
                        dari {{ $pembelians->total() }} data
                    @else
                        Tidak ada data yang ditemukan
                    @endif
                </div>
                <div class="pagination-modern">
                    {{ $pembelians->withQueryString()->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto close alert after 5 seconds
    setTimeout(function() {
        var alert = document.querySelector('.alert');
        if (alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
    
    // Add loading animation to table rows
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            if (!row.querySelector('.empty-state')) {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    });
    
    // Enhanced search functionality
    document.querySelector('input[name="search"]').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            if (!row.querySelector('.empty-state')) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    row.style.animation = 'fadeIn 0.3s ease';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .avatar-sm {
        width: 32px;
        height: 32px;
    }
</style>
@endpush

@endsection
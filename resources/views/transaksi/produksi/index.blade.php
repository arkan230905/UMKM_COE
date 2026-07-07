@extends('layouts.app')

@section('title', 'Transaksi Produksi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-industry me-2"></i>Transaksi Produksi
        </h2>
        <a href="{{ route('transaksi.produksi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Data Produksi Produk
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="produksiTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="data-produk-tab" data-bs-toggle="tab" data-bs-target="#data-produk" type="button" role="tab">
                <i class="fas fa-box me-2"></i>Data Produk
                <span class="badge bg-primary ms-2">{{ $dataProduk->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="riwayat-produksi-tab" data-bs-toggle="tab" data-bs-target="#riwayat-produksi" type="button" role="tab">
                <i class="fas fa-history me-2"></i>Riwayat Produksi
                <span class="badge bg-secondary ms-2">{{ $produksis->total() }}</span>
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="produksiTabsContent">
        
        <!-- TAB 1: DATA PRODUK -->
        <div class="tab-pane fade show active" id="data-produk" role="tabpanel">
            <div class="card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #6d4c41 0%, #4e342e 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>Data Produk Siap Produksi
                    </h5>
                    <small>Produk yang sudah setup HPP dan siap untuk diproduksi</small>
                </div>
                <div class="card-body">
                    @if($dataProduk->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Belum ada produk yang setup untuk produksi</p>
                            <a href="{{ route('transaksi.produksi.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Data Produksi Produk
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px">NO</th>
                                        <th>Produk</th>
                                        <th>Terakhir Dibuat</th>
                                        <th class="text-end">Produksi Bulanan</th>
                                        <th class="text-center">Hari Kerja</th>
                                        <th class="text-end">Qty Per Hari</th>
                                        <th class="text-end">Total Biaya/Hari</th>
                                        <th class="text-center">Stok Tersedia</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataProduk as $key => $data)
                                        @php
                                            // Check stock availability using REAL-TIME stock
                                            $stockSufficient = true;
                                            $shortageMessages = [];
                                            
                                            if ($data->lastTemplate) {
                                                foreach ($data->lastTemplate->details as $detail) {
                                                    if ($detail->bahanBaku) {
                                                        $bahan = $detail->bahanBaku;
                                                        $qtyNeeded = $detail->qty_resep;
                                                        
                                                        // PERBAIKAN: Gunakan stok_real_time untuk bahan baku
                                                        $available = (float)$bahan->stok_real_time;
                                                        
                                                        $satuanResep = $detail->satuan_resep;
                                                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                                                        
                                                        // Convert qty needed ke satuan bahan jika berbeda
                                                        if ($satuanResep !== $satuanBahan) {
                                                            $qtyNeeded = $bahan->konversiBerdasarkanProduksi($qtyNeeded, $satuanResep, $satuanBahan);
                                                        }
                                                        
                                                        if ($available < $qtyNeeded) {
                                                            $stockSufficient = false;
                                                            $shortageMessages[] = "{$bahan->nama_bahan}: butuh " . number_format($qtyNeeded, 2) . " {$satuanBahan}, tersedia " . number_format($available, 2) . " {$satuanBahan}";
                                                        }
                                                    }
                                                    
                                                    if ($detail->bahanPendukung) {
                                                        $bahan = $detail->bahanPendukung;
                                                        
                                                        // BAHAN PENDUKUNG: Gunakan NOMINAL (Rupiah) untuk validasi karena masuk BOP Proses
                                                        $nominalNeeded = (float)$detail->subtotal;
                                                        
                                                        // Get nominal tersedia from stock movements (same as Laporan Stok)
                                                        $userId = auth()->id();
                                                        $totalCostIn = DB::table('stock_movements')
                                                            ->where('item_type', 'support')
                                                            ->where('item_id', $bahan->id)
                                                            ->where('user_id', $userId)
                                                            ->where('direction', 'in')
                                                            ->sum('total_cost');
                                                        
                                                        $totalCostOut = DB::table('stock_movements')
                                                            ->where('item_type', 'support')
                                                            ->where('item_id', $bahan->id)
                                                            ->where('user_id', $userId)
                                                            ->where('direction', 'out')
                                                            ->sum('total_cost');
                                                        
                                                        $nominalAvailable = $totalCostIn - $totalCostOut;
                                                        
                                                        // Calculate qty for display: Qty = Nominal / Harga Satuan
                                                        $hargaSatuan = $bahan->harga_satuan ?? 1;
                                                        $qtyAvailable = $hargaSatuan > 0 ? ($nominalAvailable / $hargaSatuan) : 0;
                                                        $qtyNeeded = $hargaSatuan > 0 ? ($nominalNeeded / $hargaSatuan) : 0;
                                                        $satuanBahan = $bahan->satuan->nama ?? 'unit';
                                                        
                                                        // Validate based on NOMINAL (Rp)
                                                        if ($nominalAvailable < $nominalNeeded) {
                                                            $stockSufficient = false;
                                                            $shortageMessages[] = "{$bahan->nama_bahan} (Pendukung): butuh " . number_format($qtyNeeded, 2) . " {$satuanBahan} (Rp " . number_format($nominalNeeded, 0, ',', '.') . "), tersedia " . number_format($qtyAvailable, 2) . " {$satuanBahan} (Rp " . number_format($nominalAvailable, 0, ',', '.') . ")";
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <strong>{{ $data->produk->nama_produk }}</strong>
                                                @if($data->lastTemplate)
                                                    <br><small class="text-muted">Template ID: #{{ $data->lastTemplate->id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($data->lastTemplate)
                                                    {{ \Carbon\Carbon::parse($data->lastTemplate->created_at)->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ number_format($data->jumlah_produksi_bulanan ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">{{ $data->hari_produksi_bulanan ?? '-' }} hari</td>
                                            <td class="text-end">{{ number_format($data->qty_per_hari, 2, ',', '.') }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format($data->total_biaya_per_hari, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($stockSufficient)
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Cukup</span>
                                                @else
                                                    <span class="badge bg-danger" 
                                                          data-bs-toggle="tooltip" 
                                                          title="{{ implode(', ', $shortageMessages) }}">
                                                        <i class="fas fa-times"></i> Kurang
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($data->lastTemplate)
                                                    <!-- Button untuk lihat detail template -->
                                                    <a href="{{ route('transaksi.produksi.show', $data->lastTemplate->id) }}" 
                                                       class="btn btn-sm btn-outline-info me-1"
                                                       data-bs-toggle="tooltip" title="Lihat Detail Template">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <!-- Button Mulai Produksi Produk jika stok cukup -->
                                                    @if($stockSufficient)
                                                        <form action="{{ route('transaksi.produksi.mulai-hari-ini') }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <input type="hidden" name="template_id" value="{{ $data->lastTemplate->id }}">
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-success"
                                                                    onclick="return confirm('Mulai produksi {{ $data->produk->nama_produk }} hari ini dengan qty {{ number_format($data->qty_per_hari, 2) }}?')"
                                                                    data-bs-toggle="tooltip" title="Mulai Produksi Produk Hari Ini">
                                                                <i class="fas fa-play"></i> Mulai Produksi Produk
                                                            </button>
                                                        </form>
                                                    @else
                                                        <!-- Button disabled dengan alert stok kurang -->
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger" 
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#stockShortageModal{{ $data->produk_id }}"
                                                                title="Klik untuk detail bahan yang kurang">
                                                            <i class="fas fa-exclamation-triangle"></i> Stok Bahan Kurang
                                                        </button>
                                                        
                                                        <!-- Modal untuk detail kekurangan stok -->
                                                        <div class="modal fade" id="stockShortageModal{{ $data->produk_id }}" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-danger text-white">
                                                                        <h5 class="modal-title">
                                                                            <i class="fas fa-exclamation-triangle me-2"></i>Stok Bahan Tidak Cukup
                                                                        </h5>
                                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p class="mb-3">Produk <strong>{{ $data->produk->nama_produk }}</strong> tidak dapat diproduksi karena bahan berikut tidak mencukupi:</p>
                                                                        <ul class="list-group">
                                                                            @foreach($shortageMessages as $message)
                                                                                <li class="list-group-item list-group-item-danger">
                                                                                    <i class="fas fa-times-circle me-2"></i>{{ $message }}
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                        <div class="alert alert-warning mt-3 mb-0">
                                                                            <i class="fas fa-info-circle me-2"></i>
                                                                            Silakan lakukan pembelian bahan atau sesuaikan jumlah produksi.
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                        <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-primary">
                                                                            <i class="fas fa-shopping-cart me-2"></i>Beli Bahan
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No template</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- TAB 2: RIWAYAT PRODUKSI -->
        <div class="tab-pane fade" id="riwayat-produksi" role="tabpanel">
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filter Riwayat
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('transaksi.produksi.index') }}" id="filterForm">
                        <input type="hidden" name="tab" value="riwayat">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" 
                                       value="{{ request('tanggal_mulai') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" 
                                       value="{{ request('tanggal_selesai') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Produk</label>
                                <select name="produk_id" class="form-select">
                                    <option value="">Semua Produk</option>
                                    @foreach($produks ?? [] as $produk)
                                        <option value="{{ $produk->id }}" {{ request('produk_id') == $produk->id ? 'selected' : '' }}>
                                            {{ $produk->nama_produk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="dalam_proses" {{ request('status') == 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                    <a href="{{ route('transaksi.produksi.index') }}?tab=riwayat" class="btn btn-outline-secondary">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Riwayat Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Daftar Riwayat Produksi
                        @if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'produk_id', 'status']))
                            <small class="text-muted">(Filter Aktif)</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($produksis->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada riwayat produksi</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px">NO</th>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th class="text-center">Qty Produksi</th>
                                        <th class="text-end">Total Biaya</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produksis as $key => $p)
                                        <tr>
                                            <td>{{ $produksis->firstItem() + $key }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}
                                                @if($p->tanggal_mulai)
                                                    <br><small class="text-muted">Mulai: {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y H:i') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $p->produk->nama_produk }}</strong>
                                            </td>
                                            <td class="text-center">{{ number_format($p->qty_produksi, 0, ',', '.') }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format($p->total_biaya, 0, ',', '.') }}</td>
                                            <td>
                                                @if($p->status === 'draft')
                                                    <span class="badge bg-info">Siap Produksi</span>
                                                @elseif($p->status === 'dalam_proses')
                                                    <span class="badge bg-primary">Dalam Proses</span>
                                                    @if($p->proses_selesai && $p->total_proses)
                                                        <br><small class="text-info">{{ $p->proses_selesai }}/{{ $p->total_proses }} proses</small>
                                                    @endif
                                                @elseif($p->status === 'selesai')
                                                    <span class="badge bg-success">Selesai</span>
                                                    @if($p->tanggal_selesai)
                                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">{{ $p->status }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('transaksi.produksi.show', $p->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                
                                                @if($p->status === 'draft')
                                                    <form action="{{ route('transaksi.produksi.mulai-produksi', $p->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mulai produksi untuk {{ $p->produk->nama_produk }}?')">
                                                            <i class="fas fa-play"></i> Mulai
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($p->status === 'dalam_proses')
                                                    <a href="{{ route('transaksi.produksi.proses', $p->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-tasks"></i> Kelola
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if(!$produksis->isEmpty())
                    <div class="card-footer">
                        {{ $produksis->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Handle tab switching from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab === 'riwayat') {
        const riwayatTab = document.getElementById('riwayat-produksi-tab');
        const riwayatPane = document.getElementById('riwayat-produksi');
        const dataProdukTab = document.getElementById('data-produk-tab');
        const dataProdukPane = document.getElementById('data-produk');
        
        if (riwayatTab && riwayatPane) {
            dataProdukTab.classList.remove('active');
            dataProdukPane.classList.remove('show', 'active');
            riwayatTab.classList.add('active');
            riwayatPane.classList.add('show', 'active');
        }
    }
});
</script>
@endpush

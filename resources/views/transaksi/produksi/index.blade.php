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

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.produksi.index') }}">
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
                            <option value="siap_produksi" {{ request('status') == 'siap_produksi' ? 'selected' : '' }}>Siap Produksi</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="wip" {{ request('status') == 'wip' ? 'selected' : '' }}>Proses</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Start Production Again Section -->
    @php
        $completedProductions = $produksis->filter(function($p) {
            return $p->status === 'selesai';
        });
        $completedProductIds = $completedProductions->pluck('produk_id')->unique();
        $availableProducts = \App\Models\Produk::whereIn('id', $completedProductIds)
            ->whereHas('boms', function($query) {
                $query->has('details');
            })->get();
    @endphp

    @if($availableProducts->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-redo me-2"></i>Mulai Produksi Lagi Hari Ini
            </h6>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <p class="text-muted mb-3">Pilih produk yang sudah pernah diproduksi untuk memulai produksi baru dengan data yang sama:</p>
            
            <form action="{{ route('transaksi.produksi.mulai-lagi') }}" method="POST" class="row g-3" id="mulaiLagiForm">
                @csrf
                <div class="col-md-8">
                    <label class="form-label">Produk</label>
                    <select name="produk_id" class="form-select" required>
                        <option value="">Pilih Produk</option>
                        @foreach($availableProducts as $produk)
                            @php
                                $lastProduction = $completedProductions->where('produk_id', $produk->id)->sortByDesc('tanggal')->first();
                            @endphp
                            <option value="{{ $produk->id }}" data-qty="{{ $lastProduction->qty_produksi ?? 0 }}" data-bulanan="{{ $lastProduction->jumlah_produksi_bulanan ?? 0 }}" data-hari="{{ $lastProduction->hari_produksi_bulanan ?? 0 }}">
                                {{ $produk->nama_produk }} 
                                @if($lastProduction)
                                    (Terakhir: {{ number_format($lastProduction->qty_produksi, 2) }} pcs)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-success d-block w-100">
                        <i class="fas fa-play me-2"></i>Mulai Produksi Lagi
                    </button>
                </div>
            </form>
            
            <div id="productionInfo" class="mt-3" style="display: none;">
                <div class="alert alert-info">
                    <strong>Data Produksi:</strong>
                    <div class="row">
                        <div class="col-md-4">Produksi Bulanan: <span id="infoBulanan">-</span></div>
                        <div class="col-md-4">Hari Kerja: <span id="infoHari">-</span> hari</div>
                        <div class="col-md-4">Qty Hari Ini: <span id="infoQty">-</span> pcs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Produksi
                @if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'produk_id', 'status']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th class="text-end">Produksi Bulanan</th>
                            <th class="text-center">Hari Kerja</th>
                            <th class="text-end">Qty Per Hari</th>
                            <th class="text-end">Total Biaya</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produksis as $key => $p)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $p->produk->nama_produk }}</td>
                                <td class="text-end">{{ number_format($p->jumlah_produksi_bulanan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $p->hari_produksi_bulanan ?? '-' }} hari</td>
                                <td class="text-end">{{ number_format($p->qty_produksi, 2, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($p->total_biaya, 0, ',', '.') }}</td>
                                <td>
                                    @if($p->status === 'draft')
                                        <span class="badge bg-info">Siap Produksi</span>
                                    @elseif($p->status === 'dalam_proses')
                                        <span class="badge bg-primary">Dalam Proses</span>
                                        @if($p->proses_saat_ini)
                                            <br><small class="text-muted">{{ $p->proses_saat_ini }}</small>
                                            <br><small class="text-info">{{ $p->proses_selesai }}/{{ $p->total_proses }} proses</small>
                                        @endif
                                    @elseif($p->status === 'selesai')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($p->status === 'draft')
                                        <span class="badge bg-secondary">Draft</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $p->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('transaksi.produksi.show', $p->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    
                                    @if($p->status === 'draft')
                                        @php
                                            // Check stock availability
                                            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $p->produk_id)->first();
                                            $stockSufficient = true;
                                            $shortageMessages = [];
                                            
                                            if ($bomJobCosting) {
                                                // Check bahan baku
                                                $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
                                                foreach ($bomJobBBBs as $bomJobBBB) {
                                                    $bahan = $bomJobBBB->bahanBaku;
                                                    if ($bahan) {
                                                        $qtyResepTotal = $bomJobBBB->jumlah * $p->qty_produksi;
                                                        $satuanResep = $bomJobBBB->satuan ?? $bahan->satuan->nama ?? $bahan->satuan;
                                                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                                                        
                                                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                                                        $available = (float)($bahan->stok ?? 0);
                                                        
                                                        if ($available < $qtyBase) {
                                                            $stockSufficient = false;
                                                            $shortageMessages[] = "{$bahan->nama_bahan}: butuh " . number_format($qtyBase, 2) . " {$satuanBahan}, tersedia " . number_format($available, 2);
                                                        }
                                                    }
                                                }
                                                
                                                // Check bahan pendukung
                                                $bomJobBahanPendukungs = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
                                                foreach ($bomJobBahanPendukungs as $bomJobBahanPendukung) {
                                                    $bahan = $bomJobBahanPendukung->bahanPendukung;
                                                    if ($bahan) {
                                                        $qtyResepTotal = $bomJobBahanPendukung->jumlah * $p->qty_produksi;
                                                        $satuanResep = $bomJobBahanPendukung->satuan ?? $bahan->satuan->nama ?? $bahan->satuan;
                                                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                                                        
                                                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                                                        $available = 200; // Fixed stock for bahan pendukung
                                                        
                                                        if ($available < $qtyBase) {
                                                            $stockSufficient = false;
                                                            $shortageMessages[] = "{$bahan->nama_bahan}: butuh " . number_format($qtyBase, 2) . " {$satuanBahan}, tersedia " . number_format($available, 2);
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        @if($stockSufficient)
                                            <form action="{{ route('transaksi.produksi.mulai-produksi', $p->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mulai produksi untuk {{ $p->produk->nama_produk }}?')">
                                                    <i class="fas fa-play"></i> Mulai Produksi
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-danger" disabled 
                                                    title="Stok tidak cukup: {{ implode(', ', $shortageMessages) }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                                <i class="fas fa-exclamation-triangle"></i> Stok Kurang
                                            </button>
                                        @endif
                                    @endif
                                    
                                    @if($p->status === 'dalam_proses')
                                        <a href="{{ route('transaksi.produksi.proses', $p->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-tasks"></i> Kelola Proses
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                {{ $produksis->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Show production info when product is selected
    const produkSelect = document.querySelector('#mulaiLagiForm select[name="produk_id"]');
    const productionInfo = document.getElementById('productionInfo');
    const infoBulanan = document.getElementById('infoBulanan');
    const infoHari = document.getElementById('infoHari');
    const infoQty = document.getElementById('infoQty');
    
    if (produkSelect && productionInfo) {
        produkSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const qty = selectedOption.dataset.qty;
                const bulanan = selectedOption.dataset.bulanan;
                const hari = selectedOption.dataset.hari;
                
                infoBulanan.textContent = parseFloat(bulanan).toLocaleString('id-ID');
                infoHari.textContent = hari;
                infoQty.textContent = parseFloat(qty).toLocaleString('id-ID');
                
                productionInfo.style.display = 'block';
            } else {
                productionInfo.style.display = 'none';
            }
        });
    }
});
</script>
@endpush

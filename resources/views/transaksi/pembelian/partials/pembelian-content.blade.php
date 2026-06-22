<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Transaksi
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('transaksi.pembelian.index') }}">
            <input type="hidden" name="tab" value="pembelian">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">No Transaksi</label>
                    <input type="text" name="nomor_transaksi" class="form-control" 
                           value="{{ request('nomor_transaksi') }}" placeholder="Cari nomor transaksi...">
                </div>
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
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">Semua Vendor</option>
                        @foreach($vendors ?? [] as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->nama_vendor }} ({{ $vendor->kategori }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="belum_lunas" {{ request('status_pembayaran') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                        <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                <span>Riwayat Pembelian</span>
                @if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'payment_method', 'status', 'status_pembayaran']))
                    <small class="text-muted ms-3">(Filter Aktif)</small>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 1200px;">
                <thead class="table-light">
                    <tr>

                        <th class="text-center" style="width: 50px">No</th>
                        <th class="nowrap sortable" data-sort="nomor_pembelian" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>No. Transaksi</span>
                                <span class="sort-icon ms-2">
                                    @if(request('sort_by') == 'nomor_pembelian')
                                        @if(request('sort_order') == 'asc')
                                            <i class="fas fa-sort-up text-primary"></i>
                                        @else
                                            <i class="fas fa-sort-down text-primary"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="nowrap sortable" data-sort="nomor_faktur" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>No. Faktur</span>
                                <span class="sort-icon ms-2">
                                    @if(request('sort_by') == 'nomor_faktur')
                                        @if(request('sort_order') == 'asc')
                                            <i class="fas fa-sort-up text-primary"></i>
                                        @else
                                            <i class="fas fa-sort-down text-primary"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="nowrap">Bukti Faktur</th>
                        <th class="nowrap sortable" data-sort="tanggal" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Tanggal</span>
                                <span class="sort-icon ms-2">
                                    @if(request('sort_by') == 'tanggal')
                                        @if(request('sort_order') == 'asc')
                                            <i class="fas fa-sort-up text-primary"></i>
                                        @else
                                            <i class="fas fa-sort-down text-primary"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="nowrap">Vendor</th>
                        <th class="nowrap">Item</th>
                        <th class="nowrap">Satuan Pembelian</th>
                        <th class="nowrap sortable" data-sort="payment_method" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Pembayaran</span>
                                <span class="sort-icon ms-2">
                                    @if(request('sort_by') == 'payment_method')
                                        @if(request('sort_order') == 'asc')
                                            <i class="fas fa-sort-up text-primary"></i>
                                        @else
                                            <i class="fas fa-sort-down text-primary"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="nowrap sortable" data-sort="status_pembayaran" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Status Pembayaran</span>
                                <span class="sort-icon ms-2">
                                    @if(request('sort_by') == 'status_pembayaran')
                                        @if(request('sort_order') == 'asc')
                                            <i class="fas fa-sort-up text-primary"></i>
                                        @else
                                            <i class="fas fa-sort-down text-primary"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="nowrap sortable" data-sort="total_harga" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Total Harga</span>
                                <span class="sort-icon ms-2">
                                    @if(request('sort_by') == 'total_harga')
                                        @if(request('sort_order') == 'asc')
                                            <i class="fas fa-sort-up text-primary"></i>
                                        @else
                                            <i class="fas fa-sort-down text-primary"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <!-- STATUS RETUR COLUMN - HIDDEN (feature disabled but code preserved) -->
                        <th class="nowrap" style="display: none;">Status Retur</th>
                        <th class="text-center" style="width: 180px">Aksi</th>
</tr>
                </thead>
                <tbody>
                    @forelse ($pembelians as $key => $pembelian)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-center nowrap" style="color: #000; font-weight: bold;">{{ $pembelian->nomor_pembelian ?? 'KOSONG' }}</td>
                            <td class="text-center nowrap">
                                @if($pembelian->nomor_faktur)
                                    {{ $pembelian->nomor_faktur }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="nowrap text-center">
                                @if($pembelian->bukti_faktur)
                                    @php
                                        // Extract ID and filename from bukti_faktur path
                                        // Format: bukti_faktur/{id}/{filename}
                                        $parts = explode('/', $pembelian->bukti_faktur);
                                        $userId = $parts[1] ?? '';
                                        $filename = $parts[2] ?? '';
                                    @endphp
                                    <a href="{{ url('/storage/' . $pembelian->bukti_faktur) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Bukti Faktur">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="nowrap">{{ $pembelian->tanggal->format('d-m-Y') }}</td>
                            <td class="nowrap">
                                <div class="d-flex align-items-center">
<div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-store text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center nowrap">
                                @if($pembelian->details && $pembelian->details->count() > 0)
                                    @foreach($pembelian->details as $detail)
                                        <div class="mb-1">
                                            @if($detail->bahan_baku_id && $detail->bahanBaku)
                                                BB - {{ $detail->bahanBaku->nama_bahan }}
                                            @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                                BP - {{ $detail->bahanPendukung->nama_bahan }}
                                            @else
                                                <span class="text-muted">Item tidak diketahui</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center nowrap">
                                @if($pembelian->details && $pembelian->details->count() > 0)
                                    <small>
                                    @foreach($pembelian->details as $detail)
                                        <div>
                                            {{ $detail->satuan_nama }}
                                        </div>
                                    @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center nowrap">
                                @php
                                    $paymentMethod = $pembelian->payment_method ?? 'cash';
                                    if ($paymentMethod === 'credit') {
                                        $paymentText = 'Kredit';
                                    } elseif ($paymentMethod === 'transfer') {
                                        $paymentText = 'Transfer';
                                    } else {
                                        $paymentText = 'Tunai';
                                    }
                                @endphp
                                {{ $paymentText }}
                            </td>
                            <td class="text-center nowrap">
                                @php
                                    $statusPembayaran = $pembelian->status_pembayaran;
                                @endphp
                                @if($statusPembayaran === 'Lunas')
                                    <span class="text-success fw-semibold">Lunas</span>
                                @else
                                    <span class="text-warning fw-semibold">Belum Lunas</span>
                                @endif
                            </td>
                            <td class="text-center nowrap fw-semibold">
                                Rp {{ number_format($pembelian->total_harga ?? 0, 0, ',', '.') }}
                            </td>
                            <!-- STATUS RETUR CELL - HIDDEN (feature disabled but code preserved) -->
                            <td class="text-center nowrap" style="display: none;">
                                @php
                                    // Cek apakah ada retur untuk pembelian ini
                                    $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $pembelian->id)->exists();
                                @endphp
                                @if($hasRetur)
                                    Ada Retur
                                @else
                                    Tidak Ada Retur
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-grid" style="grid-template-columns: repeat(2, 1fr); gap: 5px;">
                                    <!-- Row 1: Detail | Edit -->
                                    <a href="{{ route('transaksi.pembelian.show', $pembelian->id) }}" class="btn btn-sm btn-outline-success w-100" title="Detail Transaksi">
                                        Detail
                                    </a>
                                    <a href="{{ route('transaksi.pembelian.edit', $pembelian->id) }}" class="btn btn-sm btn-outline-warning w-100" title="Edit Transaksi">
                                        Edit
                                    </a>
                                    
                                    <!-- Row 2: Jurnal | Retur (HIDDEN) -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary w-100" 
                                            title="Lihat Jurnal"
                                            onclick="loadJournal({{ $pembelian->id }}, '{{ $pembelian->nomor_pembelian }}')"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#journalModal">
                                        Jurnal
                                    </button>
                                    <!-- RETUR BUTTON - HIDDEN (feature disabled but code preserved) -->
                                    <a href="{{ route('transaksi.retur-pembelian.create', ['pembelian_id' => $pembelian->id]) }}" class="btn btn-sm btn-outline-info w-100" title="Proses Retur" style="display: none;">
                                        Retur
                                    </a>
                                    
                                    <!-- Row 3: Cetak -->
                                    <a href="{{ route('transaksi.pembelian.preview-faktur', $pembelian->id) }}" class="btn btn-sm btn-outline-info w-100" title="Cetak Faktur" target="_blank">
                                        Cetak
                                    </a>
                                    
                                    <!-- HAPUS BUTTON - HIDDEN (feature disabled but code preserved) -->
                                    <form action="{{ route('transaksi.pembelian.destroy', $pembelian->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('Yakin ingin hapus?')" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Hapus Transaksi">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data pembelian</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle sortable column clicks
    const sortableHeaders = document.querySelectorAll('.sortable');
    
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.dataset.sort;
            const currentSortBy = '{{ request("sort_by") }}';
            const currentSortOrder = '{{ request("sort_order", "asc") }}';
            
            // Determine new sort order
            let newSortOrder = 'asc';
            if (sortBy === currentSortBy) {
                // Toggle sort order if clicking the same column
                newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            }
            
            // Build URL with all current filters
            const url = new URL(window.location.href);
            url.searchParams.set('sort_by', sortBy);
            url.searchParams.set('sort_order', newSortOrder);
            
            // Redirect to new URL
            window.location.href = url.toString();
        });
        
        // Add hover effect
        header.style.transition = 'background-color 0.2s';
        header.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        header.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.sortable {
    user-select: none;
    position: relative;
}

.sortable:hover {
    background-color: #f8f9fa !important;
}

.sortable .sort-icon {
    display: inline-block;
    min-width: 15px;
    text-align: center;
}

.sortable .sort-icon i {
    font-size: 0.875rem;
}

.sortable .sort-icon .fa-sort {
    opacity: 0.3;
}

.sortable:hover .sort-icon .fa-sort {
    opacity: 0.6;
}

.sortable .sort-icon .fa-sort-up,
.sortable .sort-icon .fa-sort-down {
    opacity: 1;
}
</style>
@endpush

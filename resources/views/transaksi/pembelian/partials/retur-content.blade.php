{{-- Retur Content untuk Tab di Halaman Pembelian --}}
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-undo me-2"></i>
                <span>Daftar Retur Pembelian</span>
                <small class="text-muted ms-2">({{ $returs->count() }} data)</small>
            </div>
            <div class="text-muted small">
                Last updated: {{ now()->format('H:i:s') }}
                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @include('transaksi.retur-pembelian.partials.retur-table', [
            'returs' => $returs,
            'showCreateButton' => false,
            'showTitle' => false,
            'tableClass' => 'table table-hover align-middle mb-0'
        ])
    </div>
</div>
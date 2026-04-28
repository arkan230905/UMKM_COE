{{-- Retur Content untuk Tab di Halaman Pembelian --}}
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <i class="fas fa-undo me-2"></i>
            <span>Daftar Retur Pembelian</span>
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
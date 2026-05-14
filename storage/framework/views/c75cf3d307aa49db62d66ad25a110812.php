
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <i class="fas fa-undo me-2"></i>
            <span>Daftar Retur Pembelian</span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php echo $__env->make('transaksi.retur-pembelian.partials.retur-table', [
            'returs' => $returs,
            'showCreateButton' => false,
            'showTitle' => false,
            'tableClass' => 'table table-hover align-middle mb-0'
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/pembelian/partials/retur-content.blade.php ENDPATH**/ ?>
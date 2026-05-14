<?php $__env->startSection('title', 'Detail Harga Pokok Produksi'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-eye me-2"></i>Detail Harga Pokok Produksi
        </h2>
        <div>
            <a href="<?php echo e(route('master-data.harga-pokok-produksi.index')); ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="<?php echo e(route('master-data.harga-pokok-produksi.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Hitung HPP Baru
            </a>
        </div>
    </div>


    <!-- Product Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-box me-2"></i>Informasi Produk
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Nama Produk:</strong><br>
                            <span class="text-primary fs-5"><?php echo e($produk->nama_produk); ?></span>
                        </div>
                        <div class="col-md-2">
                            <strong>Kode:</strong><br>
                            <?php echo e($produk->kode_produk ?? '-'); ?>

                        </div>
                        <div class="col-md-2">
                            <strong>Satuan:</strong><br>
                            <?php echo e($produk->satuan->nama ?? '-'); ?>

                        </div>
                        <div class="col-md-2">
                            <strong>Stok:</strong><br>
                            <?php echo e(number_format($produk->stok, 0, ',', '.')); ?>

                        </div>
                        <div class="col-md-3">
                            <strong>Harga Jual:</strong><br>
                            <span class="text-success fs-6">Rp <?php echo e(number_format($produk->harga_jual, 0, ',', '.')); ?></span>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HPP Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Ringkasan Harga Pokok Produksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-success mb-1">Rp <?php echo e(number_format($totalBbb, 0, ',', '.')); ?></h4>
                                <small class="text-muted">Biaya Bahan Baku</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-warning mb-1">Rp <?php echo e(number_format($totalBtkl, 0, ',', '.')); ?></h4>
                                <small class="text-muted">BTKL</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-danger mb-1">Rp <?php echo e(number_format($totalBop, 0, ',', '.')); ?></h4>
                                <small class="text-muted">BOP</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-primary mb-1">Rp <?php echo e(number_format($totalHpp, 0, ',', '.')); ?></h3>
                            <small class="text-muted"><strong>Total HPP</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Biaya Bahan Baku Detail -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cube me-2"></i>Detail Biaya Bahan Baku
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedBbb->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Bahan</th>
                                        <th>Jumlah</th>
                                        <th>Satuan</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedBbb; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $bbb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($index + 1); ?></td>
                                            <td>
                                                <strong><?php echo e($bbb->biayaBahanBaku->bahanBaku->nama_bahan ?? 'N/A'); ?></strong>
                                            </td>
                                            <td><?php echo e(number_format($bbb->biayaBahanBaku->jumlah ?? 0, 2, ',', '.')); ?></td>
                                            <td><?php echo e($bbb->biayaBahanBaku->satuan ?? '-'); ?></td>
                                            <td>Rp <?php echo e(number_format($bbb->biayaBahanBaku->harga_satuan ?? 0, 0, ',', '.')); ?></td>
                                            <td><strong class="text-success">Rp <?php echo e(number_format($bbb->biayaBahanBaku->subtotal ?? 0, 0, ',', '.')); ?></strong></td>
                                            <td><?php echo e($bbb->biayaBahanBaku->keterangan ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Biaya Bahan Baku:</th>
                                        <th class="text-success">Rp <?php echo e(number_format($totalBbb, 0, ',', '.')); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data biaya bahan baku</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BTKL Detail -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Detail BTKL (Biaya Tenaga Kerja Langsung)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedBtkl->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Proses</th>
                                        <th>Kode Proses</th>
                                        <th>Tarif per Jam</th>
                                        <th>Kapasitas per Jam</th>
                                        <th>Biaya per Produk</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedBtkl; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $btkl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $tarif = $btkl->prosesProduksi->tarif_btkl ?? 0;
                                            $kapasitas = $btkl->prosesProduksi->kapasitas_per_jam ?? 1;
                                            $biayaPerProduk = $kapasitas > 0 ? $tarif / $kapasitas : 0;
                                        ?>
                                        <tr>
                                            <td><?php echo e($index + 1); ?></td>
                                            <td><strong><?php echo e($btkl->prosesProduksi->nama_proses ?? 'N/A'); ?></strong></td>
                                            <td><?php echo e($btkl->prosesProduksi->kode_proses ?? '-'); ?></td>
                                            <td>Rp <?php echo e(number_format($tarif, 0, ',', '.')); ?></td>
                                            <td><?php echo e($kapasitas); ?> unit/jam</td>
                                            <td><strong class="text-warning">Rp <?php echo e(number_format($biayaPerProduk, 0, ',', '.')); ?></strong></td>
                                            <td><?php echo e($btkl->prosesProduksi->deskripsi ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total BTKL:</th>
                                        <th class="text-warning">Rp <?php echo e(number_format($totalBtkl, 0, ',', '.')); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data BTKL</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BOP Detail -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Detail BOP (Biaya Overhead Pabrik)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedBop->count() > 0): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedBop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $bop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                // komponen_bop might already be an array (Laravel casts it)
                                $komponenBop = $bop->bopProses->komponen_bop ?? [];
                                if (is_string($komponenBop)) {
                                    $komponenBop = json_decode($komponenBop, true) ?? [];
                                }
                                $bopName = $bop->bopProses->prosesProduksi->nama_proses ?? 'BOP Item';
                                $totalBopItem = $bop->bopProses->total_bop_per_produk ?? 0;
                            ?>
                            
                            <div class="card mb-3 border-danger">
                                <div class="card-header bg-danger bg-opacity-10">
                                    <h6 class="mb-0 text-danger">
                                        <i class="fas fa-industry me-2"></i><?php echo e($index + 1); ?>. <?php echo e($bopName); ?>

                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($komponenBop) && is_array($komponenBop)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="10%">No</th>
                                                        <th width="60%">Komponen BOP</th>
                                                        <th width="30%" class="text-end">Tarif per Jam</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $komponenBop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $komponen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $ratePerHour = $komponen['rate_per_hour'] ?? 0;
                                                        ?>
                                                        <tr>
                                                            <td><?php echo e($idx + 1); ?></td>
                                                            <td><?php echo e($komponen['component'] ?? 'Unknown'); ?></td>
                                                            <td class="text-end"><strong>Rp <?php echo e(number_format($ratePerHour, 0, ',', '.')); ?></strong></td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="2" class="text-end">Total BOP <?php echo e($bopName); ?>:</th>
                                                        <th class="text-end text-danger">Rp <?php echo e(number_format($totalBopItem, 0, ',', '.')); ?></th>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" class="text-muted small">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Kapasitas: <?php echo e($kapasitas); ?> unit/jam
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Tidak ada detail komponen BOP. Total BOP: <strong>Rp <?php echo e(number_format($totalBopItem, 0, ',', '.')); ?></strong>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <!-- Grand Total BOP -->
                        <div class="card border-danger">
                            <div class="card-body bg-danger bg-opacity-10">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-0 text-danger">
                                            <i class="fas fa-calculator me-2"></i>Total Keseluruhan BOP
                                        </h5>
                                        <small class="text-muted">Jumlah total dari semua komponen BOP</small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <h3 class="mb-0 text-danger fw-bold">Rp <?php echo e(number_format($totalBop, 0, ',', '.')); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data BOP</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="mb-3">Aksi</h5>
                    <a href="<?php echo e(route('master-data.harga-pokok-produksi.index')); ?>" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-list me-2"></i>Lihat Semua HPP
                    </a>
                    <a href="<?php echo e(route('master-data.harga-pokok-produksi.create')); ?>" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-2"></i>Hitung HPP Baru
                    </a>
                    <form action="<?php echo e(route('master-data.harga-pokok-produksi.destroy', $produk->id)); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data HPP ini?')">
                            <i class="fas fa-trash me-2"></i>Hapus HPP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-end {
    border-right: 1px solid #dee2e6 !important;
}

.table th {
    font-weight: 600;
    font-size: 0.9rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    font-weight: 600;
}

.table-responsive {
    border-radius: 0.375rem;
}

.btn {
    border-radius: 0.375rem;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/bom/show.blade.php ENDPATH**/ ?>
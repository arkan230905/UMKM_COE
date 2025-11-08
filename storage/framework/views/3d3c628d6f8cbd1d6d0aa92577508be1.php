<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Detail Produk</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5><?php echo e($produk->nama_produk); ?></h5>
                        <p class="text-muted"><?php echo e($produk->deskripsi); ?></p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Margin:</strong> <?php echo e($produk->margin_percent); ?>%</p>
                            <p><strong>Metode BOPB:</strong> <?php echo e($produk->bopb_method); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rate BOPB:</strong> <?php echo e(number_format($produk->bopb_rate, 0, ',', '.')); ?></p>
                            <p><strong>BTKL per Unit:</strong> <?php echo e(number_format($produk->btkl_per_unit, 0, ',', '.')); ?></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Daftar BOM</h5>
                        <?php if($produk->boms->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah</th>
                                            <th>Satuan</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $produk->boms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($bom->bahanBaku->nama ?? 'N/A'); ?></td>
                                                <td><?php echo e($bom->jumlah); ?></td>
                                                <td><?php echo e($bom->satuan); ?></td>
                                                <td><?php echo e(number_format($bom->bahanBaku->harga_satuan ?? 0, 0, ',', '.')); ?></td>
                                                <td><?php echo e(number_format(($bom->bahanBaku->harga_satuan ?? 0) * $bom->jumlah, 0, ',', '.')); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Belum ada BOM untuk produk ini.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo e(route('master-data.produk.edit', $produk->id)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo e(route('master-data.produk.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/produk/show.blade.php ENDPATH**/ ?>
<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-cash-coin"></i> Data Penggajian</h3>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="<?php echo e(route('transaksi.penggajian.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Penggajian
        </a>
    </div>

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pegawai</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Gaji Pokok / Tarif</th>
                            <th>Jam Kerja</th>
                            <th>Tunjangan</th>
                            <th>Asuransi</th>
                            <th>Bonus</th>
                            <th>Potongan</th>
                            <th>Total Gaji</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $penggajians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $gaji): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $jenis = strtoupper($gaji->pegawai->jenis_pegawai ?? 'BTKTL');
                            ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><?php echo e($gaji->pegawai->nama ?? '-'); ?></td>
                                <td>
                                    <span class="badge <?php echo e($jenis === 'BTKL' ? 'bg-info' : 'bg-secondary'); ?>">
                                        <?php echo e($jenis); ?>

                                    </span>
                                </td>
                                <td><?php echo e(\Carbon\Carbon::parse($gaji->tanggal_penggajian)->format('d-m-Y')); ?></td>
                                <td>
                                    <?php if($jenis === 'BTKL'): ?>
                                        Rp <?php echo e(number_format($gaji->tarif_per_jam ?? 0, 0, ',', '.')); ?>/jam
                                    <?php else: ?>
                                        Rp <?php echo e(number_format($gaji->gaji_pokok ?? 0, 0, ',', '.')); ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($jenis === 'BTKL'): ?>
                                        <?php echo e(number_format($gaji->total_jam_kerja ?? 0, 2)); ?> jam
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?php echo e(number_format($gaji->tunjangan ?? 0, 0, ',', '.')); ?></td>
                                <td>Rp <?php echo e(number_format($gaji->asuransi ?? 0, 0, ',', '.')); ?></td>
                                <td>Rp <?php echo e(number_format($gaji->bonus ?? 0, 0, ',', '.')); ?></td>
                                <td>Rp <?php echo e(number_format($gaji->potongan ?? 0, 0, ',', '.')); ?></td>
                                <td><strong>Rp <?php echo e(number_format($gaji->total_gaji, 0, ',', '.')); ?></strong></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo e(route('transaksi.penggajian.show', $gaji->id)); ?>" class="btn btn-sm btn-info" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="<?php echo e(route('transaksi.penggajian.destroy', $gaji->id)); ?>" method="POST" onsubmit="return confirm('Hapus data ini?')" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="12" class="text-center">Belum ada data penggajian.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if($penggajians->count() > 0): ?>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="10" class="text-end">Total Keseluruhan:</th>
                                <th>Rp <?php echo e(number_format($penggajians->sum('total_gaji'), 0, ',', '.')); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/penggajian/index.blade.php ENDPATH**/ ?>
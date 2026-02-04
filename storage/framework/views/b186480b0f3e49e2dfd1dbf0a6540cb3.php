<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Laporan Penggajian</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('laporan.penggajian')); ?>" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bulan">Pilih Bulan</label>
                                    <input type="month" name="bulan" id="bulan" class="form-control" 
                                           value="<?php echo e(request('bulan', now()->format('Y-m'))); ?>">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="<?php echo e(route('laporan.penggajian')); ?>" class="btn btn-secondary ml-2">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive table-scroll-horizontal" style="overflow-x: auto; cursor: grab; -webkit-overflow-scrolling: touch;">
                        <table class="table table-bordered table-striped table-nowrap" style="white-space: nowrap; min-width: 100%;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $penggajians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penggajian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $jenis = strtoupper($penggajian->pegawai->jenis_pegawai ?? 'BTKTL');
                                ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e($penggajian->tanggal_penggajian ? \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('F Y') : '-'); ?></td>
                                    <td><?php echo e($penggajian->pegawai->nama ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($jenis === 'BTKL' ? 'bg-info' : 'bg-secondary'); ?>">
                                            <?php echo e($jenis); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($penggajian->tanggal_penggajian ? \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d-m-Y') : '-'); ?></td>
                                    <td class="text-right">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jenis === 'BTKL'): ?>
                                            Rp <?php echo e(number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.')); ?>/jam
                                        <?php else: ?>
                                            Rp <?php echo e(number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.')); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jenis === 'BTKL'): ?>
                                            <?php echo e(number_format($penggajian->total_jam_kerja ?? 0, 2)); ?> jam
                                        <?php else: ?>
                                            -
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="text-right">Rp <?php echo e(number_format($penggajian->tunjangan ?? 0, 0, ',', '.')); ?></td>
                                    <td class="text-right">Rp <?php echo e(number_format($penggajian->asuransi ?? 0, 0, ',', '.')); ?></td>
                                    <td class="text-right">Rp <?php echo e(number_format($penggajian->bonus ?? 0, 0, ',', '.')); ?></td>
                                    <td class="text-right">Rp <?php echo e(number_format($penggajian->potongan ?? 0, 0, ',', '.')); ?></td>
                                    <td class="text-right"><strong>Rp <?php echo e(number_format($penggajian->total_gaji, 0, ',', '.')); ?></strong></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="12" class="text-center">Tidak ada data penggajian</td>
                                </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="11" class="text-right">Total Keseluruhan</th>
                                    <th class="text-right"><strong>Rp <?php echo e(number_format($total, 0, ',', '.')); ?></strong></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="<?php echo e(route('laporan.penggajian', ['bulan' => request('bulan'), 'export' => 'pdf'])); ?>" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    /* Smooth horizontal scrolling untuk tabel - FORCE */
    .table-scroll-horizontal {
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch !important;
        scroll-behavior: smooth !important;
        cursor: grab !important;
        display: block !important;
        width: 100% !important;
    }
    
    .table-scroll-horizontal:active {
        cursor: grabbing !important;
    }
    
    /* Pastikan tabel tidak wrap - FORCE */
    .table-nowrap {
        white-space: nowrap !important;
        min-width: 100% !important;
        width: max-content !important;
    }
    
    .table-nowrap th,
    .table-nowrap td {
        white-space: nowrap !important;
        padding: 0.5rem !important;
    }
    
    /* Custom scrollbar untuk tampilan lebih baik */
    .table-scroll-horizontal::-webkit-scrollbar {
        height: 10px !important;
    }
    
    .table-scroll-horizontal::-webkit-scrollbar-track {
        background: #f1f1f1 !important;
        border-radius: 4px !important;
    }
    
    .table-scroll-horizontal::-webkit-scrollbar-thumb {
        background: #888 !important;
        border-radius: 4px !important;
    }
    
    .table-scroll-horizontal::-webkit-scrollbar-thumb:hover {
        background: #555 !important;
    }
    
    /* Pastikan container tidak overflow */
    .card-body {
        overflow: visible !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Enable drag to scroll functionality
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.querySelector('.table-scroll-horizontal');
        let isDown = false;
        let startX;
        let scrollLeft;

        if (slider) {
            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                slider.style.cursor = 'grabbing';
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            slider.addEventListener('mouseleave', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });

            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });

            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 2; // Scroll speed
                slider.scrollLeft = scrollLeft - walk;
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/laporan/penggajian/index.blade.php ENDPATH**/ ?>
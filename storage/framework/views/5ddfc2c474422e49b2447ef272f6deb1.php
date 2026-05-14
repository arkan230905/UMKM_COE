<?php $__env->startSection('title', 'Laporan Posisi Keuangan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-balance-scale me-2"></i>Laporan Posisi Keuangan
        </h2>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo e(str_pad($m, 2, '0', STR_PAD_LEFT)); ?>" 
                                <?php echo e((isset($bulan) && $bulan == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                            </option>
                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e((isset($tahun) && $tahun == $y) ? 'selected' : ''); ?>>
                                <?php echo e($y); ?>

                            </option>
                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
                <a href="<?php echo e(route('akuntansi.laporan-posisi-keuangan.pdf', ['bulan' => $bulan ?? date('m'), 'tahun' => $tahun ?? date('Y')])); ?>" 
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Cetak PDF
                </a>
            </form>
        </div>
    </div>

    <!-- Balance Status Alert -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$neraca['neraca_seimbang']): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Peringatan:</strong> Neraca tidak seimbang! 
        Selisih: Rp <?php echo e(number_format(abs($neraca['selisih']), 0, ',', '.')); ?>

    </div>
    <?php else: ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <strong>Neraca Seimbang</strong>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-balance-scale me-2"></i>Laporan Posisi Keuangan per <?php echo e(\Carbon\Carbon::create($tahun ?? date('Y'), $bulan ?? date('m'), 1)->isoFormat('MMMM YYYY')); ?>

            </h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tbody>
                            <!-- ASET SECTION -->
                            <tr class="table-secondary">
                                <th colspan="2" class="fw-bold text-uppercase">ASET</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            <!-- ASET LANCAR -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">ASET LANCAR</td>
                                <td class="text-end"></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $neraca['aset']['lancar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-5"><?php echo e($item['nama_akun']); ?></td>
                                    <td class="text-muted small"><?php echo e($item['kode_akun']); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($item['saldo'], 0, ',', '.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Aset Lancar</td>
                                <td class="text-end fw-bold">Rp <?php echo e(number_format($neraca['aset']['total_lancar'], 0, ',', '.')); ?></td>
                            </tr>
                            
                            <!-- ASET TIDAK LANCAR -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">ASET TIDAK LANCAR</td>
                                <td class="text-end"></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $neraca['aset']['tidak_lancar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-5"><?php echo e($item['nama_akun']); ?></td>
                                    <td class="text-muted small"><?php echo e($item['kode_akun']); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($item['saldo'], 0, ',', '.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Aset Tidak Lancar</td>
                                <td class="text-end fw-bold">Rp <?php echo e(number_format($neraca['aset']['total_tidak_lancar'], 0, ',', '.')); ?></td>
                            </tr>
                            
                            <!-- TOTAL ASET -->
                            <tr class="table-primary fw-bold">
                                <td colspan="2">JUMLAH ASET</td>
                                <td class="text-end">Rp <?php echo e(number_format($neraca['aset']['total_aset'], 0, ',', '.')); ?></td>
                            </tr>
                            
                            <tr>
                                <td colspan="3" class="border-0">&nbsp;</td>
                            </tr>
                            
                            <!-- KEWAJIBAN DAN EKUITAS SECTION -->
                            <tr class="table-secondary">
                                <th colspan="2" class="fw-bold text-uppercase">KEWAJIBAN DAN EKUITAS</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            <!-- KEWAJIBAN -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">KEWAJIBAN</td>
                                <td class="text-end"></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $neraca['kewajiban']['detail']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-5"><?php echo e($item['nama_akun']); ?></td>
                                    <td class="text-muted small"><?php echo e($item['kode_akun']); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($item['saldo'], 0, ',', '.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Kewajiban</td>
                                <td class="text-end fw-bold">Rp <?php echo e(number_format($neraca['kewajiban']['total'], 0, ',', '.')); ?></td>
                            </tr>
                            
                            <!-- EKUITAS -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">EKUITAS / MODAL</td>
                                <td class="text-end"></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $neraca['ekuitas']['detail']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-5"><?php echo e($item['nama_akun']); ?></td>
                                    <td class="text-muted small"><?php echo e($item['kode_akun']); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($item['saldo'], 0, ',', '.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <!-- ✅ LABA/RUGI BERJALAN dari Laporan Laba Rugi -->
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($neraca['laba_rugi_berjalan'])): ?>
                                <tr>
                                    <td class="ps-5"><?php echo e($neraca['laba_rugi_akun_nama'] ?? 'Laba/Rugi Berjalan'); ?></td>
                                    <td class="text-muted small">-</td>
                                    <td class="text-end">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($neraca['laba_rugi_berjalan'] < 0): ?>
                                            -Rp <?php echo e(number_format(abs($neraca['laba_rugi_berjalan']), 0, ',', '.')); ?>

                                        <?php else: ?>
                                            Rp <?php echo e(number_format($neraca['laba_rugi_berjalan'], 0, ',', '.')); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Ekuitas</td>
                                <td class="text-end fw-bold">Rp <?php echo e(number_format($neraca['total_ekuitas_with_laba_rugi'] ?? $neraca['ekuitas']['total'], 0, ',', '.')); ?></td>
                            </tr>
                            
                            <!-- TOTAL KEWAJIBAN DAN EKUITAS -->
                            <tr class="table-success fw-bold">
                                <td colspan="2">JUMLAH KEWAJIBAN DAN EKUITAS</td>
                                <td class="text-end">Rp <?php echo e(number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/akuntansi/laporan_posisi_keuangan.blade.php ENDPATH**/ ?>
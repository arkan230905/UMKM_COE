<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penggajian - <?php echo e(now()->format('d-m-Y')); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16px; }
        .header p { margin: 3px 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px 4px; font-size: 10px; }
        th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; }
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 9px; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
        .badge-secondary { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PENGGAJIAN</h2>
        <p>Periode: <?php echo e($bulan ?? 'Semua Data'); ?></p>
        <p>Tanggal Cetak: <?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 15%;">Nama Pegawai</th>
                <th style="width: 7%;">Jenis</th>
                <th style="width: 12%;">Gaji Pokok / Tarif</th>
                <th style="width: 8%;">Jam Kerja</th>
                <th style="width: 10%;">Tunjangan</th>
                <th style="width: 10%;">Asuransi</th>
                <th style="width: 8%;">Bonus</th>
                <th style="width: 10%;">Potongan</th>
                <th style="width: 12%;">Total Gaji</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $penggajians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penggajian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $jenis = strtoupper($penggajian->pegawai->jenis_pegawai ?? 'BTKTL');
            ?>
            <tr>
                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                <td class="text-center"><?php echo e(\Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d-m-Y')); ?></td>
                <td><?php echo e($penggajian->pegawai->nama ?? '-'); ?></td>
                <td class="text-center">
                    <span class="badge <?php echo e($jenis === 'BTKL' ? 'badge-info' : 'badge-secondary'); ?>">
                        <?php echo e($jenis); ?>

                    </span>
                </td>
                <td class="text-right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jenis === 'BTKL'): ?>
                        <?php echo e(number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.')); ?>

                    <?php else: ?>
                        <?php echo e(number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.')); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="text-right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jenis === 'BTKL'): ?>
                        <?php echo e(number_format($penggajian->total_jam_kerja ?? 0, 0, ',', '.')); ?> jam
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
                <td colspan="11" class="text-center">Tidak ada data penggajian</td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" class="text-right">Total Keseluruhan</th>
                <th class="text-right">Rp <?php echo e(number_format($total, 0, ',', '.')); ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak oleh: <?php echo e(Auth::user()->name ?? 'System'); ?> | <?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/laporan/penggajian/pdf.blade.php ENDPATH**/ ?>
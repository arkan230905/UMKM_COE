<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Umum</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 6px;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            border-radius: 3px;
            background-color: #6c757d;
            color: white;
        }
        .badge-debit {
            background-color: #0dcaf0;
        }
        .badge-kredit {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>JURNAL UMUM</h2>
        <p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($from && $to): ?>
                Periode: <?php echo e(\Carbon\Carbon::parse($from)->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse($to)->format('d/m/Y')); ?>

            <?php elseif($from): ?>
                Dari: <?php echo e(\Carbon\Carbon::parse($from)->format('d/m/Y')); ?>

            <?php elseif($to): ?>
                Sampai: <?php echo e(\Carbon\Carbon::parse($to)->format('d/m/Y')); ?>

            <?php else: ?>
                Semua Periode
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refType || $refId): ?>
            <p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refType): ?> Ref Type: <?php echo e($refType); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refId): ?> | Ref ID: <?php echo e($refId); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%">Tanggal</th>
                <th style="width: 10%">Ref</th>
                <th style="width: 18%">Memo</th>
                <th style="width: 8%">Kode Akun</th>
                <th style="width: 24%">Nama Akun</th>
                <th style="width: 16%" class="text-end">Debit</th>
                <th style="width: 16%" class="text-end">Kredit</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $totalDebit = 0;
                $totalKredit = 0;
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $e->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $totalDebit += $l->debit ?? 0;
                        $totalKredit += $l->credit ?? 0;
                    ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i===0): ?>
                            <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e(\Carbon\Carbon::parse($e->tanggal)->format('d/m/Y')); ?></td>
                            <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e($e->ref_type); ?>#<?php echo e($e->ref_id); ?></td>
                            <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e($e->memo); ?></td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td>
                            <strong><?php echo e($l->account->code ?? '-'); ?></strong>
                            <span class="badge <?php echo e(($l->debit ?? 0) > 0 ? 'badge-debit' : 'badge-kredit'); ?>">
                                <?php echo e(($l->debit ?? 0) > 0 ? 'D' : 'K'); ?>

                            </span>
                        </td>
                        <td>
                            <strong><?php echo e($l->account->name ?? 'Akun tidak ditemukan'); ?></strong>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->account): ?>
                                <br><small style="color: #666;">(<?php echo e($l->account->type ?? ''); ?>)</small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="text-end"><?php echo e($l->debit > 0 ? 'Rp '.number_format($l->debit, 0, ',', '.') : '-'); ?></td>
                        <td class="text-end"><?php echo e($l->credit > 0 ? 'Rp '.number_format($l->credit, 0, ',', '.') : '-'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data jurnal</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($entries->count() > 0): ?>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-end"><strong>TOTAL:</strong></td>
                <td class="text-end"><strong>Rp <?php echo e(number_format($totalDebit, 0, ',', '.')); ?></strong></td>
                <td class="text-end"><strong>Rp <?php echo e(number_format($totalKredit, 0, ',', '.')); ?></strong></td>
            </tr>
        </tfoot>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <div style="margin-top: 30px; font-size: 9px;">
        <p>Dicetak pada: <?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/akuntansi/jurnal-umum-pdf.blade.php ENDPATH**/ ?>
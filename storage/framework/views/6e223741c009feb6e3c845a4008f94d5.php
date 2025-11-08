<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak BOM - <?php echo e($bom->produk->nama_produk); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 1cm;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
        }
        .report-title {
            font-size: 18px;
            margin: 10px 0;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-coll-layout: fixed;
        }
        .table th, .table td {
            padding: 0.5rem;
            vertical-align: top;
            border: 1px solid #dee2e6;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .mt-4 {
            margin-top: 1.5rem;
        }
        .mb-4 {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="text-center mb-3">
            <?php if(file_exists(public_path('images/logo.png'))): ?>
                <img src="<?php echo e(public_path('images/logo.png')); ?>" alt="Logo" style="max-height: 80px;">
            <?php else: ?>
                <div class="company-name"><?php echo e(config('app.name', 'UMKM COE')); ?></div>
            <?php endif; ?>
        </div>
        <div class="report-title">LAPORAN BILL OF MATERIAL (BOM)</div>
        <div>Tanggal Cetak: <?php echo e(now()->format('d F Y H:i:s')); ?></div>
    </div>

    <div class="mb-4">
        <table class="table table-bordered">
            <tr>
                <th width="30%">Kode BOM</th>
                <td><?php echo e($bom->id); ?></td>
            </tr>
            <tr>
                <th>Nama Produk</th>
                <td><?php echo e($bom->produk->nama_produk); ?></td>
            </tr>
            <tr>
                <th>Tanggal Dibuat</th>
                <td><?php echo e($bom->created_at->format('d F Y H:i')); ?></td>
            </tr>
        </table>
    </div>

    <div class="mb-4">
        <h5>Rincian Bahan Baku</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Bahan Baku</th>
                    <th class="text-end">Kuantitas</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-end">Harga Satuan (Rp)</th>
                    <th class="text-end">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php $__currentLoopData = $bom->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($no++); ?></td>
                        <td><?php echo e($detail->bahanBaku->nama_bahan ?? $detail->bahanBaku->nama ?? 'Bahan Tidak Ditemukan'); ?></td>
                        <td class="text-end">
                            <?php if(strtoupper($detail->satuan) === 'GR'): ?>
                                <?php echo e(number_format($detail->jumlah / 1000, 3, ',', '.')); ?> KG
                            <?php else: ?>
                                <?php echo e(number_format($detail->jumlah, 2, ',', '.')); ?> <?php echo e($detail->satuan); ?>

                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo e($detail->satuan ?? 'pcs'); ?></td>
                        <td class="text-end">
                            <?php
                                $hargaPerKg = $detail->harga_per_satuan;
                                $hargaSatuan = (strtoupper($detail->satuan) === 'GR') ? $hargaPerKg / 1000 : $hargaPerKg;
                            ?>
                            <?php echo e(number_format($hargaSatuan, 0, ',', '.')); ?>

                        </td>
                        <td class="text-end"><?php echo e(number_format($detail->total_harga, 0, ',', '.')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr class="table-light">
                    <td colspan="5" class="text-end fw-bold">Total Biaya Bahan Baku</td>
                    <td class="text-end fw-bold">Rp <?php echo e(number_format($bom->details->sum('total_harga'), 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mb-4">
        <h5>Perhitungan Biaya Produksi</h5>
        <table class="table table-bordered">
            <tr>
                <th width="60%">1. Total Biaya Bahan Baku</th>
                <td class="text-end">Rp <?php echo e(number_format($bom->details->sum('total_harga'), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <th>2. Biaya Tenaga Kerja Langsung (BTKL) - 60%</th>
                <td class="text-end">Rp <?php echo e(number_format($bom->total_btkl, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <th>3. Biaya Overhead Pabrik (BOP) - 40%</th>
                <td class="text-end">
                    Rp <?php echo e(number_format($bom->total_bop, 0, ',', '.')); ?>

                    <div class="text-muted small">
                        BOP Rate: <?php echo e($bom->total_btkl > 0 ? number_format(($bom->total_bop / $bom->total_btkl) * 100, 2, ',', '.') : '0'); ?>% dari BTKL
                    </div>
                </td>
            </tr>
            <tr class="table-active">
                <th class="text-end">TOTAL BIAYA PRODUKSI</th>
                <th class="text-end">Rp <?php echo e(number_format(($bom->details->sum('total_harga') + $bom->total_btkl + $bom->total_bop), 0, ',', '.')); ?></th>
            </tr>
        </table>
    </div>

    <div class="mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="text-center">
                    <p>Mengetahui,</p>
                    <br><br><br>
                    <p>_________________________</p>
                    <p>Manager Produksi</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-center">
                    <p><?php echo e(date('d F Y')); ?></p>
                    <br><br><br>
                    <p>_________________________</p>
                    <p>Pembuat</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
<?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/print.blade.php ENDPATH**/ ?>
<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="text-white">Detail Aset</h2>
        </div>
    </div>

    <!-- Informasi Aset -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Aset</h5>
        </div>
        <div class="card-body bg-white">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-dark"><strong>Kode Aset</strong></td>
                            <td class="text-dark">: <?php echo e($aset->kode_aset); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Nama Aset</strong></td>
                            <td class="text-dark">: <?php echo e($aset->nama_aset); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Jenis Aset</strong></td>
                            <td class="text-dark">: <?php echo e($aset->kategori->jenisAset->nama ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Kategori Aset</strong></td>
                            <td class="text-dark">: <?php echo e($aset->kategori->nama ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Tanggal Pembelian</strong></td>
                            <td class="text-dark">: <?php echo e(\Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y')); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Tanggal Mulai Penyusutan</strong></td>
                            <td class="text-dark">: <?php echo e(\Carbon\Carbon::parse($aset->tanggal_akuisisi)->format('d/m/Y')); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-dark"><strong>Harga Perolehan</strong></td>
                            <td class="text-dark">: Rp <?php echo e(number_format($aset->harga_perolehan, 0, ',', '.')); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Biaya Perolehan</strong></td>
                            <td class="text-dark">: Rp <?php echo e(number_format($aset->biaya_perolehan ?? 0, 0, ',', '.')); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Total Perolehan</strong></td>
                            <td class="text-dark"><strong>: Rp <?php echo e(number_format($totalPerolehan, 0, ',', '.')); ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Nilai Residu</strong></td>
                            <td class="text-dark">: Rp <?php echo e(number_format($nilaiResidu, 0, ',', '.')); ?></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Umur Manfaat</strong></td>
                            <td class="text-dark">: <?php echo e($aset->umur_manfaat); ?> tahun</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Metode Penyusutan</strong></td>
                            <td class="text-dark">: <?php echo e(ucwords(str_replace('_', ' ', $aset->metode_penyusutan))); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Penyusutan -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Penyusutan</h5>
        </div>
        <div class="card-body bg-white">
            <div class="row text-center">
                <div class="col-md-6 mb-3">
                    <div class="card bg-info bg-opacity-10 border border-info">
                        <div class="card-body">
                            <h6 class="text-dark">Penyusutan Per Bulan</h6>
                            <h3 class="text-dark mb-0">Rp <?php echo e(number_format($penyusutanPerBulan, 0, ',', '.')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-success bg-opacity-10 border border-success">
                        <div class="card-body">
                            <h6 class="text-dark">Penyusutan Per Tahun</h6>
                            <h3 class="text-dark mb-0">Rp <?php echo e(number_format($penyusutanPerTahun, 0, ',', '.')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Penyusutan Per Tahun -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Jadwal Penyusutan Per Tahun</h5>
        </div>
        <div class="card-body bg-white">
            <div class="accordion" id="accordionDepreciation">
                <?php $__empty_1 = true; $__currentLoopData = $depreciationSchedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $penyusutanPerBulanTahunIni = $row['beban_penyusutan'] / 12;
                        $tanggalMulai = \Carbon\Carbon::parse($aset->tanggal_akuisisi);
                        $bulanMulai = $tanggalMulai->copy()->addYears($index);
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo e($index); ?>">
                            <button class="accordion-button <?php echo e($index > 0 ? 'collapsed' : ''); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo e($index); ?>" aria-expanded="<?php echo e($index == 0 ? 'true' : 'false'); ?>" aria-controls="collapse<?php echo e($index); ?>">
                                <div class="w-100 d-flex justify-content-between align-items-center pe-3">
                                    <span class="fw-bold">Tahun <?php echo e($row['tahun']); ?></span>
                                    <div class="d-flex gap-4">
                                        <span class="text-muted">Beban: <strong class="text-dark">Rp <?php echo e(number_format($row['beban_penyusutan'], 0, ',', '.')); ?></strong></span>
                                        <span class="text-muted">Akumulasi: <strong class="text-dark">Rp <?php echo e(number_format($row['akumulasi_penyusutan'], 0, ',', '.')); ?></strong></span>
                                        <span class="text-muted">Nilai Buku: <strong class="text-dark">Rp <?php echo e(number_format($row['nilai_buku_akhir'], 0, ',', '.')); ?></strong></span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?php echo e($index); ?>" class="accordion-collapse collapse <?php echo e($index == 0 ? 'show' : ''); ?>" aria-labelledby="heading<?php echo e($index); ?>" data-bs-parent="#accordionDepreciation">
                            <div class="accordion-body">
                                <h6 class="text-dark mb-3"><i class="bi bi-calendar-month me-2"></i>Detail Penyusutan Per Bulan - Tahun <?php echo e($row['tahun']); ?></h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th class="text-center text-dark">Bulan</th>
                                                <th class="text-end text-dark">Beban Penyusutan</th>
                                                <th class="text-end text-dark">Akumulasi Penyusutan</th>
                                                <th class="text-end text-dark">Nilai Buku</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $akumulasiSebelumnya = $index > 0 ? $depreciationSchedule[$index - 1]['akumulasi_penyusutan'] : 0;
                                                $nilaiBukuAwal = $totalPerolehan - $akumulasiSebelumnya;
                                            ?>
                                            <?php for($bulan = 1; $bulan <= 12; $bulan++): ?>
                                                <?php
                                                    $tanggalBulan = $bulanMulai->copy()->addMonths($bulan - 1);
                                                    $akumulasiBulan = $akumulasiSebelumnya + ($penyusutanPerBulanTahunIni * $bulan);
                                                    $nilaiBukuBulan = $totalPerolehan - $akumulasiBulan;
                                                ?>
                                                <tr>
                                                    <td class="text-center text-dark"><?php echo e($tanggalBulan->format('F Y')); ?></td>
                                                    <td class="text-end text-dark">Rp <?php echo e(number_format($penyusutanPerBulanTahunIni, 0, ',', '.')); ?></td>
                                                    <td class="text-end text-dark">Rp <?php echo e(number_format($akumulasiBulan, 0, ',', '.')); ?></td>
                                                    <td class="text-end text-dark">Rp <?php echo e(number_format(max($nilaiBukuBulan, $nilaiResidu), 0, ',', '.')); ?></td>
                                                </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td class="text-end text-dark fw-bold">Total Tahun <?php echo e($row['tahun']); ?></td>
                                                <td class="text-end text-dark fw-bold">Rp <?php echo e(number_format($row['beban_penyusutan'], 0, ',', '.')); ?></td>
                                                <td class="text-end text-dark fw-bold">Rp <?php echo e(number_format($row['akumulasi_penyusutan'], 0, ',', '.')); ?></td>
                                                <td class="text-end text-dark fw-bold">Rp <?php echo e(number_format($row['nilai_buku_akhir'], 0, ',', '.')); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="alert alert-info text-dark">
                        <i class="bi bi-info-circle me-2"></i>Belum ada jadwal penyusutan
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="mb-4">
        <a href="<?php echo e(route('master-data.aset.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="<?php echo e(route('master-data.aset.edit', $aset->id)); ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/aset/show.blade.php ENDPATH**/ ?>
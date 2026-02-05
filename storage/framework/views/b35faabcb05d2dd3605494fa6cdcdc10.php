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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aset->metode_penyusutan === 'sum_of_years_digits' && $aset->tanggal_perolehan): ?>
                        <tr>
                            <td class="text-dark"><strong>Tanggal Perolehan</strong></td>
                            <td class="text-dark">: <?php echo e(\Carbon\Carbon::parse($aset->tanggal_perolehan)->format('d/m/Y')); ?></td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                            <td class="text-dark">: Rp <?php echo e(number_format($aset->nilai_residu, 0, ',', '.')); ?></td>
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

    
    <!-- Hasil Perhitungan Penyusutan -->
    <div class="card mb-4 bg-white" id="hasil_perhitungan_card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Hasil Perhitungan Penyusutan</h5>
        </div>
        <div class="card-body bg-white">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aset->metode_penyusutan === 'sum_of_years_digits'): ?>
                <!-- Tampilan khusus untuk Sum Of Years Digits -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle me-2"></i>Informasi Perhitungan</h6>
                            <p class="mb-2">
                                <strong>Metode:</strong> <?php echo e(ucwords(str_replace('_', ' ', $aset->metode_penyusutan))); ?><br>
                                <strong>Tahun Pertama:</strong> 
                                <?php
                                    $tanggalPerolehan = \Carbon\Carbon::parse($aset->tanggal_akuisisi ?? $aset->tanggal_beli);
                                    $bulanPertama = 12 - $tanggalPerolehan->month + 1;
                                ?>
                                <?php echo e($bulanPertama); ?> bulan (<?php echo e($tanggalPerolehan->format('F')); ?> - Desember <?php echo e($tanggalPerolehan->year); ?>)<br>
                                <strong>Dasar Perhitungan:</strong> (Rp <?php echo e(number_format($totalPerolehan - $aset->nilai_residu, 0, ',', '.')); ?>) × (Bobot Tahun/Total Angka Tahun) × (Jumlah Bulan/12)
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-info bg-opacity-10 border border-info">
                            <div class="card-body">
                                <h6 class="text-dark">Penyusutan Per Bulan</h6>
                                <h3 class="text-dark mb-0">Rp <?php echo e(number_format($penyusutanPerBulan, 0, ',', '.')); ?></h3>
                                <small class="text-muted"><?php echo e($bulanPertama); ?> bulan pertama</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-success bg-opacity-10 border border-success">
                            <div class="card-body">
                                <h6 class="text-dark">Penyusutan Per Tahun</h6>
                                <h3 class="text-dark mb-0">Rp <?php echo e(number_format($penyusutanPerTahun, 0, ',', '.')); ?></h3>
                                <small class="text-muted">Tahun <?php echo e($tanggalPerolehan->year); ?> (<?php echo e($bulanPertama); ?> bulan)</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Tampilan untuk metode lain -->
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Jadwal Penyusutan Per Tahun -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Jadwal Penyusutan Per Tahun</h5>
        </div>
        <div class="card-body bg-white">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>TAHUN</th>
                            <th class="text-end">PENYUSUTAN</th>
                            <th class="text-end">AKUMULASI PENY</th>
                            <th class="text-end">NILAI BUKU</th>
                            <th>RINCIAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $depreciationData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row['periode'] ?? $row['tahun']); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($row['beban_penyusutan'], 2, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($row['akumulasi_penyusutan'], 2, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($row['nilai_buku_akhir'], 2, ',', '.')); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#monthlyDetailModal" onclick="showMonthlyDetail('<?php echo e($row['periode']); ?>', <?php echo e($row['beban_penyusutan']); ?>, <?php echo e($row['tahun']); ?>, <?php echo e($row['jumlah_bulan']); ?>)">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada jadwal penyusutan</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metodePenyusutan = '<?php echo e($aset->metode_penyusutan); ?>';
    const hasilPerhitunganCard = document.getElementById('hasil_perhitungan_card');
    
    console.log('Metode Penyusutan:', metodePenyusutan);
    console.log('Card found:', hasilPerhitunganCard);
    
    // Tampilkan hasil perhitungan untuk semua metode
    hasilPerhitunganCard.style.display = 'block';
    console.log('Menampilkan hasil perhitungan untuk metode:', metodePenyusutan);
});
</script>
<?php $__env->stopSection(); ?>

<!-- Modal Detail Per Bulan -->
<div class="modal fade" id="monthlyDetailModal" tabindex="-1" aria-labelledby="monthlyDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="monthlyDetailModalLabel">Rincian Penyusutan Per Bulan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="monthlyDetailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function showMonthlyDetail(periode, penyusutanTahunan, tahun, jumlahBulan) {
    const modal = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));
    const content = document.getElementById('monthlyDetailContent');
    
    // Filter data bulanan untuk periode yang dipilih
    const monthlyData = <?php echo json_encode($monthlyDepreciationData, 15, 512) ?>;
    
    // Cari index mulai untuk periode ini
    let startIndex = -1;
    let periodeCount = 0;
    
    for (let i = 0; i < monthlyData.length; i++) {
        if (monthlyData[i].periode.includes(tahun.toString())) {
            if (periodeCount === 0) {
                startIndex = i;
            }
            periodeCount++;
            
            // Jika sudah menemukan jumlah bulan yang sesuai, stop
            if (periodeCount >= jumlahBulan) {
                break;
            }
        }
    }
    
    // Ambil data untuk periode ini saja
    const periodeData = monthlyData.slice(startIndex, startIndex + jumlahBulan);
    
    // Generate HTML untuk rincian per bulan
    let html = `
        <div class="mb-3">
            <h6>Periode ${periode}</h6>
            <p><strong>Metode:</strong> <?php echo e(ucwords(str_replace('_', ' ', $aset->metode_penyusutan))); ?></p>
            <p><strong>Penyusutan periode:</strong> Rp ${numberFormat(penyusutanTahunan)}</p>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="text-end">Penyusutan</th>
                        <th class="text-end">Akumulasi</th>
                        <th class="text-end">Nilai Buku</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    periodeData.forEach(item => {
        html += `
            <tr>
                <td>${item.periode}</td>
                <td class="text-end">Rp ${numberFormat(item.biaya_penyusutan)}</td>
                <td class="text-end">Rp ${numberFormat(item.akumulasi_penyusutan)}</td>
                <td class="text-end">Rp ${numberFormat(item.nilai_buku)}</td>
            </tr>
        `;
    });
    
    // Hitung total untuk periode ini
    const totalPenyusutan = periodeData.reduce((sum, item) => sum + item.biaya_penyusutan, 0);
    const lastItem = periodeData[periodeData.length - 1];
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th>Total ${periodeData.length} bulan</th>
                        <th class="text-end">Rp ${numberFormat(totalPenyusutan)}</th>
                        <th class="text-end">Rp ${numberFormat(lastItem ? lastItem.akumulasi_penyusutan : 0)}</th>
                        <th class="text-end">Rp ${numberFormat(lastItem ? lastItem.nilai_buku : 0)}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    content.innerHTML = html;
    modal.show();
}

function numberFormat(num) {
    return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&.');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/aset/show.blade.php ENDPATH**/ ?>
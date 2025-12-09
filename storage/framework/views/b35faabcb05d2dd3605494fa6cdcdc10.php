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
                                <td><?php echo e($row->tahun); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($row->beban_penyusutan, 2, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($row->akumulasi_penyusutan, 2, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($row->nilai_buku_akhir, 2, ',', '.')); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aset->metode_penyusutan === 'garis_lurus'): ?>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#monthlyDetailModal" onclick="showMonthlyDetail(<?php echo e($row->tahun); ?>, <?php echo e($row->beban_penyusutan); ?>)">
                                            <i class="fas fa-info-circle"></i> Detail
                                        </button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    
    // Sembunyikan/tampilkan hasil perhitungan berdasarkan metode
    if (metodePenyusutan === 'garis_lurus') {
        // Tampilkan untuk metode garis lurus
        hasilPerhitunganCard.style.display = 'block';
        console.log('Menampilkan hasil perhitungan untuk garis lurus');
    } else if (metodePenyusutan === 'saldo_menurun' || metodePenyusutan === 'sum_of_years_digits') {
        // Sembunyikan untuk metode saldo menurun dan jumlah angka tahun
        hasilPerhitunganCard.style.display = 'none';
        console.log('Menyembunyikan hasil perhitungan untuk metode:', metodePenyusutan);
    }
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
function showMonthlyDetail(tahun, penyusutanTahunan) {
    const monthlyDepreciation = penyusutanTahunan / 12;
    const modal = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));
    const content = document.getElementById('monthlyDetailContent');
    
    // Tentukan jumlah bulan berdasarkan tahun
    const startYear = <?php echo e($aset->tanggal_akuisisi ? \Carbon\Carbon::parse($aset->tanggal_akuisisi)->year : \Carbon\Carbon::parse($aset->tanggal_beli)->year); ?>;
    const startMonth = <?php echo e($aset->tanggal_akuisisi ? \Carbon\Carbon::parse($aset->tanggal_akuisisi)->month : \Carbon\Carbon::parse($aset->tanggal_beli)->month); ?>;
    
    let monthsToShow = 12; // Default 12 bulan
    
    if (tahun === startYear) {
        // Tahun pertama: hitung dari bulan perolehan
        monthsToShow = 12 - startMonth + 1;
    }
    
    // Generate HTML untuk rincian per bulan
    let html = `
        <div class="mb-3">
            <h6>Tahun ${tahun}</h6>
            <p><strong>Penyusutan per tahun:</strong> Rp ${numberFormat(penyusutanTahunan)}</p>
            <p><strong>Penyusutan per bulan:</strong> Rp ${numberFormat(monthlyDepreciation)}</p>
            <p><strong>Jumlah bulan:</strong> ${monthsToShow} bulan</p>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-end">Penyusutan</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    if (tahun === startYear) {
        // Tahun pertama: mulai dari bulan perolehan
        for (let i = startMonth - 1; i < 12; i++) {
            html += `
                <tr>
                    <td>${monthNames[i]} ${tahun}</td>
                    <td class="text-end">Rp ${numberFormat(monthlyDepreciation)}</td>
                </tr>
            `;
        }
    } else {
        // Tahun-tahun berikutnya: 12 bulan penuh
        for (let i = 0; i < 12; i++) {
            html += `
                <tr>
                    <td>${monthNames[i]} ${tahun}</td>
                    <td class="text-end">Rp ${numberFormat(monthlyDepreciation)}</td>
                </tr>
            `;
        }
    }
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th>Total ${monthsToShow} bulan</th>
                        <th class="text-end">Rp ${numberFormat(monthlyDepreciation * monthsToShow)}</th>
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


<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs me-2"></i>Detail BTKL
        </h2>
        <div>
            <a href="<?php echo e(route('master-data.btkl.edit', $prosesProduksi)); ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="<?php echo e(route('master-data.btkl.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Detail Utama -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi BTKL: <?php echo e($prosesProduksi->nama_proses); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 40%">Kode Proses:</td>
                                    <td><code class="fs-6"><?php echo e($prosesProduksi->kode_proses); ?></code></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Nama Proses:</td>
                                    <td><?php echo e($prosesProduksi->nama_proses); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Tarif BTKL:</td>
                                    <td>
                                        <span class="fs-5 fw-bold text-primary">Rp <?php echo e(number_format($prosesProduksi->tarif_btkl, 0, ',', '.')); ?></span>
                                        <small class="text-muted">per <?php echo e($prosesProduksi->satuan_btkl); ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Satuan BTKL:</td>
                                    <td><span class="badge bg-secondary"><?php echo e($prosesProduksi->satuan_btkl); ?></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 40%">Kapasitas per Jam:</td>
                                    <td>
                                        <span class="fs-5 fw-bold text-info"><?php echo e($prosesProduksi->kapasitas_per_jam ?? 0); ?></span>
                                        <small class="text-muted">unit/jam</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Biaya per Produk:</td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksi->biaya_per_produk > 0): ?>
                                            <span class="fs-5 fw-bold text-success"><?php echo e($prosesProduksi->biaya_per_produk_formatted); ?></span>
                                            <small class="text-muted">per unit</small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Efisiensi:</td>
                                    <td>
                                        <span class="fs-6 fw-bold text-warning"><?php echo e(number_format($prosesProduksi->efisiensi_produksi, 4, ',', '.')); ?></span>
                                        <small class="text-muted">unit/Rp</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Status:</td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksi->tarif_btkl > 0 && $prosesProduksi->kapasitas_per_jam > 0): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Perlu Konfigurasi</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksi->deskripsi): ?>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold">Deskripsi Proses:</h6>
                                <p class="text-muted"><?php echo e($prosesProduksi->deskripsi); ?></p>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Perhitungan & Analisis -->
        <div class="col-md-4">
            <!-- Perhitungan Detail -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Perhitungan Biaya
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksi->kapasitas_per_jam > 0): ?>
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="fs-4 fw-bold text-primary">Rp <?php echo e(number_format($prosesProduksi->tarif_btkl, 0, ',', '.')); ?></div>
                                <small class="text-muted">Tarif per <?php echo e($prosesProduksi->satuan_btkl); ?></small>
                            </div>
                            
                            <div class="mb-3">
                                <i class="fas fa-divide text-muted"></i>
                            </div>
                            
                            <div class="mb-3">
                                <div class="fs-4 fw-bold text-info"><?php echo e($prosesProduksi->kapasitas_per_jam); ?></div>
                                <small class="text-muted">Unit per jam</small>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-0">
                                <div class="fs-3 fw-bold text-success"><?php echo e($prosesProduksi->biaya_per_produk_formatted); ?></div>
                                <small class="text-muted">Biaya per unit produk</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p>Kapasitas per jam belum diatur</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Simulasi Produksi -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Simulasi Produksi
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksi->kapasitas_per_jam > 0): ?>
                        <div class="mb-3">
                            <label class="form-label">Simulasi untuk berapa unit?</label>
                            <input type="number" id="simulasiUnit" class="form-control" value="100" min="1">
                        </div>
                        
                        <div id="hasilSimulasi">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold text-primary" id="waktuDiperlukan">-</div>
                                        <small class="text-muted">Jam diperlukan</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold text-success" id="totalBiayaBTKL">-</div>
                                        <small class="text-muted">Total biaya BTKL</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <p>Simulasi tidak tersedia</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penggunaan (jika ada) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Riwayat Penggunaan dalam BOM
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksi->bomProses && $prosesProduksi->bomProses->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>BOM</th>
                                        <th>Produk</th>
                                        <th>Urutan</th>
                                        <th>Durasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $prosesProduksi->bomProses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bomProses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><code><?php echo e($bomProses->bom->kode_bom ?? '-'); ?></code></td>
                                            <td><?php echo e($bomProses->bom->produk->nama ?? '-'); ?></td>
                                            <td><?php echo e($bomProses->urutan); ?></td>
                                            <td><?php echo e($bomProses->durasi); ?> <?php echo e($prosesProduksi->satuan_btkl); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Proses ini belum digunakan dalam BOM manapun</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const simulasiInput = document.getElementById('simulasiUnit');
    const kapasitasPerJam = <?php echo e($prosesProduksi->kapasitas_per_jam ?? 0); ?>;
    const biayaPerProduk = <?php echo e($prosesProduksi->biaya_per_produk); ?>;
    
    function updateSimulasi() {
        const unit = parseInt(simulasiInput.value) || 0;
        
        if (kapasitasPerJam > 0 && unit > 0) {
            const waktuDiperlukan = (unit / kapasitasPerJam).toFixed(2);
            const totalBiaya = (unit * biayaPerProduk);
            
            document.getElementById('waktuDiperlukan').textContent = waktuDiperlukan;
            document.getElementById('totalBiayaBTKL').textContent = 'Rp ' + totalBiaya.toLocaleString('id-ID');
        }
    }
    
    if (simulasiInput) {
        simulasiInput.addEventListener('input', updateSimulasi);
        updateSimulasi(); // Initial calculation
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/proses-produksi/show.blade.php ENDPATH**/ ?>
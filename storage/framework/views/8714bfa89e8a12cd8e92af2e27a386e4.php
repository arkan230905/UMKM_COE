<?php $__env->startSection('title', 'Tambah BTKL'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs me-2"></i>Tambah BTKL
        </h2>
        <a href="<?php echo e(route('master-data.btkl.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus me-2"></i>Form BTKL Baru
            </h5>
        </div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form action="<?php echo e(route('master-data.btkl.store')); ?>" method="POST" id="createBtklForm">
                <?php echo csrf_field(); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Proses <span class="text-danger">*</span></label>
                            <input type="text" name="nama_proses" class="form-control <?php $__errorArgs = ['nama_proses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   value="<?php echo e(old('nama_proses')); ?>" placeholder="Contoh: Menggoreng, Membumbui, Mengemas" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nama_proses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                            <select name="jabatan_id" id="jabatanSelect" class="form-select <?php $__errorArgs = ['jabatan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required onchange="calculateBTKL()">
                                <option value="">-- Pilih Jabatan BTKL --</option>
                                <?php

                                    $jabatanBtkl = \App\Models\Jabatan::where('kategori', 'btkl')
                                        ->where('user_id', auth()->id())
                                        ->with(['pegawais' => function($q) {
                                            $q->where('user_id', auth()->id());
                                        }])
                                        ->orderBy('nama')
                                        ->get();
?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jabatanBtkl; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jabatan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        // Count pegawai manually to avoid foreign key error
                                        $pegawaiCount = \App\Models\Pegawai::where('jabatan', $jabatan->nama)->count();
                                    ?>
                                    <option value="<?php echo e($jabatan->id); ?>" 
                                            data-tarif="<?php echo e($jabatan->tarif); ?>"
                                            data-pegawai-count="<?php echo e($pegawaiCount); ?>"
                                            <?php echo e(old('jabatan_id') == $jabatan->id ? 'selected' : ''); ?>>
                                        <?php echo e($jabatan->nama); ?> (<?php echo e($pegawaiCount); ?> pegawai @ Rp <?php echo e(number_format($jabatan->tarif, 0, ',', '.')); ?>/jam)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jabatan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Pilih jabatan yang mengurusi proses BTKL ini</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pegawai</label>
                            <div class="input-group">
                                <input type="number" id="jumlahPegawai" class="form-control" readonly>
                                <span class="input-group-text">orang</span>
                            </div>
                            <small class="text-muted">Otomatis dari jabatan yang dipilih</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tarif per Jam Jabatan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="tarifPerJamJabatan" class="form-control" readonly>
                            </div>
                            <small class="text-muted">Tarif per jam dari jabatan</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tarif BTKL (Auto) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tarif_btkl" id="tarifBTKL" class="form-control <?php $__errorArgs = ['tarif_btkl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       value="<?php echo e(old('tarif_btkl', 0)); ?>" readonly required>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tarif_btkl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Jumlah Pegawai × Tarif per Jam</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan BTKL <span class="text-danger">*</span></label>
                            <select name="satuan_btkl" class="form-select <?php $__errorArgs = ['satuan_btkl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="jam" <?php echo e(old('satuan_btkl', 'jam') == 'jam' ? 'selected' : ''); ?>>Jam</option>
                                <option value="menit" <?php echo e(old('satuan_btkl') == 'menit' ? 'selected' : ''); ?>>Menit</option>
                                <option value="unit" <?php echo e(old('satuan_btkl') == 'unit' ? 'selected' : ''); ?>>Unit</option>
                                <option value="batch" <?php echo e(old('satuan_btkl') == 'batch' ? 'selected' : ''); ?>>Batch</option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['satuan_btkl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="kapasitas_per_jam" id="kapasitasPerJam" class="form-control <?php $__errorArgs = ['kapasitas_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       value="<?php echo e(old('kapasitas_per_jam', 50)); ?>" min="1" step="1" placeholder="50" required onchange="calculateBiayaPerProduk()">
                                <span class="input-group-text">unit/jam</span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kapasitas_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Jumlah unit yang dapat diproduksi per jam</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Biaya per Produk (Auto)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="biayaPerProduk" class="form-control" readonly step="0.01">
                                <span class="input-group-text">per unit</span>
                            </div>
                            <small class="text-muted">Tarif BTKL ÷ Kapasitas per Jam</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi proses produksi"><?php echo e(old('deskripsi')); ?></textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('master-data.btkl.index')); ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createBtklForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        console.log('Form is being submitted...');
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});

/**
 * Calculate BTKL rate based on selected jabatan
 */
function calculateBTKL() {
    const jabatanSelect = document.getElementById('jabatanSelect');
    const selectedOption = jabatanSelect.options[jabatanSelect.selectedIndex];
    
    if (selectedOption.value) {
        const tarifPerJam = parseFloat(selectedOption.getAttribute('data-tarif')) || 0;
        const jumlahPegawai = parseInt(selectedOption.getAttribute('data-pegawai-count')) || 0;
        const tarifBTKL = tarifPerJam * jumlahPegawai;
        
        // Update display fields
        document.getElementById('jumlahPegawai').value = jumlahPegawai;
        document.getElementById('tarifPerJamJabatan').value = tarifPerJam;
        document.getElementById('tarifBTKL').value = tarifBTKL;
        
        // Calculate biaya per produk
        calculateBiayaPerProduk();
        
        // Show calculation info
        showCalculationInfo(jumlahPegawai, tarifPerJam, tarifBTKL);
    } else {
        // Clear fields
        document.getElementById('jumlahPegawai').value = '';
        document.getElementById('tarifPerJamJabatan').value = '';
        document.getElementById('tarifBTKL').value = '';
        document.getElementById('biayaPerProduk').value = '';
        hideCalculationInfo();
    }
}

/**
 * Calculate biaya per produk based on tarif BTKL and kapasitas
 */
function calculateBiayaPerProduk() {
    const tarifBTKL = parseFloat(document.getElementById('tarifBTKL').value) || 0;
    const kapasitas = parseFloat(document.getElementById('kapasitasPerJam').value) || 0;
    
    if (tarifBTKL > 0 && kapasitas > 0) {
        const biayaPerProduk = tarifBTKL / kapasitas;
        document.getElementById('biayaPerProduk').value = biayaPerProduk.toFixed(2);
        
        // Show calculation info
        showBiayaCalculationInfo(tarifBTKL, kapasitas, biayaPerProduk);
    } else {
        document.getElementById('biayaPerProduk').value = '';
        hideBiayaCalculationInfo();
    }
}

/**
 * Show calculation information
 */
function showCalculationInfo(jumlahPegawai, tarifPerJam, tarifBTKL) {
    // Remove existing info if any
    hideCalculationInfo();
    
    const infoDiv = document.createElement('div');
    infoDiv.id = 'calculationInfo';
    infoDiv.className = 'alert alert-info mt-2';
    infoDiv.innerHTML = `
        <i class="fas fa-calculator me-2"></i>
        <strong>Perhitungan Tarif BTKL:</strong><br>
        ${jumlahPegawai} pegawai × Rp ${formatNumber(tarifPerJam)}/jam = <strong>Rp ${formatNumber(tarifBTKL)}/jam</strong>
    `;
    
    document.getElementById('tarifBTKL').parentNode.parentNode.appendChild(infoDiv);
}

/**
 * Hide calculation information
 */
function hideCalculationInfo() {
    const existingInfo = document.getElementById('calculationInfo');
    if (existingInfo) {
        existingInfo.remove();
    }
}

/**
 * Show biaya per produk calculation information
 */
function showBiayaCalculationInfo(tarifBTKL, kapasitas, biayaPerProduk) {
    // Remove existing info if any
    hideBiayaCalculationInfo();
    
    const infoDiv = document.createElement('div');
    infoDiv.id = 'biayaCalculationInfo';
    infoDiv.className = 'alert alert-success mt-2';
    infoDiv.innerHTML = `
        <i class="fas fa-chart-line me-2"></i>
        <strong>Perhitungan Biaya per Produk:</strong><br>
        Rp ${formatNumber(tarifBTKL)}/jam ÷ ${kapasitas} unit/jam = <strong>Rp ${formatNumber(biayaPerProduk)}/unit</strong>
    `;
    
    document.getElementById('biayaPerProduk').parentNode.parentNode.appendChild(infoDiv);
}

/**
 * Hide biaya calculation information
 */
function hideBiayaCalculationInfo() {
    const existingInfo = document.getElementById('biayaCalculationInfo');
    if (existingInfo) {
        existingInfo.remove();
    }
}

/**
 * Format number with thousand separators, removing unnecessary decimals
 */
function formatNumber(num) {
    // If it's a whole number, show without decimals
    if (num == Math.floor(num)) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num);
    }
    
    // Format with up to 2 decimals, removing trailing zeros
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(num);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/proses-produksi/create.blade.php ENDPATH**/ ?>
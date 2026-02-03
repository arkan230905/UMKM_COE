<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>Tambah BOP Proses
        </h2>
        <a href="<?php echo e(route('master-data.bop.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus me-2"></i>Form Tambah BOP Proses
            </h5>
            <small class="text-muted">Input komponen BOP per jam untuk proses produksi</small>
        </div>
        <div class="card-body">
            <style>
                .form-floating > .form-control:focus ~ label,
                .form-floating > .form-control:not(:placeholder-shown) ~ label {
                    opacity: .65;
                    transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
                }
            </style>
            
            <form action="<?php echo e(route('master-data.bop.store-proses')); ?>" method="POST" id="createBopForm">
                <?php echo csrf_field(); ?>
                
                <!-- Pilih Proses BTKL -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-cogs me-2"></i>Pilih Proses BTKL
                                </h6>
                                <div class="form-floating">
                                    <select class="form-select <?php $__errorArgs = ['proses_produksi_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="proses_produksi_id" 
                                            name="proses_produksi_id' 
                                            required
                                            onchange="updateProsesInfo()">
                                        <option value="">Pilih Proses BTKL</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableProses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($proses->id); ?>" 
                                                    data-kode="<?php echo e($proses->kode_proses); ?>"
                                                    data-nama="<?php echo e($proses->nama_proses); ?>"
                                                    data-tarif="<?php echo e($proses->tarif_per_jam); ?>"
                                                    data-kapasitas="<?php echo e($proses->kapasitas_per_jam); ?>"
                                                    data-satuan="<?php echo e($proses->satuan_btkl); ?>"
                                                    <?php echo e(old('proses_produksi_id') == $proses->id ? 'selected' : ''); ?>>
                                                <?php echo e($proses->kode_proses); ?> - <?php echo e($proses->nama_proses); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <label for="proses_produksi_id">Proses BTKL *</label>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['proses_produksi_id'];
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
                                
                                <!-- Info Proses -->
                                <div id="prosesInfo" class="mt-3" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Kode Proses:</small>
                                            <div class="fw-semibold" id="infokode">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Tarif BTKL:</small>
                                            <div class="fw-semibold" id="infotarif">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Kapasitas/Jam:</small>
                                            <div class="fw-semibold text-info" id="infokapasitas">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Satuan:</small>
                                            <div class="fw-semibold" id="infosatuan">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Komponen BOP per Jam -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-3">
                            <i class="fas fa-list me-2"></i>Komponen BOP per Jam (Rp)
                        </h6>
                        <small class="text-muted">Pilih akun beban dan masukkan nominal per jam</small>
                    </div>
                </div>

                <!-- Dynamic BOP Components -->
                <div id="bopComponentsContainer">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $akunBeban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $akun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row mb-3 bop-component-row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="akun_beban[]" required>
                                        <option value="<?php echo e($akun->kode_akun); ?>" selected><?php echo e($akun->kode_akun); ?> - <?php echo e($akun->nama_akun); ?></option>
                                    </select>
                                    <label for="akun_beban_<?php echo e($index); ?>">
                                        <i class="fas fa-coins text-warning me-1"></i><?php echo e($akun->nama_akun); ?>

                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" 
                                           class="form-control <?php $__errorArgs = ['nominal_per_jam.' . $index];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="nominal_per_jam_<?php echo e($index); ?>" 
                                           name="nominal_per_jam[]" 
                                           value="<?php echo e(old('nominal_per_jam.' . $index, 0)); ?>" 
                                           min="0" 
                                           step="1000"
                                           placeholder="0"
                                           oninput="calculateTotal()">
                                    <label for="nominal_per_jam_<?php echo e($index); ?>">
                                        <i class="fas fa-calculator text-primary me-1"></i>Nominal per Jam (Rp)
                                    </label>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nominal_per_jam.' . $index];
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
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Total Calculation -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="mb-0">Total BOP per Jam:</h6>
                                        <div class="fs-4 text-primary fw-bold" id="totalPerJam">Rp 0</div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-0">Budget (8 jam/shift):</h6>
                                        <div class="fs-4 text-success fw-bold" id="totalBudget">Rp 0</div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-0">Per Shift:</h6>
                                        <div class="text-muted">8 jam</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Simpan BOP Proses
                        </button>
                        <a href="<?php echo e(route('master-data.bop.index')); ?>" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateProsesInfo() {
    const select = document.getElementById('proses_produksi_id');
    const selectedOption = select.options[select.selectedIndex];
    const prosesInfo = document.getElementById('prosesInfo');
    
    if (selectedOption.value) {
        document.getElementById('infokode').textContent = selectedOption.dataset.kode;
        document.getElementById('infotarif').textContent = 'Rp ' + parseInt(selectedOption.dataset.tarif).toLocaleString('id-ID');
        document.getElementById('infokapasitas').textContent = selectedOption.dataset.kapasitas + ' unit/jam';
        document.getElementById('infosatuan').textContent = selectedOption.dataset.satuan;
        
        prosesInfo.style.display = 'block';
        calculateTotal();
    } else {
        prosesInfo.style.display = 'none';
    }
}

function calculateTotal() {
    // Get all nominal inputs
    const nominalInputs = document.querySelectorAll('input[name="nominal_per_jam[]"]');
    let total = 0;
    
    nominalInputs.forEach(function(input) {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    // Update displays
    document.getElementById('totalPerJam').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('totalBudget').textContent = 'Rp ' + (total * 8).toLocaleString('id-ID');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProsesInfo();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/bop/create-proses.blade.php ENDPATH**/ ?>
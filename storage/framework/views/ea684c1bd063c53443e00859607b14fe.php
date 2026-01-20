

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
                                            name="proses_produksi_id" 
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
                    </div>
                </div>

                <div class="row">
                    <!-- Listrik Mesin -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['listrik_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="listrik_per_jam" 
                                   name="listrik_per_jam" 
                                   value="<?php echo e(old('listrik_per_jam', 0)); ?>" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="listrik_per_jam">
                                <i class="fas fa-bolt text-warning me-1"></i>Listrik Mesin per Jam *
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['listrik_per_jam'];
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

                    <!-- Gas/BBM -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['gas_bbm_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="gas_bbm_per_jam" 
                                   name="gas_bbm_per_jam" 
                                   value="<?php echo e(old('gas_bbm_per_jam', 0)); ?>" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="gas_bbm_per_jam">
                                <i class="fas fa-fire text-danger me-1"></i>Gas / BBM per Jam *
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['gas_bbm_per_jam'];
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

                    <!-- Penyusutan Mesin -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['penyusutan_mesin_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="penyusutan_mesin_per_jam" 
                                   name="penyusutan_mesin_per_jam" 
                                   value="<?php echo e(old('penyusutan_mesin_per_jam', 0)); ?>" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="penyusutan_mesin_per_jam">
                                <i class="fas fa-chart-line-down text-secondary me-1"></i>Penyusutan Mesin per Jam *
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['penyusutan_mesin_per_jam'];
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

                    <!-- Maintenance -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['maintenance_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="maintenance_per_jam" 
                                   name="maintenance_per_jam" 
                                   value="<?php echo e(old('maintenance_per_jam', 0)); ?>" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="maintenance_per_jam">
                                <i class="fas fa-tools text-primary me-1"></i>Maintenance per Jam *
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['maintenance_per_jam'];
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

                    <!-- Gaji Mandor -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['gaji_mandor_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="gaji_mandor_per_jam" 
                                   name="gaji_mandor_per_jam" 
                                   value="<?php echo e(old('gaji_mandor_per_jam', 0)); ?>" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()"
                                   required>
                            <label for="gaji_mandor_per_jam">
                                <i class="fas fa-user-tie text-success me-1"></i>Gaji Mandor per Jam *
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['gaji_mandor_per_jam'];
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

                    <!-- Lain-lain -->
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['lain_lain_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="lain_lain_per_jam" 
                                   name="lain_lain_per_jam" 
                                   value="<?php echo e(old('lain_lain_per_jam', 0)); ?>" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   oninput="calculateTotal()">
                            <label for="lain_lain_per_jam">
                                <i class="fas fa-ellipsis-h text-muted me-1"></i>Lain-lain per Jam
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['lain_lain_per_jam'];
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

                <!-- Summary Perhitungan -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan BOP
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small>Total BOP per Jam:</small>
                                        <div class="h5" id="totalBopPerJam">Rp 0</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small>Kapasitas per Jam:</small>
                                        <div class="h5" id="kapasitasPerJam">0 unit</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small>BOP per Unit:</small>
                                        <div class="h5" id="bopPerUnit">Rp 0</div>
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
    const listrik = parseFloat(document.getElementById('listrik_per_jam').value) || 0;
    const gas = parseFloat(document.getElementById('gas_bbm_per_jam').value) || 0;
    const penyusutan = parseFloat(document.getElementById('penyusutan_mesin_per_jam').value) || 0;
    const maintenance = parseFloat(document.getElementById('maintenance_per_jam').value) || 0;
    const mandor = parseFloat(document.getElementById('gaji_mandor_per_jam').value) || 0;
    const lainLain = parseFloat(document.getElementById('lain_lain_per_jam').value) || 0;
    
    const total = listrik + gas + penyusutan + maintenance + mandor + lainLain;
    
    const select = document.getElementById('proses_produksi_id');
    const selectedOption = select.options[select.selectedIndex];
    const kapasitas = selectedOption.value ? parseFloat(selectedOption.dataset.kapasitas) : 0;
    
    const bopPerUnit = kapasitas > 0 ? total / kapasitas : 0;
    
    document.getElementById('totalBopPerJam').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('kapasitasPerJam').textContent = kapasitas + ' unit';
    document.getElementById('bopPerUnit').textContent = 'Rp ' + bopPerUnit.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProsesInfo();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop/create-proses.blade.php ENDPATH**/ ?>
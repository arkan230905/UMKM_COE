<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0 text-white">Input Budget BOP</h2>
        <a href="<?php echo e(route('master-data.bop.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Form Input Budget BOP</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('master-data.bop.store')); ?>" method="POST" id="bopForm">
                <?php echo csrf_field(); ?>
                
                <!-- Akun BOP yang Dipilih -->
                <div class="alert alert-info mb-4" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="fas fa-info-circle me-2"></i>Akun BOP yang Dipilih:
                    </h6>
                    <h5 class="mb-0 fw-bold text-primary" id="selected_akun_display">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('kode_akun')): ?>
                            <?php echo e(request('kode_akun')); ?> - <?php echo e($akunBeban->where('kode_akun', request('kode_akun'))->first()->nama_akun ?? ''); ?>

                        <?php else: ?>
                            Pilih akun dari dropdown di bawah
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </h5>
                </div>

                <!-- Pilih Akun BOP -->
                <div class="mb-3">
                    <label for="kode_akun" class="form-label fw-bold text-white">
                        Akun BOP <span class="text-danger">*</span>
                    </label>
                    <select class="form-select bg-dark text-white <?php $__errorArgs = ['kode_akun'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                            id="kode_akun" 
                            name="kode_akun" 
                            required
                            onchange="updateSelectedAkun()">
                        <option value="">-- Pilih Akun BOP --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $akunBeban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $akun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($akun->kode_akun); ?>" 
                                    data-nama="<?php echo e($akun->nama_akun); ?>"
                                    <?php echo e(old('kode_akun', request('kode_akun')) == $akun->kode_akun ? 'selected' : ''); ?>>
                                <?php echo e($akun->kode_akun); ?> - <?php echo e($akun->nama_akun); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kode_akun'];
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

                <!-- Nominal Budget -->
                <div class="mb-3">
                    <label for="budget_display" class="form-label fw-bold text-white">
                        Nominal Budget <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-success text-white fw-bold">Rp</span>
                        <input type="text" 
                               class="form-control form-control-lg bg-dark text-white money-input <?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="budget_display" 
                               value="<?php echo e(old('budget') ? number_format(old('budget'), 0, ',', '.') : ''); ?>"
                               placeholder="Ketik nominal..."
                               style="color: #ffffff !important; font-size: 1.25rem; font-weight: 500;"
                               required>
                        <input type="hidden" name="budget" id="budget" value="<?php echo e(old('budget', 0)); ?>">
                    </div>
                    <small class="text-success money-hint" style="font-size: 0.95rem;"></small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Keterangan -->
                <div class="mb-4">
                    <label for="keterangan" class="form-label fw-bold text-white">
                        Keterangan <small class="text-white">(Opsional)</small>
                    </label>
                    <textarea class="form-control bg-dark text-white <?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                              id="keterangan" 
                              name="keterangan" 
                              rows="3"
                              placeholder="Tambahkan keterangan jika diperlukan..."
                              style="color: #ffffff !important;"><?php echo e(old('keterangan')); ?></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['keterangan'];
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

                <!-- Tombol Aksi -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-1"></i> Simpan Budget
                    </button>
                    <a href="<?php echo e(route('master-data.bop.index')); ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function updateSelectedAkun() {
        const select = document.getElementById('kode_akun');
        const selectedOption = select.options[select.selectedIndex];
        const display = document.getElementById('selected_akun_display');
        
        if (selectedOption.value) {
            const kodeAkun = selectedOption.value;
            const namaAkun = selectedOption.getAttribute('data-nama');
            display.textContent = kodeAkun + ' - ' + namaAkun;
        } else {
            display.textContent = 'Pilih akun dari dropdown di bawah';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Money formatting functions
        const formatID = (val) => {
            if (val === null || val === undefined || val === '') return '';
            let v = String(val).replace(/[^0-9]/g, '');
            if (!v) return '';
            return v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };
        
        const toNumber = (formatted) => {
            if (!formatted) return 0;
            let s = String(formatted).replace(/\./g, '');
            let n = parseInt(s, 10);
            return isNaN(n) ? 0 : n;
        };
        
        const compactID = (n) => {
            if (n === 0) return '';
            const u = [
                {v:1e12, s:' triliun'},
                {v:1e9, s:' miliar'},
                {v:1e6, s:' juta'},
                {v:1e3, s:' ribu'},
            ];
            for (const it of u) {
                if (n >= it.v) {
                    const val = (n / it.v).toFixed(2).replace(/\.00$/,'').replace(/\.0$/,'');
                    return '= ' + val + it.s;
                }
            }
            return '= ' + n + ' rupiah';
        };

        // Setup money input
        const displayInput = document.getElementById('budget_display');
        const hiddenInput = document.getElementById('budget');
        const hint = document.querySelector('.money-hint');
        
        const updateHint = () => {
            const num = toNumber(displayInput.value);
            hint.textContent = compactID(num);
        };
        
        displayInput.addEventListener('input', function() {
            const num = toNumber(this.value);
            this.value = formatID(num);
            hiddenInput.value = num;
            updateHint();
        });
        
        displayInput.addEventListener('blur', function() {
            const num = toNumber(this.value);
            this.value = formatID(num);
            hiddenInput.value = num;
            updateHint();
        });
        
        // Initialize
        if (displayInput.value) {
            displayInput.value = formatID(toNumber(displayInput.value));
            updateHint();
        }

        // Validasi form sebelum submit
        document.getElementById('bopForm').addEventListener('submit', function(e) {
            // Pastikan hidden input terupdate
            const displayVal = document.getElementById('budget_display').value;
            const num = toNumber(displayVal);
            document.getElementById('budget').value = num;
            
            if (!num || num <= 0) {
                e.preventDefault();
                alert('Nominal budget harus diisi dan lebih dari 0');
                document.getElementById('budget_display').focus();
                return false;
            }
            
            // Tampilkan loading
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop/create.blade.php ENDPATH**/ ?>
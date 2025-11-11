<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0 text-white">Input Budget BOP</h2>
        <a href="<?php echo e(route('master-data.bop.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

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
                        <?php if(request('kode_akun')): ?>
                            <?php echo e(request('kode_akun')); ?> - <?php echo e($akunBeban->where('kode_akun', request('kode_akun'))->first()->nama_akun ?? ''); ?>

                        <?php else: ?>
                            Pilih akun dari dropdown di bawah
                        <?php endif; ?>
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
                        <?php $__currentLoopData = $akunBeban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $akun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($akun->kode_akun); ?>" 
                                    data-nama="<?php echo e($akun->nama_akun); ?>"
                                    <?php echo e(old('kode_akun', request('kode_akun')) == $akun->kode_akun ? 'selected' : ''); ?>>
                                <?php echo e($akun->kode_akun); ?> - <?php echo e($akun->nama_akun); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['kode_akun'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Nominal Budget -->
                <div class="mb-3">
                    <label for="budget" class="form-label fw-bold text-white">
                        Nominal Budget <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-success text-white fw-bold">Rp</span>
                        <input type="number" 
                               class="form-control form-control-lg bg-dark text-white <?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="budget" 
                               name="budget" 
                               value="<?php echo e(old('budget')); ?>"
                               placeholder="Masukkan nominal budget..."
                               style="color: #ffffff !important;"
                               min="0"
                               step="1"
                               required>
                    </div>
                    <small class="text-white">
                        <i class="fas fa-lightbulb me-1"></i>Contoh: 10000000 untuk Rp 10.000.000
                    </small>
                    <?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                    <?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
        // Format angka pada input budget
        const budgetInput = document.getElementById('budget');
        
        budgetInput.addEventListener('input', function(e) {
            // Hapus karakter non-digit
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Validasi form sebelum submit
        document.getElementById('bopForm').addEventListener('submit', function(e) {
            const budget = document.getElementById('budget').value;
            
            if (!budget || parseFloat(budget) <= 0) {
                e.preventDefault();
                alert('Nominal budget harus lebih dari 0');
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/bop/create.blade.php ENDPATH**/ ?>
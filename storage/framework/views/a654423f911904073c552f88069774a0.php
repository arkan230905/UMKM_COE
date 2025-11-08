<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i> Edit Budget BOP
                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('master-data.bop.update', $bop->id)); ?>" method="POST" id="editBopForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kode_akun" class="form-label fw-bold">Kode Akun</label>
                                <input type="text" class="form-control bg-light" 
                                       value="<?php echo e($bop->coa->kode_akun); ?> - <?php echo e($bop->coa->nama_akun); ?>" 
                                       readonly>
                                <input type="hidden" name="kode_akun" value="<?php echo e($bop->kode_akun); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="budget" class="form-label fw-bold">Nominal Budget <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="text" 
                                           class="form-control border-0 border-bottom border-2" 
                                           id="budget" 
                                           name="budget" 
                                           value="<?php echo e(number_format($bop->budget, 0, ',', '.')); ?>" 
                                           required
                                           onkeyup="formatAngka(this)" 
                                           onblur="formatAngka(this, 'blur')"
                                           onfocus="formatAngka(this, 'focus')"
                                           style="font-weight: 500;">
                                </div>
                                <input type="hidden" name="budget_value" id="budget_value" value="<?php echo e($bop->budget); ?>">
                                <?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="keterangan" class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" rows="3" class="form-control"><?php echo e(old('keterangan', $bop->keterangan)); ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo e(route('master-data.bop.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Format angka untuk input budget
    function formatAngka(input, eventType = '') {
        // Jika sedang fokus, tampilkan angka biasa
        if (eventType === 'focus') {
            let value = input.value.replace(/\./g, '');
            document.getElementById('budget_value').value = value || '0';
            input.value = value;
            return;
        }
        
        // Jika blur atau keyup, format angkanya
        let value = input.value.replace(/\./g, '');
        
        // Pastikan value adalah angka
        if (isNaN(value) || value === '') {
            value = '0';
        }
        
        // Pastikan nilai tidak negatif
        value = Math.max(0, parseFloat(value));
        
        // Pastikan nilai tidak melebihi maksimum safe integer
        value = Math.min(value, Number.MAX_SAFE_INTEGER || 9007199254740991);
        
        // Simpan nilai asli ke hidden input
        document.getElementById('budget_value').value = value;
        
        // Format angka dengan pemisah ribuan
        if (eventType !== 'focus') {
            input.value = value.toLocaleString('id-ID');
        }
    }

    // Validasi form sebelum submit
    document.getElementById('editBopForm').addEventListener('submit', function(e) {
        // Pastikan budget_value diupdate dengan nilai terbaru
        const budgetInput = document.getElementById('budget');
        const budgetValue = document.getElementById('budget_value');
        
        // Format ulang nilai sebelum submit
        let value = budgetInput.value.replace(/\./g, '');
        if (isNaN(value) || value === '') {
            value = '0';
        }
        value = Math.max(0, parseFloat(value));
        
        // Update nilai budget_value dengan format yang benar
        budgetValue.value = value;
        
        if (!value || parseFloat(value) <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Nominal budget harus lebih dari 0',
                confirmButtonColor: '#3085d6',
            });
            return false;
        }
        
        // Tampilkan loading
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
        }
        
        return true;
    });
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop/edit.blade.php ENDPATH**/ ?>
<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Master Data BOP (Biaya Overhead Pabrik)</h2>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Aktual</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $akunBeban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $akun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $bop = $bops[$akun->kode_akun] ?? null;
                                $hasBudget = $bop && $bop->budget > 0;
                                $sisa = $hasBudget ? ($bop->budget - ($bop->aktual ?? 0)) : 0;
                                $textClass = $sisa < 0 ? 'text-danger' : 'text-success';
                            ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($akun->kode_akun); ?></td>
                                <td><?php echo e($akun->nama_akun); ?></td>
                                <td class="text-end"><?php echo e($hasBudget ? number_format($bop->budget, 0, ',', '.') : '-'); ?></td>
                                <td class="text-end"><?php echo e($hasBudget ? number_format($bop->aktual ?? 0, 0, ',', '.') : '-'); ?></td>
                                <td class="text-end <?php echo e($textClass); ?>">
                                    <?php echo e($hasBudget ? number_format($sisa, 0, ',', '.') : '-'); ?>

                                </td>
                                <td class="text-center">
                                    <?php if($hasBudget): ?>
                                        <a href="<?php echo e(route('master-data.bop.edit', $bop->id)); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="<?php echo e(route('master-data.bop.destroy', $bop->id)); ?>" 
                                              method="POST" 
                                              class="d-inline delete-bop-form"
                                              data-bop-id="<?php echo e($bop->id); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="button" class="btn btn-sm btn-danger delete-bop-btn" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Hapus Budget">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#addBopModal"
                                                data-kode-akun="<?php echo e($akun->kode_akun); ?>"
                                                data-nama-akun="<?php echo e($akun->nama_akun); ?>">
                                            <i class="fas fa-plus"></i> Input
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data akun beban</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Budget -->
<div class="modal fade" id="addBopModal" tabindex="-1" aria-labelledby="addBopModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBopForm" action="<?php echo e(route('master-data.bop.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addBopModalLabel">Input Budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <div class="mb-3">
                        <label class="form-label">Akun Beban</label>
                        <input type="text" class="form-control" id="selected_akun_nama" readonly>
                        <input type="hidden" name="kode_akun" id="selected_akun_kode">
                    </div>
                    
                    <div class="mb-3">
                        <label for="budget" class="form-label">Nominal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="budget" name="budget" required
                                   onkeyup="formatAngka(this)" 
                                   onblur="formatAngka(this, 'blur')"
                                   onfocus="formatAngka(this, 'focus')">
                        </div>
                        <input type="hidden" name="budget_value" id="budget_value">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Validasi form sebelum submit
    function validateForm(form) {
        const budgetInput = form.querySelector('input[name="budget"]');
        const budgetValue = budgetInput.value.replace(/\./g, '');
        
        if (!budgetValue || parseFloat(budgetValue) <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Nominal budget harus lebih dari 0',
                confirmButtonColor: '#3085d6',
            });
            return false;
        }
        
        // Tampilkan loading
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
        }
        
        return true;
    }

    // Inisialisasi modal tambah
    document.addEventListener('DOMContentLoaded', function() {
        
        // Handle modal tambah
        var addBopModal = document.getElementById('addBopModal');
        if (addBopModal) {
            addBopModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var kodeAkun = button.getAttribute('data-kode-akun');
                var namaAkun = button.getAttribute('data-nama-akun');
                
                // Set nilai form
                document.getElementById('selected_akun_kode').value = kodeAkun;
                document.getElementById('selected_akun_nama').value = kodeAkun + ' - ' + namaAkun;
                
                // Reset form
                var form = document.getElementById('addBopForm');
                form.reset();
                document.getElementById('budget_value').value = '0';
                
                // Fokus ke input budget
                setTimeout(function() {
                    document.getElementById('budget').focus();
                }, 500);
            });
        }
        
        // Handle submit form
        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // Pastikan nilai budget yang dikirim adalah angka tanpa format
                var budgetInput = this.querySelector('input[name="budget"]');
                if (budgetInput) {
                    var budgetValue = budgetInput.value.replace(/\./g, '');
                    budgetInput.value = budgetValue;
                }
                
                // Validasi minimal 1
                var budgetValueInput = this.querySelector('input[name="budget_value"]');
                if (budgetValueInput && parseFloat(budgetValueInput.value) <= 0) {
                    e.preventDefault();
                    alert('Nominal budget harus lebih dari 0');
                    return false;
                }
                
                // Tampilkan loading
                var submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                }
                
                return true;
            });
        });
    });
    
    // Format angka untuk input budget
    function formatAngka(input, eventType = '') {
        // Jika sedang fokus, tampilkan angka biasa
        if (eventType === 'focus') {
            let value = input.value.replace(/\./g, '');
            // Simpan nilai asli ke hidden input
            if (input.id === 'budget') {
                document.getElementById('budget_value').value = value || '0';
            } else if (input.id === 'edit_budget') {
                document.getElementById('edit_budget_value').value = value || '0';
            }
            input.value = value;
            return;
        }
        
        // Jika blur atau keyup, format angkanya
        let value = input.value.replace(/\./g, '');
        
        // Pastikan value adalah angka
        if (isNaN(value) || value === '') {
            value = '0';
        }
        
        // Simpan nilai asli ke hidden input
        if (input.id === 'budget') {
            document.getElementById('budget_value').value = value;
        } else if (input.id === 'edit_budget') {
            document.getElementById('edit_budget_value').value = value;
        }
        
        // Format angka dengan pemisah ribuan
        if (eventType !== 'focus') {
            input.value = parseFloat(value).toLocaleString('id-ID');
        }
        
        // Jika blur atau keyup, format angkanya
        let value = input.value.replace(/\./g, '');
            });
        }
    // Inisialisasi format angka
    document.addEventListener('DOMContentLoaded', function() {
        // Format angka untuk input budget
        document.querySelectorAll('input[type="text"][name="budget"]').forEach(function(input) {
            input.addEventListener('keyup', function(e) {
                formatAngka(this);
            });
        });
        
        // Format angka saat form disubmit
        const form = document.getElementById('addBopForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const budgetInput = document.getElementById('budget');
                if (budgetInput) {
                    // Bersihkan format angka sebelum submit
                    budgetInput.value = budgetInput.value.replace(/\./g, '');
                }
            });
        }

        // Inisialisasi modal edit budget
        var editBopModal = document.getElementById('editBopModal');
        if (editBopModal) {
            editBopModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var budget = button.getAttribute('data-budget');
                var keterangan = button.getAttribute('data-keterangan');
                var namaAkun = button.closest('tr').querySelector('td:nth-child(3)').textContent;
                
                var modal = this;
                modal.querySelector('#editBopForm').action = '/master-data/bop/' + id;
                modal.querySelector('#edit_nama_akun').value = namaAkun.trim();
                modal.querySelector('#edit_budget').value = budget;
                modal.querySelector('#edit_keterangan').value = keterangan || '';
            });
        }

        // Format input number dengan pemisah ribuan
        document.querySelectorAll('input[type="number"]').forEach(function(input) {
            input.addEventListener('change', function() {
                this.value = parseFloat(this.value).toFixed(2);
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop/index.blade.php ENDPATH**/ ?>
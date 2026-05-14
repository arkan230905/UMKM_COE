<?php $__env->startSection('title', 'Daftar Pelanggan'); ?>

<?php $__env->startSection('content'); ?>
<style>
.password-text {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    color: #6c757d;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    min-width: 140px;
    display: inline-block;
}
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-users me-2"></i>Pelanggan
        </h2>
        <a href="<?php echo e(route('master-data.pelanggan.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Pelanggan
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Pelanggan
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Password</th>
                            <th>Total Pesanan</th>
                            <th>Terdaftar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pelanggans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pelanggan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e(($pelanggans->currentPage() - 1) * $pelanggans->perPage() + $loop->iteration); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($pelanggan->nama_pelanggan); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($pelanggan->email ?? '-'); ?></td>

                                <td><?php echo e($pelanggan->telepon ?? '-'); ?></td>
                                <td><?php echo e($pelanggan->alamat ?? '-'); ?></td>
<td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pelanggan->password): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="password-text" data-password="<?php echo e($pelanggan->password); ?>"><?php echo e($pelanggan->password); ?></span>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary toggle-password" 
                                                    onclick="togglePassword(this)"
                                                    title="Sembunyikan password">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info" 
                                                    onclick="copyPassword('<?php echo e($pelanggan->password); ?>')"
                                                    title="Copy password">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">0</span>
                                </td>
                                <td><?php echo e($pelanggan->created_at->format('d/m/Y')); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('master-data.pelanggan.edit', $pelanggan->id)); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('master-data.pelanggan.destroy', $pelanggan->id)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pelanggan</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                <?php echo e($pelanggans->links()); ?>

            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Reset Password Pelanggan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <p>Reset password untuk: <strong id="resetPelangganName"></strong></p>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">Password Baru</label>
                        <input type="password" name="password" id="new_password" class="form-control" 
                               placeholder="Masukkan password baru minimal 6 karakter" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-bold">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="form-control" placeholder="Ulangi password baru" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Password baru akan langsung aktif untuk pelanggan ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-1"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(button) {
    const passwordText = button.parentElement.querySelector('.password-text');
    const icon = button.querySelector('i');
    const actualPassword = passwordText.getAttribute('data-password');
    
    if (passwordText.textContent === '••••••••') {
        // Show actual password
        passwordText.textContent = actualPassword;
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        // Hide password
        passwordText.textContent = '••••••••';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function copyPassword(password) {
    // Copy to clipboard
    navigator.clipboard.writeText(password).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalIcon;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-info');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy password: ', err);
        
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = password;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        // Show success message
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalIcon;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-info');
        }, 2000);
    });
}

function resetPassword(pelangganId, pelangganName) {
    document.getElementById('resetPelangganName').textContent = pelangganName;
    document.getElementById('resetPasswordForm').action = `/master-data/pelanggan/${pelangganId}/reset-password`;
    
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}

document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
            location.reload();
        } else {
            alert('Gagal reset password: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat reset password');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Toggle password visibility
function togglePassword(button) {
    const passwordSpan = button.parentElement.querySelector('.password-text');
    const icon = button.querySelector('i');
    
    if (passwordSpan.textContent !== '••••••••') {
        // Currently visible, hide it
        passwordSpan.textContent = '••••••••';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.setAttribute('title', 'Lihat password');
    } else {
        // Currently hidden, show it
        passwordSpan.textContent = passwordSpan.getAttribute('data-password');
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.setAttribute('title', 'Sembunyikan password');
    }
}

// Copy password to clipboard
function copyPassword(password) {
    navigator.clipboard.writeText(password).then(() => {
        // Show success feedback
        const button = event.target.closest('button');
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.remove('btn-outline-info');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-info');
        }, 2000);
    }).catch(err => {
        console.error('Gagal copy password:', err);
        alert('Gagal copy password');
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/pelanggan/index.blade.php ENDPATH**/ ?>
<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-user me-2"></i>Profil Saya
                    </h3>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Profile Photo Section -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div id="photoPreview" class="position-relative">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->profile_photo): ?>
                                    <img src="<?php echo e(asset('storage/profile-photos/' . Auth::user()->profile_photo)); ?>" 
                                         alt="Profile Photo" 
                                         class="rounded-circle border border-4 border-white shadow-lg"
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         onerror="console.log('Image load error'); this.style.display='none'; document.getElementById('defaultAvatar').style.display='flex';">
                                <?php else: ?>
                                    <div id="defaultAvatar" class="rounded-circle bg-light border border-4 border-white shadow-lg d-flex align-items-center justify-content-center"
                                         style="width: 150px; height: 150px;">
                                        <i class="fas fa-user text-muted" style="font-size: 60px;"></i>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="position-absolute bottom-0 end-0">
                                <label for="profile_photo" 
                                       class="btn btn-primary rounded-circle btn-sm"
                                       style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <i class="fas fa-camera" style="font-size: 14px;"></i>
                                </label>
                                <input type="file" 
                                       id="profile_photo" 
                                       name="profile_photo" 
                                       class="d-none" 
                                       accept="image/*"
                                       onchange="handlePhotoSelect(event)">
                            </div>
                        </div>
                        <h5 class="mt-3 mb-1"><?php echo e(Auth::user()->name); ?></h5>
                        <p class="text-muted"><?php echo e(ucfirst(Auth::user()->role)); ?></p>
                        
                        <!-- Photo Actions -->
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Format: JPG, PNG, GIF (Maks: 2MB)
                            </small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->profile_photo): ?>
                                <form action="<?php echo e(route('profil-admin.remove-photo')); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                                        <i class="fas fa-trash me-1"></i>Hapus Foto
                                    </button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- Profile Form -->
                    <form action="<?php echo e(route('profil-admin.update')); ?>" method="POST" enctype="multipart/form-data" id="profileForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           class="form-control" 
                                           value="<?php echo e(Auth::user()->name); ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" 
                                           name="username" 
                                           id="username" 
                                           class="form-control" 
                                           value="<?php echo e(Auth::user()->username); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control" 
                                           value="<?php echo e(Auth::user()->email); ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone" 
                                           class="form-control" 
                                           value="<?php echo e(Auth::user()->phone); ?>" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" onclick="validateAndSubmit(event)">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handlePhotoSelect(event) {
    const file = event.target.files[0];
    console.log('Photo selected:', file);
    
    if (file) {
        console.log('File info:', {
            name: file.name,
            type: file.type,
            size: file.size
        });
        
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Harap pilih file gambar (JPG, PNG, GIF)');
            event.target.value = '';
            return;
        }
        
        // Validate file size (2MB)
        if (file.size > 2048 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB');
            event.target.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            console.log('FileReader loaded, updating preview');
            const photoPreview = document.getElementById('photoPreview');
            photoPreview.innerHTML = `
                <img src="${e.target.result}" 
                     alt="Profile Photo Preview" 
                     class="rounded-circle border border-4 border-white shadow-lg"
                     style="width: 150px; height: 150px; object-fit: cover;"
                     onerror="console.log('Preview image load error');">
            `;
        };
        reader.readAsDataURL(file);
    }
}

function validateAndSubmit(event) {
    console.log('Form submission triggered');
    
    const photoInput = document.getElementById('profile_photo');
    const photoFile = photoInput.files[0];
    
    console.log('Photo file on submit:', photoFile);
    
    if (photoFile) {
        console.log('Photo file info:', {
            name: photoFile.name,
            type: photoFile.type,
            size: photoFile.size
        });
    } else {
        console.log('No photo file selected on submit');
    }
    
    // Let the form submit normally
    return true;
}

// Debug on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile page loaded');
    console.log('Photo input element:', document.getElementById('profile_photo'));
    console.log('Photo preview element:', document.getElementById('photoPreview'));
    console.log('Form element:', document.getElementById('profileForm'));
});
</script>

<style>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border-bottom: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 500;
}

.form-control {
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.position-absolute.bottom-0.end-0 {
    bottom: 5px;
    right: 5px;
}

.alert {
    border-radius: 10px;
    border: none;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/profile/index.blade.php ENDPATH**/ ?>
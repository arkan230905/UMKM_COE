<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UMKM Digital</title>
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>" type="image/x-icon">
    
    <!-- Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Video background */
        video#bg-video {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            filter: brightness(55%);
        }

        /* Overlay & form */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            width: 480px;
            max-width: 90vw;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            color: #fff;
        }

        .form-label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            border-radius: 8px;
            padding: 0.8rem 1rem;
            transition: all 0.3s;
            font-size: 0.875rem;
            height: 48px;
            line-height: 1.5;
            box-sizing: border-box;
            display: block;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
            color: #fff;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
            opacity: 1;
        }

        /* Dropdown options styling */
        .form-select option {
            background: rgba(0, 0, 0, 0.9);
            color: #fff;
            padding: 0.75rem;
            border: none;
        }

        .form-select option:hover {
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
        }

        /* Fix dropdown background */
        .form-select {
            background: rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
            height: 48px !important;
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #fff !important;
            height: 48px !important;
        }

        /* Better form spacing */
        .mb-3 {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
        }

        /* Remember me normal styling */
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
            margin: 0;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-check-input:checked {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .form-check-label {
            margin: 0;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.9);
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.875rem;
            height: 48px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            border: none;
            font-size: 0.875rem;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.9);
            color: white;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.9);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        .text-danger {
            color: #fca5a5;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Remove floating animation */
        .login-box {
            animation: none;
        }

        /* Input group styling */
        .mb-3 {
            margin-bottom: 1.25rem;
        }

        /* Remember me checkbox */
        .form-check {
            padding-left: 0;
            margin-bottom: 1rem;
        }

        .form-check-input {
            margin-top: 0.25rem;
        }

        /* Forgot password link */
        .text-center.mt-3 {
            margin-top: 1rem !important;
        }
    </style>
</head>
<body>

    <!-- Background video -->
    <video autoplay muted loop id="bg-video">
        <source src="<?php echo e(asset('umkm.mp4')); ?>" type="video/mp4">
    </video>

    <div class="login-container px-3">
        <div class="login-box">
            <h1>Login UMKM</h1>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                <div class="alert alert-success py-2 mb-3">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm">
                <?php echo csrf_field(); ?>
                
                <!-- Debug CSRF Token -->
                <input type="hidden" name="debug_token" value="<?php echo e(csrf_token()); ?>">
                
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any() && old('_token')): ?>
                <div class="alert alert-danger mb-3">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mb-3">
                    <label for="login_role" class="form-label">masuk ke halaman:</label>
                    <select id="login_role" name="login_role" class="form-select" required>
                        <option value="" selected disabled>Pilih halaman</option>
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
                        <option value="pegawai_pembelian">Pegawai Gudang</option>
                        <option value="kasir">Kasir</option>
                        <option value="pelanggan">Pelanggan</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['login_role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div id="login-fields" style="display: none;">
                    <!-- Field Email (hanya untuk owner, admin, pelanggan, pegawai, kasir) -->
                    <div id="email_field" class="mb-3" style="display: none;">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-control">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- Field Password (hanya untuk owner, admin, pelanggan) -->
                    <div id="password_field" class="mb-3" style="display: none;">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password" class="form-control">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="mb-3 form-check" style="display: none;">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="loginButton">LOGIN</button>

                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('password.request')); ?>">Forgot your password?</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Login page loaded');
            
            const loginRoleSelect = document.getElementById('login_role');
            
            if (!loginRoleSelect) {
                console.error('Login role select not found');
                return;
            }
            
            loginRoleSelect.addEventListener('change', function() {
                console.log('Role changed to:', this.value);
                
                const role = this.value;
                const loginFields = document.getElementById('login-fields');
                const emailField = document.getElementById('email_field');
                const passwordField = document.getElementById('password_field');
                const rememberMeField = document.querySelector('.form-check');
                
                // Pastikan semua elemen ada
                if (!loginFields || !emailField || !passwordField || !rememberMeField) {
                    console.error('Some form elements not found');
                    return;
                }
                
                // Reset semua field
                loginFields.style.display = 'none';
                emailField.style.display = 'none';
                passwordField.style.display = 'none';
                rememberMeField.style.display = 'none';
                
                // Reset required attributes
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                // Remove all required first
                if (emailInput) emailInput.removeAttribute('required');
                if (passwordInput) passwordInput.removeAttribute('required');
                
                // Tampilkan field sesuai role
                if (role !== '') {
                    loginFields.style.display = 'block';
                    emailField.style.display = 'block';
                    
                    if (emailInput) {
                        emailInput.setAttribute('required', 'required');
                    }

                    // Role admin, pegawai, kasir: tanpa password
                    if (role === 'admin' || role === 'pegawai_pembelian' || role === 'kasir') {
                        // no extra fields
                    }
                    // Role owner dan pelanggan: dengan password
                    else if (role === 'owner' || role === 'pelanggan') {
                        passwordField.style.display = 'block';
                        rememberMeField.style.display = 'flex';
                        if (passwordInput) {
                            passwordInput.setAttribute('required', 'required');
                        }
                    }

                    if (emailInput) {
                        setTimeout(() => emailInput.focus(), 100);
                    }
                }
            });

            // Auto-select role if there's an error
            <?php if(old('login_role')): ?>
                const oldRole = '<?php echo e(old('login_role')); ?>';
                if (loginRoleSelect) {
                    loginRoleSelect.value = oldRole;
                    loginRoleSelect.dispatchEvent(new Event('change'));
                }
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            // Debug form submission - JANGAN PREVENT DEFAULT
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                console.log('Login form found, attaching submit handler');
                
                loginForm.addEventListener('submit', function(e) {
                    console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
                    
                    // Validasi basic
                    const role = document.getElementById('login_role').value;
                    console.log('Selected role:', role);
                    
                    if (!role) {
                        e.preventDefault();
                        alert('Silakan pilih role terlebih dahulu');
                        console.log('Form submission prevented: no role selected');
                        return false;
                    }

                    // Validasi email untuk role selain presensi
                    {
                        const email = document.getElementById('email').value;
                        if (!email) {
                            e.preventDefault();
                            alert('Email wajib diisi');
                            console.log('Form submission prevented: no email');
                            return false;
                        }
                    }

                    // Validasi password untuk owner dan pelanggan
                    if (['owner', 'pelanggan'].includes(role)) {
                        const password = document.getElementById('password');
                        if (!password || !password.value || password.value.trim() === '') {
                            e.preventDefault();
                            alert('Password wajib diisi');
                            console.log('Form submission prevented: no password');
                            if (password) password.focus();
                            return false;
                        }
                    }

                    // Log form data
                    const formData = new FormData(loginForm);
                    console.log('Form data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`  ${key}: ${value}`);
                    }
                    
                    console.log('Form validation passed, allowing submission...');
                    // JANGAN e.preventDefault() di sini - biarkan form submit secara normal
                    return true;
                });
            } else {
                console.error('Login form not found!');
            }
        });
    </script>
    
    <!-- Bootstrap JS dari CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php /**PATH C:\UMKM_COE\resources\views/auth/login.blade.php ENDPATH**/ ?>
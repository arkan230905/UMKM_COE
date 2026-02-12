<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UMKM Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .form-control, .form-select {
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .field-group {
            margin-bottom: 20px;
        }
        .field-group.hidden {
            display: none;
        }
        .alert {
            border-radius: 8px;
            border: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Login UMKM</h1>
            <p class="text-muted">Silakan pilih role dan masukkan data Anda</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <!-- Role Selection -->
            <div class="field-group">
                <label class="form-label">Masuk sebagai:</label>
                <select id="login_role" name="login_role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="owner" {{ old('login_role') == 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="admin" {{ old('login_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="pegawai_pembelian" {{ old('login_role') == 'pegawai_pembelian' ? 'selected' : '' }}>Pegawai Gudang</option>
                    <option value="kasir" {{ old('login_role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="presensi" {{ old('login_role') == 'presensi' ? 'selected' : '' }}>Presensi Pegawai</option>
                </select>
            </div>

            <!-- Kode Perusahaan -->
            <div class="field-group hidden" id="kode_field">
                <label class="form-label">Kode Perusahaan:</label>
                <input type="text" id="kode_perusahaan" name="kode_perusahaan" class="form-control" 
                       placeholder="Masukkan kode perusahaan" value="{{ old('kode_perusahaan', 'UMKM-COE12') }}">
            </div>

            <!-- Email -->
            <div class="field-group hidden" id="email_field">
                <label class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="Masukkan email" value="{{ old('email') }}">
            </div>

            <!-- Password -->
            <div class="field-group hidden" id="password_field">
                <label class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Masukkan password">
            </div>

            <!-- Submit Button -->
            <div class="field-group">
                <button type="submit" class="btn btn-login w-100" id="submitBtn">
                    LOGIN
                </button>
            </div>

            <!-- Links -->
            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-decoration-none">Lupa password?</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('login_role');
            const kodeField = document.getElementById('kode_field');
            const emailField = document.getElementById('email_field');
            const passwordField = document.getElementById('password_field');
            const submitBtn = document.getElementById('submitBtn');

            function updateFields() {
                const role = roleSelect.value;
                console.log('Role selected:', role);

                // Hide all fields first
                kodeField.classList.add('hidden');
                emailField.classList.add('hidden');
                passwordField.classList.add('hidden');

                // Remove required attributes
                document.getElementById('kode_perusahaan').removeAttribute('required');
                document.getElementById('email').removeAttribute('required');
                document.getElementById('password').removeAttribute('required');

                if (!role) return;

                // Show fields based on role
                if (role === 'presensi') {
                    // Presensi: only kode perusahaan
                    kodeField.classList.remove('hidden');
                    document.getElementById('kode_perusahaan').setAttribute('required', 'required');
                    submitBtn.textContent = 'MASUK KE PRESENSI';
                } else {
                    // Other roles: email required
                    emailField.classList.remove('hidden');
                    document.getElementById('email').setAttribute('required', 'required');
                    submitBtn.textContent = 'LOGIN';

                    // Roles that need kode perusahaan
                    if (['owner', 'admin', 'pegawai_pembelian', 'kasir'].includes(role)) {
                        kodeField.classList.remove('hidden');
                        document.getElementById('kode_perusahaan').setAttribute('required', 'required');
                    }

                    // Roles that need password
                    if (['owner'].includes(role)) {
                        passwordField.classList.remove('hidden');
                        document.getElementById('password').setAttribute('required', 'required');
                    }
                }
            }

            roleSelect.addEventListener('change', updateFields);

            // Initialize on page load
            if (roleSelect.value) {
                updateFields();
            }

            // Form submission logging
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                console.log('=== FORM SUBMIT ===');
                const formData = new FormData(this);
                console.log('Form Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
            });
        });
    </script>
</body>
</html>
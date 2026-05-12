<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background: #0056b3;
        }
        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .hidden {
            display: none;
        }
        #debug {
            margin-top: 20px;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>Simple Login Test</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="form-group">
            <label for="login_role">Role:</label>
            <select id="login_role" name="login_role" required>
                <option value="">Pilih Role</option>
                <option value="owner">Owner</option>
                <option value="admin">Admin</option>
                <option value="pegawai_pembelian">Pegawai Gudang</option>
                <option value="kasir">Kasir</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="{{ old('email') }}">
        </div>

        <div class="form-group hidden" id="kode_perusahaan_group">
            <label for="kode_perusahaan">Kode Perusahaan:</label>
            <input type="text" id="kode_perusahaan" name="kode_perusahaan" value="{{ old('kode_perusahaan', 'UMKM-COE12') }}">
        </div>

        <div class="form-group hidden" id="password_group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
        </div>

        <button type="submit" id="submitBtn">LOGIN</button>
    </form>

    <div id="debug">
        <strong>Debug Info:</strong>
        <div id="debugContent">Select a role to see debug info</div>
    </div>

    <script>
        console.log('Simple login page loaded');

        const roleSelect = document.getElementById('login_role');
        const kodeGroup = document.getElementById('kode_perusahaan_group');
        const passwordGroup = document.getElementById('password_group');
        const kodeInput = document.getElementById('kode_perusahaan');
        const passwordInput = document.getElementById('password');
        const debugContent = document.getElementById('debugContent');
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        roleSelect.addEventListener('change', function() {
            const role = this.value;
            console.log('Role selected:', role);

            // Reset
            kodeGroup.classList.add('hidden');
            passwordGroup.classList.add('hidden');
            kodeInput.removeAttribute('required');
            passwordInput.removeAttribute('required');

            let debugInfo = `Role: ${role}<br>`;

            if (role === 'owner') {
                kodeGroup.classList.remove('hidden');
                passwordGroup.classList.remove('hidden');
                kodeInput.setAttribute('required', 'required');
                passwordInput.setAttribute('required', 'required');
                debugInfo += 'Fields: Email, Kode Perusahaan, Password';
            } else if (role === 'admin' || role === 'pegawai_pembelian' || role === 'kasir') {
                kodeGroup.classList.remove('hidden');
                kodeInput.setAttribute('required', 'required');
                debugInfo += 'Fields: Email, Kode Perusahaan (no password)';
            } else if (role === 'owner') {
            }

            debugContent.innerHTML = debugInfo;
        });

        form.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT ===');
            console.log('Action:', form.action);
            console.log('Method:', form.method);
            
            const formData = new FormData(form);
            console.log('Form Data:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }

            submitBtn.textContent = 'LOADING...';
            submitBtn.disabled = true;

            // Let form submit naturally
            return true;
        });

        // Auto-select role if there's an error
        @if (old('login_role'))
            roleSelect.value = '{{ old('login_role') }}';
            roleSelect.dispatchEvent(new Event('change'));
        @endif
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .field-status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }
        .field-status.visible {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .field-status.hidden {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        #console-log {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login Debug Mode</h1>
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="debug-box">
                    <h3>Login Form</h3>
                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        <div class="mb-3">
                            <label>Role:</label>
                            <select id="login_role" name="login_role" class="form-select" required>
                                <option value="">Pilih Role</option>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>

                        <div id="kode_field" class="mb-3" style="display: none;">
                            <label>Kode Perusahaan:</label>
                            <input type="text" id="kode_perusahaan" name="kode_perusahaan" class="form-control" value="UMKM-COE12">
                            <small class="text-muted">Default: UMKM-COE12</small>
                        </div>

                        <div id="email_field" class="mb-3" style="display: none;">
                            <label>Email:</label>
                            <input type="email" id="email" name="email" class="form-control" value="abiyyu123@gmail.com">
                            <small class="text-muted">Default: abiyyu123@gmail.com</small>
                        </div>

                        <div id="password_field" class="mb-3" style="display: none;">
                            <label>Password:</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">LOGIN</button>
                    </form>
                </div>

                <div class="debug-box">
                    <h4>Field Status</h4>
                    <div id="field-status"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="debug-box">
                    <h4>Console Log</h4>
                    <div id="console-log">Waiting for action...</div>
                </div>

                <div class="debug-box">
                    <h4>Form Data Preview</h4>
                    <div id="form-preview" style="font-family: monospace; font-size: 12px;">
                        Select a role to see form data
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const consoleLog = document.getElementById('console-log');
        const fieldStatus = document.getElementById('field-status');
        const formPreview = document.getElementById('form-preview');

        function log(message) {
            const timestamp = new Date().toLocaleTimeString();
            consoleLog.innerHTML += `[${timestamp}] ${message}\n`;
            consoleLog.scrollTop = consoleLog.scrollHeight;
            console.log(message);
        }

        function updateFieldStatus() {
            const kodeField = document.getElementById('kode_field');
            const emailField = document.getElementById('email_field');
            const passwordField = document.getElementById('password_field');
            
            const kodeInput = document.getElementById('kode_perusahaan');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            let html = '';
            
            html += `<div class="field-status ${kodeField.style.display !== 'none' ? 'visible' : 'hidden'}">`;
            html += `Kode Perusahaan: ${kodeField.style.display !== 'none' ? 'VISIBLE' : 'HIDDEN'}<br>`;
            html += `Value: "${kodeInput.value}"<br>`;
            html += `Name: "${kodeInput.name}"`;
            html += `</div>`;

            html += `<div class="field-status ${emailField.style.display !== 'none' ? 'visible' : 'hidden'}">`;
            html += `Email: ${emailField.style.display !== 'none' ? 'VISIBLE' : 'HIDDEN'}<br>`;
            html += `Value: "${emailInput.value}"<br>`;
            html += `Name: "${emailInput.name}"`;
            html += `</div>`;

            html += `<div class="field-status ${passwordField.style.display !== 'none' ? 'visible' : 'hidden'}">`;
            html += `Password: ${passwordField.style.display !== 'none' ? 'VISIBLE' : 'HIDDEN'}<br>`;
            html += `Value: "${passwordInput.value ? '***' : '(empty)'}"<br>`;
            html += `Name: "${passwordInput.name}"`;
            html += `</div>`;

            fieldStatus.innerHTML = html;
        }

        function updateFormPreview() {
            const formData = new FormData(document.getElementById('loginForm'));
            let html = '<strong>Data yang akan dikirim:</strong><br><br>';
            
            for (let [key, value] of formData.entries()) {
                html += `${key}: ${value}<br>`;
            }

            formPreview.innerHTML = html;
        }

        document.getElementById('login_role').addEventListener('change', function() {
            const role = this.value;
            log(`Role changed to: ${role}`);

            const kodeField = document.getElementById('kode_field');
            const emailField = document.getElementById('email_field');
            const passwordField = document.getElementById('password_field');

            // Reset
            kodeField.style.display = 'none';
            emailField.style.display = 'none';
            passwordField.style.display = 'none';

            if (role === 'admin') {
                kodeField.style.display = 'block';
                emailField.style.display = 'block';
                log('Showing: Kode Perusahaan + Email');
            } else if (role === 'owner') {
                kodeField.style.display = 'block';
                emailField.style.display = 'block';
                passwordField.style.display = 'block';
                log('Showing: Kode Perusahaan + Email + Password');
            }
            }

            updateFieldStatus();
            updateFormPreview();
        });

        // Update preview on input change
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', updateFormPreview);
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            log('=== FORM SUBMIT ===');
            
            const formData = new FormData(this);
            log('Form Data:');
            for (let [key, value] of formData.entries()) {
                log(`  ${key}: ${value}`);
            }

            const kodeInput = document.getElementById('kode_perusahaan');
            log(`Kode field value: "${kodeInput.value}"`);
            log(`Kode field name: "${kodeInput.name}"`);
            log(`Kode field visible: ${document.getElementById('kode_field').style.display !== 'none'}`);

            // Don't prevent - let it submit
            log('Submitting to server...');
        });

        log('Debug page loaded');
        updateFieldStatus();
        updateFormPreview();
    </script>
</body>
</html>
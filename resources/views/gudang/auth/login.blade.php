<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pegawai Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { max-width: 400px; margin: 0 auto; }
        .login-card .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .login-card .card-header { background: #17a2b8; color: #fff; border-radius: 15px 15px 0 0 !important; padding: 25px; text-align: center; }
        .login-card .card-header i { font-size: 48px; margin-bottom: 10px; }
        .login-card .card-body { padding: 30px; }
        .form-control { border-radius: 8px; padding: 12px 15px; }
        .btn-login { background: #17a2b8; border: none; border-radius: 8px; padding: 12px; font-size: 16px; font-weight: 600; }
        .btn-login:hover { background: #138496; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-warehouse d-block"></i>
                    <h4 class="mb-0">Login Pegawai Gudang</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('gudang.login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope me-1"></i> Email Pegawai</label>
                            <input type="email" name="email" class="form-control" placeholder="Masukkan email pegawai gudang" value="{{ old('email') }}" required autofocus>
                            <small class="text-muted">Gunakan email yang terdaftar di data pegawai</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-building me-1"></i> Kode Perusahaan</label>
                            <input type="text" name="kode_perusahaan" class="form-control" placeholder="Masukkan kode perusahaan" value="{{ old('kode_perusahaan') }}" required>
                        </div>
                        <button type="submit" class="btn btn-login btn-info text-white w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <small class="text-muted">Hanya pegawai dengan jabatan Bagian Gudang yang bisa login</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

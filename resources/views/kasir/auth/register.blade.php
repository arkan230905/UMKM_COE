<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); min-height: 100vh; display: flex; align-items: center; }
        .register-card { max-width: 400px; margin: 0 auto; }
        .register-card .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .register-card .card-header { background: #20c997; color: #fff; border-radius: 15px 15px 0 0 !important; padding: 25px; text-align: center; }
        .register-card .card-header i { font-size: 48px; margin-bottom: 10px; }
        .register-card .card-body { padding: 30px; }
        .form-control { border-radius: 8px; padding: 12px 15px; }
        .btn-register { background: #20c997; border: none; border-radius: 8px; padding: 12px; font-size: 16px; font-weight: 600; }
        .btn-register:hover { background: #1aa179; }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus d-block"></i>
                    <h4 class="mb-0">Daftar Kasir Baru</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('kasir.register.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user me-1"></i> Nama Kasir</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" value="{{ old('nama') }}" required autofocus>
                            <small class="text-muted">Nama ini akan digunakan untuk login</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-building me-1"></i> Kode Perusahaan</label>
                            <input type="text" name="kode_perusahaan" class="form-control" placeholder="Minta kode dari owner/admin" value="{{ old('kode_perusahaan') }}" required>
                            <small class="text-muted">Hubungi owner untuk mendapatkan kode</small>
                        </div>
                        <button type="submit" class="btn btn-register btn-info w-100 text-white mb-3">
                            <i class="fas fa-check-circle me-2"></i>Daftar
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <span class="text-muted">Sudah terdaftar?</span>
                        <a href="{{ route('kasir.login') }}" class="text-success fw-bold">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

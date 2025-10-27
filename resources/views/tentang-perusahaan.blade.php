@extends('layouts.app')

@section('content')
<div class="container py-5" style="background-color: #1e1e2f; min-height: 100vh;">
    <div class="card shadow-lg border-0" style="background-color: #2c2c3e; color: white; border-radius: 20px;">
        <div class="card-body p-5">
            <h2 class="mb-4 text-center">🏢 Tentang Perusahaan</h2>

            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            <!-- TAMPILAN DATA -->
            <div id="info-section">
                <h5>Nama Perusahaan</h5>
                <p>{{ $dataPerusahaan->nama }}</p>

                <h5>Alamat</h5>
                <p>{{ $dataPerusahaan->alamat }}</p>

                <h5>Email</h5>
                <p>{{ $dataPerusahaan->email }}</p>

                <h5>Telepon</h5>
                <p>{{ $dataPerusahaan->telepon }}</p>

                <div class="text-center mt-4">
                    <button id="btnEdit" class="btn btn-warning">✏️ Edit Data</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light">← Kembali ke Dashboard</a>
                </div>
            </div>

            <!-- FORM EDIT -->
            <div id="edit-section" style="display:none;">
                <form action="{{ route('tentang-perusahaan.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Nama Perusahaan</label>
                        <input type="text" name="nama" class="form-control" value="{{ $dataPerusahaan->nama }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" required>{{ $dataPerusahaan->alamat }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $dataPerusahaan->email }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Telepon</label>
                        <input type="text" name="telepon" class="form-control" value="{{ $dataPerusahaan->telepon }}" required>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                        <button type="button" id="btnBatal" class="btn btn-outline-light">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT TOGGLE -->
<script>
document.getElementById('btnEdit').addEventListener('click', function() {
    document.getElementById('info-section').style.display = 'none';
    document.getElementById('edit-section').style.display = 'block';
});

document.getElementById('btnBatal').addEventListener('click', function() {
    document.getElementById('edit-section').style.display = 'none';
    document.getElementById('info-section').style.display = 'block';
});
</script>
@endsection

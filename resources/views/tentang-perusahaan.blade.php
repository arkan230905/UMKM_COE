@extends('layouts.app')

@section('content')
<div class="container py-5" style="background-color: #1e1e2f; min-height: 100vh;">
    <div class="card shadow-lg border-0" style="background-color: #2c2c3e; color: white; border-radius: 20px;">
        <div class="card-body p-5">
            <h2 class="mb-4 text-center">🏢 Tentang Perusahaan</h2>

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

                @if(!empty($dataPerusahaan->kode))
                    <h5>Kode Perusahaan</h5>
                    <p><span class="badge bg-info text-dark">{{ $dataPerusahaan->kode }}</span></p>
                @endif

                <!-- INFO REKENING SECTION -->
                <div class="mt-4">
                    <div class="card" style="background-color: #3c3c4e; border: 1px solid #4c4c5e;">
                        <div class="card-header" style="background-color: #4c4c5e; border-bottom: 1px solid #5c5c6e;">
                            <h5 class="mb-0">
                                <button class="btn btn-link text-white text-decoration-none w-100 text-start d-flex justify-content-between align-items-center" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#infoRekeningCollapse" 
                                        aria-expanded="false" 
                                        aria-controls="infoRekeningCollapse">
                                    <span><i class="fas fa-university me-2"></i>Informasi Rekening Bank</span>
                                    <i class="fas fa-chevron-down" id="chevronIcon"></i>
                                </button>
                            </h5>
                        </div>
                        <div class="collapse" id="infoRekeningCollapse">
                            <div class="card-body">
                                @php
                                    // Ambil data rekening bank dari database
                                    $bankAccounts = collect();
                                    try {
                                        $bankAccounts = \App\Models\Coa::where('tipe_akun', 'asset')
                                            ->where('kategori_akun', 'kas_bank')
                                            ->whereNotNull('nomor_rekening')
                                            ->get();
                                    } catch (Exception $e) {
                                        // Fallback jika tabel tidak ada
                                    }
                                @endphp
                                
                                @if($bankAccounts->count() > 0)
                                    <div class="row">
                                        @foreach($bankAccounts as $bank)
                                            <div class="col-md-6 mb-3">
                                                <div class="card" style="background-color: #2c2c3e; border: 1px solid #4c4c5e;">
                                                    <div class="card-body">
                                                        <div class="mb-2">
                                                            <small class="text-muted">Bank</small>
                                                            <div class="fw-bold text-white">{{ $bank->nama_akun }}</div>
                                                        </div>
                                                        @if($bank->nomor_rekening)
                                                            <div class="mb-2">
                                                                <small class="text-muted">Nomor Rekening</small>
                                                                <div class="fw-bold font-monospace text-info">{{ $bank->nomor_rekening }}</div>
                                                            </div>
                                                        @endif
                                                        @if($bank->atas_nama)
                                                            <div class="mb-2">
                                                                <small class="text-muted">Atas Nama</small>
                                                                <div class="fw-bold text-white">{{ $bank->atas_nama }}</div>
                                                            </div>
                                                        @endif
                                                        <div class="mb-0">
                                                            <small class="text-muted">Kode Akun</small>
                                                            <div class="text-warning">{{ $bank->kode_akun }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-university fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Belum ada informasi rekening bank</h6>
                                        <p class="text-muted small">Tambahkan rekening bank melalui menu Chart of Accounts (COA)</p>
                                    </div>
                                @endif
                                
                                <div class="mt-3 pt-3 border-top" style="border-color: #4c4c5e !important;">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Informasi rekening diambil dari Chart of Accounts (COA) dengan kategori Kas & Bank
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INFORMASI REKENING SECTION -->
                <div class="mt-4">
                    <div class="card" style="background-color: #3c3c4e; border: 1px solid #4c4c5e;">
                        <div class="card-header" style="background-color: #4c4c5e; border-bottom: 1px solid #5c5c6e;">
                            <h5 class="mb-0">
                                <button class="btn btn-link text-white text-decoration-none w-100 text-start d-flex justify-content-between align-items-center" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#informasiRekeningCollapse" 
                                        aria-expanded="false" 
                                        aria-controls="informasiRekeningCollapse">
                                    <span><i class="fas fa-credit-card me-2"></i>Informasi Rekening</span>
                                    <i class="fas fa-chevron-down" id="chevronIcon2"></i>
                                </button>
                            </h5>
                        </div>
                        <div class="collapse" id="informasiRekeningCollapse">
                            <div class="card-body">
                                @php
                                    // Ambil data rekening bank dari database
                                    $allBankAccounts = collect();
                                    try {
                                        $allBankAccounts = \App\Models\Coa::where('tipe_akun', 'asset')
                                            ->where('kategori_akun', 'kas_bank')
                                            ->whereNotNull('nomor_rekening')
                                            ->get();
                                    } catch (Exception $e) {
                                        // Fallback jika tabel tidak ada
                                    }
                                @endphp
                                
                                @if($allBankAccounts->count() > 0)
                                    <div class="row">
                                        @foreach($allBankAccounts as $bank)
                                            <div class="col-md-6 mb-3">
                                                <div class="card" style="background-color: #2c2c3e; border: 1px solid #4c4c5e;">
                                                    <div class="card-body">
                                                        <div class="mb-2">
                                                            <small class="text-muted">Bank</small>
                                                            <div class="fw-bold text-white">{{ $bank->nama_akun }}</div>
                                                        </div>
                                                        @if($bank->nomor_rekening)
                                                            <div class="mb-2">
                                                                <small class="text-muted">Nomor Rekening</small>
                                                                <div class="fw-bold font-monospace text-info">{{ $bank->nomor_rekening }}</div>
                                                            </div>
                                                        @endif
                                                        @if($bank->atas_nama)
                                                            <div class="mb-2">
                                                                <small class="text-muted">Atas Nama</small>
                                                                <div class="fw-bold text-white">{{ $bank->atas_nama }}</div>
                                                            </div>
                                                        @endif
                                                        <div class="mb-0">
                                                            <small class="text-muted">Kode Akun</small>
                                                            <div class="text-warning">{{ $bank->kode_akun }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Belum ada informasi rekening</h6>
                                        <p class="text-muted small">Tambahkan rekening melalui menu Chart of Accounts (COA)</p>
                                    </div>
                                @endif
                                
                                <div class="mt-3 pt-3 border-top" style="border-color: #4c4c5e !important;">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Data rekening diambil dari Chart of Accounts (COA) dengan kategori Kas & Bank
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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

<script>
document.getElementById('btnEdit').addEventListener('click', function() {
    document.getElementById('info-section').style.display = 'none';
    document.getElementById('edit-section').style.display = 'block';
});
document.getElementById('btnBatal').addEventListener('click', function() {
    document.getElementById('edit-section').style.display = 'none';
    document.getElementById('info-section').style.display = 'block';
});

// Handle chevron icon rotation for collapse
document.addEventListener('DOMContentLoaded', function() {
    const collapseElement = document.getElementById('infoRekeningCollapse');
    const chevronIcon = document.getElementById('chevronIcon');
    
    if (collapseElement && chevronIcon) {
        collapseElement.addEventListener('show.bs.collapse', function() {
            chevronIcon.style.transform = 'rotate(180deg)';
            chevronIcon.style.transition = 'transform 0.3s ease';
        });
        
        collapseElement.addEventListener('hide.bs.collapse', function() {
            chevronIcon.style.transform = 'rotate(0deg)';
            chevronIcon.style.transition = 'transform 0.3s ease';
        });
    }
    
    // Handle second chevron icon for Informasi Rekening section
    const collapseElement2 = document.getElementById('informasiRekeningCollapse');
    const chevronIcon2 = document.getElementById('chevronIcon2');
    
    if (collapseElement2 && chevronIcon2) {
        collapseElement2.addEventListener('show.bs.collapse', function() {
            chevronIcon2.style.transform = 'rotate(180deg)';
            chevronIcon2.style.transition = 'transform 0.3s ease';
        });
        
        collapseElement2.addEventListener('hide.bs.collapse', function() {
            chevronIcon2.style.transform = 'rotate(0deg)';
            chevronIcon2.style.transition = 'transform 0.3s ease';
        });
    }
});
</script>
@endsection

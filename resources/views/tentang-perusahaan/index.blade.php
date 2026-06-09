@extends('layouts.app')

@section('title', 'Tentang Perusahaan')

@section('content')
<style>
    .theme-brown { color: #5C3D2E !important; }
    .theme-brown-light { color: #8A6B48 !important; }
    .bg-theme-brown { background-color: #5C3D2E !important; }
    .bg-theme-brown-light { background-color: #8A6B48 !important; }
    .bg-theme-brown-opacity { background-color: rgba(138, 107, 72, 0.1) !important; }
    .btn-theme-outline {
        color: #5C3D2E;
        border-color: #5C3D2E;
    }
    .btn-theme-outline:hover {
        background-color: #5C3D2E;
        color: white;
    }
    .accordion-button:not(.collapsed) {
        color: #5C3D2E;
        background-color: rgba(138, 107, 72, 0.05);
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }
    .accordion-button:focus {
        border-color: #8A6B48;
        box-shadow: 0 0 0 0.25rem rgba(138, 107, 72, 0.25);
    }
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #5C3D2E 0%, #8A6B48 100%); border-radius: 15px;">
                <div class="card-body p-4 text-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-white p-3 rounded-circle shadow-sm me-4 d-flex align-items-center justify-content-center theme-brown" style="width: 70px; height: 70px;">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-1 text-white fw-bold">{{ $dataPerusahaan->nama }}</h2>
                            <p class="mb-0 opacity-75"><i class="fas fa-map-marker-alt me-2"></i>Profil Resmi Perusahaan</p>
                        </div>
                    </div>
                    @if(auth()->user()->role === 'owner')
                        <a href="/tentang-perusahaan/edit" class="btn btn-light fw-bold px-4 py-2 theme-brown" style="border-radius: 8px;">
                            <i class="fas fa-edit me-2"></i>Edit Profil
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Info untuk admin bahwa ini adalah view-only -->
    @if(auth()->user()->role !== 'owner')
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" style="border-radius: 10px;">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Halaman ini bersifat read-only. Untuk mengubah data perusahaan, silakan hubungi owner.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 10px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ACCORDION CONTAINER -->
    <div class="accordion shadow-sm" id="accordionSections" style="border-radius: 15px; overflow: hidden; border: none;">
        
        <!-- INFORMASI PERUSAHAAN SECTION -->
        <div class="accordion-item border-0 border-bottom">
            <h2 class="accordion-header" id="headingPerusahaan">
                <button class="accordion-button bg-white text-dark fw-bold fs-5 py-4" type="button" data-bs-toggle="collapse" data-bs-target="#informasiPerusahaanCollapse" aria-expanded="true" aria-controls="informasiPerusahaanCollapse">
                    <i class="fas fa-id-card theme-brown-light me-3"></i>Informasi Umum Perusahaan
                </button>
            </h2>
            <div id="informasiPerusahaanCollapse" class="accordion-collapse collapse show" aria-labelledby="headingPerusahaan" data-bs-parent="#accordionSections">
                <div class="accordion-body p-4 bg-light">
                    <div class="row g-4">
                        <!-- Card: Nama Perusahaan -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-theme-brown-opacity p-2 rounded theme-brown-light me-3">
                                            <i class="fas fa-font fa-lg"></i>
                                        </div>
                                        <h6 class="text-muted mb-0 text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.8rem;">Nama Perusahaan</h6>
                                    </div>
                                    <h4 class="text-dark fw-bold mb-0 ps-5">{{ $dataPerusahaan->nama }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Alamat -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-theme-brown-opacity p-2 rounded theme-brown-light me-3">
                                            <i class="fas fa-map-marked-alt fa-lg"></i>
                                        </div>
                                        <h6 class="text-muted mb-0 text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.8rem;">Alamat Lengkap</h6>
                                    </div>
                                    <h5 class="text-dark fw-bold mb-0 ps-5" style="line-height: 1.5;">{{ $dataPerusahaan->alamat }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Email -->
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-theme-brown-opacity p-2 rounded theme-brown-light me-3">
                                            <i class="fas fa-envelope fa-lg"></i>
                                        </div>
                                        <h6 class="text-muted mb-0 text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.8rem;">Email Resmi</h6>
                                    </div>
                                    <h5 class="text-dark fw-bold mb-0 ps-5">{{ $dataPerusahaan->email }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Telepon -->
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-theme-brown-opacity p-2 rounded theme-brown-light me-3">
                                            <i class="fas fa-phone-alt fa-lg"></i>
                                        </div>
                                        <h6 class="text-muted mb-0 text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.8rem;">Nomor Telepon</h6>
                                    </div>
                                    <h5 class="text-dark fw-bold mb-0 ps-5">{{ $dataPerusahaan->telepon }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Kode Perusahaan -->
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4" style="background-color: #ffffff;">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-4 flex-shrink-0" style="width: 60px; height: 60px; background-color: #FAF4F0; color: #8A6B48;">
                                        <i class="fas fa-shield-alt fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-uppercase fw-bold mb-2" style="letter-spacing: 1px; font-size: 0.75rem; color: #333;">Kode Perusahaan</h6>
                                        @if($dataPerusahaan->kode)
                                            <div class="d-flex align-items-center rounded px-3 py-2 shadow-sm copy-badge" style="background-color: #FDF9F5; border: 1px solid #E6D8CB; cursor: pointer; transition: all 0.2s;" onclick="navigator.clipboard.writeText('{{ $dataPerusahaan->kode }}').then(() => { const btn = this.querySelector('i'); btn.className = 'fas fa-check text-success'; setTimeout(() => btn.className = 'far fa-copy', 2000); })">
                                                <span class="fw-bold me-3" style="color: #5C3D2E; font-size: 1rem; letter-spacing: 1px;">{{ $dataPerusahaan->kode }}</span>
                                                <i class="far fa-copy fs-5" style="color: #8A6B48; transition: all 0.2s;"></i>
                                            </div>
                                        @else
                                            <h5 class="text-muted mb-0 fst-italic">Belum diatur</h5>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-secondary border-0 d-flex align-items-center rounded-4 mb-0" style="background-color: #f1f3f5;">
                                <i class="fas fa-info-circle fa-2x me-3 text-secondary"></i>
                                <div>
                                    <p class="mb-1 text-dark"><strong>Kode Perusahaan</strong> digunakan untuk verifikasi login Pegawai dan Kasir.</p>
                                    <p class="mb-0 text-muted small">Akses edit halaman ini dibatasi eksklusif hanya untuk pemegang role <strong>Owner</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMASI REKENING SECTION -->
        <div class="accordion-item border-0">
            <h2 class="accordion-header" id="headingRekening">
                <button class="accordion-button collapsed bg-white text-dark fw-bold fs-5 py-4" type="button" data-bs-toggle="collapse" data-bs-target="#informasiRekeningCollapse" aria-expanded="false" aria-controls="informasiRekeningCollapse">
                    <i class="fas fa-university theme-brown-light me-3"></i>Informasi Rekening Bank
                </button>
            </h2>
            <div id="informasiRekeningCollapse" class="accordion-collapse collapse" aria-labelledby="headingRekening" data-bs-parent="#accordionSections">
                <div class="accordion-body p-4 bg-light">
                    @php
                        // Ambil bank dari COA (Aset -> nama bank)
                        $bankAccounts = collect();
                        try {
                            $bankAccounts = \App\Models\Coa::where('user_id', auth()->id())
                                ->whereIn('tipe_akun', ['Asset', 'asset', 'Aset', 'ASET', 'Aktiva'])
                                ->where('kode_akun', '!=', '111')
                                ->where(function($query) {
                                    $query->where('nama_akun', 'like', '%bank%')
                                          ->orWhere('kode_akun', 'like', '111%');
                                })
                                ->get();
                        } catch (Exception $e) { }
                    @endphp
                    
                    @if($bankAccounts->count() > 0)
                        <div class="row g-4">
                            @foreach($bankAccounts as $bank)
                            <div class="col-md-6">
                                <!-- Desain Kartu ATM Bank -->
                                <div class="card h-100 border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #4b3628 0%, #2a1e16 100%); color: #f8f9fa; overflow: hidden; position: relative;">
                                    <!-- Dekorasi background kartu -->
                                    <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                                    <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                                    
                                    <div class="card-body p-4 position-relative z-index-1">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h5 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 2px;">{{ $bank->nama_akun }}</h5>
                                            <i class="fas fa-credit-card fa-2x opacity-50" style="color: #d4af37;"></i>
                                        </div>
                                        
                                        <!-- Nomor Rekening -->
                                        <div class="mb-4">
                                            <small class="text-white-50 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Nomor Rekening</small>
                                            <div class="editable-field" data-field="nomor_rekening" data-coa-id="{{ $bank->id }}" data-value="{{ $bank->nomor_rekening ?? '' }}" style="cursor: pointer;">
                                                <div class="display-mode d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold fs-3 font-monospace" style="letter-spacing: 2px; color: #f8f9fa;">
                                                        {{ $bank->nomor_rekening ?: '0000 0000 0000' }}
                                                    </span>
                                                    <i class="fas fa-pen text-white-50 ms-2" style="font-size: 0.8rem;" title="Edit"></i>
                                                </div>
                                                <div class="edit-mode" style="display: none;">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control edit-input font-monospace text-center text-dark" value="{{ $bank->nomor_rekening ?? '' }}" placeholder="Masukkan no. rek">
                                                        <button class="btn btn-success save-btn px-3" type="button"><i class="fas fa-check"></i></button>
                                                        <button class="btn btn-danger cancel-btn px-3" type="button"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Atas Nama -->
                                        <div>
                                            <small class="text-white-50 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Atas Nama</small>
                                            <div class="editable-field" data-field="atas_nama" data-coa-id="{{ $bank->id }}" data-value="{{ $bank->atas_nama ?? '' }}" style="cursor: pointer;">
                                                <div class="display-mode d-flex align-items-center">
                                                    <span class="fw-bold fs-5 text-uppercase" style="letter-spacing: 1px; color: #f8f9fa;">
                                                        {{ $bank->atas_nama ?: 'NAMA PEMILIK' }}
                                                    </span>
                                                    <i class="fas fa-pen text-white-50 ms-3" style="font-size: 0.8rem;" title="Edit"></i>
                                                </div>
                                                <div class="edit-mode" style="display: none;">
                                                    <div class="input-group input-group-sm mt-1">
                                                        <input type="text" class="form-control edit-input text-uppercase text-dark" value="{{ $bank->atas_nama ?? '' }}" placeholder="Nama Pemilik">
                                                        <button class="btn btn-success save-btn px-3" type="button"><i class="fas fa-check"></i></button>
                                                        <button class="btn btn-danger cancel-btn px-3" type="button"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
                            <i class="fas fa-piggy-bank fa-4x text-muted mb-3 opacity-25"></i>
                            <h5 class="text-muted fw-bold">Belum Ada Rekening Bank</h5>
                            <p class="text-muted mb-0">Tambahkan akun kas/bank melalui menu Chart of Accounts (COA) untuk melihatnya di sini.</p>
                        </div>
                    @endif
                    
                    <div class="text-center mt-4">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="fas fa-info-circle me-1"></i> Data rekening bersumber otomatis dari Chart of Accounts
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="text-center mt-5">
        <a href="/dashboard" class="btn btn-theme-outline rounded-pill px-4 py-2 fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle inline editing for bank fields
    document.querySelectorAll('.editable-field').forEach(function(field) {
        const displayMode = field.querySelector('.display-mode');
        const editMode = field.querySelector('.edit-mode');
        const input = field.querySelector('.edit-input');
        const saveBtn = field.querySelector('.save-btn');
        const cancelBtn = field.querySelector('.cancel-btn');
        const displayText = field.querySelector('.display-mode span');
        
        // Cek permission: hanya owner yang bisa edit (diasumsikan tombol edit profil ada jika owner)
        @if(auth()->user()->role !== 'owner')
            field.style.cursor = 'default';
            const penIcon = field.querySelector('.fa-pen');
            if(penIcon) penIcon.style.display = 'none';
            return; // hentikan event listener jika bukan owner
        @endif
        
        displayMode.addEventListener('click', function() {
            displayMode.style.display = 'none';
            editMode.style.display = 'block';
            input.focus();
            input.select();
        });
        
        cancelBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            input.value = field.dataset.value;
            editMode.style.display = 'none';
            displayMode.style.display = 'flex';
        });
        
        saveBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            saveBankField(field, input.value);
        });
        
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveBankField(field, input.value);
            } else if (e.key === 'Escape') {
                cancelBtn.click();
            }
        });
    });
    
    function saveBankField(fieldElement, newValue) {
        const coaId = fieldElement.dataset.coaId;
        const fieldName = fieldElement.dataset.field;
        const displayText = fieldElement.querySelector('.display-mode span');
        const input = fieldElement.querySelector('.edit-input');
        const saveBtn = fieldElement.querySelector('.save-btn');
        const displayMode = fieldElement.querySelector('.display-mode');
        const editMode = fieldElement.querySelector('.edit-mode');
        
        const originalHtml = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        saveBtn.disabled = true;
        
        fetch('{{ route("tentang-perusahaan.update-bank-field") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                coa_id: coaId,
                field: fieldName,
                value: newValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fieldElement.dataset.value = newValue;
                if (newValue.trim() === '') {
                    displayText.textContent = fieldName === 'nomor_rekening' ? '0000 0000 0000' : 'NAMA PEMILIK';
                    displayText.style.opacity = '0.5';
                } else {
                    displayText.textContent = newValue;
                    displayText.style.opacity = '1';
                }
                showToast('success', 'Berhasil memperbarui data!');
            } else {
                showToast('error', data.message || 'Gagal menyimpan data');
                input.value = fieldElement.dataset.value;
            }
        })
        .catch(error => {
            showToast('error', 'Terjadi kesalahan jaringan');
            input.value = fieldElement.dataset.value;
        })
        .finally(() => {
            saveBtn.innerHTML = originalHtml;
            saveBtn.disabled = false;
            editMode.style.display = 'none';
            displayMode.style.display = 'flex';
        });
    }
    
    function showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} shadow-lg border-0 d-flex align-items-center position-fixed`;
        toast.style.cssText = 'top: 30px; right: 30px; z-index: 9999; border-radius: 12px; animation: slideIn 0.3s ease-out;';
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} fa-lg me-3"></i>
            <strong class="me-auto">${message}</strong>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease-in forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Add custom keyframes for toast animation
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.innerHTML = `
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes fadeOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        `;
        document.head.appendChild(style);
    }
});
</script>
@endpush
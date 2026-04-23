@extends('layouts.app')

@section('title', 'Tentang Perusahaan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-building me-2"></i>Tentang Perusahaan
        </h2>
        @if(auth()->user()->role === 'owner')
            <a href="/tentang-perusahaan/edit" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Data
            </a>
        @endif
    </div>

    <!-- Info untuk admin bahwa ini adalah view-only -->
    @if(auth()->user()->role !== 'owner')
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Halaman ini bersifat read-only. Untuk mengubah data perusahaan, silakan hubungi owner.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ACCORDION CONTAINER -->
    <div class="accordion" id="accordionSections">
        <!-- INFORMASI PERUSAHAAN SECTION -->
        <div class="card">
            <div class="card-header" style="cursor: pointer;" 
                 data-bs-toggle="collapse" 
                 data-bs-target="#informasiPerusahaanCollapse" 
                 aria-expanded="true" 
                 aria-controls="informasiPerusahaanCollapse">
                <h5 class="mb-0 d-flex justify-content-between align-items-center text-white">
                    <span><i class="fas fa-building me-2"></i>Informasi Perusahaan</span>
                    <i class="fas fa-chevron-down" id="chevronIconPerusahaan"></i>
                </h5>
            </div>
            <div class="collapse show" id="informasiPerusahaanCollapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="30%" class="bg-light">Nama Perusahaan</th>
                                            <td class="text-dark fw-bold fs-5">{{ $dataPerusahaan->nama }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Alamat</th>
                                            <td class="text-dark fw-bold fs-5">{{ $dataPerusahaan->alamat }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Email</th>
                                            <td class="text-dark fw-bold fs-5">{{ $dataPerusahaan->email }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Telepon</th>
                                            <td class="text-dark fw-bold fs-5">{{ $dataPerusahaan->telepon }}</td>
                                        </tr>
                                        @if($dataPerusahaan->kode)
                                            <tr>
                                                <th class="bg-light">Kode Perusahaan</th>
                                                <td>
                                                    <span class="badge bg-primary fs-6">{{ $dataPerusahaan->kode }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Kode Perusahaan:</strong> Digunakan untuk login pegawai dan kasir
                                            </p>
                                            <p class="mb-2">
                                                <strong>Akses Edit:</strong> Hanya user dengan role Owner yang dapat mengubah data perusahaan
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Role yang Terhubung:</strong> Admin, Pegawai, Kasir
                                            </p>
                                            <p class="mb-2">
                                                <strong>Update Otomatis:</strong> Perubahan data akan langsung terupdate di seluruh sistem
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMASI REKENING SECTION -->
        <div class="card">
            <div class="card-header" style="cursor: pointer;" 
                 data-bs-toggle="collapse" 
                 data-bs-target="#informasiRekeningCollapse" 
                 aria-expanded="false" 
                 aria-controls="informasiRekeningCollapse">
                <h5 class="mb-0 d-flex justify-content-between align-items-center text-white">
                    <span><i class="fas fa-credit-card me-2"></i>Informasi Rekening</span>
                    <i class="fas fa-chevron-down" id="chevronIconRekening"></i>
                </h5>
            </div>
            <div class="collapse" id="informasiRekeningCollapse">
                <div class="card-body">
                    @php
                        // MASTER SOURCE: Ambil semua bank dari COA (Asset accounts yang mengandung kata "bank")
                        // Query ini juga digunakan di AccountHelper::getBankAccountsForTransfer()
                        // untuk memastikan konsistensi data di seluruh aplikasi
                        $bankAccounts = collect();
                        try {
                            $bankAccounts = \App\Models\Coa::where('tipe_akun', 'asset')
                                ->where(function($query) {
                                    $query->where('nama_akun', 'like', '%bank%')
                                          ->orWhere('kode_akun', '111');
                                })
                                ->get();
                        } catch (Exception $e) {
                            // Fallback jika tabel tidak ada
                        }
                    @endphp
                    
                    @if($bankAccounts->count() > 0)
                        <div class="row">
                            @foreach($bankAccounts as $bank)
                            <div class="col-md-6 mb-4">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <small class="text-muted fw-bold">Bank</small>
                                            <div class="fw-bold text-dark fs-5">{{ $bank->nama_akun }}</div>
                                        </div>
                                        
                                        <!-- Nomor Rekening - Inline Edit -->
                                        <div class="mb-3">
                                            <small class="text-muted fw-bold">Nomor Rekening</small>
                                            <div class="editable-field" 
                                                 data-field="nomor_rekening" 
                                                 data-coa-id="{{ $bank->id }}"
                                                 data-value="{{ $bank->nomor_rekening ?? '' }}"
                                                 style="cursor: pointer;">
                                                <div class="display-mode d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-dark fs-5 font-monospace">
                                                        {{ $bank->nomor_rekening ?: 'Klik untuk menambah nomor rekening' }}
                                                    </span>
                                                    <i class="fas fa-chevron-right text-muted"></i>
                                                </div>
                                                <div class="edit-mode" style="display: none;">
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control edit-input" 
                                                               value="{{ $bank->nomor_rekening ?? '' }}"
                                                               placeholder="Masukkan nomor rekening">
                                                        <button class="btn btn-success btn-sm save-btn" type="button">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-secondary btn-sm cancel-btn" type="button">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Atas Nama - Inline Edit -->
                                        <div class="mb-3">
                                            <small class="text-muted fw-bold">Atas Nama</small>
                                            <div class="editable-field" 
                                                 data-field="atas_nama" 
                                                 data-coa-id="{{ $bank->id }}"
                                                 data-value="{{ $bank->atas_nama ?? '' }}"
                                                 style="cursor: pointer;">
                                                <div class="display-mode d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-dark fs-5">
                                                        {{ $bank->atas_nama ?: 'Klik untuk menambah nama pemilik' }}
                                                    </span>
                                                    <i class="fas fa-chevron-right text-muted"></i>
                                                </div>
                                                <div class="edit-mode" style="display: none;">
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control edit-input" 
                                                               value="{{ $bank->atas_nama ?? '' }}"
                                                               placeholder="Masukkan nama pemilik rekening">
                                                        <button class="btn btn-success btn-sm save-btn" type="button">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-secondary btn-sm cancel-btn" type="button">
                                                            <i class="fas fa-times"></i>
                                                        </button>
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
                        <div class="text-center py-4">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Belum ada akun bank di COA</h6>
                            <p class="text-muted small">Tambahkan akun bank melalui menu Chart of Accounts (COA)</p>
                        </div>
                    @endif
                    
                    <div class="mt-3 pt-3 border-top border-light">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Nama bank diambil otomatis dari COA. Klik nomor rekening atau atas nama untuk mengedit.
                            <br>
                            <i class="fas fa-sync-alt me-1"></i>
                            Perubahan data di sini akan otomatis tersinkronisasi ke halaman pembayaran.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Custom accordion behavior - when one closes, the other opens
document.addEventListener('DOMContentLoaded', function() {
    const collapsePerusahaan = document.getElementById('informasiPerusahaanCollapse');
    const collapseRekening = document.getElementById('informasiRekeningCollapse');
    const chevronPerusahaan = document.getElementById('chevronIconPerusahaan');
    const chevronRekening = document.getElementById('chevronIconRekening');
    
    // Handle Informasi Perusahaan section
    if (collapsePerusahaan && chevronPerusahaan) {
        collapsePerusahaan.addEventListener('show.bs.collapse', function() {
            chevronPerusahaan.style.transform = 'rotate(180deg)';
            chevronPerusahaan.style.transition = 'transform 0.3s ease';
        });
        
        collapsePerusahaan.addEventListener('hide.bs.collapse', function() {
            chevronPerusahaan.style.transform = 'rotate(0deg)';
            chevronPerusahaan.style.transition = 'transform 0.3s ease';
            
            // When Perusahaan closes, open Rekening
            if (collapseRekening && !collapseRekening.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(collapseRekening, {
                    show: true
                });
            }
        });
    }
    
    // Handle Informasi Rekening section
    if (collapseRekening && chevronRekening) {
        collapseRekening.addEventListener('show.bs.collapse', function() {
            chevronRekening.style.transform = 'rotate(180deg)';
            chevronRekening.style.transition = 'transform 0.3s ease';
        });
        
        collapseRekening.addEventListener('hide.bs.collapse', function() {
            chevronRekening.style.transform = 'rotate(0deg)';
            chevronRekening.style.transition = 'transform 0.3s ease';
            
            // When Rekening closes, open Perusahaan
            if (collapsePerusahaan && !collapsePerusahaan.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(collapsePerusahaan, {
                    show: true
                });
            }
        });
    }
    
    // Handle inline editing for bank fields
    document.querySelectorAll('.editable-field').forEach(function(field) {
        const displayMode = field.querySelector('.display-mode');
        const editMode = field.querySelector('.edit-mode');
        const input = field.querySelector('.edit-input');
        const saveBtn = field.querySelector('.save-btn');
        const cancelBtn = field.querySelector('.cancel-btn');
        const displayText = field.querySelector('.display-mode span');
        
        // Click to edit
        displayMode.addEventListener('click', function() {
            displayMode.style.display = 'none';
            editMode.style.display = 'block';
            input.focus();
            input.select();
        });
        
        // Cancel edit
        cancelBtn.addEventListener('click', function() {
            input.value = field.dataset.value;
            editMode.style.display = 'none';
            displayMode.style.display = 'flex';
        });
        
        // Save edit
        saveBtn.addEventListener('click', function() {
            saveField(field, input.value);
        });
        
        // Save on Enter key
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                saveField(field, input.value);
            } else if (e.key === 'Escape') {
                cancelBtn.click();
            }
        });
    });
    
    function saveField(fieldElement, newValue) {
        const coaId = fieldElement.dataset.coaId;
        const fieldName = fieldElement.dataset.field;
        const displayText = fieldElement.querySelector('.display-mode span');
        const input = fieldElement.querySelector('.edit-input');
        const saveBtn = fieldElement.querySelector('.save-btn');
        const displayMode = fieldElement.querySelector('.display-mode');
        const editMode = fieldElement.querySelector('.edit-mode');
        
        // Show loading
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        saveBtn.disabled = true;
        
        // Send AJAX request
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
                // Update display
                fieldElement.dataset.value = newValue;
                if (newValue.trim() === '') {
                    displayText.textContent = fieldName === 'nomor_rekening' ? 
                        'Klik untuk menambah nomor rekening' : 
                        'Klik untuk menambah nama pemilik';
                    displayText.classList.add('text-muted');
                    displayText.classList.remove('text-dark');
                } else {
                    displayText.textContent = newValue;
                    displayText.classList.remove('text-muted');
                    displayText.classList.add('text-dark');
                }
                
                // Show success message
                showToast('success', 'Data berhasil diperbarui');
            } else {
                showToast('error', data.message || 'Terjadi kesalahan');
                input.value = fieldElement.dataset.value; // Reset value
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Terjadi kesalahan jaringan');
            input.value = fieldElement.dataset.value; // Reset value
        })
        .finally(() => {
            // Reset button and hide edit mode
            saveBtn.innerHTML = '<i class="fas fa-check"></i>';
            saveBtn.disabled = false;
            editMode.style.display = 'none';
            displayMode.style.display = 'flex';
        });
    }
    
    function showToast(type, message) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
});
</script>
@endpush
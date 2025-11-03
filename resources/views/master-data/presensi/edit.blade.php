@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #1b1b28; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-pencil-square me-2"></i> Edit Presensi
        </h2>
        <a href="{{ route('master-data.presensi.index') }}" class="btn btn-outline-light">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Terjadi kesalahan!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mx-3" style="background-color: #222232; border-radius: 15px;">
        <div class="card-body p-4">
            <form action="{{ route('master-data.presensi.update', $presensi->id) }}" method="POST" id="presensiForm">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Pilih Pegawai -->
                    <div class="col-md-6">
                        <label for="pegawai_id" class="form-label text-white">
                            <i class="bi bi-person-fill me-1"></i>Pegawai <span class="text-danger">*</span>
                        </label>
                        <select name="pegawai_id" id="pegawai_id" 
                            class="form-select bg-dark text-white border-dark @error('pegawai_id') is-invalid @enderror" 
                            required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" 
                                    {{ old('pegawai_id', $presensi->pegawai_id) == $pegawai->id ? 'selected' : '' }}
                                    class="text-white">
                                    {{ $pegawai->nama }} ({{ $pegawai->nomor_induk_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('pegawai_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Presensi -->
                    <div class="col-md-6">
                        <label for="tgl_presensi" class="form-label text-white">
                            <i class="bi bi-calendar-event me-1"></i>Tanggal <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tgl_presensi" id="tgl_presensi" 
                               class="form-control bg-dark text-white border-dark @error('tgl_presensi') is-invalid @enderror" 
                               value="{{ old('tgl_presensi', \Carbon\Carbon::parse($presensi->tgl_presensi)->format('Y-m-d')) }}" 
                               required
                               max="{{ now()->format('Y-m-d') }}">
                        @error('tgl_presensi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-12">
                        <label for="status" class="form-label text-white">
                            <i class="bi bi-info-circle me-1"></i>Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" id="status" 
                            class="form-select bg-dark text-white border-dark @error('status') is-invalid @enderror" 
                            required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Hadir" {{ old('status', $presensi->status) == 'Hadir' ? 'selected' : '' }} class="text-white">
                                Hadir
                            </option>
                            <option value="Izin" {{ old('status', $presensi->status) == 'Izin' ? 'selected' : '' }} class="text-white">
                                Izin
                            </option>
                            <option value="Sakit" {{ old('status', $presensi->status) == 'Sakit' ? 'selected' : '' }} class="text-white">
                                Sakit
                            </option>
                            <option value="Alpa" {{ old('status', $presensi->status) == 'Alpa' ? 'selected' : '' }} class="text-white">
                                Alpa
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jam Masuk -->
                    <div class="col-md-6 jam-field" style="{{ $presensi->status != 'Hadir' ? 'display: none;' : '' }}">
                        <label for="jam_masuk" class="form-label text-white">
                            <i class="bi bi-clock-history me-1"></i>Jam Masuk <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="jam_masuk" id="jam_masuk" 
                               class="form-control bg-dark text-white border-dark @error('jam_masuk') is-invalid @enderror" 
                               value="{{ old('jam_masuk', $presensi->status == 'Hadir' ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') : '08:00') }}" 
                               {{ $presensi->status != 'Hadir' ? 'disabled' : '' }}
                               pattern="[0-9]{2}:[0-9]{2}">
                        @error('jam_masuk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jam Keluar -->
                    <div class="col-md-6 jam-field" style="{{ $presensi->status != 'Hadir' ? 'display: none;' : '' }}">
                        <label for="jam_keluar" class="form-label text-white">
                            <i class="bi bi-clock-fill me-1"></i>Jam Keluar <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="jam_keluar" id="jam_keluar" 
                               class="form-control bg-dark text-white border-dark @error('jam_keluar') is-invalid @enderror" 
                               value="{{ old('jam_keluar', $presensi->status == 'Hadir' ? \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') : '17:00') }}" 
                               {{ $presensi->status != 'Hadir' ? 'disabled' : '' }}
                               pattern="[0-9]{2}:[0-9]{2}">
                        @error('jam_keluar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="col-12">
                        <label for="keterangan" class="form-label text-white">
                            <i class="bi bi-card-text me-1"></i>Keterangan
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="2"
                            class="form-control bg-dark text-white border-dark @error('keterangan') is-invalid @enderror"
                            placeholder="Masukkan keterangan (opsional)">{{ old('keterangan', $presensi->keterangan) }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-light" onclick="window.history.back()">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('presensiForm');
    const jamMasuk = document.getElementById('jam_masuk');
    const jamKeluar = document.getElementById('jam_keluar');
    const submitBtn = document.getElementById('submitBtn');
    const statusSelect = document.getElementById('status');
    const jamFields = document.querySelectorAll('.jam-field');
    
    // Sembunyikan field jam jika status bukan Hadir
    function toggleJamFields() {
        if (statusSelect.value !== 'Hadir') {
            jamFields.forEach(field => {
                field.style.display = 'none';
                field.querySelector('input').disabled = true;
            });
        } else {
            jamFields.forEach(field => {
                field.style.display = 'block';
                field.querySelector('input').disabled = false;
            });
        }
    }
    
    // Panggil fungsi saat halaman dimuat
    toggleJamFields();
    
    // Panggil fungsi saat status berubah
    statusSelect.addEventListener('change', toggleJamFields);
    
    // Auto-set jam keluar when jam masuk changes
    if (jamMasuk) {
        jamMasuk.addEventListener('change', function() {
            if (statusSelect && statusSelect.value === 'Hadir' && jamKeluar) {
                const [hours, minutes] = this.value.split(':').map(Number);
                const date = new Date();
                date.setHours(hours + 9, minutes, 0); // Default +9 jam
                
                // Jika melewati tengah malam, set ke jam 23:59
                if (date.getHours() >= 24) {
                    date.setHours(23, 59, 0);
                }
                
                const newHours = String(date.getHours()).padStart(2, '0');
                const newMinutes = String(date.getMinutes()).padStart(2, '0');
                jamKeluar.value = `${newHours}:${newMinutes}`;
            }
        });
    }
    
    // Validasi form sebelum submit
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validasi jam keluar harus setelah jam masuk
            if (statusSelect.value === 'Hadir' && jamMasuk && jamMasuk.value && jamKeluar && jamKeluar.value) {
                const masuk = new Date('2000-01-01T' + jamMasuk.value);
                const keluar = new Date('2000-01-01T' + jamKeluar.value);
                
                if (keluar <= masuk) {
                    e.preventDefault();
                    alert('Jam keluar harus setelah jam masuk');
                    jamKeluar.focus();
                    isValid = false;
                }
            }
            
            // Jika validasi berhasil, tampilkan loading
            if (isValid && submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat fa-spin me-1"></i> Menyimpan...';
                return true;
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    /* Style untuk form */
    .form-control, .form-select, 
    .form-control:focus, .form-select:focus {
        background-color: #1e1e2f !important;
        border-color: #2d2d3a !important;
        color: #ffffff !important;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25) !important;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    /* Style untuk option di select */
    option {
        background-color: #1e1e2f;
        color: #ffffff;
    }
    
    /* Style untuk card */
    .card {
        background-color: #222232;
        border: 1px solid #2d2d3a;
    }
    
    /* Style untuk text muted */
    .text-muted {
        color: #8a8a9a !important;
    }
    
    /* Style untuk tombol */
    .btn-outline-light {
        border-color: #4a4a5a;
    }
    
    .btn-outline-light:hover {
        background-color: #2d2d3a;
        border-color: #4a4a5a;
    }
    
    /* Style untuk alert */
    .alert {
        border: none;
        border-left: 4px solid;
    }
    
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.1);
        border-left-color: #dc3545;
        color: #f8d7da;
    }
    
    .alert-success {
        background-color: rgba(25, 135, 84, 0.1);
        border-left-color: #198754;
        color: #d1e7dd;
    }
    
    /* Style untuk loading */
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }
    
    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush
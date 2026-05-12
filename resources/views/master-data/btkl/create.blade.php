@extends('layouts.app')

@section('title', 'Tambah BTKL')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-user-clock me-2"></i>Tambah BTKL
        </h2>
        <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.btkl.store') }}" method="POST" class="needs-validation" novalidate id="btklForm">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="nama_btkl" class="form-label">Nama Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_btkl" 
                               id="nama_btkl" 
                               class="form-control @error('nama_btkl') is-invalid @enderror" 
                               value="{{ old('nama_btkl') }}"
                               placeholder="Contoh: Menggoreng, Membumbui, Mengemas"
                               required>
                        @error('nama_btkl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Nama proses produksi (contoh: Menggoreng, Membumbui, Mengemas)</small>
                    </div>

                    <div class="col-md-6">
                        <label for="jabatan_id" class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                        <select name="jabatan_id" 
                                id="jabatan_id" 
                                class="form-select @error('jabatan_id') is-invalid @enderror" 
                                required>
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach($jabatanBtkl as $jabatan)
                                <option value="{{ $jabatan->id }}" {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Pilih jabatan yang mengurusi proses BTKL ini</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jumlah Pegawai</label>
                        <div class="input-group">
                            <input type="number" 
                                   id="jumlah_pegawai_display" 
                                   class="form-control" 
                                   value="0"
                                   readonly>
                            <span class="input-group-text">orang</span>
                        </div>
                        <small class="form-text text-muted">Otomatis dari jabatan yang dipilih</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tarif per Produk Jabatan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" 
                                   id="tarif_per_jam_jabatan_display" 
                                   class="form-control" 
                                   value="0"
                                   readonly>
                            <span class="input-group-text">/Produk</span>
                        </div>
                        <small class="form-text text-muted">Tarif per Produk dari jabatan</small>
                    </div>

                    
                    <div class="col-md-12">
                        <label for="deskripsi_proses" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi_proses" 
                                  id="deskripsi_proses" 
                                  class="form-control @error('deskripsi_proses') is-invalid @enderror" 
                                  rows="3"
                                  placeholder="Deskripsi detail proses produksi">{{ old('deskripsi_proses') }}</textarea>
                        @error('deskripsi_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save me-1"></i> Simpan Data
                            </button>
                            <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden fields untuk data yang akan disimpan -->
                <input type="hidden" name="kode_proses" id="kode_proses" value="{{ old('kode_proses', $nextKode) }}">
                <input type="hidden" name="tarif_btkl" id="tarif_btkl_hidden" value="0">
                <input type="hidden" name="form_token" value="{{ md5(time() . auth()->id()) }}">
            </form>
        </div>
    </div>
</div>

<script>
// Employee data - menggunakan employeeData yang sudah di-map dengan pegawai_count
const employeeData = @json($employeeData ?? []);

document.addEventListener('DOMContentLoaded', function() {
    const jabatanSelect = document.getElementById('jabatan_id');
    const jumlahPegawaiDisplay = document.getElementById('jumlah_pegawai_display');
    const tarifPerJamJabatanDisplay = document.getElementById('tarif_per_jam_jabatan_display');
    const tarifBtklDisplay = document.getElementById('tarif_btkl_display');
    const tarifBtklHidden = document.getElementById('tarif_btkl_hidden');
    const tarifCalculationDisplay = document.getElementById('tarifCalculationDisplay');
    const tarifCalculationText = document.getElementById('tarifCalculationText');
    
    let currentJabatan = null;

    function updateFormValues(jabatan) {
        console.log('updateFormValues called with:', jabatan);
        
        if (jabatan) {
            const jumlahPegawai = jabatan.pegawai_count || 0;
            const tarifPerJamJabatan = jabatan.tarif || 0;
            const tarifBtkl = jumlahPegawai * tarifPerJamJabatan;
            
            console.log('Calculated values:', {
                jumlahPegawai,
                tarifPerJamJabatan,
                tarifBtkl
            });
            
            // Update display fields
            jumlahPegawaiDisplay.value = jumlahPegawai;
            tarifPerJamJabatanDisplay.value = formatRupiah(tarifPerJamJabatan);
            
            // Update hidden field
            tarifBtklHidden.value = tarifBtkl;
            
            currentJabatan = jabatan;
        } else {
            console.log('Resetting form values');
            // Reset all fields
            jumlahPegawaiDisplay.value = '0';
            tarifPerJamJabatanDisplay.value = 'Rp 0';
            tarifBtklHidden.value = '0';
            
            currentJabatan = null;
        }
    }

    function formatRupiah(number) {
        return 'Rp ' + number.toLocaleString('id-ID');
    }

    jabatanSelect.addEventListener('change', function() {
        const selectedJabatanId = parseInt(this.value);
        
        console.log('Selected Jabatan ID:', selectedJabatanId);
        console.log('Employee Data:', employeeData);
        
        if (selectedJabatanId) {
            const jabatan = employeeData.find(j => j.id === selectedJabatanId);
            console.log('Found Jabatan:', jabatan);
            updateFormValues(jabatan);
        } else {
            updateFormValues(null);
        }
    });

    // Form submission validation
    document.getElementById('submitBtn').addEventListener('click', function(e) {
        // Prevent duplicate submissions
        if (this.disabled) {
            e.preventDefault();
            return false;
        }
        
        const selectedJabatanId = parseInt(jabatanSelect.value);
        
        if (!selectedJabatanId) {
            e.preventDefault();
            alert('Silakan pilih jabatan terlebih dahulu!');
            jabatanSelect.focus();
            return false;
        }
        
        const jabatan = employeeData.find(j => j.id === selectedJabatanId);
        if (!jabatan) {
            e.preventDefault();
            alert('Jabatan tidak valid!');
            return false;
        }
        
        // Ensure tariff is calculated correctly
        const jumlahPegawai = jabatan.pegawai_count || 0;
        const tarifPerJamJabatan = jabatan.tarif || 0;
        const calculatedTarif = jumlahPegawai * tarifPerJamJabatan;
        
        console.log('Final validation:', {
            jumlahPegawai,
            tarifPerJamJabatan,
            calculatedTarif,
            currentHiddenValue: tarifBtklHidden.value
        });
        
        // Force update hidden field with calculated value
        tarifBtklHidden.value = calculatedTarif;
        
        if (calculatedTarif <= 0) {
            e.preventDefault();
            alert('Tarif BTKL harus lebih dari 0! Pastikan jabatan memiliki tarif dan pegawai.');
            jabatanSelect.focus();
            return false;
        }
        
        // ENHANCED: Comprehensive duplicate prevention
        if (this.disabled || this.classList.contains('btn-success')) {
            e.preventDefault();
            return false;
        }
        
        const originalText = this.innerHTML;
        const form = this.closest('form');
        
        // Disable immediately and add multiple protection layers
        this.disabled = true;
        this.classList.add('btn-success');
        this.classList.remove('btn-primary');
        
        // Show instant success feedback
        this.innerHTML = '<i class="bi bi-check-circle me-1"></i> Disimpan!';
        
        console.log('Form submission validated with tariff:', calculatedTarif);
        
        // Add form submission tracking
        form.setAttribute('data-submitting', 'true');
        
        // Submit form immediately
        form.submit();
        
        // Prevent any further submissions
        setTimeout(() => {
            form.setAttribute('data-submitted', 'true');
        }, 100);
        
        return false;
    });
});
</script>
@endsection

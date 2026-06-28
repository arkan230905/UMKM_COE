@extends('layouts.app')

@section('title', 'Tambah Vendor')

@section('content')
<div class="container">
    <h2>Tambah Vendor</h2>

    <form action="{{ route('master-data.vendor.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama_vendor" class="form-label">Nama Vendor <span style="color: red;">*</span></label>
            <input type="text" name="nama_vendor" class="form-control" value="{{ old('nama_vendor') }}" required>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori Vendor <span style="color: red;">*</span></label>
            <select name="kategori" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Bahan Baku" {{ old('kategori') == 'Bahan Baku' ? 'selected' : '' }}>Bahan Baku</option>
                <option value="Bahan Pendukung" {{ old('kategori') == 'Bahan Pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                <option value="Bahan Baku & Bahan Pendukung" {{ old('kategori') == 'Bahan Baku & Bahan Pendukung' ? 'selected' : '' }}>Bahan Baku & Bahan Pendukung</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat <span style="color: red;">*</span></label>
            <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}" required>
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telepon <span style="color: red;">*</span></label>
            <input type="text" 
                   name="no_telp" 
                   id="no_telp_input" 
                   class="form-control" 
                   value="{{ old('no_telp') }}" 
                   pattern="[0-9]+"
                   title="Hanya angka yang diperbolehkan"
                   required>
            
            <div id="no_telp_error" class="invalid-feedback d-none">
                No. Telepon hanya boleh berisi angka!
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('master-data.vendor.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const noTelpInput = document.getElementById('no_telp_input');
    const noTelpError = document.getElementById('no_telp_error');
    const form = noTelpInput.closest('form');
    
    // Real-time validation saat user mengetik
    noTelpInput.addEventListener('input', function(e) {
        // Hapus semua karakter non-digit
        const value = this.value;
        const onlyNumbers = value.replace(/[^0-9]/g, '');
        
        // Jika ada karakter non-digit yang dihapus, tampilkan error
        if (value !== onlyNumbers) {
            this.classList.add('is-invalid');
            noTelpError.classList.remove('d-none');
            noTelpError.classList.add('d-block');
            
            // Auto-correct: set value ke numbers only
            this.value = onlyNumbers;
            
            // Show alert
            if (value.length > onlyNumbers.length) {
                // Ada karakter yang dihapus
                setTimeout(() => {
                    alert('No. Telepon hanya boleh berisi angka!.');
                }, 100);
            }
        } else if (onlyNumbers.length > 0) {
            // Valid input
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            noTelpError.classList.remove('d-block');
            noTelpError.classList.add('d-none');
        } else {
            // Empty input
            this.classList.remove('is-invalid', 'is-valid');
            noTelpError.classList.remove('d-block');
            noTelpError.classList.add('d-none');
        }
    });
    
    // ========================================
    // UNIVERSAL REQUIRED FIELD VALIDATION
    // Validasi semua field dengan * merah wajib diisi
    // ========================================
    form.addEventListener('submit', function(e) {
        let emptyFields = [];
        let firstEmptyField = null;
        
        // Check all required inputs
        const requiredInputs = form.querySelectorAll('[required]');
        
        requiredInputs.forEach(input => {
            const value = input.value.trim();
            
            if (value === '') {
                const label = form.querySelector(`label[for="${input.name}"]`) || 
                             input.parentElement.querySelector('label');
                const fieldName = label ? label.textContent.replace('*', '').trim() : input.name;
                
                emptyFields.push(fieldName);
                input.classList.add('is-invalid');
                
                if (!firstEmptyField) {
                    firstEmptyField = input;
                }
            }
        });
        
        // Jika ada field kosong, prevent submit dan tampilkan alert
        if (emptyFields.length > 0) {
            e.preventDefault();
            
            if (firstEmptyField) {
                firstEmptyField.focus();
            }
            
            alert('⚠️ Form belum lengkap!\n\n' + 
                  'Field yang wajib diisi (*):\n\n' + 
                  '• ' + emptyFields.join('\n• ') + 
                  '\n\nSilakan lengkapi semua field yang ditandai dengan bintang merah (*).');
            
            return false;
        }
        
        // Validate phone number
        const phoneValue = noTelpInput.value;
        const isPhoneValid = /^[0-9]+$/.test(phoneValue) && phoneValue.length > 0;
        
        if (!isPhoneValid) {
            e.preventDefault();
            noTelpInput.classList.add('is-invalid');
            noTelpError.classList.remove('d-none');
            noTelpError.classList.add('d-block');
            noTelpInput.focus();
            
            alert('⚠️ No. Telepon tidak valid!\n\nPastikan hanya berisi angka (0-9).');
            return false;
        }
    });
});
</script>
@endsection

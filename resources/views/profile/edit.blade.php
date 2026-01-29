@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Edit Profil</h1>
        
        <!-- Foto Profil dan Tombol Ganti Foto -->
        <div class="d-flex align-items-center">
            <div class="me-3 position-relative">
                <div id="photo-preview" class="position-relative">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/profile-photos/' . $user->profile_photo) }}" 
                             alt="Profile Photo" 
                             class="img-thumbnail rounded-circle" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="img-thumbnail rounded-circle d-flex align-items-center justify-content-center bg-light" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user text-muted fs-3"></i>
                        </div>
                    @endif
                </div>
                <div class="position-absolute bottom-0 end-0">
                    <label for="profile_photo" class="btn btn-sm btn-primary rounded-circle" 
                           style="width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-camera" style="font-size: 12px;"></i>
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" class="d-none" accept="image/*">
                </div>
            </div>
            <div>
                <h6 class="mb-1">Foto Profil</h6>
                <small class="text-muted">Klik ikon kamera untuk mengganti foto</small>
                <br>
                <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                @if($user->profile_photo)
                <br>
                <form action="{{ route('profil-admin.remove-photo') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger mt-1" onclick="return confirm('Hapus foto profil?')">
                        <i class="fas fa-trash me-1"></i>Hapus Foto
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profil-admin.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="{{ $user->username }}">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone }}" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>

    <form action="{{ route('profil-admin.destroy') }}" method="POST" class="mt-3">
        @csrf
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" class="btn btn-danger">Hapus Akun</button>
    </form>
</div>

<script>
document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validasi file
        if (!file.type.match('image.*')) {
            alert('Harap pilih file gambar (JPG, PNG, GIF)');
            e.target.value = '';
            return;
        }
        
        if (file.size > 2048 * 1024) { // 2MB
            alert('Ukuran file terlalu besar. Maksimal 2MB');
            e.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const photoPreview = document.getElementById('photo-preview');
            photoPreview.innerHTML = `
                <img src="${e.target.result}" 
                     alt="Profile Photo Preview" 
                     class="img-thumbnail rounded-circle" 
                     style="width: 80px; height: 80px; object-fit: cover;">
            `;
        };
        reader.readAsDataURL(file);
    }
});

// Auto-save photo when selected (optional enhancement)
document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Show loading indicator
        const submitBtn = document.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengunggah foto...';
        submitBtn.disabled = true;
        
        // Reset button after 2 seconds (simulating upload)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    }
});
</script>
@endsection

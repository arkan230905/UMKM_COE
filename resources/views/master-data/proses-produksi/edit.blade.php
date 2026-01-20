@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h2 class="mb-4 text-white"><i class="bi bi-user-clock me-2"></i>Edit Proses Produksi</h2>

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

    <div class="card shadow-sm">
        <div class="card-body" style="color: white !important;">
            <style>
                .card-body input, .card-body select, .card-body textarea {
                    color: white !important;
                    background-color: rgba(0,0,0,0.8) !important;
                    border: 1px solid rgba(255,255,255,0.3) !important;
                }
                .card-body input::placeholder, .card-body textarea::placeholder {
                    color: rgba(255,255,255,0.7) !important;
                }
                .card-body .input-group-text {
                    color: white !important;
                    background-color: rgba(0,0,0,0.6) !important;
                    border-color: rgba(255,255,255,0.3) !important;
                }
                .card-body .form-control, .card-body .form-select {
                    border-color: rgba(255,255,255,0.3) !important;
                }
                .card-body .form-control:focus {
                    background-color: rgba(0,0,0,0.9) !important;
                    border-color: #007bff !important;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
                }
            </style>
            <form action="{{ route('master-data.btkl.update', $prosesProduksi) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="kode_proses" class="form-label text-white">Kode Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="kode_proses" 
                               id="kode_proses" 
                               class="form-control @error('kode_proses') is-invalid @enderror" 
                               value="{{ old('kode_proses', $prosesProduksi->kode_proses) }}"
                               readonly
                               required>
                        @error('kode_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nama_proses" class="form-label text-white">Nama Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_proses" 
                               id="nama_proses" 
                               class="form-control @error('nama_proses') is-invalid @enderror" 
                               value="{{ old('nama_proses', $prosesProduksi->nama_proses) }}"
                               placeholder="Contoh: Menggoreng, Membumbui, Mengemas"
                               required>
                        @error('nama_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tarif_btkl" class="form-label text-white">Tarif BTKL <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="tarif_btkl" 
                                   id="tarif_btkl" 
                                   class="form-control @error('tarif_btkl') is-invalid @enderror" 
                                   value="{{ old('tarif_btkl', $prosesProduksi->tarif_btkl) }}"
                                   min="0" 
                                   step="100" 
                                   placeholder="15000"
                                   required>
                        </div>
                        @error('tarif_btkl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya Tenaga Kerja Langsung per satuan waktu</small>
                    </div>

                    <div class="col-md-6">
                        <label for="satuan_btkl" class="form-label text-white">Satuan BTKL <span class="text-danger">*</span></label>
                        <select name="satuan_btkl" id="satuan_btkl" class="form-select @error('satuan_btkl') is-invalid @enderror" required>
                            <option value="jam" {{ old('satuan_btkl', $prosesProduksi->satuan_btkl) == 'jam' ? 'selected' : '' }}>Jam</option>
                            <option value="menit" {{ old('satuan_btkl', $prosesProduksi->satuan_btkl) == 'menit' ? 'selected' : '' }}>Menit</option>
                            <option value="unit" {{ old('satuan_btkl', $prosesProduksi->satuan_btkl) == 'unit' ? 'selected' : '' }}>Unit</option>
                            <option value="batch" {{ old('satuan_btkl', $prosesProduksi->satuan_btkl) == 'batch' ? 'selected' : '' }}>Batch</option>
                        </select>
                        @error('satuan_btkl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="kapasitas_per_jam" class="form-label text-white">Kapasitas per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" 
                                   name="kapasitas_per_jam" 
                                   id="kapasitas_per_jam" 
                                   class="form-control @error('kapasitas_per_jam') is-invalid @enderror" 
                                   value="{{ old('kapasitas_per_jam', $prosesProduksi->kapasitas_per_jam ?? 50) }}"
                                   min="1" 
                                   step="1" 
                                   placeholder="50"
                                   required>
                            <span class="input-group-text">unit/jam</span>
                        </div>
                        @error('kapasitas_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Jumlah unit yang dapat diproduksi per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="deskripsi" class="form-label text-white">Deskripsi</label>
                        <textarea name="deskripsi" 
                                  id="deskripsi" 
                                  class="form-control @error('deskripsi') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="Deskripsi proses produksi (opsional)">{{ old('deskripsi', $prosesProduksi->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                    <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = document.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        console.log('Form is being submitted...');
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-spinner bi-spin"></i> Menyimpan...';
    });
});
</script>
@endsection

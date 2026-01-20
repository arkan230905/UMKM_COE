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
            <form action="{{ route('master-data.btkl.update', $btkl->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="kode_proses" class="form-label text-white">Kode Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="kode_proses" 
                               id="kode_proses" 
                               class="form-control @error('kode_proses') is-invalid @enderror" 
                               value="{{ old('kode_proses') ?? $btkl->kode_proses }}"
                               readonly
                               required>
                        @error('kode_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="jabatan_id" class="form-label text-white">Nama BTKL <span class="text-danger">*</span></label>
                        <select name="jabatan_id" id="jabatan_id" class="form-select @error('jabatan_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Nama BTKL --</option>
                            @foreach($jabatanBtkl as $jabatan)
                                <option value="{{ $jabatan->id }}" {{ (old('jabatan_id') ?? $btkl->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Data diambil dari Klasifikasi Tenaga Kerja kategori BTKL</small>
                    </div>

                    <div class="col-md-6">
                        <label for="tarif_per_jam" class="form-label text-white">Tarif BTKL per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp/jam</span>
                            <input type="number" 
                                   name="tarif_per_jam" 
                                   id="tarif_per_jam" 
                                   class="form-control @error('tarif_per_jam') is-invalid @enderror" 
                                   value="{{ old('tarif_per_jam') ?? $btkl->tarif_per_jam }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="15000"
                                   required>
                        </div>
                        @error('tarif_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="kapasitas_per_jam" class="form-label text-white">Kapasitas per Jam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" 
                                   name="kapasitas_per_jam" 
                                   id="kapasitas_per_jam" 
                                   class="form-control @error('kapasitas_per_jam') is-invalid @enderror" 
                                   value="{{ old('kapasitas_per_jam') ?? $btkl->kapasitas_per_jam }}"
                                   min="0" 
                                   placeholder="100"
                                   required>
                            <span class="input-group-text">pcs/jam</span>
                        </div>
                        @error('kapasitas_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="satuan" class="form-label text-white">Satuan <span class="text-danger">*</span></label>
                        <select name="satuan" id="satuan" class="form-select @error('satuan') is-invalid @enderror" required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($satuanOptions as $satuan)
                                <option value="{{ $satuan }}" {{ (old('satuan') ?? $btkl->satuan) == $satuan ? 'selected' : '' }}>
                                    {{ $satuan }}
                                </option>
                            @endforeach
                        </select>
                        @error('satuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="deskripsi_proses" class="form-label text-white">Deskripsi Proses</label>
                        <textarea name="deskripsi_proses" 
                                  id="deskripsi_proses" 
                                  class="form-control @error('deskripsi_proses') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="Deskripsi detail proses produksi (opsional)">{{ old('deskripsi_proses') ?? $btkl->deskripsi_proses }}</textarea>
                        @error('deskripsi_proses')
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
@endsection
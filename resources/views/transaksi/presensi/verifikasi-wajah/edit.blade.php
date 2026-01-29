@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-fill me-2"></i> Edit Verifikasi Wajah
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('transaksi.presensi.verifikasi-wajah.update', $verifikasi->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="nomor_induk_pegawai" class="form-label">Pegawai</label>
                            <select name="nomor_induk_pegawai" id="nomor_induk_pegawai" class="form-select" required>
                                <option value="">Pilih Pegawai</option>
                                @foreach($pegawais as $pegawai)
                                    <option value="{{ $pegawai->nomor_induk_pegawai }}" 
                                            @if($pegawai->nomor_induk_pegawai == $verifikasi->nomor_induk_pegawai) selected @endif>
                                        {{ $pegawai->nama }} ({{ $pegawai->nomor_induk_pegawai }})
                                    </option>
                                @endforeach
                            </select>
                            @error('nomor_induk_pegawai')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="foto_wajah" class="form-label">Foto Wajah</label>
                            <input type="file" name="foto_wajah" id="foto_wajah" class="form-control" 
                                   accept="image/*">
                            <div class="form-text">Format: JPG, PNG, maksimal 2MB</div>
                            @if($verifikasi->foto_wajah)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/foto-wajah/' . $verifikasi->foto_wajah) }}" 
                                         alt="Foto Wajah" 
                                         class="img-thumbnail" 
                                         style="max-width: 200px; max-height: 200px;">
                                </div>
                            @endif
                            @error('foto_wajah')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="aktif" id="aktif" class="form-check-input" 
                                       @if($verifikasi->aktif) checked @endif>
                                <label class="form-check-label" for="aktif">
                                    Aktif
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('transaksi.presensi.verifikasi-wajah.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

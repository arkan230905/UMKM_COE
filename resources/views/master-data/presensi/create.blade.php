{{-- resources/views/master-data/presensi/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center">➕ Tambah Presensi</h2>

    {{-- Notifikasi Error --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Notifikasi Success --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm p-4">
        <form action="{{ route('master-data.presensi.store') }}" method="POST">
            @csrf

            {{-- Pilih Pegawai --}}
            <div class="mb-3">
                <label for="pegawai_id" class="form-label">Pegawai</label>
                <select name="pegawai_id" id="pegawai_id" class="form-select" required>
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawais as $pegawai)
                        <option value="{{ $pegawai->id }}" {{ old('pegawai_id') == $pegawai->id ? 'selected' : '' }}>
                            {{ $pegawai->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal Presensi --}}
            <div class="mb-3">
                <label for="tgl_presensi" class="form-label">Tanggal</label>
                <input type="date" name="tgl_presensi" id="tgl_presensi" class="form-control" 
                       value="{{ old('tgl_presensi', date('Y-m-d')) }}" required>
            </div>

            {{-- Jam Masuk --}}
            <div class="mb-3">
                <label for="jam_masuk" class="form-label">Jam Masuk</label>
                <input type="time" name="jam_masuk" id="jam_masuk" class="form-control" 
                       value="{{ old('jam_masuk') }}" required>
            </div>

            {{-- Jam Keluar --}}
            <div class="mb-3">
                <label for="jam_keluar" class="form-label">Jam Keluar</label>
                <input type="time" name="jam_keluar" id="jam_keluar" class="form-control" 
                       value="{{ old('jam_keluar') }}" required>
            </div>

            {{-- Submit Button --}}
            <div class="text-end">
                <a href="{{ route('master-data.presensi.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>
@endsection

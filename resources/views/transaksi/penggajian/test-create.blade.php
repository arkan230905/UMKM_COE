@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-plus-circle"></i> Test Simpan Penggajian (Minimal)</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <form action="{{ route('transaksi.penggajian.store') }}" method="POST">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="pegawai_id" class="form-label">Pilih Pegawai *</label>
                        <select name="pegawai_id" class="form-select" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach ($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}">{{ $pegawai->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_penggajian" class="form-label">Tanggal Penggajian *</label>
                        <input type="date" name="tanggal_penggajian" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="coa_kasbank" class="form-label">Akun Kas/Bank *</label>
                        <select name="coa_kasbank" class="form-select" required>
                            @foreach($kasbank as $kb)
                                <option value="{{ $kb->kode_akun }}">{{ $kb->nama_akun }} ({{ $kb->kode_akun }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="bonus" class="form-label">Bonus</label>
                        <input type="number" name="bonus" class="form-control" value="0" step="0.01" min="0">
                    </div>

                    <div class="col-md-3">
                        <label for="potongan" class="form-label">Potongan</label>
                        <input type="number" name="potongan" class="form-control" value="0" step="0.01" min="0">
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <div>
                        <button type="submit" class="btn btn-success me-2">
                            <i class="bi bi-save"></i> Test Simpan
                        </button>
                        <button type="submit" class="btn btn-warning" name="test_mode" value="hardcoded">
                            <i class="bi bi-bug"></i> Test Hardcoded
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

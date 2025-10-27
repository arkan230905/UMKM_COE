@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Data BOP</h2>
    <form action="{{ route('master-data.bop.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Akun Beban</label>
            <select name="coa_id" class="form-control" required>
                <option value="">-- Pilih Akun Beban --</option>
                @foreach ($coa as $c)
                    <option value="{{ $c->id }}">{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <input type="text" name="keterangan" class="form-control">
        </div>

        {{-- Tambahan baru --}}
        <div class="mb-3">
            <label class="form-label">Nominal</label>
            <input type="number" name="nominal" class="form-control" step="0.01" placeholder="Masukkan jumlah nominal">
        </div>

        <div class="mb-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control">
        </div>
        {{-- Akhir tambahan baru --}}

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection

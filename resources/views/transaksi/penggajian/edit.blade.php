@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Edit Penggajian</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('transaksi.penggajian.update', $penggajian->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label>Pegawai</label>
            <select name="pegawai_id" id="pegawai_id" class="form-control" required>
                <option value="">-- Pilih Pegawai --</option>
                @foreach($pegawai as $p)
                    <option value="{{ $p->id }}" 
                        data-gaji="{{ $p->gaji }}" 
                        {{ $penggajian->pegawai_id == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Gaji Pokok (per jam)</label>
            <input type="number" step="0.01" name="gaji_pokok" id="gaji_pokok" 
                   class="form-control" 
                   value="{{ $penggajian->gaji_pokok }}" readonly>
            <small class="text-muted">Otomatis diambil dari data pegawai</small>
        </div>

        <div class="mb-3">
            <label>Total Jam Kerja</label>
            <input type="number" step="0.01" name="total_jam_kerja" id="total_jam_kerja"
                   class="form-control" 
                   value="{{ $penggajian->total_jam_kerja ?? 0 }}" readonly>
            <small class="text-muted">Dihitung otomatis dari data presensi</small>
        </div>

        <div class="mb-3">
            <label>Tunjangan</label>
            <input type="number" step="0.01" name="tunjangan" class="form-control" 
                   value="{{ $penggajian->tunjangan ?? 0 }}">
        </div>

        <div class="mb-3">
            <label>Potongan</label>
            <input type="number" step="0.01" name="potongan" class="form-control" 
                   value="{{ $penggajian->potongan ?? 0 }}">
        </div>

        <div class="mb-3">
            <label>Tanggal Penggajian</label>
            <input type="date" name="tanggal_penggajian" class="form-control" 
                   value="{{ $penggajian->tanggal_penggajian }}" required>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

{{-- Script untuk otomatis isi gaji pokok --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectPegawai = document.getElementById('pegawai_id');
    const inputGaji = document.getElementById('gaji_pokok');

    selectPegawai.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const gaji = selectedOption.getAttribute('data-gaji') || '';
        inputGaji.value = gaji;
    });
});
</script>
@endsection

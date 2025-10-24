@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Penggajian</h2>
    <form action="{{ route('transaksi.penggajian.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="pegawai_id" class="form-label">Pegawai</label>
            <select name="pegawai_id" id="pegawai_id" class="form-control" required>
                <option value="">-- Pilih Pegawai --</option>
                @foreach ($pegawai as $p)
                    <option value="{{ $p->id }}" data-gaji="{{ $p->gaji }}">{{ $p->nama }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label>Gaji Pokok per Jam</label>
            <input type="number" name="gaji_pokok" class="form-control" value="0" readonly>
            <small class="text-muted">Dihitung otomatis berdasarkan total jam kerja bulan ini</small>
        </div>

        <div class="mb-3">
            <label>Total Jam Kerja</label>
            <input type="number" name="total_jam_kerja" class="form-control" value="0" readonly>
        </div>

        <div class="mb-3">
            <label>Tunjangan</label>
            <input type="number" name="tunjangan" class="form-control" placeholder="Opsional">
        </div>

        <div class="mb-3">
            <label>Potongan</label>
            <input type="number" name="potongan" class="form-control" placeholder="Opsional">
        </div>

        <div class="mb-3">
            <label>Tanggal Penggajian</label>
            <input type="date" name="tanggal_penggajian" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
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

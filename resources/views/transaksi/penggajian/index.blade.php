@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Data Penggajian</h4>

    <div class="mb-3">
        <a href="{{ route('transaksi.penggajian.create') }}" class="btn btn-primary">Tambah Penggajian</a>

        <!-- Tombol Generate Gaji -->
        <form action="{{ route('transaksi.penggajian.generate') }}" method="POST" class="d-inline">
            @csrf
            <input type="month" name="tanggal_penggajian" value="{{ date('Y-m') }}" class="form-control d-inline-block" style="width:auto;">
            <button type="submit" class="btn btn-success">Generate Gaji Bulan Ini</button>
        </form>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tanggal Penggajian</th>
                <th>Pegawai</th>
                <th>Gaji Pokok</th>
                <th>Tunjangan</th>
                <th>Potongan</th>
                <th>Total Jam Kerja</th>
                <th>Total Gaji</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penggajians as $penggajian)
            <tr>
                <td>{{ $penggajian->id }}</td>
                <td>{{ $penggajian->tanggal_penggajian }}</td>
                <td>{{ $penggajian->pegawai?->nama ?? '-' }}</td>
                <td>{{ number_format($penggajian->gaji_pokok, 0, ',', '.') }}</td>
                <td>{{ number_format($penggajian->tunjangan, 0, ',', '.') }}</td>
                <td>{{ number_format($penggajian->potongan, 0, ',', '.') }}</td>
                <td>{{ number_format($penggajian->total_jam_kerja, 0, ',', '.') }}</td>
                <td><strong>{{ number_format($penggajian->total_gaji, 0, ',', '.') }}</strong></td>
                <td>
                    <a href="{{ route('transaksi.penggajian.edit', $penggajian->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('transaksi.penggajian.destroy', $penggajian->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

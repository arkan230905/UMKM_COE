@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-cash-coin"></i> Data Penggajian</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('transaksi.penggajian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Penggajian
        </a>
    </div>

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <table class="table table-dark table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Pegawai</th>
                        <th>Tanggal</th>
                        <th>Total Jam</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
                        <th>Potongan</th>
                        <th>Total Gaji</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penggajians as $index => $gaji)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $gaji->pegawai->nama ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($gaji->tanggal_penggajian)->format('d-m-Y') }}</td>
                            <td>{{ $gaji->total_jam_kerja ?? 0 }}</td>
                            <td>Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($gaji->tunjangan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($gaji->potongan, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</strong></td>
                            <td>
                                <form action="{{ route('transaksi.penggajian.destroy', $gaji->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data penggajian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

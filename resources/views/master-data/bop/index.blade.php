@extends('layouts.app')

@section('content')
<div class="container text-center">
    <h2 class="mb-4 fw-bold">Master Data BOP</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('master-data.bop.recalc') }}" class="d-flex justify-content-center align-items-center gap-2 mb-3">
        @csrf
        <label class="me-2">Periode:</label>
        <input type="month" name="periode" class="form-control" style="max-width: 220px;" value="{{ now()->format('Y-m') }}">
        <button type="submit" class="btn btn-primary">Rekalkulasi Beban Gaji (Perkiraan)</button>
    </form>

    {{-- Tabel Data BOP --}}
    <table class="table table-bordered table-striped align-middle text-center shadow-sm">
        <thead class="table-dark">
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama Akun</th>
                <th>Nominal</th>
                <th>Tanggal</th>
                <th style="width: 20%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bop as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->coa->nama_akun ?? '-' }}</td>
                    <td>{{ $item->nominal ? number_format($item->nominal, 0, ',', '.') : '-' }}</td>
                    <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}</td>
                    <td>
                        <a href="{{ route('master-data.bop.edit', $item->id) }}" class="btn btn-warning btn-sm">
                            Edit
                        </a>
                        <form action="{{ route('master-data.bop.destroy', $item->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data BOP</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

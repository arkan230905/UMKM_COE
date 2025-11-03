@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Master Data BOP</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.bop-budget.index', ['periode' => now()->format('Y-m')]) }}" class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> Masukan BOPB
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
        <form method="POST" action="{{ route('master-data.bop.recalc') }}" class="d-flex align-items-center gap-2">
            @csrf
            <label class="me-2">Periode:</label>
            <input type="month" name="periode" class="form-control" style="max-width: 220px;" value="{{ now()->format('Y-m') }}">
            <button type="submit" class="btn btn-primary">Rekalkulasi Beban Gaji (Perkiraan)</button>
        </form>
        <a href="{{ route('master-data.bop-budget.index', ['periode' => now()->format('Y-m')]) }}" class="btn btn-success" style="white-space: nowrap; padding: 0.5rem 1rem; font-weight: 500;">
            <i class="fas fa-plus-circle me-1"></i> Masukan BOPB
        </a>
    </div>

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

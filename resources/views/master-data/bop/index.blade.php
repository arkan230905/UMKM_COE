@extends('layouts.app')

@section('content')
<div class="container text-center">
    <h2 class="mb-4 fw-bold">Master Data BOP</h2>

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
                    <td>
                        {{ $item->nominal ? number_format($item->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td>
                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
                    </td>
                    <td>
                        {{-- Tombol Edit --}}
                        <a href="{{ route('master-data.bop.edit', $item->id) }}" class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        {{-- Tombol Hapus --}}
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
                    <td colspan="6">Belum ada data BOP</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

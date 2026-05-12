@extends('layouts.gudang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daftar Bahan Baku</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Nama Bahan</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Stok Min</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bahanBakus as $bb)
                <tr>
                    <td><code>{{ $bb->kode_bahan }}</code></td>
                    <td>{{ $bb->nama_bahan }}</td>
                    <td class="text-center">{{ number_format($bb->stok, 2) }} {{ $bb->satuan->kode ?? '' }}</td>
                    <td class="text-center">{{ number_format($bb->stok_minimum, 2) }}</td>
                    <td class="text-center">
                        @if($bb->stok <= 0)
                            <span class="badge bg-danger">Habis</span>
                        @elseif($bb->stok < $bb->stok_minimum)
                            <span class="badge bg-warning">Menipis</span>
                        @else
                            <span class="badge bg-success">Aman</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $bahanBakus->links() }}
    </div>
</div>
@endsection

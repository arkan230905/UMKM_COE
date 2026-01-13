@extends('layouts.gudang')

@section('title', 'Stok Bahan Pendukung')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Stok Bahan Pendukung</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Nama Bahan</th>
                    <th>Kategori</th>
                    <th class="text-end">Stok</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga/Satuan</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bahanPendukungs as $bp)
                <tr>
                    <td><code>{{ $bp->kode_bahan }}</code></td>
                    <td>{{ $bp->nama_bahan }}</td>
                    <td>{{ $bp->kategoriBahanPendukung->nama ?? $bp->kategori ?? '-' }}</td>
                    <td class="text-end">{{ number_format($bp->stok, 2, ',', '.') }}</td>
                    <td>{{ $bp->satuan->nama ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format($bp->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($bp->stok <= 0)
                            <span class="badge bg-danger">Habis</span>
                        @elseif($bp->stok < $bp->stok_minimum)
                            <span class="badge bg-warning">Menipis</span>
                        @else
                            <span class="badge bg-success">Aman</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $bahanPendukungs->links() }}
    </div>
</div>
@endsection

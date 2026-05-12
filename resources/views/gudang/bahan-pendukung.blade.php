@extends('layouts.gudang')

@section('title', 'Bahan Pendukung')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bahan Pendukung</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanPendukungs as $bahan)
                            <tr>
                                <td>{{ $bahan->kode_bahan }}</td>
                                <td>{{ $bahan->nama_bahan }}</td>
                                <td>{{ $bahan->kategoriBahanPendukung->nama ?? 'N/A' }}</td>
                                <td>{{ number_format($bahan->stok, 2) }}</td>
                                <td>Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data bahan pendukung</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $bahanPendukungs->links() }}
        </div>
    </div>
</div>
@endsection
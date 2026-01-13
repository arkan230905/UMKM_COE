@extends('layouts.gudang')

@section('title', 'Bahan Baku')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bahan Baku</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Bahan</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanBakus as $bahan)
                            <tr>
                                <td>{{ $bahan->kode_bahan }}</td>
                                <td>{{ $bahan->nama_bahan }}</td>
                                <td>{{ number_format($bahan->stok, 2) }}</td>
                                <td>{{ $bahan->satuan->kode ?? 'N/A' }}</td>
                                <td>Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data bahan baku</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $bahanBakus->links() }}
        </div>
    </div>
</div>
@endsection
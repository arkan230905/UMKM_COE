@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Data Produk</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary mb-3">Tambah Produk</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Produk</th>
                <th>Harga BOM</th>
                <th>Presentase Keuntungan</th>
                <th>Harga Jual</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produks as $produk)
                <?php
                    $bom = (float) ($hargaBom[$produk->id] ?? 0);
                    $margin = (float) ($produk->margin_percent ?? 0);
                    $hargaJualHitung = $bom * (1 + $margin/100);
                ?>
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $produk->nama_produk }}</td>
                    <td>Rp {{ number_format($bom, 0, ',', '.') }}</td>
                    <td>{{ rtrim(rtrim(number_format($margin,2,',','.'),'0'),',') }}%</td>
                    <td>Rp {{ number_format($hargaJualHitung, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('master-data.produk.edit', $produk->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('master-data.produk.destroy', $produk->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada produk</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

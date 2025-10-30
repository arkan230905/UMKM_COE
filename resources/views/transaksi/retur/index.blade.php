@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Data Retur</h4>

    <a href="{{ route('transaksi.retur.create') }}" class="btn btn-primary mb-3">Tambah Retur</a>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tipe</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Kompensasi</th>
                <th>Items</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returs as $retur)
            <tr>
                <td>{{ $retur->id }}</td>
                <td>
                    <span class="badge {{ $retur->type==='sale' ? 'bg-primary' : 'bg-success' }}">
                        {{ strtoupper($retur->type) }}
                    </span>
                </td>
                <td>{{ $retur->tanggal }}</td>
                <td>
                    @switch($retur->status)
                        @case('posted') <span class="badge bg-success">Posted</span> @break
                        @case('approved') <span class="badge bg-info">Approved</span> @break
                        @default <span class="badge bg-secondary">Draft</span>
                    @endswitch
                </td>
                <td>{{ $retur->kompensasi }}</td>
                <td>
                    @foreach($retur->details as $d)
                        <div>{{ $d->produk->nama_produk ?? '-' }} <span class="text-muted">x {{ rtrim(rtrim(number_format($d->qty,4,',','.'),'0'),',') }}</span></div>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('transaksi.retur.edit', $retur->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    @if($retur->status !== 'posted')
                        <form action="{{ route('transaksi.retur.post', $retur->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Posting retur ini?')">
                            @csrf
                            <button class="btn btn-success btn-sm">Post</button>
                        </form>
                    @endif
                    <form action="{{ route('transaksi.retur.destroy', $retur->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
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

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Transaksi Produksi</h3>
        <a href="{{ route('transaksi.produksi.create') }}" class="btn btn-primary">Tambah Produksi</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Qty Produksi</th>
                <th>Total Biaya</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produksis as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->tanggal }}</td>
                    <td>{{ $p->produk->nama_produk }}</td>
                    <td>{{ rtrim(rtrim(number_format($p->qty_produksi,4,',','.'),'0'),',') }}</td>
                    <td>Rp {{ number_format($p->total_biaya,0,',','.') }}</td>
                    <td>
                        @if($p->status === 'wip' || $p->status === 'pending' || !$p->status)
                            <span class="badge bg-warning text-dark">Proses</span>
                        @elseif($p->status === 'completed')
                            <span class="badge bg-success">Selesai</span>
                        @else
                            <span class="badge bg-secondary">{{ $p->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($p->status === 'wip' || $p->status === 'pending' || !$p->status)
                            <form action="{{ route('transaksi.produksi.complete', $p->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm" onclick="return confirm('Tandai produksi ini sebagai selesai?')">
                                    ✓ Selesai Produksi
                                </button>
                            </form>
                        @else
                            <a href="{{ route('transaksi.produksi.show', $p->id) }}" class="btn btn-info btn-sm">Detail</a>
                            <form action="{{ route('transaksi.produksi.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus transaksi produksi ini? Data jurnal terkait juga akan dihapus.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $produksis->links() }}
</div>
@endsection

@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Pembelian</h2>
        <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-primary">+ Tambah Pembelian</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Tanggal</th>
                <th>Vendor</th>
                <th>Pembayaran</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pembelians as $pembelian)
                <tr>
                    <td>{{ $pembelian->tanggal->format('d-m-Y') }}</td>
                    <td>{{ $pembelian->vendor->nama_vendor ?? '-' }}</td>
                    <td>{{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}</td>
                    <td>Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('transaksi.pembelian.show', $pembelian->id) }}" class="btn btn-info btn-sm">Detail</a>
                        <form action="{{ route('transaksi.pembelian.destroy', $pembelian->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada data pembelian.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

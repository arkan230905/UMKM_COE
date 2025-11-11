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
                <th>Item Dibeli</th>
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
                    <td>
                        @if($pembelian->details && $pembelian->details->count() > 0)
                            <small>
                            @foreach($pembelian->details as $detail)
                                <div>
                                    • {{ $detail->bahanBaku->nama_bahan ?? '-' }} 
                                    ({{ number_format($detail->jumlah ?? 0, 0, ',', '.') }} {{ $detail->bahanBaku->satuan->nama ?? 'unit' }})
                                    - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                    = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</strong>
                                </div>
                            @endforeach
                            </small>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}</td>
                    <td>
                        @php
                            $totalPembelian = $pembelian->total;
                            // Jika total = 0, hitung dari details
                            if ($totalPembelian == 0 && $pembelian->details && $pembelian->details->count() > 0) {
                                $totalPembelian = $pembelian->details->sum(function($detail) {
                                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                });
                            }
                        @endphp
                        <strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong>
                    </td>
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
                    <td colspan="6" class="text-center">Belum ada data pembelian.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

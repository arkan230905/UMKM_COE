@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-cart-check"></i> Daftar Pembelian
        </h2>
        <p class="text-muted">Kelola transaksi pembelian bahan baku</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('pegawai-pembelian.pembelian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Pembelian Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($pembelians->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Pembelian</th>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembelians as $index => $pembelian)
                    <tr>
                        <td>{{ $pembelians->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $pembelian->nomor_pembelian ?? 'PB-' . str_pad($pembelian->id, 5, '0', STR_PAD_LEFT) }}</strong>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $pembelian->vendor->nama_vendor ?? '-' }}</td>
                        <td class="fw-bold">Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}</td>
                        <td>
                            @if($pembelian->payment_method === 'cash')
                            <span class="badge bg-success">Tunai</span>
                            @elseif($pembelian->payment_method === 'transfer')
                            <span class="badge bg-info">Transfer</span>
                            @else
                            <span class="badge bg-warning">Kredit</span>
                            @endif
                        </td>
                        <td>
                            @if($pembelian->status === 'lunas' || $pembelian->payment_method === 'cash')
                            <span class="badge bg-success">Lunas</span>
                            @else
                            <span class="badge bg-warning">Belum Lunas</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pegawai-pembelian.pembelian.show', $pembelian->id) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('pegawai-pembelian.pembelian.destroy', $pembelian->id) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus pembelian ini? Stok bahan baku akan dikurangi kembali.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $pembelians->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Belum ada data pembelian</p>
            <a href="{{ route('pegawai-pembelian.pembelian.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Buat Pembelian Baru
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

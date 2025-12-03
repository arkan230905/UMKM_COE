@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-box-seam"></i> Daftar Bahan Baku
        </h2>
        <p class="text-muted">Kelola data bahan baku untuk pembelian</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('pegawai-pembelian.bahan-baku.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Bahan Baku
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($bahanBakus->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Harga Satuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bahanBakus as $index => $bahan)
                    <tr>
                        <td>{{ $bahanBakus->firstItem() + $index }}</td>
                        <td>{{ $bahan->nama_bahan }}</td>
                        <td>{{ $bahan->satuan->nama_satuan ?? '-' }}</td>
                        <td>
                            @if($bahan->stok < 5)
                            <span class="badge bg-danger">{{ $bahan->stok }}</span>
                            @elseif($bahan->stok < 10)
                            <span class="badge bg-warning">{{ $bahan->stok }}</span>
                            @else
                            <span class="badge bg-success">{{ $bahan->stok }}</span>
                            @endif
                        </td>
                        <td>Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pegawai-pembelian.bahan-baku.show', $bahan->id) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pegawai-pembelian.bahan-baku.edit', $bahan->id) }}" 
                                   class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('pegawai-pembelian.bahan-baku.destroy', $bahan->id) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus bahan baku ini?')">
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
            {{ $bahanBakus->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Belum ada data bahan baku</p>
            <a href="{{ route('pegawai-pembelian.bahan-baku.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Tambah Bahan Baku
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

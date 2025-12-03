@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-building"></i> Daftar Vendor
        </h2>
        <p class="text-muted">Kelola data vendor supplier bahan baku</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('pegawai-pembelian.vendor.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Vendor
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($vendors->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Vendor</th>
                        <th>Kategori</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendors as $index => $vendor)
                    <tr>
                        <td>{{ $vendors->firstItem() + $index }}</td>
                        <td><strong>{{ $vendor->nama_vendor }}</strong></td>
                        <td>
                            @if($vendor->kategori)
                            <span class="badge bg-info">{{ $vendor->kategori }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $vendor->no_telp ?? '-' }}</td>
                        <td>{{ $vendor->email ?? '-' }}</td>
                        <td>{{ Str::limit($vendor->alamat ?? '-', 30) }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pegawai-pembelian.vendor.show', $vendor->id) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pegawai-pembelian.vendor.edit', $vendor->id) }}" 
                                   class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('pegawai-pembelian.vendor.destroy', $vendor->id) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus vendor ini?')">
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
            {{ $vendors->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Belum ada data vendor</p>
            <a href="{{ route('pegawai-pembelian.vendor.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Tambah Vendor
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

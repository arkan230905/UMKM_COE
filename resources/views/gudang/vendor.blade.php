@extends('layouts.gudang')

@section('title', 'Vendor')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Vendor</h1>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama Vendor</th>
                    <th>Kategori</th>
                    <th>Alamat</th>
                    <th>No. Telepon</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td>{{ $vendor->nama_vendor }}</td>
                        <td>
                            @if($vendor->kategori == 'Bahan Baku')
                                <span class="badge bg-primary">{{ $vendor->kategori }}</span>
                            @elseif($vendor->kategori == 'Bahan Pendukung')
                                <span class="badge bg-warning text-dark">{{ $vendor->kategori }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $vendor->kategori }}</span>
                            @endif
                        </td>
                        <td>{{ $vendor->alamat }}</td>
                        <td>{{ $vendor->no_telp }}</td>
                        <td>{{ $vendor->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data vendor</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{ $vendors->links() }}
</div>
@endsection
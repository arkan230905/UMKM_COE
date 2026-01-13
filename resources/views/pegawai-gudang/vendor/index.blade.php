@extends('layouts.pegawai-gudang')

@section('title', 'Vendor')

@push('styles')
<style>
    /* Force all table text to be black - comprehensive approach */
    .table td,
    .table th,
    .table td *,
    .table th *,
    .table-striped tbody tr td,
    .table-striped tbody tr th,
    .table td span,
    .table td div,
    .table td p,
    .table td strong,
    .table td small,
    .table td em,
    .table td i,
    .table td b,
    .table th span,
    .table th div,
    .table th p,
    .table th strong,
    .table th small,
    .table th em,
    .table th i,
    .table th b,
    .table-responsive *,
    .card-body *,
    .table * {
        color: #000 !important;
    }
    
    /* Remove striped pattern and make all rows white */
    .table-striped tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }
    
    .table-striped tbody tr:nth-child(even) {
        background-color: #ffffff !important;
    }
    
    .table-striped tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    
    /* Force black text on hover too */
    .table-striped tbody tr:hover *,
    .table-striped tbody tr:hover td,
    .table-striped tbody tr:hover th {
        color: #000 !important;
    }
    
    /* Exception: Keep category badges with their original colors */
    .table td .badge {
        color: inherit !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Vendor</h1>
    </div>

    <div class="card">
        <div class="card-body">
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
                                <td style="color: #000 !important;">{{ $vendor->nama_vendor }}</td>
                                <td>
                                    @if($vendor->kategori == 'Bahan Baku')
                                        <span class="badge bg-primary">{{ $vendor->kategori }}</span>
                                    @elseif($vendor->kategori == 'Bahan Pendukung')
                                        <span class="badge bg-warning text-dark">{{ $vendor->kategori }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $vendor->kategori }}</span>
                                    @endif
                                </td>
                                <td style="color: #000 !important;">{{ $vendor->alamat }}</td>
                                <td style="color: #000 !important;">{{ $vendor->no_telp }}</td>
                                <td style="color: #000 !important;">{{ $vendor->email }}</td>
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
    </div>
</div>
@endsection
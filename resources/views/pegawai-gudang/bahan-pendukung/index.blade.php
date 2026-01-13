@extends('layouts.pegawai-gudang')

@section('title', 'Bahan Pendukung')

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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bahan Pendukung</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanPendukungs as $bahan)
                            <tr>
                                <td style="color: #000 !important;">{{ $bahan->kode_bahan }}</td>
                                <td style="color: #000 !important;">{{ $bahan->nama_bahan }}</td>
                                <td style="color: #000 !important;">{{ $bahan->kategoriBahanPendukung->nama ?? $bahan->kategori ?? 'N/A' }}</td>
                                <td style="color: #000 !important;">{{ number_format($bahan->stok, 2) }}</td>
                                <td style="color: #000 !important;">Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data bahan pendukung</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $bahanPendukungs->links() }}
        </div>
    </div>
</div>
@endsection
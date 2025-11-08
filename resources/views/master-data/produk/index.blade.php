@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Daftar Produk</h1>
        <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Harga BOM</th>
                            <th class="text-center">Margin</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-center">Stok</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                            @php
                                $hargaBomProduk = $produk->harga_bom ?? 0;
                                $margin = (float) ($produk->margin_percent ?? 30);
                                $hargaJual = $produk->harga_jual ?? $hargaBomProduk * (1 + ($margin / 100));
                                $stok = (float) $produk->stok;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>{{ $produk->deskripsi ? Str::limit($produk->deskripsi, 50) : '-' }}</td>
                                <td class="text-right">Rp {{ number_format($hargaBomProduk, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($margin, 0, ',', '.') }}%</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                                <td class="text-center {{ $stok <= 0 ? 'text-danger font-weight-bold' : '' }}">
                                    {{ number_format($stok, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('master-data.produk.edit', $produk->id) }}" 
                                           class="btn btn-sm btn-warning" 
                                           data-toggle="tooltip" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.produk.destroy', $produk->id) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data produk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
    .text-right {
        text-align: right !important;
    }
    .text-center {
        text-align: center !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize DataTable
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy();
        }
        
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": [0, 7] 
                },
                { 
                    "searchable": false, 
                    "targets": [0, 7] 
                },
                {
                    "className": "text-right",
                    "targets": [3, 5]
                },
                {
                    "className": "text-center",
                    "targets": [4, 6, 7]
                }
            ],
            "order": [[1, 'asc']],
            "pageLength": 25
        });
    });
</script>
@endpush
@endsection

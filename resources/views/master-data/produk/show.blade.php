@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Detail Produk</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>{{ $produk->nama_produk }}</h5>
                        <p class="text-muted">{{ $produk->deskripsi }}</p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Margin:</strong> {{ $produk->margin_percent }}%</p>
                            <p><strong>Metode BOPB:</strong> {{ $produk->bopb_method }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rate BOPB:</strong> {{ number_format($produk->bopb_rate, 0, ',', '.') }}</p>
                            <p><strong>BTKL per Unit:</strong> {{ number_format($produk->btkl_per_unit, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Daftar BOM</h5>
                        @if($produk->boms->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah</th>
                                            <th>Satuan</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($produk->boms as $bom)
                                            <tr>
                                                <td>{{ $bom->bahanBaku->nama ?? 'N/A' }}</td>
                                                <td>{{ $bom->jumlah }}</td>
                                                <td>{{ $bom->satuan }}</td>
                                                <td>{{ number_format($bom->bahanBaku->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                                <td>{{ number_format(($bom->bahanBaku->harga_satuan ?? 0) * $bom->jumlah, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                Belum ada BOM untuk produk ini.
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('master-data.produk.edit', $produk->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

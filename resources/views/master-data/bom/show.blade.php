@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail BOM: {{ $bom->produk->nama_produk }}</h1>
        <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informasi Dasar</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Produk</th>
                            <td>{{ $bom->produk->nama_produk }}</td>
                        </tr>
                        <tr>
                            <th>Periode</th>
                            <td>{{ date('F Y', strtotime($bom->periode)) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Rincian Bahan Baku</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th class="text-end">Kuantitas</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bom->details as $detail)
                            <tr>
                                <td>{{ $detail->bahanBaku->nama_bahan ?? '-' }}</td>
                                <td class="text-end">{{ number_format((float)($detail->kuantitas ?? 0), 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format((float)($detail->harga_satuan ?? 0), 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format((float)($detail->subtotal ?? (($detail->kuantitas ?? 0)*($detail->harga_satuan ?? 0))), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="table-active">
                            <th colspan="3" class="text-end">Total Bahan</th>
                            <th class="text-end">Rp {{ number_format((float)($bom->total_biaya ?? 0), 0, ',', '.') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Perhitungan Biaya Produksi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <table class="table table-bordered">
                        <tr>
                            <th width="60%">1. Total Biaya Bahan Baku</th>
                            <td class="text-end">Rp {{ number_format($bom->total_biaya, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>2. Biaya Tenaga Kerja Langsung (BTKL)</th>
                            <td class="text-end">Rp {{ number_format($bom->total_btkl, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>3. Biaya Overhead Pabrik (BOP)</th>
                            <td class="text-end">
                                Rp {{ number_format($bom->total_bop, 0, ',', '.') }}
                                <small class="d-block text-muted">BOP Rate: {{ number_format($bom->bop_rate * 100, 2) }}% dari BTKL</small>
                            </td>
                        </tr>
                        <tr class="table-active">
                            <th>Total Biaya Produksi</th>
                            <th class="text-end">Rp {{ number_format($bom->total_biaya + $bom->total_btkl + $bom->total_bop, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th>Jumlah Unit</th>
                            <td class="text-end">{{ $bom->jumlah }} {{ $bom->satuan_resep }}</td>
                        </tr>
                        <tr class="table-active">
                            <th>Biaya Produksi per Unit</th>
                            <th class="text-end">
                                Rp {{ number_format(($bom->total_biaya + $bom->total_btkl + $bom->total_bop) / $bom->jumlah, 0, ',', '.') }}
                            </th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('master-data.bom.edit', $bom->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <form action="{{ route('master-data.bom.destroy', $bom->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus BOM ini?')">
                <i class="fas fa-trash me-1"></i> Hapus
            </button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Biaya Bahan Baku - Simple')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-edit me-2"></i>Edit Biaya Bahan - {{ $produk->nama_produk }}
        </h2>
        <a href="{{ route('master-data.biaya-bahan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="alert alert-info">
        <h6><i class="fas fa-info-circle me-2"></i>Mode Sederhana</h6>
        <p class="mb-0">Ini adalah versi sederhana dari form edit yang tidak menggunakan JavaScript kompleks. Gunakan ini jika form utama mengalami masalah.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <h6>Terjadi kesalahan:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Edit Biaya Bahan Baku</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.biaya-bahan.update', $produk->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($biayaBahanList as $index => $detail)
                                <tr>
                                    <td>
                                        <select name="bahan_baku[{{ $index }}][id]" class="form-select" required>
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            @foreach($bahanBakus as $bahanBaku)
                                                <option value="{{ $bahanBaku->id }}" 
                                                        {{ $detail->bahan_baku_id == $bahanBaku->id ? 'selected' : '' }}>
                                                    {{ $bahanBaku->nama_bahan }} - Rp {{ number_format($bahanBaku->harga_satuan, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="bahan_baku[{{ $index }}][jumlah]" 
                                               class="form-control" 
                                               value="{{ $detail->jumlah }}" 
                                               step="0.01" 
                                               min="0.01" 
                                               required>
                                    </td>
                                    <td>
                                        <select name="bahan_baku[{{ $index }}][satuan]" class="form-select" required>
                                            <option value="">-- Pilih Satuan --</option>
                                            @foreach($satuans as $satuan)
                                                <option value="{{ $satuan->nama }}" 
                                                        {{ $detail->satuan == $satuan->nama ? 'selected' : '' }}>
                                                    {{ $satuan->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="text-end">
                                            Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-end fw-bold text-success">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-warning">
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-end">
                                    Rp {{ number_format($biayaBahanList->sum('subtotal'), 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('master-data.biaya-bahan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Debug Info -->
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Debug Info</h6>
        </div>
        <div class="card-body">
            <p><strong>Product ID:</strong> {{ $produk->id }}</p>
            <p><strong>Biaya Bahan Count:</strong> {{ $biayaBahanList->count() }}</p>
            <p><strong>Form Action:</strong> {{ route('master-data.biaya-bahan.update', $produk->id) }}</p>
        </div>
    </div>
</div>
@endsection
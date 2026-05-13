@extends('layouts.app')

@section('title', 'Pengaturan Paket Menu')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Pengaturan Paket Menu</h4>
            <p class="text-muted mb-0">Kelola paket menu untuk penjualan</p>
        </div>
        <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-paket-list" type="button">
                        <i class="fas fa-list me-2"></i>Daftar Paket Menu
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-paket-form" type="button">
                        <i class="fas fa-plus me-2"></i>Tambah Paket Menu
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Tab Daftar Paket Menu -->
                <div class="tab-pane fade show active" id="tab-paket-list">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Paket</th>
                                    <th>Isi Paket</th>
                                    <th class="text-end">Harga Normal</th>
                                    <th class="text-end">Harga Paket</th>
                                    <th class="text-center">Diskon</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="paket-tbody">
                                @forelse($paketMenus as $index => $paket)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $paket->nama_paket }}</strong></td>
                                    <td>
                                        @foreach($paket->details as $detail)
                                        <div>• {{ $detail->produk->nama_produk ?? '-' }} ({{ $detail->jumlah }} porsi)</div>
                                        @endforeach
                                    </td>
                                    <td class="text-end text-decoration-line-through text-muted">Rp {{ number_format($paket->harga_normal, 0, ',', '.') }}</td>
                                    <td class="text-end text-success fw-bold">Rp {{ number_format($paket->harga_paket, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ number_format($paket->diskon_persen, 2) }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" {{ $paket->status === 'aktif' ? 'checked' : '' }} 
                                                   onchange="togglePaketStatus({{ $paket->id }}, this.checked)">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning me-1" onclick="editPaket({{ json_encode($paket) }})">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePaket({{ $paket->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data paket menu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
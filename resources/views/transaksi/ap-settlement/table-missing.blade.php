@extends('layouts.app')

@push('styles')
<style>
/* AP Settlement Error Page - ULTRA AGGRESSIVE WHITE TEXT */
body .ap-settlement-error *,
body .ap-settlement-error *::before,
body .ap-settlement-error *::after,
.container.ap-settlement-error *,
.container.ap-settlement-error *::before,
.container.ap-settlement-error *::after {
    color: white !important;
}

body .ap-settlement-error .card,
.container.ap-settlement-error .card {
    background: rgba(44, 44, 62, 0.9) !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
}

body .ap-settlement-error .card-body,
.container.ap-settlement-error .card-body {
    background: rgba(44, 44, 62, 0.8) !important;
    color: white !important;
}

body .ap-settlement-error .alert-danger,
.container.ap-settlement-error .alert-danger {
    background: rgba(220, 53, 69, 0.2) !important;
    border: 1px solid rgba(220, 53, 69, 0.3) !important;
    color: white !important;
}

body .ap-settlement-error .card-header,
.container.ap-settlement-error .card-header {
    color: white !important;
}

body .ap-settlement-error h1, body .ap-settlement-error h2, body .ap-settlement-error h3, 
body .ap-settlement-error h4, body .ap-settlement-error h5, body .ap-settlement-error h6,
body .ap-settlement-error p, body .ap-settlement-error li, body .ap-settlement-error span,
body .ap-settlement-error div, body .ap-settlement-error strong, body .ap-settlement-error small,
.container.ap-settlement-error h1, .container.ap-settlement-error h2, .container.ap-settlement-error h3,
.container.ap-settlement-error h4, .container.ap-settlement-error h5, .container.ap-settlement-error h6,
.container.ap-settlement-error p, .container.ap-settlement-error li, .container.ap-settlement-error span,
.container.ap-settlement-error div, .container.ap-settlement-error strong, .container.ap-settlement-error small {
    color: white !important;
}

body .ap-settlement-error code,
.container.ap-settlement-error code {
    background: rgba(102, 126, 234, 0.4) !important;
    color: white !important;
    padding: 4px 8px !important;
    border-radius: 4px !important;
    font-weight: bold !important;
}

body .ap-settlement-error .text-muted,
.container.ap-settlement-error .text-muted {
    color: rgba(255,255,255,0.7) !important;
}

/* Override Bootstrap classes */
body .ap-settlement-error .alert,
body .ap-settlement-error .card,
body .ap-settlement-error .card-body,
body .ap-settlement-error .card-header,
.container.ap-settlement-error .alert,
.container.ap-settlement-error .card,
.container.ap-settlement-error .card-body,
.container.ap-settlement-error .card-header {
    color: white !important;
}
</style>
@endpush

@section('content')
<div class="container ap-settlement-error" style="color: white !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" style="background: rgba(44, 44, 62, 0.9) !important; color: white !important;">
                <div class="card-header bg-danger text-white" style="color: white !important;">
                    <h4 style="color: white !important;"><i class="fas fa-exclamation-triangle"></i> Tabel Pelunasan Utang Tidak Ditemukan</h4>
                </div>
                <div class="card-body" style="background: rgba(44, 44, 62, 0.8) !important; color: white !important;">
                    <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.2) !important; color: white !important; border: 1px solid rgba(220, 53, 69, 0.3) !important;">
                        <h5 style="color: white !important;">❌ Kesalahan Basis Data</h5>
                        <p style="color: white !important;">Tabel <code style="background: rgba(102, 126, 234, 0.4) !important; color: white !important; padding: 4px 8px !important; border-radius: 4px !important;">pelunasan_utangs</code> belum dibuat di basis data.</p>
                    </div>
                    
                    <h5 style="color: white !important;">🔧 Cara Memperbaiki:</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary" style="background: rgba(44, 44, 62, 0.8) !important;">
                                <div class="card-header bg-primary text-white" style="color: white !important;">
                                    <strong style="color: white !important;">Pilihan 1: Skrip Otomatis</strong>
                                </div>
                                <div class="card-body" style="background: rgba(44, 44, 62, 0.7) !important; color: white !important;">
                                    <p style="color: white !important;">Klik tautan di bawah untuk menjalankan skrip perbaikan:</p>
                                    <a href="{{ url('/jalankan-migration.php') }}" class="btn btn-primary btn-block" target="_blank">
                                        🚀 Jalankan Migration Laravel
                                    </a>
                                    <small class="text-muted" style="color: rgba(255,255,255,0.7) !important;">Menggunakan Laravel Migration & Seeder</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-info" style="background: rgba(44, 44, 62, 0.8) !important;">
                                <div class="card-header bg-info text-white" style="color: white !important;">
                                    <strong style="color: white !important;">Pilihan 2: Manual SQL</strong>
                                </div>
                                <div class="card-body" style="background: rgba(44, 44, 62, 0.7) !important; color: white !important;">
                                    <p style="color: white !important;">Jalankan berkas SQL di phpMyAdmin:</p>
                                    <code style="background: rgba(102, 126, 234, 0.4) !important; color: white !important; padding: 4px 8px !important; border-radius: 4px !important;">buat-tabel-pelunasan-utangs.sql</code>
                                    <br><br>
                                    <div class="table-responsive">
                                        <table class="table table-borderless align-middle mb-0 custom-table">
                                            <thead>
                                                <tr>
                                                    <th class="ps-3 py-3">#</th>
                                                    <th class="ps-3 py-3">Vendor</th>
                                                    <th class="ps-3 py-3">No. Pembelian</th>
                                                    <th class="ps-3 py-3">Total Utang</th>
                                                    <th class="ps-3 py-3">Status</th>
                                                    <th class="ps-3 py-3">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($settlements ?? []) as $settlement)
                                                    <tr>
                                                        <td>{{ $loop->index + 1 }}</td>
                                                        <td>{{ $settlement->vendor->nama_vendor }}</td>
                                                        <td>{{ $settlement->no_pembelian }}</td>
                                                        <td>Rp {{ number_format($settlement->total_utang, 0, ',', '.') }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $settlement->status == 'Lunas' ? 'success' : 'warning' }}">
                                                                {{ $settlement->status }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('transaksi.ap-settlement.show', $settlement->id) }}" class="btn btn-sm btn-info">
                                                                <i class="bi bi-eye"></i> Lihat
                                                            </a>
                                                            <form action="{{ route('transaksi.ap-settlement.destroy', $settlement->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4">
                                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                                            <div class="text-muted">Belum ada data pelunasan utang</div>
                                                            <div class="text-muted">Silakan tambahkan data pelunasan terlebih dahulu</div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5 style="color: white !important;">📋 Langkah-langkah:</h5>
                        <ol style="color: white !important;">
                            <li style="color: white !important;">Pilih salah satu pilihan di atas</li>
                            <li style="color: white !important;">Jalankan skrip atau SQL</li>
                            <li style="color: white !important;">Muat ulang halaman ini</li>
                            <li style="color: white !important;">Fitur Pelunasan Utang akan berfungsi</li>
                        </ol>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                        </a>
                        <button onclick="location.reload()" class="btn btn-success">
                            <i class="fas fa-sync"></i> Muat Ulang Halaman
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
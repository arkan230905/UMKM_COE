@extends('layouts.app')

@section('title', 'Bukti Faktur Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-invoice me-2"></i>Bukti Faktur Pembelian
        </h2>
        <div>
            <a href="{{ url('/transaksi/pembelian/' . $pembelian->id) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail
            </a>
        </div>
    </div>

    <!-- Pembelian Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Informasi Pembelian
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Nomor Pembelian:</strong><br>
                            <span class="text-primary fs-5">{{ $pembelian->nomor_pembelian }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Nomor Faktur:</strong><br>
                            <span class="text-primary fs-5">{{ $pembelian->nomor_faktur }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Tanggal:</strong><br>
                            <span class="text-primary fs-5">{{ date('d M Y', strtotime($pembelian->tanggal)) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Supplier:</strong><br>
                            <span class="text-primary fs-5">{{ $pembelian->vendor->nama_vendor ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>Metode Pembayaran:</strong><br>
                            <span class="text-primary fs-5">{{ $pembelian->payment_method ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Bank:</strong><br>
                            <span class="text-primary fs-5">{{ $pembelian->bank->nama_bank ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Total:</strong><br>
                            <span class="text-success fs-5">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bukti Faktur -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-image me-2"></i>Bukti Faktur
                    </h6>
                </div>
                <div class="card-body text-center">
                    @if($filename && $buktiPath)
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>File:</strong> {{ $filename }}
                            <br>
                            <small class="text-muted">Path: {{ $buktiPath }}</small>
                        </div>
                        
                        <!-- Check if file exists -->
                        @if(file_exists($buktiPath))
                            <div class="mb-3">
                                <h5 class="text-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Bukti Faktur Tersedia
                                </h5>
                                
                                <!-- Display image with direct URL -->
                                <div class="border rounded p-3 bg-light">
                                    <img src="{{ url('/bukti-faktur/' . $filename) }}" 
                                         alt="Bukti Faktur {{ $filename }}" 
                                         class="img-fluid" 
                                         style="max-width: 100%; height: auto;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}';">
                                </div>
                                
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Bukti Faktur berhasil ditampilkan!</strong>
                                    <br>
                                    <small class="text-muted">
                                        File: {{ $filename }}<br>
                                        URL: {{ url('/bukti-faktur/' . $pembelian->id . '/' . $filename) }}
                                    </small>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>File Tidak Ditemukan</strong>
                                <br>
                                <small class="text-muted">
                                    File: {{ $filename }}<br>
                                    Path: {{ $buktiPath }}<br>
                                    Kemungkinan file telah dihapus atau dipindahkan.
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Tidak Ada Bukti Faktur</strong>
                            <br>
                            <small class="text-muted">
                                Pembelian ini tidak memiliki bukti faktur yang tersimpan.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Download Button -->
    @if($filename && $buktiPath && file_exists($buktiPath))
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <a href="{{ url('/bukti-faktur/' . $pembelian->id . '/' . $filename) }}" 
                   class="btn btn-primary" 
                   download="{{ $filename }}">
                    <i class="fas fa-download me-2"></i>
                    Download Bukti Faktur
                </a>
            </div>
        </div>
    @endif
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    font-weight: 600;
}

.border {
    border: 2px solid #dee2e6 !important;
}

.img-fluid {
    max-width: 100%;
    height: auto;
}
</style>
@endsection

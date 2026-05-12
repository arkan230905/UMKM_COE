@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4><i class="fas fa-exclamation-circle"></i> Error pada AP Settlement</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5>⚠️ Terjadi Kesalahan</h5>
                        <p><strong>Error:</strong> {{ $error }}</p>
                    </div>
                    
                    <h5>🔧 Kemungkinan Penyebab:</h5>
                    <ul>
                        <li>Tabel <code>ap_settlements</code> belum dibuat</li>
                        <li>Struktur tabel tidak sesuai</li>
                        <li>Koneksi database bermasalah</li>
                        <li>Model atau relasi bermasalah</li>
                    </ul>
                    
                    <h5>💡 Solusi:</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ url('/check-ap-settlements.php') }}" class="btn btn-info btn-block" target="_blank">
                                🔍 Cek Status Tabel
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ url('/fix-ap-settlements-now.php') }}" class="btn btn-primary btn-block" target="_blank">
                                🚀 Jalankan Fix Script
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button onclick="location.reload()" class="btn btn-success btn-block">
                                🔄 Coba Lagi
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
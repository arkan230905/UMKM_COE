@extends('layouts.app')

@section('content')
<div class="container text-dark">
    <h2 class="mb-4 text-dark">TEST - Edit Aset (File Baru)</h2>
    
    <div class="alert alert-success">
        <strong>File baru berhasil dibuat!</strong> Jika Anda melihat pesan ini, berarti file view sudah ter-update.
    </div>

    <div class="card">
        <div class="card-body">
            <p>Ini adalah file test untuk memastikan view cache sudah clear.</p>
            
            <!-- Section COA -->
            <div class="card border-0 shadow-sm mb-4 bg-white">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Akun COA untuk Jurnal</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Section COA sudah muncul!</strong> Ini membuktikan file sudah ter-update.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
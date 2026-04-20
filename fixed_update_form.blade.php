@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Update Company Information</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('kelola-catalog.settings.company.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="{{ $company->nama ?? '' }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="catalog_description" class="form-label">Description</label>
                            <textarea class="form-control" id="catalog_description" name="catalog_description" rows="4" required>{{ $company->catalog_description ?? '' }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $company->email ?? '' }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telepon" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="telepon" name="telepon" value="{{ $company->telepon ?? '' }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Address</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" value="{{ $company->alamat ?? '' }}" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Update Semua Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

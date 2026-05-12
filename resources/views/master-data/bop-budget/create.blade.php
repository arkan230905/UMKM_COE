@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card bg-dark text-white">
        <div class="card-header bg-primary">
            <h5 class="mb-0">Tambah BOP Budget</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.bop-budget.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="periode" class="text-white">Periode</label>
                            <input type="month" name="periode" id="periode" 
                                   class="form-control bg-secondary text-white @error('periode') is-invalid @enderror" 
                                   value="{{ old('periode', $periode) }}" required>
                            @error('periode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="coa_id" class="text-white">Akun COA</label>
                            <select name="coa_id" id="coa_id" 
                                    class="form-control select2 bg-secondary text-white @error('coa_id') is-invalid @enderror" required>
                                <option value="">Pilih Akun COA</option>
                                @foreach($coas as $coa)
                                    <option value="{{ $coa->id }}" {{ old('coa_id') == $coa->id ? 'selected' : '' }}>
                                        {{ $coa->kode }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                            @error('coa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="jumlah_budget" class="text-white">Jumlah Budget</label>
                            <input type="number" name="jumlah_budget" id="jumlah_budget" 
                                   class="form-control bg-secondary text-white @error('jumlah_budget') is-invalid @enderror" 
                                   value="{{ old('jumlah_budget') }}" required min="0" step="0.01">
                            @error('jumlah_budget')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label for="keterangan" class="text-white">Keterangan (Opsional)</label>
                            <textarea name="keterangan" id="keterangan" rows="2" 
                                      class="form-control bg-secondary text-white @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('master-data.bop-budget.index') }}?periode={{ $periode }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .card-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .form-control, .select2-selection {
        background-color: #2c3e50 !important;
        color: white !important;
        border: 1px solid #4a6b8a;
    }
    .form-control:focus, .select2-selection:focus {
        background-color: #2c3e50 !important;
        color: white !important;
        border-color: #4a6b8a;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: white transparent transparent transparent;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3498db;
        color: white;
    }
    .select2-dropdown {
        background-color: #2c3e50;
        border: 1px solid #4a6b8a;
    }
    .select2-results__option {
        color: white;
        padding: 8px 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: white !important;
    }
    .bg-secondary {
        background-color: #2c3e50 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Pilih Akun COA',
            dropdownParent: $('.card-body')
        });
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Tambah BOP Budget</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.bop-budget.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="periode">Periode</label>
                            <input type="month" name="periode" id="periode" 
                                   class="form-control @error('periode') is-invalid @enderror" 
                                   value="{{ old('periode', $periode) }}" required>
                            @error('periode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="coa_id">Akun COA</label>
                            <select name="coa_id" id="coa_id" 
                                    class="form-control select2 @error('coa_id') is-invalid @enderror" required>
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
                            <label for="jumlah_budget">Jumlah Budget</label>
                            <input type="number" name="jumlah_budget" id="jumlah_budget" 
                                   class="form-control @error('jumlah_budget') is-invalid @enderror" 
                                   value="{{ old('jumlah_budget') }}" required min="0" step="0.01">
                            @error('jumlah_budget')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label for="keterangan">Keterangan (Opsional)</label>
                            <textarea name="keterangan" id="keterangan" rows="2" 
                                      class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        padding-top: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 6px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Pilih Akun COA',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
@endsection

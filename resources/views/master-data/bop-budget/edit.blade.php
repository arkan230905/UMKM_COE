@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Edit BOP Budget - {{ $bopBudget->nama_akun }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('bop-budget.update', $bopBudget->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Periode</label>
                            <input type="text" class="form-control" 
                                   value="{{ \Carbon\Carbon::parse($bopBudget->periode)->isoFormat('MMMM YYYY') }}" 
                                   readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Akun COA</label>
                            <input type="text" class="form-control" 
                                   value="{{ $bopBudget->coa->kode }} - {{ $bopBudget->nama_akun }}" 
                                   readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="jumlah_budget">Jumlah Budget</label>
                            <input type="number" name="jumlah_budget" id="jumlah_budget" 
                                   class="form-control @error('jumlah_budget') is-invalid @enderror" 
                                   value="{{ old('jumlah_budget', $bopBudget->jumlah_budget) }}" 
                                   required min="0" step="0.01">
                            @error('jumlah_budget')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label for="keterangan">Keterangan (Opsional)</label>
                            <textarea name="keterangan" id="keterangan" rows="2" 
                                      class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $bopBudget->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('bop-budget.index') }}?periode={{ $bopBudget->periode }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

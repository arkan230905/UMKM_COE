@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i> Edit Budget BOP
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('master-data.bop.update', $bop->id) }}" method="POST" id="editBopForm">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kode_akun" class="form-label fw-bold">Kode Akun</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $bop->coa->kode_akun }} - {{ $bop->coa->nama_akun }}" 
                                       readonly>
                                <input type="hidden" name="kode_akun" value="{{ $bop->kode_akun }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="budget_display" class="form-label fw-bold">Nominal Budget <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-success text-white fw-bold">Rp</span>
                                    <input type="text" 
                                           class="form-control form-control-lg money-input" 
                                           id="budget_display" 
                                           value="{{ number_format($bop->budget, 0, ',', '.') }}" 
                                           placeholder="0"
                                           style="font-size: 1.25rem; font-weight: 500;">
                                    <input type="hidden" name="budget" id="budget" value="{{ $bop->budget }}">
                                </div>
                                <small class="text-muted money-hint"></small>
                                @error('budget')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="keterangan" class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" rows="3" class="form-control">{{ old('keterangan', $bop->keterangan) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('master-data.bop.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Money formatting functions
        const formatID = (val) => {
            if (val === null || val === undefined || val === '') return '';
            let v = String(val).replace(/[^0-9]/g, '');
            if (!v) return '';
            return v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };
        
        const toNumber = (formatted) => {
            if (!formatted) return 0;
            let s = String(formatted).replace(/\./g, '');
            let n = parseInt(s, 10);
            return isNaN(n) ? 0 : n;
        };
        
        const compactID = (n) => {
            if (n === 0) return '';
            const u = [
                {v:1e12, s:' triliun'},
                {v:1e9, s:' miliar'},
                {v:1e6, s:' juta'},
                {v:1e3, s:' ribu'},
            ];
            for (const it of u) {
                if (n >= it.v) {
                    const val = (n / it.v).toFixed(2).replace(/\.00$/,'').replace(/\.0$/,'');
                    return '= ' + val + it.s;
                }
            }
            return '= ' + n + ' rupiah';
        };

        // Setup money input
        const displayInput = document.getElementById('budget_display');
        const hiddenInput = document.getElementById('budget');
        const hint = document.querySelector('.money-hint');
        
        const updateHint = () => {
            const num = toNumber(displayInput.value);
            hint.textContent = compactID(num);
        };
        
        displayInput.addEventListener('input', function() {
            const num = toNumber(this.value);
            this.value = formatID(num);
            hiddenInput.value = num;
            updateHint();
        });
        
        displayInput.addEventListener('blur', function() {
            const num = toNumber(this.value);
            this.value = formatID(num);
            hiddenInput.value = num;
            updateHint();
        });
        
        // Initialize
        updateHint();

        // Validasi form sebelum submit
        document.getElementById('editBopForm').addEventListener('submit', function(e) {
            const budget = document.getElementById('budget').value;
            
            if (!budget || parseFloat(budget) <= 0) {
                e.preventDefault();
                alert('Nominal budget harus lebih dari 0');
                return false;
            }
            
            // Tampilkan loading
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
            }
            
            return true;
        });
    });
</script>
@endsection

@endsection
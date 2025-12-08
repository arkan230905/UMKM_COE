@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0 text-white">Input Budget BOP</h2>
        <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Form Input Budget BOP</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.bop.store') }}" method="POST" id="bopForm">
                @csrf
                
                <!-- Akun BOP yang Dipilih -->
                <div class="alert alert-info mb-4" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="fas fa-info-circle me-2"></i>Akun BOP yang Dipilih:
                    </h6>
                    <h5 class="mb-0 fw-bold text-primary" id="selected_akun_display">
                        @if(request('kode_akun'))
                            {{ request('kode_akun') }} - {{ $akunBeban->where('kode_akun', request('kode_akun'))->first()->nama_akun ?? '' }}
                        @else
                            Pilih akun dari dropdown di bawah
                        @endif
                    </h5>
                </div>

                <!-- Pilih Akun BOP -->
                <div class="mb-3">
                    <label for="kode_akun" class="form-label fw-bold text-white">
                        Akun BOP <span class="text-danger">*</span>
                    </label>
                    <select class="form-select bg-dark text-white @error('kode_akun') is-invalid @enderror" 
                            id="kode_akun" 
                            name="kode_akun" 
                            required
                            onchange="updateSelectedAkun()">
                        <option value="">-- Pilih Akun BOP --</option>
                        @foreach($akunBeban as $akun)
                            <option value="{{ $akun->kode_akun }}" 
                                    data-nama="{{ $akun->nama_akun }}"
                                    {{ old('kode_akun', request('kode_akun')) == $akun->kode_akun ? 'selected' : '' }}>
                                {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                            </option>
                        @endforeach
                    </select>
                    @error('kode_akun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nominal Budget -->
                <div class="mb-3">
                    <label for="budget_display" class="form-label fw-bold text-white">
                        Nominal Budget <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-success text-white fw-bold">Rp</span>
                        <input type="text" 
                               class="form-control form-control-lg bg-dark text-white money-input @error('budget') is-invalid @enderror" 
                               id="budget_display" 
                               value="{{ old('budget') ? number_format(old('budget'), 0, ',', '.') : '' }}"
                               placeholder="Ketik nominal..."
                               style="color: #ffffff !important; font-size: 1.25rem; font-weight: 500;"
                               required>
                        <input type="hidden" name="budget" id="budget" value="{{ old('budget', 0) }}">
                    </div>
                    <small class="text-success money-hint" style="font-size: 0.95rem;"></small>
                    @error('budget')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div class="mb-4">
                    <label for="keterangan" class="form-label fw-bold text-white">
                        Keterangan <small class="text-white">(Opsional)</small>
                    </label>
                    <textarea class="form-control bg-dark text-white @error('keterangan') is-invalid @enderror" 
                              id="keterangan" 
                              name="keterangan" 
                              rows="3"
                              placeholder="Tambahkan keterangan jika diperlukan..."
                              style="color: #ffffff !important;">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-1"></i> Simpan Budget
                    </button>
                    <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateSelectedAkun() {
        const select = document.getElementById('kode_akun');
        const selectedOption = select.options[select.selectedIndex];
        const display = document.getElementById('selected_akun_display');
        
        if (selectedOption.value) {
            const kodeAkun = selectedOption.value;
            const namaAkun = selectedOption.getAttribute('data-nama');
            display.textContent = kodeAkun + ' - ' + namaAkun;
        } else {
            display.textContent = 'Pilih akun dari dropdown di bawah';
        }
    }

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
        if (displayInput.value) {
            displayInput.value = formatID(toNumber(displayInput.value));
            updateHint();
        }

        // Validasi form sebelum submit
        document.getElementById('bopForm').addEventListener('submit', function(e) {
            // Pastikan hidden input terupdate
            const displayVal = document.getElementById('budget_display').value;
            const num = toNumber(displayVal);
            document.getElementById('budget').value = num;
            
            if (!num || num <= 0) {
                e.preventDefault();
                alert('Nominal budget harus diisi dan lebih dari 0');
                document.getElementById('budget_display').focus();
                return false;
            }
            
            // Tampilkan loading
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
        });
    });
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Bahan Pendukung</h1>
        <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.bahan-pendukung.update', $bahanPendukung) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="row">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Kode Bahan</label>
                            <input type="text" class="form-control" value="{{ $bahanPendukung->kode_bahan }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                                   value="{{ old('nama_bahan', $bahanPendukung->nama_bahan) }}" required>
                            @error('nama_bahan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                                    @foreach($kategoris as $kat)
                                        <option value="{{ $kat->id }}" {{ ($bahanPendukung->kategori_id ?? '') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('master-data.kategori-bahan-pendukung.index') }}" class="btn btn-outline-secondary" title="Kelola Kategori">
                                    <i class="fas fa-cog"></i>
                                </a>
                            </div>
                            @error('kategori_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan_id" class="form-select @error('satuan_id') is-invalid @enderror" required>
                                @foreach($satuans as $satuan)
                                    <option value="{{ $satuan->id }}" {{ $bahanPendukung->satuan_id == $satuan->id ? 'selected' : '' }}>
                                        {{ $satuan->nama }} ({{ $satuan->kode }})
                                    </option>
                                @endforeach
                            </select>
                            @error('satuan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Harga per Satuan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="harga_satuan" class="form-control number-input" 
                                       value="{{ old('harga_satuan', number_format($bahanPendukung->harga_satuan, 0, ',', '.')) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="text" name="stok" class="form-control decimal-input" 
                                   value="{{ old('stok', $bahanPendukung->stok ? rtrim(rtrim(number_format($bahanPendukung->stok, 5, ',', '.'), '0'), ',') : '') }}">
                            <small class="text-muted">Stok saat ini</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok Minimum</label>
                            <input type="text" name="stok_minimum" class="form-control decimal-input" 
                                   value="{{ old('stok_minimum', $bahanPendukung->stok_minimum ? rtrim(rtrim(number_format($bahanPendukung->stok_minimum, 5, ',', '.'), '0'), ',') : '') }}">
                            <small class="text-muted">Batas minimum</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                       {{ $bahanPendukung->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $bahanPendukung->deskripsi) }}</textarea>
                </div>

                <!-- Sub Satuan Section -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-exchange-alt me-2"></i>Konversi Sub Satuan
                            <span class="text-danger">*</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Contoh:</strong> Jika satuan utama adalah Kilogram, maka:
                                    <br>• Sub Satuan 1: 1 Kilogram = 1000 Gram
                                    <br>• Sub Satuan 2: 1 Kilogram = 3 Potong  
                                    <br>• Sub Satuan 3: 2 Kilogram = 1 Ekor
                                    <br><small class="text-muted">Kolom "Satuan Utama" akan otomatis terisi sesuai pilihan satuan utama di atas.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Sub Satuan 1 -->
                        <div class="row align-items-end mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Konversi 1 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_1_konversi" class="form-control decimal-input @error('sub_satuan_1_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_1_konversi', $bahanPendukung->sub_satuan_1_konversi ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_1_konversi, 5, ',', '.'), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_1_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Satuan Utama</label>
                                <input type="text" class="form-control satuan-utama-text" value="{{ $bahanPendukung->satuan->nama ?? 'Pilih Satuan Utama' }}" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 1 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_1_nilai" class="form-control decimal-input @error('sub_satuan_1_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_1_nilai', $bahanPendukung->sub_satuan_1_nilai ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_1_nilai, 5, ',', '.'), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_1_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 1 <span class="text-danger">*</span></label>
                                <select name="sub_satuan_1_id" class="form-select @error('sub_satuan_1_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_1_id', $bahanPendukung->sub_satuan_1_id) == $satuan->id ? 'selected' : '' }}>
                                            {{ $satuan->nama }} ({{ $satuan->kode }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('sub_satuan_1_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearSubSatuan(1)" title="Reset">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Sub Satuan 2 -->
                        <div class="row align-items-end mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Konversi 2 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_2_konversi" class="form-control decimal-input @error('sub_satuan_2_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_konversi', $bahanPendukung->sub_satuan_2_konversi ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_2_konversi, 5, ',', '.'), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_2_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control satuan-utama-text" value="{{ $bahanPendukung->satuan->nama ?? 'Pilih Satuan Utama' }}" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 2 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_2_nilai" class="form-control decimal-input @error('sub_satuan_2_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_nilai', $bahanPendukung->sub_satuan_2_nilai ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_2_nilai, 5, ',', '.'), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_2_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 2 <span class="text-danger">*</span></label>
                                <select name="sub_satuan_2_id" class="form-select @error('sub_satuan_2_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_2_id', $bahanPendukung->sub_satuan_2_id) == $satuan->id ? 'selected' : '' }}>
                                            {{ $satuan->nama }} ({{ $satuan->kode }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('sub_satuan_2_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearSubSatuan(2)" title="Reset">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Sub Satuan 3 -->
                        <div class="row align-items-end mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Konversi 3 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_3_konversi" class="form-control decimal-input @error('sub_satuan_3_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_konversi', $bahanPendukung->sub_satuan_3_konversi ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_3_konversi, 5, ',', '.'), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_3_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control satuan-utama-text" value="{{ $bahanPendukung->satuan->nama ?? 'Pilih Satuan Utama' }}" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 3 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_3_nilai" class="form-control decimal-input @error('sub_satuan_3_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_nilai', $bahanPendukung->sub_satuan_3_nilai ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_3_nilai, 5, ',', '.'), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_3_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 3 <span class="text-danger">*</span></label>
                                <select name="sub_satuan_3_id" class="form-select @error('sub_satuan_3_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_3_id', $bahanPendukung->sub_satuan_3_id) == $satuan->id ? 'selected' : '' }}>
                                            {{ $satuan->nama }} ({{ $satuan->kode }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('sub_satuan_3_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearSubSatuan(3)" title="Reset">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function clearSubSatuan(index) {
    // Reset the sub satuan fields to default values
    document.querySelector(`input[name="sub_satuan_${index}_konversi"]`).value = '1';
    document.querySelector(`select[name="sub_satuan_${index}_id"]`).value = '';
    document.querySelector(`input[name="sub_satuan_${index}_nilai"]`).value = '1';
}

// Number formatting functions
function formatNumber(input) {
    let value = input.value.replace(/[^\d,]/g, '');
    
    if (value === '') return;
    
    // Handle comma as decimal separator
    if (value.includes(',')) {
        let parts = value.split(',');
        if (parts.length === 2) {
            // Format: integer part + comma + decimal part
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            let decimalPart = parts[1];
            input.value = integerPart + ',' + decimalPart;
        } else {
            // Only integer part
            input.value = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    } else {
        // Only integer, add thousand separators
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
}

function formatDecimal(input) {
    let value = input.value.replace(/[^\d,]/g, '');
    
    if (value === '') return;
    
    // Handle comma as decimal separator - keep it simple for decimal inputs
    input.value = value;
}

function parseFormattedNumber(value) {
    if (!value) return '';
    
    // Remove thousand separators (dots) and convert comma to dot for server
    return value.replace(/\./g, '').replace(',', '.');
}

// Update satuan utama display when main satuan changes
document.addEventListener('DOMContentLoaded', function() {
    const satuanSelect = document.querySelector('select[name="satuan_id"]');
    const satuanUtamaTexts = document.querySelectorAll('.satuan-utama-text');
    
    function updateSatuanUtamaDisplay() {
        const selectedOption = satuanSelect.options[satuanSelect.selectedIndex];
        let satuanText = 'Pilih Satuan Utama';
        
        if (selectedOption && selectedOption.value) {
            // Extract nama satuan from option text (format: "Nama (Kode)")
            const optionText = selectedOption.text;
            const satuanNama = optionText.split(' (')[0]; // Get part before " ("
            satuanText = satuanNama;
        }
        
        satuanUtamaTexts.forEach(input => {
            input.value = satuanText;
        });
    }
    
    // Initial call and event listener
    satuanSelect.addEventListener('change', updateSatuanUtamaDisplay);
    updateSatuanUtamaDisplay();
    
    // Add event listeners for number formatting
    document.querySelectorAll('.number-input').forEach(input => {
        input.addEventListener('input', function() {
            formatNumber(this);
        });
    });
    
    document.querySelectorAll('.decimal-input').forEach(input => {
        input.addEventListener('input', function() {
            formatDecimal(this);
        });
    });
    
    // Form validation and submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = [
                'sub_satuan_1_konversi', 'sub_satuan_1_id', 'sub_satuan_1_nilai',
                'sub_satuan_2_konversi', 'sub_satuan_2_id', 'sub_satuan_2_nilai',
                'sub_satuan_3_konversi', 'sub_satuan_3_id', 'sub_satuan_3_nilai'
            ];
            
            requiredFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field && (!field.value || field.value.trim() === '')) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field) {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field Sub Satuan yang wajib diisi.');
                return;
            }
            
            // Convert formatted numbers back to standard format for server processing
            document.querySelectorAll('.number-input, .decimal-input').forEach(input => {
                if (input.value) {
                    input.value = parseFormattedNumber(input.value);
                }
            });
        });
    }
});
</script>
@endpush

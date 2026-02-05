@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Bahan Baku</h1>
        <a href="{{ route('master-data.bahan-baku.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.bahan-baku.update', $bahanBaku->id) }}" method="POST" novalidate>
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
                            <input type="text" class="form-control" value="{{ $bahanBaku->kode_bahan ?? 'Auto Generate' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                                   value="{{ old('nama_bahan', $bahanBaku->nama_bahan) }}" required>
                            @error('nama_bahan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan_id" class="form-select @error('satuan_id') is-invalid @enderror" required>
                                @foreach($satuans as $satuan)
                                    <option value="{{ $satuan->id }}" {{ $bahanBaku->satuan_id == $satuan->id ? 'selected' : '' }}>
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
                                <input type="number" name="harga_satuan" class="form-control @error('harga_satuan') is-invalid @enderror" 
                                       value="{{ old('harga_satuan', $bahanBaku->harga_satuan) }}" min="0" step="100" required>
                            </div>
                            @error('harga_satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control @error('stok') is-invalid @enderror" 
                                   value="{{ old('stok', $bahanBaku->stok) }}" min="0" step="0.01">
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Stok saat ini</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok Minimum</label>
                            <input type="number" name="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror" 
                                   value="{{ old('stok_minimum', $bahanBaku->stok_minimum ?? 0) }}" min="0" step="0.01">
                            @error('stok_minimum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Batas minimum</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Harga Rata-rata</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" 
                                       value="{{ number_format($bahanBaku->harga_rata_rata ?? 0, 0, ',', '.') }}" readonly>
                            </div>
                            <small class="text-muted">Harga rata-rata pembelian</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi bahan baku (opsional)">{{ old('deskripsi', $bahanBaku->deskripsi ?? '') }}</textarea>
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
                                <input type="text" name="sub_satuan_1_konversi" class="form-control number-input @error('sub_satuan_1_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_1_konversi', $bahanBaku->sub_satuan_1_konversi ? rtrim(rtrim(number_format($bahanBaku->sub_satuan_1_konversi, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_1_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Satuan Utama</label>
                                <input type="text" class="form-control satuan-utama-text" value="{{ $bahanBaku->satuan->nama ?? 'Pilih Satuan Utama' }}" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 1 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_1_nilai" class="form-control number-input @error('sub_satuan_1_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_1_nilai', $bahanBaku->sub_satuan_1_nilai ? rtrim(rtrim(number_format($bahanBaku->sub_satuan_1_nilai, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_1_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 1 <span class="text-danger">*</span></label>
                                <select name="sub_satuan_1_id" class="form-select @error('sub_satuan_1_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_1_id', $bahanBaku->sub_satuan_1_id) == $satuan->id ? 'selected' : '' }}>
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
                                <input type="text" name="sub_satuan_2_konversi" class="form-control number-input @error('sub_satuan_2_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_konversi', $bahanBaku->sub_satuan_2_konversi ? rtrim(rtrim(number_format($bahanBaku->sub_satuan_2_konversi, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_2_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control satuan-utama-text" value="{{ $bahanBaku->satuan->nama ?? 'Pilih Satuan Utama' }}" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 2 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_2_nilai" class="form-control number-input @error('sub_satuan_2_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_nilai', $bahanBaku->sub_satuan_2_nilai ? rtrim(rtrim(number_format($bahanBaku->sub_satuan_2_nilai, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_2_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 2 <span class="text-danger">*</span></label>
                                <select name="sub_satuan_2_id" class="form-select @error('sub_satuan_2_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_2_id', $bahanBaku->sub_satuan_2_id) == $satuan->id ? 'selected' : '' }}>
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
                                <input type="text" name="sub_satuan_3_konversi" class="form-control number-input @error('sub_satuan_3_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_konversi', $bahanBaku->sub_satuan_3_konversi ? rtrim(rtrim(number_format($bahanBaku->sub_satuan_3_konversi, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_3_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control satuan-utama-text" value="{{ $bahanBaku->satuan->nama ?? 'Pilih Satuan Utama' }}" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 3 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_3_nilai" class="form-control number-input @error('sub_satuan_3_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_nilai', $bahanBaku->sub_satuan_3_nilai ? rtrim(rtrim(number_format($bahanBaku->sub_satuan_3_nilai, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_3_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 3 <span class="text-danger">*</span></label>
                                <select name="sub_satuan_3_id" class="form-select @error('sub_satuan_3_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_3_id', $bahanBaku->sub_satuan_3_id) == $satuan->id ? 'selected' : '' }}>
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
                    <a href="{{ route('master-data.bahan-baku.index') }}" class="btn btn-secondary">Batal</a>
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

// Handle number input with comma decimal separator
function setupNumberInputs() {
    const numberInputs = document.querySelectorAll('.number-input');
    
    numberInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value;
            
            // Allow numbers, comma, and dot
            value = value.replace(/[^0-9,\.]/g, '');
            
            // Replace multiple commas/dots with single one
            value = value.replace(/[,\.]{2,}/g, ',');
            
            // Ensure only one decimal separator
            const parts = value.split(/[,\.]/);
            if (parts.length > 2) {
                value = parts[0] + ',' + parts.slice(1).join('');
            }
            
            e.target.value = value;
        });
        
        input.addEventListener('blur', function(e) {
            let value = e.target.value;
            if (value && !isNaN(value.replace(',', '.'))) {
                // Format the number properly
                const numValue = parseFloat(value.replace(',', '.'));
                if (numValue === Math.floor(numValue)) {
                    e.target.value = numValue.toString();
                } else {
                    e.target.value = numValue.toString().replace('.', ',');
                }
            }
        });
    });
}

// Convert comma to dot before form submission
function convertCommasToDots() {
    const numberInputs = document.querySelectorAll('.number-input');
    numberInputs.forEach(input => {
        if (input.value) {
            input.value = input.value.replace(',', '.');
        }
    });
}

// Update satuan utama display when main satuan changes
document.addEventListener('DOMContentLoaded', function() {
    const satuanSelect = document.querySelector('select[name="satuan_id"]');
    const satuanUtamaTexts = document.querySelectorAll('.satuan-utama-text');
    
    // Setup number inputs
    setupNumberInputs();
    
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
    
    // Form validation and submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Convert commas to dots before validation
            convertCommasToDots();
            
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
                    
                    // Validate number fields
                    if (fieldName.includes('konversi') || fieldName.includes('nilai')) {
                        const numValue = parseFloat(field.value);
                        if (isNaN(numValue) || numValue <= 0) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        }
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field Sub Satuan yang wajib diisi dengan nilai yang valid.');
            }
        });
    }
});
</script>
@endpush

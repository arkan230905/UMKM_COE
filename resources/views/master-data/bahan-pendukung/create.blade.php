@extends('layouts.app')

@section('title', 'Tambah Bahan Pendukung')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tambah Bahan Pendukung</h1>
        <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.bahan-pendukung.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                                   value="{{ old('nama_bahan') }}" placeholder="Contoh: Gas LPG, Minyak Goreng" required>
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
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategoris as $kat)
                                        <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
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
                            <label class="form-label">Kode Bahan</label>
                            <input type="text" name="kode_bahan" class="form-control @error('kode_bahan') is-invalid @enderror" 
                                   value="{{ old('kode_bahan') }}" placeholder="Contoh: BP001">
                            @error('kode_bahan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kosongkan untuk auto generate</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan_id" class="form-select @error('satuan_id') is-invalid @enderror" required>
                                <option value="">Pilih Satuan</option>
                                @foreach($satuans as $satuan)
                                    <option value="{{ $satuan->id }}" {{ old('satuan_id') == $satuan->id ? 'selected' : '' }}>
                                        {{ $satuan->nama }} ({{ $satuan->kode }})
                                    </option>
                                @endforeach
                            </select>
                            @error('satuan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>

                                <input type="text" name="harga_satuan" id="harga_satuan" class="form-control price-input @error('harga_satuan') is-invalid @enderror" 
                                       value="{{ old('harga_satuan', '0') }}" required>
                                <input type="hidden" name="harga_satuan_raw" id="harga_satuan_raw" value="0">

                                <input type="text" name="harga_satuan" class="form-control number-input @error('harga_satuan') is-invalid @enderror" 
                                       value="{{ old('harga_satuan') }}" placeholder="0" required>

                            </div>
                            @error('harga_satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <div class="input-group">
                                <input type="text" name="stok" class="form-control number-input @error('stok') is-invalid @enderror" 
                                       value="{{ old('stok') }}" placeholder="0">
                                <span class="input-group-text" id="satuan_utama_display"></span>
                            </div>
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Informasi: Saldo awal ini mencatat stok per tanggal 1 bulan berjalan.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok Minimum</label>
                            <div class="input-group">
                                <input type="text" name="stok_minimum" class="form-control number-input @error('stok_minimum') is-invalid @enderror" 
                                       value="{{ old('stok_minimum') }}" placeholder="0">
                                <span class="input-group-text" id="satuan_utama_display_min"></span>
                            </div>
                            @error('stok_minimum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Batas minimum</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                      rows="3" placeholder="Deskripsi bahan pendukung">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Sub Satuan Section -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-exchange-alt me-2"></i>Konversi Sub Satuan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Contoh:</strong> Jika satuan utama adalah Kilogram, maka:
                                    <br>• Sub Satuan 1: 1 Kilogram = 1000 Gram
                                    <br>• Sub Satuan 2: 1 Kilogram = 3 Botol  
                                    <br>• Sub Satuan 3: 2 Kilogram = 1 Tabung
                                    <br><small class="text-muted">Kolom "Satuan Utama" akan otomatis terisi sesuai pilihan satuan utama di atas.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Sub Satuan 1 -->
                        <div class="row align-items-end mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Konversi 1 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_1_konversi" class="form-control number-input @error('sub_satuan_1_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_1_konversi', '1') }}" placeholder="1" required>
                                @error('sub_satuan_1_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Satuan Utama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control satuan-utama-text" value="Pilih Satuan Utama" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 1 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_1_nilai" class="form-control number-input @error('sub_satuan_1_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_1_nilai', '1') }}" placeholder="1" required>
                                @error('sub_satuan_1_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 1 <span class="text-danger">*</span></label>
                                <select name="sub_satuan_1_id" class="form-select @error('sub_satuan_1_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_1_id') == $satuan->id ? 'selected' : '' }}>
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
                                <label class="form-label">Konversi 2</label>
                                <input type="text" name="sub_satuan_2_konversi" class="form-control number-input @error('sub_satuan_2_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_konversi', '1') }}" placeholder="1">
                                @error('sub_satuan_2_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control satuan-utama-text" value="Pilih Satuan Utama" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 2</label>
                                <input type="text" name="sub_satuan_2_nilai" class="form-control number-input @error('sub_satuan_2_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_nilai', '1') }}" placeholder="1">
                                @error('sub_satuan_2_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 2</label>
                                <select name="sub_satuan_2_id" class="form-select @error('sub_satuan_2_id') is-invalid @enderror">
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_2_id') == $satuan->id ? 'selected' : '' }}>
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
                                <label class="form-label">Konversi 3</label>
                                <input type="text" name="sub_satuan_3_konversi" class="form-control number-input @error('sub_satuan_3_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_konversi', '1') }}" placeholder="1">
                                @error('sub_satuan_3_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control satuan-utama-text" value="Pilih Satuan Utama" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 3</label>
                                <input type="text" name="sub_satuan_3_nilai" class="form-control number-input @error('sub_satuan_3_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_nilai', '1') }}" placeholder="1">
                                @error('sub_satuan_3_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 3</label>
                                <select name="sub_satuan_3_id" class="form-select @error('sub_satuan_3_id') is-invalid @enderror">
                                    <option value="">- Pilih Satuan -</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('sub_satuan_3_id') == $satuan->id ? 'selected' : '' }}>
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
                
                <!-- COA Fields - COMPLETELY MANUAL -->
                <h5 class="mb-3">Akun COA</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">COA Pembelian <span class="text-danger">*</span></label>
                            <select name="coa_pembelian_id" id="coa_pembelian_id" class="form-select" required>
                                <option value="">-- Pilih COA Pembelian --</option>
                                @foreach($coas as $coa)
                                    <option value="{{ $coa->kode_akun }}">{{ $coa->nama_akun }} ({{ $coa->kode_akun }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">* Wajib diisi</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">COA Persediaan <span class="text-danger">*</span></label>
                            <select name="coa_persediaan_id" id="coa_persediaan_id" class="form-select" required>
                                <option value="">-- Pilih COA Persediaan --</option>
                                @foreach($coas as $coa)
                                    <option value="{{ $coa->kode_akun }}">{{ $coa->nama_akun }} ({{ $coa->kode_akun }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">* Wajib diisi</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">COA HPP <span class="text-danger">*</span></label>
                            <select name="coa_hpp_id" id="coa_hpp_id" class="form-select" required>
                                <option value="">-- Pilih COA HPP --</option>
                                @foreach($coas as $coa)
                                    <option value="{{ $coa->kode_akun }}">{{ $coa->nama_akun }} ({{ $coa->kode_akun }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">* Wajib diisi</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- NO JAVASCRIPT AUTO-FILL - COMPLETELY MANUAL COA SELECTION -->
@push('scripts')
<script>
// Format number with thousand separator (dot)
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Remove thousand separator and parse to number
function parseFormattedNumber(str) {
    return parseFloat(str.replace(/\./g, '')) || 0;
}

// Setup price input formatting
function setupPriceInput() {
    const priceInput = document.getElementById('harga_satuan');
    const priceRawInput = document.getElementById('harga_satuan_raw');
    
    if (!priceInput) return;
    
    // Format on input
    priceInput.addEventListener('input', function(e) {
        let value = e.target.value;
        
        // Remove non-numeric characters except dot
        value = value.replace(/[^0-9]/g, '');
        
        // Parse to number
        const numValue = parseInt(value) || 0;
        
        // Format with thousand separator
        e.target.value = formatNumber(numValue);
        
        // Store raw value
        if (priceRawInput) {
            priceRawInput.value = numValue;
        }
    });
    
    // Initial format
    const initialValue = parseInt(priceInput.value.replace(/\./g, '')) || 0;
    priceInput.value = formatNumber(initialValue);
    if (priceRawInput) {
        priceRawInput.value = initialValue;
    }
}

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

// Convert commas to dots before form submission
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
    
    // Setup price input
    setupPriceInput();
    
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
        
        // Update all satuan utama text fields
        satuanUtamaTexts.forEach(input => {
            input.value = satuanText;
        });
        
        console.log('Satuan Utama updated to:', satuanText); // Debug log
    }
    
    // Initial call to set the value on page load
    updateSatuanUtamaDisplay();
    
    // Event listener for when satuan changes
    if (satuanSelect) {
        satuanSelect.addEventListener('change', function() {
            console.log('Satuan changed'); // Debug log
            updateSatuanUtamaDisplay();
        });
    }
    
    // Form validation and submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Convert price input to raw value before submission
            const priceInput = document.getElementById('harga_satuan');
            const priceRawInput = document.getElementById('harga_satuan_raw');
            if (priceInput && priceRawInput) {
                priceInput.value = priceRawInput.value;
            }
            
            // Convert commas to dots before validation
            convertCommasToDots();
        });
    }
});
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Edit Bahan Pendukung')

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
            <form action="{{ route('master-data.bahan-pendukung.update', $bahanPendukung->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                                   value="{{ old('nama_bahan', $bahanPendukung->nama_bahan) }}" placeholder="Contoh: Gas LPG, Minyak Goreng" required>
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
                                        <option value="{{ $kat->id }}" {{ old('kategori_id', $bahanPendukung->kategori_id) == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
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
                                   value="{{ old('kode_bahan', $bahanPendukung->kode_bahan) }}" placeholder="Contoh: BP001">
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
                                    <option value="{{ $satuan->id }}" {{ old('satuan_id', $bahanPendukung->satuan_id) == $satuan->id ? 'selected' : '' }}>
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
                                       value="{{ old('harga_satuan', $bahanPendukung->harga_satuan) }}" placeholder="0" required>
                                <input type="hidden" name="harga_satuan_raw" id="harga_satuan_raw" value="{{ $bahanPendukung->harga_satuan }}">
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
                                       value="{{ old('stok', $bahanPendukung->stok) }}" placeholder="0">
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
                                <input type="text" name="stok_minimum" id="stok_minimum" class="form-control price-input @error('stok_minimum') is-invalid @enderror" 
                                       value="{{ old('stok_minimum', $bahanPendukung->stok_minimum) }}" placeholder="0">
                                <input type="hidden" name="stok_minimum_raw" id="stok_minimum_raw" value="{{ $bahanPendukung->stok_minimum }}">
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
                                      rows="3" placeholder="Deskripsi bahan pendukung">{{ old('deskripsi', $bahanPendukung->deskripsi) }}</textarea>
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
                                       value="{{ old('sub_satuan_1_konversi', $bahanPendukung->sub_satuan_1_konversi ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_1_konversi, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
                                @error('sub_satuan_1_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Satuan Utama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control satuan-utama-text" value="{{ $bahanPendukung->satuan->nama ?? 'Pilih Satuan Utama' }}" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">=</span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nilai 1 <span class="text-danger">*</span></label>
                                <input type="text" name="sub_satuan_1_nilai" class="form-control number-input @error('sub_satuan_1_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_1_nilai', $bahanPendukung->sub_satuan_1_nilai ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_1_nilai, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1" required>
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
                                <label class="form-label">Konversi 2</label>
                                <input type="text" name="sub_satuan_2_konversi" class="form-control number-input @error('sub_satuan_2_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_konversi', $bahanPendukung->sub_satuan_2_konversi ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_2_konversi, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1">
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
                                <label class="form-label">Nilai 2</label>
                                <input type="text" name="sub_satuan_2_nilai" class="form-control number-input @error('sub_satuan_2_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_2_nilai', $bahanPendukung->sub_satuan_2_nilai ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_2_nilai, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1">
                                @error('sub_satuan_2_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 2</label>
                                <select name="sub_satuan_2_id" class="form-select @error('sub_satuan_2_id') is-invalid @enderror">
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
                                <label class="form-label">Konversi 3</label>
                                <input type="text" name="sub_satuan_3_konversi" class="form-control number-input @error('sub_satuan_3_konversi') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_konversi', $bahanPendukung->sub_satuan_3_konversi ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_3_konversi, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1">
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
                                <label class="form-label">Nilai 3</label>
                                <input type="text" name="sub_satuan_3_nilai" class="form-control number-input @error('sub_satuan_3_nilai') is-invalid @enderror" 
                                       value="{{ old('sub_satuan_3_nilai', $bahanPendukung->sub_satuan_3_nilai ? rtrim(rtrim(number_format($bahanPendukung->sub_satuan_3_nilai, 4, ',', ''), '0'), ',') : '1') }}" placeholder="1">
                                @error('sub_satuan_3_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sub Satuan 3</label>
                                <select name="sub_satuan_3_id" class="form-select @error('sub_satuan_3_id') is-invalid @enderror">
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
                
                <!-- COA Fields - COMPLETELY MANUAL -->
                <h5 class="mb-3">Akun COA</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">COA Pembelian <span class="text-danger">*</span></label>
                            <select name="coa_pembelian_id" id="coa_pembelian_id" class="form-select" required>
                                <option value="">-- Pilih COA Pembelian --</option>
                                @foreach($coas as $coa)
                                    <option value="{{ $coa->kode_akun }}" {{ old('coa_pembelian_id', $bahanPendukung->coa_pembelian_id) == $coa->kode_akun ? 'selected' : '' }}>{{ $coa->nama_akun }} ({{ $coa->kode_akun }})</option>
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
                                    <option value="{{ $coa->kode_akun }}" {{ old('coa_persediaan_id', $bahanPendukung->coa_persediaan_id) == $coa->kode_akun ? 'selected' : '' }}>{{ $coa->nama_akun }} ({{ $coa->kode_akun }})</option>
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
                                    <option value="{{ $coa->kode_akun }}" {{ old('coa_hpp_id', $bahanPendukung->coa_hpp_id) == $coa->kode_akun ? 'selected' : '' }}>{{ $coa->nama_akun }} ({{ $coa->kode_akun }})</option>
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

// Format price input with thousand separator
function setupPriceFormatting() {
    const priceInput = document.getElementById('harga_satuan');
    const priceRawInput = document.getElementById('harga_satuan_raw');
    const stokMinInput = document.getElementById('stok_minimum');
    const stokMinRawInput = document.getElementById('stok_minimum_raw');
    
    // Format harga satuan
    if (priceInput) {
        // Format on input
        priceInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^0-9]/g, '');
            const numValue = parseInt(value) || 0;
            e.target.value = numValue.toLocaleString('id-ID');
            if (priceRawInput) {
                priceRawInput.value = numValue;
            }
        });
        
        // Initial format - parse float first to handle decimals, then convert to int
        let initialValue = parseFloat(priceInput.value) || 0;
        initialValue = Math.floor(initialValue); // Use Math.floor to remove decimals
        priceInput.value = initialValue.toLocaleString('id-ID');
        if (priceRawInput) {
            priceRawInput.value = initialValue;
        }
    }
    
    // Format stok minimum
    if (stokMinInput) {
        stokMinInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^0-9]/g, '');
            const numValue = parseInt(value) || 0;
            e.target.value = numValue.toLocaleString('id-ID');
            if (stokMinRawInput) {
                stokMinRawInput.value = numValue;
            }
        });
        
        // Initial format - parse float first to handle decimals, then convert to int
        let initialMinValue = parseFloat(stokMinInput.value) || 0;
        initialMinValue = Math.floor(initialMinValue); // Use Math.floor to remove decimals
        stokMinInput.value = initialMinValue.toLocaleString('id-ID');
        if (stokMinRawInput) {
            stokMinRawInput.value = initialMinValue;
        }
    }
    
    // Before form submission, use raw values
    const form = priceInput ? priceInput.closest('form') : null;
    if (form) {
        form.addEventListener('submit', function() {
            if (priceInput && priceRawInput && priceRawInput.value) {
                priceInput.value = priceRawInput.value;
            } else if (priceInput) {
                priceInput.value = priceInput.value.replace(/\./g, '');
            }
            
            if (stokMinInput && stokMinRawInput && stokMinRawInput.value) {
                stokMinInput.value = stokMinRawInput.value;
            } else if (stokMinInput) {
                stokMinInput.value = stokMinInput.value.replace(/\./g, '');
            }
        });
    }
}

// Update satuan utama display when main satuan changes
document.addEventListener('DOMContentLoaded', function() {
    const satuanSelect = document.querySelector('select[name="satuan_id"]');
    const satuanUtamaTexts = document.querySelectorAll('.satuan-utama-text');
    
    // Setup number inputs
    setupNumberInputs();
    
    // Setup price formatting
    setupPriceFormatting();
    
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
        });
    }
});
</script>
@endpush
@endsection

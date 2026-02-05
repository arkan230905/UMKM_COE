@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs me-2"></i>Tambah BTKL
        </h2>
        <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus me-2"></i>Form BTKL Baru
            </h5>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('master-data.btkl.store') }}" method="POST" id="createBtklForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Proses <span class="text-danger">*</span></label>
                            <input type="text" name="nama_proses" class="form-control @error('nama_proses') is-invalid @enderror" 
                                   value="{{ old('nama_proses') }}" placeholder="Contoh: Menggoreng, Membumbui, Mengemas" required>
                            @error('nama_proses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                            <select name="jabatan_id" id="jabatanSelect" class="form-select @error('jabatan_id') is-invalid @enderror" required onchange="calculateBTKL()">
                                <option value="">-- Pilih Jabatan BTKL --</option>
                                @php
                                    $jabatanBtkl = \App\Models\Jabatan::where('kategori', 'btkl')->with('pegawais')->get();
                                @endphp
                                @foreach($jabatanBtkl as $jabatan)
                                    <option value="{{ $jabatan->id }}" 
                                            data-tarif="{{ $jabatan->tarif }}"
                                            data-pegawai-count="{{ $jabatan->pegawais->count() }}"
                                            {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                        {{ $jabatan->nama }} ({{ $jabatan->pegawais->count() }} pegawai @ Rp {{ number_format($jabatan->tarif, 0, ',', '.') }}/jam)
                                    </option>
                                @endforeach
                            </select>
                            @error('jabatan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih jabatan yang mengurusi proses BTKL ini</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pegawai</label>
                            <div class="input-group">
                                <input type="number" id="jumlahPegawai" class="form-control" readonly>
                                <span class="input-group-text">orang</span>
                            </div>
                            <small class="text-muted">Otomatis dari jabatan yang dipilih</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tarif per Jam Jabatan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="tarifPerJamJabatan" class="form-control" readonly>
                            </div>
                            <small class="text-muted">Tarif per jam dari jabatan</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tarif BTKL (Auto) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tarif_btkl" id="tarifBTKL" class="form-control @error('tarif_btkl') is-invalid @enderror" 
                                       value="{{ old('tarif_btkl', 0) }}" readonly required>
                            </div>
                            @error('tarif_btkl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Jumlah Pegawai × Tarif per Jam</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan BTKL <span class="text-danger">*</span></label>
                            <select name="satuan_btkl" class="form-select @error('satuan_btkl') is-invalid @enderror" required>
                                <option value="jam" {{ old('satuan_btkl', 'jam') == 'jam' ? 'selected' : '' }}>Jam</option>
                                <option value="menit" {{ old('satuan_btkl') == 'menit' ? 'selected' : '' }}>Menit</option>
                                <option value="unit" {{ old('satuan_btkl') == 'unit' ? 'selected' : '' }}>Unit</option>
                                <option value="batch" {{ old('satuan_btkl') == 'batch' ? 'selected' : '' }}>Batch</option>
                            </select>
                            @error('satuan_btkl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="kapasitas_per_jam" id="kapasitasPerJam" class="form-control @error('kapasitas_per_jam') is-invalid @enderror" 
                                       value="{{ old('kapasitas_per_jam', 50) }}" min="1" step="1" placeholder="50" required onchange="calculateBiayaPerProduk()">
                                <span class="input-group-text">unit/jam</span>
                            </div>
                            @error('kapasitas_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Jumlah unit yang dapat diproduksi per jam</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Biaya per Produk (Auto)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="biayaPerProduk" class="form-control" readonly step="0.01">
                                <span class="input-group-text">per unit</span>
                            </div>
                            <small class="text-muted">Tarif BTKL ÷ Kapasitas per Jam</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi proses produksi">{{ old('deskripsi') }}</textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createBtklForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        console.log('Form is being submitted...');
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});

/**
 * Calculate BTKL rate based on selected jabatan
 */
function calculateBTKL() {
    const jabatanSelect = document.getElementById('jabatanSelect');
    const selectedOption = jabatanSelect.options[jabatanSelect.selectedIndex];
    
    if (selectedOption.value) {
        const tarifPerJam = parseFloat(selectedOption.getAttribute('data-tarif')) || 0;
        const jumlahPegawai = parseInt(selectedOption.getAttribute('data-pegawai-count')) || 0;
        const tarifBTKL = tarifPerJam * jumlahPegawai;
        
        // Update display fields
        document.getElementById('jumlahPegawai').value = jumlahPegawai;
        document.getElementById('tarifPerJamJabatan').value = tarifPerJam;
        document.getElementById('tarifBTKL').value = tarifBTKL;
        
        // Calculate biaya per produk
        calculateBiayaPerProduk();
        
        // Show calculation info
        showCalculationInfo(jumlahPegawai, tarifPerJam, tarifBTKL);
    } else {
        // Clear fields
        document.getElementById('jumlahPegawai').value = '';
        document.getElementById('tarifPerJamJabatan').value = '';
        document.getElementById('tarifBTKL').value = '';
        document.getElementById('biayaPerProduk').value = '';
        hideCalculationInfo();
    }
}

/**
 * Calculate biaya per produk based on tarif BTKL and kapasitas
 */
function calculateBiayaPerProduk() {
    const tarifBTKL = parseFloat(document.getElementById('tarifBTKL').value) || 0;
    const kapasitas = parseFloat(document.getElementById('kapasitasPerJam').value) || 0;
    
    if (tarifBTKL > 0 && kapasitas > 0) {
        const biayaPerProduk = tarifBTKL / kapasitas;
        document.getElementById('biayaPerProduk').value = biayaPerProduk.toFixed(2);
        
        // Show calculation info
        showBiayaCalculationInfo(tarifBTKL, kapasitas, biayaPerProduk);
    } else {
        document.getElementById('biayaPerProduk').value = '';
        hideBiayaCalculationInfo();
    }
}

/**
 * Show calculation information
 */
function showCalculationInfo(jumlahPegawai, tarifPerJam, tarifBTKL) {
    // Remove existing info if any
    hideCalculationInfo();
    
    const infoDiv = document.createElement('div');
    infoDiv.id = 'calculationInfo';
    infoDiv.className = 'alert alert-info mt-2';
    infoDiv.innerHTML = `
        <i class="fas fa-calculator me-2"></i>
        <strong>Perhitungan Tarif BTKL:</strong><br>
        ${jumlahPegawai} pegawai × Rp ${formatNumber(tarifPerJam)}/jam = <strong>Rp ${formatNumber(tarifBTKL)}/jam</strong>
    `;
    
    document.getElementById('tarifBTKL').parentNode.parentNode.appendChild(infoDiv);
}

/**
 * Hide calculation information
 */
function hideCalculationInfo() {
    const existingInfo = document.getElementById('calculationInfo');
    if (existingInfo) {
        existingInfo.remove();
    }
}

/**
 * Show biaya per produk calculation information
 */
function showBiayaCalculationInfo(tarifBTKL, kapasitas, biayaPerProduk) {
    // Remove existing info if any
    hideBiayaCalculationInfo();
    
    const infoDiv = document.createElement('div');
    infoDiv.id = 'biayaCalculationInfo';
    infoDiv.className = 'alert alert-success mt-2';
    infoDiv.innerHTML = `
        <i class="fas fa-chart-line me-2"></i>
        <strong>Perhitungan Biaya per Produk:</strong><br>
        Rp ${formatNumber(tarifBTKL)}/jam ÷ ${kapasitas} unit/jam = <strong>Rp ${formatNumber(biayaPerProduk)}/unit</strong>
    `;
    
    document.getElementById('biayaPerProduk').parentNode.parentNode.appendChild(infoDiv);
}

/**
 * Hide biaya calculation information
 */
function hideBiayaCalculationInfo() {
    const existingInfo = document.getElementById('biayaCalculationInfo');
    if (existingInfo) {
        existingInfo.remove();
    }
}

/**
 * Format number with thousand separators, removing unnecessary decimals
 */
function formatNumber(num) {
    // If it's a whole number, show without decimals
    if (num == Math.floor(num)) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num);
    }
    
    // Format with up to 2 decimals, removing trailing zeros
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(num);
}
</script>
@endsection

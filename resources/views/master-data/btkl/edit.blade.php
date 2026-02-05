@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-user-clock me-2"></i>Edit Proses Produksi (BTKL)
        </h2>
        <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.btkl.update', $btkl->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="kode_proses" class="form-label">Kode Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="kode_proses" 
                               id="kode_proses" 
                               class="form-control @error('kode_proses') is-invalid @enderror" 
                               value="{{ old('kode_proses', $btkl->kode_proses) }}"
                               required>
                        @error('kode_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nama_btkl" class="form-label">Nama Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_btkl" 
                               id="nama_btkl" 
                               class="form-control @error('nama_btkl') is-invalid @enderror" 
                               value="{{ old('nama_btkl', $btkl->nama_btkl) }}"
                               placeholder="Contoh: Penggorengan Adonan"
                               required>
                        @error('nama_btkl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Nama proses produksi (contoh: Penggorengan Adonan, Pencampuran Bahan, dll)</small>
                    </div>

                    <div class="col-md-6">
                        <label for="jabatan_id" class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                        <select name="jabatan_id" 
                                id="jabatan_id" 
                                class="form-select @error('jabatan_id') is-invalid @enderror" 
                                required>
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach($jabatanBtkl as $jabatan)
                                <option value="{{ $jabatan->id }}" {{ (old('jabatan_id') ?? $btkl->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Jabatan yang mengolah proses BTKL ini</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tarif BTKL per Jam <span class="text-info">(Otomatis)</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp/jam</span>
                            <input type="text" 
                                   id="tarif_per_jam_display" 
                                   class="form-control" 
                                   value="{{ number_format($btkl->tarif_per_jam, 0, ',', '.') }}"
                                   readonly>
                        </div>
                        <small class="form-text text-muted">Dihitung otomatis: Tarif Jabatan × Jumlah Pegawai</small>
                        
                        <div id="tarifCalculationDisplay" class="mt-2">
                            <div class="alert alert-info py-2">
                                <span id="tarifCalculationText">Rp {{ number_format($btkl->jabatan->tarif ?? 0, 0, ',', '.') }} x {{ $btkl->jabatan->pegawais->count() ?? 0 }} pegawai = Rp {{ number_format($btkl->tarif_per_jam, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                        <select name="satuan" 
                                id="satuan" 
                                class="form-select @error('satuan') is-invalid @enderror" 
                                required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($satuanOptions as $satuan)
                                <option value="{{ $satuan }}" {{ old('satuan', $btkl->satuan) == $satuan ? 'selected' : '' }}>
                                    {{ $satuan }}
                                </option>
                            @endforeach
                        </select>
                        @error('satuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="kapasitas_per_jam" class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="kapasitas_per_jam" 
                               id="kapasitas_per_jam" 
                               class="form-control @error('kapasitas_per_jam') is-invalid @enderror" 
                               value="{{ old('kapasitas_per_jam', $btkl->kapasitas_per_jam) }}"
                               min="0"
                               required>
                        @error('kapasitas_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Berapa pcs bisa diproduksi per jam</small>
                    </div>

                    <div class="col-md-12">
                        <label for="deskripsi_proses" class="form-label">Deskripsi Proses</label>
                        <textarea name="deskripsi_proses" 
                                  id="deskripsi_proses" 
                                  class="form-control @error('deskripsi_proses') is-invalid @enderror" 
                                  rows="3"
                                  placeholder="Deskripsi detail proses produksi">{{ old('deskripsi_proses', $btkl->deskripsi_proses) }}</textarea>
                        @error('deskripsi_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Biaya Per Produk <span class="text-info">(Otomatis)</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp/pcs</span>
                            <input type="text" 
                                   id="biaya_per_produk_display" 
                                   class="form-control" 
                                   value="{{ $btkl->biaya_per_produk_formatted }}"
                                   readonly>
                        </div>
                        <small class="form-text text-muted">Dihitung otomatis: Tarif BTKL ÷ Kapasitas/Jam</small>
                        
                        <div id="biayaPerProdukDisplay" class="mt-2">
                            <div class="alert alert-warning py-2">
                                <span id="biayaPerProdukText">Rp {{ number_format($btkl->tarif_per_jam, 0, ',', '.') }} ÷ {{ $btkl->kapasitas_per_jam }} pcs = Rp {{ $btkl->biaya_per_produk_formatted }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Data
                            </button>
                            <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Employee data
const employeeData = @json($jabatanBtkl ?? []);

document.addEventListener('DOMContentLoaded', function() {
    const jabatanSelect = document.getElementById('jabatan_id');
    const tarifDisplay = document.getElementById('tarif_per_jam_display');
    const tarifCalculationDisplay = document.getElementById('tarifCalculationDisplay');
    const tarifCalculationText = document.getElementById('tarifCalculationText');
    const kapasitasInput = document.getElementById('kapasitas_per_jam');
    const biayaPerProdukDisplay = document.getElementById('biaya_per_produk_display');
    const biayaPerProdukText = document.getElementById('biayaPerProdukText');
    
    let currentTarifBtkl = {{ $btkl->tarif_per_jam }};

    function updateTarifCalculation(jabatan) {
        if (jabatan) {
            const jumlahPegawai = jabatan.pegawai_count || 0;
            const tarifPerJam = jabatan.tarif || 0;
            currentTarifBtkl = tarifPerJam * jumlahPegawai;
            
            tarifDisplay.value = currentTarifBtkl.toLocaleString('id-ID');
            tarifCalculationText.textContent = 'Rp ' + tarifPerJam.toLocaleString('id-ID') + ' x ' + jumlahPegawai + ' pegawai = Rp ' + currentTarifBtkl.toLocaleString('id-ID');
            tarifCalculationDisplay.style.display = 'block';
        } else {
            tarifDisplay.value = '0';
            tarifCalculationDisplay.style.display = 'none';
            currentTarifBtkl = 0;
        }
        
        updateBiayaPerProduk();
    }

    function updateBiayaPerProduk() {
        const kapasitas = parseInt(kapasitasInput.value) || 0;
        const tarif = currentTarifBtkl;
        
        if (kapasitas > 0 && tarif > 0) {
            const biayaPerProduk = tarif / kapasitas;
            biayaPerProdukDisplay.value = formatNumberClean(biayaPerProduk);
            biayaPerProdukText.textContent = 'Rp ' + formatNumberClean(tarif) + ' / ' + kapasitas + ' pcs = ' + formatRupiahClean(biayaPerProduk);
            biayaPerProdukDisplay.parentElement.nextElementSibling.style.display = 'block';
        } else {
            biayaPerProdukDisplay.value = '0';
            biayaPerProdukDisplay.parentElement.nextElementSibling.style.display = 'none';
        }
    }

    jabatanSelect.addEventListener('change', function() {
        const selectedJabatanId = parseInt(this.value);
        
        if (selectedJabatanId) {
            const jabatan = employeeData.find(j => j.id === selectedJabatanId);
            updateTarifCalculation(jabatan);
        } else {
            updateTarifCalculation(null);
        }
    });
    
    kapasitasInput.addEventListener('input', updateBiayaPerProduk);
    
    // Clean number formatting function
    function formatNumberClean(number) {
        if (number == Math.floor(number)) {
            return number.toLocaleString('id-ID');
        }
        let formatted = number.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        // Remove trailing zeros after decimal
        if (formatted.includes(',')) {
            formatted = formatted.replace(/,?0+$/, '');
        }
        return formatted;
    }
    
    // Clean rupiah formatting function  
    function formatRupiahClean(number) {
        return 'Rp ' + formatNumberClean(number);
    }
});
</script>
@endsection

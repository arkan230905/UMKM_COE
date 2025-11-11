@extends('layouts.app')

@section('content')
<div class="container text-light">
    <h2 class="mb-4 text-white">Edit Aset</h2>

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

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.aset.update', $aset->id) }}" method="POST" id="asetForm">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="nama_aset" class="form-label text-white">Nama Aset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control bg-dark text-white @error('nama_aset') is-invalid @enderror" 
                           id="nama_aset" name="nama_aset" value="{{ old('nama_aset', $aset->nama_aset) }}" required>
                    @error('nama_aset')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis_aset_id" class="form-label text-white">Jenis Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-dark text-white @error('jenis_aset_id') is-invalid @enderror" 
                            id="jenis_aset_id" name="jenis_aset_id" required onchange="loadKategoriAset()">
                        <option value="" disabled selected>-- Pilih Jenis Aset --</option>
                        @foreach($jenisAsets as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('jenis_aset_id', $aset->kategori->jenis_aset_id ?? '') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_aset_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kategori_aset_id" class="form-label text-white">Kategori Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-dark text-white @error('kategori_aset_id') is-invalid @enderror" 
                            id="kategori_aset_id" name="kategori_aset_id" required>
                        <option value="" disabled selected>-- Pilih Jenis Aset terlebih dahulu --</option>
                    </select>
                    @error('kategori_aset_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="harga_perolehan" class="form-label text-white">Harga Perolehan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control bg-dark text-white @error('harga_perolehan') is-invalid @enderror" 
                               id="harga_perolehan" name="harga_perolehan" value="{{ old('harga_perolehan', $aset->harga_perolehan) }}" 
                               required oninput="hitungTotal()">
                        @error('harga_perolehan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="biaya_perolehan" class="form-label text-white">Biaya Perolehan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control bg-dark text-white @error('biaya_perolehan') is-invalid @enderror" 
                               id="biaya_perolehan" name="biaya_perolehan" value="{{ old('biaya_perolehan', $aset->biaya_perolehan ?? 0) }}" 
                               required oninput="hitungTotal()">
                        <small class="text-muted">Biaya tambahan seperti ongkir, instalasi, dll</small>
                        @error('biaya_perolehan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">Total Perolehan</label>
                    <div class="form-control bg-secondary text-white" id="total_perolehan_display">Rp 0</div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="metode_penyusutan" class="form-label text-white">Metode Penyusutan <span class="text-danger">*</span></label>
                        <select class="form-select bg-dark text-white @error('metode_penyusutan') is-invalid @enderror" 
                                id="metode_penyusutan" name="metode_penyusutan" required onchange="hitungPenyusutan()">
                            <option value="" disabled selected>-- Pilih Metode --</option>
                            @foreach($metodePenyusutan as $key => $value)
                                <option value="{{ $key }}" {{ old('metode_penyusutan', $aset->metode_penyusutan) == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @error('metode_penyusutan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="umur_manfaat" class="form-label text-white">Umur Manfaat (tahun) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control bg-dark text-white @error('umur_manfaat') is-invalid @enderror" 
                               id="umur_manfaat" name="umur_manfaat" value="{{ old('umur_manfaat', $aset->umur_manfaat ?? 5) }}" 
                               min="1" max="100" required oninput="hitungPenyusutan()">
                        @error('umur_manfaat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="nilai_residu" class="form-label text-white">Nilai Residu (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control bg-dark text-white @error('nilai_residu') is-invalid @enderror" 
                               id="nilai_residu" name="nilai_residu" value="{{ old('nilai_residu', $aset->nilai_residu ?? 0) }}" 
                               required oninput="hitungPenyusutan()">
                        <small class="text-muted">Nilai sisa di akhir umur manfaat</small>
                        @error('nilai_residu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Ringkasan Penyusutan -->
                <div class="card border-0 shadow-sm mb-4 bg-dark">
                    <div class="card-header bg-primary text-light">
                        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Hasil Perhitungan Penyusutan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 table-dark">
                                <tbody>
                                    <tr>
                                        <td class="bg-secondary text-white fw-bold" width="50%">Nilai yang Disusutkan</td>
                                        <td class="text-end text-white" id="nilai_disusutkan_display">Rp 0</td>
                                    </tr>
                                    <tr class="bg-success bg-opacity-25">
                                        <td class="fw-bold text-white">Penyusutan Per Tahun</td>
                                        <td class="text-end fw-bold text-success" id="penyusutan_tahunan_display">Rp 0</td>
                                    </tr>
                                    <tr class="bg-info bg-opacity-25">
                                        <td class="fw-bold text-white">Penyusutan Per Bulan</td>
                                        <td class="text-end fw-bold text-info" id="penyusutan_bulanan_display">Rp 0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <small><i class="bi bi-info-circle me-1"></i> Perhitungan ini adalah estimasi berdasarkan metode yang dipilih</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_beli" class="form-label text-white">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control bg-dark text-white @error('tanggal_beli') is-invalid @enderror" 
                               id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli', $aset->tanggal_beli instanceof \Carbon\Carbon ? $aset->tanggal_beli->format('Y-m-d') : $aset->tanggal_beli) }}" required>
                        @error('tanggal_beli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akuisisi" class="form-label text-white">Tanggal Mulai Penyusutan</label>
                        <input type="date" class="form-control bg-dark text-white @error('tanggal_akuisisi') is-invalid @enderror" 
                               id="tanggal_akuisisi" name="tanggal_akuisisi" value="{{ old('tanggal_akuisisi', $aset->tanggal_akuisisi instanceof \Carbon\Carbon ? $aset->tanggal_akuisisi->format('Y-m-d') : $aset->tanggal_akuisisi) }}">
                        <small class="text-muted">Kosongkan jika sama dengan tanggal pembelian</small>
                        @error('tanggal_akuisisi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label text-white">Keterangan</label>
                    <textarea class="form-control bg-dark text-white @error('keterangan') is-invalid @enderror" 
                              id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $aset->keterangan) }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Aset
                    </button>
                    <a href="{{ route('master-data.aset.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data kategori aset per jenis
const kategoriData = @json($jenisAsets);

// Load kategori aset berdasarkan jenis yang dipilih
function loadKategoriAset() {
    const jenisId = document.getElementById('jenis_aset_id').value;
    const kategoriSelect = document.getElementById('kategori_aset_id');
    
    kategoriSelect.innerHTML = '<option value="" disabled selected>-- Pilih Kategori Aset --</option>';
    
    if (jenisId) {
        const jenis = kategoriData.find(j => j.id == jenisId);
        if (jenis && jenis.kategories) {
            jenis.kategories.forEach(kategori => {
                const option = document.createElement('option');
                option.value = kategori.id;
                option.textContent = kategori.nama;
                if ('{{ old("kategori_aset_id") }}' == kategori.id) {
                    option.selected = true;
                }
                kategoriSelect.appendChild(option);
            });
        }
    }
}

// Hitung total perolehan
function hitungTotal() {
    const harga = parseFloat(document.getElementById('harga_perolehan').value) || 0;
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    
    document.getElementById('total_perolehan_display').textContent = 'Rp ' + formatRupiah(total);
    
    hitungPenyusutan();
}

// Hitung penyusutan
function hitungPenyusutan() {
    const harga = parseFloat(document.getElementById('harga_perolehan').value) || 0;
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    const residu = parseFloat(document.getElementById('nilai_residu').value) || 0;
    const umur = parseFloat(document.getElementById('umur_manfaat').value) || 1;
    const metode = document.getElementById('metode_penyusutan').value;
    
    const nilaiDisusutkan = total - residu;
    let penyusutanTahunan = 0;
    
    if (metode === 'garis_lurus') {
        // Metode garis lurus
        penyusutanTahunan = nilaiDisusutkan / umur;
    } else if (metode === 'saldo_menurun') {
        // Metode saldo menurun (double declining)
        const rate = 2 / umur;
        penyusutanTahunan = total * rate;
    } else if (metode === 'sum_of_years_digits') {
        // Metode jumlah angka tahun (tahun pertama)
        const sumOfYears = (umur * (umur + 1)) / 2;
        penyusutanTahunan = (nilaiDisusutkan * umur) / sumOfYears;
    }
    
    const penyusutanBulanan = penyusutanTahunan / 12;
    
    document.getElementById('nilai_disusutkan_display').textContent = 'Rp ' + formatRupiah(nilaiDisusutkan);
    document.getElementById('penyusutan_tahunan_display').textContent = 'Rp ' + formatRupiah(penyusutanTahunan);
    document.getElementById('penyusutan_bulanan_display').textContent = 'Rp ' + formatRupiah(penyusutanBulanan);
}

// Format rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(angka);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load kategori if jenis already selected
    const jenisSelect = document.getElementById('jenis_aset_id'); if (jenisSelect.value) {
        loadKategoriAset(); } hitungTotal();
    }
    
    // Calculate initial values
    hitungTotal();
});
</script>
@endsection


@extends('layouts.app')

@section('content')
<div class="container text-dark">
    <h2 class="mb-4 text-dark">Tambah Aset Baru</h2>

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

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.aset.store') }}" method="POST" id="asetForm">
                @csrf
                
                <div class="mb-3">
                    <label for="nama_aset" class="form-label text-dark">Nama Aset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control bg-white text-dark @error('nama_aset') is-invalid @enderror" 
                           id="nama_aset" name="nama_aset" value="{{ old('nama_aset') }}" required>
                    @error('nama_aset')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis_aset_id" class="form-label text-dark">Jenis Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-white text-dark @error('jenis_aset_id') is-invalid @enderror" 
                            id="jenis_aset_id" name="jenis_aset_id" required onchange="loadKategoriAset()">
                        <option value="" disabled selected>-- Pilih Jenis Aset --</option>
                        @foreach($jenisAsets as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('jenis_aset_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                        <option value="add_new" style="border-top: 1px solid #ccc; font-style: italic; color: #007bff;">
                            + Tambah Jenis Aset Baru
                        </option>
                    </select>
                    @error('jenis_aset_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kategori_aset_id" class="form-label text-dark">Kategori Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-white text-dark @error('kategori_aset_id') is-invalid @enderror" 
                            id="kategori_aset_id" name="kategori_aset_id" required onchange="checkPenyusutan()">
                        <option value="" disabled selected>-- Pilih Jenis Aset terlebih dahulu --</option>
                    </select>
                    @error('kategori_aset_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="harga_perolehan" class="form-label text-dark">Harga Perolehan (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-white text-dark @error('harga_perolehan') is-invalid @enderror" 
                               id="harga_perolehan" name="harga_perolehan" value="{{ old('harga_perolehan') ? number_format(old('harga_perolehan'), 0, ',', '.') : '' }}" 
                               placeholder="0"
                               required inputmode="numeric" oninput="hitungTotal()" onblur="formatRupiahInput(this)">
                        @error('harga_perolehan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="biaya_perolehan" class="form-label text-dark">Biaya Perolehan (Rp)</label>
                        <input type="number" step="0.01" class="form-control bg-white text-dark @error('biaya_perolehan') is-invalid @enderror" 
                               id="biaya_perolehan" name="biaya_perolehan" value="{{ old('biaya_perolehan', 0) }}" 
                               required oninput="hitungTotal()">
                        <small class="text-muted">Biaya tambahan seperti ongkir, instalasi, dll</small>
                        @error('biaya_perolehan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-dark">Total Perolehan</label>
                    <div class="form-control bg-light text-dark" id="total_perolehan_display">Rp 0</div>
                </div>

                <!-- Section Penyusutan - Hanya muncul untuk aset yang disusutkan -->
                <div id="section_penyusutan" style="display: none;">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Aset ini mengalami penyusutan.</strong> Silakan isi informasi penyusutan di bawah.
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="metode_penyusutan" class="form-label text-dark">Metode Penyusutan <span class="text-danger">*</span></label>
                            <select class="form-select bg-white text-dark @error('metode_penyusutan') is-invalid @enderror" 
                                    id="metode_penyusutan" name="metode_penyusutan" onchange="hitungPenyusutan()">
                                <option value="" disabled selected>-- Pilih Metode --</option>
                                @foreach($metodePenyusutan as $key => $value)
                                    <option value="{{ $key }}" {{ old('metode_penyusutan') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('metode_penyusutan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="umur_manfaat" class="form-label text-dark">Umur Manfaat (tahun) <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control bg-white text-dark @error('umur_manfaat') is-invalid @enderror" 
                                   id="umur_manfaat" name="umur_manfaat" value="{{ old('umur_manfaat') }}" 
                                   oninput="hitungPenyusutan()">
                            <small class="text-muted">Perkiraan umur ekonomis aset</small>
                            @error('umur_manfaat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tarif Penyusutan Per Tahun (hanya untuk saldo menurun) -->
                        <div class="col-md-4 mb-3" id="tarif_penyusutan_container" style="display: none;">
                            <label for="tarif_penyusutan" class="form-label text-dark">Tarif Penyusutan Per Tahun (%)</label>
                            <input type="number" step="0.1" min="0" max="200" class="form-control bg-white text-dark @error('tarif_penyusutan') is-invalid @enderror" 
                                   id="tarif_penyusutan" name="tarif_penyusutan" value="{{ old('tarif_penyusutan') }}" 
                                   oninput="hitungPenyusutan()">
                            <small class="text-muted">Tarif penyusutan dalam persen (contoh: 50 untuk 50%)</small>
                            @error('tarif_penyusutan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bulan Mulai (hanya untuk saldo menurun) -->
                        <div class="col-md-4 mb-3" id="bulan_mulai_container" style="display: none;">
                            <label for="bulan_mulai" class="form-label text-dark">Bulan Mulai</label>
                            <select class="form-select bg-white text-dark @error('bulan_mulai') is-invalid @enderror" 
                                    id="bulan_mulai" name="bulan_mulai" onchange="hitungPenyusutan()">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                            <small class="text-muted">Bulan pembelian aset</small>
                            @error('bulan_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Perolehan (hanya untuk jumlah angka tahun) -->
                        <div class="col-md-4 mb-3" id="tanggal_perolehan_container" style="display: none;">
                            <label for="tanggal_perolehan" class="form-label text-dark">Tanggal Perolehan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control bg-white text-dark @error('tanggal_perolehan') is-invalid @enderror" 
                                   id="tanggal_perolehan" name="tanggal_perolehan" value="{{ old('tanggal_perolehan') }}" 
                                   onchange="hitungPenyusutan()">
                            <small class="text-muted">Tanggal pembelian aset</small>
                            @error('tanggal_perolehan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="nilai_residu" class="form-label text-dark">Nilai Residu (Rp)</label>
                            <input type="text" class="form-control bg-white text-dark @error('nilai_residu') is-invalid @enderror" 
                                   id="nilai_residu" name="nilai_residu" value="{{ old('nilai_residu') ? number_format(old('nilai_residu'), 0, ',', '.') : '' }}" 
                                   placeholder="0"
                                   inputmode="numeric" oninput="hitungPenyusutan()" onblur="formatRupiahInput(this)">
                            <small class="text-muted">Nilai sisa di akhir umur manfaat</small>
                            @error('nilai_residu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Ringkasan Penyusutan -->
                    <div class="card border-0 shadow-sm mb-4 bg-white">
                        <div class="card-header bg-light text-dark">
                            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Informasi Penyusutan</h5>
                        </div>
                        <div class="card-body">
                            <!-- Informasi Metode Penyusutan -->
                            <div id="info_metode_penyusutan" class="mb-3" style="display: none;">
                                <div class="alert alert-info bg-light border-info">
                                    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Detail Metode Penyusutan</h6>
                                    <div id="rumus_penyusutan"></div>
                                    <div id="tarif_penyusutan"></div>
                                    <div id="keterangan_penyusutan"></div>
                                </div>
                            </div>
                            
                                                        
                            <!-- Hasil Perhitungan -->
                            <h6 class="text-dark mb-3" id="hasil_perhitungan_header"><i class="bi bi-calculator me-2"></i>Hasil Perhitungan Penyusutan</h6>
                            <div class="table-responsive" id="hasil_perhitungan_container">
                                <table class="table table-bordered mb-0 table-light">
                                    <tbody>
                                        <tr>
                                            <td class="bg-light text-dark fw-bold" width="50%">Nilai yang Disusutkan</td>
                                            <td class="text-end text-dark" id="nilai_disusutkan_display">Rp 0</td>
                                        </tr>
                                        <tr class="bg-success bg-opacity-25">
                                            <td class="fw-bold text-dark">Penyusutan Per Tahun</td>
                                            <td class="text-end fw-bold text-success" id="penyusutan_tahunan_display">Rp 0</td>
                                        </tr>
                                        <tr class="bg-info bg-opacity-25">
                                            <td class="fw-bold text-dark">Penyusutan Per Bulan</td>
                                            <td class="text-end fw-bold text-info" id="penyusutan_bulanan_display">Rp 0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Tabel Perhitungan Per Tahun (hanya untuk saldo menurun) -->
                            <div id="tabel_perhitungan_tahunan" class="mt-4" style="display: none;">
                                <h6 class="text-dark mb-3"><i class="bi bi-table me-2"></i>Perhitungan Penyusutan Per Tahun</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-light table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">TAHUN</th>
                                                <th class="text-end">PENYUSUTAN</th>
                                                <th class="text-end">AKUMULASI PENY</th>
                                                <th class="text-end">NILAI BUKU</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabel_perhitungan_body">
                                            <!-- Akan diisi oleh JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Perhitungan Jumlah Angka Tahun (hanya untuk metode jumlah angka tahun) -->
                            <div id="perhitungan_jumlah_angka_tahun" class="mt-4" style="display: none;">
                                <h6 class="text-dark mb-3"><i class="bi bi-calculator me-2"></i>Perhitungan Jumlah Angka Tahun</h6>
                                <div class="card bg-white">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="text-dark mb-2"><strong>Umur Manfaat:</strong> <span id="umur_manfaat_display">-</span> tahun</p>
                                                <p class="text-dark mb-2"><strong>Rumus:</strong> <span id="rumus_jumlah_angka">-</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="text-dark mb-2"><strong>Hasil Perhitungan:</strong></p>
                                                <h4 class="text-success" id="hasil_jumlah_angka">-</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <small><i class="bi bi-info-circle me-1"></i> Perhitungan ini adalah estimasi berdasarkan metode yang dipilih</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert untuk aset yang tidak disusutkan -->
                <div id="alert_tidak_disusutkan" class="alert alert-warning mb-4" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Aset ini tidak mengalami penyusutan.</strong> 
                    <span id="alasan_tidak_disusutkan"></span>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_beli" class="form-label text-dark">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control bg-white text-dark @error('tanggal_beli') is-invalid @enderror" 
                               id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli', date('Y-m-d')) }}" required>
                        @error('tanggal_beli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akuisisi" class="form-label text-dark">Tanggal Mulai Penyusutan</label>
                        <input type="date" class="form-control bg-white text-dark @error('tanggal_akuisisi') is-invalid @enderror" 
                               id="tanggal_akuisisi" name="tanggal_akuisisi" value="{{ old('tanggal_akuisisi') }}">
                        <small class="text-muted">Kosongkan jika sama dengan tanggal pembelian</small>
                        @error('tanggal_akuisisi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Keterangan field hidden per user request -->
                <input type="hidden" id="keterangan" name="keterangan" value="">

                <!-- Section COA -->
                <div class="card border-0 shadow-sm mb-4 bg-white">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Akun COA untuk Jurnal</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Pilih akun COA</strong> untuk pencatatan jurnal aset agar struktur akuntansi terdata dengan rapi.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="asset_coa_id" class="form-label text-dark">Akun COA Aset <span class="text-danger">*</span></label>
                                <select class="form-select bg-white text-dark @error('asset_coa_id') is-invalid @enderror" 
                                        id="asset_coa_id" name="asset_coa_id" required>
                                    <option value="" disabled selected>-- Pilih Akun Aset --</option>
                                    @foreach($coaAsets as $coa)
                                        <option value="{{ $coa->id }}" {{ old('asset_coa_id') == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih akun aset untuk mencatat nilai perolehan aset</small>
                                @error('asset_coa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="accum_depr_coa_id" class="form-label text-dark">Akun COA Akumulasi Penyusutan <span class="text-danger">*</span></label>
                                <select class="form-select bg-white text-dark @error('accum_depr_coa_id') is-invalid @enderror" 
                                        id="accum_depr_coa_id" name="accum_depr_coa_id" required>
                                    <option value="" disabled selected>-- Pilih Akun Akumulasi --</option>
                                    @foreach($coaAkumulasi as $coa)
                                        <option value="{{ $coa->id }}" {{ old('accum_depr_coa_id') == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih akun akumulasi penyusutan aset</small>
                                @error('accum_depr_coa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="expense_coa_id" class="form-label text-dark">Akun COA Beban Penyusutan <span class="text-danger">*</span></label>
                                <select class="form-select bg-white text-dark @error('expense_coa_id') is-invalid @enderror" 
                                        id="expense_coa_id" name="expense_coa_id" required>
                                    <option value="" disabled selected>-- Pilih Akun Beban --</option>
                                    @foreach($coaBeban as $coa)
                                        <option value="{{ $coa->id }}" {{ old('expense_coa_id') == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih akun beban penyusutan untuk mencatat biaya penyusutan</small>
                                @error('expense_coa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Aset
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

// Fungsi untuk format number otomatis
function formatNumber(input) {
    // Hapus semua karakter kecuali digit dan titik
    let value = input.value.replace(/[^\d.]/g, '');
    
    // Jika kosong, set ke 0
    if (value === '') {
        input.value = '0';
        return;
    }
    
    // Format dengan titik setiap 3 digit
    const parts = value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    // Update input value
    input.value = parts.join('.');
}

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
                option.dataset.disusutkan = kategori.disusutkan ? '1' : '0';
                option.dataset.jenisNama = jenis.nama;
                if ('{{ old("kategori_aset_id") }}' == kategori.id) {
                    option.selected = true;
                }
                kategoriSelect.appendChild(option);
            });
        }
    }
    
    // Check penyusutan after loading
    checkPenyusutan();
}

// Check apakah kategori aset yang dipilih disusutkan atau tidak
function checkPenyusutan() {
    const kategoriSelect = document.getElementById('kategori_aset_id');
    const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
    
    const sectionPenyusutan = document.getElementById('section_penyusutan');
    const alertTidakDisusutkan = document.getElementById('alert_tidak_disusutkan');
    const alasanTidakDisusutkan = document.getElementById('alasan_tidak_disusutkan');
    
    // Fields penyusutan
    const metodePenyusutan = document.getElementById('metode_penyusutan');
    const umurManfaat = document.getElementById('umur_manfaat');
    const nilaiResidu = document.getElementById('nilai_residu');
    
    if (selectedOption && selectedOption.value) {
        const disusutkan = selectedOption.dataset.disusutkan === '1';
        const jenisNama = selectedOption.dataset.jenisNama || '';
        const kategoriNama = selectedOption.textContent;
        
        if (disusutkan) {
            // Aset DISUSUTKAN - tampilkan form penyusutan
            sectionPenyusutan.style.display = 'block';
            alertTidakDisusutkan.style.display = 'none';
            
            // Set required
            metodePenyusutan.required = true;
            umurManfaat.required = true;
            nilaiResidu.required = true;
            
        } else {
            // Aset TIDAK DISUSUTKAN - sembunyikan form penyusutan
            sectionPenyusutan.style.display = 'none';
            alertTidakDisusutkan.style.display = 'block';
            
            // Remove required
            metodePenyusutan.required = false;
            umurManfaat.required = false;
            nilaiResidu.required = false;
            
            // Set nilai default untuk aset yang tidak disusutkan
            metodePenyusutan.value = '';
            umurManfaat.value = 0;
            nilaiResidu.value = '';
            
            // Tampilkan alasan
            let alasan = '';
            if (jenisNama.includes('Lancar')) {
                alasan = 'Aset lancar tidak mengalami penyusutan karena bersifat likuid dan akan dikonversi menjadi kas dalam waktu dekat.';
            } else if (kategoriNama.includes('Tanah')) {
                alasan = 'Tanah tidak mengalami penyusutan karena memiliki umur manfaat tidak terbatas dan cenderung meningkat nilainya.';
            } else if (jenisNama.includes('Tak Berwujud')) {
                alasan = 'Aset tak berwujud tidak disusutkan, tetapi diamortisasi dengan metode yang berbeda.';
            } else if (jenisNama.includes('Investasi')) {
                alasan = 'Investasi jangka panjang tidak disusutkan karena nilainya mengikuti nilai pasar.';
            } else {
                alasan = 'Aset ini tidak mengalami penyusutan sesuai standar akuntansi.';
            }
            alasanTidakDisusutkan.textContent = alasan;
        }
    } else {
        // Belum ada kategori dipilih
        sectionPenyusutan.style.display = 'none';
        alertTidakDisusutkan.style.display = 'none';
    }
}

// Format angka ke format rupiah Indonesia (dengan titik sebagai pemisah ribuan, tanpa desimal)
function formatRupiah(angka) {
    // Hapus semua karakter non-digit
    let numStr = angka.toString().replace(/\D/g, '');
    // Konversi ke integer untuk memastikan tidak ada desimal
    let num = parseInt(numStr) || 0;
    // Format dengan titik sebagai pemisah ribuan
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Format input saat blur (bukan oninput untuk mencegah infinite loop)
function formatRupiahInput(input) {
    // Hapus semua karakter non-digit
    let value = input.value.replace(/\D/g, '');
    
    // Jika kosong, set ke kosong
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Format dengan titik sebagai pemisah ribuan
    input.value = formatRupiah(value);
}

// Unformat rupiah (hilangkan titik) untuk perhitungan
function unformatRupiah(value) {
    if (typeof value !== 'string') return value;
    return parseFloat(value.replace(/\./g, '')) || 0;
}

// Hitung total perolehan
function hitungTotal() {
    const harga = unformatRupiah(document.getElementById('harga_perolehan').value);
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    
    document.getElementById('total_perolehan_display').textContent = 'Rp ' + formatRupiah(total);
    
    hitungPenyusutan();
}

// Hitung penyusutan per tahun untuk metode jumlah angka tahun
function hitungPerhitunganTahunanSumOfYears(total, residu, umur) {
    const tabelContainer = document.getElementById('tabel_perhitungan_tahunan');
    const tabelBody = document.getElementById('tabel_perhitungan_body');
    
    const ND = total - residu;  // Nilai Disusutkan
    const JAT = (umur * (umur + 1)) / 2;  // Jumlah Angka Tahun
    
    let html = '';
    
    // Struktur periode sesuai Google Sheets
    const rows = [
        { label: '2022 (4)', angka: 5, bulan: 4 },
        { label: '2023 (8)', angka: 5, bulan: 8 },
        { label: '2023 (4)', angka: 4, bulan: 4 },
        { label: '2024 (8)', angka: 4, bulan: 8 },
        { label: '2024 (4)', angka: 3, bulan: 4 },
        { label: '2025 (8)', angka: 3, bulan: 8 },
        { label: '2025 (4)', angka: 2, bulan: 4 },
        { label: '2026 (8)', angka: 2, bulan: 8 },
        { label: '2026 (4)', angka: 1, bulan: 4 },
        { label: '2027 (8)', angka: 1, bulan: 8 }
    ];
    
    const result = [];
    let akumulasi = 0;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const angka = row.angka;
        const bulan = row.bulan;
        
        // Rumus penyusutan PERSIS seperti di sheet: ND * (angka/JAT) * (bulan/12)
        let penyusutan = ND * (angka / JAT) * (bulan / 12.0);
        penyusutan = Math.round(penyusutan);
        
        // Rumus akumulasi PERSIS seperti di sheet
        if (i === 0) {
            akumulasi = penyusutan;
        } else {
            akumulasi = akumulasi + penyusutan;
        }
        akumulasi = Math.round(akumulasi);
        
        // Rumus nilai buku PERSIS seperti di sheet: HP - akumulasi
        let nilaiBuku = total - akumulasi;
        nilaiBuku = Math.round(nilaiBuku);
        
        result.push({
            tahun: row.label,
            penyusutan: penyusutan,
            akumulasi: akumulasi,
            nilai_buku: nilaiBuku
        });
    }
    
    // Koreksi setelah loop agar total akumulasi = ND dan nilai buku akhir = NR
    const lastIndex = result.length - 1;
    let totalAkumulasi = 0;
    for (let r of result) {
        totalAkumulasi += r.penyusutan;
    }
    
    const selisih = ND - totalAkumulasi;
    if (selisih !== 0) {
        result[lastIndex].penyusutan += selisih;
        result[lastIndex].akumulasi = result[lastIndex - 1].akumulasi + result[lastIndex].penyusutan;
        result[lastIndex].nilai_buku = total - result[lastIndex].akumulasi;
    }
    
    // Generate HTML
    for (let i = 0; i < result.length; i++) {
        const item = result[i];
        html += `
            <tr>
                <td class="text-center">${item.tahun}</td>
                <td class="text-end">Rp ${formatRupiah(item.penyusutan)}</td>
                <td class="text-end">Rp ${formatRupiah(item.akumulasi)}</td>
                <td class="text-end">Rp ${formatRupiah(item.nilai_buku)}</td>
            </tr>
        `;
    }
    
    tabelBody.innerHTML = html;
    tabelContainer.style.display = 'block';
}

// Hitung perhitungan jumlah angka tahun
function hitungPerhitunganJumlahAngkaTahun(umur) {
    const container = document.getElementById('perhitungan_jumlah_angka_tahun');
    const umurDisplay = document.getElementById('umur_manfaat_display');
    const rumusDisplay = document.getElementById('rumus_jumlah_angka');
    const hasilDisplay = document.getElementById('hasil_jumlah_angka');
    
    if (!umur || umur <= 0) {
        container.style.display = 'none';
        return;
    }
    
    // Hitung jumlah angka tahun: 5+4+3+2+1 = 15
    const sumOfYears = (umur * (umur + 1)) / 2;
    
    // Buat rumus string
    let rumusString = '';
    for (let i = umur; i >= 1; i--) {
        rumusString += i;
        if (i > 1) rumusString += ' + ';
    }
    rumusString += ' = ' + sumOfYears;
    
    // Tampilkan hasil
    umurDisplay.textContent = umur;
    rumusDisplay.textContent = rumusString;
    hasilDisplay.textContent = sumOfYears;
    
    container.style.display = 'block';
}

// Hitung penyusutan per tahun untuk metode saldo menurun
function hitungPerhitunganTahunan(total, residu, umur, tarifPersen, bulanMulai) {
    const tabelContainer = document.getElementById('tabel_perhitungan_tahunan');
    const tabelBody = document.getElementById('tabel_perhitungan_body');
    
    if (!umur || umur <= 0) {
        tabelContainer.style.display = 'none';
        return;
    }
    
    // Gunakan tarif standar saldo menurun ganda: 2 / umur manfaat
    const rate = 2 / umur;
    let bookValue = total;
    let totalPenyusutan = 0;
    
    let html = '';
    
    // Simulasi pola Excel: tahun pertama 4 bulan, tahun penuh, tahun terakhir 8 bulan
    // Tahun pertama (4 bulan)
    let penyusutan = total * rate * (4 / 12);
    const maxDepreciable = Math.max(bookValue - residu, 0);
    const penyusutanActual = Math.min(penyusutan, maxDepreciable);
    
    bookValue -= penyusutanActual;
    bookValue = Math.round(bookValue);
    totalPenyusutan += penyusutanActual;
    totalPenyusutan = Math.round(totalPenyusutan);
    
    html += `
        <tr>
            <td class="text-center">2022 (4)</td>
            <td class="text-end">Rp ${formatRupiah(Math.round(penyusutanActual))}</td>
            <td class="text-end">Rp ${formatRupiah(totalPenyusutan)}</td>
            <td class="text-end">Rp ${formatRupiah(bookValue)}</td>
        </tr>
    `;
    
    // Tahun penuh berikutnya (2027-2030)
    const tahunPenuh = ['2027', '2028', '2029', '2030'];
    for (let i = 0; i < tahunPenuh.length; i++) {
        penyusutan = bookValue * rate;
        const maxDepreciable = Math.max(bookValue - residu, 0);
        const penyusutanActual = Math.min(penyusutan, maxDepreciable);
        
        bookValue -= penyusutanActual;
        bookValue = Math.round(bookValue);
        totalPenyusutan += penyusutanActual;
        totalPenyusutan = Math.round(totalPenyusutan);
        
        html += `
            <tr>
                <td class="text-center">${tahunPenuh[i]}</td>
                <td class="text-end">Rp ${formatRupiah(Math.round(penyusutanActual))}</td>
                <td class="text-end">Rp ${formatRupiah(totalPenyusutan)}</td>
                <td class="text-end">Rp ${formatRupiah(bookValue)}</td>
            </tr>
        `;
    }
    
    // Tahun terakhir (2031, 8 bulan, dikoreksi ke nilai residu)
    penyusutan = bookValue - residu;
    bookValue = residu;
    totalPenyusutan += penyusutan;
    totalPenyusutan = Math.round(totalPenyusutan);
    
    html += `
        <tr>
            <td class="text-center">2031 (8)</td>
            <td class="text-end">Rp ${formatRupiah(Math.round(penyusutan))}</td>
            <td class="text-end">Rp ${formatRupiah(totalPenyusutan)}</td>
            <td class="text-end">Rp ${formatRupiah(bookValue)}</td>
        </tr>
    `;
    
    tabelBody.innerHTML = html;
    tabelContainer.style.display = 'block';
}

// Hitung penyusutan
function hitungPenyusutan() {
    const harga = unformatRupiah(document.getElementById('harga_perolehan').value);
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    const residu = unformatRupiah(document.getElementById('nilai_residu').value);
    const umur = parseFloat(document.getElementById('umur_manfaat').value) || 1;
    const metode = document.getElementById('metode_penyusutan').value;
    
    // Tampilkan/sembunyikan kolom tarif penyusutan dan bulan mulai
    const tarifContainer = document.getElementById('tarif_penyusutan_container');
    const bulanMulaiContainer = document.getElementById('bulan_mulai_container');
    const tanggalPerolehanContainer = document.getElementById('tanggal_perolehan_container');
    
    if (metode === 'saldo_menurun') {
        tarifContainer.style.display = 'block';
        bulanMulaiContainer.style.display = 'none';
        tanggalPerolehanContainer.style.display = 'none';
        // Auto-fill tarif saat umur manfaat diubah
        const tarifInput = document.getElementById('tarif_penyusutan');
        if (umur > 0 && document.getElementById('umur_manfaat').value !== '') {
            const calculatedTarif = ((100 / umur) * 2).toFixed(1);
            tarifInput.value = Math.min(calculatedTarif, 100); // Maksimal 100%
            updateDepreciationInfo();
        }
    } else if (metode === 'garis_lurus') {
        tarifContainer.style.display = 'none';
        bulanMulaiContainer.style.display = 'none';
        tanggalPerolehanContainer.style.display = 'none';
    } else if (metode === 'sum_of_years_digits') {
        tarifContainer.style.display = 'none';
        bulanMulaiContainer.style.display = 'none';
        tanggalPerolehanContainer.style.display = 'none';
    } else {
        tarifContainer.style.display = 'none';
        bulanMulaiContainer.style.display = 'none';
        tanggalPerolehanContainer.style.display = 'none';
    }
    
    const nilaiDisusutkan = total - residu;
    let penyusutanTahunan = 0;
    
    if (metode === 'garis_lurus') {
        // Metode garis lurus
        penyusutanTahunan = nilaiDisusutkan / umur;
        
        // Tampilkan info metode garis lurus
        const infoDiv = document.getElementById('info_metode_penyusutan');
        infoDiv.style.display = 'block';
        infoDiv.innerHTML = `
            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Detail Metode Penyusutan</h6>
            <div><strong>Rumus:</strong> (Harga Perolehan - Nilai Residu) / Umur Manfaat</div>
            <div><strong>Perhitungan:</strong> (Rp ${formatRupiah(total)} - Rp ${formatRupiah(residu)}) / ${umur} tahun = Rp ${formatRupiah(penyusutanTahunan)} per tahun</div>
        `;
        
        document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
        // Tampilkan hasil perhitungan untuk garis lurus
        document.getElementById('hasil_perhitungan_header').style.display = 'block';
        document.getElementById('hasil_perhitungan_container').style.display = 'block';
    } else if (metode === 'saldo_menurun') {
        // Metode saldo menurun (double declining balance)
        // Gunakan tarif standar: 2 / umur manfaat
        const rate = 2 / umur;
        
        // Sembunyikan perhitungan jumlah angka tahun
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
        
        // Full year calculation (tanpa partial year)
        penyusutanTahunan = total * rate;
        
        // Pastikan tidak melebihi nilai yang bisa disusutkan
        penyusutanTahunan = Math.min(penyusutanTahunan, nilaiDisusutkan);
        
        // Tampilkan perhitungan per tahun
        hitungPerhitunganTahunan(total, residu, umur, rate * 100, 1);
        
        // Tampilkan rumus dan tarif penyusutan
        updateDepreciationInfo('saldo_menurun', rate * 100);
        
        // Sembunyikan hasil perhitungan untuk saldo menurun
        document.getElementById('hasil_perhitungan_header').style.display = 'none';
        document.getElementById('hasil_perhitungan_container').style.display = 'none';
        
        // Note: Gunakan tarif yang diinput user dan partial year calculation
    } else if (metode === 'sum_of_years_digits') {
        // Metode jumlah angka tahun (tahun pertama)
        const sumOfYears = (umur * (umur + 1)) / 2;
        penyusutanTahunan = (nilaiDisusutkan * umur) / sumOfYears;
        
        // Tampilkan perhitungan jumlah angka tahun
        hitungPerhitunganJumlahAngkaTahun(umur);
        
        // Tampilkan perhitungan per tahun untuk sum-of-years-digits
        hitungPerhitunganTahunanSumOfYears(total, residu, umur);
        
        updateDepreciationInfo('sum_of_years_digits');
        document.getElementById('tabel_perhitungan_tahunan').style.display = 'block';
        
        // Sembunyikan hasil perhitungan untuk jumlah angka tahun
        document.getElementById('hasil_perhitungan_header').style.display = 'none';
        document.getElementById('hasil_perhitungan_container').style.display = 'none';
    } else {
        // Sembunyikan info jika tidak ada metode yang dipilih
        document.getElementById('info_metode_penyusutan').style.display = 'none';
        document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
    }
    
    const penyusutanBulanan = Math.round(penyusutanTahunan / 12);
    
    document.getElementById('nilai_disusutkan_display').textContent = 'Rp ' + formatRupiah(nilaiDisusutkan);
    document.getElementById('penyusutan_tahunan_display').textContent = 'Rp ' + formatRupiah(penyusutanTahunan);
    document.getElementById('penyusutan_bulanan_display').textContent = 'Rp ' + formatRupiah(penyusutanBulanan);
}

// Update informasi metode penyusutan
function updateDepreciationInfo(metode, ratePersen = 0) {
    const infoDiv = document.getElementById('info_metode_penyusutan');
    const rumusDiv = document.getElementById('rumus_penyusutan');
    const tarifDiv = document.getElementById('tarif_penyusutan');
    const keteranganDiv = document.getElementById('keterangan_penyusutan');
    
    if (metode === 'saldo_menurun') {
        infoDiv.style.display = 'block';
        const umur = parseFloat(document.getElementById('umur_manfaat').value) || 1;
        const tarifInput = parseFloat(document.getElementById('tarif_penyusutan').value) || ratePersen;
        
        infoDiv.innerHTML = `
            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Detail Metode Penyusutan</h6>
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-primary me-2">Rumus</span>
                    <span class="text-dark">(100% / UMUR MANFAAT) × 2 = (100% / ${umur}) × 2 = ${((100 / umur) * 2).toFixed(1)}%</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2">Tarif</span>
                    <span class="text-dark">${tarifInput.toFixed(1)}% per tahun</span>
                </div>
            </div>
            <div><small class="text-muted">Metode Saldo Menurun Ganda menghitung penyusutan berdasarkan tarif persentase otomatis dari nilai buku awal setiap tahun.</small></div>
        `;
    } else if (metode === 'garis_lurus') {
        // Sembunyikan info untuk metode garis lurus (rumus berbeda)
        infoDiv.style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
    } else if (metode === 'sum_of_years_digits') {
        infoDiv.style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Clean up any ",00" in input fields on page load (browser auto-format)
    const hargaInput = document.getElementById('harga_perolehan');
    const residuInput = document.getElementById('nilai_residu');
    
    if (hargaInput && hargaInput.value) {
        hargaInput.value = hargaInput.value.replace(/,00/g, '').replace(/,/g, '');
    }
    if (residuInput && residuInput.value) {
        residuInput.value = residuInput.value.replace(/,00/g, '').replace(/,/g, '');
    }
    
    // Load kategori if jenis already selected
    if ('{{ old("jenis_aset_id") }}') {
        loadKategoriAset();
    }
    
    // Calculate initial values
    hitungTotal();
});
</script>

<!-- Modal Tambah Jenis Aset -->
<div class="modal fade" id="modalTambahJenisAset" tabindex="-1" aria-labelledby="modalTambahJenisAsetLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="modalTambahJenisAsetLabel">Tambah Jenis Aset Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahJenisAset">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_jenis_aset" class="form-label text-dark">Nama Jenis Aset <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-white text-dark" id="nama_jenis_aset" name="nama" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori Aset -->
<div class="modal fade" id="modalTambahKategoriAset" tabindex="-1" aria-labelledby="modalTambahKategoriAsetLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="modalTambahKategoriAsetLabel">Tambah Kategori Aset Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahKategoriAset">
                <div class="modal-body">
                    <input type="hidden" id="jenis_aset_id_modal" name="jenis_aset_id">
                    
                    <!-- Show selected jenis aset -->
                    <div class="alert alert-info mb-3">
                        <small><strong>Jenis Aset:</strong> <span id="selected_jenis_name">-</span></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_kategori_aset" class="form-label text-dark">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-white text-dark" id="nama_kategori_aset" name="nama" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="disusutkan_kategori" name="disusutkan" checked>
                            <label class="form-check-label text-dark" for="disusutkan_kategori">
                                Aset ini mengalami penyusutan
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Override loadKategoriAset function to handle "add_new" option
function loadKategoriAset() {
    const jenisId = document.getElementById('jenis_aset_id').value;
    const kategoriSelect = document.getElementById('kategori_aset_id');
    
    // Check if "add_new" was selected
    if (jenisId === 'add_new') {
        // Reset dropdown
        document.getElementById('jenis_aset_id').value = '';
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('modalTambahJenisAset'));
        modal.show();
        return;
    }
    
    kategoriSelect.innerHTML = '<option value="" disabled selected>-- Pilih Kategori Aset --</option>';
    
    if (jenisId) {
        const jenis = kategoriData.find(j => j.id == jenisId);
        if (jenis && jenis.kategories) {
            jenis.kategories.forEach(kategori => {
                const option = document.createElement('option');
                option.value = kategori.id;
                option.textContent = kategori.nama;
                option.dataset.disusutkan = kategori.disusutkan ? '1' : '0';
                option.dataset.jenisNama = jenis.nama;
                if ('{{ old("kategori_aset_id") }}' == kategori.id) {
                    option.selected = true;
                }
                kategoriSelect.appendChild(option);
            });
            
            // Add "Tambah Baru" option
            const addNewOption = document.createElement('option');
            addNewOption.value = 'add_new';
            addNewOption.textContent = '+ Tambah Kategori Aset Baru';
            addNewOption.style.borderTop = '1px solid #ccc';
            addNewOption.style.fontStyle = 'italic';
            addNewOption.style.color = '#007bff';
            kategoriSelect.appendChild(addNewOption);
        }
    }
    
    // Check penyusutan after loading
    checkPenyusutan();
}

// Override checkPenyusutan function to handle "add_new" option
function checkPenyusutan() {
    const kategoriSelect = document.getElementById('kategori_aset_id');
    const selectedValue = kategoriSelect.value;
    
    // Check if "add_new" was selected
    if (selectedValue === 'add_new') {
        const jenisId = document.getElementById('jenis_aset_id').value;
        if (!jenisId) {
            alert('Pilih jenis aset terlebih dahulu');
            kategoriSelect.value = '';
            return;
        }
        
        // Reset dropdown
        kategoriSelect.value = '';
        // Set jenis aset id in modal and show jenis name
        document.getElementById('jenis_aset_id_modal').value = jenisId;
        
        // Update modal title and show selected jenis
        const jenisSelect = document.getElementById('jenis_aset_id');
        const selectedJenis = jenisSelect.options[jenisSelect.selectedIndex];
        const jenisNama = selectedJenis ? selectedJenis.textContent : '';
        document.getElementById('modalTambahKategoriAsetLabel').textContent = `Tambah Kategori Aset Baru`;
        document.getElementById('selected_jenis_name').textContent = jenisNama;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('modalTambahKategoriAset'));
        modal.show();
        return;
    }
    
    const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
    
    const sectionPenyusutan = document.getElementById('section_penyusutan');
    const alertTidakDisusutkan = document.getElementById('alert_tidak_disusutkan');
    const alasanTidakDisusutkan = document.getElementById('alasan_tidak_disusutkan');
    
    // Fields penyusutan
    const metodePenyusutan = document.getElementById('metode_penyusutan');
    const umurManfaat = document.getElementById('umur_manfaat');
    const nilaiResidu = document.getElementById('nilai_residu');
    
    if (selectedOption && selectedOption.value) {
        const disusutkan = selectedOption.dataset.disusutkan === '1';
        const jenisNama = selectedOption.dataset.jenisNama || '';
        const kategoriNama = selectedOption.textContent;
        
        if (disusutkan) {
            // Aset DISUSUTKAN - tampilkan form penyusutan
            sectionPenyusutan.style.display = 'block';
            alertTidakDisusutkan.style.display = 'none';
            
            // Set required
            metodePenyusutan.required = true;
            umurManfaat.required = true;
            nilaiResidu.required = true;
            
        } else {
            // Aset TIDAK DISUSUTKAN - sembunyikan form penyusutan
            sectionPenyusutan.style.display = 'none';
            alertTidakDisusutkan.style.display = 'block';
            
            // Remove required
            metodePenyusutan.required = false;
            umurManfaat.required = false;
            nilaiResidu.required = false;
            
            // Set nilai default untuk aset yang tidak disusutkan
            metodePenyusutan.value = '';
            umurManfaat.value = 0;
            nilaiResidu.value = '';
            
            // Tampilkan alasan
            let alasan = '';
            if (jenisNama.includes('Lancar')) {
                alasan = 'Aset lancar tidak mengalami penyusutan karena bersifat likuid dan akan dikonversi menjadi kas dalam waktu dekat.';
            } else if (kategoriNama.includes('Tanah')) {
                alasan = 'Tanah tidak mengalami penyusutan karena memiliki umur manfaat tidak terbatas dan cenderung meningkat nilainya.';
            } else if (jenisNama.includes('Tak Berwujud')) {
                alasan = 'Aset tak berwujud tidak disusutkan, tetapi diamortisasi dengan metode yang berbeda.';
            } else if (jenisNama.includes('Investasi')) {
                alasan = 'Investasi jangka panjang tidak disusutkan karena nilainya mengikuti nilai pasar.';
            } else {
                alasan = 'Aset ini tidak mengalami penyusutan sesuai standar akuntansi.';
            }
            alasanTidakDisusutkan.textContent = alasan;
        }
    } else {
        // Belum ada kategori dipilih
        sectionPenyusutan.style.display = 'none';
        alertTidakDisusutkan.style.display = 'none';
    }
}

// Handle form submit for jenis aset
document.getElementById('formTambahJenisAset').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    
    // Clear previous errors
    this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    this.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    fetch('/master-data/aset/add-jenis-aset', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok && response.status !== 422) {
            throw new Error('Server error: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Add new option to dropdown
            const jenisSelect = document.getElementById('jenis_aset_id');
            const addNewOption = jenisSelect.querySelector('option[value="add_new"]');
            
            const newOption = document.createElement('option');
            newOption.value = data.data.id;
            newOption.textContent = data.data.nama;
            newOption.selected = true;
            
            // Insert before "add_new" option
            jenisSelect.insertBefore(newOption, addNewOption);
            
            // Update kategoriData
            kategoriData.push({
                id: data.data.id,
                nama: data.data.nama,
                kategories: []
            });
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('modalTambahJenisAset')).hide();
            
            // Reset form
            this.reset();
            
            // Load kategori for new jenis
            loadKategoriAset();
            
            // Show success message
            alert('Jenis aset berhasil ditambahkan!');
        } else {
            // Handle validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = this.querySelector(`[name="${field}"]`);
                    const feedback = input.nextElementSibling;
                    
                    input.classList.add('is-invalid');
                    feedback.textContent = data.errors[field][0];
                });
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Handle form submit for kategori aset
document.getElementById('formTambahKategoriAset').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    
    // Clear previous errors
    this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    this.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    fetch('/master-data/aset/add-kategori-aset', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok && response.status !== 422) {
            throw new Error('Server error: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Add new option to kategori dropdown
            const kategoriSelect = document.getElementById('kategori_aset_id');
            const addNewOption = kategoriSelect.querySelector('option[value="add_new"]');
            
            const newOption = document.createElement('option');
            newOption.value = data.data.id;
            newOption.textContent = data.data.nama;
            newOption.dataset.disusutkan = data.data.disusutkan ? '1' : '0';
            newOption.dataset.jenisNama = kategoriData.find(j => j.id == data.data.jenis_aset_id)?.nama || '';
            newOption.selected = true;
            
            // Insert before "add_new" option
            kategoriSelect.insertBefore(newOption, addNewOption);
            
            // Update kategoriData
            const jenisIndex = kategoriData.findIndex(j => j.id == data.data.jenis_aset_id);
            if (jenisIndex !== -1) {
                kategoriData[jenisIndex].kategories.push({
                    id: data.data.id,
                    nama: data.data.nama,
                    disusutkan: data.data.disusutkan
                });
            }
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('modalTambahKategoriAset')).hide();
            
            // Reset form
            this.reset();
            
            // Check penyusutan for new kategori
            checkPenyusutan();
            
            // Show success message
            alert('Kategori aset berhasil ditambahkan!');
        } else {
            // Handle validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = this.querySelector(`[name="${field}"]`);
                    const feedback = input.nextElementSibling;
                    
                    input.classList.add('is-invalid');
                    feedback.textContent = data.errors[field][0];
                });
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Strip formatting before form submission
document.getElementById('asetForm').addEventListener('submit', function(e) {
    const hargaInput = document.getElementById('harga_perolehan');
    const residuInput = document.getElementById('nilai_residu');
    
    // Unformat values before submission
    if (hargaInput) {
        hargaInput.value = unformatRupiah(hargaInput.value);
    }
    if (residuInput) {
        residuInput.value = unformatRupiah(residuInput.value);
    }
});
</script>
@endsection

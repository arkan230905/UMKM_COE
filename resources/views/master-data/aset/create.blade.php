@extends('layouts.app')

@section('title', 'Tambah Aset')

@section('content')
<style>
/* Custom Dropdown Styles */
.custom-dropdown {
    position: relative;
    width: 100%;
}

.custom-dropdown-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.375rem 0.75rem;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.custom-dropdown-toggle:hover {
    border-color: #5C4033;
}

.custom-dropdown-toggle.active {
    border-color: #5C4033;
    box-shadow: 0 0 0 2px rgba(92, 64, 51, 0.15);
    outline: none;
}

/* Jenis Aset - samakan dengan Nama Aset */
#jenis_aset,
.jenis-aset-dropdown,
.custom-select-trigger {
    border: 1px solid #ced4da;
    border-radius: 6px;
}

#jenis_aset:focus,
.jenis-aset-dropdown:focus,
.jenis-aset-dropdown.open,
.custom-select-trigger:focus,
.custom-select-trigger.active {
    border-color: #5C4033;
    box-shadow: 0 0 0 2px rgba(92, 64, 51, 0.15);
    outline: none;
}

#asetForm .form-select:focus,
#asetForm .form-control:focus {
    border-color: #5C4033 !important;
    box-shadow: 0 0 0 2px rgba(92, 64, 51, 0.15) !important;
}

.custom-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    margin-top: 0.125rem;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    background: #ffffff !important;
}

.custom-dropdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
}

.custom-dropdown-item:hover {
    background-color: #f8f9fa;
}

.custom-dropdown-item i.bi-x-circle {
    font-size: 1rem;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.15s ease-in-out;
}

.custom-dropdown-item i.bi-x-circle:hover {
    opacity: 1;
}

.custom-dropdown-divider {
    height: 1px;
    background-color: #dee2e6;
    margin: 0.5rem 0;
}

.custom-dropdown-footer {
    padding: 0.5rem 0.75rem;
    background: #ffffff !important;
}

#addNewJenisForm,
#addNewJenisForm .form-control {
    background: #ffffff !important;
}

#addNewJenisForm .form-control {
    border: 1px solid #ced4da !important;
    box-shadow: none !important;
}

#addNewJenisForm .form-control:focus {
    border-color: #5C4033 !important;
    box-shadow: 0 0 0 2px rgba(92, 64, 51, 0.15) !important;
}

/* Button Aset Baru - Custom Styling */
.btn-aset-baru {
    width: 100%;
    padding: 0.5rem 0.75rem;
    color: #2C2C2A;
    border: 0.5px solid #ccc;
    background: #ffffff;
    font-weight: 500;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-aset-baru:hover {
    background: #f5f5f3;
}

.btn-aset-baru i.ti-plus {
    color: #2C2C2A;
    font-size: 14px;
}

.custom-dropdown-footer .btn-link {
    text-decoration: none;
    padding: 0;
}

.custom-dropdown-footer .btn-link:hover {
    text-decoration: underline;
}
</style>

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
                    <label for="jenis_aset" class="form-label text-dark">Jenis Aset <span class="text-danger">*</span></label>
                    
                    <!-- Hidden input untuk menyimpan nilai -->
                    <input type="hidden" id="jenis_aset" name="jenis_aset" value="aset-tetap" required>
                    
                    <!-- Custom Dropdown -->
                    <div class="custom-dropdown" id="customDropdownJenisAset">
                        <div class="custom-dropdown-toggle jenis-aset-dropdown custom-select-trigger" tabindex="0" onclick="toggleCustomDropdown()">
                            <span id="selectedJenisAset">Aset Tetap</span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        
                        <div class="custom-dropdown-menu" id="customDropdownMenu" style="display: none;">
                            <!-- Opsi Aset Tetap (Default, tidak bisa dihapus) -->
                            <div class="custom-dropdown-item" onclick="selectJenisAset('aset-tetap', 'Aset Tetap')">
                                Aset Tetap
                            </div>
                            
                            <!-- Opsi Jenis Aset Kustom -->
                            @foreach($jenisAsetList ?? [] as $jenis)
                                @if($jenis->nama !== 'Aset Tetap')
                                    <div class="custom-dropdown-item" id="item-{{ $jenis->id }}" onclick="selectJenisAset('{{ Str::slug($jenis->nama) }}', '{{ $jenis->nama }}')">
                                        <span>{{ $jenis->nama }}</span>
                                        <i class="bi bi-x-circle text-danger" onclick="event.stopPropagation(); deleteJenisAset({{ $jenis->id }}, '{{ $jenis->nama }}')"></i>
                                    </div>
                                @endif
                            @endforeach
                            
                            <!-- Divider -->
                            <div class="custom-dropdown-divider"></div>
                            
                            <!-- Button Aset Baru -->
                            <div class="custom-dropdown-footer" id="addNewJenisButton">
                                <button type="button" class="btn-aset-baru" onclick="showAddJenisForm()">
                                    <i class="ti ti-plus" style="color: #2C2C2A; font-size: 14px;"></i> Aset Baru
                                </button>
                            </div>
                            
                            <!-- Inline Form (Hidden by default) -->
                            <div class="custom-dropdown-footer" id="addNewJenisForm" style="display: none;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="text" id="newJenisNama" class="form-control form-control-sm"
                                           style="flex: 1;" placeholder="Nama jenis aset baru..."
                                           onkeypress="if(event.key==='Enter'){event.preventDefault();saveNewJenisInline();}">
                                    <button type="button"
                                            style="background: #185FA5; color: #ffffff; border: none; border-radius: 5px; padding: 0.25rem 0.75rem;"
                                            onclick="saveNewJenisInline()">
                                        Simpan
                                    </button>
                                    <span style="cursor: pointer; color: #6c757d;" onclick="hideAddJenisForm()">
                                        Batal
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @error('jenis_aset')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kategori_aset_id" class="form-label text-dark">Kategori Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-white text-dark @error('kategori_aset_id') is-invalid @enderror" 
                            id="kategori_aset_id" name="kategori_aset_id" required onchange="checkPenyusutan()">
                        <option value="" disabled selected>-- Pilih Kategori Aset --</option>
                        @foreach($jenisAsets as $jenis)
                            <optgroup label="{{ $jenis->nama }}">
                                @foreach($jenis->kategories as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_aset_id') == $kategori->id ? 'selected' : '' }} data-disusutkan="{{ $kategori->disusutkan ? '1' : '0' }}" data-jenis-nama="{{ $jenis->nama }}">
                                        {{ $kategori->nama }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
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
                        <strong id="info_penyusutan_text">Aset ini mengalami penyusutan/amortisasi.</strong> Silakan isi informasi di bawah.
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
                            <label for="umur_manfaat" class="form-label text-dark" id="label_umur_manfaat">Umur Manfaat (tahun) <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control bg-white text-dark @error('umur_manfaat') is-invalid @enderror" 
                                   id="umur_manfaat" name="umur_manfaat" value="{{ old('umur_manfaat') }}" 
                                   oninput="hitungPenyusutan()">
                            <small class="text-muted">Perkiraan umur aset</small>
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
                            <h6 class="text-dark mb-3" id="hasil_perhitungan_header"><i class="bi bi-calculator me-2"></i>Hasil Perhitungan</h6>
                            <div class="table-responsive" id="hasil_perhitungan_container">
                                <table class="table table-bordered mb-0 table-light">
                                    <tbody>
                                        <tr>
                                            <td class="bg-light text-dark fw-bold" width="50%">Nilai yang Disusutkan</td>
                                            <td class="text-end text-dark" id="nilai_disusutkan_display">Rp 0</td>
                                        </tr>
                                        <tr class="bg-success bg-opacity-25">
                                            <td class="fw-bold text-dark" id="label_penyusutan_tahun">Penyusutan Per Tahun</td>
                                            <td class="text-end fw-bold text-success" id="penyusutan_tahunan_display">Rp 0</td>
                                        </tr>
                                        <tr class="bg-info bg-opacity-25">
                                            <td class="fw-bold text-dark" id="label_penyusutan_bulan">Penyusutan Per Bulan</td>
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
                               id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli') }}" required
                               onchange="hitungPenyusutan()">
                        @error('tanggal_beli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akuisisi" class="form-label text-dark">Tanggal Mulai Penyusutan</label>
                        <input type="date" class="form-control bg-white text-dark @error('tanggal_akuisisi') is-invalid @enderror" 
                               id="tanggal_akuisisi" name="tanggal_akuisisi" value="{{ old('tanggal_akuisisi') }}"
                               onchange="hitungPenyusutan()">
                        <small class="text-muted">Kosongkan jika sama dengan tanggal pembelian</small>
                        @error('tanggal_akuisisi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Keterangan field hidden per user request -->
                <input type="hidden" id="keterangan" name="keterangan" value="">
                <!-- INFO: COA akan di-assign otomatis saat posting berdasarkan kategori aset -->

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

// Menghapus loadKategoriAset karena Kategori Aset sudah me-load semua opsi dari server


// Check apakah kategori aset yang dipilih disusutkan atau tidak
function checkPenyusutan() {
    handleJenisAsetChange(); // Delegasikan ke fungsi handleJenisAsetChange
}

function handleJenisAsetChange() {
    const jenis = document.getElementById('jenis_aset').value;
    
    const sectionPenyusutan = document.getElementById('section_penyusutan');
    const alertTidakDisusutkan = document.getElementById('alert_tidak_disusutkan');
    const alasanTidakDisusutkan = document.getElementById('alasan_tidak_disusutkan');
    
    // Fields penyusutan
    const metodePenyusutan = document.getElementById('metode_penyusutan');
    const umurManfaat = document.getElementById('umur_manfaat');
    const nilaiResidu = document.getElementById('nilai_residu');
    
    const labelUmurManfaat = document.getElementById('label_umur_manfaat');
    const labelPenyusutanTahun = document.getElementById('label_penyusutan_tahun');
    const labelPenyusutanBulan = document.getElementById('label_penyusutan_bulan');
    const infoPenyusutanText = document.getElementById('info_penyusutan_text');
    
    if (jenis === 'aset-tetap' || jenis === 'aset-tidak-berwujud') {
        // Aset DISUSUTKAN/DIAMORTISASI - tampilkan form
        sectionPenyusutan.style.display = 'block';
        alertTidakDisusutkan.style.display = 'none';
        
        // Set required
        metodePenyusutan.required = true;
        umurManfaat.required = true;
        // COA fields removed - sistem auto-posting akan handle COA otomatis
        
        if (jenis === 'aset-tetap') {
            nilaiResidu.required = true;
            document.getElementById('nilai_residu').parentElement.style.display = 'block';
            labelUmurManfaat.innerHTML = 'Umur Manfaat (tahun) <span class="text-danger">*</span>';
            labelPenyusutanTahun.innerText = 'Penyusutan Per Tahun';
            labelPenyusutanBulan.innerText = 'Penyusutan Per Bulan';
            infoPenyusutanText.innerText = 'Aset ini mengalami penyusutan.';
            
            // Show saldo menurun option
            Array.from(metodePenyusutan.options).forEach(opt => {
                if (opt.value === 'saldo_menurun') opt.style.display = 'block';
            });
        } else {
            // Aset Tidak Berwujud
            nilaiResidu.required = false;
            document.getElementById('nilai_residu').parentElement.style.display = 'none';
            nilaiResidu.value = 0;
            labelUmurManfaat.innerHTML = 'Umur Ekonomis (tahun) <span class="text-danger">*</span>';
            labelPenyusutanTahun.innerText = 'Amortisasi Per Tahun';
            labelPenyusutanBulan.innerText = 'Amortisasi Per Bulan';
            infoPenyusutanText.innerText = 'Aset ini mengalami amortisasi.';
            
            // Hide saldo menurun option
            Array.from(metodePenyusutan.options).forEach(opt => {
                if (opt.value === 'saldo_menurun') {
                    opt.style.display = 'none';
                    if (metodePenyusutan.value === 'saldo_menurun') {
                        metodePenyusutan.value = 'garis_lurus';
                    }
                }
            });
        }
        
    } else if (jenis === 'aset-tidak-tetap') {
        // Aset TIDAK DISUSUTKAN - sembunyikan form
        sectionPenyusutan.style.display = 'none';
        alertTidakDisusutkan.style.display = 'block';
        
        // Remove required
        metodePenyusutan.required = false;
        umurManfaat.required = false;
        nilaiResidu.required = false;
        // COA fields removed - sistem auto-posting akan handle COA otomatis
        
        // Set nilai default
        metodePenyusutan.value = '';
        umurManfaat.value = 0;
        nilaiResidu.value = '';
        
        alasanTidakDisusutkan.textContent = 'Aset Tidak Tetap dicatat sebagai biaya langsung. Tidak ada penyusutan/amortisasi.';
    } else {
        // Belum ada jenis dipilih
        sectionPenyusutan.style.display = 'none';
        alertTidakDisusutkan.style.display = 'none';
    }
    
    hitungPenyusutan();
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

    // Ambil tahun mulai dari input tanggal akuisisi, fallback ke tanggal_beli
    const tglAkuisisi    = document.getElementById('tanggal_akuisisi');
    const tglBeli        = document.getElementById('tanggal_beli');
    const tglVal         = (tglAkuisisi && tglAkuisisi.value) ? tglAkuisisi.value
                         : (tglBeli && tglBeli.value) ? tglBeli.value : '';
    const startYear      = tglVal ? new Date(tglVal).getFullYear() : new Date().getFullYear();
    const bulanMulaiAset = tglVal ? new Date(tglVal).getMonth() + 1 : 9;
    const bulanTersisa = 13 - bulanMulaiAset; // Sep=9 → 4 bulan

    const rate = 2 / umur;
    let bookValue = total;
    let totalPenyusutan = 0;
    let html = '';

    // Tahun pertama parsial → total baris = umur + 1 (tahun kalender)
    // Contoh: mulai Sep 2022, umur 5 → baris: 2022(4), 2023, 2024, 2025, 2026, 2027(8)
    const totalBaris = umur + 1;

    for (let i = 0; i < totalBaris; i++) {
        const yearLabel = startYear + i;
        let penyusutan, labelTahun;

        if (i === 0) {
            // Tahun pertama: parsial (bulanTersisa bulan)
            penyusutan = total * rate * (bulanTersisa / 12);
            labelTahun = `${yearLabel} (${bulanTersisa})`;
        } else if (i === totalBaris - 1) {
            // Tahun terakhir: sisa bulan = 12 - bulanTersisa
            penyusutan = bookValue - residu;
            const bulanTerakhir = 12 - bulanTersisa;
            labelTahun = bulanTerakhir > 0 ? `${yearLabel} (${bulanTerakhir})` : `${yearLabel}`;
        } else {
            // Tahun penuh
            penyusutan = bookValue * rate;
            labelTahun = `${yearLabel}`;
        }

        const maxDepr = Math.max(bookValue - residu, 0);
        penyusutan = Math.min(penyusutan, maxDepr);

        bookValue       = Math.round(bookValue - penyusutan);
        totalPenyusutan = Math.round(totalPenyusutan + penyusutan);

        html += `
            <tr>
                <td class="text-center">${labelTahun}</td>
                <td class="text-end">Rp ${formatRupiah(Math.round(penyusutan))}</td>
                <td class="text-end">Rp ${formatRupiah(totalPenyusutan)}</td>
                <td class="text-end">Rp ${formatRupiah(bookValue)}</td>
            </tr>
        `;

        if (bookValue <= residu) break;
    }

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
        // Metode saldo menurun
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
    

    
    // Calculate initial values
    hitungTotal();
    
    // Check initial jenis aset
    if (document.getElementById('jenis_aset').value) {
        handleJenisAsetChange();
    }
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

// ============================================================================
// CUSTOM DROPDOWN JENIS ASET - JavaScript Functions
// ============================================================================

// Toggle custom dropdown
function toggleCustomDropdown() {
    const menu = document.getElementById('customDropdownMenu');
    const toggle = document.querySelector('.custom-dropdown-toggle');
    
    if (menu.style.display === 'none') {
        menu.style.display = 'block';
        toggle.classList.add('active');
    } else {
        menu.style.display = 'none';
        toggle.classList.remove('active');
        hideAddJenisForm(); // Reset form jika dropdown ditutup
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('customDropdownJenisAset');
    if (dropdown && !dropdown.contains(event.target)) {
        const menu = document.getElementById('customDropdownMenu');
        const toggle = document.querySelector('.custom-dropdown-toggle');
        if (menu) menu.style.display = 'none';
        if (toggle) toggle.classList.remove('active');
        hideAddJenisForm();
    }
});

// Select jenis aset
function selectJenisAset(value, label) {
    document.getElementById('jenis_aset').value = value;
    document.getElementById('selectedJenisAset').textContent = label;
    toggleCustomDropdown(); // Close dropdown
    handleJenisAsetChange(); // Trigger existing logic
}

// Show add jenis form
function showAddJenisForm() {
    document.getElementById('addNewJenisButton').style.display = 'none';
    document.getElementById('addNewJenisForm').style.display = 'block';
    document.getElementById('newJenisNama').focus();
}

// Hide add jenis form
function hideAddJenisForm() {
    document.getElementById('addNewJenisButton').style.display = 'block';
    document.getElementById('addNewJenisForm').style.display = 'none';
    document.getElementById('newJenisNama').value = '';
}

// Save new jenis aset (inline in dropdown)
function saveNewJenisInline() {
    const nama = document.getElementById('newJenisNama').value.trim();
    
    if (!nama) {
        alert('Nama jenis aset tidak boleh kosong');
        return;
    }
    
    // Kirim AJAX request
    fetch('{{ route("master-data.jenis-aset.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ nama: nama })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Tambahkan opsi baru ke dropdown (sebelum divider)
            const menu = document.getElementById('customDropdownMenu');
            const divider = menu.querySelector('.custom-dropdown-divider');
            
            const newItem = document.createElement('div');
            newItem.className = 'custom-dropdown-item';
            newItem.id = `item-${data.data.id}`;
            newItem.onclick = function() { selectJenisAset(data.data.slug, data.data.nama); };
            newItem.innerHTML = `
                <span>${data.data.nama}</span>
                <i class="bi bi-x-circle text-danger" onclick="event.stopPropagation(); deleteJenisAset(${data.data.id}, '${data.data.nama}')"></i>
            `;
            
            // Insert before divider
            menu.insertBefore(newItem, divider);
            
            // Select the new item
            selectJenisAset(data.data.slug, data.data.nama);
            
            // Reset form
            hideAddJenisForm();
            
            alert('Jenis aset berhasil ditambahkan!');
        } else {
            alert('Gagal menambahkan jenis aset: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan jenis aset');
    });
}

// Delete jenis aset
function deleteJenisAset(id, nama) {
    if (!confirm(`Hapus jenis aset "${nama}"?\n\nJenis aset ini akan dihapus dan tidak bisa digunakan lagi.`)) {
        return;
    }
    
    fetch(`{{ url('master-data/jenis-aset') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hapus item dari dropdown
            const item = document.getElementById(`item-${id}`);
            if (item) item.remove();
            
            // Jika item yang dihapus sedang terpilih, set ke default
            const currentValue = document.getElementById('jenis_aset').value;
            if (currentValue === nama.toLowerCase().replace(/\s+/g, '-')) {
                selectJenisAset('aset-tetap', 'Aset Tetap');
            }
            
            alert('Jenis aset berhasil dihapus!');
        } else {
            alert('Gagal menghapus jenis aset: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus jenis aset');
    });
}


</script>
@endsection

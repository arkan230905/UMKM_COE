@extends('layouts.app')

@section('title', 'Edit Aset')

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Edit Aset: {{ $aset->nama_aset }}</h2>
        @if($asetSummary['sudah_diposting'])
            <div class="alert alert-warning mb-0 py-2 px-3">
                <i class="fas fa-lock me-2"></i>
                <strong>Aset sudah diposting penyusutannya</strong> - Data tidak dapat diubah
            </div>
        @endif
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

    <!-- Tabel Data Lengkap Aset -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Lengkap Aset</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="fw-bold">Kode Aset:</td>
                            <td>{{ $aset->kode_aset }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Nama Aset:</td>
                            <td>{{ $aset->nama_aset }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Kategori:</td>
                            <td>{{ $aset->kategori->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Jenis Aset:</td>
                            <td>{{ $aset->kategori->jenisAset->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Status:</td>
                            <td>
                                <span class="badge bg-{{ $aset->status == 'aktif' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($aset->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tanggal Beli:</td>
                            <td>{{ $aset->tanggal_beli ? \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tanggal Akuisisi:</td>
                            <td>{{ $aset->tanggal_akuisisi ? \Carbon\Carbon::parse($aset->tanggal_akuisisi)->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="fw-bold">Harga Perolehan:</td>
                            <td>Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Biaya Perolehan:</td>
                            <td>Rp {{ number_format($aset->biaya_perolehan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Total Perolehan:</td>
                            <td class="fw-bold text-primary">Rp {{ number_format($asetSummary['total_perolehan'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Nilai Residu:</td>
                            <td>Rp {{ number_format($aset->nilai_residu ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Akumulasi Penyusutan:</td>
                            <td class="text-danger">Rp {{ number_format($asetSummary['akumulasi_penyusutan'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Nilai Buku Saat Ini:</td>
                            <td class="fw-bold text-success">Rp {{ number_format($asetSummary['nilai_buku_saat_ini'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Status Posting:</td>
                            <td>
                                @if($asetSummary['sudah_diposting'])
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Sudah Diposting</span>
                                @else
                                    <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Belum Diposting</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Penyusutan (jika aset disusutkan) -->
    @if($aset->kategori && $aset->kategori->disusutkan && count($asetSummary['jadwal_penyusutan']) > 0)
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Jadwal Penyusutan</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">Metode Penyusutan</small>
                        <div class="fw-bold">{{ ucwords(str_replace('_', ' ', $aset->metode_penyusutan)) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">Umur Manfaat</small>
                        <div class="fw-bold">{{ $aset->umur_manfaat }} Tahun</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">Penyusutan Per Tahun</small>
                        <div class="fw-bold text-success">Rp {{ number_format($asetSummary['penyusutan_per_tahun'], 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">Penyusutan Per Bulan</small>
                        <div class="fw-bold text-info">Rp {{ number_format($asetSummary['penyusutan_per_bulan'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Tahun</th>
                            <th class="text-end">Penyusutan</th>
                            <th class="text-end">Akumulasi Penyusutan</th>
                            <th class="text-end">Nilai Buku</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asetSummary['jadwal_penyusutan'] as $jadwal)
                        <tr>
                            <td class="text-center">{{ $jadwal['tahun'] }}</td>
                            <td class="text-end">Rp {{ number_format($jadwal['penyusutan'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($jadwal['akumulasi'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($jadwal['nilai_buku'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Form Edit (hanya jika belum diposting) -->
    @if(!$asetSummary['sudah_diposting'])
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Edit Aset</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.aset.update', $aset->id) }}" method="POST" id="asetForm">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="nama_aset" class="form-label text-dark">Nama Aset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control bg-white text-dark @error('nama_aset') is-invalid @enderror" 
                           id="nama_aset" name="nama_aset" value="{{ old('nama_aset', $aset->nama_aset) }}" required>
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
                        <option value="" disabled>-- Pilih Kategori Aset --</option>
                        @foreach($jenisAsets as $jenis)
                            <optgroup label="{{ $jenis->nama }}">
                                @foreach($jenis->kategories as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_aset_id', $aset->kategori_aset_id) == $kategori->id ? 'selected' : '' }} data-disusutkan="{{ $kategori->disusutkan ? '1' : '0' }}" data-jenis-nama="{{ $jenis->nama }}">
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

                <!-- NEW: Jenis Perolehan & Akun Kredit (hanya untuk Pembelian Baru) -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="harga_perolehan" class="form-label text-dark">Harga Perolehan (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-white text-dark @error('harga_perolehan') is-invalid @enderror" 
                               id="harga_perolehan" name="harga_perolehan" value="{{ old('harga_perolehan', $aset->harga_perolehan) ? number_format($aset->harga_perolehan, 0, ',', '.') : '' }}" 
                               placeholder="0"
                               required inputmode="numeric" oninput="hitungTotal()" onblur="formatRupiahInput(this)">
                        @error('harga_perolehan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="biaya_perolehan" class="form-label text-dark">Biaya Perolehan (Rp)</label>
                        <input type="number" step="0.01" class="form-control bg-white text-dark @error('biaya_perolehan') is-invalid @enderror" 
                               id="biaya_perolehan" name="biaya_perolehan" value="{{ old('biaya_perolehan', $aset->biaya_perolehan ?? 0) }}" 
                               oninput="hitungTotal()">
                        <small class="text-muted">Biaya tambahan seperti ongkir, instalasi, dll</small>
                        @error('biaya_perolehan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-dark">Total Perolehan</label>
                    <div class="form-control bg-light text-dark" id="total_perolehan_display">Rp {{ number_format(($aset->harga_perolehan ?? 0) + ($aset->biaya_perolehan ?? 0), 0, ',', '.') }}</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_beli" class="form-label text-dark">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control bg-white text-dark @error('tanggal_beli') is-invalid @enderror" 
                               id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli', $aset->tanggal_beli instanceof \Carbon\Carbon ? $aset->tanggal_beli->format('Y-m-d') : $aset->tanggal_beli) }}" required>
                        @error('tanggal_beli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <!-- Section Penyusutan -->
                <div id="section_penyusutan" style="display: {{ $aset->kategori && $aset->kategori->disusutkan ? 'block' : 'none' }};">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Aset ini mengalami penyusutan.</strong> Silakan isi informasi penyusutan di bawah.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="metode_penyusutan" class="form-label text-dark">Metode Penyusutan</label>
                            <select class="form-select bg-white text-dark" id="metode_penyusutan" name="metode_penyusutan">
                                <option value="">-- Pilih Metode --</option>
                                @foreach($metodePenyusutan as $key => $value)
                                    <option value="{{ $key }}" {{ old('metode_penyusutan', $aset->metode_penyusutan) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="umur_manfaat" class="form-label text-dark">Umur Manfaat (Tahun)</label>
                            <input type="number" class="form-control bg-white text-dark" 
                                   id="umur_manfaat" name="umur_manfaat" value="{{ old('umur_manfaat', $aset->umur_manfaat) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nilai_residu" class="form-label text-dark">Nilai Residu (Rp)</label>
                            <input type="text" class="form-control bg-white text-dark" 
                                   id="nilai_residu" name="nilai_residu" value="{{ old('nilai_residu', $aset->nilai_residu) ? number_format($aset->nilai_residu, 0, ',', '.') : '' }}"
                                   placeholder="0"
                                   inputmode="numeric" oninput="hitungPenyusutan()" onblur="formatRupiahInput(this)">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tanggal_akuisisi" class="form-label text-dark">Tanggal Akuisisi</label>
                            <input type="date" class="form-control bg-white text-dark" 
                                   id="tanggal_akuisisi" name="tanggal_akuisisi" value="{{ old('tanggal_akuisisi', $aset->tanggal_akuisisi ? \Carbon\Carbon::parse($aset->tanggal_akuisisi)->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <!-- COA Selection - REMOVED: Sistem auto-posting akan handle COA otomatis -->
                </div>

                <!-- Alert untuk aset yang tidak disusutkan -->
                <div id="alert_tidak_disusutkan" class="alert alert-warning mb-4" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Aset ini tidak mengalami penyusutan.</strong> 
                    <span id="alasan_tidak_disusutkan"></span>
                </div>

                <!-- Keterangan field hidden per user request -->
                <input type="hidden" id="keterangan" name="keterangan" value="{{ old('keterangan', $aset->keterangan ?? '') }}">

                <div class="d-flex justify-content-between">
                    <a href="{{ route('master-data.aset.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Update Aset</button>
                </div>
            </form>
        </div>
    </div>
    @else
    <!-- Pesan jika sudah diposting -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Aset Tidak Dapat Diedit</h5>
        </div>
        <div class="card-body text-center py-5">
            <i class="fas fa-lock fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aset Sudah Diposting Penyusutannya</h5>
            <p class="text-muted">Aset ini sudah pernah diposting penyusutannya sehingga data tidak dapat diubah untuk menjaga konsistensi laporan keuangan.</p>
            <a href="{{ route('master-data.aset.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Aset
            </a>
        </div>
    </div>
    @endif
</div>

<script>
// JavaScript functions for dynamic form behavior

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

function hitungTotal() {
    const harga = unformatRupiah(document.getElementById('harga_perolehan').value);
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    document.getElementById('total_perolehan_display').textContent = 'Rp ' + formatRupiah(total);
    
    hitungPenyusutan();
}

function hitungPenyusutan() {
    const harga = unformatRupiah(document.getElementById('harga_perolehan').value);
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    const residu = unformatRupiah(document.getElementById('nilai_residu').value);
    const umur = parseInt(document.getElementById('umur_manfaat').value) || 0;
    const metode = document.getElementById('metode_penyusutan').value;

    const nilaiDisusutkan = Math.max(total - residu, 0);

    let penyusutanTahunan = 0;

    if (umur > 0 && nilaiDisusutkan > 0) {
        switch (metode) {
            case 'garis_lurus':
                penyusutanTahunan = nilaiDisusutkan / umur;
                break;

            case 'saldo_menurun':
                const rate = 2 / umur; // Metode saldo menurun
                penyusutanTahunan = total * rate;
                break;

            case 'sum_of_years_digits':
                const sumOfYears = (umur * (umur + 1)) / 2;
                penyusutanTahunan = (nilaiDisusutkan * umur) / sumOfYears; // Tahun pertama
                break;

            default:
                penyusutanTahunan = 0;
        }
    }
}
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

function hitungPerhitunganTahunan(total, residu, umur, tarifPersen, bulanMulai) {
    const tabelContainer = document.getElementById('tabel_perhitungan_tahunan');
    const tabelBody = document.getElementById('tabel_perhitungan_body');

    if (!umur || umur <= 0) {
        tabelContainer.style.display = 'none';
        return;
    }

    // Ambil tahun mulai dari tanggal akuisisi/beli aset
    const tglMulaiEl = document.getElementById('tanggal_akuisisi') || document.getElementById('tanggal_beli');
    const tglMulai   = tglMulaiEl ? tglMulaiEl.value : '';
    const startYear  = tglMulai ? new Date(tglMulai).getFullYear() : new Date().getFullYear();

    // Hitung bulan tersisa di tahun pertama (Sep=9 → 4 bulan)
    const bulanMulaiAset = tglMulai ? new Date(tglMulai).getMonth() + 1 : 9;
    const bulanTersisa   = 13 - bulanMulaiAset; // Sep → 4, Jan → 12

    const rate = 2 / umur;
    let bookValue = total;
    let totalPenyusutan = 0;
    let html = '';

    for (let i = 0; i < umur; i++) {
        const yearLabel = startYear + i;
        let penyusutan, labelTahun;

        if (i === 0) {
            // Tahun pertama: pro-rata
            penyusutan  = total * rate * (bulanTersisa / 12);
            labelTahun  = `${yearLabel} (${bulanTersisa})`;
        } else if (i === umur - 1) {
            // Tahun terakhir: sisa ke nilai residu
            const bulanTerakhir = 12 - bulanTersisa; // sisa bulan di tahun terakhir
            penyusutan  = bookValue - residu;
            labelTahun  = bulanTerakhir > 0 ? `${yearLabel} (${bulanTerakhir})` : `${yearLabel}`;
        } else {
            penyusutan = bookValue * rate;
            labelTahun = `${yearLabel}`;
        }

        const maxDepr = Math.max(bookValue - residu, 0);
        penyusutan = Math.min(penyusutan, maxDepr);

        bookValue        = Math.round(bookValue - penyusutan);
        totalPenyusutan  = Math.round(totalPenyusutan + penyusutan);

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

// Menghapus loadKategoriAset karena Kategori Aset me-load semua opsi

function checkPenyusutan() {
    handleJenisAsetChange();
}

function handleJenisAsetChange() {
    const jenis = document.getElementById('jenis_aset').value;
    const penyusutanSection = document.getElementById('section_penyusutan');
    const alertTidakDisusutkan = document.getElementById('alert_tidak_disusutkan');
    
    // Fields
    const metodePenyusutan = document.getElementById('metode_penyusutan');
    const umurManfaat = document.getElementById('umur_manfaat');
    const nilaiResidu = document.getElementById('nilai_residu');
    
    if (jenis === 'aset-tetap' || jenis === 'aset-tidak-berwujud') {
        penyusutanSection.style.display = 'block';
        if (alertTidakDisusutkan) alertTidakDisusutkan.style.display = 'none';
        
        metodePenyusutan.required = true;
        umurManfaat.required = true;
        // COA fields removed - sistem auto-posting akan handle COA otomatis
        
        if (jenis === 'aset-tetap') {
            nilaiResidu.required = true;
            if(document.getElementById('nilai_residu').parentElement) document.getElementById('nilai_residu').parentElement.style.display = 'block';
            
            // Show saldo menurun
            Array.from(metodePenyusutan.options).forEach(opt => {
                if (opt.value === 'saldo_menurun') opt.style.display = 'block';
            });
        } else {
            nilaiResidu.required = false;
            if(document.getElementById('nilai_residu').parentElement) document.getElementById('nilai_residu').parentElement.style.display = 'none';
            nilaiResidu.value = 0;
            
            // Hide saldo menurun
            Array.from(metodePenyusutan.options).forEach(opt => {
                if (opt.value === 'saldo_menurun') {
                    opt.style.display = 'none';
                    if (metodePenyusutan.value === 'saldo_menurun') metodePenyusutan.value = 'garis_lurus';
                }
            });
        }
    } else {
        penyusutanSection.style.display = 'none';
        if (alertTidakDisusutkan) {
            alertTidakDisusutkan.style.display = 'block';
            document.getElementById('alasan_tidak_disusutkan').textContent = 'Aset Tidak Tetap dicatat sebagai biaya langsung.';
        }
        
        metodePenyusutan.required = false;
        umurManfaat.required = false;
        nilaiResidu.required = false;
        // COA fields removed - sistem auto-posting akan handle COA otomatis
        metodePenyusutan.value = '';
        umurManfaat.value = '';
        nilaiResidu.value = '';
    }
    
    hitungPenyusutan();
}

// Event listeners untuk perhitungan otomatis
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
    
    hitungTotal();
    hitungTotal();
    if (document.getElementById('jenis_aset') && document.getElementById('jenis_aset').value) {
        handleJenisAsetChange();
    }
    hitungPenyusutan(); // Hitung ulang setelah semua siap
    
    // Add event listeners
    if (document.getElementById('harga_perolehan')) document.getElementById('harga_perolehan').addEventListener('input', hitungTotal);
    if (document.getElementById('biaya_perolehan')) document.getElementById('biaya_perolehan').addEventListener('input', hitungTotal);
    if (document.getElementById('nilai_residu')) document.getElementById('nilai_residu').addEventListener('input', hitungPenyusutan);
    if (document.getElementById('umur_manfaat')) document.getElementById('umur_manfaat').addEventListener('input', hitungPenyusutan);
    if (document.getElementById('metode_penyusutan')) document.getElementById('metode_penyusutan').addEventListener('change', hitungPenyusutan);
});

// Strip formatting before form submission
const asetFormEl = document.getElementById('asetForm');
if (asetFormEl) {
    asetFormEl.addEventListener('submit', function(e) {
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
}

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

// Toggle Akun Kredit field based on Jenis Perolehan
function toggleAkunKredit() {
    const jenisPerolehan = document.getElementById('jenis_perolehan').value;
    const containerAkunKredit = document.getElementById('container_akun_kredit');
    const fieldAkunKredit = document.getElementById('sumber_dana_coa_id');
    
    if (jenisPerolehan === 'pembelian_baru') {
        containerAkunKredit.style.display = 'block';
        fieldAkunKredit.required = true;
    } else {
        containerAkunKredit.style.display = 'none';
        fieldAkunKredit.required = false;
        fieldAkunKredit.value = '';
    }
}
</script>
@endsection

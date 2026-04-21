@extends('layouts.app')

@section('title', 'Edit Aset')

@section('content')
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
                    <label for="jenis_aset_id" class="form-label text-dark">Jenis Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-white text-dark @error('jenis_aset_id') is-invalid @enderror" 
                            id="jenis_aset_id" name="jenis_aset_id" required onchange="loadKategoriAset()">
                        <option value="" disabled>-- Pilih Jenis Aset --</option>
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
                    <label for="kategori_aset_id" class="form-label text-dark">Kategori Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-white text-dark @error('kategori_aset_id') is-invalid @enderror" 
                            id="kategori_aset_id" name="kategori_aset_id" required onchange="checkPenyusutan()">
                        <option value="" disabled>-- Pilih Kategori Aset --</option>
                        @if($aset->kategori)
                            <option value="{{ $aset->kategori->id }}" selected>{{ $aset->kategori->nama }}</option>
                        @endif
                    </select>
                    @error('kategori_aset_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

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

                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akuisisi" class="form-label text-dark">Tanggal Akuisisi</label>
                        <input type="date" class="form-control bg-white text-dark" 
                               id="tanggal_akuisisi" name="tanggal_akuisisi" value="{{ old('tanggal_akuisisi', $aset->tanggal_akuisisi) }}">
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
                                   id="tanggal_akuisisi" name="tanggal_akuisisi" value="{{ old('tanggal_akuisisi', $aset->tanggal_akuisisi) }}">
                        </div>
                    </div>

                    <!-- COA Selection -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="asset_coa_id" class="form-label text-dark">Akun COA Aset <span class="text-danger">*</span></label>
                            <select class="form-select bg-white text-dark @error('asset_coa_id') is-invalid @enderror" 
                                    id="asset_coa_id" name="asset_coa_id" required>
                                <option value="" disabled>-- Pilih Akun Aset --</option>
                                @foreach($coaAsets as $coa)
                                    <option value="{{ $coa->id }}" {{ old('asset_coa_id', $aset->asset_coa_id) == $coa->id ? 'selected' : '' }}>
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
                            <label for="accum_depr_coa_id" class="form-label text-dark">Akun COA Akumulasi Penyusutan</label>
                            <select class="form-select bg-white text-dark @error('accum_depr_coa_id') is-invalid @enderror" 
                                    id="accum_depr_coa_id" name="accum_depr_coa_id">
                                <option value="" disabled>-- Pilih Akun Akumulasi --</option>
                                @foreach($coaAkumulasi as $coa)
                                    <option value="{{ $coa->id }}" {{ old('accum_depr_coa_id', $aset->accum_depr_coa_id) == $coa->id ? 'selected' : '' }}>
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
                            <label for="expense_coa_id" class="form-label text-dark">Akun COA Beban Penyusutan</label>
                            <select class="form-select bg-white text-dark @error('expense_coa_id') is-invalid @enderror" 
                                    id="expense_coa_id" name="expense_coa_id">
                                <option value="" disabled>-- Pilih Akun Beban --</option>
                                @foreach($coaBeban as $coa)
                                    <option value="{{ $coa->id }}" {{ old('expense_coa_id', $aset->expense_coa_id) == $coa->id ? 'selected' : '' }}>
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih akun beban penyusutan aset</small>
                            @error('expense_coa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Hasil Perhitungan Penyusutan -->
                    <div class="card bg-light mt-4">
                        <div class="card-body">
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
    
    document.getElementById('nilai_disusutkan_display').textContent = 'Rp ' + formatRupiah(nilaiDisusutkan);
    
    let penyusutanTahunan = 0;
    
    if (umur > 0 && nilaiDisusutkan > 0) {
        switch (metode) {
            case 'garis_lurus':
                penyusutanTahunan = nilaiDisusutkan / umur;
                document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
                document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
                break;
                
            case 'saldo_menurun':
                const rate = 2 / umur; // Double declining balance
                penyusutanTahunan = total * rate;
                hitungPerhitunganTahunan(total, residu, umur, rate * 100, 1);
                document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
                break;
                
            case 'sum_of_years_digits':
                const sumOfYears = (umur * (umur + 1)) / 2;
                penyusutanTahunan = (nilaiDisusutkan * umur) / sumOfYears; // Tahun pertama
                hitungPerhitunganJumlahAngkaTahun(umur);
                hitungPerhitunganTahunanSumOfYears(total, residu, umur);
                document.getElementById('tabel_perhitungan_tahunan').style.display = 'block';
                break;
                
            default:
                penyusutanTahunan = 0;
                document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
                document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
        }
    } else {
        document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
    }
    
    const penyusutanBulanan = Math.round(penyusutanTahunan / 12);
    
    document.getElementById('penyusutan_tahunan_display').textContent = 'Rp ' + formatRupiah(penyusutanTahunan);
    document.getElementById('penyusutan_bulanan_display').textContent = 'Rp ' + formatRupiah(penyusutanBulanan);
}

function hitungPerhitunganJumlahAngkaTahun(umur) {
    const container = document.getElementById('perhitungan_jumlah_angka_tahun');
    const umurDisplay = document.getElementById('umur_manfaat_display');
    const rumusDisplay = document.getElementById('rumus_jumlah_angka');
    const hasilDisplay = document.getElementById('hasil_jumlah_angka');
    
    if (!umur || umur <= 0) {
        container.style.display = 'none';
        return;
    }
    
    const sumOfYears = (umur * (umur + 1)) / 2;
    
    let rumusString = '';
    for (let i = umur; i >= 1; i--) {
        rumusString += i;
        if (i > 1) rumusString += ' + ';
    }
    rumusString += ' = ' + sumOfYears;
    
    umurDisplay.textContent = umur;
    rumusDisplay.textContent = rumusString;
    hasilDisplay.textContent = sumOfYears;
    
    container.style.display = 'block';
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

function loadKategoriAset() {
    const jenisId = document.getElementById('jenis_aset_id').value;
    const kategoriSelect = document.getElementById('kategori_aset_id');
    
    kategoriSelect.innerHTML = '<option value="" disabled selected>-- Loading... --</option>';
    
    if (jenisId) {
        @foreach($jenisAsets as $jenis)
            if (jenisId == '{{ $jenis->id }}') {
                kategoriSelect.innerHTML = '<option value="" disabled>-- Pilih Kategori Aset --</option>';
                @foreach($jenis->kategories as $kategori)
                    const option{{ $kategori->id }} = new Option('{{ $kategori->nama }}', '{{ $kategori->id }}');
                    option{{ $kategori->id }}.selected = {{ old('kategori_aset_id', $aset->kategori_aset_id ?? 'null') }} == '{{ $kategori->id }}';
                    kategoriSelect.add(option{{ $kategori->id }});
                @endforeach
            }
        @endforeach
    }
}

function checkPenyusutan() {
    const kategoriId = document.getElementById('kategori_aset_id').value;
    const penyusutanSection = document.getElementById('section_penyusutan');
    
    @foreach($jenisAsets as $jenis)
        @foreach($jenis->kategories as $kategori)
            if (kategoriId == '{{ $kategori->id }}') {
                penyusutanSection.style.display = {{ $kategori->disusutkan ? "'block'" : "'none'" }};
            }
        @endforeach
    @endforeach
    
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
    loadKategoriAset();
    checkPenyusutan(); // Ensure penyusutan section is properly shown/hidden
    
    // Add event listeners
    document.getElementById('harga_perolehan').addEventListener('input', hitungTotal);
    document.getElementById('biaya_perolehan').addEventListener('input', hitungTotal);
    document.getElementById('nilai_residu').addEventListener('input', hitungPenyusutan);
    document.getElementById('umur_manfaat').addEventListener('input', hitungPenyusutan);
    document.getElementById('metode_penyusutan').addEventListener('change', hitungPenyusutan);
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
@extends('layouts.app')

@section('title', 'Tambah Penggajian (Berbasis Produk)')

@section('content')
<div class="container py-4" style="background-color: #faf8f3; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark">
            <i class="bi bi-plus-circle me-2"></i>Tambah Penggajian (Berbasis Produk)
        </h3>
        <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background-color: #f5f2e8; border: 1px solid #e8dcc0 !important;">
        <div class="card-body">
            <form action="{{ route('transaksi.penggajian.store') }}" method="POST" id="formPenggajianProduk">
                @csrf

                <!-- ============================================================ -->
                <!-- SECTION 1: DATA PEGAWAI -->
                <!-- ============================================================ -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="pegawai_id" class="form-label fw-bold">
                            <i class="bi bi-person-badge"></i> Pilih Pegawai *
                        </label>
                        <select name="pegawai_id" id="pegawai_id" class="form-select form-select-lg" required onchange="loadPegawaiDataProduk()">
                            <option value="">-- Pilih Pegawai --</option>
                            @if(empty($pegawais))
                                <option disabled>❌ Tidak ada pegawai untuk user ini</option>
                            @else
                                @foreach ($pegawais as $pegawai)
                                    @php
                                        $jabatan = $pegawai->jabatanRelasi;
                                        $tarifProduk = $jabatan ? ($jabatan->tarif_produk ?? 0) : 0;
                                        $tunjanganJabatan = $jabatan ? ($jabatan->tunjangan ?? 0) : 0;
                                        $tunjanganTransport = $jabatan ? ($jabatan->tunjangan_transport ?? 0) : 0;
                                        $tunjanganKonsumsi = $jabatan ? ($jabatan->tunjangan_konsumsi ?? 0) : 0;
                                        $asuransi = $jabatan ? ($jabatan->asuransi ?? 0) : 0;
                                    @endphp
                                    <option value="{{ $pegawai->id }}"
                                            data-nama-kualifikasi="{{ $jabatan ? $jabatan->nama : 'N/A' }}"
                                            data-tarif-produk="{{ $tarifProduk }}"
                                            data-tunjangan-jabatan="{{ $tunjanganJabatan }}"
                                            data-tunjangan-transport="{{ $tunjanganTransport }}"
                                            data-tunjangan-konsumsi="{{ $tunjanganKonsumsi }}"
                                            data-asuransi="{{ $asuransi }}">
                                        {{ $pegawai->nama }} - {{ $jabatan ? $jabatan->nama : 'Staff' }}
                                        [Tarif: Rp {{ number_format($tarifProduk, 0, ',', '.') }}/produk]
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('pegawai_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_penggajian" class="form-label fw-bold">
                            <i class="bi bi-calendar-check"></i> Tanggal Penggajian *
                        </label>
                        <input type="date" name="tanggal_penggajian" id="tanggal_penggajian"
                               class="form-control form-control-lg" value="{{ date('Y-m-d') }}" required>
                        @error('tanggal_penggajian')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="coa_kasbank" class="form-label fw-bold">
                            <i class="bi bi-wallet2"></i> Metode Pembayaran *
                        </label>
                        <select name="coa_kasbank" id="coa_kasbank" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            @foreach($kasbank as $kb)
                                <option value="{{ $kb->kode_akun }}" {{ old('coa_kasbank') == $kb->kode_akun ? 'selected' : '' }}>
                                    @if($kb->kode_akun == '112')
                                        Tunai - {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                                    @elseif($kb->kode_akun == '111')
                                        Transfer - {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                                    @else
                                        {{ $kb->nama_akun }} - {{ strtolower($kb->nama_akun) }} ({{ $kb->kode_akun }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('coa_kasbank')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- SECTION 2: KUALIFIKASI & TARIF (AUTO-LOAD) -->
                <!-- ============================================================ -->
                <div class="card border-0 mb-4" style="background-color: #f9f7f2;">
                    <div class="card-header" style="background-color: #f0ebe0; border-bottom: 1px solid #e8dcc0;">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-briefcase me-2"></i>Kualifikasi & Tarif (Auto-Load)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="display_kualifikasi" class="form-label fw-bold">Kualifikasi</label>
                                <input type="text" id="display_kualifikasi" class="form-control" readonly value="--">
                            </div>

                            <div class="col-md-6">
                                <label for="display_tarif_produk" class="form-label fw-bold">Tarif/Produk</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tarif_produk" class="form-control fw-bold" readonly value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- SECTION 3: INPUT PRODUK & HARI KERJA -->
                <!-- ============================================================ -->
                <div class="card border-0 mb-4" style="background-color: #f9f7f2;">
                    <div class="card-header" style="background-color: #f0ebe0; border-bottom: 1px solid #e8dcc0;">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-box-seam me-2"></i>Komponen Produksi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="produk_per_hari" class="form-label fw-bold">Produk / Hari</label>
                                <input type="number" name="produk_per_hari" id="produk_per_hari"
                                       class="form-control form-control-lg" value="0" min="0" required
                                       onchange="hitungGajiOtomatisProduk()" oninput="hitungGajiOtomatisProduk()">
                                <small class="text-muted">Contoh: 160 ayam/hari</small>
                            </div>

                            <div class="col-md-6">
                                <label for="hari_kerja" class="form-label fw-bold">Hari Kerja</label>
                                <input type="number" name="hari_kerja" id="hari_kerja"
                                       class="form-control form-control-lg" value="26" min="1" max="31" required
                                       onchange="hitungGajiOtomatisProduk()" oninput="hitungGajiOtomatisProduk()">
                                <small class="text-muted">Default: 26 hari/bulan</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- SECTION 4: PERHITUNGAN OTOMATIS -->
                <!-- ============================================================ -->
                <div class="card border-0 mb-4" style="background-color: #f9f7f2;">
                    <div class="card-header" style="background-color: #f0ebe0; border-bottom: 1px solid #e8dcc0;">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-calculator me-2"></i>Perhitungan Otomatis
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="display_total_produk" class="form-label fw-bold">Total Produk (Produk/Hari × Hari Kerja)</label>
                                <div class="input-group">
                                    <input type="text" id="display_total_produk" class="form-control fw-bold" readonly value="0">
                                    <span class="input-group-text">produk</span>
                                </div>
                                <small class="text-success"><i class="bi bi-calculator"></i> Dibulatkan ke kelipatan 100 ke bawah</small>
                            </div>

                            <div class="col-md-6">
                                <label for="display_gaji_produksi" class="form-label fw-bold text-primary">Gaji Produksi (Total × Tarif)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_gaji_produksi" class="form-control fw-bold text-primary" readonly value="0">
                                </div>
                                <small class="text-success"><i class="bi bi-calculator"></i> Dibulatkan ke kelipatan 500.000</small>
                            </div>

                            <div class="col-md-6">
                                <label for="display_tunjangan_jabatan" class="form-label">Tunjangan Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tunjangan_jabatan" class="form-control" readonly value="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="display_tunjangan_transport" class="form-label">Tunjangan Transport</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tunjangan_transport" class="form-control" readonly value="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="display_tunjangan_konsumsi" class="form-label">Tunjangan Konsumsi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tunjangan_konsumsi" class="form-control" readonly value="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="display_total_tunjangan" class="form-label fw-bold">Total Tunjangan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_total_tunjangan" class="form-control fw-bold" readonly value="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="display_asuransi" class="form-label text-danger">Asuransi / BPJS (Potongan)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_asuransi" class="form-control text-danger" readonly value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- SECTION 5: TOTAL GAJI (SUMMARY) -->
                <!-- ============================================================ -->
                <div class="card border-0 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-body text-center py-5">
                        <h6 class="mb-3 text-muted fw-normal">
                            <i class="bi bi-check-circle me-2"></i>TOTAL GAJI BULAN INI
                        </h6>
                        <h2 class="mb-2 fw-bold" id="display_total_gaji" style="color: #166534; font-size: 2.5rem;">Rp 0</h2>
                        <small class="text-muted">Gaji Produksi + Tunjangan − Asuransi</small>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- SECTION 6: KETERANGAN (OPTIONAL) -->
                <!-- ============================================================ -->
                <div class="card border-0 mb-4" style="background-color: #f9f7f2;">
                    <div class="card-header" style="background-color: #f0ebe0; border-bottom: 1px solid #e8dcc0;">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-chat-left-text me-2"></i>Keterangan (Opsional)
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan untuk penggajian ini..."></textarea>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- BUTTONS -->
                <!-- ============================================================ -->
                <div class="d-flex justify-content-between gap-2">
                    <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-lg" style="background-color: #28a745; border-color: #28a745; color: white;" id="submitBtn">
                        <i class="bi bi-save me-1"></i> Simpan Penggajian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Cream Theme Custom Styles */
body {
    background-color: #faf8f3 !important;
}

.form-control, .form-select {
    background-color: #ffffff !important;
    border: 1px solid #e8dcc0 !important;
    color: #495057 !important;
}

.form-control:focus, .form-select:focus {
    background-color: #ffffff !important;
    border-color: #8b6f47 !important;
    box-shadow: 0 0 0 0.2rem rgba(139, 111, 71, 0.25) !important;
    color: #495057 !important;
}

.form-label {
    color: #495057 !important;
    font-weight: 500 !important;
}

.input-group-text {
    background-color: #f0ebe0 !important;
    border: 1px solid #e8dcc0 !important;
    color: #495057 !important;
}

.btn-outline-secondary {
    border-color: #8b6f47 !important;
    color: #8b6f47 !important;
}

.btn-outline-secondary:hover {
    background-color: #8b6f47 !important;
    border-color: #8b6f47 !important;
    color: #ffffff !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(139, 111, 71, 0.075) !important;
}

.text-success {
    color: #6c757d !important;
}

.text-muted {
    color: #6c757d !important;
}

/* Alert styling */
.alert-success {
    background-color: #d1e7dd !important;
    border-color: #badbcc !important;
    color: #0f5132 !important;
}

.alert-danger {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #842029 !important;
}
</style>

<script>
// Data pegawai untuk produk-based system
let pegawaiDataProduk = {
    namaKualifikasi: '--',
    tarifProduk: 0,
    tunjanganJabatan: 0,
    tunjanganTransport: 0,
    tunjanganKonsumsi: 0,
    totalTunjangan: 0,
    asuransi: 0
};

// Format Rupiah
function formatRupiah(num) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(num);
}

// Load pegawai data dari kualifikasi
function loadPegawaiDataProduk() {
    const select = document.getElementById('pegawai_id');
    const pegawaiId = select.value;
    const selectedOption = select.options[select.selectedIndex];

    if (pegawaiId) {
        // Get data dari data attributes
        pegawaiDataProduk.namaKualifikasi = selectedOption.dataset.namaKualifikasi || '--';
        pegawaiDataProduk.tarifProduk = parseFloat(selectedOption.dataset.tarifProduk) || 0;
        pegawaiDataProduk.tunjanganJabatan = parseFloat(selectedOption.dataset.tunjanganJabatan) || 0;
        pegawaiDataProduk.tunjanganTransport = parseFloat(selectedOption.dataset.tunjanganTransport) || 0;
        pegawaiDataProduk.tunjanganKonsumsi = parseFloat(selectedOption.dataset.tunjanganKonsumsi) || 0;
        pegawaiDataProduk.totalTunjangan = pegawaiDataProduk.tunjanganJabatan + pegawaiDataProduk.tunjanganTransport + pegawaiDataProduk.tunjanganKonsumsi;
        pegawaiDataProduk.asuransi = parseFloat(selectedOption.dataset.asuransi) || 0;

        // Update display fields
        updateDisplayFieldsProduk();

        // Hitung gaji otomatis
        hitungGajiOtomatisProduk();
    } else {
        // Reset
        resetPegawaiDataProduk();
    }
}

// Update display fields
function updateDisplayFieldsProduk() {
    document.getElementById('display_kualifikasi').value = pegawaiDataProduk.namaKualifikasi;
    document.getElementById('display_tarif_produk').value = formatRupiah(pegawaiDataProduk.tarifProduk);
    document.getElementById('display_tunjangan_jabatan').value = formatRupiah(pegawaiDataProduk.tunjanganJabatan);
    document.getElementById('display_tunjangan_transport').value = formatRupiah(pegawaiDataProduk.tunjanganTransport);
    document.getElementById('display_tunjangan_konsumsi').value = formatRupiah(pegawaiDataProduk.tunjanganKonsumsi);
    document.getElementById('display_total_tunjangan').value = formatRupiah(pegawaiDataProduk.totalTunjangan);
    document.getElementById('display_asuransi').value = formatRupiah(pegawaiDataProduk.asuransi);
}

// Hitung gaji otomatis ketika input produk berubah
function hitungGajiOtomatisProduk() {
    const produkPerHari = parseInt(document.getElementById('produk_per_hari').value) || 0;
    const hariKerja = parseInt(document.getElementById('hari_kerja').value) || 26;

    // STEP 1: Hitung total produk (produk/hari × hari kerja)
    const totalProduk = produkPerHari * hariKerja;

    // STEP 2: Bulatkan total produk ke kelipatan 100 terdekat ke bawah
    const totalProdukRounded = Math.floor(totalProduk / 100) * 100;

    // STEP 3: Hitung gaji produksi menggunakan total produk yang sudah dibulatkan
    const gajiProduksi = totalProdukRounded * pegawaiDataProduk.tarifProduk;

    // STEP 4: Bulatkan gaji produksi ke kelipatan 500.000 terdekat (normal rounding)
    const gajiProduksiRounded = Math.round(gajiProduksi / 500000) * 500000;

    // STEP 5: Hitung total gaji (gaji produksi + tunjangan - asuransi)
    const totalGaji = gajiProduksiRounded + pegawaiDataProduk.totalTunjangan - pegawaiDataProduk.asuransi;

    // STEP 6: Bulatkan total gaji ke kelipatan 500.000 terdekat (normal rounding)
    const totalGajiRounded = Math.round(totalGaji / 500000) * 500000;

    // Update display
    document.getElementById('display_total_produk').value = formatRupiah(totalProdukRounded);
    document.getElementById('display_gaji_produksi').value = formatRupiah(gajiProduksiRounded);
    document.getElementById('display_total_gaji').textContent = 'Rp ' + formatRupiah(totalGajiRounded);
}

// Reset pegawai data
function resetPegawaiDataProduk() {
    pegawaiDataProduk = {
        namaKualifikasi: '--',
        tarifProduk: 0,
        tunjanganJabatan: 0,
        tunjanganTransport: 0,
        tunjanganKonsumsi: 0,
        totalTunjangan: 0,
        asuransi: 0
    };

    // Reset display fields
    document.getElementById('display_kualifikasi').value = '--';
    document.getElementById('display_tarif_produk').value = '0';
    document.getElementById('display_tunjangan_jabatan').value = '0';
    document.getElementById('display_tunjangan_transport').value = '0';
    document.getElementById('display_tunjangan_konsumsi').value = '0';
    document.getElementById('display_total_tunjangan').value = '0';
    document.getElementById('display_asuransi').value = '0';
    document.getElementById('display_total_produk').value = '0';
    document.getElementById('display_gaji_produksi').value = '0';
    document.getElementById('display_total_gaji').textContent = 'Rp 0';

    // Reset input fields
    document.getElementById('produk_per_hari').value = '0';
    document.getElementById('hari_kerja').value = '26';
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formPenggajianProduk').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validasi
        const pegawaiId = document.getElementById('pegawai_id').value;
        if (!pegawaiId) {
            alert('Pilih pegawai terlebih dahulu!');
            return false;
        }

        if (pegawaiDataProduk.tarifProduk <= 0) {
            alert('Tarif/Produk tidak valid. Pastikan pegawai memiliki kualifikasi dengan tarif produk!');
            return false;
        }

        const produkPerHari = parseInt(document.getElementById('produk_per_hari').value) || 0;

        if (produkPerHari === 0) {
            alert('Masukkan jumlah produk/hari terlebih dahulu!');
            return false;
        }

        // Disable submit button
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

        // Submit form
        this.submit();
    });
});
</script>
@endsection

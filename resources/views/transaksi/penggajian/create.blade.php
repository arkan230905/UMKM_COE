@extends('layouts.app')

@section('title', 'Tambah Penggajian')

@section('content')
<div class="container py-4" style="background-color: #f4f6f8; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark">
            <i class="bi bi-plus-circle me-2"></i>Tambah Penggajian
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

    <form action="{{ route('transaksi.penggajian.store') }}" method="POST" id="formPenggajian">
        @csrf

        <!-- Hidden fields untuk data produk yang akan dikirim ke backend -->
        <input type="hidden" name="produk_hari_1_5" id="hidden_produk_hari_1_5" value="0">
        <input type="hidden" name="produk_hari_6_10" id="hidden_produk_hari_6_10" value="0">
        <input type="hidden" name="produk_hari_11_20" id="hidden_produk_hari_11_20" value="0">
        <input type="hidden" name="produk_hari_21_30" id="hidden_produk_hari_21_30" value="0">

        <!-- ============================================================ -->
        <!-- SECTION 1: DATA PEGAWAI -->
        <!-- ============================================================ -->
        <div class="card border-0 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0;">
                <h5 class="mb-0 text-dark" style="font-size: 0.95rem; font-weight: 600;">
                    <i class="bi bi-person-badge me-2"></i>Data Pegawai
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="pegawai_id" class="form-label fw-bold">Pilih Pegawai</label>
                    <select name="pegawai_id" id="pegawai_id" class="form-select" required onchange="loadPegawaiData()">
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
                                        data-tarif-produk="{{ $tarifProduk }}"
                                        data-tunjangan-jabatan="{{ $tunjanganJabatan }}"
                                        data-tunjangan-transport="{{ $tunjanganTransport }}"
                                        data-tunjangan-konsumsi="{{ $tunjanganKonsumsi }}"
                                        data-asuransi="{{ $asuransi }}">
                                    {{ $pegawai->nama }} - {{ $jabatan ? $jabatan->nama : 'Staff' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('pegawai_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="tanggal_penggajian" class="form-label fw-bold">Tanggal Penggajian</label>
                        <input type="date" name="tanggal_penggajian" id="tanggal_penggajian"
                               class="form-control" value="{{ date('Y-m-d') }}" required>
                        @error('tanggal_penggajian')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="coa_kasbank" class="form-label fw-bold">Metode Pembayaran</label>
                        <select name="coa_kasbank" id="coa_kasbank" class="form-select" required>
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            @foreach($kasbank as $kb)
                                <option value="{{ $kb->kode_akun }}" {{ old('coa_kasbank') == $kb->kode_akun ? 'selected' : '' }}>
                                    @if($kb->kode_akun == '112')
                                        Tunai
                                    @elseif($kb->kode_akun == '111')
                                        Transfer Bank
                                    @else
                                        {{ $kb->nama_akun }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('coa_kasbank')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 2: KOMPONEN PRODUKSI -->
        <!-- ============================================================ -->
        <div class="card border-0 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0;">
                <h5 class="mb-0 text-dark" style="font-size: 0.95rem; font-weight: 600;">
                    <i class="bi bi-box-seam me-2"></i>Komponen Produksi
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="produk_per_hari" class="form-label fw-bold">Produk / Hari</label>
                        <input type="number" name="produk_per_hari" id="produk_per_hari"
                               class="form-control" value="0" min="0" required
                               onchange="hitungOtomatis()" oninput="hitungOtomatis()">
                        <small class="text-muted">Contoh: 160 ayam/hari</small>
                    </div>

                    <div class="col-md-6">
                        <label for="hari_kerja" class="form-label fw-bold">Hari Kerja</label>
                        <div class="input-group">
                            <input type="number" name="hari_kerja" id="hari_kerja"
                                   class="form-control" value="26" min="1" max="31" required
                                   onchange="hitungOtomatis()" oninput="hitungOtomatis()">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">hari</span>
                        </div>
                        <small class="text-muted">Default: 26 hari/bulan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 3: PERHITUNGAN OTOMATIS -->
        <!-- ============================================================ -->
        <div class="card border-0 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0;">
                <h5 class="mb-0 text-dark" style="font-size: 0.95rem; font-weight: 600;">
                    <i class="bi bi-calculator me-2"></i>Perhitungan Otomatis
                </h5>
            </div>
            <div class="card-body">
                <div style="background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px;">
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 0.9rem; color: #6b7280;">Tarif / Produk</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">Rp</span>
                            <input type="text" id="display_tarif_produk" class="form-control" readonly value="0" style="background-color: #ffffff; border: 1px solid #d1d5db;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size: 0.9rem; color: #6b7280;">Total Produk (produk/hari × hari kerja)</label>
                        <div class="input-group">
                            <input type="text" id="display_total_produk" class="form-control" readonly value="0" style="background-color: #ffffff; border: 1px solid #d1d5db;">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">produk</span>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Gaji Produksi (total × tarif)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">Rp</span>
                            <input type="text" id="display_gaji_produksi" class="form-control fw-bold" readonly value="0" 
                                   style="background-color: #ffffff; border: 1px solid #d1d5db; color: #166534; font-size: 1.1rem;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 4: TUNJANGAN & ASURANSI -->
        <!-- ============================================================ -->
        <div class="card border-0 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0;">
                <h5 class="mb-0 text-dark" style="font-size: 0.95rem; font-weight: 600;">
                    <i class="bi bi-gift me-2"></i>Tunjangan & Asuransi
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 0.9rem;">Tunj. Jabatan</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">Rp</span>
                            <input type="text" id="display_tunjangan_jabatan" class="form-control" readonly value="0" style="background-color: #ffffff; border: 1px solid #d1d5db;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 0.9rem;">Tunj. Transport</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">Rp</span>
                            <input type="text" id="display_tunjangan_transport" class="form-control" readonly value="0" style="background-color: #ffffff; border: 1px solid #d1d5db;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 0.9rem;">Tunj. Konsumsi</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">Rp</span>
                            <input type="text" id="display_tunjangan_konsumsi" class="form-control" readonly value="0" style="background-color: #ffffff; border: 1px solid #d1d5db;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 0.9rem; color: #dc2626;">Asuransi / BPJS</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #f8f9fa; border: 1px solid #d1d5db;">Rp</span>
                            <input type="text" id="display_asuransi" class="form-control" readonly value="0" 
                                   style="background-color: #ffffff; border: 1px solid #d1d5db; color: #dc2626;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 5: TOTAL GAJI -->
        <!-- ============================================================ -->
        <div class="card border-0 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="card-body text-center py-4">
                <p class="mb-2" style="font-size: 0.85rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Total Gaji Bulan Ini</p>
                <h2 class="mb-2 fw-bold" id="display_total_gaji" style="color: #166534; font-size: 2.2rem;">Rp 0</h2>
                <p style="font-size: 0.85rem; color: #6b7280;">Gaji Produksi + Tunjangan − Asuransi</p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 6: PEMBAYARAN & KETERANGAN -->
        <!-- ============================================================ -->
        <div class="card border-0 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0;">
                <h5 class="mb-0 text-dark" style="font-size: 0.95rem; font-weight: 600;">
                    <i class="bi bi-chat-left-text me-2"></i>Pembayaran & Keterangan
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="metode_pembayaran" class="form-label fw-bold">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                        <option value="transfer_bank">Transfer Bank</option>
                        <option value="tunai">Tunai</option>
                        <option value="cek">Cek</option>
                    </select>
                </div>

                <div>
                    <label for="keterangan" class="form-label fw-bold">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3" 
                              placeholder="Catatan tambahan untuk penggajian ini..."></textarea>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TOMBOL BAWAH -->
        <!-- ============================================================ -->
        <div class="row g-2 mb-4">
            <div class="col-md-6">
                <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-outline-secondary w-100" 
                   style="border: 1px solid #d1d5db; color: #6b7280; background-color: #ffffff;">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </a>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn w-100" style="background-color: #5c3d2e; border-color: #5c3d2e; color: #ffffff; font-weight: 600;" id="submitBtn">
                    <i class="bi bi-save me-1"></i> Simpan Penggajian
                </button>
            </div>
        </div>
    </form>
</div>

<style>
/* Form styling */
.form-control, .form-select {
    background-color: #ffffff !important;
    border: 1px solid #d1d5db !important;
    color: #495057 !important;
    border-radius: 5px !important;
}

.form-control:focus, .form-select:focus {
    background-color: #ffffff !important;
    border-color: #5c3d2e !important;
    box-shadow: 0 0 0 0.2rem rgba(92, 61, 46, 0.15) !important;
    color: #495057 !important;
}

.form-label {
    color: #374151 !important;
    font-weight: 500 !important;
    font-size: 0.95rem !important;
}

.input-group-text {
    background-color: #f8f9fa !important;
    border: 1px solid #d1d5db !important;
    color: #6b7280 !important;
}

.btn-outline-secondary {
    border-color: #d1d5db !important;
    color: #6b7280 !important;
}

.btn-outline-secondary:hover {
    background-color: #f3f4f6 !important;
    border-color: #d1d5db !important;
    color: #374151 !important;
}

.card {
    box-shadow: none !important;
}

.text-muted {
    color: #9ca3af !important;
    font-size: 0.85rem !important;
}

.alert-success {
    background-color: #d1fae5 !important;
    border-color: #a7f3d0 !important;
    color: #065f46 !important;
}

.alert-danger {
    background-color: #fee2e2 !important;
    border-color: #fecaca !important;
    color: #991b1b !important;
}
</style>

<script>
// Data pegawai
let pegawaiData = {
    tarifProduk: 0,
    tunjanganJabatan: 0,
    tunjanganTransport: 0,
    tunjanganKonsumsi: 0,
    asuransi: 0
};

// Format Rupiah
function formatRupiah(num) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(num);
}

// Load pegawai data
function loadPegawaiData() {
    const select = document.getElementById('pegawai_id');
    const pegawaiId = select.value;
    const selectedOption = select.options[select.selectedIndex];

    if (pegawaiId) {
        pegawaiData.tarifProduk = parseFloat(selectedOption.dataset.tarifProduk) || 0;
        pegawaiData.tunjanganJabatan = parseFloat(selectedOption.dataset.tunjanganJabatan) || 0;
        pegawaiData.tunjanganTransport = parseFloat(selectedOption.dataset.tunjanganTransport) || 0;
        pegawaiData.tunjanganKonsumsi = parseFloat(selectedOption.dataset.tunjanganKonsumsi) || 0;
        pegawaiData.asuransi = parseFloat(selectedOption.dataset.asuransi) || 0;

        updateDisplayFields();
        hitungOtomatis();
    } else {
        resetPegawaiData();
    }
}

// Update display fields
function updateDisplayFields() {
    document.getElementById('display_tarif_produk').value = formatRupiah(pegawaiData.tarifProduk);
    document.getElementById('display_tunjangan_jabatan').value = formatRupiah(pegawaiData.tunjanganJabatan);
    document.getElementById('display_tunjangan_transport').value = formatRupiah(pegawaiData.tunjanganTransport);
    document.getElementById('display_tunjangan_konsumsi').value = formatRupiah(pegawaiData.tunjanganKonsumsi);
    document.getElementById('display_asuransi').value = formatRupiah(pegawaiData.asuransi);
}

// Hitung otomatis
function hitungOtomatis() {
    const produkPerHari = parseInt(document.getElementById('produk_per_hari').value) || 0;
    const hariKerja = parseInt(document.getElementById('hari_kerja').value) || 26;

    const totalProduk = produkPerHari * hariKerja;
    const gajiProduksi = totalProduk * pegawaiData.tarifProduk;
    const totalTunjangan = pegawaiData.tunjanganJabatan + pegawaiData.tunjanganTransport + pegawaiData.tunjanganKonsumsi;
    const totalGaji = gajiProduksi + totalTunjangan - pegawaiData.asuransi;

    // Bulatkan total gaji ke kelipatan 1.000 terdekat
    const totalGajiRounded = Math.round(totalGaji / 1000) * 1000;

    document.getElementById('display_total_produk').value = formatRupiah(totalProduk);
    document.getElementById('display_gaji_produksi').value = formatRupiah(gajiProduksi);
    document.getElementById('display_total_gaji').textContent = 'Rp ' + formatRupiah(totalGajiRounded);
}

// Reset pegawai data
function resetPegawaiData() {
    pegawaiData = {
        tarifProduk: 0,
        tunjanganJabatan: 0,
        tunjanganTransport: 0,
        tunjanganKonsumsi: 0,
        asuransi: 0
    };

    document.getElementById('display_tarif_produk').value = '0';
    document.getElementById('display_tunjangan_jabatan').value = '0';
    document.getElementById('display_tunjangan_transport').value = '0';
    document.getElementById('display_tunjangan_konsumsi').value = '0';
    document.getElementById('display_asuransi').value = '0';
    document.getElementById('display_total_produk').value = '0';
    document.getElementById('display_gaji_produksi').value = '0';
    document.getElementById('display_total_gaji').textContent = 'Rp 0';

    document.getElementById('produk_per_hari').value = '0';
    document.getElementById('hari_kerja').value = '26';
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formPenggajian').addEventListener('submit', function(e) {
        const pegawaiId = document.getElementById('pegawai_id').value;
        if (!pegawaiId) {
            alert('Pilih pegawai terlebih dahulu!');
            e.preventDefault();
            return false;
        }

        if (pegawaiData.tarifProduk <= 0) {
            alert('Tarif/Produk tidak valid. Pastikan pegawai memiliki kualifikasi dengan tarif produk!');
            e.preventDefault();
            return false;
        }

        const produkPerHari = parseInt(document.getElementById('produk_per_hari').value) || 0;
        const hariKerja = parseInt(document.getElementById('hari_kerja').value) || 26;
        const totalProduk = produkPerHari * hariKerja;

        if (totalProduk === 0) {
            alert('Masukkan jumlah produk terlebih dahulu!');
            e.preventDefault();
            return false;
        }

        // Populate hidden fields for backend processing
        // Distribute produk evenly across 4 periods
        const produkPerPeriod = Math.floor(totalProduk / 4);
        const remainder = totalProduk % 4;
        
        document.getElementById('hidden_produk_hari_1_5').value = produkPerPeriod + (remainder > 0 ? 1 : 0);
        document.getElementById('hidden_produk_hari_6_10').value = produkPerPeriod + (remainder > 1 ? 1 : 0);
        document.getElementById('hidden_produk_hari_11_20').value = produkPerPeriod + (remainder > 2 ? 1 : 0);
        document.getElementById('hidden_produk_hari_21_30').value = produkPerPeriod;

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    });

    // Add event listeners for real-time calculation
    document.getElementById('produk_per_hari').addEventListener('input', hitungOtomatis);
    document.getElementById('hari_kerja').addEventListener('input', hitungOtomatis);
});
</script>
@endsection

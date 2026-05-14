@extends('layouts.app')

@section('title', 'Tambah Penggajian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-cash-coin me-2"></i>Tambah Penggajian
        </h2>
        <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('transaksi.penggajian.store') }}" id="formPenggajian">
                @csrf

                <!-- SECTION 1: DATA PEGAWAI -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="pegawai_id" class="form-label">Pegawai <span class="text-danger">*</span></label>
                        <select name="pegawai_id" id="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror" required onchange="updateTarif()">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach ($pegawais as $p)
                                <option value="{{ $p->id }}" 
                                    data-tarif="{{ $p->jabatanRelasi->tarif_produk ?? 0 }}"
                                    data-tunjangan-jabatan="{{ $p->jabatanRelasi->tunjangan ?? 0 }}"
                                    data-tunjangan-transport="{{ $p->jabatanRelasi->tunjangan_transport ?? 0 }}"
                                    data-tunjangan-konsumsi="{{ $p->jabatanRelasi->tunjangan_konsumsi ?? 0 }}"
                                    data-bpjs="{{ $p->jabatanRelasi->asuransi ?? 0 }}">
                                    {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('pegawai_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="coa_kasbank" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="coa_kasbank" id="coa_kasbank" class="form-select @error('coa_kasbank') is-invalid @enderror" required>
                            <option value="">-- Pilih --</option>
                            @foreach ($kasbank as $kb)
                                <option value="{{ $kb->kode_akun }}">
                                    {{ $kb->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                        @error('coa_kasbank')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_penggajian" class="form-label">Tanggal Penggajian <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_penggajian" id="tanggal_penggajian" class="form-control @error('tanggal_penggajian') is-invalid @enderror" required value="{{ date('Y-m-d') }}">
                        @error('tanggal_penggajian')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Tanggal pelaksanaan pembayaran gaji</small>
                    </div>
                </div>

                <!-- SECTION 2: KOMPONEN PRODUKSI -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="total_produk" class="form-label">Total Produk Bulan Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="total_produk_bulanan" id="total_produk" class="form-control @error('total_produk_bulanan') is-invalid @enderror" value="0" min="0" oninput="hitungOtomatis()" required>
                            <span class="input-group-text">produk</span>
                        </div>
                        @error('total_produk_bulanan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="hari_kerja" class="form-label">Hari Kerja</label>
                        <div class="input-group">
                            <input type="number" name="hari_kerja" id="hari_kerja" class="form-control" value="26" min="1" max="31" oninput="hitungOtomatis()">
                            <span class="input-group-text">hari</span>
                        </div>
                        <small class="form-text text-muted">Default: 26 hari/bulan</small>
                    </div>

                    <div class="col-md-6">
                        <label for="rata_rata_hari" class="form-label">Rata-rata / Hari <span class="badge bg-info">referensi</span></label>
                        <div class="input-group">
                            <input type="text" id="rata_rata_hari" class="form-control" readonly value="0">
                            <span class="input-group-text">produk/hari</span>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: PERHITUNGAN OTOMATIS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Total Produk</label>
                        <div class="input-group">
                            <input type="text" id="display_total_produk" class="form-control" readonly value="0">
                            <span class="input-group-text">produk</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tarif / Produk</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="display_tarif" class="form-control" readonly value="0">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gaji Produksi (Mentah)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="display_gaji_mentah" class="form-control" readonly value="0">
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                    </div>

                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="aktif_bulat" name="pembulatan_aktif" onchange="togglePembulatan()">
                            <label class="form-check-label" for="aktif_bulat">
                                Aktifkan Pembulatan Gaji
                            </label>
                        </div>
                    </div>

                    <div id="panel_bulat" class="col-md-6" style="display: none;">
                        <label for="step_bulat" class="form-label">Bulatkan ke Kelipatan</label>
                        <select name="pembulatan_step" id="step_bulat" class="form-select" onchange="hitungOtomatis()">
                            <option value="1000">Rp 1.000</option>
                            <option value="10000">Rp 10.000</option>
                            <option value="100000" selected>Rp 100.000</option>
                            <option value="500000">Rp 500.000</option>
                        </select>
                    </div>

                    <div id="info_selisih" class="col-md-6" style="display: none;">
                        <div class="alert alert-info py-2 mb-0">
                            <strong>Selisih Pembulatan:</strong> Rp <span id="selisih_value">0</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gaji Produksi Final</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="display_gaji_final" class="form-control fw-bold text-primary" readonly value="0">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: TUNJANGAN DAN ASURANSI -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="tunj_jabatan" class="form-label">Tunjangan Jabatan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="tunjangan_jabatan" id="tunj_jabatan" class="form-control" value="0" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tunj_transport" class="form-label">Tunjangan Transport</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="tunjangan_transport" id="tunj_transport" class="form-control" value="150000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tunj_konsumsi" class="form-label">Tunjangan Konsumsi</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="tunjangan_konsumsi" id="tunj_konsumsi" class="form-control" value="375000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="bpjs" class="form-label">Asuransi BPJS</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="asuransi" id="bpjs" class="form-control" value="100000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>
                </div>

                <!-- TOTAL GAJI -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="alert alert-light border py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Total Gaji Bulan Ini</small>
                                    <h4 class="mb-0 text-primary" id="display_total_gaji">Rp 0</h4>
                                    <small class="text-muted">Gaji produksi + tunjangan – asuransi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan Penggajian
                            </button>
                            <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hidden input untuk gaji final -->
                <input type="hidden" name="gaji_produksi_final" id="h-final" value="0">
            </form>
        </div>
    </div>
</div>

<script>
    // Konstanta
    let TARIF_PRODUK = 729;

    // Format Rupiah
    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num);
    }

    // Parse Rupiah
    function parseRupiah(str) {
        return parseInt(str.replace(/\D/g, '')) || 0;
    }

    // Update Tarif dari pegawai yang dipilih
    function updateTarif() {
        const select = document.getElementById('pegawai_id');
        const option = select.options[select.selectedIndex];
        
        TARIF_PRODUK = parseInt(option.dataset.tarif) || 729;
        
        // Update tunjangan default
        document.getElementById('tunj_jabatan').value = parseInt(option.dataset.tunjanganJabatan) || 0;
        document.getElementById('tunj_transport').value = parseInt(option.dataset.tunjanganTransport) || 150000;
        document.getElementById('tunj_konsumsi').value = parseInt(option.dataset.tunjanganKonsumsi) || 375000;
        document.getElementById('bpjs').value = parseInt(option.dataset.bpjs) || 100000;
        
        hitungOtomatis();
    }

    // Toggle Pembulatan
    function togglePembulatan() {
        const checkbox = document.getElementById('aktif_bulat');
        const panel = document.getElementById('panel_bulat');
        const infoBox = document.getElementById('info_selisih');
        
        if (checkbox.checked) {
            panel.style.display = 'block';
            infoBox.style.display = 'block';
        } else {
            panel.style.display = 'none';
            infoBox.style.display = 'none';
        }
        
        hitungOtomatis();
    }

    // Hitung Otomatis
    function hitungOtomatis() {
        // Ambil nilai input
        const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;
        const hariKerja = parseInt(document.getElementById('hari_kerja').value) || 26;
        const tunjanganJabatan = parseInt(document.getElementById('tunj_jabatan').value) || 0;
        const tunjanganTransport = parseInt(document.getElementById('tunj_transport').value) || 0;
        const tunjanganKonsumsi = parseInt(document.getElementById('tunj_konsumsi').value) || 0;
        const bpjs = parseInt(document.getElementById('bpjs').value) || 0;
        const aktifBulat = document.getElementById('aktif_bulat').checked;
        const stepBulat = parseInt(document.getElementById('step_bulat').value) || 100000;

        // Hitung rata-rata per hari
        const rataRataHari = hariKerja > 0 ? Math.round(totalProduk / hariKerja) : 0;
        document.getElementById('rata_rata_hari').value = formatRupiah(rataRataHari);

        // Hitung gaji mentah
        const gajiMentah = totalProduk * TARIF_PRODUK;

        // Hitung gaji final dengan pembulatan
        let gajiFinal = gajiMentah;
        let selisih = 0;

        if (aktifBulat) {
            gajiFinal = Math.ceil(gajiMentah / stepBulat) * stepBulat;
            selisih = gajiFinal - gajiMentah;
            document.getElementById('selisih_value').textContent = formatRupiah(selisih);
        }

        // Hitung total gaji
        const totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi;
        const totalGaji = gajiFinal + totalTunjangan - bpjs;

        // Update display
        document.getElementById('display_total_produk').value = formatRupiah(totalProduk);
        document.getElementById('display_tarif').value = formatRupiah(TARIF_PRODUK);
        document.getElementById('display_gaji_mentah').value = formatRupiah(gajiMentah);
        document.getElementById('display_gaji_final').value = formatRupiah(gajiFinal);
        document.getElementById('display_total_gaji').textContent = 'Rp ' + formatRupiah(totalGaji);

        // Isi hidden input untuk gaji final
        document.getElementById('h-final').value = gajiFinal;
    }

    // Form Submit
    document.getElementById('formPenggajian').addEventListener('submit', function(e) {
        // Validasi sebelum submit
        const pegawaiId = document.getElementById('pegawai_id').value;
        const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;

        if (!pegawaiId) {
            e.preventDefault();
            alert('Pilih pegawai terlebih dahulu!');
            return;
        }

        if (totalProduk <= 0) {
            e.preventDefault();
            alert('Total produk harus lebih dari 0!');
            return;
        }

        // Update hidden input sebelum submit
        const gajiFinal = parseRupiah(document.getElementById('display_gaji_final').value);
        document.getElementById('h-final').value = gajiFinal;
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        hitungOtomatis();
    });
</script>
@endsection

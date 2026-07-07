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
                        <select name="pegawai_id" id="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror" required onchange="handlePegawaiChange()">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach ($pegawais as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->nama }} ({{ $p->kualifikasiRelasi->nama_kualifikasi ?? $p->kualifikasi ?? 'Tanpa Kualifikasi' }})
                                </option>
                            @endforeach
                        </select>
                        @error('pegawai_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" id="kategori" class="form-select @error('kategori') is-invalid @enderror" required onchange="handleKategoriChange()">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="BTKL">BTKL (Biaya Tenaga Kerja Langsung)</option>
                            <option value="BTKTL">BTKTL (Biaya Tenaga Kerja Tidak Langsung)</option>
                        </select>
                        <small id="kategori_status" class="form-text mt-1 d-block"></small>
                        @error('kategori')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-select @error('metode_pembayaran') is-invalid @enderror" required onchange="toggleRekeningBlock()">
                            <option value="">-- Pilih --</option>
                            <option value="tunai" {{ old('metode_pembayaran') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                            <option value="transfer_bank" {{ old('metode_pembayaran', 'transfer_bank') == 'transfer_bank' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                        @error('metode_pembayaran')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_penggajian" class="form-label">Tanggal Penggajian <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_penggajian" id="tanggal_penggajian" class="form-control @error('tanggal_penggajian') is-invalid @enderror" required value="{{ date('Y-m-d') }}" onchange="handleTanggalChange()">
                        @error('tanggal_penggajian')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Tanggal pelaksanaan pembayaran gaji (untuk dokumentasi dan ekstrak bulan)</small>
                    </div>

                    <!-- BLOK REKENING TUJUAN -->
                    <div class="col-12" id="blok_rekening_tujuan" style="display: none;">
                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading fw-bold mb-2"><i class="bi bi-bank me-1"></i> Informasi Rekening Tujuan</h6>
                            <div id="info_rekening_tersedia" style="display: none;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Bank</small>
                                        <strong id="display_nama_bank">-</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">No. Rekening</small>
                                        <strong id="display_no_rekening">-</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Atas Nama</small>
                                        <strong id="display_atas_nama">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div id="info_rekening_kosong" class="text-danger mt-2" style="display: none;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Pegawai ini belum memiliki data rekening bank. Lengkapi di Master Data Pegawai terlebih dahulu.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: KOMPONEN PRODUKSI -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="total_produk" class="form-label">Total Produk Bulan Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="total_produk_bulanan" id="total_produk" class="form-control @error('total_produk_bulanan') is-invalid @enderror" value="" min="0" oninput="hitungOtomatis()" required>
                            <span class="input-group-text">produk</span>
                        </div>
                        <small id="total_produk_status" class="form-text mt-1 d-block"></small>
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
                        <small id="hari_kerja_status" class="form-text text-muted mt-1 d-block">Auto-filled dari presensi, bisa diedit jika perlu</small>
                    </div>

                    <div class="col-md-6">
                        <label for="rata_rata_hari" class="form-label">Rata-rata / Hari</label>
                        <div class="input-group">
                            <input type="text" id="rata_rata_hari" class="form-control" readonly value="0">
                            <span class="input-group-text">produk/hari</span>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: PERHITUNGAN OTOMATIS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Tarif / Produk <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="tarif_produk_input" name="tarif_produk" class="form-control input-rupiah" value="0" oninput="hitungOtomatis()" required>
                        </div>
                        <small class="form-text text-muted d-block mt-1" id="tarif_status">Auto-filled dari kualifikasi, atau input manual jika tidak ada</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gaji Pokok</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="display_gaji_mentah" class="form-control fw-bold text-primary" readonly value="0">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: TUNJANGAN DAN ASURANSI -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="tunj_jabatan" class="form-label">Tunjangan Jabatan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="tunjangan_jabatan" id="tunj_jabatan" class="form-control input-rupiah" value="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tunj_transport" class="form-label">Tunjangan Transport</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="tunjangan_transport" id="tunj_transport" class="form-control input-rupiah" value="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tunj_konsumsi" class="form-label">Tunjangan Konsumsi</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="tunjangan_konsumsi" id="tunj_konsumsi" class="form-control input-rupiah" value="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="bpjs" class="form-label">Asuransi BPJS</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="asuransi" id="bpjs" class="form-control input-rupiah" value="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="potongan_alpa" class="form-label">Potongan Alpa</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="potongan" id="potongan_alpa" class="form-control input-rupiah bg-light" value="0" readonly>
                        </div>
                        <small id="potongan_status" class="form-text text-muted mt-1 d-block">Potongan otomatis berdasarkan jumlah alpa</small>
                    </div>
                </div>

                <!-- TOTAL GAJI -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <!-- Baris Pertama: Diterima Karyawan -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded text-primary">
                                            <i class="bi bi-person fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Diterima Pegawai</h6>
                                            <small class="text-muted">Gaji pokok + semua tunjangan</small>
                                        </div>
                                    </div>
                                    <h3 class="mb-0 fw-bold text-primary" id="display_total_gaji">Rp 0</h3>
                                </div>

                                <!-- Baris Kedua: BPJS -->
                                <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                                    <small class="text-muted"><i class="bi bi-plus-lg me-1"></i> Asuransi BPJS (beban perusahaan)</small>
                                    <small class="text-muted fw-semibold" id="display_bpjs_beban">Rp 0</small>
                                </div>

                                <!-- Baris Ketiga: Total Biaya Perusahaan -->
                                <div class="bg-info bg-opacity-10 rounded p-3 mt-3 border border-info border-opacity-25">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-info text-white p-2 rounded">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <h6 class="mb-0 fw-bold text-dark">Total Biaya Perusahaan</h6>
                                        </div>
                                        <h4 class="mb-0 fw-bold text-info-emphasis" id="display_total_biaya">Rp 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="btn_simpan">
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
    let TARIF_PRODUK = 0;
    let GAJI_POKOK = 0;
    let IS_PRODUKSI = false;
    let JUMLAH_ALPA = 0;
    let HARI_KERJA_TOTAL = 26;
    let HAS_REKENING = false;

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

    // Setup format/unformat untuk field input-rupiah
    function setupRupiahFormatting() {
        document.querySelectorAll('.input-rupiah').forEach(function(input) {
            input.addEventListener('blur', function() {
                const raw = parseRupiah(this.value);
                this.value = raw > 0 ? formatRupiah(raw) : '0';
            });

            input.addEventListener('focus', function() {
                this.value = parseRupiah(this.value);
            });
        });
    }

    // Clear form state
    function clearFormState() {
        TARIF_PRODUK = 0;
        GAJI_POKOK = 0;
        IS_PRODUKSI = false;
        JUMLAH_ALPA = 0;
        HAS_REKENING = false;
        document.getElementById('kategori').value = '';
        document.getElementById('kategori_status').textContent = '';
        document.getElementById('tarif_produk_input').value = 0;
        document.getElementById('tarif_status').textContent = 'Auto-filled dari kualifikasi, atau input manual jika tidak ada';
        document.getElementById('tarif_status').className = 'form-text text-muted d-block mt-1';
        document.getElementById('total_produk').value = '';
        document.getElementById('total_produk').readOnly = false;
        document.getElementById('total_produk').style.backgroundColor = '#fff';
        document.getElementById('total_produk_status').textContent = '';
        document.getElementById('tunj_jabatan').value = 0;
        document.getElementById('tunj_transport').value = 0;
        document.getElementById('tunj_konsumsi').value = 0;
        document.getElementById('bpjs').value = 0;
        if(document.getElementById('potongan_alpa')) document.getElementById('potongan_alpa').value = 0;
        document.getElementById('display_gaji_mentah').value = '0';
        document.getElementById('display_total_gaji').textContent = 'Rp 0';
        document.getElementById('display_total_biaya').textContent = 'Rp 0';
        document.getElementById('rata_rata_hari').value = '0';
        document.getElementById('h-final').value = 0;
        
        document.getElementById('blok_rekening_tujuan').style.display = 'none';
        document.getElementById('info_rekening_tersedia').style.display = 'none';
        document.getElementById('info_rekening_kosong').style.display = 'none';
        document.getElementById('btn_simpan').disabled = false;
    }

    // Handle Pegawai Change
    function handlePegawaiChange() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        
        if (!pegawaiId) {
            clearFormState();
            hitungOtomatis();
            return;
        }
        
        console.log('Fetching data for pegawai:', pegawaiId);
        
        fetch(`/api/pegawai/${pegawaiId}/data`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);
                
                GAJI_POKOK = parseInt(data.gaji_pokok) || 0;
                
                // Set default tunjangan dan asuransi
                document.getElementById('tunj_jabatan').value = formatRupiah(parseInt(data.tunjangan_jabatan) || 0);
                document.getElementById('tunj_transport').value = formatRupiah(parseInt(data.tunjangan_transport) || 0);
                document.getElementById('tunj_konsumsi').value = formatRupiah(parseInt(data.tunjangan_konsumsi) || 0);
                // Asuransi dari API, default 0 kalau kosong
                document.getElementById('bpjs').value = formatRupiah(parseInt(data.asuransi) || 0);
                
                // Update Rekening Info
                const noRekening = data.nomor_rekening || '';
                if (noRekening !== '') {
                    HAS_REKENING = true;
                    document.getElementById('display_nama_bank').textContent = data.bank || '-';
                    document.getElementById('display_no_rekening').textContent = noRekening;
                    document.getElementById('display_atas_nama').textContent = data.nama_rekening || '-';
                    document.getElementById('info_rekening_tersedia').style.display = 'block';
                    document.getElementById('info_rekening_kosong').style.display = 'none';
                } else {
                    HAS_REKENING = false;
                    document.getElementById('info_rekening_tersedia').style.display = 'none';
                    document.getElementById('info_rekening_kosong').style.display = 'block';
                }

                // Auto-set tarif
                const tarifField = document.getElementById('tarif_produk_input');
                const tarifStatus = document.getElementById('tarif_status');
                const tarifDariKualifikasi = parseInt(data.tarif) || 0;
                
                if (tarifDariKualifikasi > 0) {
                    TARIF_PRODUK = tarifDariKualifikasi;
                    tarifField.value = formatRupiah(tarifDariKualifikasi);
                    tarifStatus.textContent = '✓ Tarif dari kualifikasi: ' + (data.kualifikasi_nama || data.jabatan_nama || 'pegawai');
                    tarifStatus.className = 'form-text text-success d-block mt-1';
                } else {
                    TARIF_PRODUK = 0;
                    tarifField.value = 0;
                    tarifStatus.textContent = 'Tarif tidak ditemukan (isi manual jika perlu)';
                    tarifStatus.className = 'form-text text-muted d-block mt-1';
                }

                // Auto-set kategori
                const kategoriDropdown = document.getElementById('kategori');
                if (data.kategori) {
                    for (let i = 0; i < kategoriDropdown.options.length; i++) {
                        if (kategoriDropdown.options[i].value === data.kategori) {
                            kategoriDropdown.selectedIndex = i;
                            break;
                        }
                    }
                }
                
                // Trigger change to set up Produksi/Gaji Tetap state correctly
                handleKategoriChange();
                updatePresensiData();
                toggleRekeningBlock();
            })
            .catch(error => {
                console.error('Error fetching pegawai:', error);
                clearFormState();
                hitungOtomatis();
            });
    }

    // Handle Kategori Change
    function handleKategoriChange() {
        const kategori = document.getElementById('kategori').value;
        const totalProdukField = document.getElementById('total_produk');
        const kategoriStatus = document.getElementById('kategori_status');
        const totalProdukStatus = document.getElementById('total_produk_status');
        
        if (!kategori) {
            IS_PRODUKSI = false;
            kategoriStatus.textContent = '';
            totalProdukStatus.textContent = '';
            totalProdukField.value = '';
            totalProdukField.readOnly = false;
            totalProdukField.style.backgroundColor = '#fff';
            hitungOtomatis();
            return;
        }
        
        if (kategori === 'BTKL') {
            IS_PRODUKSI = true;
            kategoriStatus.textContent = '✓ Kategori Produksi (BTKL)';
            kategoriStatus.className = 'form-text text-success d-block mt-1';
            
            totalProdukField.readOnly = true;
            totalProdukField.style.backgroundColor = '#f5f5f5';
            totalProdukStatus.textContent = '';
            totalProdukStatus.className = 'form-text text-success d-block mt-1';
            totalProdukField.placeholder = 'Otomatis dari Transaksi Produksi';
            
            updateTotalProduk();
        } else if (kategori === 'BTKTL') {
            IS_PRODUKSI = false;
            kategoriStatus.textContent = '';
            kategoriStatus.className = '';
            
            totalProdukField.readOnly = false;
            totalProdukField.style.backgroundColor = '#fff';
            totalProdukField.value = '';
            totalProdukStatus.textContent = '';
            totalProdukStatus.className = '';
            totalProdukField.placeholder = 'Kosong (gaji tetap)';
            
            hitungOtomatis();
        }
    }

    // Handle Tanggal Change
    function handleTanggalChange() {
        if (IS_PRODUKSI) {
            updateTotalProduk();
        }
        updatePresensiData();
    }

    // Update Total Produk for Produksi
    function updateTotalProduk() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tanggalInput = document.getElementById('tanggal_penggajian').value;
        const totalProdukField = document.getElementById('total_produk');
        const hariKerjaField = document.getElementById('hari_kerja');
        
        if (!IS_PRODUKSI) {
            hitungOtomatis();
            return;
        }
        
        if (!pegawaiId || !tanggalInput) {
            totalProdukField.value = '';
            hariKerjaField.value = 26;
            hitungOtomatis();
            return;
        }
        
        const date = new Date(tanggalInput);
        const bulan = String(date.getMonth() + 1).padStart(2, '0');
        const tahun = date.getFullYear();
        
        fetch(`/api/pegawai/${pegawaiId}/produksi/${bulan}/${tahun}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                const totalProduksi = data.data.total_produksi || 0;
                const hariProduksiBulanan = data.data.hari_produksi_bulanan || 26;
                
                totalProdukField.value = totalProduksi;
                hariKerjaField.value = hariProduksiBulanan;
                
                if (totalProduksi === 0) {
                    document.getElementById('total_produk_status').textContent = '⚠ Data produksi bulanan kosong/0';
                    document.getElementById('total_produk_status').className = 'form-text text-warning d-block mt-1';
                } else {
                    document.getElementById('total_produk_status').textContent = '';
                    document.getElementById('total_produk_status').className = 'form-text text-success d-block mt-1';
                }
                
                hitungOtomatis();
            })
            .catch(error => {
                console.error('Error fetching produksi:', error);
                totalProdukField.value = 0;
                hariKerjaField.value = 26;
                hitungOtomatis();
            });
    }

    // Hitung Otomatis
    function hitungOtomatis() {
        const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;
        const hariKerja = parseInt(document.getElementById('hari_kerja').value) || 26;
        const tunjanganJabatan = parseRupiah(document.getElementById('tunj_jabatan').value) || 0;
        const tunjanganTransport = parseRupiah(document.getElementById('tunj_transport').value) || 0;
        const tunjanganKonsumsi = parseRupiah(document.getElementById('tunj_konsumsi').value) || 0;
        const bpjs = parseRupiah(document.getElementById('bpjs').value) || 0;

        // Update TARIF_PRODUK dari input field (hanya untuk referensi HPP, bukan untuk hitung gaji)
        TARIF_PRODUK = parseRupiah(document.getElementById('tarif_produk_input').value) || 0;

        const rataRataHari = hariKerja > 0 ? Math.round(totalProduk / hariKerja) : 0;
        document.getElementById('rata_rata_hari').value = formatRupiah(rataRataHari);

        // Gaji Pokok = nilai aktual dari kualifikasis.gaji_pokok (BUKAN tarif x produk)
        // Tarif/Produk hanya dipakai untuk alokasi HPP, bukan untuk menghitung ulang gaji
        const gajiPokok = GAJI_POKOK;

        // Hitung Potongan Alpa: (JUMLAH_ALPA / HARI_KERJA_TOTAL) * Gaji Pokok
        let potonganAlpa = 0;
        if (JUMLAH_ALPA > 0 && HARI_KERJA_TOTAL > 0) {
            potonganAlpa = Math.round((JUMLAH_ALPA / HARI_KERJA_TOTAL) * gajiPokok);
        }
        if (document.getElementById('potongan_alpa')) {
            document.getElementById('potongan_alpa').value = formatRupiah(potonganAlpa);
        }

        const totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi;

        // Total Gaji Karyawan = Gaji Pokok + Tunjangan (yang diterima karyawan)
        const totalGajiKaryawan = gajiPokok + totalTunjangan - potonganAlpa;

        // Total Biaya Perusahaan = Total Gaji Karyawan + Asuransi BPJS
        const totalBiayaPerusahaan = totalGajiKaryawan + bpjs;

        document.getElementById('display_gaji_mentah').value = formatRupiah(gajiPokok);
        document.getElementById('display_total_gaji').textContent = 'Rp ' + formatRupiah(totalGajiKaryawan);
        document.getElementById('display_total_biaya').textContent = 'Rp ' + formatRupiah(totalBiayaPerusahaan);
        if(document.getElementById('display_bpjs_beban')) {
            document.getElementById('display_bpjs_beban').textContent = 'Rp ' + formatRupiah(bpjs);
        }
        document.getElementById('h-final').value = gajiPokok;
    }

    // Form Submit
    document.getElementById('formPenggajian').addEventListener('submit', function(e) {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const kategoriId = document.getElementById('kategori').value;
        const totalProduk = document.getElementById('total_produk').value;
        
        if (!pegawaiId) {
            e.preventDefault();
            alert('Pilih pegawai terlebih dahulu!');
            return;
        }
        
        if (!kategoriId) {
            e.preventDefault();
            alert('Pilih kategori terlebih dahulu!');
            return;
        }

        if (IS_PRODUKSI) {
            if (!totalProduk || totalProduk <= 0) {
                e.preventDefault();
                alert('Untuk kategori Produksi (BTKL), Total Produk harus lebih dari 0. Pastikan data produksi di bulan ini tersedia.');
                return;
            }
        } else {
            // For Gaji Tetap, if totalProduk is empty string, we set it to 0 so validation passes
            if (totalProduk === '') {
                document.getElementById('total_produk').value = 0;
            }
        }

        // Unformat semua field input-rupiah sebelum submit
        document.querySelectorAll('.input-rupiah').forEach(function(input) {
            input.value = parseRupiah(input.value);
        });

        // h-final = Gaji Pokok aktual dari kualifikasi
        document.getElementById('h-final').value = GAJI_POKOK;
    });

    // Update Presensi Data
    function updatePresensiData() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tanggalInput = document.getElementById('tanggal_penggajian').value;
        const hariKerjaField = document.getElementById('hari_kerja');
        const hariKerjaStatus = document.getElementById('hari_kerja_status');
        
        if (!pegawaiId || !tanggalInput) {
            JUMLAH_ALPA = 0;
            if (hariKerjaStatus) {
                hariKerjaStatus.textContent = 'Auto-filled dari presensi, bisa diedit jika perlu';
                hariKerjaStatus.className = 'form-text text-muted d-block mt-1';
            }
            hitungOtomatis();
            return;
        }
        
        const date = new Date(tanggalInput);
        const bulan = String(date.getMonth() + 1).padStart(2, '0');
        const tahun = date.getFullYear();
        
        fetch(`/api/pegawai/${pegawaiId}/presensi/${bulan}/${tahun}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.data.total_data === 0) {
                    JUMLAH_ALPA = 0;
                    HARI_KERJA_TOTAL = 26; // Kembali ke default
                    if (hariKerjaStatus) {
                        hariKerjaStatus.textContent = '⚠ Belum ada data presensi untuk periode ini.';
                        hariKerjaStatus.className = 'form-text text-warning d-block mt-1';
                    }
                } else {
                    JUMLAH_ALPA = data.data.jumlah_alpa || 0;
                    const jumlahHadir = data.data.jumlah_hadir || 0;
                    
                    // Total pembagi = Hadir + Alpa (atau 26 jika 0)
                    HARI_KERJA_TOTAL = (jumlahHadir + JUMLAH_ALPA) > 0 ? (jumlahHadir + JUMLAH_ALPA) : 26;
                    
                    // Update hari kerja dengan jumlah hadir
                    if (hariKerjaField) hariKerjaField.value = jumlahHadir;
                    
                    if (hariKerjaStatus) {
                        hariKerjaStatus.textContent = `✓ Data presensi ditemukan: ${jumlahHadir} Hadir, ${JUMLAH_ALPA} Alpa.`;
                        hariKerjaStatus.className = 'form-text text-success d-block mt-1';
                    }
                }
                hitungOtomatis();
            })
            .catch(error => {
                console.error('Error fetching presensi:', error);
                JUMLAH_ALPA = 0;
                if (hariKerjaStatus) {
                    hariKerjaStatus.textContent = 'Auto-filled dari presensi, bisa diedit jika perlu';
                    hariKerjaStatus.className = 'form-text text-muted d-block mt-1';
                }
                hitungOtomatis();
            });
    }

    // Update Metode Pembayaran
    function toggleRekeningBlock() {
        const metode = document.getElementById('metode_pembayaran').value;
        const pegawaiId = document.getElementById('pegawai_id').value;
        const btnSimpan = document.getElementById('btn_simpan');
        
        if (metode === 'transfer_bank' && pegawaiId) {
            document.getElementById('blok_rekening_tujuan').style.display = 'block';
            if (!HAS_REKENING) {
                btnSimpan.disabled = true;
            } else {
                btnSimpan.disabled = false;
            }
        } else {
            document.getElementById('blok_rekening_tujuan').style.display = 'none';
            btnSimpan.disabled = false;
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Form initialized');
        clearFormState();
        setupRupiahFormatting(); // Setup format listener
        hitungOtomatis();
        
        // Cek init metode_pembayaran
        toggleRekeningBlock();
    });

    // Clear state on window focus
    window.addEventListener('focus', function() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tarifValue = document.getElementById('tarif_produk_input').value;
        
        if (!pegawaiId && tarifValue) {
            clearFormState();
            document.getElementById('pegawai_id').value = '';
        }
    });
</script>
@endsection

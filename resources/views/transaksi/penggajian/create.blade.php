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
                                    {{ $p->nama }}
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
                        <label for="coa_kasbank" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="coa_kasbank" id="coa_kasbank" class="form-select @error('coa_kasbank') is-invalid @enderror" required onchange="updateMetodePembayaran()">
                            <option value="">-- Pilih --</option>
                            @foreach ($kasbank as $kb)
                                @php
                                    $labelStr = strtolower($kb->nama_akun);
                                    if (str_contains($labelStr, 'kas kecil')) continue;
                                    $label = str_contains($labelStr, 'bank') ? 'Transfer Bank' : 'Tunai';
                                @endphp
                                <option value="{{ $kb->kode_akun }}" data-nama="{{ strtolower($kb->nama_akun) }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="metode_pembayaran" id="metode_pembayaran" value="transfer_bank">
                        @error('coa_kasbank')
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
                        <small class="form-text text-muted">Default: 26 hari/bulan</small>
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
                        <label for="tunj_jabatan" class="form-label">Tunjangan Kualifikasi</label>
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
    let TARIF_PRODUK = 0;
    let GAJI_POKOK = 0;
    let IS_PRODUKSI = false;

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
        document.getElementById('display_gaji_mentah').value = '0';
        document.getElementById('display_gaji_final').value = '0';
        document.getElementById('display_total_gaji').textContent = 'Rp 0';
        document.getElementById('rata_rata_hari').value = '0';
        document.getElementById('aktif_bulat').checked = false;
        document.getElementById('panel_bulat').style.display = 'none';
        document.getElementById('info_selisih').style.display = 'none';
        document.getElementById('h-final').value = 0;
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
            totalProdukStatus.textContent = '✓ Otomatis dari Transaksi Produksi (readonly)';
            totalProdukStatus.className = 'form-text text-success d-block mt-1';
            totalProdukField.placeholder = 'Otomatis dari Transaksi Produksi';
            
            updateTotalProduk();
        } else if (kategori === 'BTKTL') {
            IS_PRODUKSI = false;
            kategoriStatus.textContent = '✗ Kategori Gaji Tetap (BTKTL)';
            kategoriStatus.className = 'form-text text-warning d-block mt-1';
            
            totalProdukField.readOnly = false;
            totalProdukField.style.backgroundColor = '#fff';
            totalProdukField.value = '';
            totalProdukStatus.textContent = '⚠ Gaji tetap - Tidak ada produksi (isi 0 jika diperlukan)';
            totalProdukStatus.className = 'form-text text-warning d-block mt-1';
            totalProdukField.placeholder = 'Kosong (gaji tetap)';
            
            hitungOtomatis();
        }
    }

    // Handle Tanggal Change
    function handleTanggalChange() {
        if (IS_PRODUKSI) {
            updateTotalProduk();
        }
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
                    document.getElementById('total_produk_status').textContent = '✓ Otomatis dari Transaksi Produksi (readonly)';
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
        const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;
        const hariKerja = parseInt(document.getElementById('hari_kerja').value) || 26;
        const tunjanganJabatan = parseRupiah(document.getElementById('tunj_jabatan').value) || 0;
        const tunjanganTransport = parseRupiah(document.getElementById('tunj_transport').value) || 0;
        const tunjanganKonsumsi = parseRupiah(document.getElementById('tunj_konsumsi').value) || 0;
        const bpjs = parseRupiah(document.getElementById('bpjs').value) || 0;
        const aktifBulat = document.getElementById('aktif_bulat').checked;
        const stepBulat = parseInt(document.getElementById('step_bulat').value) || 100000;

        // Update TARIF_PRODUK dari input field
        TARIF_PRODUK = parseRupiah(document.getElementById('tarif_produk_input').value) || 0;

        const rataRataHari = hariKerja > 0 ? Math.round(totalProduk / hariKerja) : 0;
        document.getElementById('rata_rata_hari').value = formatRupiah(rataRataHari);

        let gajiMentah = 0;
        if (IS_PRODUKSI) {
            gajiMentah = totalProduk * TARIF_PRODUK;
        } else {
            gajiMentah = GAJI_POKOK;
        }

        let gajiFinal = gajiMentah;
        let selisih = 0;

        if (aktifBulat) {
            gajiFinal = Math.ceil(gajiMentah / stepBulat) * stepBulat;
            selisih = gajiFinal - gajiMentah;
            document.getElementById('selisih_value').textContent = formatRupiah(selisih);
        }

        const totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi;
        const totalGaji = gajiFinal + totalTunjangan - bpjs;

        document.getElementById('display_gaji_mentah').value = formatRupiah(gajiMentah);
        document.getElementById('display_gaji_final').value = formatRupiah(gajiFinal);
        document.getElementById('display_total_gaji').textContent = 'Rp ' + formatRupiah(totalGaji);
        document.getElementById('h-final').value = gajiFinal;
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

        const gajiFinal = parseRupiah(document.getElementById('display_gaji_final').value);
        document.getElementById('h-final').value = gajiFinal;
    });

    // Update Metode Pembayaran berdasarkan pilihan COA Kas/Bank
    function updateMetodePembayaran() {
        const select = document.getElementById('coa_kasbank');
        const hiddenField = document.getElementById('metode_pembayaran');
        
        if (!hiddenField) return;
        
        if (select.selectedIndex > 0) {
            const selectedOption = select.options[select.selectedIndex];
            const namaAkun = (selectedOption.getAttribute('data-nama') || selectedOption.text).toLowerCase();
            
            // Deteksi apakah Tunai atau Transfer berdasarkan nama akun
            if (namaAkun.includes('kas') || namaAkun.includes('tunai') || namaAkun.includes('cash')) {
                hiddenField.value = 'tunai';
            } else if (namaAkun.includes('bank') || namaAkun.includes('transfer')) {
                hiddenField.value = 'transfer_bank';
            } else {
                // Default ke transfer_bank
                hiddenField.value = 'transfer_bank';
            }
        } else {
            hiddenField.value = 'transfer_bank';
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Form initialized');
        clearFormState();
        setupRupiahFormatting(); // Setup format listener
        hitungOtomatis();
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

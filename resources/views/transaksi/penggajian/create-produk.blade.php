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
                        <select name="kategori" id="kategori" class="form-select @error('kategori') is-invalid @enderror" required onchange="handleKategoriChange(this.value)">
                            <option value="">-- Pilih Kategori --</option>
                            @if(isset($kategoris))
                                @foreach ($kategoris as $k)
                                    <option value="{{ $k->nama }}">{{ $k->nama }} ({{ strtoupper($k->kategori) }})</option>
                                @endforeach
                            @endif
                        </select>
                        <small id="kategori_status" class="form-text mt-1 d-block"></small>
                    </div>

                    <div class="col-md-6">
                        <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-select @error('metode_pembayaran') is-invalid @enderror" required>
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
                        <input type="date" name="tanggal_penggajian" id="tanggal_penggajian" class="form-control @error('tanggal_penggajian') is-invalid @enderror" required value="{{ date('Y-m-d') }}">
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
                            <input type="number" name="total_produk_bulanan" id="total_produk" class="form-control @error('total_produk_bulanan') is-invalid @enderror" value="0" min="0" oninput="hitungOtomatis()" required>
                            <span class="input-group-text">produk</span>
                        </div>
                        <small id="status_total_produk" class="form-text text-muted d-block mt-1">Otomatis jika Produksi (BTKL), atau isi manual</small>
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
                            <input type="text" id="rata_rata_hari" class="form-control" value="0" disabled>
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
                            <input type="text" id="tarif_produk_input" name="tarif_produk" class="form-control input-rupiah bg-light" value="0" readonly>
                        </div>
                        <small class="form-text text-muted d-block mt-1" id="tarif_status"></small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gaji Pokok</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="display_gaji_mentah" name="gaji_pokok_display" class="form-control fw-bold text-primary" value="0" disabled>
                        </div>
                        <small class="form-text text-muted d-block mt-1">Diambil otomatis dari Kualifikasi pegawai</small>
                    </div>
                </div>

                <!-- SECTION 4: TUNJANGAN DAN ASURANSI -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="tunj_jabatan" class="form-label">Tunjangan Jabatan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="tunjangan_jabatan" id="tunj_jabatan" class="form-control input-rupiah bg-light" value="0" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tunj_transport" class="form-label">Tunjangan Transport</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="tunjangan_transport" id="tunj_transport" class="form-control input-rupiah bg-light" value="0" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tunj_konsumsi" class="form-label">Tunjangan Konsumsi</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="tunjangan_konsumsi" id="tunj_konsumsi" class="form-control input-rupiah bg-light" value="0" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="bpjs" class="form-label">Asuransi BPJS</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="asuransi" id="bpjs" class="form-control input-rupiah bg-light" value="0" readonly>
                        </div>
                    </div>
                </div>

                <!-- TOTAL GAJI -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="alert alert-light border py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Total Gaji Karyawan</small>
                                    <h4 class="mb-0 text-primary" id="display_total_gaji">Rp 0</h4>
                                    <small class="text-muted">Gaji pokok + tunjangan (diterima karyawan)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-light border py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Total Biaya Perusahaan</small>
                                    <h4 class="mb-0 text-primary" id="display_total_biaya">Rp 0</h4>
                                    <small class="text-muted">Total gaji karyawan + asuransi ditanggung perusahaan</small>
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

                <!-- Hidden input untuk gaji final - sudah bukan form field, hanya untuk submit -->
                <input type="hidden" name="gaji_produksi_final" id="h-final" value="0">
            </form>
        </div>
    </div>
</div>

<script>
    // Konstanta
    let TARIF_PRODUK = 0;
    let GAJI_POKOK = 0;  // Nilai aktual dari kualifikasis.gaji_pokok
    let IS_PRODUKSI = true;
    let TUNJANGAN_JABATAN = 0;
    let TUNJANGAN_TRANSPORT = 0;
    let TUNJANGAN_KONSUMSI = 0;
    let ASURANSI_BPJS = 0;

    // Update Metode Pembayaran (removed as we use direct select now)
    function updateMetodePembayaran() {
        // No longer needed
    }

    // Format Rupiah dengan titik pemisah ribuan
    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num);
    }

    // Parse Rupiah - hapus semua karakter non-digit
    function parseRupiah(str) {
        return parseInt(str.toString().replace(/\D/g, '')) || 0;
    }

    // BUG 2 FIX: Setup format/unformat untuk field input-rupiah
    function setupRupiahFormatting() {
        document.querySelectorAll('.input-rupiah').forEach(function(input) {
            // Saat blur (keluar dari field) - format dengan titik
            input.addEventListener('blur', function() {
                const raw = parseRupiah(this.value);
                this.value = raw > 0 ? formatRupiah(raw) : '0';
            });

            // Saat focus (masuk ke field) - tampil angka murni
            input.addEventListener('focus', function() {
                this.value = parseRupiah(this.value);
            });
        });
    }

    // Parse Rupiah (legacy - masih dipakai)
    function parseRupiahLegacy(str) {
        return parseInt(str.replace(/\D/g, '')) || 0;
    }

    // Clear form state - RESET semua field tunjangan ke 0
    function clearFormState() {
        TARIF_PRODUK = 0;
        GAJI_POKOK = 0;
        IS_PRODUKSI = true;
        document.getElementById('tarif_produk_input').value = 0;
        document.getElementById('kategori').value = '';
        document.getElementById('kategori_status').textContent = '';
        document.getElementById('status_total_produk').textContent = 'Otomatis jika Produksi (BTKL), atau isi manual';
        document.getElementById('total_produk').readOnly = false;
        document.getElementById('total_produk').style.backgroundColor = '#fff';
        document.getElementById('tarif_status').textContent = '';
        document.getElementById('tunj_jabatan').value = 0;
        document.getElementById('tunj_transport').value = 0;
        document.getElementById('tunj_konsumsi').value = 0;
        document.getElementById('bpjs').value = 0;
        document.getElementById('display_gaji_mentah').value = '0';
        document.getElementById('display_total_gaji').textContent = 'Rp 0';
        document.getElementById('display_total_biaya').textContent = 'Rp 0';
        document.getElementById('rata_rata_hari').value = '0';
        document.getElementById('h-final').value = 0;
    }

    // Update Tarif saat pegawai dipilih
    function updateTarif() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tarifField = document.getElementById('tarif_produk_input');
        const tarifStatus = document.getElementById('tarif_status');
        
        if (!pegawaiId) {
            clearFormState();
            hitungOtomatis();
            return;
        }
        
        // BUG 1 FIX: Reset field tunjangan ke 0 dulu sebelum fetch
        document.getElementById('tunj_jabatan').value = 0;
        document.getElementById('tunj_transport').value = 0;
        document.getElementById('tunj_konsumsi').value = 0;
        
        console.log('Fetching data for pegawai:', pegawaiId);
        
        fetch(`/api/pegawai/${pegawaiId}/data`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);
                
                const tarifDariKualifikasi = parseInt(data.tarif) || 0;
                // Ambil Gaji Pokok aktual dari kualifikasi (bukan hasil perkalian)
                GAJI_POKOK = parseInt(data.gaji_pokok) || 0;
                
                // Simpan tunjangan dan asuransi ke variabel global
                TUNJANGAN_JABATAN = parseInt(data.tunjangan_jabatan) || 0;
                TUNJANGAN_TRANSPORT = parseInt(data.tunjangan_transport) || 0;
                TUNJANGAN_KONSUMSI = parseInt(data.tunjangan_konsumsi) || 0;
                ASURANSI_BPJS = parseInt(data.asuransi) || 0;
                
                if (tarifDariKualifikasi > 0) {
                    TARIF_PRODUK = tarifDariKualifikasi;
                    tarifField.value = formatRupiah(tarifDariKualifikasi);
                    tarifStatus.textContent = '✓ Tarif dari kualifikasi: ' + (data.kualifikasi_nama || data.jabatan_nama || 'pegawai');
                    tarifStatus.className = 'form-text text-success d-block mt-1';
                } else {
                    TARIF_PRODUK = 0;
                    tarifField.value = '0';
                    tarifStatus.textContent = 'Tarif dari kualifikasi: ' + (data.kualifikasi_nama || data.jabatan_nama || 'pegawai');
                    tarifStatus.className = 'form-text text-muted d-block mt-1';
                }
                
                const selectKategori = document.getElementById('kategori');
                if (data.kategori && Array.from(selectKategori.options).some(o => o.value === data.kategori)) {
                    selectKategori.value = data.kategori;
                    handleKategoriChange(data.kategori, false);
                } else {
                    selectKategori.value = '';
                    IS_PRODUKSI = true;
                }
                
                // Set tunjangan dari API setelah pegawai dipilih
                document.getElementById('tunj_jabatan').value = formatRupiah(TUNJANGAN_JABATAN);
                document.getElementById('tunj_transport').value = formatRupiah(TUNJANGAN_TRANSPORT);
                document.getElementById('tunj_konsumsi').value = formatRupiah(TUNJANGAN_KONSUMSI);
                document.getElementById('bpjs').value = formatRupiah(ASURANSI_BPJS);
                
                console.log('Gaji Pokok from API:', GAJI_POKOK);
                
                updateTotalProduk();
                hitungOtomatis();
            })
            .catch(error => {
                console.error('Error:', error);
                TARIF_PRODUK = 0;
                tarifField.value = 0;
                tarifStatus.textContent = 'Tarif dapat diisi manual jika diperlukan';
                tarifStatus.className = 'form-text text-muted d-block mt-1';
                hitungOtomatis();
            });
    }

    // Handle Kategori Change
    function handleKategoriChange(kategori, fetchTarif = true) {
        if (!kategori) {
            IS_PRODUKSI = true;
            document.getElementById('kategori_status').textContent = '';
            document.getElementById('total_produk').readOnly = false;
            document.getElementById('total_produk').style.backgroundColor = '#fff';
            return;
        }

        fetch(`/api/master-kategori/${encodeURIComponent(kategori)}`)
            .then(res => res.ok ? res.json() : null)
            .then(data => {
                if (data && data.status === 'success') {
                    IS_PRODUKSI = data.data.produksi;
                    const statusText = IS_PRODUKSI ? '✓ Kategori Produksi (BTKL)' : '✗ Kategori Gaji Tetap (BTKTI)';
                    const color = IS_PRODUKSI ? 'green' : 'orange';
                    
                    const statusEl = document.getElementById('kategori_status');
                    statusEl.textContent = statusText;
                    statusEl.style.color = color;

                    const totalField = document.getElementById('total_produk');
                    const statusTotal = document.getElementById('status_total_produk');
                    
                    if (IS_PRODUKSI) {
                        totalField.readOnly = true;
                        totalField.style.backgroundColor = '#f5f5f5';
                        statusTotal.textContent = '✓ Otomatis dari Transaksi Produksi (readonly)';
                        statusTotal.style.color = 'green';
                        updateTotalProduk(); // Fetch produksi
                    } else {
                        totalField.readOnly = false;
                        totalField.style.backgroundColor = '#fff';
                        totalField.value = 0;
                        statusTotal.textContent = '⚠ Gaji tetap - Tidak ada produksi (bisa dikosongkan/edit)';
                        statusTotal.style.color = 'orange';
                    }
                }
            })
            .catch(err => console.error('Error checking kategori:', err));
    }

    // Update Total Produk
    function updateTotalProduk() {
        if (!IS_PRODUKSI) {
            return;
        }
        
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tanggalInput = document.getElementById('tanggal_penggajian').value;
        const totalProdukField = document.getElementById('total_produk');
        const hariKerjaField = document.getElementById('hari_kerja');
        
        if (!pegawaiId || !tanggalInput) {
            totalProdukField.value = 0;
            hariKerjaField.value = 26;
            return;
        }
        
        const date = new Date(tanggalInput);
        const bulan = String(date.getMonth() + 1).padStart(2, '0');
        const tahun = date.getFullYear();
        
        console.log(`Fetching produksi for pegawai ${pegawaiId}, bulan ${bulan}/${tahun}`);
        
        fetch(`/api/pegawai/${pegawaiId}/produksi/${bulan}/${tahun}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Produksi response:', data);
                const totalProduksi = data.data.total_produksi || 0;
                const hariProduksiBulanan = data.data.hari_produksi_bulanan || 26;
                
                totalProdukField.value = totalProduksi;
                hariKerjaField.value = hariProduksiBulanan;
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
        const totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi;

        console.log('hitungOtomatis - GAJI_POKOK:', GAJI_POKOK, 'gajiPokok display:', formatRupiah(gajiPokok));

        // Total Gaji Karyawan = Gaji Pokok + Tunjangan (yang diterima karyawan)
        const totalGajiKaryawan = gajiPokok + totalTunjangan;

        // Total Biaya Perusahaan = Total Gaji Karyawan + Asuransi BPJS
        const totalBiayaPerusahaan = totalGajiKaryawan + bpjs;

        document.getElementById('display_gaji_mentah').value = formatRupiah(gajiPokok);
        document.getElementById('display_total_gaji').textContent = 'Rp ' + formatRupiah(totalGajiKaryawan);
        document.getElementById('display_total_biaya').textContent = 'Rp ' + formatRupiah(totalBiayaPerusahaan);
        document.getElementById('h-final').value = gajiPokok;
    }

    // Form Submit
    document.getElementById('formPenggajian').addEventListener('submit', function(e) {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;
        const tarifProduk = parseRupiah(document.getElementById('tarif_produk_input').value) || 0;

        if (!pegawaiId) {
            e.preventDefault();
            alert('Pilih pegawai terlebih dahulu!');
            return;
        }

        if (tarifProduk <= 0) {
            e.preventDefault();
            alert('Tarif produk harus lebih dari 0!');
            return;
        }

        if (IS_PRODUKSI && totalProduk <= 0) {
            e.preventDefault();
            alert('Total produk harus lebih dari 0 untuk kategori Produksi!');
            return;
        }

        // Unformat semua field input-rupiah sebelum submit
        document.querySelectorAll('.input-rupiah').forEach(function(input) {
            input.value = parseRupiah(input.value);
        });

        // h-final = Gaji Pokok aktual dari kualifikasi
        document.getElementById('h-final').value = GAJI_POKOK;
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Form initialized');
        clearFormState();
        setupRupiahFormatting(); // BUG 2 FIX: Setup format listener
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

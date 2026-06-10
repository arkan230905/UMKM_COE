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
                            <input type="number" id="tarif_produk_input" name="tarif_produk" class="form-control" value="0" min="0" oninput="hitungOtomatis()" required>
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
                            <input type="number" name="asuransi" id="bpjs" class="form-control" value="0" min="0" oninput="hitungOtomatis()">
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
    let IS_PRODUKSI = true;

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

    // Clear form state
    function clearFormState() {
        TARIF_PRODUK = 0;
        IS_PRODUKSI = true;
        document.getElementById('tarif_produk_input').value = 0;
        document.getElementById('kategori').value = '';
        document.getElementById('kategori_status').textContent = '';
        document.getElementById('status_total_produk').textContent = 'Otomatis jika Produksi (BTKL), atau isi manual';
        document.getElementById('total_produk').readOnly = false;
        document.getElementById('total_produk').style.backgroundColor = '#fff';
        document.getElementById('tarif_status').textContent = 'Auto-filled dari kualifikasi, atau input manual jika tidak ada';
        document.getElementById('tunj_jabatan').value = 0;
        document.getElementById('tunj_transport').value = 150000;
        document.getElementById('tunj_konsumsi').value = 375000;
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
        
        console.log('Fetching data for pegawai:', pegawaiId);
        
        fetch(`/api/pegawai/${pegawaiId}/data`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);
                
                const tarifDariKualifikasi = parseInt(data.tarif) || 0;
                
                if (tarifDariKualifikasi > 0) {
                    TARIF_PRODUK = tarifDariKualifikasi;
                    tarifField.value = tarifDariKualifikasi;
                    tarifStatus.textContent = '✓ Tarif dari kualifikasi: ' + (data.kualifikasi_nama || data.jabatan_nama || 'pegawai');
                    tarifStatus.className = 'form-text text-success d-block mt-1';
                } else {
                    TARIF_PRODUK = 0;
                    tarifField.value = 0;
                    tarifStatus.textContent = 'Tarif dari kualifikasi: ' + (data.kualifikasi_nama || data.jabatan_nama || 'pegawai');
                    tarifStatus.className = 'form-text text-muted d-block mt-1';
                }
                
                const selectKategori = document.getElementById('kategori');
                if (data.kategori && Array.from(selectKategori.options).some(o => o.value === data.kategori)) {
                    selectKategori.value = data.kategori;
                    handleKategoriChange(data.kategori, false); // false = jangan fetch tarif ulang karena sudah di atas
                } else {
                    selectKategori.value = '';
                    IS_PRODUKSI = true;
                }
                
                document.getElementById('tunj_jabatan').value = parseInt(data.tunjangan_jabatan) || 0;
                document.getElementById('tunj_transport').value = parseInt(data.tunjangan_transport) || 150000;
                document.getElementById('tunj_konsumsi').value = parseInt(data.tunjangan_konsumsi) || 375000;
                // Gunakan nilai asuransi dari API, bahkan jika 0
                document.getElementById('bpjs').value = parseInt(data.asuransi) ?? 0;
                
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
        const tunjanganJabatan = parseInt(document.getElementById('tunj_jabatan').value) || 0;
        const tunjanganTransport = parseInt(document.getElementById('tunj_transport').value) || 0;
        const tunjanganKonsumsi = parseInt(document.getElementById('tunj_konsumsi').value) || 0;
        const bpjs = parseInt(document.getElementById('bpjs').value) || 0;
        const aktifBulat = document.getElementById('aktif_bulat').checked;
        const stepBulat = parseInt(document.getElementById('step_bulat').value) || 100000;

        // Update TARIF_PRODUK dari input field
        TARIF_PRODUK = parseInt(document.getElementById('tarif_produk_input').value) || 0;

        const rataRataHari = hariKerja > 0 ? Math.round(totalProduk / hariKerja) : 0;
        document.getElementById('rata_rata_hari').value = formatRupiah(rataRataHari);

        const gajiMentah = totalProduk * TARIF_PRODUK;

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
        const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;
        const tarifProduk = parseInt(document.getElementById('tarif_produk_input').value) || 0;

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

        const gajiFinal = parseRupiah(document.getElementById('display_gaji_final').value);
        document.getElementById('h-final').value = gajiFinal;
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Form initialized');
        clearFormState();
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

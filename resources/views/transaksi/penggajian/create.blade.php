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
                        <label class="form-label">Tarif / Produk</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="display_tarif" class="form-control" readonly value="" placeholder="Pilih pegawai terlebih dahulu">
                        </div>
                        <small class="form-text text-muted d-block mt-1" id="tarif_status"></small>
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
    let TARIF_PRODUK = 0; // Start with 0, not 729

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

    // Clear form state (untuk multi-tenant isolation)
    function clearFormState() {
        TARIF_PRODUK = 0;
        
        // Clear tarif field
        document.getElementById('display_tarif').value = '';
        document.getElementById('display_tarif').placeholder = 'Pilih pegawai terlebih dahulu';
        document.getElementById('tarif_status').textContent = '';
        
        // Clear tunjangan ke default
        document.getElementById('tunj_jabatan').value = 0;
        document.getElementById('tunj_transport').value = 150000;
        document.getElementById('tunj_konsumsi').value = 375000;
        document.getElementById('bpjs').value = 100000;
        
        // Clear calculation fields
        document.getElementById('display_gaji_mentah').value = '0';
        document.getElementById('display_gaji_final').value = '0';
        document.getElementById('display_total_gaji').textContent = 'Rp 0';
        document.getElementById('rata_rata_hari').value = '0';
        
        // Clear pembulatan
        document.getElementById('aktif_bulat').checked = false;
        document.getElementById('panel_bulat').style.display = 'none';
        document.getElementById('info_selisih').style.display = 'none';
        
        // Clear hidden input
        document.getElementById('h-final').value = 0;
    }

    // Update Tarif dan Total Produk saat pegawai dipilih
    function updateTarif() {
        const select = document.getElementById('pegawai_id');
        const pegawaiId = select.value;
        const tarifField = document.getElementById('display_tarif');
        const tarifStatus = document.getElementById('tarif_status');
        
        // Jika pegawai dikosongkan
        if (!pegawaiId) {
            TARIF_PRODUK = 0;
            tarifField.value = '';
            tarifField.placeholder = 'Pilih pegawai terlebih dahulu';
            tarifStatus.textContent = '';
            
            // Clear tunjangan
            document.getElementById('tunj_jabatan').value = 0;
            document.getElementById('tunj_transport').value = 150000;
            document.getElementById('tunj_konsumsi').value = 375000;
            document.getElementById('bpjs').value = 100000;
            
            // Clear total produk
            document.getElementById('total_produk').value = 0;
            document.getElementById('hari_kerja').value = 26;
            
            hitungOtomatis();
            return;
        }
        
        console.log('=== updateTarif START for pegawai:', pegawaiId);
        
        // Fetch data kualifikasi pegawai dari API
        fetch(`/api/pegawai/${pegawaiId}/data`)
            .then(response => {
                console.log('getEmployeeData Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: Gagal mengambil data pegawai`);
                }
                return response.json();
            })
            .then(data => {
                console.log('getEmployeeData Response:', data);
                
                // Set tarif dari kualifikasi - PENTING: convert ke integer
                TARIF_PRODUK = parseInt(data.tarif) || 0;
                
                console.log('TARIF_PRODUK set to:', TARIF_PRODUK);
                
                if (TARIF_PRODUK > 0) {
                    tarifField.value = formatRupiah(TARIF_PRODUK);
                    tarifStatus.textContent = '✓ Tarif dari kualifikasi pegawai: ' + data.jabatan_nama;
                    tarifStatus.className = 'form-text text-success d-block mt-1';
                } else {
                    tarifField.value = '';
                    tarifStatus.textContent = '⚠ Pegawai tidak memiliki tarif di kualifikasi';
                    tarifStatus.className = 'form-text text-warning d-block mt-1';
                }
                
                // Update tunjangan dari kualifikasi - PENTING: convert ke integer
                const tunjanganJabatan = parseInt(data.tunjangan_jabatan) || 0;
                const tunjanganTransport = parseInt(data.tunjangan_transport) || 150000;
                const tunjanganKonsumsi = parseInt(data.tunjangan_konsumsi) || 375000;
                const asuransi = parseInt(data.asuransi) || 100000;
                
                console.log('Setting tunjangan:', {
                    jabatan: tunjanganJabatan,
                    transport: tunjanganTransport,
                    konsumsi: tunjanganKonsumsi,
                    asuransi: asuransi
                });
                
                document.getElementById('tunj_jabatan').value = tunjanganJabatan;
                document.getElementById('tunj_transport').value = tunjanganTransport;
                document.getElementById('tunj_konsumsi').value = tunjanganKonsumsi;
                document.getElementById('bpjs').value = asuransi;
                
                // PENTING: Fetch total produksi SETELAH tarif berhasil di-set
                console.log('Calling updateTotalProduk...');
                updateTotalProduk();
                
                console.log('=== updateTarif END');
            })
            .catch(error => {
                console.error('Error fetching tarif:', error);
                TARIF_PRODUK = 0;
                tarifField.value = '';
                tarifStatus.textContent = '✗ Error: ' + error.message;
                tarifStatus.className = 'form-text text-danger d-block mt-1';
                
                // Clear total produk jika ada error
                document.getElementById('total_produk').value = 0;
                document.getElementById('hari_kerja').value = 26;
                
                hitungOtomatis();
            });
    }

    // Update Total Produk dari produksi bulan ini
    // PENTING: Ambil dari kolom jumlah_produksi_bulanan di tabel produksis
    function updateTotalProduk() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tanggalInput = document.getElementById('tanggal_penggajian').value;
        const totalProdukField = document.getElementById('total_produk');
        const hariKerjaField = document.getElementById('hari_kerja');
        
        console.log('=== updateTotalProduk START');
        console.log('pegawaiId:', pegawaiId);
        console.log('tanggalInput:', tanggalInput);
        
        if (!pegawaiId) {
            console.warn('pegawaiId is empty, resetting fields');
            totalProdukField.value = 0;
            hariKerjaField.value = 26;
            hitungOtomatis();
            return;
        }
        
        if (!tanggalInput) {
            console.warn('tanggalInput is empty, using today date');
            // Use today's date if not set
            const today = new Date();
            const bulan = String(today.getMonth() + 1).padStart(2, '0');
            const tahun = today.getFullYear();
        } else {
            // Extract bulan dan tahun dari tanggal penggajian
            const date = new Date(tanggalInput);
            const bulan = String(date.getMonth() + 1).padStart(2, '0');
            const tahun = date.getFullYear();
            
            console.log(`Fetching TOTAL PRODUK BULAN untuk pegawai ${pegawaiId}, bulan ${bulan}/${tahun}`);
            
            // Fetch total produksi PER BULAN dari kolom jumlah_produksi_bulanan
            fetch(`/api/pegawai/${pegawaiId}/produksi/${bulan}/${tahun}`)
                .then(response => {
                    console.log('getTotalProduksiByMonth Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('getTotalProduksiByMonth Response:', data);
                    
                    // Ambil total_produksi dari kolom jumlah_produksi_bulanan
                    const totalProduksi = parseInt(data.data.total_produksi) || 0;
                    const hariProduksiBulanan = parseInt(data.data.hari_produksi_bulanan) || 26;
                    
                    console.log('Parsed values:', {
                        totalProduksi: totalProduksi,
                        hariProduksiBulanan: hariProduksiBulanan
                    });
                    
                    if (totalProduksi === 0) {
                        console.warn('Total produksi adalah 0 - tidak ada data produksi untuk bulan ini');
                        totalProdukField.value = 0;
                        hariKerjaField.value = 26;
                    } else {
                        totalProdukField.value = totalProduksi;
                        hariKerjaField.value = hariProduksiBulanan;
                        console.log('✓ Total produksi BULAN set to:', totalProduksi, 'Hari kerja:', hariProduksiBulanan);
                    }
                    
                    hitungOtomatis();
                    console.log('=== updateTotalProduk END (success)');
                })
                .catch(error => {
                    console.error('Error fetching total produksi:', error);
                    totalProdukField.value = 0;
                    hariKerjaField.value = 26;
                    hitungOtomatis();
                    console.log('=== updateTotalProduk END (error)');
                });
        }
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

        if (TARIF_PRODUK <= 0) {
            e.preventDefault();
            alert('Pegawai tidak memiliki tarif di kualifikasi. Harap set tarif terlebih dahulu!');
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

    // Initialize - CRITICAL untuk multi-tenant isolation
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== FORM INITIALIZATION START ===');
        console.log('Timestamp:', new Date().toISOString());
        console.log('Current User ID:', document.querySelector('meta[name="user-id"]')?.content || 'unknown');
        
        // STEP 1: Clear browser cache/storage
        try {
            sessionStorage.clear();
            localStorage.clear();
            console.log('✓ Browser storage cleared');
        } catch (e) {
            console.warn('Could not clear storage:', e);
        }
        
        // STEP 2: Force clear all form fields
        const tarifField = document.getElementById('display_tarif');
        const pegawaiSelect = document.getElementById('pegawai_id');
        
        // Clear tarif field dengan force - multiple methods untuk memastikan
        tarifField.value = '';
        tarifField.textContent = '';
        tarifField.innerHTML = '';
        tarifField.setAttribute('value', '');
        tarifField.placeholder = 'Pilih pegawai terlebih dahulu';
        
        // Remove any inline styles that might show old value
        tarifField.style.display = '';
        tarifField.style.visibility = '';
        
        // Clear pegawai selection
        pegawaiSelect.value = '';
        pegawaiSelect.selectedIndex = 0;
        
        console.log('✓ Tarif field cleared:', {
            value: tarifField.value,
            placeholder: tarifField.placeholder,
            innerHTML: tarifField.innerHTML
        });
        
        // STEP 3: Reset TARIF_PRODUK variable
        TARIF_PRODUK = 0;
        console.log('✓ TARIF_PRODUK reset to 0');
        
        // STEP 4: Clear form state
        clearFormState();
        console.log('✓ Form state cleared');
        
        // STEP 5: Hitung otomatis dengan state yang sudah di-clear
        hitungOtomatis();
        console.log('✓ Calculation updated');
        
        // STEP 6: Verify final state multiple times
        const verifyState = function(attempt = 1) {
            const finalTarifValue = document.getElementById('display_tarif').value;
            const finalPegawaiValue = document.getElementById('pegawai_id').value;
            
            console.log(`=== FINAL STATE VERIFICATION (Attempt ${attempt}) ===`);
            console.log('Tarif field value:', finalTarifValue);
            console.log('Pegawai field value:', finalPegawaiValue);
            console.log('TARIF_PRODUK variable:', TARIF_PRODUK);
            
            if (finalTarifValue !== '') {
                console.error(`⚠ WARNING (Attempt ${attempt}): Tarif field is not empty! Force clearing...`);
                tarifField.value = '';
                tarifField.textContent = '';
                tarifField.innerHTML = '';
                TARIF_PRODUK = 0;
                
                // Retry verification
                if (attempt < 3) {
                    setTimeout(() => verifyState(attempt + 1), 50);
                }
            } else {
                console.log('✓ Tarif field is properly cleared');
            }
        };
        
        // Run verification at multiple intervals
        verifyState(1);
        setTimeout(() => verifyState(2), 100);
        setTimeout(() => verifyState(3), 200);
        
        console.log('=== FORM INITIALIZATION COMPLETE ===');
    });

    // BONUS: Clear state saat window focus (jika user switch tab/window)
    window.addEventListener('focus', function() {
        // Verify pegawai selection is still valid
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tarifValue = document.getElementById('display_tarif').value;
        
        if (!pegawaiId && tarifValue) {
            // Pegawai tidak dipilih tapi tarif masih ada - clear state
            console.log('⚠ Inconsistent state detected on focus - clearing');
            clearFormState();
            document.getElementById('pegawai_id').value = '';
            document.getElementById('display_tarif').value = '';
            TARIF_PRODUK = 0;
            console.log('✓ Form state cleared on window focus (multi-tenant safety)');
        }
    });
</script>
@endsection

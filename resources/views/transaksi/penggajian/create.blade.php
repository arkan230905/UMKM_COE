@extends('layouts.app')

@section('title', 'Tambah Penggajian')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-plus-circle"></i> Tambah Penggajian</h3>

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

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <form action="{{ route('transaksi.penggajian.store') }}" method="POST" id="formPenggajian">
                @csrf

                <!-- Hidden fields untuk data pegawai - akan diisi otomatis dari kualifikasi -->
                <input type="hidden" name="pegawai_selected" id="hidden_pegawai_selected" value="0">
                <input type="hidden" name="gaji_pokok" id="hidden_gaji_pokok" value="0">
                <input type="hidden" name="tarif_per_jam" id="hidden_tarif_per_jam" value="0">
                <input type="hidden" name="tunjangan_jabatan" id="hidden_tunjangan_jabatan" value="0">
                <input type="hidden" name="tunjangan_transport" id="hidden_tunjangan_transport" value="0">
                <input type="hidden" name="tunjangan_konsumsi" id="hidden_tunjangan_konsumsi" value="0">
                <input type="hidden" name="total_tunjangan" id="hidden_tunjangan" value="0">
                <input type="hidden" name="asuransi" id="hidden_asuransi" value="0">
                <input type="hidden" name="jenis_pegawai" id="hidden_jenis_pegawai" value="">
                <input type="hidden" name="total_jam_kerja" id="hidden_total_jam_kerja" value="0">
                <!-- Note: Data gaji akan diambil langsung dari kualifikasi dan presensi, bukan dari form -->>

                <!-- Informasi Pegawai -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="pegawai_id" class="form-label fw-bold">
                            <i class="bi bi-person-badge"></i> Pilih Pegawai *
                        </label>
                        <select name="pegawai_id" id="pegawai_id" class="form-select form-select-lg" required onchange="loadPegawaiData()">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach ($pegawais as $pegawai)
                                @php
                                    $jabatan = $pegawai->jabatanRelasi;
                                    $gajiPokok = $jabatan ? ($jabatan->gaji_pokok ?? 0) : ($pegawai->gaji_pokok ?? 0);
                                    $tarif = $jabatan ? ($jabatan->tarif_per_jam ?? 0) : ($pegawai->tarif_per_jam ?? 0);
                                    $tunjanganJabatan = $jabatan ? ($jabatan->tunjangan ?? 0) : ($pegawai->tunjangan ?? 0);
                                    $tunjanganTransport = $jabatan ? ($jabatan->tunjangan_transport ?? 0) : ($pegawai->tunjangan_transport ?? 0);
                                    $tunjanganKonsumsi = $jabatan ? ($jabatan->tunjangan_konsumsi ?? 0) : ($pegawai->tunjangan_konsumsi ?? 0);
                                    $asuransi = $jabatan ? ($jabatan->asuransi ?? 0) : ($pegawai->asuransi ?? 0);
                                @endphp
                                <option value="{{ $pegawai->id }}"
                                        data-jenis="{{ strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl') }}"
                                        data-gaji-pokok="{{ $gajiPokok }}"
                                        data-tarif="{{ $tarif }}"
                                        data-tunjangan-jabatan="{{ $tunjanganJabatan }}"
                                        data-tunjangan-transport="{{ $tunjanganTransport }}"
                                        data-tunjangan-konsumsi="{{ $tunjanganKonsumsi }}"
                                        data-asuransi="{{ $asuransi }}">
                                    {{ $pegawai->nama }} - {{ $pegawai->jabatan_nama ?? 'Staff' }} ({{ strtoupper($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'BTKTL') }})
                                    [Gaji: {{ number_format($gajiPokok, 0, ',', '.') }}, Tarif: {{ number_format($tarif, 0, ',', '.') }}]
                                </option>
                            @endforeach
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
                               class="form-control form-control-lg" value="{{ date('Y-m-d') }}" required onchange="loadJamKerja()">
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

                <!-- Komponen Gaji (Otomatis dari Kualifikasi dan Presensi) -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Komponen Gaji</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- BTKL Fields -->
                            <div class="col-md-6" id="field-tarif" style="display:none;">
                                <label for="display_tarif" class="form-label">Tarif per Jam</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tarif" class="form-control" readonly value="0">
                                </div>
                            </div>

                            <div class="col-md-6" id="field-jam-kerja" style="display:none;">
                                <label for="display_jam_kerja" class="form-label">Total Jam Kerja (Bulan Ini)</label>
                                <div class="input-group">
                                    <input type="text" id="display_jam_kerja" class="form-control" readonly value="0">
                                    <span class="input-group-text">Jam</span>
                                </div>
                            </div>

                            <div class="col-md-6" id="field-gaji-dasar" style="display:none;">
                                <label for="display_gaji_dasar" class="form-label fw-bold text-primary">Total Gaji (diluar tunjangan)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_gaji_dasar" class="form-control fw-bold" readonly value="0">
                                </div>
                                <small class="text-success"><i class="bi bi-calculator"></i> Tarif per Jam × Total Jam Kerja</small>
                            </div>

                            <!-- BTKTL Fields -->
                            <div class="col-md-6" id="field-gaji-pokok">
                                <label for="display_gaji_pokok" class="form-label fw-bold">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_gaji_pokok" class="form-control fw-bold" readonly value="0">
                                </div>
                            </div>

                            <!-- Tunjangan Detail -->
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
                                <label for="display_asuransi" class="form-label">Asuransi / BPJS</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_asuransi" class="form-control" readonly value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Manual (Bonus & Potongan) -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Input Manual</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="bonus" class="form-label">Bonus</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" name="bonus" id="bonus"
                                           class="form-control" value="0" onchange="hitungTotal()">
                                </div>
                                <small class="text-muted">Bonus kinerja, lembur, dll</small>
                                @error('bonus')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="potongan" class="form-label">Potongan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" name="potongan" id="potongan"
                                           class="form-control" value="0" onchange="hitungTotal()">
                                </div>
                                <small class="text-muted">Keterlambatan, pinjaman, dll</small>
                                @error('potongan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Gaji -->
                <div class="card border-0 mb-4" style="background-color: #f8f9fa;">
                    <div class="card-body text-center py-4">
                        <h5 class="mb-2 text-dark fw-bold">Total Gaji</h5>
                        <h2 class="mb-0 fw-bold" id="display_total" style="color: #333; font-size: 2rem;">Rp 0,00</h2>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn" onclick="return debugFormSubmission()">
                        <i class="bi bi-save"></i> Simpan Penggajian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Debug form submission
function debugFormSubmission() {
    console.log('=== FORM SUBMISSION DEBUG ===');
    console.log('Hidden field values:');
    console.log('gaji_pokok:', document.getElementById('hidden_gaji_pokok').value);
    console.log('tarif_per_jam:', document.getElementById('hidden_tarif_per_jam').value);
    console.log('tunjangan:', document.getElementById('hidden_tunjangan').value);
    console.log('asuransi:', document.getElementById('hidden_asuransi').value);
    console.log('total_jam_kerja:', document.getElementById('hidden_total_jam_kerja').value);
    console.log('jenis_pegawai:', document.getElementById('hidden_jenis_pegawai').value);
    console.log('pegawaiData object:', pegawaiData);
    
    // Check if employee data is loaded
    const pegawaiId = document.getElementById('pegawai_id').value;
    if (!pegawaiId) {
        alert('Please select an employee first!');
        return false;
    }
    
    // Check if data is loaded for BTKL employees
    const jenisPegawai = document.getElementById('hidden_jenis_pegawai').value;
    if (jenisPegawai === 'btkl') {
        const tarifPerJam = parseFloat(document.getElementById('hidden_tarif_per_jam').value);
        const totalJamKerja = parseFloat(document.getElementById('hidden_total_jam_kerja').value);
        
        if (tarifPerJam === 0) {
            alert('Tarif per jam is 0. Please check employee data.');
            return false;
        }
        
        if (totalJamKerja === 0) {
            alert('Total jam kerja is 0. Please check attendance data.');
            return false;
        }
    }
    
    // Allow form to submit
    return true;
}
// Data pegawai
let pegawaiData = {
    jenis: 'btktl',
    gajiPokok: 0,
    tarif: 0,
    tunjanganJabatan: 0,
    tunjanganTransport: 0,
    tunjanganKonsumsi: 0,
    totalTunjangan: 0,
    asuransi: 0,
    jamKerja: 0,
    gajiDasar: 0
};

// Load data pegawai dari KUALIFIKASI dan PRESENSI
function loadPegawaiData() {
    const select = document.getElementById('pegawai_id');
    const pegawaiId = select.value;
    
    if (pegawaiId) {
        console.log('Loading data for pegawai ID:', pegawaiId);
        
        // Show loading state
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        
        // Get employee data from kualifikasi (jabatan)
        fetch(`/api/pegawai/${pegawaiId}/data`)
            .then(response => {
                console.log('API Response status:', response.status);
                console.log('API Response URL:', response.url);
                
                if (!response.ok) {
                    // Log more details about the error
                    return response.text().then(text => {
                        console.error('API Error Response:', text);
                        throw new Error(`API request failed: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Data dari KUALIFIKASI:', data);
                
                if (data.error) {
                    console.error('API Error:', data.message);
                    throw new Error(data.message);
                }
                
                // Update pegawaiData with KUALIFIKASI values
                pegawaiData.jenis = data.jenis || 'btktl';
                pegawaiData.gajiPokok = parseFloat(data.gaji_pokok) || 0;
                pegawaiData.tarif = parseFloat(data.tarif) || 0;
                pegawaiData.tunjanganJabatan = parseFloat(data.tunjangan_jabatan) || 0;
                pegawaiData.tunjanganTransport = parseFloat(data.tunjangan_transport) || 0;
                pegawaiData.tunjanganKonsumsi = parseFloat(data.tunjangan_konsumsi) || 0;
                pegawaiData.totalTunjangan = pegawaiData.tunjanganJabatan + pegawaiData.tunjanganTransport + pegawaiData.tunjanganKonsumsi;
                pegawaiData.asuransi = parseFloat(data.asuransi) || 0;

                // Update hidden fields
                document.getElementById('hidden_gaji_pokok').value = pegawaiData.gajiPokok;
                document.getElementById('hidden_tarif_per_jam').value = pegawaiData.tarif;
                document.getElementById('hidden_tunjangan_jabatan').value = pegawaiData.tunjanganJabatan;
                document.getElementById('hidden_tunjangan_transport').value = pegawaiData.tunjanganTransport;
                document.getElementById('hidden_tunjangan_konsumsi').value = pegawaiData.tunjanganKonsumsi;
                document.getElementById('hidden_tunjangan').value = pegawaiData.totalTunjangan;
                document.getElementById('hidden_asuransi').value = pegawaiData.asuransi;
                document.getElementById('hidden_jenis_pegawai').value = pegawaiData.jenis;
                
                // System updates pegawaiData silently
                
                // Update display fields (READ-ONLY)
                updateDisplayFields();
                
                // Load jam kerja from PRESENSI if BTKL
                if (pegawaiData.jenis === 'btkl') {
                    loadJamKerjaFromPresensi();
                } else {
                    // For BTKTL, gaji dasar = gaji pokok
                    pegawaiData.gajiDasar = pegawaiData.gajiPokok;
                    document.getElementById('display_gaji_dasar').value = pegawaiData.gajiDasar.toLocaleString('id-ID');
                    enableSubmitButton();
                }
            })
            .catch(error => {
                // Handle error silently - system uses default values
                resetPegawaiData();
            });
    } else {
        // Reset if no employee selected
        resetPegawaiData();
    }
}

// Update display fields (READ-ONLY - data dari kualifikasi dan presensi)
function updateDisplayFields() {
    // Update display fields
    document.getElementById('display_tarif').value = pegawaiData.tarif.toLocaleString('id-ID');
    document.getElementById('display_jam_kerja').value = pegawaiData.jamKerja.toLocaleString('id-ID');
    document.getElementById('display_gaji_dasar').value = pegawaiData.gajiDasar.toLocaleString('id-ID');
    document.getElementById('display_gaji_pokok').value = pegawaiData.gajiPokok.toLocaleString('id-ID');
    document.getElementById('display_tunjangan_jabatan').value = pegawaiData.tunjanganJabatan.toLocaleString('id-ID');
    document.getElementById('display_tunjangan_transport').value = pegawaiData.tunjanganTransport.toLocaleString('id-ID');
    document.getElementById('display_tunjangan_konsumsi').value = pegawaiData.tunjanganKonsumsi.toLocaleString('id-ID');
    document.getElementById('display_total_tunjangan').value = pegawaiData.totalTunjangan.toLocaleString('id-ID');
    document.getElementById('display_asuransi').value = pegawaiData.asuransi.toLocaleString('id-ID');

    // Show/hide fields based on employee type
    updateFieldVisibility();

    // Recalculate total
    hitungTotal();
}

// Load jam kerja dari PRESENSI (bukan dari form)
function loadJamKerjaFromPresensi() {
    console.log('=== Loading jam kerja from PRESENSI ===');
    
    const pegawaiId = document.getElementById('pegawai_id').value;
    const tanggal = document.getElementById('tanggal_penggajian').value;

    if (pegawaiId && tanggal) {
        // Parse tanggal untuk mendapatkan bulan dan tahun
        const date = new Date(tanggal);
        const month = date.getMonth() + 1;
        const year = date.getFullYear();

        console.log('Getting presensi for:', { pegawaiId, month, year });

        const apiUrl = `/api/presensi/jam-kerja?pegawai_id=${pegawaiId}&month=${month}&year=${year}`;
        console.log('API URL:', apiUrl);

        // Show loading indicator with timeout
        const jamKerjaField = document.getElementById('display_jam_kerja');
        jamKerjaField.value = 'Loading...';
        
        // Set timeout to prevent infinite loading
        const timeoutId = setTimeout(() => {
            jamKerjaField.value = '0';
            pegawaiData.jamKerja = 0;
            pegawaiData.gajiDasar = pegawaiData.tarif * pegawaiData.jamKerja;
            updateFormFields();
            enableSubmitButton();
        }, 5000); // 5 seconds timeout

        // Fetch jam kerja dari PRESENSI with AbortController for timeout
        const controller = new AbortController();
        const timeoutId2 = setTimeout(() => controller.abort(), 10000); // 10 seconds fetch timeout
        
        fetch(apiUrl, { signal: controller.signal })
            .then(response => {
                clearTimeout(timeoutId2);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                clearTimeout(timeoutId);
                
                if (data.error) {
                    pegawaiData.jamKerja = 0;
                } else {
                    pegawaiData.jamKerja = parseFloat(data.total_jam) || 0;
                }
                
                // Calculate gaji dasar for BTKL (tarif dari kualifikasi × jam dari presensi)
                pegawaiData.gajiDasar = pegawaiData.tarif * pegawaiData.jamKerja;

                // Update hidden field for total jam kerja
                document.getElementById('hidden_total_jam_kerja').value = pegawaiData.jamKerja;

                // Update display fields
                updateDisplayFields();

                // Enable submit button
                enableSubmitButton();

                console.log('Final calculation:', {
                    tarif_dari_kualifikasi: pegawaiData.tarif,
                    jam_dari_presensi: pegawaiData.jamKerja,
                    gaji_dasar: pegawaiData.gajiDasar,
                    tunjangan_dari_kualifikasi: pegawaiData.totalTunjangan,
                    asuransi_dari_kualifikasi: pegawaiData.asuransi
                });
            })
            .catch(error => {
                console.error('Error loading presensi data:', error);
                alert('Gagal memuat data presensi: ' + error.message);
                pegawaiData.jamKerja = 0;
                pegawaiData.gajiDasar = 0;
                
                // Update display fields
                updateDisplayFields();
                
                // Enable submit button even with error
                enableSubmitButton();
            });
    } else {
        console.log('Missing pegawaiId or tanggal for presensi lookup');
        enableSubmitButton();
    }
}

// Reset pegawai data
function resetPegawaiData() {
    pegawaiData = {
        jenis: 'btktl',
        gajiPokok: 0,
        tarif: 0,
        tunjanganJabatan: 0,
        tunjanganTransport: 0,
        tunjanganKonsumsi: 0,
        totalTunjangan: 0,
        asuransi: 0,
        jamKerja: 0,
        gajiDasar: 0
    };

    // Reset hidden fields
    document.getElementById('hidden_gaji_pokok').value = 0;
    document.getElementById('hidden_tarif_per_jam').value = 0;
    document.getElementById('hidden_tunjangan_jabatan').value = 0;
    document.getElementById('hidden_tunjangan_transport').value = 0;
    document.getElementById('hidden_tunjangan_konsumsi').value = 0;
    document.getElementById('hidden_tunjangan').value = 0;
    document.getElementById('hidden_asuransi').value = 0;
    document.getElementById('hidden_jenis_pegawai').value = '';
    document.getElementById('hidden_total_jam_kerja').value = 0;

    // Reset display fields
    document.getElementById('display_tarif').value = '0';
    document.getElementById('display_jam_kerja').value = '0';
    document.getElementById('display_gaji_dasar').value = '0';
    document.getElementById('display_gaji_pokok').value = '0';
    document.getElementById('display_tunjangan_jabatan').value = '0';
    document.getElementById('display_tunjangan_transport').value = '0';
    document.getElementById('display_tunjangan_konsumsi').value = '0';
    document.getElementById('display_total_tunjangan').value = '0';
    document.getElementById('display_asuransi').value = '0';

    // Show BTKTL fields by default
    document.getElementById('field-gaji-pokok').style.display = 'block';
    document.getElementById('field-tarif').style.display = 'none';
    document.getElementById('field-jam-kerja').style.display = 'none';
    document.getElementById('field-gaji-dasar').style.display = 'none';

    hitungTotal();
}

// Fallback function to load from static data attributes
function loadStaticPegawaiData() {
    const select = document.getElementById('pegawai_id');
    const option = select.options[select.selectedIndex];

    if (option.value) {
        pegawaiData.jenis = option.dataset.jenis || 'btktl';
        pegawaiData.gajiPokok = parseFloat(option.dataset.gajiPokok) || 0;
        pegawaiData.tarif = parseFloat(option.dataset.tarif) || 0;
        // Calculate total tunjangan from individual components
        const tunjanganJabatan = parseFloat(option.dataset.tunjanganJabatan) || 0;
        const tunjanganTransport = parseFloat(option.dataset.tunjanganTransport) || 0;
        const tunjanganKonsumsi = parseFloat(option.dataset.tunjanganKonsumsi) || 0;
        pegawaiData.tunjanganJabatan = tunjanganJabatan;
        pegawaiData.tunjanganTransport = tunjanganTransport;
        pegawaiData.tunjanganKonsumsi = tunjanganKonsumsi;
        pegawaiData.totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi || parseFloat(option.dataset.tunjangan) || 0;
        pegawaiData.asuransi = parseFloat(option.dataset.asuransi) || 0;

        // Update hidden fields
        document.getElementById('hidden_gaji_pokok').value = pegawaiData.gajiPokok;
        document.getElementById('hidden_tarif_per_jam').value = pegawaiData.tarif;
        document.getElementById('hidden_tunjangan_jabatan').value = pegawaiData.tunjanganJabatan;
        document.getElementById('hidden_tunjangan_transport').value = pegawaiData.tunjanganTransport;
        document.getElementById('hidden_tunjangan_konsumsi').value = pegawaiData.tunjanganKonsumsi;
        document.getElementById('hidden_tunjangan').value = pegawaiData.totalTunjangan;
        document.getElementById('hidden_asuransi').value = pegawaiData.asuransi;
        document.getElementById('hidden_jenis_pegawai').value = pegawaiData.jenis;
        document.getElementById('hidden_total_jam_kerja').value = pegawaiData.jamKerja;

        // Update display
        updateDisplayFields();

        // Load jam kerja if BTKL
        if (pegawaiData.jenis === 'btkl') {
            loadJamKerja();
        } else {
            // Calculate gaji dasar for BTKTL
            pegawaiData.gajiDasar = pegawaiData.gajiPokok;
            updateDisplayFields();
        }
    } else {
        // Reset hidden fields
        document.getElementById('hidden_gaji_pokok').value = 0;
        document.getElementById('hidden_tarif_per_jam').value = 0;
        document.getElementById('hidden_tunjangan_jabatan').value = 0;
        document.getElementById('hidden_tunjangan_transport').value = 0;
        document.getElementById('hidden_tunjangan_konsumsi').value = 0;
        document.getElementById('hidden_tunjangan').value = 0;
        document.getElementById('hidden_asuransi').value = 0;
        document.getElementById('hidden_jenis_pegawai').value = '';
        document.getElementById('hidden_total_jam_kerja').value = 0;

        // Reset pegawaiData
        pegawaiData.gajiPokok = 0;
        pegawaiData.tarif = 0;
        pegawaiData.tunjanganJabatan = 0;
        pegawaiData.tunjanganTransport = 0;
        pegawaiData.tunjanganKonsumsi = 0;
        pegawaiData.totalTunjangan = 0;
        pegawaiData.asuransi = 0;
        pegawaiData.jamKerja = 0;
        pegawaiData.gajiDasar = 0;

        updateDisplayFields();
    }
}

// Load jam kerja dari presensi
function loadJamKerja() {
    console.log('=== loadJamKerja() called ===');

    const pegawaiId = document.getElementById('pegawai_id').value;
    const tanggal = document.getElementById('tanggal_penggajian').value;

    console.log('pegawaiId:', pegawaiId);
    console.log('tanggal:', tanggal);
    console.log('pegawaiData.jenis:', pegawaiData.jenis);

    if (pegawaiId && tanggal && pegawaiData.jenis === 'btkl') {
        // Parse tanggal untuk mendapatkan bulan dan tahun
        const date = new Date(tanggal);
        const month = date.getMonth() + 1;
        const year = date.getFullYear();

        console.log('Parsed date - month:', month, 'year:', year);

        const apiUrl = `/api/presensi/jam-kerja?pegawai_id=${pegawaiId}&month=${month}&year=${year}`;
        console.log('API URL:', apiUrl);

        // Fetch jam kerja dari server
        fetch(apiUrl)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);

                if (data.error) {
                    console.error('API Error:', data.message);
                    pegawaiData.jamKerja = 0;
                } else {
                    pegawaiData.jamKerja = parseFloat(data.total_jam) || 0;
                }

                // Calculate gaji dasar for BTKL
                pegawaiData.gajiDasar = pegawaiData.tarif * pegawaiData.jamKerja;

                // Update display fields
                updateDisplayFields();

                // Enable submit button
                enableSubmitButton();
            })
            .catch(error => {
                console.error('Error loading presensi data:', error);
                pegawaiData.jamKerja = 0;
                pegawaiData.gajiDasar = 0;

                // Update display fields
                updateDisplayFields();

                // Enable submit button
                enableSubmitButton();
            });
    } else {
        // Reset jam kerja for non-BTKL employees
        pegawaiData.jamKerja = 0;
        pegawaiData.gajiDasar = pegawaiData.gajiPokok; // Use gaji pokok for BTKTL

        // Update display fields
        updateDisplayFields();

        // Enable submit button
        enableSubmitButton();
    }
}

// Enable submit button and restore normal text
function enableSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-save"></i> Simpan Penggajian';
}

// Update field visibility based on employee type
function updateFieldVisibility() {
    if (pegawaiData.jenis === 'btkl') {
        // Show BTKL fields, hide BTKTL fields
        document.getElementById('field-gaji-pokok').style.display = 'none';
        document.getElementById('field-tarif').style.display = 'block';
        document.getElementById('field-jam-kerja').style.display = 'block';
        document.getElementById('field-gaji-dasar').style.display = 'block';
    } else {
        // Show BTKTL fields, hide BTKL fields
        document.getElementById('field-gaji-pokok').style.display = 'block';
        document.getElementById('field-tarif').style.display = 'none';
        document.getElementById('field-jam-kerja').style.display = 'none';
        document.getElementById('field-gaji-dasar').style.display = 'none';
    }
}

// Hitung total gaji
function hitungTotal() {
    const bonus = parseFloat(document.getElementById('bonus').value) || 0;
    const potongan = parseFloat(document.getElementById('potongan').value) || 0;

    let total = 0;

    if (pegawaiData.jenis === 'btkl') {
        // BTKL = (Tarif × Jam Kerja) + Total Tunjangan + Asuransi + Bonus - Potongan
        total = pegawaiData.gajiDasar + pegawaiData.totalTunjangan + pegawaiData.asuransi + bonus - potongan;
    } else {
        // BTKTL = Gaji Pokok + Total Tunjangan + Asuransi + Bonus - Potongan
        total = pegawaiData.gajiPokok + pegawaiData.totalTunjangan + pegawaiData.asuransi + bonus - potongan;
    }

    // Format dengan 2 desimal dan separator Indonesia
    const formattedTotal = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(total);

    document.getElementById('display_total').textContent = 'Rp ' + formattedTotal;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    hitungTotal();

    // Add event listeners for real-time calculation
    document.getElementById('bonus').addEventListener('input', hitungTotal);
    document.getElementById('potongan').addEventListener('input', hitungTotal);

    // Add event listener for tanggal change to reload presensi data
    document.getElementById('tanggal_penggajian').addEventListener('change', function() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        if (pegawaiId && pegawaiData.jenis === 'btkl') {
            // Reload jam kerja from presensi for new tanggal
            loadJamKerjaFromPresensi();
        }
    });

    // Debug form submission
    document.getElementById('formPenggajian').addEventListener('submit', function(e) {
        console.log('Form data yang akan dikirim:');
        const formData = new FormData(this);
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
        
        // Validasi required fields
        const pegawaiId = document.getElementById('pegawai_id').value;
        const tanggal = document.getElementById('tanggal_penggajian').value;
        const coaKasbank = document.getElementById('coa_kasbank').value;
        
        if (!pegawaiId) {
            alert('Pilih pegawai terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        
        if (!tanggal) {
            alert('Pilih tanggal penggajian terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        
        if (!coaKasbank) {
            alert('Pilih akun kas/bank terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        
        // Disable submit button untuk prevent double submit
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
        
        console.log('Form validation passed, submitting...');
    });
});
</script>
@endsection

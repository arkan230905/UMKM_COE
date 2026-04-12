@extends('layouts.app')

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

                <!-- Hidden fields untuk data pegawai -->
                <input type="hidden" name="gaji_pokok" id="hidden_gaji_pokok" value="0">
                <input type="hidden" name="tarif_per_jam" id="hidden_tarif_per_jam" value="0">
                <input type="hidden" name="tunjangan" id="hidden_tunjangan" value="0">
                <input type="hidden" name="asuransi" id="hidden_asuransi" value="0">
                <input type="hidden" name="total_jam_kerja" id="hidden_total_jam_kerja" value="0">
                <input type="hidden" name="jenis_pegawai" id="hidden_jenis_pegawai" value="">

                <!-- Informasi Pegawai -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="pegawai_id" class="form-label fw-bold">
                            <i class="bi bi-person-badge"></i> Pilih Pegawai *
                        </label>
                        <select name="pegawai_id" id="pegawai_id" class="form-select form-select-lg" required onchange="loadPegawaiData()">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach ($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}"
                                        data-jenis="{{ strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl') }}"
                                        data-gaji-pokok="{{ $pegawai->jabatanRelasi->gaji ?? $pegawai->gaji_pokok ?? 0 }}"
                                        data-tarif="{{ $pegawai->jabatanRelasi->tarif ?? $pegawai->tarif_per_jam ?? 0 }}"
                                        data-tunjangan-jabatan="{{ $pegawai->jabatanRelasi->tunjangan ?? 0 }}"
                                        data-tunjangan-transport="{{ $pegawai->jabatanRelasi->tunjangan_transport ?? 0 }}"
                                        data-tunjangan-konsumsi="{{ $pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0 }}"
                                        data-asuransi="{{ $pegawai->jabatanRelasi->asuransi ?? 0 }}">
                                    {{ $pegawai->nama }} - {{ $pegawai->jabatan_nama ?? 'Staff' }} ({{ strtoupper($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'BTKTL') }})
                                    [Gaji: {{ number_format($pegawai->jabatanRelasi->gaji ?? $pegawai->gaji_pokok ?? 0, 0, ',', '.') }}, Tarif: {{ number_format($pegawai->jabatanRelasi->tarif ?? $pegawai->tarif_per_jam ?? 0, 0, ',', '.') }}]
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
                                <option value="{{ $kb->kode_akun }}" {{ $kb->kode_akun == '1101' ? 'selected' : '' }}>
                                    {{ $kb->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                        @error('coa_kasbank')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Komponen Gaji (Otomatis dari Data Pegawai) -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Komponen Gaji (Otomatis dari Data Pegawai)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- BTKTL Fields -->
                            <div class="col-md-6" id="field-gaji-pokok">
                                <label for="display_gaji_pokok" class="form-label">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_gaji_pokok" class="form-control" readonly value="0">
                                </div>
                                <small class="text-muted">Untuk pegawai BTKTL</small>
                            </div>

                            <!-- BTKL Fields -->
                            <div class="col-md-6" id="field-tarif" style="display:none;">
                                <label for="display_tarif" class="form-label">Tarif per Jam</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tarif" class="form-control" readonly value="0">
                                </div>
                                <small class="text-muted">Untuk pegawai BTKL</small>
                            </div>

                            <div class="col-md-6" id="field-jam-kerja" style="display:none;">
                                <label for="display_jam_kerja" class="form-label">Total Jam Kerja (Bulan Ini)</label>
                                <div class="input-group">
                                    <input type="text" id="display_jam_kerja" class="form-control" readonly value="0">
                                    <span class="input-group-text">Jam</span>
                                </div>
                                <small class="text-muted">Dari data presensi</small>
                            </div>

                            <div class="col-md-6" id="field-gaji-dasar" style="display:none;">
                                <label for="display_gaji_dasar" class="form-label fw-bold text-primary">Gaji Dasar</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_gaji_dasar" class="form-control fw-bold" readonly value="0">
                                </div>
                                <small class="text-muted">Tarif per Jam × Total Jam Kerja</small>
                            </div>

                            <div class="col-md-6">
                                <label for="display_total_tunjangan" class="form-label fw-bold">Total Tunjangan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_total_tunjangan" class="form-control fw-bold" readonly value="0">
                                </div>
                                <small class="text-muted">Dihitung otomatis dari komponen tunjangan</small>
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
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                        <i class="bi bi-save"></i> Simpan Penggajian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data pegawai
let pegawaiData = {
    jenis: 'btktl',
    gajiPokok: 0,
    tarif: 0,
    totalTunjangan: 0,
    asuransi: 0,
    jamKerja: 0,
    gajiDasar: 0
};

// Load data pegawai
function loadPegawaiData() {
    const select = document.getElementById('pegawai_id');
    const pegawaiId = select.value;
    
    if (pegawaiId) {
        // Fetch real-time data from API
        fetch(`/transaksi/penggajian/pegawai/${pegawaiId}/data`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.message);
                    // Fallback to static data if API fails
                    loadStaticPegawaiData();
                    return;
                }
                
                // Update pegawaiData with real-time values
                pegawaiData.jenis = data.jenis || 'btktl';
                pegawaiData.gajiPokok = parseFloat(data.gaji_pokok) || 0;
                pegawaiData.tarif = parseFloat(data.tarif) || 0;
                pegawaiData.totalTunjangan = parseFloat(data.total_tunjangan) || 0;
                pegawaiData.asuransi = parseFloat(data.asuransi) || 0;

                // Update hidden fields
                document.getElementById('hidden_gaji_pokok').value = pegawaiData.gajiPokok;
                document.getElementById('hidden_tarif_per_jam').value = pegawaiData.tarif;
                document.getElementById('hidden_tunjangan').value = pegawaiData.totalTunjangan;
                document.getElementById('hidden_asuransi').value = pegawaiData.asuransi;
                document.getElementById('hidden_jenis_pegawai').value = pegawaiData.jenis;
                document.getElementById('hidden_total_jam_kerja').value = pegawaiData.jamKerja;

                // Update display
                document.getElementById('display_gaji_pokok').value = pegawaiData.gajiPokok.toLocaleString('id-ID');
                document.getElementById('display_tarif').value = pegawaiData.tarif.toLocaleString('id-ID');
                document.getElementById('display_total_tunjangan').value = pegawaiData.totalTunjangan.toLocaleString('id-ID');
                document.getElementById('display_asuransi').value = pegawaiData.asuransi.toLocaleString('id-ID');
                document.getElementById('display_jam_kerja').value = '0';
                document.getElementById('display_gaji_dasar').value = '0';

                // Show/hide fields based on employee type
                updateFieldVisibility();
                
                // Load jam kerja if BTKL
                if (pegawaiData.jenis === 'btkl') {
                    loadJamKerja();
                } else {
                    // Calculate gaji dasar for BTKTL
                    pegawaiData.gajiDasar = pegawaiData.gajiPokok;
                    document.getElementById('display_gaji_dasar').value = pegawaiData.gajiDasar.toLocaleString('id-ID');
                }
                
                hitungTotal();
            })
            .catch(error => {
                console.error('Error loading employee data:', error);
                // Fallback to static data if API fails
                loadStaticPegawaiData();
            });
    } else {
        // Reset if no employee selected
        resetPegawaiData();
    }
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
        pegawaiData.totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi || parseFloat(option.dataset.tunjangan) || 0;
        pegawaiData.asuransi = parseFloat(option.dataset.asuransi) || 0;

        // Update hidden fields
        document.getElementById('hidden_gaji_pokok').value = pegawaiData.gajiPokok;
        document.getElementById('hidden_tarif_per_jam').value = pegawaiData.tarif;
        document.getElementById('hidden_tunjangan').value = pegawaiData.totalTunjangan;
        document.getElementById('hidden_asuransi').value = pegawaiData.asuransi;
        document.getElementById('hidden_jenis_pegawai').value = pegawaiData.jenis;
        document.getElementById('hidden_total_jam_kerja').value = pegawaiData.jamKerja;

        // Update display
        document.getElementById('display_gaji_pokok').value = pegawaiData.gajiPokok.toLocaleString('id-ID');
        document.getElementById('display_tarif').value = pegawaiData.tarif.toLocaleString('id-ID');
        document.getElementById('display_total_tunjangan').value = pegawaiData.totalTunjangan.toLocaleString('id-ID');
        document.getElementById('display_asuransi').value = pegawaiData.asuransi.toLocaleString('id-ID');
        document.getElementById('display_jam_kerja').value = '0';
        document.getElementById('display_gaji_dasar').value = '0';

        // Show/hide fields based on employee type
        updateFieldVisibility();
        
        // Load jam kerja if BTKL
        if (pegawaiData.jenis === 'btkl') {
            loadJamKerja();
        } else {
            // Calculate gaji dasar for BTKTL
            pegawaiData.gajiDasar = pegawaiData.gajiPokok;
            document.getElementById('display_gaji_dasar').value = pegawaiData.gajiDasar.toLocaleString('id-ID');
        }
    } else {
        // Reset hidden fields
        document.getElementById('hidden_gaji_pokok').value = 0;
        document.getElementById('hidden_tarif_per_jam').value = 0;
        document.getElementById('hidden_tunjangan').value = 0;
        document.getElementById('hidden_asuransi').value = 0;
        document.getElementById('hidden_jenis_pegawai').value = '';
        document.getElementById('hidden_total_jam_kerja').value = 0;

        // Reset display fields
        document.getElementById('display_gaji_pokok').value = '0';
        document.getElementById('display_tarif').value = '0';
        document.getElementById('display_total_tunjangan').value = '0';
        document.getElementById('display_asuransi').value = '0';
        document.getElementById('display_jam_kerja').value = '0';
        document.getElementById('display_gaji_dasar').value = '0';

        // Reset pegawaiData
        pegawaiData.gajiPokok = 0;
        pegawaiData.tarif = 0;
        pegawaiData.totalTunjangan = 0;
        pegawaiData.asuransi = 0;
        pegawaiData.jamKerja = 0;
        pegawaiData.gajiDasar = 0;

        // Hide BTKL fields
        document.getElementById('field-gaji-pokok').style.display = 'block';
        document.getElementById('field-tarif').style.display = 'none';
        document.getElementById('field-jam-kerja').style.display = 'none';
        document.getElementById('field-gaji-dasar').style.display = 'none';
    }
}

// Load jam kerja dari presensi
function loadJamKerja() {
    const pegawaiId = document.getElementById('pegawai_id').value;
    const tanggal = document.getElementById('tanggal_penggajian').value;

    if (pegawaiId && tanggal && pegawaiData.jenis === 'btkl') {
        // Parse tanggal untuk mendapatkan bulan dan tahun
        const date = new Date(tanggal);
        const month = date.getMonth() + 1;
        const year = date.getFullYear();

        // Fetch jam kerja dari server
        fetch(`/api/presensi/jam-kerja?pegawai_id=${pegawaiId}&month=${month}&year=${year}`)
            .then(response => response.json())
            .then(data => {
                pegawaiData.jamKerja = parseFloat(data.total_jam) || 0;
                document.getElementById('display_jam_kerja').value = pegawaiData.jamKerja.toLocaleString('id-ID');
                document.getElementById('hidden_total_jam_kerja').value = pegawaiData.jamKerja;

                // Calculate gaji dasar for BTKL
                pegawaiData.gajiDasar = pegawaiData.tarif * pegawaiData.jamKerja;
                document.getElementById('display_gaji_dasar').value = pegawaiData.gajiDasar.toLocaleString('id-ID');

                hitungTotal();
            })
            .catch(error => {
                console.error('Error loading jam kerja:', error);
                pegawaiData.jamKerja = 0;
                pegawaiData.gajiDasar = 0;
                document.getElementById('display_jam_kerja').value = '0';
                document.getElementById('hidden_total_jam_kerja').value = 0;
                document.getElementById('display_gaji_dasar').value = '0';
                hitungTotal();
            });
    } else {
        hitungTotal();
    }
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

@extends('layouts.app')

@section('title', 'Tambah Data Pegawai')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">➕ Tambah Data Pegawai</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master-data.pegawai.store') }}" method="POST" id="pegawai-form">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama Pegawai</label>
                <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="no_telepon" class="form-label">No. Telepon</label>
                <input type="text" name="no_telepon" id="no_telepon" class="form-control" value="{{ old('no_telepon') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" rows="2" required>{{ old('alamat') }}</textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Jenis Kelamin</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki_laki" value="L" {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="laki_laki">Laki-laki</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="P" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="perempuan">Perempuan</label>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="kategori" class="form-label">Kategori Pegawai</label>
                <select name="kategori" id="kategori" class="form-select" required onchange="loadJabatanByKategori()">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k }}" {{ old('kategori') == $k ? 'selected' : '' }}>
                            {{ strtoupper($k) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="jabatan_id" class="form-label">Jabatan</label>
                <select name="jabatan_id" id="jabatan_id" class="form-select" required onchange="loadJabatanDetail()">
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach($jabatans as $j)
                        <option value="{{ $j->id }}"
                                data-nama="{{ $j->nama }}"
                                data-kategori="{{ $j->kategori }}"
                                data-tunjangan="{{ $j->tunjangan }}"
                                data-asuransi="{{ $j->asuransi }}"
                                data-gaji="{{ $j->gaji }}"
                                data-tarif="{{ $j->tarif }}"
                                {{ old('jabatan_id')==$j->id?'selected':'' }}>
                            {{ $j->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Preview otomatis dari Jabatan -->
            <div class="col-12">
                <div class="alert alert-secondary small" id="preview-box" style="display:none">
                    <h6>Detail Kualifikasi Jabatan:</h6>
                    <div class="row">
                        <div class="col-md-6"><strong>Kategori:</strong> <span id="pv-kategori">-</span></div>
                        <div class="col-md-6"><strong>Tunjangan:</strong> Rp <span id="pv-tunjangan">0</span></div>
                        <div class="col-md-6"><strong>Asuransi:</strong> Rp <span id="pv-asuransi">0</span></div>
                        <div class="col-md-6"><strong>Gaji Pokok (BTKTL):</strong> Rp <span id="pv-gaji-pokok">0</span></div>
                        <div class="col-md-6"><strong>Tarif / Jam (BTKL):</strong> Rp <span id="pv-tarif-per-jam">0</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Rekening Bank -->
        <div class="col-12 mt-4">
            <h5>Informasi Rekening Bank</h5>
            <hr>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">Bank</label>
                <input type="text" name="bank" id="bank" class="form-control" value="{{ old('bank') }}" required>
            </div>

            <div class="col-md-4 mb-3">
                <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                <input type="text" name="nomor_rekening" id="nomor_rekening" class="form-control" value="{{ old('nomor_rekening') }}" required>
            </div>

            <div class="col-md-4 mb-3">
                <label for="nama_rekening" class="form-label">Nama Rekening</label>
                <input type="text" name="nama_rekening" id="nama_rekening" class="form-control" value="{{ old('nama_rekening') }}" required>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Data Pegawai
                </button>
                <a href="{{ route('master-data.pegawai.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </form>
</div>

<script>
// Global variables
let jabatanData = {};

// Format number untuk Indonesia
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(Number(num || 0));
}

// Load jabatan berdasarkan kategori
function loadJabatanByKategori() {
    const kategori = document.getElementById('kategori').value;
    const jabatanSelect = document.getElementById('jabatan_id');
    
    // Reset jabatan dropdown
    jabatanSelect.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
    document.getElementById('preview-box').style.display = 'none';
    
    if (kategori) {
        fetch(`/master-data/api/jabatan/by-kategori?kategori_id=${encodeURIComponent(kategori)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(jabatan => {
                        const option = document.createElement('option');
                        option.value = jabatan.id;
                        option.setAttribute('data-nama', jabatan.nama);
                        option.setAttribute('data-kategori', jabatan.kategori);
                        option.setAttribute('data-tunjangan', jabatan.tunjangan);
                        option.setAttribute('data-asuransi', jabatan.asuransi);
                        option.setAttribute('data-gaji', jabatan.gaji);
                        option.setAttribute('data-tarif', jabatan.tarif);
                        option.textContent = jabatan.nama;
                        jabatanSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading jabatan:', error);
            });
    }
}

// Load detail jabatan
function loadJabatanDetail() {
    const jabatanId = document.getElementById('jabatan_id').value;
    const selectedOption = document.getElementById('jabatan_id').options[document.getElementById('jabatan_id').selectedIndex];
    
    if (jabatanId && selectedOption) {
        jabatanData = {
            nama: selectedOption.getAttribute('data-nama'),
            kategori: selectedOption.getAttribute('data-kategori'),
            tunjangan: parseFloat(selectedOption.getAttribute('data-tunjangan')) || 0,
            asuransi: parseFloat(selectedOption.getAttribute('data-asuransi')) || 0,
            gaji: parseFloat(selectedOption.getAttribute('data-gaji')) || 0,
            tarif: parseFloat(selectedOption.getAttribute('data-tarif')) || 0
        };
        
        updatePreview();
    } else {
        document.getElementById('preview-box').style.display = 'none';
    }
}

// Update preview box
function updatePreview() {
    if (jabatanData.nama) {
        document.getElementById('pv-kategori').textContent = jabatanData.kategori ? jabatanData.kategori.toUpperCase() : '-';
        document.getElementById('pv-tunjangan').textContent = formatNumber(jabatanData.tunjangan);
        document.getElementById('pv-asuransi').textContent = formatNumber(jabatanData.asuransi);
        document.getElementById('pv-gaji-pokok').textContent = formatNumber(jabatanData.gaji);
        document.getElementById('pv-tarif-per-jam').textContent = formatNumber(jabatanData.tarif);
        document.getElementById('preview-box').style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load initial jabatan if kategori is pre-selected
    const kategori = document.getElementById('kategori').value;
    if (kategori) {
        loadJabatanByKategori();
    }
    
    // Load initial jabatan detail if jabatan is pre-selected
    const jabatanId = document.getElementById('jabatan_id').value;
    if (jabatanId) {
        loadJabatanDetail();
    }
});
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Ubah Pegawai</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master-data.pegawai.update', $pegawai->nomor_induk_pegawai) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $pegawai->nama) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $pegawai->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telp</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp', $pegawai->no_telp) }}" required>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" required>{{ old('alamat', $pegawai->alamat) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="jabatan" class="form-label">Jabatan</label>
            <input type="text" class="form-control" id="jabatan" name="jabatan" value="{{ old('jabatan', $pegawai->jabatan) }}" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" 
                       value="{{ old('tanggal_masuk', $pegawai->tanggal_masuk ? (is_string($pegawai->tanggal_masuk) ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->format('Y-m-d') : $pegawai->tanggal_masuk->format('Y-m-d')) : '') }}" 
                       required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status Aktif</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status_aktif" id="status_aktif_1" 
                           value="1" {{ old('status_aktif', $pegawai->status_aktif) ? 'checked' : '' }} required>
                    <label class="form-check-label" for="status_aktif_1">Aktif</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status_aktif" id="status_aktif_0" 
                           value="0" {{ !old('status_aktif', $pegawai->status_aktif) ? 'checked' : '' }} required>
                    <label class="form-check-label" for="status_aktif_0">Tidak Aktif</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="kategori_tenaga_kerja" class="form-label">Kategori Tenaga Kerja</label>
            <select class="form-select" id="kategori_tenaga_kerja" name="kategori_tenaga_kerja" required onchange="toggleSalaryFields()">
                <option value="">-- Pilih Kategori --</option>
                <option value="BTKL" {{ old('kategori_tenaga_kerja', $pegawai->kategori_tenaga_kerja) == 'BTKL' ? 'selected' : '' }}>BTKL (Buruh Tidak Langsung - Gaji Per Jam)</option>
                <option value="BTKTL" {{ old('kategori_tenaga_kerja', $pegawai->kategori_tenaga_kerja) == 'BTKTL' ? 'selected' : '' }}>BTKTL (Buruh Tidak Langsung - Gaji Bulanan)</option>
            </select>
        </div>

        <!-- BTKL Fields -->
        <div id="btkl-fields" style="display: {{ old('kategori_tenaga_kerja', $pegawai->kategori_tenaga_kerja) == 'BTKL' ? 'block' : 'none' }};">
            <div class="mb-3">
                <label for="tarif_per_jam" class="form-label">Tarif Per Jam</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control" id="tarif_per_jam" name="tarif_per_jam" 
                           value="{{ old('tarif_per_jam', $pegawai->kategori_tenaga_kerja == 'BTKL' ? $pegawai->tarif_per_jam : '') }}" 
                           min="0"
                           {{ old('kategori_tenaga_kerja', $pegawai->kategori_tenaga_kerja) == 'BTKL' ? 'required' : '' }}
                           oninput="updateSalaryEstimate()">
                </div>
                <small class="text-muted">* Gaji akan dihitung berdasarkan presensi bulanan</small>
            </div>
            <!-- Hidden gaji_pokok field for BTKL -->
            <input type="hidden" name="gaji_pokok" value="0">
        </div>

        <!-- BTKTL Fields -->
        <div id="btktl-fields" style="display: {{ old('kategori_tenaga_kerja', $pegawai->kategori_tenaga_kerja) == 'BTKTL' ? 'block' : 'none' }};">
            <div class="mb-3">
                <label for="gaji_pokok" class="form-label">Gaji Pokok (per bulan)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control" id="gaji_pokok" name="gaji_pokok" 
                           value="{{ old('gaji_pokok', $pegawai->kategori_tenaga_kerja == 'BTKTL' ? $pegawai->gaji_pokok : '') }}" 
                           min="0"
                           {{ old('kategori_tenaga_kerja', $pegawai->kategori_tenaga_kerja) == 'BTKTL' ? 'required' : '' }}
                           oninput="updateSalaryEstimate()">
                </div>
            </div>
            <!-- Hidden tarif_per_jam field for BTKTL -->
            <input type="hidden" name="tarif_per_jam" value="0">
        </div>

        <!-- Tunjangan (for both types) -->
        <div class="mb-3">
            <label for="tunjangan" class="form-label">Tunjangan Tetap (opsional)</label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" class="form-control" id="tunjangan" name="tunjangan" 
                       value="{{ old('tunjangan', $pegawai->tunjangan) }}" min="0"
                       oninput="updateSalaryEstimate()">
            </div>
            <small class="text-muted">* Tunjangan tetap yang akan ditambahkan ke gaji pokok</small>
        </div>

        <!-- Gaji Total (readonly) -->
        <div class="mb-3">
            <label class="form-label">Estimasi Gaji Per Bulan</label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control" id="total_gaji" readonly 
                       value="{{ number_format($pegawai->gaji, 0, ',', '.') }}">
            </div>
            <small class="text-muted">* Estimasi gaji pokok + tunjangan tetap. Gaji BTKL akan dihitung berdasarkan presensi.</small>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('master-data.pegawai.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@push('scripts')
<script>
    function toggleSalaryFields() {
        const category = document.getElementById('kategori_tenaga_kerja').value;
        const btklFields = document.getElementById('btkl-fields');
        const btktlFields = document.getElementById('btktl-fields');
        const tarifPerJamGroup = document.getElementById('tarif_per_jam_group');
        const gajiPokokGroup = document.getElementById('gaji_pokok_group');
        const tarifPerJam = document.getElementById('tarif_per_jam');
        const gajiPokok = document.getElementById('gaji_pokok');
        const totalGaji = document.getElementById('total_gaji');

        // Toggle fields visibility and clear values when switching
        if (category === 'BTKL') {
            btklFields.style.display = 'block';
            btktlFields.style.display = 'none';
            tarifPerJam.required = true;
            gajiPokok.required = false;
            // Clear BTKTL fields
            gajiPokok.value = '';
        } else if (category === 'BTKTL') {
            btklFields.style.display = 'none';
            btktlFields.style.display = 'block';
            tarifPerJam.required = false;
            gajiPokok.required = true;
            // Clear BTKL fields
            tarifPerJam.value = '';
        } else {
            btklFields.style.display = 'none';
            btktlFields.style.display = 'none';
        }
        
        updateSalaryEstimate();
    }

    function updateSalaryEstimate() {
        const category = document.getElementById('kategori_tenaga_kerja').value;
        const tunjangan = parseFloat(document.getElementById('tunjangan').value) || 0;
        let estimate = 0;

        if (category === 'BTKL') {
            const tarifPerJam = parseFloat(document.getElementById('tarif_per_jam').value) || 0;
            // For BTKL, we'll just show the hourly rate * 0 as base since actual hours come from attendance
            estimate = tunjangan; // Only show the fixed allowance for BTKL
        } else if (category === 'BTKTL') {
            const gajiPokok = parseFloat(document.getElementById('gaji_pokok').value) || 0;
            estimate = gajiPokok + tunjangan;
        }

        // Update estimate display
        document.getElementById('total_gaji').value = estimate.toLocaleString('id-ID');
    }

    // Add event listeners for real-time calculation
    document.addEventListener('DOMContentLoaded', function() {
        // Initial setup
        toggleSalaryFields();
        
        // Add event listeners for input changes
        document.getElementById('tarif_per_jam').addEventListener('input', updateSalaryEstimate);
        document.getElementById('gaji_pokok').addEventListener('input', updateSalaryEstimate);
        document.getElementById('tunjangan').addEventListener('input', updateSalaryEstimate);
    });
</script>
@endpush

@endsection

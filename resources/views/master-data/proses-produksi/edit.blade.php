@extends('layouts.app')

@section('title', 'Edit BTKL')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-edit me-2"></i>Edit BTKL
        </h2>
        <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>Form Edit BTKL
            </h5>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('master-data.btkl.update', $prosesProduksi) }}" method="POST" id="editBtklForm">
                @csrf
                @method('PATCH')
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Kode Proses</label>
                            <input type="text" class="form-control bg-light" value="{{ $prosesProduksi->kode_proses }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nama Proses <span class="text-danger">*</span></label>
                            <input type="text" name="nama_proses" class="form-control @error('nama_proses') is-invalid @enderror" 
                                   value="{{ old('nama_proses', $prosesProduksi->nama_proses) }}" placeholder="Contoh: Pengisian Cup Jasuke" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                            <select name="jabatan_id" id="jabatanSelect" class="form-select @error('jabatan_id') is-invalid @enderror" required onchange="calculateBTKL()">
                                <option value="">-- Pilih Jabatan BTKL --</option>
                                @php
                                    $jabatanBtkl = \App\Models\Kualifikasi::where('kategori', 'btkl')
                                        ->where('user_id', auth()->id())
                                        ->orderBy('nama_kualifikasi')
                                        ->get();
                                @endphp
                                @foreach($jabatanBtkl as $jabatan)
                                    @php
                                        $pegawaiCount = \App\Models\Pegawai::where('jabatan', $jabatan->nama_kualifikasi)->count();
                                    @endphp
                                    <option value="{{ $jabatan->id }}" 
                                            data-tarif="{{ $jabatan->tarif_produk ?? $jabatan->tarif }}"
                                            data-pegawai-count="{{ $pegawaiCount }}"
                                            {{ old('jabatan_id', $prosesProduksi->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                        {{ $jabatan->nama_kualifikasi }} ({{ $pegawaiCount }} pegawai @ Rp {{ number_format($jabatan->tarif_produk ?? $jabatan->tarif, 0, ',', '.') }}/produk)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pegawai</label>
                            <div class="input-group">
                                <input type="number" id="jumlahPegawai" class="form-control bg-light" readonly>
                                <span class="input-group-text">orang</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tarif Dasar per Produk</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="tarifPerJamJabatan" class="form-control bg-light" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Total Tarif BTKL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="tarifBTKLDisplay" class="form-control bg-light" readonly>
                                <input type="hidden" name="tarif_per_produk" id="tarifPerProduk" value="{{ old('tarif_per_produk', $prosesProduksi->tarif_per_produk ?? 0) }}">
                                <input type="hidden" name="jumlah_pegawai" id="jumlahPegawaiHidden" value="{{ old('jumlah_pegawai', $prosesProduksi->jumlah_pegawai ?? 0) }}">
                            </div>
                            <small class="text-muted">Otomatis: Pegawai × Tarif Dasar</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi proses produksi">{{ old('deskripsi', $prosesProduksi->deskripsi) }}</textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Jalankan kalkulasi saat halaman selesai loading untuk mengisi field readonly
    calculateBTKL();
});

function calculateBTKL() {
    const jabatanSelect = document.getElementById('jabatanSelect');
    const selectedOption = jabatanSelect.options[jabatanSelect.selectedIndex];
    
    if (selectedOption.value) {
        const tarifDasar = parseFloat(selectedOption.getAttribute('data-tarif')) || 0;
        const jumlahPegawai = parseInt(selectedOption.getAttribute('data-pegawai-count')) || 0;
        const totalBTKL = tarifDasar * jumlahPegawai;
        
        document.getElementById('jumlahPegawai').value = jumlahPegawai;
        document.getElementById('tarifPerJamJabatan').value = tarifDasar;
        document.getElementById('tarifBTKLDisplay').value = totalBTKL;
        
        // Update hidden inputs untuk dikirim ke controller
        document.getElementById('tarifPerProduk').value = tarifDasar;
        document.getElementById('jumlahPegawaiHidden').value = jumlahPegawai;
        
        showCalculationInfo(jumlahPegawai, tarifDasar, totalBTKL);
    } else {
        document.getElementById('jumlahPegawai').value = '';
        document.getElementById('tarifPerJamJabatan').value = '';
        document.getElementById('tarifBTKLDisplay').value = '';
        document.getElementById('tarifPerProduk').value = 0;
        document.getElementById('jumlahPegawaiHidden').value = 0;
        hideCalculationInfo();
    }
}

function showCalculationInfo(jumlahPegawai, tarifDasar, totalBTKL) {
    hideCalculationInfo();
    const infoDiv = document.createElement('div');
    infoDiv.id = 'calculationInfo';
    infoDiv.className = 'alert alert-info mt-2';
    infoDiv.innerHTML = `
        <i class="fas fa-calculator me-2"></i>
        <strong>Perhitungan:</strong><br>
        ${jumlahPegawai} pegawai × Rp ${formatNumber(tarifDasar)}/produk = <strong>Rp ${formatNumber(totalBTKL)}/produk</strong>
    `;
    document.getElementById('tarifBTKLDisplay').parentNode.parentNode.appendChild(infoDiv);
}

function hideCalculationInfo() {
    const existingInfo = document.getElementById('calculationInfo');
    if (existingInfo) existingInfo.remove();
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}
</script>
@endsection
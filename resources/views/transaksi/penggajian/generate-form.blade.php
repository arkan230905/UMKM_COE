@extends('layouts.app')

@section('title', 'Generate Penggajian Bulanan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fas fa-cogs me-2"></i>Generate Penggajian Bulanan
            </h2>
            <small class="text-muted">Buat penggajian otomatis berdasarkan data presensi bulanan</small>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card">
        <div class="card-header bg-brown text-white">
            <h5 class="mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>Pilih Periode Penggajian
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('penggajian.generate') }}" class="row g-4">
                @csrf

                <!-- Bulan -->
                <div class="col-md-4">
                    <label for="bulan" class="form-label">
                        <strong>Bulan <span class="text-danger">*</span></strong>
                    </label>
                    <select name="bulan" id="bulan" class="form-select form-select-lg" required>
                        <option value="">-- Pilih Bulan --</option>
                        @foreach($bulanList as $key => $bulan)
                            <option value="{{ $key }}" 
                                {{ old('bulan') == $key ? 'selected' : '' }}>
                                {{ $bulan }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Pilih bulan untuk penggajian
                    </small>
                </div>

                <!-- Tahun -->
                <div class="col-md-4">
                    <label for="tahun" class="form-label">
                        <strong>Tahun <span class="text-danger">*</span></strong>
                    </label>
                    <select name="tahun" id="tahun" class="form-select form-select-lg" required>
                        <option value="">-- Pilih Tahun --</option>
                        @foreach($tahunList as $tahun)
                            <option value="{{ $tahun }}" 
                                {{ old('tahun') == $tahun ? 'selected' : '' }}>
                                {{ $tahun }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Pilih tahun untuk penggajian
                    </small>
                </div>

                <!-- Tanggal Penggajian -->
                <div class="col-md-4">
                    <label for="tanggal_penggajian" class="form-label">
                        <strong>Tanggal Penggajian</strong>
                    </label>
                    <input type="date" name="tanggal_penggajian" id="tanggal_penggajian" 
                           class="form-control form-control-lg" 
                           value="{{ old('tanggal_penggajian') }}">
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Kosongkan untuk menggunakan akhir bulan
                    </small>
                </div>

                <!-- Info Box -->
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-lightbulb me-2"></i>Informasi Penting
                        </h6>
                        <ul class="mb-0">
                            <li>Sistem akan mengambil data presensi dari bulan yang dipilih</li>
                            <li>Penggajian dihitung berdasarkan <strong>total jam kerja aktual</strong> dari presensi harian</li>
                            <li>Rumus: <code>Gaji Pokok = Total Jam × Tarif Per Jam</code></li>
                            <li>Jika penggajian untuk periode ini sudah ada, akan di-update dengan data presensi terbaru</li>
                            <li>Setiap pegawai aktif akan diproses secara otomatis</li>
                        </ul>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-eye me-2"></i>Preview Data yang Akan Diproses
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="preview-content" class="text-muted">
                                <p>Pilih bulan dan tahun untuk melihat preview data</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('penggajian.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                            <i class="fas fa-check me-2"></i>Generate Penggajian
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Generate Penggajian
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan membuat penggajian untuk:</p>
                    <div class="alert alert-info">
                        <strong id="confirm-periode">-</strong>
                    </div>
                    <p class="mb-0">Proses ini akan:</p>
                    <ul>
                        <li>Mengambil data presensi dari periode yang dipilih</li>
                        <li>Menghitung total jam kerja aktual setiap pegawai</li>
                        <li>Membuat/update penggajian untuk semua pegawai aktif</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="confirm-btn">
                        <i class="fas fa-check me-2"></i>Ya, Generate Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-brown {
        background-color: #8B6F47;
    }

    .form-select-lg, .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }

    .alert-info {
        background-color: #e7f3ff;
        border-color: #b3d9ff;
        color: #004085;
    }

    .alert-info h6 {
        color: #004085;
    }

    code {
        background-color: #f4f4f4;
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        color: #d63384;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');
    const submitBtn = document.getElementById('submit-btn');
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const confirmPeriode = document.getElementById('confirm-periode');
    const confirmBtn = document.getElementById('confirm-btn');

    const bulanList = {
        1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
        5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
        9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
    };

    // Update preview when bulan or tahun changes
    [bulanSelect, tahunSelect].forEach(select => {
        select.addEventListener('change', updatePreview);
    });

    function updatePreview() {
        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;

        if (!bulan || !tahun) {
            document.getElementById('preview-content').innerHTML = 
                '<p class="text-muted">Pilih bulan dan tahun untuk melihat preview data</p>';
            return;
        }

        const bulanNama = bulanList[bulan];
        document.getElementById('preview-content').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Periode:</strong> ${bulanNama} ${tahun}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="badge bg-info">Siap di-generate</span></p>
                </div>
            </div>
            <hr>
            <p class="small text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Sistem akan memproses semua pegawai aktif dengan data presensi dari periode ini.
            </p>
        `;
    }

    // Handle form submission
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();

        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;

        if (!bulan || !tahun) {
            alert('Pilih bulan dan tahun terlebih dahulu');
            return;
        }

        const bulanNama = bulanList[bulan];
        confirmPeriode.textContent = `${bulanNama} ${tahun}`;
        confirmModal.show();
    });

    // Handle confirmation
    confirmBtn.addEventListener('click', function() {
        document.querySelector('form').submit();
    });
});
</script>
@endsection

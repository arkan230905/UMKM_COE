@extends('layouts.app')

@section('title', 'Edit Penggajian')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Edit Data Penggajian</h4>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Informasi:</strong> Data gaji pokok, tarif per jam, tunjangan, dan asuransi akan diambil otomatis dari master data kualifikasi tenaga kerja. 
        Jam kerja akan diambil dari data presensi periode yang dipilih.
    </div>

    <form action="{{ route('transaksi.penggajian.update', $penggajian->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Data Dasar</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="pegawai_id" class="form-label">Pegawai <span class="text-danger">*</span></label>
                            <select name="pegawai_id" id="pegawai_id" class="form-select" required>
                                <option value="">Pilih Pegawai</option>
                                @foreach($pegawais as $pegawai)
                                    <option value="{{ $pegawai->id }}" 
                                            {{ $penggajian->pegawai_id == $pegawai->id ? 'selected' : '' }}
                                            data-jenis="{{ $pegawai->jenis_pegawai }}"
                                            data-jabatan="{{ $pegawai->jabatan }}">
                                        {{ $pegawai->nama }} - {{ $pegawai->jabatan }} ({{ strtoupper($pegawai->jenis_pegawai) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_penggajian" class="form-label">Tanggal Penggajian <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_penggajian" id="tanggal_penggajian" 
                                   class="form-control" value="{{ $penggajian->tanggal_penggajian->format('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="coa_kasbank" class="form-label">Akun Kas/Bank <span class="text-danger">*</span></label>
                            <select name="coa_kasbank" id="coa_kasbank" class="form-select" required>
                                <option value="">Pilih Akun</option>
                                @foreach($coaKasBank as $coa)
                                    <option value="{{ $coa->kode_akun }}" {{ $penggajian->coa_kasbank == $coa->kode_akun ? 'selected' : '' }}>
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Data Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="bonus" class="form-label">Bonus</label>
                            <input type="number" name="bonus" id="bonus" class="form-control" 
                                   value="{{ $penggajian->bonus }}" min="0" step="0.01">
                        </div>

                        <div class="mb-3">
                            <label for="potongan" class="form-label">Potongan</label>
                            <input type="number" name="potongan" id="potongan" class="form-control" 
                                   value="{{ $penggajian->potongan }}" min="0" step="0.01">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Data (akan diisi via JavaScript) -->
        <div class="card mt-4" id="preview-card" style="display: none;">
            <div class="card-header">
                <h5>Preview Perhitungan</h5>
            </div>
            <div class="card-body" id="preview-content">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Update Penggajian
            </button>
            <a href="{{ route('transaksi.penggajian.show', $penggajian->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pegawaiSelect = document.getElementById('pegawai_id');
    const tanggalInput = document.getElementById('tanggal_penggajian');
    const bonusInput = document.getElementById('bonus');
    const potonganInput = document.getElementById('potongan');
    const previewCard = document.getElementById('preview-card');
    const previewContent = document.getElementById('preview-content');

    function loadPreview() {
        const pegawaiId = pegawaiSelect.value;
        const tanggal = tanggalInput.value;
        
        if (pegawaiId && tanggal) {
            fetch(`/transaksi/penggajian/pegawai/${pegawaiId}/data?tanggal=${tanggal}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const bonus = parseFloat(bonusInput.value) || 0;
                        const potongan = parseFloat(potonganInput.value) || 0;
                        
                        let html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Data dari Master Kualifikasi:</h6>
                                    <ul class="list-unstyled">
                                        <li>Jenis Pegawai: <strong>${data.jenis_pegawai.toUpperCase()}</strong></li>
                                        ${data.jenis_pegawai === 'btkl' ? 
                                            `<li>Tarif per Jam: <strong>Rp ${data.tarif_per_jam.toLocaleString()}</strong></li>` :
                                            `<li>Gaji Pokok: <strong>Rp ${data.gaji_pokok.toLocaleString()}</strong></li>`
                                        }
                                        <li>Tunjangan Transport: <strong>Rp ${data.tunjangan_transport.toLocaleString()}</strong></li>
                                        <li>Tunjangan Konsumsi: <strong>Rp ${data.tunjangan_konsumsi.toLocaleString()}</strong></li>
                                        <li>Asuransi: <strong>Rp ${data.asuransi.toLocaleString()}</strong></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Perhitungan:</h6>
                                    <ul class="list-unstyled">
                                        ${data.jenis_pegawai === 'btkl' ? 
                                            `<li>Total Jam Kerja: <strong>${data.total_jam_kerja} jam</strong></li>
                                             <li>Gaji Dasar: <strong>Rp ${(data.tarif_per_jam * data.total_jam_kerja).toLocaleString()}</strong></li>` :
                                            `<li>Gaji Dasar: <strong>Rp ${data.gaji_pokok.toLocaleString()}</strong></li>`
                                        }
                                        <li>Total Tunjangan: <strong>Rp ${data.total_tunjangan.toLocaleString()}</strong></li>
                                        <li>Bonus: <strong>Rp ${bonus.toLocaleString()}</strong></li>
                                        <li>Potongan: <strong>Rp ${potongan.toLocaleString()}</strong></li>
                                        <li class="border-top pt-2 mt-2"><strong>Total Gaji: Rp ${(data.total_gaji + bonus - potongan).toLocaleString()}</strong></li>
                                    </ul>
                                </div>
                            </div>
                        `;
                        
                        previewContent.innerHTML = html;
                        previewCard.style.display = 'block';
                    } else {
                        previewContent.innerHTML = `<div class="alert alert-warning">${data.message}</div>`;
                        previewCard.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    previewContent.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat preview</div>';
                    previewCard.style.display = 'block';
                });
        } else {
            previewCard.style.display = 'none';
        }
    }

    // Load preview on page load
    loadPreview();

    // Event listeners
    pegawaiSelect.addEventListener('change', loadPreview);
    tanggalInput.addEventListener('change', loadPreview);
    bonusInput.addEventListener('input', loadPreview);
    potonganInput.addEventListener('input', loadPreview);
});
</script>
@endsection

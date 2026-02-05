@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">
            <i class="fas fa-edit me-2"></i>Edit BOP Proses
        </h2>
        <div>
            <a href="{{ route('master-data.bop.show-proses', $bopProses->id) }}" class="btn btn-info">
                <i class="fas fa-eye me-2"></i>Lihat Detail
            </a>
            <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('master-data.bop.update-proses', $bopProses->id) }}" method="POST" id="editBopForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Informasi Proses BTKL -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Proses BTKL</h6>
                        <small>Data BTKL terkait dengan BOP proses produksi</small>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <strong>Kode Proses:</strong><br>
                                <code class="text-primary fs-6">{{ $bopProses->prosesProduksi->kode_proses }}</code>
                            </div>
                            <div class="col-6">
                                <strong>Nama Proses:</strong><br>
                                <span class="text-dark">{{ $bopProses->prosesProduksi->nama_proses }}</span>
                            </div>
                            <div class="col-6">
                                <strong>Tarif BTKL:</strong><br>
                                <span class="text-success fw-bold">{{ format_rupiah_clean($bopProses->prosesProduksi->tarif_per_jam ?? 0) }}</span>
                            </div>
                            <div class="col-6">
                                <strong>Kapasitas:</strong><br>
                                <span class="badge bg-info fs-6">{{ $bopProses->prosesProduksi->kapasitas_per_jam }} unit/jam</span>
                            </div>
                            <div class="col-6">
                                <strong>BTKL / pcs:</strong><br>
                                <span class="text-success">{{ format_rupiah_clean($bopProses->prosesProduksi->biaya_per_produk ?? 0) }}</span>
                            </div>
                            <div class="col-6">
                                <strong>Deskripsi:</strong><br>
                                <span class="text-muted">{{ $bopProses->prosesProduksi->deskripsi ?? 'Proses penggorengan makanan' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan BOP (Real-time) -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Ringkasan BOP</h6>
                        <small>Perhitungan otomatis berdasarkan input</small>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total BOP per Jam:</strong>
                                    <span class="fs-5 text-warning fw-bold" id="totalBopPerJam">{{ format_rupiah_clean($bopProses->total_bop_per_jam) }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>BOP per Unit:</strong>
                                    <span class="fs-5 text-success fw-bold" id="bopPerUnit">{{ format_rupiah_clean($bopProses->bop_per_unit) }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Efisiensi BOP:</strong>
                                    <span class="text-muted">{{ number_format(($bopProses->prosesProduksi->kapasitas_per_jam ?? 0), 0) }} unit per jam</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Edit Komponen BOP -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Komponen BOP per Jam</h6>
                <small>Masukkan biaya overhead pabrik per jam operasi mesin</small>
            </div>
            <div class="card-body" style="color: white !important;">
                <style>
                    .card-body input, .card-body select, .card-body textarea {
                        color: white !important;
                        background-color: rgba(0,0,0,0.8) !important;
                        border: 1px solid rgba(255,255,255,0.3) !important;
                    }
                    .card-body input::placeholder, .card-body textarea::placeholder {
                        color: rgba(255,255,255,0.7) !important;
                    }
                    .card-body .input-group-text {
                        color: white !important;
                        background-color: rgba(0,0,0,0.6) !important;
                        border-color: rgba(255,255,255,0.3) !important;
                    }
                    .card-body .form-control, .card-body .form-select {
                        border-color: rgba(255,255,255,0.3) !important;
                    }
                    .card-body .form-control:focus {
                        background-color: rgba(0,0,0,0.9) !important;
                        border-color: #007bff !important;
                        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
                    }
                </style>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="listrik_per_jam" class="form-label text-white">
                            <i class="fas fa-bolt text-warning me-2"></i>Listrik Mesin per Jam <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="listrik_per_jam" 
                                   id="listrik_per_jam" 
                                   class="form-control @error('listrik_per_jam') is-invalid @enderror" 
                                   value="{{ old('listrik_per_jam', $bopProses->listrik_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="5000"
                                   oninput="calculateTotal()"
                                   required>
                        </div>
                        @error('listrik_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya listrik untuk operasi mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="gas_bbm_per_jam" class="form-label text-white">
                            <i class="fas fa-fire text-danger me-2"></i>Gas/BBM per Jam <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="gas_bbm_per_jam" 
                                   id="gas_bbm_per_jam" 
                                   class="form-control @error('gas_bbm_per_jam') is-invalid @enderror" 
                                   value="{{ old('gas_bbm_per_jam', $bopProses->gas_bbm_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="3000"
                                   oninput="calculateTotal()"
                                   required>
                        </div>
                        @error('gas_bbm_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya bahan bakar untuk operasi mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="penyusutan_mesin_per_jam" class="form-label text-white">
                            <i class="fas fa-chart-line-down text-secondary me-2"></i>Penyusutan Mesin per Jam <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="penyusutan_mesin_per_jam" 
                                   id="penyusutan_mesin_per_jam" 
                                   class="form-control @error('penyusutan_mesin_per_jam') is-invalid @enderror" 
                                   value="{{ old('penyusutan_mesin_per_jam', $bopProses->penyusutan_mesin_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="2000"
                                   oninput="calculateTotal()"
                                   required>
                        </div>
                        @error('penyusutan_mesin_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Alokasi penyusutan mesin per jam operasi</small>
                    </div>

                    <div class="col-md-6">
                        <label for="maintenance_per_jam" class="form-label text-white">
                            <i class="fas fa-tools text-info me-2"></i>Maintenance per Jam <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="maintenance_per_jam" 
                                   id="maintenance_per_jam" 
                                   class="form-control @error('maintenance_per_jam') is-invalid @enderror" 
                                   value="{{ old('maintenance_per_jam', $bopProses->maintenance_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="1500"
                                   oninput="calculateTotal()"
                                   required>
                        </div>
                        @error('maintenance_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya perawatan dan maintenance mesin per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="gaji_mandor_per_jam" class="form-label text-white">
                            <i class="fas fa-user-tie text-success me-2"></i>Gaji Mandor per Jam <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="gaji_mandor_per_jam" 
                                   id="gaji_mandor_per_jam" 
                                   class="form-control @error('gaji_mandor_per_jam') is-invalid @enderror" 
                                   value="{{ old('gaji_mandor_per_jam', $bopProses->gaji_mandor_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="4000"
                                   oninput="calculateTotal()"
                                   required>
                        </div>
                        @error('gaji_mandor_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Alokasi gaji mandor/supervisor per jam</small>
                    </div>

                    <div class="col-md-6">
                        <label for="lain_lain_per_jam" class="form-label text-white">
                            <i class="fas fa-ellipsis-h text-muted me-2"></i>Lain-lain per Jam
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   name="lain_lain_per_jam" 
                                   id="lain_lain_per_jam" 
                                   class="form-control @error('lain_lain_per_jam') is-invalid @enderror" 
                                   value="{{ old('lain_lain_per_jam', $bopProses->lain_lain_per_jam) }}"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="1000"
                                   oninput="calculateTotal()">
                        </div>
                        @error('lain_lain_per_jam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-light">Biaya overhead lainnya per jam (opsional)</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Update BOP Proses
                    </button>
                    <a href="{{ route('master-data.bop.show-proses', $bopProses->id) }}" class="btn btn-info btn-lg">
                        <i class="fas fa-eye me-2"></i>Lihat Detail
                    </a>
                    <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});

function calculateTotal() {
    const listrik = parseFloat(document.getElementById('listrik_per_jam').value) || 0;
    const gas = parseFloat(document.getElementById('gas_bbm_per_jam').value) || 0;
    const penyusutan = parseFloat(document.getElementById('penyusutan_mesin_per_jam').value) || 0;
    const maintenance = parseFloat(document.getElementById('maintenance_per_jam').value) || 0;
    const mandor = parseFloat(document.getElementById('gaji_mandor_per_jam').value) || 0;
    const lainLain = parseFloat(document.getElementById('lain_lain_per_jam').value) || 0;
    
    const total = listrik + gas + penyusutan + maintenance + mandor + lainLain;
    const kapasitas = {{ $bopProses->prosesProduksi->kapasitas_per_jam }};
    const bopPerUnit = kapasitas > 0 ? total / kapasitas : 0;
    
    document.getElementById('totalBopPerJam').textContent = formatRupiahClean(total);
    document.getElementById('bopPerUnit').textContent = formatRupiahClean(bopPerUnit);
}

// Clean number formatting function
function formatNumberClean(number) {
    if (number == Math.floor(number)) {
        return number.toLocaleString('id-ID');
    }
    let formatted = number.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    // Remove trailing zeros after decimal
    if (formatted.includes(',')) {
        formatted = formatted.replace(/,?0+$/, '');
    }
    return formatted;
}

// Clean rupiah formatting function  
function formatRupiahClean(number) {
    return 'Rp ' + formatNumberClean(number);
}
</script>
@endsection
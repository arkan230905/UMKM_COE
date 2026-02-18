@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs me-2"></i>Detail BTKL
        </h2>
        <div>
            <a href="{{ route('master-data.btkl.edit', $prosesProduksi) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Detail Utama -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi BTKL: {{ $prosesProduksi->nama_proses }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 40%">Kode Proses:</td>
                                    <td><code class="fs-6">{{ $prosesProduksi->kode_proses }}</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Nama Proses:</td>
                                    <td>{{ $prosesProduksi->nama_proses }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Tarif BTKL:</td>
                                    <td>
                                        <span class="fs-5 fw-bold text-primary">Rp {{ number_format($prosesProduksi->tarif_btkl, 0, ',', '.') }}</span>
                                        <small class="text-muted">per {{ $prosesProduksi->satuan_btkl }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Satuan BTKL:</td>
                                    <td><span class="badge bg-secondary">{{ $prosesProduksi->satuan_btkl }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 40%">Kapasitas per Jam:</td>
                                    <td>
                                        <span class="fs-5 fw-bold text-info">{{ $prosesProduksi->kapasitas_per_jam ?? 0 }}</span>
                                        <small class="text-muted">unit/jam</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Biaya per Produk:</td>
                                    <td>
                                        @if($prosesProduksi->biaya_per_produk > 0)
                                            <span class="fs-5 fw-bold text-success">{{ $prosesProduksi->biaya_per_produk_formatted }}</span>
                                            <small class="text-muted">per unit</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Efisiensi:</td>
                                    <td>
                                        <span class="fs-6 fw-bold text-warning">{{ number_format($prosesProduksi->efisiensi_produksi, 4, ',', '.') }}</span>
                                        <small class="text-muted">unit/Rp</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Status:</td>
                                    <td>
                                        @if($prosesProduksi->tarif_btkl > 0 && $prosesProduksi->kapasitas_per_jam > 0)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-warning">Perlu Konfigurasi</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($prosesProduksi->deskripsi)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold">Deskripsi Proses:</h6>
                                <p class="text-muted">{{ $prosesProduksi->deskripsi }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Perhitungan & Analisis -->
        <div class="col-md-4">
            <!-- Perhitungan Detail -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Perhitungan Biaya
                    </h6>
                </div>
                <div class="card-body">
                    @if($prosesProduksi->kapasitas_per_jam > 0)
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="fs-4 fw-bold text-primary">Rp {{ number_format($prosesProduksi->tarif_btkl, 0, ',', '.') }}</div>
                                <small class="text-muted">Tarif per {{ $prosesProduksi->satuan_btkl }}</small>
                            </div>
                            
                            <div class="mb-3">
                                <i class="fas fa-divide text-muted"></i>
                            </div>
                            
                            <div class="mb-3">
                                <div class="fs-4 fw-bold text-info">{{ $prosesProduksi->kapasitas_per_jam }}</div>
                                <small class="text-muted">Unit per jam</small>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-0">
                                <div class="fs-3 fw-bold text-success">{{ $prosesProduksi->biaya_per_produk_formatted }}</div>
                                <small class="text-muted">Biaya per unit produk</small>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p>Kapasitas per jam belum diatur</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Simulasi Produksi -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Simulasi Produksi
                    </h6>
                </div>
                <div class="card-body">
                    @if($prosesProduksi->kapasitas_per_jam > 0)
                        <div class="mb-3">
                            <label class="form-label">Simulasi untuk berapa unit?</label>
                            <input type="number" id="simulasiUnit" class="form-control" value="100" min="1">
                        </div>
                        
                        <div id="hasilSimulasi">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold text-primary" id="waktuDiperlukan">-</div>
                                        <small class="text-muted">Jam diperlukan</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold text-success" id="totalBiayaBTKL">-</div>
                                        <small class="text-muted">Total biaya BTKL</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <p>Simulasi tidak tersedia</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penggunaan (jika ada) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Riwayat Penggunaan dalam Harga Pokok Produksi
                    </h6>
                </div>
                <div class="card-body">
                    @if($prosesProduksi->bomProses && $prosesProduksi->bomProses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Harga Pokok Produksi</th>
                                        <th>Produk</th>
                                        <th>Urutan</th>
                                        <th>Durasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prosesProduksi->bomProses as $bomProses)
                                        <tr>
                                            <td><code>{{ $bomProses->bom->kode_bom ?? '-' }}</code></td>
                                            <td>{{ $bomProses->bom->produk->nama ?? '-' }}</td>
                                            <td>{{ $bomProses->urutan }}</td>
                                            <td>{{ $bomProses->durasi }} {{ $prosesProduksi->satuan_btkl }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Proses ini belum digunakan dalam Harga Pokok Produksi manapun</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const simulasiInput = document.getElementById('simulasiUnit');
    const kapasitasPerJam = {{ $prosesProduksi->kapasitas_per_jam ?? 0 }};
    const biayaPerProduk = {{ $prosesProduksi->biaya_per_produk }};
    
    function updateSimulasi() {
        const unit = parseInt(simulasiInput.value) || 0;
        
        if (kapasitasPerJam > 0 && unit > 0) {
            const waktuDiperlukan = (unit / kapasitasPerJam).toFixed(2);
            const totalBiaya = (unit * biayaPerProduk);
            
            document.getElementById('waktuDiperlukan').textContent = waktuDiperlukan;
            document.getElementById('totalBiayaBTKL').textContent = 'Rp ' + totalBiaya.toLocaleString('id-ID');
        }
    }
    
    if (simulasiInput) {
        simulasiInput.addEventListener('input', updateSimulasi);
        updateSimulasi(); // Initial calculation
    }
});
</script>
@endsection
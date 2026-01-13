@extends('layouts.app')

@section('title', 'Jadwal Penyusutan')

@push('styles')
<style>
    /* Override body background specifically for this page */
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        min-height: 100vh !important;
    }
    
    .container-fluid {
        background-color: rgba(255, 255, 255, 0.95) !important;
        border-radius: 15px !important;
        padding: 20px !important;
        margin: 20px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
    }
    
    .card {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%) !important;
        border: none !important;
        border-radius: 15px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    }
    
    .card-header {
        background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%) !important;
        border-radius: 15px 15px 0 0 !important;
        color: white !important;
        font-weight: 600 !important;
    }
    
    .card-body {
        padding: 25px !important;
    }
    
    .table {
        background-color: white !important;
        border-radius: 10px !important;
        overflow: hidden !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
    }
    
    .table th {
        background: linear-gradient(90deg, #6c757d 0%, #495057 100%) !important;
        color: white !important;
        font-weight: 600 !important;
        border: none !important;
    }
    
    .table td {
        border-bottom: 1px solid #e9ecef !important;
        color: #495057 !important;
    }
    
    .modal-content {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%) !important;
        border-radius: 15px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
    }
    
    .modal-header {
        background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%) !important;
        border-radius: 15px 15px 0 0 !important;
        color: white !important;
        font-weight: 600 !important;
    }
    
    .btn-outline-primary {
        border: 2px solid #4a90e2 !important;
        color: #4a90e2 !important;
        background: transparent !important;
        border-radius: 20px !important;
        padding: 5px 15px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    
    .btn-outline-primary:hover {
        background: #4a90e2 !important;
        color: white !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3) !important;
    }
    
    .btn-secondary {
        background: linear-gradient(45deg, #6c757d 0%, #495057 100%) !important;
        border: none !important;
        color: white !important;
        font-weight: 600 !important;
        border-radius: 25px !important;
        padding: 10px 25px !important;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    
    .btn-secondary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4) !important;
        background: linear-gradient(45deg, #495057 0%, #343a40 100%) !important;
    }
    
    /* Override text colors */
    strong {
        color: #2c3e50 !important;
        font-weight: 600 !important;
    }
    
    .card-body div {
        color: #495057 !important;
    }
    
    h5, h6 {
        color: #2c3e50 !important;
        font-weight: 700 !important;
    }
    
    /* Force all text to be white */
    .container-fluid label,
    .container-fluid .form-label,
    .container-fluid h1,
    .container-fluid h2,
    .container-fluid h3,
    .container-fluid h4,
    .container-fluid h5,
    .container-fluid h6,
    .container-fluid .card-title,
    .container-fluid th,
    .container-fluid td,
    .container-fluid strong,
    .container-fluid div {
        color: #ffffff !important;
    }
    
    .table {
        color: #ffffff !important;
    }
    
    .table th,
    .table td {
        border-color: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
    }
    
    .modal-content {
        background-color: #212529 !important;
        color: #ffffff !important;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Jadwal Penyusutan — {{ $asset->nama_aset }}</h5>
            <a href="{{ route('master-data.aset.show', $asset->id) }}" class="btn btn-secondary">Kembali</a>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <strong>Harga Perolehan</strong>
                    <div>Rp {{ number_format((float)$asset->harga_perolehan, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Nilai Residu</strong>
                    <div>Rp {{ number_format((float)$asset->nilai_residu, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Umur Manfaat</strong>
                    <div>{{ $asset->umur_manfaat }} tahun</div>
                </div>
                <div class="col-md-3">
                    <strong>Tanggal Beli</strong>
                    <div>{{ optional($asset->tanggal_beli)->format('d M Y') }}</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>TAHUN</th>
                            <th class="text-end">PENYUSUTAN</th>
                            <th class="text-end">AKUMULASI</th>
                            <th class="text-end">NILAI BUKU</th>
                            <th>RINCIAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depreciation_schedule as $row)
                        <tr>
                            <td>{{ $row['tahun'] }}</td>
                            <td class="text-end">Rp {{ number_format((float)$row['beban_penyusutan'], 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format((float)$row['akumulasi_penyusutan'], 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format((float)$row['nilai_buku_akhir'], 2, ',', '.') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="showMonthlyDetail(
                                        {{ $row['tahun_int'] }},
                                        {{ (float)$row['beban_penyusutan'] }},
                                        {{ (float)$row['akumulasi_penyusutan'] }},
                                        {{ (float)$row['nilai_buku_akhir'] + $row['beban_penyusutan'] }}
                                    )">Detail</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada jadwal penyusutan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- =============================== -->
<!--     MODAL DETAIL BULANAN        -->
<!-- =============================== -->
<div class="modal fade" id="monthlyDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Penyusutan Bulanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="monthlyDetailContent">
                <!-- hasil perhitungan akan muncul di sini -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showMonthlyDetail(tahun, penyusutanTahunan, akumulasiAwal, nilaiBukuAwal) {
    const modal = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));
    const content = document.getElementById('monthlyDetailContent');
    
    const startYear = {{ optional($asset->tanggal_beli)->year ?? 'new Date().getFullYear()' }};
    const startMonth = {{ optional($asset->tanggal_beli)->month ?? '1' }};
    
    // Selalu tampilkan 12 bulan (Januari - Desember)
    const monthsToShow = 12;
    const monthlyDepreciation = penyusutanTahunan / 12;
    
    let accumulatedDepreciation = akumulasiAwal;
    let currentBookValue = nilaiBukuAwal;
    
    const monthNames = [
        'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];
    
    let html = `
        <div class="mb-3">
            <h6>Tahun ${tahun}</h6>
            <p><strong>Penyusutan per tahun:</strong> Rp ${numberFormat(penyusutanTahunan)}</p>
            <p><strong>Penyusutan per bulan:</strong> Rp ${numberFormat(monthlyDepreciation)}</p>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-end">Beban Penyusutan</th>
                        <th class="text-end">Akumulasi</th>
                        <th class="text-end">Nilai Buku</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    for (let i = 0; i < monthsToShow; i++) {
        accumulatedDepreciation += monthlyDepreciation;
        currentBookValue -= monthlyDepreciation;
        
        // Selalu mulai dari Januari (index 0) sampai Desember (index 11)
        const monthIndex = i;
        
        html += `
            <tr>
                <td>${monthNames[monthIndex]} ${tahun}</td>
                <td class="text-end">Rp ${numberFormat(monthlyDepreciation)}</td>
                <td class="text-end">Rp ${numberFormat(accumulatedDepreciation)}</td>
                <td class="text-end">Rp ${numberFormat(currentBookValue)}</td>
            </tr>
        `;
    }
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    content.innerHTML = html;
    modal.show();
}

function numberFormat(num) {
    num = Number(num) || 0;
    return num.toLocaleString('id-ID', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}
</script>
@endpush
@endsection
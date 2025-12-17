@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #1b1b28; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-file-earmark-text me-2"></i> Rekap Presensi Bulanan
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.presensi.index', ['bulan' => $bulan]) }}" class="btn btn-secondary fw-semibold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('master-data.presensi.export-ringkasan-pdf', ['bulan' => $bulan]) }}" class="btn btn-success fw-semibold shadow-sm">
                <i class="bi bi-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-lg border-0 mx-3" style="background-color: #222232; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
        <div class="card-header bg-transparent border-0 py-3 px-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-calendar-month me-2"></i>Rekap Presensi
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center justify-content-end">
                        <span class="badge bg-info fs-6">
                            <strong>{{ $bulanLabel }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-4 py-4">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0 custom-table">
                    <thead>
                        <tr>
                            <th class="ps-3 py-3">#</th>
                            <th>NAMA PEGAWAI</th>
                            <th class="text-center">HADIR</th>
                            <th class="text-center">SAKIT</th>
                            <th class="text-center">IZIN</th>
                            <th class="text-center">ALPHA</th>
                            <th class="text-center">TOTAL JAM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ringkasan as $index => $item)
                        <tr class="data-row">
                            <td class="ps-3 fw-bold text-light">{{ $index + 1 }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-person-circle" style="font-size: 24px; color: #6c63ff;"></i>
                                    <div>
                                        @if($item->pegawai)
                                            <div class="fw-semibold text-secondary" style="font-size: 15px;">
                                                {{ $item->pegawai->nama ?? $item->pegawai->kode_pegawai }}
                                            </div>
                                            <div style="color: #999; font-size: 11px; margin-top: 2px;">NIP: {{ $item->pegawai->kode_pegawai }}</div>
                                        @else
                                            <div class="fw-semibold text-danger" style="font-size: 15px;">
                                                Pegawai tidak ditemukan
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $item->total_hadir }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $item->total_sakit }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $item->total_izin }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $item->total_alpha }}</span>
                            </td>
                            <td class="text-center fw-bold text-primary">
                                {{ $item->total_jam_formatted }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <h5 class="mb-0">Tidak ada data presensi untuk bulan ini</h5>
                                <p class="mb-0">Pilih bulan lain atau tambahkan data presensi baru</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .custom-table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
        --bs-table-hover-bg: rgba(108, 99, 255, 0.1);
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
        color: var(--bs-table-color-state, var(--bs-table-color-type, var(--bs-table-color)));
    }
    
    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan animasi pada baris tabel
    const dataRows = document.querySelectorAll('.data-row');
    dataRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        row.style.transition = `opacity 0.3s ease-out ${index * 0.05}s, transform 0.3s ease-out ${index * 0.05}s`;
        
        // Trigger reflow
        void row.offsetWidth;
        
        // Tambahkan kelas untuk animasi
        row.style.opacity = '1';
        row.style.transform = 'translateY(0)';
    });
});
</script>
@endpush

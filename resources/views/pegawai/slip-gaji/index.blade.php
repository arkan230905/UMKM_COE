@extends('layouts.app')

@section('title', 'Slip Gaji')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Slip Gaji Saya
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filter Periode -->
                    <form method="GET" action="{{ route('pegawai.slip-gaji.index') }}" class="mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Bulan</label>
                                <select name="month" class="form-select">
                                    <option value="">Semua Bulan</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tahun</label>
                                <select name="year" class="form-select">
                                    <option value="">Semua Tahun</option>
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('pegawai.slip-gaji.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Info Pegawai -->
                    <div class="alert alert-info">
                        <i class="fas fa-user me-2"></i>
                        <strong>{{ $pegawai->nama }}</strong> - {{ strtoupper($pegawai->jenis_pegawai) }}
                        <span class="mx-2">|</span>
                        <i class="fas fa-building me-2"></i>
                        {{ $pegawai->jabatan ?? '-' }}
                    </div>
                </div>
            </div>

            <!-- Tabel Slip Gaji -->
            <div class="card">
                <div class="card-body">
                    @if($penggajians->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="text-center" style="width: 50px">No</th>
                                        <th>Periode</th>
                                        <th>Tanggal Penggajian</th>
                                        <th>Total Gaji</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($penggajians as $index => $penggajian)
                                        <tr>
                                            <td class="text-center">
                                                {{ ($penggajians->currentPage() - 1) * $penggajians->perPage() + $loop->iteration }}
                                            </td>
                                            <td>
                                                <span class="fw-semibold">
                                                    {{ $penggajian->tanggal_penggajian->format('F Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $penggajian->tanggal_penggajian->format('d F Y') }}
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">
                                                    Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($penggajian->status_pembayaran === 'lunas')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i> Lunas
                                                    </span>
                                                @elseif($penggajian->status_pembayaran === 'disetujui')
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-clock me-1"></i> Disetujui
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        {{ ucfirst($penggajian->status_pembayaran) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('pegawai.slip-gaji.show', $penggajian->id) }}"
                                                       class="btn btn-outline-primary"
                                                       data-bs-toggle="tooltip"
                                                       title="Lihat Slip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('pegawai.slip-gaji.pdf', $penggajian->id) }}"
                                                       class="btn btn-outline-success"
                                                       data-bs-toggle="tooltip"
                                                       title="Download PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-primary">
                                Menampilkan {{ $penggajians->firstItem() }} - {{ $penggajians->lastItem() }}
                                dari {{ $penggajians->total() }} data
                            </small>
                            {{ $penggajians->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice-dollar fa-3x text-info mb-3"></i>
                            <h5 class="text-primary">Belum ada slip gaji</h5>
                            <p class="text-dark">Slip gaji akan muncul setelah penggajian disetujui atau dibayar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

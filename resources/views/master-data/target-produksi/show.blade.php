@extends('layouts.app')

@section('title', 'Detail Target Produksi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-chart-line me-2"></i>Detail Target Produksi</h2>
            <p class="text-muted mb-0">{{ $target->produk->nama_produk }} - Tahun {{ $target->tahun }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('master-data.target-produksi.edit', $target->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('master-data.target-produksi.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Target Tahunan</h6>
                            <h3 class="mb-0">{{ number_format($target->total_target_tahunan, 0, ',', '.') }}</h3>
                            <small class="text-muted">Unit</small>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-bullseye fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Realisasi</h6>
                            <h3 class="mb-0">{{ number_format($summary['total_realisasi'], 0, ',', '.') }}</h3>
                            <small class="text-muted">Unit</small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Persentase</h6>
                            <h3 class="mb-0">{{ number_format($summary['persentase'], 1) }}%</h3>
                            <small class="text-muted">Pencapaian</small>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-percentage fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-{{ $summary['selisih'] >= 0 ? 'success' : 'danger' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Selisih</h6>
                            <h3 class="mb-0">{{ number_format($summary['selisih'], 0, ',', '.') }}</h3>
                            <small class="text-muted">Unit</small>
                        </div>
                        <div class="text-{{ $summary['selisih'] >= 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $summary['selisih'] >= 0 ? 'arrow-up' : 'arrow-down' }} fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Bulanan -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Target & Realisasi Bulanan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="12%">Bulan</th>
                            <th width="12%" class="text-end">Target</th>
                            <th width="8%" class="text-center">Hari Kerja</th>
                            <th width="12%" class="text-end">Target/Hari</th>
                            <th width="12%" class="text-end">Realisasi</th>
                            <th width="10%" class="text-end">Selisih</th>
                            <th width="8%" class="text-center">%</th>
                            <th width="11%" class="text-center">Status</th>
                            <th width="10%" class="text-center">Lock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparison as $item)
                            @php
                                $achievement = $item['persentase'];
                                $statusClass = $achievement >= 100 ? 'success' : ($achievement >= 75 ? 'warning' : 'danger');
                                $statusIcon = $achievement >= 100 ? 'check-circle' : ($achievement >= 75 ? 'exclamation-circle' : 'times-circle');
                                
                                // Get detail for this month
                                $detail = $target->details->firstWhere('bulan', $item['bulan']);
                                $hariKerja = $detail->hari_kerja ?? '-';
                                $targetPerHari = $detail->target_per_hari ?? 0;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $item['bulan'] }}</td>
                                <td><strong>{{ $item['nama_bulan'] }}</strong></td>
                                <td class="text-end">{{ number_format($item['target'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $hariKerja }} hari</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-primary">{{ number_format($targetPerHari, 2, ',', '.') }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-{{ $statusClass }}">
                                        {{ number_format($item['realisasi'], 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-{{ $item['selisih'] >= 0 ? 'success' : 'danger' }}">
                                        {{ $item['selisih'] >= 0 ? '+' : '' }}{{ number_format($item['selisih'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ number_format($achievement, 1) }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <i class="fas fa-{{ $statusIcon }} text-{{ $statusClass }} fa-lg"></i>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item['status'] === 'Locked' ? 'secondary' : 'success' }}">
                                        {{ $item['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="2" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>{{ number_format($target->total_target_tahunan, 0, ',', '.') }}</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-end"><strong>{{ number_format($summary['total_realisasi'], 0, ',', '.') }}</strong></td>
                            <td class="text-end">
                                <strong class="text-{{ $summary['selisih'] >= 0 ? 'success' : 'danger' }}">
                                    {{ $summary['selisih'] >= 0 ? '+' : '' }}{{ number_format($summary['selisih'], 0, ',', '.') }}
                                </strong>
                            </td>
                            <td class="text-center">
                                <strong class="badge bg-info">{{ number_format($summary['persentase'], 1) }}%</strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Log History -->
    @if($target->logs->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Perubahan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th width="15%">Tanggal</th>
                                <th width="15%">User</th>
                                <th width="15%">Aksi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($target->logs->take(10) as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $log->user->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'updated' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

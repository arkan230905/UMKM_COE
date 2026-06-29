@extends('layouts.app')

@section('title', 'Target Produksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Target Produksi</h2>
        <p class="text-muted mb-0">Kelola target produksi tahunan dan bulanan</p>
    </div>
    <a href="{{ route('master-data.target-produksi.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Buat Target Produksi
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Target Produksi</h5>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="mb-0">Filter Tahun:</label>
            <select name="tahun" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        @if($targets->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tahun</th>
                        <th>Produk</th>
                        <th class="text-end">Total Target</th>
                        <th class="text-end">Realisasi</th>
                        <th class="text-center">Pencapaian</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($targets as $index => $target)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="badge bg-info">{{ $target->tahun }}</span>
                        </td>
                        <td>
                            <strong>{{ $target->produk->nama_produk }}</strong><br>
                            <small class="text-muted">Kode: {{ $target->produk->kode_produk }}</small>
                        </td>
                        <td class="text-end">
                            <strong>{{ number_format($target->total_target_tahunan, 0, ',', '.') }}</strong> Unit
                        </td>
                        <td class="text-end">
                            <span class="text-{{ $target->total_realisasi >= $target->total_target_tahunan ? 'success' : 'warning' }}">
                                <strong>{{ number_format($target->total_realisasi, 0, ',', '.') }}</strong> Unit
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $color = match(true) {
                                    $target->persentase_pencapaian >= 100 => 'success',
                                    $target->persentase_pencapaian >= 80 => 'info',
                                    $target->persentase_pencapaian >= 60 => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ number_format($target->persentase_pencapaian, 1) }}%</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $target->status_color }}">{{ $target->status }}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('master-data.target-produksi.show', $target->id) }}" 
                                   class="btn btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.target-produksi.edit', $target->id) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.target-produksi.destroy', $target->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus target produksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <p class="text-muted">Belum ada target produksi untuk tahun {{ $tahun }}</p>
            <a href="{{ route('master-data.target-produksi.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Buat Target Produksi
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

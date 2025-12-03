@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Master Data BOP (Biaya Overhead Pabrik)</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Tabel Data BOP --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Aktual</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($akunBeban as $akun)
                            @php
                                $bop = $bops[$akun->kode_akun] ?? null;
                                $hasBudget = $bop && $bop->budget > 0;
                                $sisa = $hasBudget ? ($bop->budget - ($bop->aktual ?? 0)) : 0;
                                $textClass = $sisa < 0 ? 'text-danger' : 'text-success';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $akun->kode_akun }}</td>
                                <td>{{ $akun->nama_akun }}</td>
                                <td class="text-end">{{ $hasBudget ? number_format($bop->budget, 0, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $hasBudget ? number_format($bop->aktual ?? 0, 0, ',', '.') : '-' }}</td>
                                <td class="text-end {{ $textClass }}">
                                    {{ $hasBudget ? number_format($sisa, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($hasBudget)
                                        <a href="{{ route('master-data.bop.edit', $bop->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('master-data.bop.destroy', $bop->id) }}" 
                                              method="POST" 
                                              class="d-inline delete-bop-form"
                                              data-bop-id="{{ $bop->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger delete-bop-btn" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Hapus Budget">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('master-data.bop.create', ['kode_akun' => $akun->kode_akun]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Input
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data akun beban</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete button
        document.querySelectorAll('.delete-bop-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                if (confirm('Apakah Anda yakin ingin menghapus budget BOP ini?')) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush

@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-calculator me-2"></i>BOM Job Process Costing</h3>
        <a href="{{ route('master-data.bom-job-costing.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Buat BOM Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Daftar BOM Job Costing</h5>
                    <small class="text-muted">Perhitungan HPP: BBB + BTKL + Bahan Pendukung + BOP</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="12%">Kode BOM</th>
                            <th width="22%">Nama BOM</th>
                            <th class="text-center" width="10%">Jumlah Produk</th>
                            <th class="text-end" width="15%">Total HPP</th>
                            <th class="text-end" width="15%">HPP/Unit</th>
                            <th width="10%">Periode</th>
                            <th class="text-center" width="11%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $index => $bom)
                        <tr>
                            <td>{{ ($boms->currentPage() - 1) * $boms->perPage() + $index + 1 }}</td>
                            <td><code>{{ $bom->kode_bom }}</code></td>
                            <td>{{ $bom->nama_bom }}</td>
                            <td class="text-center">{{ number_format($bom->jumlah_produk) }} pcs</td>
                            <td class="text-end fw-bold">Rp {{ number_format($bom->total_hpp, 0, ',', '.') }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($bom->hpp_per_unit, 0, ',', '.') }}</td>
                            <td>{{ $bom->periode }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master-data.bom-job-costing.show', $bom->id) }}" 
                                       class="btn btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('master-data.bom-job-costing.edit', $bom->id) }}" 
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('master-data.bom-job-costing.print', $bom->id) }}" 
                                       class="btn btn-outline-secondary" title="Cetak" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $bom->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $bom->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                Yakin ingin menghapus BOM <strong>{{ $bom->nama_bom }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('master-data.bom-job-costing.destroy', $bom->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    Belum ada data BOM Job Costing
                                </div>
                                <a href="{{ route('master-data.bom-job-costing.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-plus"></i> Buat BOM Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($boms->hasPages())
        <div class="card-footer bg-white">
            {{ $boms->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

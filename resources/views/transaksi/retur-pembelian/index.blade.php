@extends('layouts.app')

@push('styles')
<style>
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
    vertical-align: middle;
}
.table td {
    vertical-align: middle;
}
.text-success {
    color: #198754 !important;
}
.action-buttons {
    display: flex;
    gap: 5px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}
.action-buttons .btn {
    margin: 2px;
    min-width: 35px;
}
.action-buttons form {
    margin: 0;
}
.status-badge {
    min-width: 100px;
    text-align: center;
}
.jenis-retur-badge {
    min-width: 90px;
    text-align: center;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Retur Pembelian</h1>
        <a href="{{ route('transaksi.retur-pembelian.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Retur Pembelian
        </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ERROR MESSAGES --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Terjadi Kesalahan:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            {{-- DATA COUNT INFO --}}
            <div class="mb-3">
                <small class="text-muted">Total data: {{ $returs->count() }} retur</small>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Tanggal</th>
                            <th width="15%">No Retur</th>
                            <th width="15%">Vendor</th>
                            <th width="12%">Jenis Retur</th>
                            <th width="10%">Status</th>
                            <th width="18%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returs as $retur)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($retur->return_date)
                                        {{ $retur->return_date->format('d/m/Y') }}
                                    @else
                                        {{ date('d/m/Y', strtotime($retur->created_at)) }}
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $retur->return_number }}</strong>
                                    @if($retur->calculated_total > 0)
                                        <br><small class="text-success">Rp {{ number_format($retur->calculated_total, 0, ',', '.') }}</small>
                                    @elseif($retur->total_retur > 0)
                                        <br><small class="text-success">Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($retur->pembelian_id && $retur->pembelian)
                                        <a href="{{ route('transaksi.pembelian.show', $retur->pembelian_id) }}" class="text-decoration-none">
                                            {{ $retur->pembelian->vendor->nama_vendor ?? 'Vendor' }}
                                        </a>
                                        <br><small class="text-muted">{{ $retur->pembelian->nomor_pembelian ?? 'Pembelian #' . $retur->pembelian_id }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($retur->jenis_retur === 'refund')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-money-bill-wave me-1"></i>Refund
                                        </span>
                                    @elseif($retur->jenis_retur === 'tukar_barang')
                                        <span class="badge bg-info">
                                            <i class="fas fa-exchange-alt me-1"></i>Tukar Barang
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $retur->jenis_retur_display }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $retur->status_badge['class'] }}">
                                        {{ $retur->status_badge['text'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                <td class="text-center">
                                    <div class="action-buttons">
                                        {{-- Detail Button --}}
                                        <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Dynamic Action Button (menggunakan method model) --}}
                                        @if($retur->action_button)
                                            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin melanjutkan ke tahap berikutnya?')">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $retur->action_button['class'] }}" 
                                                        title="{{ $retur->action_button['text'] }}">
                                                    @if(in_array($retur->status, ['pending', 'menunggu_acc']))
                                                        <i class="fas fa-check me-1"></i>
                                                    @elseif($retur->status == 'disetujui')
                                                        <i class="fas fa-shipping-fast me-1"></i>
                                                    @elseif($retur->status == 'dikirim' && $retur->jenis_retur == 'tukar_barang')
                                                        <i class="fas fa-cogs me-1"></i>
                                                    @elseif($retur->status == 'dikirim' && $retur->jenis_retur == 'refund')
                                                        <i class="fas fa-handshake me-1"></i>
                                                    @elseif($retur->status == 'diproses')
                                                        <i class="fas fa-check-circle me-1"></i>
                                                    @elseif($retur->status == 'diterima')
                                                        <i class="fas fa-money-bill-wave me-1"></i>
                                                    @endif
                                                    {{ $retur->action_button['text'] }}
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Delete Button (only if not completed) --}}
                                        @if(!in_array($retur->status, ['selesai', 'refund_selesai']))
                                            <form action="{{ route('transaksi.retur-pembelian.destroy', $retur->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus retur ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">Belum ada data retur pembelian</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success messages after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-success');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endpush

@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-money-check-alt me-2"></i>Pembayaran Beban
        </h2>
        <a href="{{ route('transaksi.pembayaran-beban.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Pembayaran
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.pembayaran-beban.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" 
                               value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" 
                               value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Beban Operasional</label>
                        <select name="beban_operasional_id" class="form-select">
                            <option value="">Semua Beban</option>
                            @foreach($bebanOperasional ?? [] as $bo)
                                <option value="{{ $bo->id }}" {{ request('beban_operasional_id') == $bo->id ? 'selected' : '' }}>
                                    {{ $bo->nama_beban }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Akun Beban</label>
                        <select name="akun_beban_id" class="form-select">
                            <option value="">Semua Akun Beban</option>
                            @foreach($coaBebans ?? [] as $coa)
                                <option value="{{ $coa->kode_akun }}" {{ request('akun_beban_id') == $coa->kode_akun ? 'selected' : '' }}>
                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Akun Kas/Bank</label>
                        <select name="akun_kas_id" class="form-select">
                            <option value="">Semua Kas/Bank</option>
                            @foreach($coaKas ?? [] as $coa)
                                <option value="{{ $coa->kode_akun }}" {{ request('akun_kas_id') == $coa->kode_akun ? 'selected' : '' }}>
                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Pembayaran Beban
                @if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'akun_beban_id', 'akun_kas_id']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th>Tanggal</th>
                            <th>Beban Operasional</th>
                            <th>Akun Beban</th>
                            <th>Akun Kas/Bank</th>
                            <th class="text-end">Jumlah Pembayaran</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $key => $item)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->nama_beban_operasional }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-coins text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->coaBeban->nama_akun ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $item->coaBeban->kode_akun ?? 'Unknown' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-money-bill-wave text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->coaKasBank->nama_akun ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $item->coaKasBank->kode_akun ?? 'Unknown' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-info">{{ $item->nominal_pembayaran_formatted }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transaksi.pembayaran-beban.show', $item->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('transaksi.pembayaran-beban.print', $item->id) }}" class="btn btn-outline-secondary" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <form action="{{ route('transaksi.pembayaran-beban.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pembayaran beban</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            responsive: true,
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 5] },
                { className: 'text-right', targets: [4] },
                { width: '100px', targets: [5] }
            ]
        });
    });
</script>
@endpush

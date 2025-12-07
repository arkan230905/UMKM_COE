@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Retur Saya</h2>
        <a href="{{ route('pelanggan.returns.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajukan Retur</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Kode Retur (Memo)</th>
                            <th>Tanggal</th>
                            <th>Referensi</th>
                            <th>Kompensasi</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returs as $r)
                        <tr>
                            <td>{{ $r->memo }}</td>
                            <td>{{ $r->created_at->format('d M Y') }}</td>
                            <td>#{{ optional(App\Models\Order::find($r->ref_id))->nomor_order ?? '-' }}</td>
                            <td class="text-capitalize">{{ $r->kompensasi }}</td>
                            <td class="text-capitalize">{{ $r->status ?? 'draft' }}</td>
                            <td class="fw-bold">Rp {{ number_format($r->jumlah, 0, ',', '.') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $r->id }}">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada data retur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">{{ $returs->links() }}</div>
        </div>
    </div>
</div>

{{-- ======================== --}}
{{-- MODAL DETAIL PER RETUR --}}
{{-- ======================== --}}
@forelse($returs as $r)
<!-- Modal Detail Retur -->
<div class="modal fade" id="detailModal{{ $r->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Retur: {{ $r->memo }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Tanggal:</strong> {{ $r->created_at->format('d M Y') }}</div>
                    <div class="col-md-6"><strong>Referensi:</strong> #{{ optional(App\Models\Order::find($r->ref_id))->nomor_order ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Kompensasi:</strong> {{ ucfirst($r->kompensasi) }}</div>
                    <div class="col-md-6"><strong>Status:</strong> {{ ucfirst($r->status ?? 'draft') }}</div>
                </div>

                @if($r->alasan)
                <div class="mb-3">
                    <strong>Alasan:</strong><br>
                    <span class="text-muted">{{ $r->alasan }}</span>
                </div>
                @endif

                <h6 class="mt-4 mb-3">Item yang Diretur</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($r->details as $d)
                            <tr>
                                <td>{{ optional($d->produk)->nama_produk ?? '-' }}</td>
                                <td class="text-end">{{ $d->qty }}</td>
                                <td class="text-end">Rp {{ number_format($d->harga_satuan_asal, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($d->qty * $d->harga_satuan_asal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada item.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end">Rp {{ number_format($r->jumlah, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@empty
{{-- Tidak ada retur → tidak perlu modal --}}
@endforelse

@endsection

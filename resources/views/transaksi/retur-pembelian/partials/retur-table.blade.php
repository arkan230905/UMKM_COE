{{-- Komponen Tabel Retur yang Dapat Digunakan Bersama --}}
@php
    $showCreateButton = $showCreateButton ?? true;
    $showTitle = $showTitle ?? true;
    $tableClass = $tableClass ?? 'table table-bordered table-hover';
@endphp

<div class="retur-table-container">
    {{-- Header Section --}}
    @if($showTitle)
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>{{ $pageTitle ?? 'Retur Pembelian' }}</h1>
                <small class="text-muted">Total data: {{ $returs->count() }} retur</small>
            </div>
            @if($showCreateButton)
                <a href="{{ route('transaksi.retur-pembelian.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Retur Pembelian
                </a>
            @endif
        </div>
    @endif

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

    {{-- Table Section --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="{{ $tableClass }}">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th class="text-center" width="8%">Tanggal</th>
                            <th class="text-center" width="12%">No Retur</th>
                            <th class="text-center" width="12%">No Transaksi</th>
                            <th class="text-center" width="12%">Vendor</th>
                            <th class="text-center" width="15%">Item</th>
                            <th class="text-center" width="10%">Jenis Retur</th>
                            <th class="text-center" width="8%">Status</th>
                            <th class="text-center" width="18%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returs as $retur)
                            <tr @if(session('new_retur_id') == $retur->id) class="table-success" @endif>
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
                                    @if(session('new_retur_id') == $retur->id)
                                        <span class="badge bg-success ms-1">BARU</span>
                                    @endif
                                </td>
                                <td>
                                    @if($retur->pembelian_id && $retur->pembelian)
                                        <a href="{{ route('transaksi.pembelian.show', $retur->pembelian_id) }}" class="text-decoration-none">
                                            {{ $retur->pembelian->nomor_pembelian ?? 'Pembelian #' . $retur->pembelian_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($retur->pembelian_id && $retur->pembelian)
                                        {{ $retur->pembelian->vendor->nama_vendor ?? 'Vendor' }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($retur->items && $retur->items->count() > 0)
                                        @foreach($retur->items->take(2) as $item)
                                            <div class="mb-1">
                                                @if($item->bahan_baku_id)
                                                    <small>BB - {{ $item->bahanBaku->nama_bahan ?? 'Unknown' }}</small>
                                                @elseif($item->bahan_pendukung_id)
                                                    <small>BP - {{ $item->bahanPendukung->nama_bahan ?? 'Unknown' }}</small>
                                                @else
                                                    <small class="text-muted">Unknown Item</small>
                                                @endif
                                                <br><small class="text-muted">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</small>
                                            </div>
                                        @endforeach
                                        @if($retur->items->count() > 2)
                                            <small class="text-muted">+{{ $retur->items->count() - 2 }} item lainnya</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($retur->jenis_retur === 'refund')
                                        <i class="fas fa-money-bill-wave me-1"></i>Refund
                                    @elseif($retur->jenis_retur === 'tukar_barang')
                                        <i class="fas fa-exchange-alt me-1"></i>Tukar Barang
                                    @else
                                        {{ $retur->jenis_retur_display }}
                                    @endif
                                </td>
                                <td>
                                    @include('transaksi.retur-pembelian.status-badge', ['status' => $retur->status])
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        {{-- Detail Button --}}
                                        <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Dynamic Action Buttons --}}
                                        @include('transaksi.retur-pembelian.action-buttons-clean', ['retur' => $retur])

                                        {{-- Delete Button (only if not completed) --}}
                                        @if($retur->status != 'selesai')
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
                                <td colspan="9" class="text-center py-4">
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

{{-- Shared Styles --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/retur-pembelian.css') }}">
@endpush

{{-- Shared Scripts --}}
@push('scripts')
@if(session('new_retur_id'))
<script>
// Auto-scroll to new retur if exists
document.addEventListener('DOMContentLoaded', function() {
    const newReturRow = document.querySelector('tr.table-success');
    if (newReturRow) {
        setTimeout(function() {
            newReturRow.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            // Add a subtle animation to highlight the new row
            newReturRow.style.animation = 'pulse 2s ease-in-out';
        }, 500);
    }
    
    console.log('New retur created with ID: {{ session("new_retur_id") }}');
});
</script>
@endif
@endpush
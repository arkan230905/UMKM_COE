<!-- Retur Content -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-undo me-2"></i>
                <span>Daftar Retur Pembelian</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px">No</th>
                        <th>Tanggal Retur</th>
                        <th>No Retur</th>
                        <th>Vendor</th>
                        <th>Jenis Retur</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Debug information
                        \Log::info('Retur data in view:', [
                            'count' => $returs->count(),
                            'returs' => $returs->toArray()
                        ]);
                    @endphp
                    @forelse ($returs as $key => $retur)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>
                                @if($retur->return_date)
                                    {{ $retur->return_date instanceof \Carbon\Carbon ? $retur->return_date->format('d-m-Y') : $retur->return_date }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $retur->return_number ?? '-' }}</td>
                            <td>
                                @if($retur->pembelian && $retur->pembelian->vendor)
                                    {{ $retur->pembelian->vendor->nama_vendor }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($retur->jenis_retur === 'tukar_barang')
                                    Tukar Barang
                                @elseif($retur->jenis_retur === 'refund')
                                    Refund (Pengembalian Uang)
                                @else
                                    {{ $retur->jenis_retur ?? 'Tidak Diketahui' }}
                                @endif
                            </td>
                            <td>
                                @php
                                    try {
                                        $statusBadge = $retur->status_badge;
                                    } catch (\Exception $e) {
                                        $statusBadge = ['class' => 'bg-secondary', 'text' => $retur->status ?? 'Unknown'];
                                        \Log::error('Status badge error: ' . $e->getMessage());
                                    }
                                @endphp
                                <span class="badge {{ $statusBadge['class'] }}">
                                    {{ $statusBadge['text'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <!-- Detail Button (Always Available) -->
                                    <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
                                       class="btn btn-sm btn-info" title="Detail Retur">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </a>
                                    
                                    <!-- Dynamic Action Button Based on Status -->
                                    @if($retur->action_button)
                                        <form method="POST" action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                                              style="display: inline-block;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $retur->action_button['class'] }}" 
                                                    title="{{ $retur->action_button['text'] }}"
                                                    onclick="return confirm('Yakin ingin mengubah status ke {{ $retur->status_badge['text'] }}?')">
                                                <i class="fas fa-arrow-right me-1"></i>{{ $retur->action_button['text'] }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-undo fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data retur pembelian</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
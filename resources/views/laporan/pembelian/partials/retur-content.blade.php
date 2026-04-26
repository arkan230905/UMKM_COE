<!-- Filter Form for Purchase Returns -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <input type="hidden" name="tab" value="retur">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="purchase_start_date" class="form-control" value="{{ request('purchase_start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="purchase_end_date" class="form-control" value="{{ request('purchase_end_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="purchase_status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('purchase_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="disetujui" {{ request('purchase_status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="dikirim" {{ request('purchase_status') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                    <option value="selesai" {{ request('purchase_status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('laporan.pembelian.index') }}?tab=retur" class="btn btn-secondary w-100">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Card for Purchase Returns -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="card-title text-dark">Total Retur Pembelian</h6>
                <h4 class="mb-0 text-dark">Rp {{ number_format($totalPurchaseReturns ?? 0, 0, ',', '.') }}</h4>
                <small class="text-dark opacity-75">
                    @if(request('purchase_start_date') && request('purchase_end_date'))
                        {{ \Carbon\Carbon::parse(request('purchase_start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('purchase_end_date'))->format('d/m/Y') }}
                    @else
                        Semua Periode
                    @endif
                    <br><i class="fas fa-info-circle me-1"></i>Sudah termasuk PPN (sesuai pembelian)
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Returns Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-retur">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:5%">No</th>
                        <th class="text-center nowrap">No. Retur</th>
                        <th class="text-center nowrap">Tanggal</th>
                        <th class="text-center nowrap">No. Transaksi</th>
                        <th class="text-center nowrap">Vendor</th>
                        <th class="text-center nowrap">Jenis Retur</th>
                        <th class="text-center">Item Diretur</th>
                        <th class="text-center">Alasan</th>
                        <th class="text-center nowrap">Total Retur</th>
                        <th class="text-center nowrap">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseReturns ?? [] as $index => $return)
                        <tr>
                            <td class="text-center">{{ ($purchaseReturns->firstItem() ?? 0) + $index }}</td>
                            <td class="text-center nowrap"><strong>{{ $return->return_number ?? 'RET-' . str_pad($return->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td class="text-center nowrap">{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center nowrap">
                                @if($return->pembelian)
                                    <strong>{{ $return->pembelian->nomor_pembelian }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center nowrap">{{ $return->pembelian->vendor->nama_vendor ?? '-' }}</td>
                            <td class="text-center nowrap">
                                @if($return->jenis_retur === 'tukar_barang')
                                    Tukar Barang
                                @elseif($return->jenis_retur === 'refund')
                                    Refund
                                @else
                                    {{ $return->jenis_retur ?? 'Tidak Diketahui' }}
                                @endif
                            </td>
                            <td>
                                @if($return->items && $return->items->count() > 0)
                                    <div class="small">
                                        @foreach($return->items as $item)
                                            <div class="mb-1">
                                                @if($item->bahanBaku)
                                                    • <span class="text-primary fw-semibold">BB</span> {{ $item->bahanBaku->nama_bahan }}
                                                @elseif($item->bahanPendukung)
                                                    • <span class="text-info fw-semibold">BP</span> {{ $item->bahanPendukung->nama_bahan }}
                                                @else
                                                    • Item tidak diketahui
                                                @endif
                                                <span class="text-muted">
                                                    ({{ number_format($item->quantity ?? 0, 2) }} {{ $item->unit ?? 'unit' }})
                                                </span>
                                                - Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                                                = <strong>Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $return->reason ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $subtotal = $return->total_retur ?? 0;
                                    // Get PPN from pembelian, default to 11% if not set
                                    $ppnPersen = $return->pembelian->ppn_persen ?? 11;
                                    $ppnAmount = $subtotal * ($ppnPersen / 100);
                                    $totalWithPpn = $subtotal + $ppnAmount;
                                @endphp
                                <div class="small text-muted">
                                    Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}<br>
                                    PPN {{ $ppnPersen }}%: Rp {{ number_format($ppnAmount, 0, ',', '.') }}
                                </div>
                                <strong class="text-primary">Rp {{ number_format($totalWithPpn, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusBadge = $return->status_badge ?? ['class' => 'bg-secondary', 'text' => ucfirst($return->status ?? 'Unknown')];
                                @endphp
                                {{ $statusBadge['text'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Tidak ada data retur pembelian</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(isset($purchaseReturns) && $purchaseReturns->hasPages())
        <div class="card-footer">
            {{ $purchaseReturns->withQueryString()->links() }}
        </div>
    @endif
</div>
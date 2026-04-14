<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <input type="hidden" name="tab" value="pembelian">
            <div class="col-md-4">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select">
                    <option value="">Semua Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->nama_vendor }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian</h5>
                <h3 class="mb-0 text-dark">Rp {{ number_format($totalPembelianFiltered, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75">
                    @if(request('start_date') && request('end_date'))
                        {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                    @else
                        Semua Periode
                    @endif
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian Tunai</h5>
                <h3 class="mb-0 text-dark">Rp {{ number_format($totalPembelianTunai, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75">Pembayaran Cash</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian Belum Lunas</h5>
                <h3 class="mb-0 text-dark">Rp {{ number_format($totalPembelianBelumLunas, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75">Sisa Utang</small>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:5%">No</th>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th>Item Dibeli</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Metode Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th style="width:12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pembelian as $index => $p)
                        <tr>
                            <td>{{ $pembelian->firstItem() + $index }}</td>
                            <td><strong>{{ $p->nomor_pembelian ?? '-' }}</strong></td>
                            <td>{{ optional($p->tanggal)->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $p->vendor->nama_vendor ?? '-' }}</td>
                            <td>
                                @if($p->details && $p->details->count() > 0)
                                    <div class="small">
                                        @foreach($p->details as $detail)
                                            <div class="mb-1">
                                                • 
                                                @if($detail->tipe_item === 'bahan_baku' && $detail->bahanBaku)
                                                    {{ $detail->bahanBaku->nama_bahan }}
                                                @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung)
                                                    {{ $detail->bahanPendukung->nama_bahan }}
                                                @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                                    {{ $detail->bahanPendukung->nama_bahan }}
                                                @elseif($detail->bahan_baku_id && $detail->bahanBaku)
                                                    {{ $detail->bahanBaku->nama_bahan }}
                                                @else
                                                    Item
                                                @endif
                                                <span class="text-muted">
                                                    ({{ number_format($detail->jumlah ?? 0, 0, ',', '.') }} 
                                                    @php
                                                        $satuanItem = 'unit';
                                                        if ($detail->bahan_baku_id && $detail->bahanBaku) {
                                                            $satuanItem = $detail->satuan ?: ($detail->bahanBaku->satuan->nama ?? 'unit');
                                                        } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                                                            $satuanItem = $detail->satuan ?: ($detail->bahanPendukung->satuanRelation->nama ?? 'unit');
                                                        } elseif ($detail->bahan_baku_id) {
                                                            $satuanItem = $detail->satuan ?: 'unit';
                                                        } elseif ($detail->bahan_pendukung_id) {
                                                            $satuanItem = $detail->satuan ?: 'unit';
                                                        }
                                                    @endphp
                                                    {{ $satuanItem }})
                                                </span>
                                                - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Detail tidak tersedia
                                    </span>
                                    <div class="small text-muted mt-1">
                                        Total: Rp {{ number_format($p->total_harga ?? 0, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end">
                                @php
                                    $totalPembelian = 0;
                                    if ($p->details && $p->details->count() > 0) {
                                        $totalPembelian = $p->details->sum(function($detail) {
                                            return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                        });
                                    }
                                    if ($p->total_harga > $totalPembelian) {
                                        $totalPembelian = $p->total_harga;
                                    }
                                @endphp
                                <strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-center">
                                @php
                                    $paymentMethodText = '';
                                    switch($p->payment_method) {
                                        case 'cash':
                                            $paymentMethodText = 'Tunai';
                                            break;
                                        case 'transfer':
                                            $paymentMethodText = 'Transfer';
                                            break;
                                        case 'credit':
                                            $paymentMethodText = 'Kredit';
                                            break;
                                        default:
                                            $paymentMethodText = ucfirst($p->payment_method ?? 'Tunai');
                                    }
                                @endphp
                                {{ $paymentMethodText }}
                            </td>
                            <td class="text-center">
                                @php
                                    $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $p->id)->exists();
                                    $statusText = $hasRetur ? 'Ada Retur' : 'Tidak Ada Retur';
                                @endphp
                                {{ $statusText }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('transaksi.pembelian.show', $p) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('laporan.pembelian.invoice', $p) }}" target="_blank" class="btn btn-sm btn-primary" title="Cetak Invoice">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Tidak ada data pembelian</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pembelian->hasPages())
        <div class="card-footer">
            {{ $pembelian->withQueryString()->links('vendor.pagination.custom-small') }}
        </div>
    @endif
</div>
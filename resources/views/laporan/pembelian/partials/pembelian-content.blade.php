<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <input type="hidden" name="tab" value="pembelian">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label class="form-label">Jenis Bahan</label>
                <select name="jenis_bahan" class="form-select">
                    <option value="">Semua</option>
                    <option value="bahan_baku" {{ request('jenis_bahan') == 'bahan_baku' ? 'selected' : '' }}>Bahan Baku</option>
                    <option value="bahan_pendukung" {{ request('jenis_bahan') == 'bahan_pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cari Bahan</label>
                <input type="text" name="search_bahan" class="form-control" placeholder="Cari nama bahan..." value="{{ request('search_bahan') }}">
            </div>
            <div class="col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('laporan.pembelian.index') }}?tab=pembelian" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Filter Summary (shown when filter is active) -->
@if(request('jenis_bahan') || request('search_bahan'))
<div class="card mb-4 border-info">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Hasil Filter Bahan
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-search text-info me-2"></i>
                    <strong>Filter Aktif:</strong>
                </div>
                <ul class="mb-0">
                    @if(request('search_bahan'))
                        <li>Nama Bahan: <span class="badge bg-primary">{{ request('search_bahan') }}</span></li>
                    @endif
                    @if(request('jenis_bahan'))
                        <li>Jenis: <span class="badge bg-success">{{ request('jenis_bahan') == 'bahan_baku' ? 'Bahan Baku' : 'Bahan Pendukung' }}</span></li>
                    @endif
                </ul>
            </div>
            <div class="col-md-6">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-3 bg-light">
                            <div class="text-muted small">Total Qty Dibeli</div>
                            <h4 class="mb-0 text-primary">{{ number_format($totalQtyBahan ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 bg-light">
                            <div class="text-muted small">Total Pembelian Bahan</div>
                            <h4 class="mb-0 text-success">Rp {{ number_format($totalNominalBahan ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Summary Cards -->
<div class="row mb-4 summary-grid">
    <div class="col">
        <div class="card bg-primary text-dark h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-dark mb-2">Total Pembelian</h5>
                <h3 class="mb-2 text-dark">Rp {{ number_format($totalPembelianFiltered, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75 mt-auto">
                    @if(request('start_date') && request('end_date'))
                        {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                    @else
                        Semua Periode
                    @endif
                </small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-success text-dark h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-dark mb-2">Total Pembelian Tunai</h5>
                <h3 class="mb-2 text-dark">Rp {{ number_format($totalPembelianTunai, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75 mt-auto">Pembayaran Cash</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-info text-dark h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-dark mb-2">Total Pembelian Kredit</h5>
                <h3 class="mb-2 text-dark">Rp {{ number_format($totalPembelianKredit, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75 mt-auto">Pembayaran Kredit</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-secondary text-dark h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-dark mb-2">Total Pembelian Non Tunai</h5>
                <h3 class="mb-2 text-dark">Rp {{ number_format($totalPembelianNonTunai, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75 mt-auto">Pembayaran Transfer</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-dark mb-2">Total Belum Lunas</h5>
                <h3 class="mb-2 text-dark">Rp {{ number_format($totalPembelianBelumLunas, 0, ',', '.') }}</h3>
                <small class="text-dark opacity-75 mt-auto">Sisa Utang</small>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-pembelian">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:5%">No</th>
                        <th class="text-center nowrap">No. Transaksi</th>
                        <th class="text-center nowrap">Tanggal</th>
                        <th class="text-center nowrap">Vendor</th>
                        <th class="text-center">Item Dibeli</th>
                        <th class="text-center nowrap">Total</th>
                        <th class="text-center nowrap">Metode Pembayaran</th>
                        <th class="text-center nowrap">Status</th>
                        <th class="text-center nowrap" style="width:12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pembelian as $index => $p)
                        <tr>
                            <td class="text-center">{{ ($pembelian->currentPage() - 1) * $pembelian->perPage() + $index + 1 }}</td>
                            <td class="text-center nowrap"><strong>{{ $p->nomor_pembelian ?? '-' }}</strong></td>
                            <td class="text-center nowrap">{{ optional($p->tanggal)->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-center nowrap">{{ $p->vendor->nama_vendor ?? '-' }}</td>
                            <td class="text-center">
                                @if($p->details && $p->details->count() > 0)
                                    @php
                                        // Filter details based on search and jenis_bahan
                                        $filteredDetails = $p->details;
                                        
                                        // Filter by jenis_bahan
                                        if(request('jenis_bahan')) {
                                            $filteredDetails = $filteredDetails->filter(function($detail) {
                                                if(request('jenis_bahan') === 'bahan_baku') {
                                                    return $detail->tipe_item === 'bahan_baku' || $detail->bahan_baku_id;
                                                } elseif(request('jenis_bahan') === 'bahan_pendukung') {
                                                    return $detail->tipe_item === 'bahan_pendukung' || $detail->bahan_pendukung_id;
                                                }
                                                return true;
                                            });
                                        }
                                        
                                        // Filter by search_bahan
                                        if(request('search_bahan')) {
                                            $searchBahan = strtolower(request('search_bahan'));
                                            $filteredDetails = $filteredDetails->filter(function($detail) use ($searchBahan) {
                                                $namaBahan = '';
                                                if($detail->bahanBaku) {
                                                    $namaBahan = strtolower($detail->bahanBaku->nama_bahan);
                                                } elseif($detail->bahanPendukung) {
                                                    $namaBahan = strtolower($detail->bahanPendukung->nama_bahan);
                                                }
                                                return strpos($namaBahan, $searchBahan) !== false;
                                            });
                                        }
                                    @endphp
                                    
                                    @if($filteredDetails->count() > 0)
                                        <div class="small text-center">
                                            @foreach($filteredDetails as $detail)
                                                <div class="mb-1 text-center">
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
                                                    @php
                                                        $qty = $detail->jumlah ?? 0;
                                                        // Remove .00 from whole numbers
                                                        $qtyFormatted = ($qty == floor($qty)) ? number_format($qty, 0, ',', '.') : number_format($qty, 2, ',', '.');
                                                    @endphp
                                                    <span class="text-muted">- {{ $qtyFormatted }}</span>
                                                    - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                    = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-filter"></i> Tidak ada item yang sesuai filter
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Detail tidak tersedia
                                    </span>
                                    <div class="small text-muted text-center mt-1">
                                        Total: Rp {{ number_format($p->total_harga ?? 0, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    // Calculate total based on filtered details
                                    $totalPembelian = 0;
                                    if ($p->details && $p->details->count() > 0) {
                                        // Use filtered details if filter is active
                                        if(request('jenis_bahan') || request('search_bahan')) {
                                            $detailsToSum = $p->details;
                                            
                                            // Filter by jenis_bahan
                                            if(request('jenis_bahan')) {
                                                $detailsToSum = $detailsToSum->filter(function($detail) {
                                                    if(request('jenis_bahan') === 'bahan_baku') {
                                                        return $detail->tipe_item === 'bahan_baku' || $detail->bahan_baku_id;
                                                    } elseif(request('jenis_bahan') === 'bahan_pendukung') {
                                                        return $detail->tipe_item === 'bahan_pendukung' || $detail->bahan_pendukung_id;
                                                    }
                                                    return true;
                                                });
                                            }
                                            
                                            // Filter by search_bahan
                                            if(request('search_bahan')) {
                                                $searchBahan = strtolower(request('search_bahan'));
                                                $detailsToSum = $detailsToSum->filter(function($detail) use ($searchBahan) {
                                                    $namaBahan = '';
                                                    if($detail->bahanBaku) {
                                                        $namaBahan = strtolower($detail->bahanBaku->nama_bahan);
                                                    } elseif($detail->bahanPendukung) {
                                                        $namaBahan = strtolower($detail->bahanPendukung->nama_bahan);
                                                    }
                                                    return strpos($namaBahan, $searchBahan) !== false;
                                                });
                                            }
                                            
                                            $subtotalDetails = $detailsToSum->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                            });
                                            
                                            // Add PPN proportionally if filter is active
                                            $allDetailsSubtotal = $p->details->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                            });
                                            
                                            if ($allDetailsSubtotal > 0 && ($p->ppn_nominal ?? 0) > 0) {
                                                // Calculate proportional PPN for filtered items
                                                $ppnProportion = $subtotalDetails / $allDetailsSubtotal;
                                                $totalPembelian = $subtotalDetails + (($p->ppn_nominal ?? 0) * $ppnProportion);
                                            } else {
                                                $totalPembelian = $subtotalDetails;
                                            }
                                        } else {
                                            // No filter, use total_harga from pembelian (includes PPN)
                                            $subtotalDetails = $p->details->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                            });
                                            
                                            // Add PPN
                                            $totalPembelian = $subtotalDetails + ($p->ppn_nominal ?? 0);
                                            
                                            // Use p->total_harga if it's greater (includes biaya_kirim, etc)
                                            if ($p->total_harga > $totalPembelian) {
                                                $totalPembelian = $p->total_harga;
                                            }
                                        }
                                    } else {
                                        // No details, use total_harga
                                        $totalPembelian = $p->total_harga ?? 0;
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
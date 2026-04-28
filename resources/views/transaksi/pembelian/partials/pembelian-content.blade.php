<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Transaksi
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('transaksi.pembelian.index') }}">
            <input type="hidden" name="tab" value="pembelian">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">No Transaksi</label>
                    <input type="text" name="nomor_transaksi" class="form-control" 
                           value="{{ request('nomor_transaksi') }}" placeholder="Cari nomor transaksi...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" 
                           value="{{ request('tanggal_mulai') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" 
                           value="{{ request('tanggal_selesai') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">Semua Vendor</option>
                        @foreach($vendors ?? [] as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->nama_vendor }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="belum_lunas" {{ request('status_pembayaran') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                        <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                <span>Riwayat Pembelian</span>
                @if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'payment_method', 'status', 'status_pembayaran']))
                    <small class="text-muted ms-3">(Filter Aktif)</small>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 1200px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center nowrap" style="width: 50px">No</th>
                        <th class="text-center nowrap">No. Transaksi</th>
                        <th class="text-center nowrap">No. Faktur</th>
                        <th class="text-center nowrap">Tanggal</th>
                        <th class="text-center nowrap">Vendor</th>
                        <th class="text-center nowrap">Item</th>
                        <th class="text-center nowrap">Satuan Pembelian</th>
                        <th class="text-center nowrap">Pembayaran</th>
                        <th class="text-center nowrap">Status Pembayaran</th>
                        <th class="text-center nowrap">Total Harga</th>
                        <th class="text-center nowrap">Status Retur</th>
                        <th class="text-center nowrap" style="width: 180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pembelians as $key => $pembelian)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-center nowrap" style="color: #000; font-weight: bold;">{{ $pembelian->nomor_pembelian ?? 'KOSONG' }}</td>
                            <td class="text-center nowrap">
                                @if($pembelian->nomor_faktur)
                                    {{ $pembelian->nomor_faktur }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center nowrap">{{ $pembelian->tanggal->format('d-m-Y') }}</td>
                            <td class="text-center nowrap">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-store text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center nowrap">
                                @if($pembelian->details && $pembelian->details->count() > 0)
                                    @foreach($pembelian->details as $detail)
                                        <div class="mb-1">
                                            @if($detail->bahan_baku_id && $detail->bahanBaku)
                                                BB - {{ $detail->bahanBaku->nama_bahan }}
                                            @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                                BP - {{ $detail->bahanPendukung->nama_bahan }}
                                            @else
                                                <span class="text-muted">Item tidak diketahui</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center nowrap">
                                @if($pembelian->details && $pembelian->details->count() > 0)
                                    <small>
                                    @foreach($pembelian->details as $detail)
                                        <div>
                                            {{ $detail->satuan_nama }}
                                        </div>
                                    @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center nowrap">
                                @php
                                    $paymentMethod = $pembelian->payment_method ?? 'cash';
                                    if ($paymentMethod === 'credit') {
                                        $paymentText = 'Kredit';
                                    } elseif ($paymentMethod === 'transfer') {
                                        $paymentText = 'Transfer';
                                    } else {
                                        $paymentText = 'Tunai';
                                    }
                                @endphp
                                {{ $paymentText }}
                            </td>
                            <td class="text-center nowrap">
                                @php
                                    $statusPembayaran = $pembelian->status_pembayaran;
                                @endphp
                                @if($statusPembayaran === 'Lunas')
                                    <span class="text-success fw-semibold">Lunas</span>
                                @else
                                    <span class="text-warning fw-semibold">Belum Lunas</span>
                                @endif
                            </td>
                            <td class="text-center nowrap fw-semibold">
                                Rp {{ number_format($pembelian->total_harga ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-center nowrap">
                                @php
                                    // Cek apakah ada retur untuk pembelian ini
                                    $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $pembelian->id)->exists();
                                @endphp
                                @if($hasRetur)
                                    Ada Retur
                                @else
                                    Tidak Ada Retur
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-grid" style="grid-template-columns: repeat(2, 1fr); gap: 5px;">
                                    <!-- Row 1: Detail | Edit -->
                                    <a href="{{ route('transaksi.pembelian.show', $pembelian->id) }}" class="btn btn-sm btn-outline-success w-100" title="Detail Transaksi">
                                        Detail
                                    </a>
                                    <a href="{{ route('transaksi.pembelian.edit', $pembelian->id) }}" class="btn btn-sm btn-outline-warning w-100" title="Edit Transaksi">
                                        Edit
                                    </a>
                                    
                                    <!-- Row 2: Jurnal | Retur -->
                                    <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'purchase', 'ref_id' => $pembelian->id]) }}" class="btn btn-sm btn-outline-primary w-100" title="Lihat Jurnal">
                                        Jurnal
                                    </a>
                                    <a href="{{ route('transaksi.retur-pembelian.create', ['pembelian_id' => $pembelian->id]) }}" class="btn btn-sm btn-outline-info w-100" title="Proses Retur">
                                        Retur
                                    </a>
                                    
                                    <!-- Row 3: Cetak -->
                                    <a href="{{ route('transaksi.pembelian.preview-faktur', $pembelian->id) }}" class="btn btn-sm btn-outline-info w-100" title="Cetak Faktur" target="_blank">
                                        Cetak
                                    </a>
                                    
                                    <!-- Row 4: Hapus -->
                                    <form action="{{ route('transaksi.pembelian.destroy', $pembelian->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('Yakin ingin hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Hapus Transaksi">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data pembelian</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
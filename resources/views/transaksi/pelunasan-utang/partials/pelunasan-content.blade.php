<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Transaksi
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('transaksi.pelunasan-utang.index') }}">
            <input type="hidden" name="tab" value="pelunasan">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kode Transaksi</label>
                    <input type="text" name="kode_transaksi" class="form-control" 
                           value="{{ request('kode_transaksi') }}" placeholder="Cari kode transaksi...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai_pelunasan" class="form-control" 
                           value="{{ request('tanggal_mulai_pelunasan') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai_pelunasan" class="form-control" 
                           value="{{ request('tanggal_selesai_pelunasan') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id_pelunasan" class="form-select">
                        <option value="">Semua Vendor</option>
                        @foreach($vendors ?? [] as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id_pelunasan') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->nama_vendor }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                        <a href="{{ route('transaksi.pelunasan-utang.index', ['tab' => 'pelunasan']) }}" class="btn btn-outline-secondary">
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
                <span>Riwayat Pelunasan Utang</span>
                @if(request()->hasAny(['kode_transaksi', 'tanggal_mulai_pelunasan', 'tanggal_selesai_pelunasan', 'vendor_id_pelunasan']))
                    <small class="text-muted ms-3">(Filter Aktif)</small>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        @php
            // Group pelunasan by pembelian dan ambil hanya pelunasan pertama untuk ditampilkan
            $groupedByPembelian = collect($pelunasanUtang)->groupBy('pembelian_id');
            $pelunasanPertama = $groupedByPembelian->map(function($group) {
                return $group->sortBy('tanggal')->sortBy('id')->first();
            });
        @endphp
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px">No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pembelian</th>
                        <th>Vendor</th>
                        <th>Item</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pelunasanPertama as $key => $pelunasan)
                        @php
                            $allPelunasan = $groupedByPembelian[$pelunasan->pembelian_id];
                            $jumlahPelunasan = $allPelunasan->count();
                            // Hitung total dibayar untuk pembelian ini
                            $totalDibayarPembelian = $allPelunasan->sum('jumlah');
                            $totalPembelian = $pelunasan->pembelian_total ?? 0;
                            
                            // Ambil DP dari pembelian
                            $dpPembelian = \DB::table('pembelians')
                                ->where('id', $pelunasan->pembelian_id)
                                ->value('dp') ?? 0;
                            
                            // Ambil data retur untuk pembelian ini
                            $totalRefund = \DB::table('purchase_returns')
                                ->where('pembelian_id', $pelunasan->pembelian_id)
                                ->where('user_id', auth()->id())
                                ->where('jenis_retur', 'refund')
                                ->whereIn('status', ['disetujui', 'dikirim', 'selesai'])
                                ->sum('total_return_amount');
                            
                            $sisaUtangPembelian = $totalPembelian - $dpPembelian - $totalDibayarPembelian - $totalRefund;
                            $statusLunas = $sisaUtangPembelian <= 0;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $pelunasan->kode_transaksi }}</strong>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($pelunasan->tanggal)->format('d-m-Y') }}</td>
                            <td>
                                <strong>{{ $pelunasan->nomor_pembelian ?? '-' }}</strong>
                            </td>
                            <td>{{ $pelunasan->nama_vendor ?? '-' }}</td>
                            <td>
                                @if($pelunasan->items_list)
                                    @php
                                        $items = explode(', ', $pelunasan->items_list);
                                        $itemCount = count($items);
                                    @endphp
                                    <small class="text-muted">
                                        @if($itemCount <= 2)
                                            {{ $pelunasan->items_list }}
                                        @else
                                            {{ $items[0] }}
                                            @if($itemCount > 1)
                                                <span class="badge bg-secondary ms-1">+{{ $itemCount - 1 }} item</span>
                                            @endif
                                        @endif
                                    </small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-success">Rp {{ number_format($pelunasan->jumlah, 0, ',', '.') }}</strong>
                                @if($jumlahPelunasan > 1)
                                    <br><small class="text-muted">dari {{ $jumlahPelunasan }} kali bayar</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($statusLunas)
                                    <strong class="text-success">Lunas</strong>
                                @else
                                    <strong class="text-warning">Belum Lunas</strong>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('transaksi.pelunasan-utang.show', $pelunasan->id) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#riwayatModal{{ $pelunasan->pembelian_id }}"
                                            title="Lihat Riwayat Pelunasan">
                                        <i class="fas fa-history"></i>
                                        @if($jumlahPelunasan > 1)
                                            <span class="badge bg-danger rounded-pill ms-1">{{ $jumlahPelunasan }}</span>
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data pelunasan utang</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Riwayat Pelunasan per Pembelian -->
@php
    $groupedByPembelian = collect($pelunasanUtang)->groupBy('pembelian_id');
@endphp

@foreach($groupedByPembelian as $pembelianId => $riwayatPelunasan)
    @php
        $firstItem = $riwayatPelunasan->first();
        $totalPembelian = $firstItem->pembelian_total ?? 0;
        $totalDibayar = $riwayatPelunasan->sum('jumlah');
        
        // Ambil DP dari pembelian
        $dpPembelian = \DB::table('pembelians')
            ->where('id', $pembelianId)
            ->value('dp') ?? 0;
        
        // Ambil data retur untuk pembelian ini
        $totalRefund = \DB::table('purchase_returns')
            ->where('pembelian_id', $pembelianId)
            ->where('user_id', auth()->id())
            ->where('jenis_retur', 'refund')
            ->whereIn('status', ['disetujui', 'dikirim', 'selesai'])
            ->sum('total_return_amount');
        
        $sisaUtang = $totalPembelian - $dpPembelian - $totalDibayar - $totalRefund;
    @endphp
    
    <div class="modal fade" id="riwayatModal{{ $pembelianId }}" tabindex="-1" aria-labelledby="riwayatModalLabel{{ $pembelianId }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #6B4F3A;">
                    <h5 class="modal-title" id="riwayatModalLabel{{ $pembelianId }}">
                        <i class="fas fa-history me-2"></i>Riwayat Pelunasan - {{ $firstItem->nomor_pembelian }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Info Pembelian -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <small class="text-muted">Vendor</small>
                            <div><strong>{{ $firstItem->nama_vendor }}</strong></div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Pembelian</small>
                            <div><strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong></div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">DP</small>
                            <div><strong class="text-primary">Rp {{ number_format($dpPembelian, 0, ',', '.') }}</strong></div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Dibayar</small>
                            <div><strong class="text-success">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</strong></div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Sisa Utang</small>
                            <div>
                                <strong class="{{ $sisaUtang > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format(max(0, $sisaUtang), 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Tabel Riwayat -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="15%">Kode Transaksi</th>
                                    <th width="12%">Tanggal</th>
                                    <th width="20%">COA Pelunasan</th>
                                    <th class="text-end" width="15%">Jumlah</th>
                                    <th class="text-end" width="15%">Sisa Utang</th>
                                    <th class="text-center" width="10%">Status</th>
                                    <th class="text-center" width="8%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sisaUtangKumulatif = $totalPembelian - $dpPembelian - $totalRefund;
                                @endphp
                                @foreach($riwayatPelunasan->sortBy('tanggal')->sortBy('id') as $index => $item)
                                    @php
                                        // Kurangi sisa utang dengan jumlah pembayaran ini
                                        $sisaUtangKumulatif -= $item->jumlah;
                                        // Tentukan status berdasarkan sisa utang akhir (bukan dari database)
                                        $statusLunas = $sisaUtang <= 0; // Gunakan $sisaUtang dari parent scope (total sisa utang)
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $item->kode_transaksi }}</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                                        <td>
                                            <small class="text-muted">{{ $item->coa_pelunasan_kode }}</small><br>
                                            <small>{{ $item->coa_pelunasan_nama }}</small>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-end">
                                            @if($sisaUtangKumulatif > 0)
                                                <strong class="text-danger">
                                                    Rp {{ number_format($sisaUtangKumulatif, 0, ',', '.') }}
                                                </strong>
                                            @else
                                                <strong class="text-success">Rp 0</strong>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($statusLunas)
                                                <strong class="text-success">Lunas</strong>
                                            @else
                                                <strong class="text-warning">Belum Lunas</strong>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('transaksi.pelunasan-utang.show', $item->id) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('transaksi.pelunasan-utang.jurnal', $item->id) }}" 
                                                   class="btn btn-sm btn-outline-secondary" 
                                                   title="Lihat Jurnal">
                                                    <i class="fas fa-book"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</th>
                                    <th colspan="3"></th>
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
@endforeach

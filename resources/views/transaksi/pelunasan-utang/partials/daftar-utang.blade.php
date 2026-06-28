<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Utang
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('transaksi.pelunasan-utang.index') }}">
            <input type="hidden" name="tab" value="daftar-utang">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">No Pembelian</label>
                    <input type="text" name="nomor_pembelian" class="form-control" 
                           value="{{ request('nomor_pembelian') }}" placeholder="Cari nomor pembelian...">
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
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" 
                           value="{{ request('tanggal_mulai') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" 
                           value="{{ request('tanggal_selesai') }}">
                </div>
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                        <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-outline-secondary">
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
                <span>Daftar Utang Belum Lunas</span>
                @if(request()->hasAny(['nomor_pembelian', 'vendor_id', 'tanggal_mulai', 'tanggal_selesai']))
                    <small class="text-muted ms-3">(Filter Aktif)</small>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px">No</th>
                        <th>No. Pembelian</th>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th class="text-end">Total Pembelian</th>
                        <th class="text-end">DP</th>
                        <th class="text-end">Sisa Utang</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-center" style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarUtang as $key => $pembelian)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>
                                <strong>{{ $pembelian->nomor_pembelian ?? '-' }}</strong>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d-m-Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-store text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $pembelian->nama_vendor ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <strong>Rp {{ number_format($pembelian->total_harga ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-end">
                                @if(($pembelian->dp ?? 0) > 0)
                                    <span class="text-info">Rp {{ number_format($pembelian->dp ?? 0, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="badge bg-danger" style="font-size: 1rem;">
                                    Rp {{ number_format($pembelian->sisa_utang_real ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                @if($pembelian->tanggal_jatuh_tempo)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($pembelian->tanggal_jatuh_tempo);
                                        $today = \Carbon\Carbon::today();
                                        $isOverdue = $dueDate->lt($today);
                                    @endphp
                                    <small class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $dueDate->format('d-m-Y') }}
                                        @if($isOverdue)
                                            <br><span class="badge bg-danger">Jatuh Tempo</span>
                                        @endif
                                    </small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('transaksi.pelunasan-utang.create', ['pembelian_id' => $pembelian->id]) }}" 
                                   class="btn btn-sm btn-success" 
                                   title="Lunasi Utang">
                                    <i class="fas fa-money-bill-wave me-1"></i>Lunasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-muted">Tidak ada utang yang perlu dilunasi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

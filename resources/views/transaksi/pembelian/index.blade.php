@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            @if(request('tab') == 'retur')
                <i class="fas fa-undo me-2"></i>Retur Pembelian
            @else
                <i class="fas fa-shopping-cart me-2"></i>Daftar Pembelian
            @endif
        </h2>
        @if(request('tab') != 'retur')
            <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Pembelian
            </a>
        @endif
    </div>

    <!-- Content based on selected tab -->
    @if(request('tab') == 'retur')
        <!-- Retur Content -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-list me-2"></i>
                        <div class="inline-tabs">
                            <a href="{{ route('transaksi.pembelian.index', ['tab' => 'pembelian']) }}" 
                               class="inline-tab {{ request('tab', 'pembelian') == 'pembelian' ? 'active' : '' }}">
                                Riwayat Pembelian
                            </a>
                            <span class="tab-separator">|</span>
                            <a href="{{ route('transaksi.pembelian.index', ['tab' => 'retur']) }}" 
                               class="inline-tab {{ request('tab') == 'retur' ? 'active' : '' }}">
                                Retur
                            </a>
                        </div>
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
                            @forelse ($returs as $key => $retur)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>{{ $retur->return_date->format('d-m-Y') }}</td>
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
                                        @if($retur->status === 'completed' || $retur->status === 'barang_diterima' || $retur->status === 'dana_diterima')
                                            <span class="text-success fw-semibold">Selesai</span>
                                        @elseif($retur->status === 'approved' || $retur->status === 'disetujui_vendor')
                                            <span class="text-info fw-semibold">Disetujui</span>
                                        @elseif($retur->status === 'diproses_vendor')
                                            <span class="text-warning fw-semibold">Diproses Vendor</span>
                                        @elseif($retur->status === 'barang_dikembalikan')
                                            <span class="text-primary fw-semibold">Barang Dikembalikan</span>
                                        @elseif($retur->status === 'menunggu_pembayaran')
                                            <span class="text-info fw-semibold">Menunggu Pembayaran</span>
                                        @elseif($retur->status === 'menunggu_vendor')
                                            <span class="text-secondary fw-semibold">Menunggu Vendor</span>
                                        @else
                                            <span class="text-warning fw-semibold">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            <!-- Detail Button (Always Available) -->
                                            <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
                                               class="btn btn-sm btn-success" title="Detail Retur">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </a>
                                            
                                            <!-- Dynamic Action Buttons Based on Status and Type -->
                                            @if($retur->jenis_retur == 'tukar_barang')
                                                @if($retur->status == 'pending' || $retur->status == 'menunggu_vendor')
                                                    <a href="{{ route('retur.updateStatus', [$retur->id, 'disetujui_vendor']) }}" 
                                                       class="btn btn-sm btn-primary" title="Setujui Vendor"
                                                       onclick="return confirm('Yakin vendor sudah menyetujui tukar barang?')">
                                                        <i class="fas fa-check me-1"></i>Setujui Vendor
                                                    </a>
                                                @endif
                                                
                                                @if($retur->status == 'disetujui_vendor')
                                                    <a href="{{ route('retur.updateStatus', [$retur->id, 'diproses_vendor']) }}" 
                                                       class="btn btn-sm btn-warning" title="Proses Barang"
                                                       onclick="return confirm('Yakin vendor sudah memproses barang?')">
                                                        <i class="fas fa-cogs me-1"></i>Proses Barang
                                                    </a>
                                                @endif
                                                
                                                @if($retur->status == 'diproses_vendor')
                                                    <a href="{{ route('retur.updateStatus', [$retur->id, 'barang_diterima']) }}" 
                                                       class="btn btn-sm btn-success" title="Barang Diterima"
                                                       onclick="return confirm('Yakin barang pengganti sudah diterima?')">
                                                        <i class="fas fa-box me-1"></i>Barang Diterima
                                                    </a>
                                                @endif
                                            @endif
                                            
                                            @if($retur->jenis_retur == 'refund')
                                                @if($retur->status == 'pending')
                                                    <a href="{{ route('retur.updateStatus', [$retur->id, 'barang_dikembalikan']) }}" 
                                                       class="btn btn-sm btn-warning" title="Barang Dikembalikan"
                                                       onclick="return confirm('Yakin barang sudah dikembalikan ke vendor?')">
                                                        <i class="fas fa-undo me-1"></i>Barang Dikembalikan
                                                    </a>
                                                @endif
                                                
                                                @if($retur->status == 'barang_dikembalikan')
                                                    <a href="{{ route('retur.updateStatus', [$retur->id, 'menunggu_pembayaran']) }}" 
                                                       class="btn btn-sm btn-info" title="Vendor Sudah Terima"
                                                       onclick="return confirm('Yakin vendor sudah menerima barang?')">
                                                        <i class="fas fa-handshake me-1"></i>Vendor Sudah Terima
                                                    </a>
                                                @endif
                                                
                                                @if($retur->status == 'menunggu_pembayaran')
                                                    <a href="{{ route('retur.updateStatus', [$retur->id, 'dana_diterima']) }}" 
                                                       class="btn btn-sm btn-success" title="Uang Diterima"
                                                       onclick="return confirm('Yakin uang refund sudah diterima?')">
                                                        <i class="fas fa-money-bill me-1"></i>Uang Diterima
                                                    </a>
                                                @endif
                                            @endif
                                            
                                            <!-- Legacy Proses Button (for backward compatibility) -->
                                            @if($retur->status === 'pending' && !in_array($retur->jenis_retur, ['tukar_barang', 'refund']))
                                                <form action="{{ route('transaksi.retur-pembelian.proses', $retur->id) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Yakin mau menyelesaikan retur ini?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Proses Retur">
                                                        <i class="fas fa-check me-1"></i>Proses
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
    @else
        <!-- Pembelian Content (Default) -->
        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Filter Transaksi
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('transaksi.pembelian.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Nomor Transaksi</label>
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

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-list me-2"></i>
                        <div class="inline-tabs">
                            <a href="{{ route('transaksi.pembelian.index', ['tab' => 'pembelian']) }}" 
                               class="inline-tab {{ request('tab', 'pembelian') == 'pembelian' ? 'active' : '' }}">
                                Riwayat Pembelian
                            </a>
                            <span class="tab-separator">|</span>
                            <a href="{{ route('transaksi.pembelian.index', ['tab' => 'retur']) }}" 
                               class="inline-tab {{ request('tab') == 'retur' ? 'active' : '' }}">
                                Retur
                            </a>
                        </div>
                        @if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'payment_method', 'status', 'status_pembayaran']))
                            <small class="text-white-50 ms-3">(Filter Aktif)</small>
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
                                <th>Nomor Transaksi</th>
                                <th>Nomor Faktur</th>
                                <th>Tanggal</th>
                                <th>Vendor</th>
                                <th>Item Dibeli</th>
                                <th>Satuan Pembelian</th>
                                <th>Pembayaran</th>
                                <th>Status Pembayaran</th>
                                <th>Total Harga</th>
                                <th>Status Retur</th>
                                <th class="text-center" style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pembelians as $key => $pembelian)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td style="color: #000; font-weight: bold;">{{ $pembelian->nomor_pembelian ?? 'KOSONG' }}</td>
                                    <td>
                                        @if($pembelian->nomor_faktur)
                                            {{ $pembelian->nomor_faktur }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $pembelian->tanggal->format('d-m-Y') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                                <i class="fas fa-store text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
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
                                    <td>
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
                                    <td>
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
                                    <td>
                                        @php
                                            $statusPembayaran = $pembelian->status_pembayaran;
                                        @endphp
                                        @if($statusPembayaran === 'Lunas')
                                            <span class="text-success fw-semibold">Lunas</span>
                                        @else
                                            <span class="text-warning fw-semibold">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($pembelian->total_harga ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td>
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
                                            
                                            <!-- Row 3: Cetak | Hapus -->
                                            <a href="{{ route('transaksi.pembelian.cetak-pdf', $pembelian->id) }}" class="btn btn-sm btn-outline-secondary w-100" title="Download PDF">
                                                Cetak
                                            </a>
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
    @endif
</div>

@push('styles')
<style>
.inline-tabs {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.inline-tab {
    color: rgba(255, 255, 255, 0.5);
    text-decoration: none;
    font-weight: 500;
    padding: 0.25rem 0;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
    position: relative;
}

.inline-tab:hover {
    color: rgba(255, 255, 255, 0.75);
    text-decoration: none;
}

.inline-tab.active {
    color: white;
    border-bottom-color: white;
    font-weight: 600;
}

.tab-separator {
    color: rgba(255, 255, 255, 0.3);
    font-weight: 300;
    margin: 0 0.25rem;
}
</style>
@endpush
@endsection

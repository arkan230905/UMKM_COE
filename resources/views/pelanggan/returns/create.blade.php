@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Ajukan Retur</h2>
        <a href="{{ route('pelanggan.returns.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pelanggan.returns.create') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label text-dark">Pilih Pesanan</label>
                    <select name="order_id" class="form-select" required>
                        <option value="">-- Pilih Pesanan --</option>
                        @foreach($orders as $o)
                            <option value="{{ $o->id }}" {{ request('order_id') == $o->id ? 'selected' : '' }}>
                                #{{ $o->nomor_order }} - Rp {{ number_format($o->total_amount, 0, ',', '.') }} ({{ ucfirst($o->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-info w-100">Muat Item</button>
                </div>
            </form>
        </div>
    </div>

    @if($order)
    <form action="{{ route('pelanggan.returns.store') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-dark"><i class="bi bi-box-seam"></i> Item Pesanan yang Bisa Diretur</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty Dipesan</th>
                                <th width="160">Qty Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $it)
                            <tr>
                                <td>{{ $it->produk->nama_produk ?? 'Produk' }}</td>
                                <td>Rp {{ number_format($it->harga, 0, ',', '.') }}</td>
                                <td>{{ $it->qty }}</td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $it->id }}">
                                        <input type="number" name="items[{{ $loop->index }}][qty]" value="0" min="0" max="{{ $it->qty }}" class="form-control">
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h5 class="mb-0 text-dark"><i class="bi bi-clipboard-check"></i> Detail Pengajuan</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label text-dark">Kompensasi</label>
                    <select name="tipe_kompensasi" class="form-select" required>
                        <option value="barang">Tukar Barang</option>
                        <option value="uang">Refund Uang</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label text-dark">Alasan</label>
                    <textarea name="alasan" rows="3" class="form-control" placeholder="Tuliskan alasan retur (opsional)"></textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success px-4"><i class="bi bi-send"></i> Ajukan Retur</button>
        </div>
    </form>
    @endif
</div>
@endsection

@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="mb-3">Tambah Penjualan</h3>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('transaksi.penjualan.store') }}" method="POST" id="form-penjualan">
        @csrf

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method_jual" class="form-select" required>
                    <option value="cash" selected>Tunai</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit">Kredit</option>
                </select>
            </div>
            <div class="col-md-3" id="sumber_dana_wrapper_jual">
                <label class="form-label">Terima di</label>
                <select name="sumber_dana" id="sumber_dana_jual" class="form-select">
                    @foreach($kasbank as $kb)
                        <option value="{{ $kb->kode_akun }}">
                            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Barcode Scanner Input -->
        <div class="card mb-3 border-primary">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-barcode fa-2x text-primary"></i>
                    </div>
                    <div class="col">
                        <label class="form-label mb-1 small text-muted">Scan Barcode Produk</label>
                        <div class="input-group">
                            <input type="text" id="barcode-scanner" class="form-control form-control-lg" 
                                   placeholder="Siap untuk scan barcode..." 
                                   autocomplete="off" readonly autofocus>
                            <div class="input-group-text bg-success text-white">
                                <i class="fas fa-wifi me-1"></i>
                                <span id="scan-indicator">Siap Scan</span>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetScannerState()" title="Reset Scanner">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Sistem otomatis mendeteksi barcode - tidak perlu klik atau tekan tombol
                        </small>
                    </div>
                    <div class="col-auto">
                        <button type="button" id="barcode-status" class="btn btn-outline-secondary btn-sm" onclick="toggleProductList()">
                            <i class="fas fa-list me-1"></i>Daftar Produk
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <h5>Detail Penjualan</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="detailTableJual">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga/Satuan</th>
                        <th class="text-end">Diskon (%)</th>
                        <th class="text-end">Subtotal</th>
                        <th style="width:6%">NO<button class="btn btn-success btn-sm" type="button" id="addRowJual">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="produk_id[]" class="form-select produk-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}" 
                                            data-price="{{ round($p->harga_jual ?? 0) }}"
                                            data-stok="{{ $p->stok ?? 0 }}">
                                        {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok ?? 0, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted stok-info"></small>
                        </td>
                        <td><input type="number" step="1" min="1" name="jumlah[]" class="form-control jumlah" value="1" required></td>
                        <td><input type="text" name="harga_satuan[]" class="form-control harga" value="0" readonly required></td>
                        <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="0"></td>
                        <td><input type="text" class="form-control subtotal" value="0" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-3 ms-auto">
                <label class="form-label">Subtotal Produk</label>
                <input type="text" name="subtotal_produk" class="form-control" value="0" readonly>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Biaya Ongkir</label>
                <input type="number" step="0.01" min="0" name="biaya_ongkir" class="form-control" value="0" id="biaya_ongkir" 
                     onclick="this.focus()" 
                     onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Service</label>
                <input type="number" step="0.01" min="0" name="biaya_service" class="form-control" value="0" id="biaya_service" 
                         onclick="this.focus()" 
                         onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">PPN (%)</label>
                <input type="number" step="0.01" min="0" name="ppn_persen" class="form-control" value="0" id="ppn_persen" 
                         onclick="this.focus()" 
                         onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total PPN</label>
                <input type="text" name="total_ppn" class="form-control" value="0" readonly id="total_ppn">
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4 ms-auto">
                <label class="form-label">Total Final</label>
                <input type="text" name="total" class="form-control" value="0" readonly id="total_final">
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>

<!-- Product List Modal -->
<div class="modal fade" id="productListModal" tabindex="-1" aria-labelledby="productListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productListModalLabel">
                    <i class="fas fa-barcode me-2"></i>Daftar Produk & Barcode
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="modal-search" class="form-control" placeholder="Cari produk atau barcode...">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Produk</th>
                                <th>Barcode</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="product-list-body">
                            @foreach($produks as $p)
                            <tr class="product-row" data-barcode="{{ $p->barcode ?? '' }}" data-name="{{ strtolower($p->nama_produk ?? $p->nama) }}">
                                <td>{{ $p->nama_produk ?? $p->nama }}</td>
                                <td>
                                    @if($p->barcode)
                                        <code>{{ $p->barcode }}</code>
                                    @else
                                        <small class="text-muted">Tidak ada</small>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($p->harga_jual ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ ($p->stok ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($p->stok ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="addProductFromModal({{ $p->id }}, '{{ addslashes($p->nama_produk ?? $p->nama) }}', {{ $p->harga_jual ?? 0 }}, {{ $p->stok ?? 0 }})">>
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


@endsection

@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-white">Checkout</h2>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-truck"></i> Data Pengiriman</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pelanggan.checkout.process') }}" method="POST" id="checkoutForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-white">Nama Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="nama_penerima" class="form-control bg-dark text-white @error('nama_penerima') is-invalid @enderror" value="{{ old('nama_penerima', auth()->user()->name) }}" required>
                            @error('nama_penerima')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat_pengiriman" class="form-control bg-dark text-white @error('alamat_pengiriman') is-invalid @enderror" rows="3" required placeholder="Masukkan alamat lengkap dengan kode pos">{{ old('alamat_pengiriman') }}</textarea>
                            @error('alamat_pengiriman')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">No. Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="telepon_penerima" class="form-control bg-dark text-white @error('telepon_penerima') is-invalid @enderror" value="{{ old('telepon_penerima', auth()->user()->phone) }}" required placeholder="08xxxxxxxxxx">
                            @error('telepon_penerima')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select bg-dark text-white @error('payment_method') is-invalid @enderror" required>
                                <option value="">-- Pilih Metode Pembayaran --</option>
                                <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>
                                    <i class="bi bi-qr-code"></i> QRIS (Scan & Pay)
                                </option>
                                <option value="va_bca" {{ old('payment_method') == 'va_bca' ? 'selected' : '' }}>
                                    BCA Virtual Account
                                </option>
                                <option value="va_bni" {{ old('payment_method') == 'va_bni' ? 'selected' : '' }}>
                                    BNI Virtual Account
                                </option>
                                <option value="va_bri" {{ old('payment_method') == 'va_bri' ? 'selected' : '' }}>
                                    BRI Virtual Account
                                </option>
                                <option value="va_mandiri" {{ old('payment_method') == 'va_mandiri' ? 'selected' : '' }}>
                                    Mandiri Virtual Account
                                </option>
                            </select>
                            @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pembayaran aman melalui Midtrans</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Catatan (Optional)</label>
                            <textarea name="catatan" class="form-control bg-dark text-white" rows="2" placeholder="Catatan untuk penjual (optional)">{{ old('catatan') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-credit-card"></i> Proses Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    @foreach($carts as $cart)
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <div>
                            <strong class="text-white">{{ $cart->produk->nama_produk }}</strong>
                            <br>
                            <small class="text-muted">{{ $cart->qty }} x Rp {{ number_format($cart->harga, 0, ',', '.') }}</small>
                        </div>
                        <span class="text-white">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                    
                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span class="text-white">Total:</span>
                        <span class="text-primary">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="bi bi-shield-check"></i> Pembayaran aman dengan Midtrans
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

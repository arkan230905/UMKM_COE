@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <!-- Header Title & Steps -->
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill mb-2" style="background: #fdf5eb; color: #8b5a2b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">
            KERANJANG <i class="bi bi-cart-fill"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Kelola & Review Pesananmu</h4>
        <p class="text-muted mb-3" style="font-size: 0.85rem;">Cek kembali produk pilihanmu, atur jumlahnya, lalu lanjutkan ke pembayaran.</p>
        
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <!-- Step 1 (Active) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #8b5a2b;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #fdf5eb;">
                    <i class="bi bi-cart-fill" style="color: #8b5a2b; font-size: 0.9rem;"></i>
                </div>
                <span style="color: #8b5a2b; font-weight: 600; font-size: 0.85rem;">Keranjang</span>
            </div>
            
            <!-- Step 2 (Inactive) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #f0f0f0;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #f8f9fa;">
                    <i class="bi bi-truck text-muted" style="font-size: 0.9rem;"></i>
                </div>
                <span class="text-muted" style="font-weight: 600; font-size: 0.85rem;">Checkout</span>
            </div>
            
            <!-- Step 3 (Inactive) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #f0f0f0;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #f8f9fa;">
                    <i class="bi bi-receipt text-muted" style="font-size: 0.9rem;"></i>
                </div>
                <span class="text-muted" style="font-weight: 600; font-size: 0.85rem;">Pembayaran</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($carts->isEmpty())
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #6c757d;"></i>
            <h4 class="mt-3 text-dark">Keranjang Kosong</h4>
            <p class="text-muted">Belum ada produk di keranjang Anda</p>
            <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard") }}" class="btn text-white mt-2" style="background: #8b5a2b; border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 600;">
                <i class="bi bi-shop"></i> Mulai Belanja
            </a>
        </div>
    </div>
    @else
    <div class="row">
        <!-- Left Column: Items & Voucher -->
        <div class="col-lg-8 mb-4">
            <!-- Items Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                            <thead style="background-color: #fdfaf7; font-size: 0.9rem;">
                                <tr>
                                    <th style="padding: 1rem 1.5rem; border-bottom: 1px solid #eaeaea; font-weight: 600;">Produk</th>
                                    <th style="padding: 1rem 0.5rem; border-bottom: 1px solid #eaeaea; font-weight: 600;">Harga</th>
                                    <th width="140" style="padding: 1rem 0.5rem; border-bottom: 1px solid #eaeaea; font-weight: 600; text-align: center;">Qty</th>
                                    <th style="padding: 1rem 0.5rem; border-bottom: 1px solid #eaeaea; font-weight: 600;">Subtotal</th>
                                    <th width="80" style="padding: 1rem 1.5rem; border-bottom: 1px solid #eaeaea; font-weight: 600; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($carts as $cart)
                                @if($cart->produk)
                                <tr style="font-size: 0.9rem;">
                                    <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid #f5f5f5;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                                @if($cart->produk->foto)
                                                <img src="{{ Storage::url($cart->produk->foto) }}" alt="{{ $cart->produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ccc;">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                                @endif
                                            </div>
                                            <div>
                                                <strong style="font-size: 0.95rem; color: #2d3748;">{{ $cart->produk->nama_produk }}</strong>
                                                <br>
                                                <small style="color: #888;">Stok tersedia: {{ number_format($cart->produk->stok_tersedia ?? $cart->produk->stok, 0, ',', '.') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1.2rem 0.5rem; border-bottom: 1px solid #f5f5f5;">Rp {{ number_format($cart->harga, 0, ',', '.') }}</td>
                                    <td style="padding: 1.2rem 0.5rem; border-bottom: 1px solid #f5f5f5;">
                                        <!-- Qty Control inside a pill -->
                                        <div class="d-flex align-items-center justify-content-center" style="background: #fdfaf7; border-radius: 50px; padding: 0.2rem; border: 1px solid #f0e6da;">
                                            <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/cart/" . $cart->id) }}" method="POST" class="d-inline" style="margin: 0;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="qty" value="{{ max(1, $cart->qty - 1) }}">
                                                <button type="submit" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: transparent; color: #8b5a2b; font-size: 1rem; cursor: pointer;" {{ $cart->qty <= 1 ? 'disabled' : '' }}>
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                            </form>

                                            <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/cart/" . $cart->id) }}" method="POST" class="d-inline" style="margin: 0; width: 40px;">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="qty" value="{{ $cart->qty }}" min="1" max="{{ $cart->produk->stok_tersedia ?? $cart->produk->stok }}" class="form-control text-center p-0" onchange="this.form.submit()" style="border: none; background: transparent; font-size: 0.85rem; font-weight: 600; appearance: textfield; box-shadow: none;">
                                            </form>

                                            <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/cart/" . $cart->id) }}" method="POST" class="d-inline" style="margin: 0;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="qty" value="{{ min($cart->produk->stok_tersedia ?? $cart->produk->stok, $cart->qty + 1) }}">
                                                <button type="submit" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: #cda47b; color: white; border-radius: 50%; font-size: 1rem; cursor: pointer;" {{ $cart->qty >= ($cart->produk->stok_tersedia ?? $cart->produk->stok) ? 'disabled' : '' }}>
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="fw-bold" style="padding: 1.2rem 0.5rem; color: #2d3748; border-bottom: 1px solid #f5f5f5;">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</td>
                                    <td style="padding: 1.2rem 1.5rem; text-align: center; border-bottom: 1px solid #f5f5f5;">
                                        <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/cart/" . $cart->id) }}" method="POST" class="d-inline" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background: white; border: 1px solid #ff7675; color: #ff7675; border-radius: 6px; padding: 0.3rem 0.6rem;" onclick="return confirm('Hapus item ini dari keranjang?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Bottom Buttons (Left side actions) -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard") }}" class="btn" style="background: #f8f9fa; border: 1px solid #ddd; color: #666; border-radius: 8px; font-size: 0.85rem; padding: 0.5rem 1rem;">
                    <i class="bi bi-arrow-left"></i> Lanjut Belanja
                </a>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; position: sticky; top: 100px;">
                <div class="card-body p-4">
                    <!-- Action Buttons -->
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/checkout") }}" class="btn w-100 d-flex justify-content-between align-items-center" style="background: #8b5a2b; color: white; border-radius: 8px; padding: 0.8rem 1rem; font-weight: 600;">
                            <span>Checkout</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        
                        <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/cart/clear") }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn w-100" style="background: white; border: 1px solid #ff7675; color: #ff7675; border-radius: 8px; padding: 0.6rem 1rem; font-size: 0.85rem;" onclick="return confirm('Kosongkan semua keranjang?')">
                                <i class="bi bi-trash"></i> Kosongkan Keranjang
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

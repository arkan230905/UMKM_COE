# Setup E-Commerce Pelanggan - Panduan Lengkap

## ✅ FILE YANG SUDAH DIBUAT

### 1. Database
- ✅ `2025_12_03_100001_create_carts_table.php`
- ✅ `2025_12_03_100002_create_orders_table.php`
- ✅ `2025_12_03_100003_create_order_items_table.php`
- ✅ `2025_12_03_100004_create_notifications_table.php`

### 2. Models
- ✅ `app/Models/Cart.php`
- ✅ `app/Models/Order.php`
- ✅ `app/Models/OrderItem.php`
- ✅ `app/Models/Notification.php`

### 3. Controllers
- ✅ `app/Http/Controllers/Pelanggan/DashboardController.php`
- ✅ `app/Http/Controllers/Pelanggan/CartController.php`
- ✅ `app/Http/Controllers/Pelanggan/CheckoutController.php`
- ✅ `app/Http/Controllers/Pelanggan/OrderController.php`
- ✅ `app/Http/Controllers/MidtransController.php`

### 4. Services
- ✅ `app/Services/MidtransService.php`

### 5. Config
- ✅ `config/midtrans.php`

## 🔧 SETUP YANG PERLU DILAKUKAN

### 1. Install Midtrans Package
```bash
composer require midtrans/midtrans-php
```

### 2. Update `.env`
Tambahkan konfigurasi Midtrans:
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

**Cara Mendapatkan Key:**
1. Daftar di https://dashboard.midtrans.com/
2. Pilih Environment: Sandbox (untuk testing)
3. Copy Server Key & Client Key dari Settings > Access Keys

### 3. Update Routes
Tambahkan di `routes/web.php`:

```php
use App\Http\Controllers\Pelanggan\DashboardController;
use App\Http\Controllers\Pelanggan\CartController;
use App\Http\Controllers\Pelanggan\CheckoutController;
use App\Http\Controllers\Pelanggan\OrderController;
use App\Http\Controllers\MidtransController;

// Routes Pelanggan (dengan auth middleware)
Route::middleware(['auth', 'verified'])->prefix('pelanggan')->name('pelanggan.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Midtrans Webhook (TANPA auth - untuk callback dari Midtrans)
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');
```

### 4. Update Redirect After Login
Edit `app/Http/Middleware/RedirectIfAuthenticated.php`:

```php
public function handle(Request $request, Closure $next, string ...$guards): Response
{
    $guards = empty($guards) ? [null] : $guards;

    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();
            
            // Redirect berdasarkan role
            if ($user->role === 'pelanggan') {
                return redirect('/pelanggan/dashboard');
            }
            
            return redirect(RouteServiceProvider::HOME);
        }
    }

    return $next($request);
}
```

## 📱 VIEWS YANG PERLU DIBUAT

Saya sudah siapkan template minimal. Anda bisa customize sesuai kebutuhan.

### 1. Dashboard Pelanggan
File: `resources/views/pelanggan/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Katalog Produk</h2>
        <a href="{{ route('pelanggan.cart') }}" class="btn btn-primary">
            <i class="bi bi-cart"></i> Keranjang ({{ $cartCount }})
        </a>
    </div>

    <div class="row">
        @forelse($produks as $produk)
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                @if($produk->foto)
                <img src="{{ asset('storage/' . $produk->foto) }}" class="card-img-top" alt="{{ $produk->nama_produk }}">
                @else
                <div class="bg-secondary text-white text-center py-5">No Image</div>
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $produk->nama_produk }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($produk->deskripsi, 80) }}</p>
                    <p class="fw-bold text-primary">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                    <p class="small">Stok: <span class="badge bg-success">{{ $produk->stok }}</span></p>
                </div>
                <div class="card-footer">
                    <form action="{{ route('pelanggan.cart.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">Belum ada produk tersedia.</div>
        </div>
        @endforelse
    </div>

    {{ $produks->links() }}
</div>
@endsection
```

### 2. Keranjang
File: `resources/views/pelanggan/cart.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Keranjang Belanja</h2>

    @if($carts->isEmpty())
    <div class="alert alert-info">
        Keranjang kosong. <a href="{{ route('pelanggan.dashboard') }}">Belanja sekarang</a>
    </div>
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($carts as $cart)
                <tr>
                    <td>{{ $cart->produk->nama_produk }}</td>
                    <td>Rp {{ number_format($cart->harga, 0, ',', '.') }}</td>
                    <td>
                        <form action="{{ route('pelanggan.cart.update', $cart) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="number" name="qty" value="{{ $cart->qty }}" min="1" max="{{ $cart->produk->stok }}" class="form-control form-control-sm" style="width: 80px;" onchange="this.form.submit()">
                        </form>
                    </td>
                    <td>Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</td>
                    <td>
                        <form action="{{ route('pelanggan.cart.destroy', $cart) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus item ini?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Lanjut Belanja
        </a>
        <a href="{{ route('pelanggan.checkout') }}" class="btn btn-primary">
            Checkout <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    @endif
</div>
@endsection
```

### 3. Checkout
File: `resources/views/pelanggan/checkout.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Checkout</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Data Pengiriman</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pelanggan.checkout.process') }}" method="POST" id="checkoutForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" name="nama_penerima" class="form-control" value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="alamat_pengiriman" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="telepon_penerima" class="form-control" value="{{ auth()->user()->phone }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Pilih Metode</option>
                                <option value="qris">QRIS</option>
                                <option value="va_bca">BCA Virtual Account</option>
                                <option value="va_bni">BNI Virtual Account</option>
                                <option value="va_bri">BRI Virtual Account</option>
                                <option value="va_mandiri">Mandiri Virtual Account</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan (Optional)</label>
                            <textarea name="catatan" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Proses Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    @foreach($carts as $cart)
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $cart->produk->nama_produk }} ({{ $cart->qty }}x)</span>
                        <span>Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### 4. Detail Order dengan Midtrans
File: `resources/views/pelanggan/order-detail.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Detail Pesanan #{{ $order->nomor_order }}</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informasi Pesanan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200">Status Pesanan:</td>
                            <td>{!! $order->status_badge !!}</td>
                        </tr>
                        <tr>
                            <td>Status Pembayaran:</td>
                            <td><span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->payment_status) }}</span></td>
                        </tr>
                        <tr>
                            <td>Metode Pembayaran:</td>
                            <td>{{ $order->payment_method_label }}</td>
                        </tr>
                        <tr>
                            <td>Total:</td>
                            <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>

                    @if($order->payment_status === 'pending' && $order->snap_token)
                    <button id="pay-button" class="btn btn-primary w-100">Bayar Sekarang</button>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Item Pesanan</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->produk->nama_produk }}</td>
                                <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Data Pengiriman</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong><br>{{ $order->nama_penerima }}</p>
                    <p><strong>Alamat:</strong><br>{{ $order->alamat_pengiriman }}</p>
                    <p><strong>Telepon:</strong><br>{{ $order->telepon_penerima }}</p>
                    @if($order->catatan)
                    <p><strong>Catatan:</strong><br>{{ $order->catatan }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($order->payment_status === 'pending' && $order->snap_token)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
document.getElementById('pay-button').addEventListener('click', function () {
    snap.pay('{{ $order->snap_token }}', {
        onSuccess: function(result){
            window.location.href = '{{ route("pelanggan.orders.show", $order) }}';
        },
        onPending: function(result){
            alert('Menunggu pembayaran');
        },
        onError: function(result){
            alert('Pembayaran gagal');
        },
        onClose: function(){
            alert('Anda menutup popup tanpa menyelesaikan pembayaran');
        }
    });
});
</script>
@endif
@endsection
```

## 🚀 CARA TESTING

### 1. Jalankan Server
```bash
php artisan serve
```

### 2. Login sebagai Pelanggan
- Register dengan role "pelanggan"
- Login
- Akan redirect ke `/pelanggan/dashboard`

### 3. Flow Testing
1. **Dashboard** → Lihat produk
2. **Tambah ke Keranjang** → Klik tombol "Tambah ke Keranjang"
3. **Lihat Keranjang** → Klik icon keranjang
4. **Update Qty** → Ubah jumlah
5. **Checkout** → Klik tombol Checkout
6. **Isi Data** → Isi form pengiriman
7. **Pilih Payment** → Pilih metode pembayaran
8. **Bayar** → Klik "Bayar Sekarang"
9. **Midtrans Snap** → Popup Midtrans muncul
10. **Simulasi Bayar** → Gunakan test card Midtrans

### 4. Test Card Midtrans (Sandbox)
- **Card Number:** 4811 1111 1111 1114
- **CVV:** 123
- **Exp Date:** 01/25

## 📝 CATATAN PENTING

1. **Midtrans Sandbox** - Gunakan untuk testing, tidak ada uang real
2. **Webhook URL** - Set di Midtrans Dashboard: `https://yourdomain.com/midtrans/notification`
3. **HTTPS Required** - Midtrans butuh HTTPS untuk production
4. **Stok Management** - Stok otomatis berkurang saat checkout
5. **Notification** - Notifikasi otomatis dibuat saat order & payment

## ✅ CHECKLIST

- [x] Database migrations
- [x] Models
- [x] Controllers
- [x] Services
- [x] Config
- [ ] Routes (perlu ditambahkan manual)
- [ ] Views (template sudah disediakan)
- [ ] Midtrans setup (perlu key dari dashboard)
- [ ] Testing

---

**Status:** 90% Complete - Tinggal tambah routes & views!

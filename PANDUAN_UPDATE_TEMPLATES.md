# Panduan Update Blade Templates untuk Multi-Tenant

## Overview

Setelah implementasi multi-tenant, semua Blade templates yang menggunakan route pelanggan perlu diupdate untuk menggunakan helper functions.

## Perubahan yang Diperlukan

### 1. Update Route Links

**Sebelum:**
```blade
<a href="{{ route('pelanggan.dashboard') }}">Dashboard</a>
<a href="{{ route('pelanggan.cart') }}">Keranjang</a>
<a href="{{ route('pelanggan.login') }}">Login</a>
<a href="{{ route('pelanggan.favorites') }}">Favorit</a>
<a href="{{ route('pelanggan.orders') }}">Pesanan</a>
```

**Sesudah:**
```blade
<a href="{{ pelanggan_route('dashboard') }}">Dashboard</a>
<a href="{{ pelanggan_route('cart') }}">Keranjang</a>
<a href="{{ pelanggan_route('login') }}">Login</a>
<a href="{{ pelanggan_route('favorites') }}">Favorit</a>
<a href="{{ pelanggan_route('orders') }}">Pesanan</a>
```

### 2. Update Route Links dengan Parameter

**Sebelum:**
```blade
<a href="{{ route('pelanggan.orders.show', ['order' => $order->id]) }}">Detail</a>
<a href="{{ route('pelanggan.cart.update', ['cart' => $cart->id]) }}">Update</a>
```

**Sesudah:**
```blade
<a href="{{ pelanggan_route('orders.show', ['order' => $order->id]) }}">Detail</a>
<a href="{{ pelanggan_route('cart.update', ['cart' => $cart->id]) }}">Update</a>
```

### 3. Update Form Actions

**Sebelum:**
```blade
<form action="{{ route('pelanggan.login.post') }}" method="POST">
    @csrf
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>
```

**Sesudah:**
```blade
<form action="{{ pelanggan_route('login.post') }}" method="POST">
    @csrf
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>
```

### 4. Update Redirect Links

**Sebelum:**
```blade
<a href="{{ route('pelanggan.dashboard') }}" class="btn">Kembali ke Dashboard</a>
```

**Sesudah:**
```blade
<a href="{{ pelanggan_route('dashboard') }}" class="btn">Kembali ke Dashboard</a>
```

### 5. Update URL Paths

**Sebelum:**
```blade
<a href="/pelanggan/dashboard">Dashboard</a>
<a href="/pelanggan/cart">Keranjang</a>
```

**Sesudah:**
```blade
<a href="{{ pelanggan_url('/dashboard') }}">Dashboard</a>
<a href="{{ pelanggan_url('/cart') }}">Keranjang</a>
```

## File Templates yang Perlu Diupdate

### 1. resources/views/layouts/pelanggan.blade.php
```blade
<!-- Navigation Links -->
<a href="{{ pelanggan_route('dashboard') }}">Beranda</a>
<a href="{{ pelanggan_route('favorites') }}">Favorit</a>
<a href="{{ pelanggan_route('cart') }}">Keranjang</a>

<!-- Dropdown Menu -->
<a href="{{ pelanggan_route('orders') }}">Pesanan Saya</a>
<a href="{{ pelanggan_route('returns.create') }}">Retur</a>

<!-- Logout Form -->
<form method="POST" action="{{ pelanggan_route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
```

### 2. resources/views/pelanggan/dashboard.blade.php
```blade
<!-- Search Form -->
<form action="{{ pelanggan_route('dashboard') }}" method="GET">
    <input type="text" name="q" placeholder="Cari produk...">
    <button type="submit">Cari</button>
</form>

<!-- Buttons -->
<a href="{{ pelanggan_route('dashboard') }}" class="btn">Mulai Belanja</a>
<a href="{{ pelanggan_route('cart') }}" class="btn">Keranjang Saya</a>

<!-- Category Filter -->
<a href="{{ pelanggan_route('dashboard', ['kategori' => $kat->id]) }}">{{ $kat->nama }}</a>
```

### 3. resources/views/pelanggan/auth/login-register.blade.php
```blade
<!-- Login Form -->
<form action="{{ pelanggan_route('login.post') }}" method="POST">
    @csrf
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>

<!-- Register Form -->
<form action="{{ pelanggan_route('register.post') }}" method="POST">
    @csrf
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Daftar</button>
</form>

<!-- Back Link -->
<a href="{{ pelanggan_route('dashboard') }}">Kembali ke Katalog</a>
```

### 4. resources/views/pelanggan/cart.blade.php
```blade
<!-- Checkout Button -->
<a href="{{ pelanggan_route('checkout') }}" class="btn">Lanjut ke Checkout</a>

<!-- Continue Shopping -->
<a href="{{ pelanggan_route('dashboard') }}" class="btn">Lanjut Belanja</a>

<!-- Form Action -->
<form action="{{ pelanggan_route('cart.clear') }}" method="POST">
    @csrf
    <button type="submit">Kosongkan Keranjang</button>
</form>
```

### 5. resources/views/pelanggan/checkout.blade.php
```blade
<!-- Form Action -->
<form action="{{ pelanggan_route('checkout.process') }}" method="POST">
    @csrf
    <!-- Form fields -->
</form>

<!-- Back Link -->
<a href="{{ pelanggan_route('cart') }}">Kembali ke Keranjang</a>
```

### 6. resources/views/pelanggan/orders.blade.php
```blade
<!-- Back Button -->
<a href="{{ pelanggan_route('dashboard') }}" class="btn">Kembali Belanja</a>

<!-- Order Detail Link -->
<a href="{{ pelanggan_route('orders.show', ['order' => $order->id]) }}">Detail</a>

<!-- Review Button -->
<form action="{{ pelanggan_route('reviews.store') }}" method="POST">
    @csrf
    <!-- Form fields -->
</form>
```

### 7. resources/views/pelanggan/favorites.blade.php
```blade
<!-- Back Link -->
<a href="{{ pelanggan_route('dashboard') }}">Jelajahi Produk</a>

<!-- Add to Cart Form -->
<form action="{{ pelanggan_route('cart.ajax.store') }}" method="POST">
    @csrf
    <!-- Form fields -->
</form>
```

### 8. resources/views/pelanggan/returns/create.blade.php
```blade
<!-- Form Action -->
<form action="{{ pelanggan_route('returns.store') }}" method="POST">
    @csrf
    <!-- Form fields -->
</form>

<!-- Back Button -->
<a href="{{ pelanggan_route('dashboard') }}">Batal</a>
```

## JavaScript Updates

### 1. AJAX Requests

**Sebelum:**
```javascript
fetch('/pelanggan/cart/ajax/store', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ produk_id: 1, qty: 1 })
});
```

**Sesudah:**
```javascript
// Tambahkan data attribute di HTML
<div data-perusahaan-slug="{{ perusahaan_slug() }}"></div>

// Di JavaScript
const slug = document.querySelector('[data-perusahaan-slug]').value;
fetch(`/${slug}/pelanggan/cart/ajax/store`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ produk_id: 1, qty: 1 })
});
```

### 2. Dynamic URLs

**Sebelum:**
```javascript
const cartUrl = '/pelanggan/cart';
const dashboardUrl = '/pelanggan/dashboard';
```

**Sesudah:**
```javascript
// Di Blade template
<script>
    const cartUrl = '{{ pelanggan_url("/cart") }}';
    const dashboardUrl = '{{ pelanggan_url("/dashboard") }}';
</script>
```

## Checklist Update

- [ ] Update `resources/views/layouts/pelanggan.blade.php`
- [ ] Update `resources/views/pelanggan/dashboard.blade.php`
- [ ] Update `resources/views/pelanggan/auth/login-register.blade.php`
- [ ] Update `resources/views/pelanggan/cart.blade.php`
- [ ] Update `resources/views/pelanggan/checkout.blade.php`
- [ ] Update `resources/views/pelanggan/orders.blade.php`
- [ ] Update `resources/views/pelanggan/favorites.blade.php`
- [ ] Update `resources/views/pelanggan/returns/create.blade.php`
- [ ] Update JavaScript files dengan dynamic URLs
- [ ] Test semua links dan forms
- [ ] Test AJAX requests

## Testing

### 1. Test Links
```bash
# Buka browser dan test setiap link
http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard
http://localhost:8000/pt-arkan-trans-jaya/pelanggan/cart
http://localhost:8000/pt-arkan-trans-jaya/pelanggan/login
```

### 2. Test Forms
```bash
# Test login form
# Test register form
# Test checkout form
# Test review form
```

### 3. Test AJAX
```bash
# Test add to cart
# Test toggle favorite
# Test update cart quantity
```

## Common Issues

### Issue 1: Route not found
**Penyebab:** Lupa menambahkan parameter `perusahaan_slug`
**Solusi:** Gunakan helper `pelanggan_route()` yang otomatis menambahkan parameter

### Issue 2: 404 Not Found
**Penyebab:** URL tidak sesuai dengan routing
**Solusi:** Pastikan menggunakan helper functions, bukan hardcode URL

### Issue 3: Session lost
**Penyebab:** Middleware tidak set perusahaan
**Solusi:** Verifikasi middleware terdaftar di routing

## Best Practices

1. **Selalu gunakan helper functions**
   ```blade
   <!-- ✅ Benar -->
   <a href="{{ pelanggan_route('dashboard') }}">Dashboard</a>
   
   <!-- ❌ Salah -->
   <a href="/pelanggan/dashboard">Dashboard</a>
   ```

2. **Gunakan data attributes untuk JavaScript**
   ```blade
   <div data-perusahaan-slug="{{ perusahaan_slug() }}"></div>
   ```

3. **Test setiap perubahan**
   - Test links
   - Test forms
   - Test AJAX requests

4. **Commit changes secara bertahap**
   - Update satu file template
   - Test
   - Commit
   - Lanjut ke file berikutnya

## Automation Script

Untuk mempercepat update, Anda bisa menggunakan script find & replace:

```bash
# Find all route() calls untuk pelanggan
grep -r "route('pelanggan\." resources/views/

# Replace dengan pelanggan_route()
# Gunakan IDE find & replace feature
```

## Selesai!

Setelah semua templates diupdate, sistem multi-tenant siap digunakan dengan URL berbasis perusahaan.

Verifikasi dengan mengakses:
```
http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard
```

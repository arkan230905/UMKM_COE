# Multi-Tenant Pelanggan E-Commerce System

## Overview

Sistem pelanggan e-commerce sekarang menggunakan URL berbasis perusahaan (multi-tenant). Setiap perusahaan memiliki URL unik dengan slug perusahaan di dalamnya.

## URL Structure

```
http://localhost:8000/{perusahaan_slug}/pelanggan/dashboard
```

### Contoh:
- `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard`
- `http://localhost:8000/pr-6a09deaddd260/pelanggan/dashboard` (menggunakan kode)

## Perusahaan Slug

Slug perusahaan dihasilkan dari:
1. **Kolom `slug`** (jika ada) - prioritas utama
2. **Kolom `kode`** - jika slug kosong
3. **Kolom `nama`** - jika kode kosong

Slug dikonversi ke lowercase dan spasi diganti dengan hyphen.

### Contoh Konversi:
- `PT ARKAN TRANS JAYA` → `pt-arkan-trans-jaya`
- `ABC123` → `abc123`
- `PT MAJU JAYA` → `pt-maju-jaya`

## Cara Mengakses

### 1. Dashboard Pelanggan
```
GET /{perusahaan_slug}/pelanggan/dashboard
```

### 2. Login Pelanggan
```
GET /{perusahaan_slug}/pelanggan/login
POST /{perusahaan_slug}/pelanggan/login
```

### 3. Register Pelanggan
```
POST /{perusahaan_slug}/pelanggan/register
```

### 4. Keranjang
```
GET /{perusahaan_slug}/pelanggan/cart
POST /{perusahaan_slug}/pelanggan/cart
```

### 5. Checkout
```
GET /{perusahaan_slug}/pelanggan/checkout
POST /{perusahaan_slug}/pelanggan/checkout/process
```

### 6. Pesanan
```
GET /{perusahaan_slug}/pelanggan/orders
GET /{perusahaan_slug}/pelanggan/orders/{order_id}
```

### 7. Favorit
```
GET /{perusahaan_slug}/pelanggan/favorites
POST /{perusahaan_slug}/pelanggan/favorites/toggle
```

### 8. Retur
```
GET /{perusahaan_slug}/pelanggan/returns/create
POST /{perusahaan_slug}/pelanggan/returns
```

## Implementasi di Code

### Helper Functions

```php
// Get current perusahaan
$perusahaan = current_perusahaan();

// Get current perusahaan slug
$slug = perusahaan_slug();

// Generate route dengan perusahaan slug
$url = pelanggan_route('dashboard');
$url = pelanggan_route('cart');
$url = pelanggan_route('orders.show', ['order' => $order->id]);

// Generate URL
$url = pelanggan_url('/dashboard');
$url = pelanggan_url('/cart');
```

### Di Blade Template

```blade
<!-- Link ke dashboard -->
<a href="{{ pelanggan_route('dashboard') }}">Dashboard</a>

<!-- Link ke cart -->
<a href="{{ pelanggan_route('cart') }}">Keranjang</a>

<!-- Link ke orders dengan parameter -->
<a href="{{ pelanggan_route('orders.show', ['order' => $order->id]) }}">Detail Pesanan</a>

<!-- Link ke favorites -->
<a href="{{ pelanggan_route('favorites') }}">Favorit</a>
```

### Di Controller

```php
// Redirect dengan perusahaan slug
return redirect()->route('pelanggan.dashboard', ['perusahaan_slug' => perusahaan_slug()]);

// Redirect ke cart
return redirect()->route('pelanggan.cart', ['perusahaan_slug' => perusahaan_slug()]);

// Redirect ke orders
return redirect()->route('pelanggan.orders', ['perusahaan_slug' => perusahaan_slug()]);
```

## Middleware

Middleware `SetPerusahaanFromUrl` secara otomatis:
1. Menangkap `perusahaan_slug` dari URL
2. Mencari perusahaan berdasarkan slug, kode, atau nama
3. Menyimpan perusahaan di session
4. Menambahkan perusahaan ke request attributes

Jika perusahaan tidak ditemukan, middleware akan mengembalikan response 404.

## Database

### Kolom Perusahaan
- `id` - Primary key
- `user_id` - Owner ID (multi-tenant isolation)
- `nama` - Nama perusahaan
- `slug` - URL slug (unique)
- `kode` - Kode perusahaan
- `alamat` - Alamat
- `email` - Email
- `telepon` - Nomor telepon
- ... (kolom lainnya)

### Migrasi

Untuk menambahkan slug ke perusahaan yang sudah ada:
```bash
php artisan db:seed --class=UpdatePerusahaanSlugSeeder
```

## Keamanan

### Multi-Tenant Isolation
- Setiap perusahaan hanya bisa melihat produk miliknya (`user_id` match)
- Pelanggan tidak terikat ke perusahaan tertentu (dapat belanja di berbagai perusahaan)
- Data penjualan difilter berdasarkan `user_id` perusahaan

### Validasi
- Middleware memvalidasi perusahaan dari URL
- Jika perusahaan tidak ditemukan, request ditolak dengan 404
- Session menyimpan perusahaan untuk akses cepat

## Contoh Implementasi

### Menampilkan Produk Perusahaan

```php
public function index(Request $request)
{
    // Get perusahaan dari middleware
    $perusahaan = $request->attributes->get('perusahaan');
    
    // Query produk hanya dari perusahaan ini
    $produks = Produk::where('user_id', $perusahaan->user_id)->get();
    
    return view('pelanggan.dashboard', compact('produks', 'perusahaan'));
}
```

### Membuat Pesanan

```php
public function checkout(Request $request)
{
    $perusahaan = $request->attributes->get('perusahaan');
    
    // Validasi bahwa semua produk di cart milik perusahaan ini
    $cart = Cart::where('user_id', auth('pelanggan')->id())->get();
    
    foreach ($cart as $item) {
        if ($item->produk->user_id !== $perusahaan->user_id) {
            return back()->with('error', 'Produk tidak valid untuk perusahaan ini');
        }
    }
    
    // Proses checkout...
}
```

## Testing

### Test URL Pelanggan
```bash
# Dashboard
curl http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard

# Login
curl -X POST http://localhost:8000/pt-arkan-trans-jaya/pelanggan/login \
  -d "email=test@example.com&password=password"

# Cart
curl http://localhost:8000/pt-arkan-trans-jaya/pelanggan/cart
```

## Troubleshooting

### Perusahaan tidak ditemukan (404)
- Pastikan slug perusahaan benar
- Cek di database: `SELECT slug FROM perusahaan WHERE nama LIKE '%nama%'`
- Jika slug kosong, jalankan seeder: `php artisan db:seed --class=UpdatePerusahaanSlugSeeder`

### Produk tidak muncul
- Pastikan produk milik perusahaan yang benar (`user_id` match)
- Cek di database: `SELECT * FROM produks WHERE user_id = ?`

### Session hilang
- Pastikan middleware `set.perusahaan` terdaftar di routing
- Cek di `routes/web.php`: `->middleware('set.perusahaan')`

## Migrasi dari URL Lama

Jika sebelumnya menggunakan URL tanpa perusahaan slug:
```
Old: http://localhost:8000/pelanggan/dashboard
New: http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard
```

Update semua link di:
1. Blade templates - gunakan helper `pelanggan_route()`
2. Controllers - gunakan helper `pelanggan_route()`
3. JavaScript - update fetch URLs
4. Email templates - update links

## Future Enhancements

- [ ] Subdomain support: `pt-arkan-trans-jaya.localhost:8000/pelanggan/dashboard`
- [ ] Custom domain support: `shop.pt-arkan-trans-jaya.com/pelanggan/dashboard`
- [ ] Perusahaan branding per URL
- [ ] Analytics per perusahaan

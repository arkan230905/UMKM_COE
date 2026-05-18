# Implementasi Multi-Tenant Pelanggan E-Commerce

## Status Implementasi

✅ **SELESAI** - Sistem multi-tenant dengan nama perusahaan di URL telah diimplementasikan.

## Perubahan yang Dilakukan

### 1. Routing (routes/web.php)
- Mengubah prefix dari `pelanggan` menjadi `{perusahaan_slug}/pelanggan`
- Menambahkan middleware `set.perusahaan` ke semua route pelanggan
- Semua route sekarang menerima parameter `perusahaan_slug`

**Sebelum:**
```
GET /pelanggan/dashboard
GET /pelanggan/login
POST /pelanggan/register
```

**Sesudah:**
```
GET /{perusahaan_slug}/pelanggan/dashboard
GET /{perusahaan_slug}/pelanggan/login
POST /{perusahaan_slug}/pelanggan/register
```

### 2. Middleware (app/Http/Middleware/SetPerusahaanFromUrl.php)
- Middleware baru untuk menangkap `perusahaan_slug` dari URL
- Mencari perusahaan berdasarkan:
  1. Kolom `slug` (prioritas utama)
  2. Kolom `kode`
  3. Kolom `nama`
- Menyimpan perusahaan di session dan request attributes
- Mengembalikan 404 jika perusahaan tidak ditemukan

### 3. Model Perusahaan (app/Models/Perusahaan.php)
- Menambahkan kolom `slug` ke `$fillable`
- Menambahkan observer untuk auto-generate slug saat create/update
- Slug dihasilkan dari kode atau nama, dikonversi ke lowercase dengan hyphen

### 4. Database Migration (database/migrations/2026_05_18_000000_add_slug_to_perusahaan_table.php)
- Menambahkan kolom `slug` (unique, nullable) ke tabel `perusahaan`
- Kolom ditempatkan setelah `kode`

### 5. Seeder (database/seeders/UpdatePerusahaanSlugSeeder.php)
- Seeder untuk update slug pada perusahaan yang sudah ada
- Memastikan slug unik dengan menambahkan counter jika diperlukan
- Jalankan dengan: `php artisan db:seed --class=UpdatePerusahaanSlugSeeder`

### 6. Helper Functions (app/Helpers/helpers.php & app/Helpers/PerusahaanHelper.php)
- `current_perusahaan()` - Get perusahaan dari session
- `perusahaan_slug()` - Get slug perusahaan
- `pelanggan_route()` - Generate route dengan perusahaan slug
- `pelanggan_url()` - Generate URL dengan perusahaan slug

### 7. Controllers Update
- **DashboardController**: Filter produk berdasarkan `user_id` perusahaan
- **LoginController**: Menambahkan perusahaan ke redirect routes
- Semua controller sekarang menerima perusahaan dari middleware

### 8. HTTP Kernel (app/Http/Kernel.php)
- Mendaftarkan middleware alias `set.perusahaan`

## Cara Menggunakan

### 1. Akses Dashboard Pelanggan
```
http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard
```

Slug dapat berupa:
- Slug yang disimpan di database: `pt-arkan-trans-jaya`
- Kode perusahaan: `ABC123`
- Nama perusahaan (dengan hyphen): `pt-arkan-trans-jaya`

### 2. Di Blade Template
```blade
<!-- Link ke dashboard -->
<a href="{{ pelanggan_route('dashboard') }}">Dashboard</a>

<!-- Link ke cart -->
<a href="{{ pelanggan_route('cart') }}">Keranjang</a>

<!-- Link dengan parameter -->
<a href="{{ pelanggan_route('orders.show', ['order' => $order->id]) }}">Detail Pesanan</a>
```

### 3. Di Controller
```php
// Get perusahaan dari request
$perusahaan = $request->attributes->get('perusahaan');

// Redirect dengan perusahaan slug
return redirect()->route('pelanggan.dashboard', ['perusahaan_slug' => perusahaan_slug()]);

// Query produk hanya dari perusahaan ini
$produks = Produk::where('user_id', $perusahaan->user_id)->get();
```

### 4. Di JavaScript
```javascript
// Fetch dengan perusahaan slug
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

## Keamanan Multi-Tenant

### 1. Isolasi Data
- Setiap perusahaan hanya bisa melihat produk miliknya (`user_id` match)
- Pelanggan tidak terikat ke perusahaan tertentu
- Data penjualan difilter berdasarkan `user_id` perusahaan

### 2. Validasi URL
- Middleware memvalidasi perusahaan dari URL
- Jika perusahaan tidak ditemukan, request ditolak dengan 404
- Session menyimpan perusahaan untuk akses cepat

### 3. Query Filtering
- Semua query produk harus filter berdasarkan `user_id` perusahaan
- Best sellers hanya dari penjualan perusahaan tersebut
- Kategori hanya dari perusahaan tersebut

## Contoh URL

### Perusahaan: PT ARKAN TRANS JAYA
- Slug: `pt-arkan-trans-jaya`
- Dashboard: `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard`
- Login: `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/login`
- Cart: `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/cart`
- Checkout: `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/checkout`
- Orders: `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/orders`
- Favorites: `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/favorites`

### Perusahaan: PT MAJU JAYA
- Slug: `pt-maju-jaya`
- Dashboard: `http://localhost:8000/pt-maju-jaya/pelanggan/dashboard`
- Login: `http://localhost:8000/pt-maju-jaya/pelanggan/login`
- Cart: `http://localhost:8000/pt-maju-jaya/pelanggan/cart`

## Testing

### 1. Cek Perusahaan di Database
```sql
SELECT id, nama, kode, slug FROM perusahaan LIMIT 10;
```

### 2. Update Slug untuk Perusahaan Lama
```bash
php artisan db:seed --class=UpdatePerusahaanSlugSeeder
```

### 3. Test Multi-Tenant System
```bash
php artisan test:multi-tenant-pelanggan
```

### 4. Manual Testing
```bash
# Test dashboard
curl http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard

# Test login
curl -X POST http://localhost:8000/pt-arkan-trans-jaya/pelanggan/login \
  -d "email=test@example.com&password=password"

# Test cart
curl http://localhost:8000/pt-arkan-trans-jaya/pelanggan/cart
```

## Troubleshooting

### 1. Perusahaan tidak ditemukan (404)
**Solusi:**
- Pastikan slug perusahaan benar
- Cek di database: `SELECT slug FROM perusahaan WHERE nama LIKE '%nama%'`
- Jika slug kosong, jalankan seeder: `php artisan db:seed --class=UpdatePerusahaanSlugSeeder`

### 2. Produk tidak muncul
**Solusi:**
- Pastikan produk milik perusahaan yang benar (`user_id` match)
- Cek di database: `SELECT * FROM produks WHERE user_id = ?`
- Verifikasi di controller bahwa query filter berdasarkan `user_id` perusahaan

### 3. Session hilang
**Solusi:**
- Pastikan middleware `set.perusahaan` terdaftar di routing
- Cek di `routes/web.php`: `->middleware('set.perusahaan')`
- Verifikasi middleware di `app/Http/Kernel.php`

### 4. Route tidak ditemukan
**Solusi:**
- Pastikan parameter `perusahaan_slug` ditambahkan ke semua route calls
- Gunakan helper `pelanggan_route()` untuk generate route dengan otomatis
- Jangan hardcode route tanpa parameter

## File yang Diubah/Ditambah

### Ditambah:
- `app/Http/Middleware/SetPerusahaanFromUrl.php` - Middleware untuk set perusahaan
- `app/Helpers/PerusahaanHelper.php` - Helper class untuk perusahaan
- `app/Helpers/helpers.php` - Global helper functions
- `database/migrations/2026_05_18_000000_add_slug_to_perusahaan_table.php` - Migration untuk slug
- `database/seeders/UpdatePerusahaanSlugSeeder.php` - Seeder untuk update slug
- `app/Console/Commands/TestMultiTenantPelanggan.php` - Command untuk test
- `MULTI_TENANT_PELANGGAN.md` - Dokumentasi lengkap
- `IMPLEMENTASI_MULTI_TENANT.md` - File ini

### Diubah:
- `routes/web.php` - Update routing dengan perusahaan slug
- `app/Http/Kernel.php` - Daftarkan middleware alias
- `app/Models/Perusahaan.php` - Tambah slug ke fillable dan observer
- `app/Http/Controllers/Pelanggan/DashboardController.php` - Filter produk berdasarkan perusahaan
- `app/Http/Controllers/Pelanggan/Auth/LoginController.php` - Update redirect dengan perusahaan slug
- `composer.json` - Sudah terdaftar helpers.php di autoload

## Next Steps

1. **Jalankan Migration:**
   ```bash
   php artisan migrate
   ```

2. **Update Slug Perusahaan:**
   ```bash
   php artisan db:seed --class=UpdatePerusahaanSlugSeeder
   ```

3. **Test Sistem:**
   ```bash
   php artisan test:multi-tenant-pelanggan
   ```

4. **Update Blade Templates:**
   - Ganti semua `route('pelanggan.xxx')` dengan `pelanggan_route('xxx')`
   - Ganti semua `url('/pelanggan/xxx')` dengan `pelanggan_url('/xxx')`

5. **Update JavaScript:**
   - Ganti hardcoded URLs dengan dynamic URLs menggunakan perusahaan slug

6. **Test Manual:**
   - Akses `http://localhost:8000/{slug}/pelanggan/dashboard`
   - Verifikasi produk yang ditampilkan sesuai dengan perusahaan

## Catatan Penting

- **Backward Compatibility:** URL lama (`/pelanggan/dashboard`) tidak lagi berfungsi
- **Session Management:** Perusahaan disimpan di session untuk akses cepat
- **Multi-Tenant Isolation:** Setiap perusahaan hanya bisa melihat data miliknya
- **Slug Uniqueness:** Slug harus unik di database (enforced by migration)
- **Helper Functions:** Gunakan helper functions untuk generate URLs, jangan hardcode

## Support

Untuk pertanyaan atau masalah, silakan:
1. Cek dokumentasi di `MULTI_TENANT_PELANGGAN.md`
2. Jalankan test command: `php artisan test:multi-tenant-pelanggan`
3. Cek database untuk memastikan slug sudah ada
4. Verifikasi middleware terdaftar di routing

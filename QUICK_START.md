# Quick Start Guide - Multi-Tenant Pelanggan E-Commerce

## 🚀 Mulai dalam 5 Menit

### Step 1: Jalankan Migration (1 menit)
```bash
php artisan migrate
```

### Step 2: Update Slug Perusahaan (1 menit)
```bash
php artisan db:seed --class=UpdatePerusahaanSlugSeeder
```

### Step 3: Test Sistem (1 menit)
```bash
php artisan test:multi-tenant-pelanggan
```

### Step 4: Akses Dashboard (1 menit)
```
http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard
```

### Step 5: Update Templates (1 menit)
Ganti di Blade templates:
```blade
<!-- Sebelum -->
<a href="{{ route('pelanggan.dashboard') }}">Dashboard</a>

<!-- Sesudah -->
<a href="{{ pelanggan_route('dashboard') }}">Dashboard</a>
```

---

## 📋 Checklist

- [ ] Jalankan migration
- [ ] Update slug perusahaan
- [ ] Test sistem
- [ ] Akses dashboard
- [ ] Update templates
- [ ] Test manual

---

## 🔗 URL Examples

| Perusahaan | URL |
|-----------|-----|
| PT ARKAN TRANS JAYA | `http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard` |
| PT MAJU JAYA | `http://localhost:8000/pt-maju-jaya/pelanggan/dashboard` |

---

## 💡 Helper Functions

```php
// Get perusahaan saat ini
$perusahaan = current_perusahaan();

// Get slug
$slug = perusahaan_slug();

// Generate route
$url = pelanggan_route('dashboard');
$url = pelanggan_route('cart');
$url = pelanggan_route('orders.show', ['order' => $order->id]);

// Generate URL
$url = pelanggan_url('/dashboard');
```

---

## 🐛 Troubleshooting

### Perusahaan tidak ditemukan?
```bash
php artisan db:seed --class=UpdatePerusahaanSlugSeeder
```

### Produk tidak muncul?
Pastikan produk milik perusahaan yang benar di database.

### Route tidak ditemukan?
Gunakan helper `pelanggan_route()` bukan `route()`.

---

## 📚 Dokumentasi Lengkap

- **MULTI_TENANT_PELANGGAN.md** - Dokumentasi lengkap
- **IMPLEMENTASI_MULTI_TENANT.md** - Dokumentasi teknis
- **PANDUAN_UPDATE_TEMPLATES.md** - Update templates
- **RINGKASAN_IMPLEMENTASI.md** - Ringkasan

---

## ✅ Selesai!

Sistem multi-tenant siap digunakan!

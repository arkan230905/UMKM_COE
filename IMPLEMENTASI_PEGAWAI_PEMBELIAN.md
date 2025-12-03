# 🛒 Implementasi Halaman Pegawai Pembelian

## 📋 Konsep Sistem

### Role & Akses:

**1. Pelanggan (E-Commerce)**
- Belanja produk
- Checkout & bayar
- Data masuk ke: **Penjualan** (Owner/Admin lihat)

**2. Pegawai Pembelian**
- Tambah Bahan Baku
- Tambah Vendor
- Buat Transaksi Pembelian
- Pilih metode pembayaran
- Retur Pembelian
- Data masuk ke: **Pembelian** (Owner/Admin lihat)

**3. Admin/Owner**
- Lihat semua data
- Dashboard lengkap
- Laporan keuangan
- Monitoring semua transaksi

---

## 🎯 Fitur Pegawai Pembelian

### Menu yang Bisa Diakses:

**1. Dashboard**
- Ringkasan pembelian hari ini
- Total pembelian bulan ini
- Daftar vendor
- Stok bahan baku

**2. Master Data**
- ✅ Bahan Baku (CRUD)
- ✅ Vendor (CRUD)
- ❌ Produk (tidak bisa akses)
- ❌ COA (tidak bisa akses)
- ❌ Pegawai (tidak bisa akses)

**3. Transaksi**
- ✅ Pembelian Bahan Baku (CRUD)
  - Pilih vendor
  - Pilih bahan baku
  - Input qty & harga
  - Pilih metode pembayaran (Tunai/Transfer/Kredit)
  - Generate nomor pembelian otomatis
- ✅ Retur Pembelian (CRUD)
  - Pilih pembelian yang mau diretur
  - Input qty retur
  - Alasan retur
- ❌ Penjualan (tidak bisa akses)
- ❌ Penggajian (tidak bisa akses)

**4. Laporan**
- ✅ Laporan Pembelian (Read Only)
- ✅ Laporan Retur Pembelian (Read Only)
- ❌ Laporan Keuangan (tidak bisa akses)

---

## 🔐 Keamanan & Validasi

### Middleware:
```php
// Hanya pegawai_pembelian yang bisa akses
Route::middleware(['auth', 'check.role:pegawai_pembelian'])
```

### Validasi di Controller:
```php
if (auth()->user()->role !== 'pegawai_pembelian') {
    abort(403, 'Unauthorized');
}
```

---

## 🎨 Design Layout

### Layout Khusus Pegawai Pembelian:
- Navbar sederhana (bukan sidebar penuh)
- Menu horizontal:
  - Dashboard
  - Bahan Baku
  - Vendor
  - Pembelian
  - Retur
  - Laporan
- User dropdown (Profile, Logout)

### Warna Theme:
- Primary: Blue (#3498db)
- Secondary: Green (#2ecc71)
- Danger: Red (#e74c3c)

---

## 📊 Flow Transaksi Pembelian

```
1. Pegawai Pembelian Login
   ↓
2. Dashboard Pegawai Pembelian
   ↓
3. Klik "Buat Pembelian"
   ↓
4. Pilih Vendor
   ↓
5. Tambah Bahan Baku:
   - Pilih bahan baku
   - Input qty
   - Harga otomatis/manual
   ↓
6. Pilih Metode Pembayaran:
   - Tunai
   - Transfer Bank
   - Kredit (Hutang)
   ↓
7. Submit Pembelian
   ↓
8. Data Masuk ke Database
   ↓
9. Admin/Owner Bisa Lihat di:
   - Laporan Pembelian
   - Dashboard Admin
   - Jurnal Akuntansi
```

---

## 📊 Flow Retur Pembelian

```
1. Pegawai Pembelian Login
   ↓
2. Klik "Retur Pembelian"
   ↓
3. Pilih Pembelian yang Mau Diretur
   ↓
4. Pilih Bahan Baku yang Diretur
   ↓
5. Input Qty Retur
   ↓
6. Input Alasan Retur
   ↓
7. Submit Retur
   ↓
8. Stok Bahan Baku Berkurang
   ↓
9. Data Masuk ke Database
   ↓
10. Admin/Owner Bisa Lihat di:
    - Laporan Retur
    - Dashboard Admin
```

---

## 🗄️ Database yang Digunakan

### Tables:
1. **bahan_bakus** - Master bahan baku
2. **vendors** - Master vendor
3. **pembelians** - Header pembelian
4. **pembelian_details** - Detail item pembelian
5. **returs** - Header retur
6. **retur_details** - Detail item retur

### Relasi:
- Pembelian → Vendor (many to one)
- Pembelian → Pembelian Details (one to many)
- Pembelian Details → Bahan Baku (many to one)
- Retur → Pembelian (many to one)
- Retur → Retur Details (one to many)

---

## 🚀 Implementasi

### 1. Routes
```php
// Pegawai Pembelian Routes
Route::prefix('pegawai-pembelian')->name('pegawai-pembelian.')->group(function () {
    Route::get('/dashboard', [PegawaiPembelianController::class, 'dashboard']);
    
    // Bahan Baku
    Route::resource('bahan-baku', BahanBakuController::class);
    
    // Vendor
    Route::resource('vendor', VendorController::class);
    
    // Pembelian
    Route::resource('pembelian', PembelianController::class);
    
    // Retur
    Route::resource('retur', ReturPembelianController::class);
    
    // Laporan
    Route::get('/laporan/pembelian', [LaporanController::class, 'pembelian']);
    Route::get('/laporan/retur', [LaporanController::class, 'retur']);
});
```

### 2. Controllers
- `PegawaiPembelianController` - Dashboard
- `BahanBakuController` - CRUD bahan baku
- `VendorController` - CRUD vendor
- `PembelianController` - CRUD pembelian
- `ReturPembelianController` - CRUD retur

### 3. Views
- `pegawai-pembelian/dashboard.blade.php`
- `pegawai-pembelian/bahan-baku/*`
- `pegawai-pembelian/vendor/*`
- `pegawai-pembelian/pembelian/*`
- `pegawai-pembelian/retur/*`
- `pegawai-pembelian/laporan/*`

### 4. Layout
- `layouts/pegawai-pembelian.blade.php` (navbar horizontal)

---

## ✅ Checklist Implementasi

- [ ] Buat layout pegawai pembelian
- [ ] Buat dashboard pegawai pembelian
- [ ] Implementasi CRUD bahan baku
- [ ] Implementasi CRUD vendor
- [ ] Implementasi transaksi pembelian
- [ ] Implementasi retur pembelian
- [ ] Implementasi laporan
- [ ] Testing semua fitur
- [ ] Dokumentasi

---

## 📝 Catatan Penting

1. **Data Terintegrasi:**
   - Pembelian oleh pegawai → Masuk ke laporan admin
   - Penjualan oleh pelanggan → Masuk ke laporan admin
   - Admin/Owner lihat semua

2. **Akses Terbatas:**
   - Pegawai pembelian hanya lihat data pembelian
   - Tidak bisa lihat penjualan
   - Tidak bisa lihat keuangan lengkap

3. **Validasi:**
   - Cek role di setiap controller
   - Cek ownership data
   - Validasi stok bahan baku

---

**Status:** Siap untuk implementasi!

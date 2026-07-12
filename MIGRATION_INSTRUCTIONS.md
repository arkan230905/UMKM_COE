# Migration Instructions untuk Flow "Ambil di Toko"

## ⚠️ PENTING: Jalankan Migration Ini

Ada error yang terjadi karena kolom `completed_at` dan beberapa kolom lainnya belum ada di tabel `orders`. 

### Cara Mengatasi:

#### **Opsi 1: Jika Menggunakan Terminal/Command Line (RECOMMENDED)**

1. Buka terminal/command prompt di folder project
2. Jalankan perintah:
```bash
php artisan migrate
```

Ini akan menjalankan migration terbaru yang menambahkan kolom-kolom:
- `completed_at` ← untuk menandai waktu pesanan selesai
- `picked_up_at` ← untuk menandai waktu pesanan diambil (Ambil di Toko)
- `payment_success_at` ← untuk menandai waktu pembayaran berhasil
- `ready_pickup_at` ← untuk menandai waktu pesanan siap diambil
- `shipped_at` ← untuk menandai waktu pesanan dikirim (Delivery)
- `approved_at` ← untuk menandai waktu pesanan disetujui
- `rejected_at` ← untuk menandai waktu pesanan ditolak
- `perusahaan_id` ← untuk multi-tenant support

#### **Opsi 2: Jika Menggunakan Database GUI (phpMyAdmin, DBeaver, dll)**

Jalankan SQL query berikut di database Anda:

```sql
-- Tambah kolom-kolom yang diperlukan untuk flow "Ambil di Toko"
ALTER TABLE orders ADD COLUMN completed_at TIMESTAMP NULL DEFAULT NULL AFTER paid_at;
ALTER TABLE orders ADD COLUMN payment_success_at TIMESTAMP NULL DEFAULT NULL AFTER completed_at;
ALTER TABLE orders ADD COLUMN ready_pickup_at TIMESTAMP NULL DEFAULT NULL AFTER payment_success_at;
ALTER TABLE orders ADD COLUMN picked_up_at TIMESTAMP NULL DEFAULT NULL AFTER ready_pickup_at;
ALTER TABLE orders ADD COLUMN shipped_at TIMESTAMP NULL DEFAULT NULL AFTER picked_up_at;
ALTER TABLE orders ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL AFTER shipped_at;
ALTER TABLE orders ADD COLUMN rejected_at TIMESTAMP NULL DEFAULT NULL AFTER approved_at;

-- Tambah foreign key perusahaan_id jika belum ada
-- ALTER TABLE orders ADD COLUMN perusahaan_id BIGINT UNSIGNED NULL AFTER user_id;
-- ALTER TABLE orders ADD CONSTRAINT orders_perusahaan_id_foreign FOREIGN KEY (perusahaan_id) REFERENCES perusahaans(id) ON DELETE CASCADE;
```

### Setelah Migration Berhasil:

✅ Kolom `completed_at` sudah ada di tabel orders
✅ Semua kolom timestamp untuk flow pesanan sudah lengkap
✅ Fitur "Diambil" untuk Ambil di Toko bisa digunakan tanpa error
✅ Tombol "Diambil" di halaman admin tidak akan error lagi

### Verifikasi Migration:

Setelah migration selesai, bisa kembali ke halaman admin dan coba klik tombol "Diambil" lagi. Seharusnya sekarang berjalan tanpa error!

---

## 📝 File Migration yang Ditambahkan:
- `database/migrations/2026_07_13_000000_add_pickup_timestamps_to_orders_table.php`

## 📝 File yang Diupdate:
- `app/Http/Controllers/PenjualanController.php` - Method pickupOrder() sudah update untuk handle kolom yang belum ada dengan Schema::hasColumn() check

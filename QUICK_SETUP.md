# Quick Setup - COA Ayam Ketumbar

## 🚀 Langkah Cepat untuk Tim

Setelah `git pull`, jalankan perintah berikut:

```bash
# 1. Copy .env jika belum ada
copy .env.example .env

# 2. Edit .env sesuai database lokal Anda
# DB_DATABASE=nama_database_anda
# DB_USERNAME=root
# DB_PASSWORD=password_anda

# 3. Install dependencies
composer install

# 4. Reset database dan jalankan seeder
php artisan migrate:fresh --seed
```

## ✅ Selesai!

Database Anda sekarang menggunakan **COA Ayam Ketumbar**.

---

## ⚠️ PENTING
- Perintah `migrate:fresh --seed` akan **menghapus semua data**
- Pastikan backup data penting terlebih dahulu
- Hanya untuk environment **development/local**

---

Untuk panduan lengkap, baca: [PANDUAN_SETUP_COA_AYAM_KETUMBAR.md](./PANDUAN_SETUP_COA_AYAM_KETUMBAR.md)

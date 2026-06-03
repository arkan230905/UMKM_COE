# Panduan Setup COA Ayam Ketumbar

## Untuk Tim Developer

Setelah melakukan `git pull` dari repository, ikuti langkah-langkah berikut untuk menyinkronkan database dengan COA Ayam Ketumbar dan perubahan tabel BTKL:

---

## 📋 Perubahan Terbaru

### 1. **COA Default untuk User Baru**
User baru yang mendaftar akan otomatis mendapat **COA Ayam Ketumbar** (bukan Jasuke).

### 2. **Tabel BTKL Disederhanakan**
Kolom yang tidak digunakan telah dihapus:
- ❌ `tarif_per_jam`
- ❌ `satuan`
- ❌ `kapasitas_per_jam`

Sekarang menggunakan **pembebanan per produk** dari `jabatans.tarif_produk`.

---

## 📋 Langkah-Langkah Setup

### 1. Pull Perubahan dari Repository
```bash
git pull origin main
```
atau sesuaikan dengan nama branch yang digunakan.

---

### 2. Pastikan File `.env` Sudah Dikonfigurasi
Pastikan file `.env` Anda sudah memiliki konfigurasi database yang benar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=password_anda
```

**Catatan:** Jika belum ada file `.env`, copy dari `.env.example`:
```bash
copy .env.example .env
```
Kemudian edit sesuai konfigurasi database lokal Anda.

---

### 3. Install Dependencies (Jika Belum)
```bash
composer install
```

---

### 4. **PENTING: Backup Database Lama (Opsional)**
Jika Anda memiliki data penting di database lama, backup terlebih dahulu:

```bash
# Untuk MySQL
mysqldump -u root -p nama_database_anda > backup_database_$(date +%Y%m%d_%H%M%S).sql
```

---

### 5. Reset Database dan Jalankan Seeder
Jalankan perintah berikut untuk membersihkan database dan mengisi ulang dengan COA Ayam Ketumbar:

```bash
php artisan migrate:fresh --seed
```

**ATAU jika hanya ingin menjalankan migrasi baru:**

```bash
php artisan migrate
```

**Perintah `migrate:fresh --seed` akan:**
- ✅ Menghapus semua tabel yang ada
- ✅ Membuat ulang semua tabel dari migrasi
- ✅ Menjalankan seeder dengan urutan:
  1. UserSeeder (membuat user default)
  2. CompanySeeder (membuat data perusahaan)
  3. CoaTemplateSeeder (template COA)
  4. **AyamKetumbarCoaSeeder** (COA untuk usaha ayam)

---

### 6. Verifikasi Setup Berhasil
Setelah seeder selesai, Anda akan melihat output seperti ini:

```
INFO  Seeding database.

Database\Seeders\UserSeeder ............... DONE
Database\Seeders\CompanySeeder ............ DONE
Database\Seeders\CoaTemplateSeeder ........ DONE
Database\Seeders\AyamKetumbarCoaSeeder .... DONE
```

---

### 7. Login ke Aplikasi
Gunakan kredensial default yang dibuat oleh UserSeeder:

**Cek file:** `database/seeders/UserSeeder.php` untuk melihat user yang dibuat.

Biasanya:
- **Email:** admin@example.com (atau sesuai yang ada di seeder)
- **Password:** password (atau sesuai yang ada di seeder)

---

## 🔍 Apa yang Berubah?

### Perubahan File
File yang diubah dalam commit ini:
- `database/seeders/DatabaseSeeder.php` - Mengganti `JasukeCoaSeeder` menjadi `AyamKetumbarCoaSeeder`

### COA Ayam Ketumbar Mencakup:

#### 1. **Aset (11xxx)**
- Kas & Bank (111-113)
- Persediaan Bahan Baku Ayam (1141-1144):
  - Ayam Potong
  - Ayam Kampung
  - Bebek
  - Ayam Lainnya
- Persediaan Bahan Pendukung (1150-1157):
  - Air, Minyak Goreng, Tepung, Bumbu, Kemasan
- Persediaan Barang Jadi (1161-1162):
  - Ayam Crispy Macdi
  - Ayam Goreng Bundo
- Aset Tetap (119-126): Peralatan, Gedung, Kendaraan, Mesin

#### 2. **Kewajiban (21x)**
- Hutang Usaha, Hutang Gaji, PPN Keluaran

#### 3. **Modal (31x)**
- Modal Usaha, Prive

#### 4. **Pendapatan (41x)**
- Penjualan Produk Ayam
- Retur Penjualan
- Pendapatan Lain-lain

#### 5. **Biaya Produksi (51x-55x)**
- **BBB** - Biaya Bahan Baku (510-512)
- **BTKL** - Biaya Tenaga Kerja Langsung (520-522)
- **BOP** - Biaya Overhead Pabrik (530-538)
- **BOP BTKTL** - Tenaga Kerja Tidak Langsung (540-546)
- **BOP TL** - BOP Tidak Langsung Lainnya (550-559)

---

## ⚠️ Peringatan

### Data Akan Hilang!
Perintah `migrate:fresh --seed` akan **menghapus semua data** di database. Pastikan:
- ✅ Anda sudah backup data penting
- ✅ Ini adalah environment development/local
- ❌ **JANGAN** jalankan di production tanpa backup!

### Jika Ingin Mempertahankan Data Tertentu
Jika Anda ingin mempertahankan beberapa data, Anda bisa:

1. **Hanya menjalankan seeder COA:**
```bash
php artisan db:seed --class=AyamKetumbarCoaSeeder
```

2. **Atau hapus COA lama terlebih dahulu:**
```sql
DELETE FROM coas WHERE user_id = 1;
```
Kemudian jalankan seeder COA.

---

## 🆘 Troubleshooting

### Error: "COA already exists for user ID"
Jika Anda melihat pesan ini, berarti COA sudah ada. Anda bisa:
1. Hapus COA lama dari database
2. Atau jalankan `migrate:fresh --seed` untuk reset total

### Error: Database Connection
Pastikan:
- MySQL/MariaDB sudah running
- Kredensial di `.env` sudah benar
- Database sudah dibuat

### Error: Class Not Found
Jalankan:
```bash
composer dump-autoload
```

---

## 📞 Kontak

Jika ada masalah atau pertanyaan, hubungi:
- **Developer Lead:** [Nama Anda]
- **Email:** [Email Anda]

---

## 📝 Catatan Tambahan

- Seeder ini dirancang khusus untuk usaha **Ayam Ketumbar**
- COA mengikuti standar akuntansi untuk usaha manufaktur makanan
- Struktur COA mendukung **Process Costing** dan **Job Order Costing**

---

**Terakhir diupdate:** 25 Mei 2026
**Versi:** 1.0

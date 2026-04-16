# Setup Database - Panduan Instalasi

Panduan ini untuk setup database setelah pull dari GitHub.

## Langkah-langkah Setup

### 1. Clone Repository
```bash
git clone <repository-url>
cd <project-folder>
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env` dan sesuaikan dengan database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=username_database
DB_PASSWORD=password_database
```

### 5. Jalankan Migration
```bash
php artisan migrate:fresh
```

### 6. Jalankan Seeder
```bash
php artisan db:seed
```

Perintah ini akan otomatis mengisi:
- ✅ **Chart of Accounts (COA)** - 90+ akun siap pakai
- ✅ **Satuan** - 15 satuan (Kg, Gram, Liter, dll)
- ✅ **User Admin** default

### 7. Login ke Sistem
Gunakan kredensial default:
- **Email**: `adminumkm@gmail.com`
- **Password**: `password`

⚠️ **PENTING**: Segera ganti password setelah login pertama!

---

## Data yang Otomatis Terisi

### Chart of Accounts (COA)
Sistem akan otomatis membuat struktur COA lengkap:

#### 🏦 ASET (11)
- Kas Bank (111) - Rp 100.000.000
- Kas (112) - Rp 75.000.000
- Kas Kecil (113)
- Persediaan Bahan Baku (114-1144)
  - Ayam potong, ayam kampung, bebek, dll
- Persediaan Bahan Pendukung (115-1157)
  - Air, Minyak Goreng, Tepung, Bumbu, Kemasan
- Persediaan Barang Jadi (116-1162)
  - Ayam Crispy Macdi, Ayam Goreng Bundo
- Persediaan Barang dalam Proses (117)
- Piutang (118)
- Aset Tetap (119-126)
  - Peralatan, Gedung, Kendaraan, Mesin
  - Akumulasi Penyusutan
- PPN Masukkan (127)

#### 💰 KEWAJIBAN (21)
- Hutang Usaha (210)
- Hutang Gaji (211)
- PPN Keluaran (212)

#### 🏛️ MODAL (31)
- Modal Usaha (310)
- Prive (311)

#### 📈 PENDAPATAN (41-43)
- Penjualan Produk (410-411)
- Retur Penjualan (42)
- Pendapatan Ongkir (43)

#### 💸 BIAYA (51-55)
- **BBB** - Biaya Bahan Baku (51)
- **BTKL** - Biaya Tenaga Kerja Langsung (52)
- **BOP** - Biaya Overhead Pabrik (53)
- **BOP BTKTL** - Biaya Tenaga Kerja Tidak Langsung (54)
- **BOP TL** - BOP Tidak Langsung Lainnya (55)

### Satuan
- **Berat**: Gram, Ons, Kilogram
- **Volume**: Mililiter, Liter, Galon, Sendok Teh, Sendok Makan
- **Jumlah**: Pieces, Potong, Ekor, Bungkus, Tabung, Siung
- **Daya**: Watt

---

## Troubleshooting

### Error: "Access denied for user"
Pastikan kredensial database di `.env` sudah benar.

### Error: "Database does not exist"
Buat database terlebih dahulu:
```sql
CREATE DATABASE nama_database_anda;
```

### Error: "Class not found"
Jalankan:
```bash
composer dump-autoload
```

### Ingin Reset Database
```bash
php artisan migrate:fresh --seed
```
⚠️ **PERINGATAN**: Ini akan menghapus semua data!

---

## Seeder yang Tersedia

### InitialSetupSeeder (Otomatis)
Seeder utama yang berisi COA dan Satuan. Dijalankan otomatis saat `php artisan db:seed`.

### Seeder Development (Optional)
Jika ingin data sample untuk testing, uncomment di `DatabaseSeeder.php`:
- AccountsTableSeeder
- PegawaiSeeder
- BopSeeder
- PresensiSeeder

---

## Kontak & Support

Jika ada masalah saat setup, silakan hubungi tim development.

**Selamat menggunakan sistem! 🎉**

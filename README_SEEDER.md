# Database Seeder - Dokumentasi Lengkap

## 📋 Daftar Isi
- [Pengenalan](#pengenalan)
- [Cara Menggunakan](#cara-menggunakan)
- [Data yang Akan Terisi](#data-yang-akan-terisi)
- [Struktur Seeder](#struktur-seeder)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Pengenalan

Sistem ini dilengkapi dengan **InitialSetupSeeder** yang akan otomatis mengisi database dengan data master yang diperlukan untuk menjalankan aplikasi. Seeder ini dirancang agar siapapun yang pull dari GitHub dapat langsung menggunakan sistem tanpa perlu input data manual.

---

## 🚀 Cara Menggunakan

### Setup Pertama Kali (Fresh Install)

```bash
# 1. Clone repository
git clone <repository-url>
cd <project-folder>

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
# Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Jalankan migration dan seeder
php artisan migrate:fresh --seed
```

### Reset Database (Development)

```bash
# PERINGATAN: Ini akan menghapus semua data!
php artisan migrate:fresh --seed
```

### Jalankan Seeder Saja (Tanpa Migration)

```bash
php artisan db:seed
```

### Jalankan Seeder Spesifik

```bash
# Hanya COA dan Satuan
php artisan db:seed --class=InitialSetupSeeder

# Hanya User
php artisan db:seed --class=UserSeeder
```

---

## 📊 Data yang Akan Terisi

### 1. Chart of Accounts (COA) - 90+ Akun

#### 🏦 ASET (Kode: 11)
| Kode | Nama Akun | Saldo Awal |
|------|-----------|------------|
| 111 | Kas Bank | Rp 100.000.000 |
| 112 | Kas | Rp 75.000.000 |
| 113 | Kas Kecil | - |
| 114-1144 | Persediaan Bahan Baku | - |
| 115-1157 | Persediaan Bahan Pendukung | - |
| 116-1162 | Persediaan Barang Jadi | - |
| 117 | Persediaan Barang dalam Proses | - |
| 118 | Piutang | - |
| 119-126 | Aset Tetap & Akumulasi Penyusutan | - |
| 127 | PPN Masukkan | - |

**Detail Persediaan Bahan Baku:**
- Ayam potong (1141)
- Ayam kampung (1142)
- Bebek (1143)
- Ayam lainnya (1144)

**Detail Persediaan Bahan Pendukung:**
- Air (1150)
- Minyak Goreng (1151)
- Tepung Terigu (1152)
- Tepung Maizena (1153)
- Lada (1154)
- Bubuk Kaldu (1155)
- Bubuk Bawang Putih (1156)
- Kemasan (1157)

**Detail Persediaan Barang Jadi:**
- Ayam Crispy Macdi (1161)
- Ayam Goreng Bundo (1162)

**Detail Aset Tetap:**
- Peralatan (119) + Akumulasi Penyusutan (120)
- Gedung (121) + Akumulasi Penyusutan (122)
- Kendaraan (123) + Akumulasi Penyusutan (124)
- Mesin (125) + Akumulasi Penyusutan (126)

#### 💰 KEWAJIBAN (Kode: 21)
| Kode | Nama Akun |
|------|-----------|
| 210 | Hutang Usaha |
| 211 | Hutang Gaji |
| 212 | PPN Keluaran |

#### 🏛️ MODAL (Kode: 31)
| Kode | Nama Akun |
|------|-----------|
| 310 | Modal Usaha |
| 311 | Prive |

#### 📈 PENDAPATAN (Kode: 41-43)
| Kode | Nama Akun |
|------|-----------|
| 410 | Penjualan - Produk Ayam Crispy Macdi |
| 411 | Penjualan - Produk Ayam Goreng Bundo |
| 42 | Retur Penjualan |
| 43 | Pendapatan Ongkir |

#### 💸 BIAYA (Kode: 51-55)

**BBB - Biaya Bahan Baku (51)**
- BBB-ayam potong (510)
- BBB-ayam kampung (511)
- BBB-bebek (512)

**BTKL - Biaya Tenaga Kerja Langsung (52)**
- BTKL-Perbumbuan (520)
- BTKL-Penggorengan (521)
- BTKL-Pengemasan (522)

**BOP - Biaya Overhead Pabrik (53)**
- BOP-Biaya Bahan Baku Tidak Langsung (530)
- BOP-Air (531)
- BOP-Minyak Goreng (532)
- BOP-Tepung Terigu (533)
- BOP-Tepung Maizena (534)
- BOP-Lada (535)
- BOP-Bubuk Kaldu (536)
- BOP-Bubuk Bawang Putih (537)
- BOP-Kemasan (538)

**BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung (54)**
- Biaya Pegawai Pemasaran (540)
- Biaya Pegawai Kemasan (541)
- Biaya Satpam Pabrik (542)
- Biaya Cleaning Service (543)
- Biaya Mandor (544)
- Biaya Pegawai Keuangan (545)
- BTKTL Lainnya (546)

**BOP TL - BOP Tidak Langsung Lainnya (55)**
- Biaya Listrik (550)
- Sewa Tempat (551)
- Biaya Penyusutan Gedung (552)
- Biaya Penyusutan Peralatan (553)
- Biaya Penyusutan Kendaraan (554)
- Biaya Penyusutan Mesin (555)
- Biaya Air (556)
- Lainnya (557)
- Beban Transport Pembelian (558)
- Diskon Pembelian (559)

---

### 2. Satuan - 15 Satuan

| Kode | Nama | Kategori | Faktor ke Dasar |
|------|------|----------|-----------------|
| G | Gram | Berat | 1 |
| ONS | Ons | Berat | 100 |
| KG | Kilogram | Berat | 1000 |
| ML | Mililiter | Volume | 1 |
| LTR | Liter | Volume | 1000 |
| GL | Galon | Volume | 19000 |
| SDT | Sendok Teh | Volume | 5 |
| SDM | Sendok Makan | Volume | 15 |
| PCS | Pieces | Jumlah | 1 |
| PTG | Potong | Jumlah | 1 |
| EKOR | Ekor | Jumlah | 1 |
| BNGKS | Bungkus | Jumlah | 1 |
| TBG | Tabung | Jumlah | 1 |
| SNG | Siung | Jumlah | 1 |
| WATT | Watt | Daya | 1 |

---

### 3. Jenis Aset - 3 Jenis

| Nama | Deskripsi |
|------|-----------|
| Aset Tetap | Aset yang digunakan dalam operasional jangka panjang dan memiliki umur manfaat lebih dari satu tahun |
| Aset Lancar | Aset yang dapat dikonversi menjadi kas dalam waktu singkat (kurang dari satu tahun) |
| Aset Tidak Berwujud | Aset yang tidak memiliki bentuk fisik seperti hak paten, merek dagang, dan goodwill |

---

### 4. Kategori Aset - 10 Kategori

#### Aset Tetap
| Kode | Nama | Umur Ekonomis | Tarif Penyusutan | Disusutkan |
|------|------|---------------|------------------|------------|
| TNH | Tanah | 0 tahun | 0% | Tidak |
| BGN | Bangunan | 20 tahun | 5% | Ya |
| KND | Kendaraan | 8 tahun | 12.5% | Ya |
| PRL | Peralatan | 5 tahun | 20% | Ya |
| MSN | Mesin | 8 tahun | 12.5% | Ya |
| INV | Inventaris Kantor | 4 tahun | 25% | Ya |

#### Aset Lancar
| Kode | Nama | Disusutkan |
|------|------|------------|
| PSD | Persediaan | Tidak |
| PTG | Piutang | Tidak |

#### Aset Tidak Berwujud
| Kode | Nama | Umur Ekonomis | Tarif Penyusutan | Disusutkan |
|------|------|---------------|------------------|------------|
| GDW | Goodwill | 5 tahun | 20% | Ya |
| PTN | Paten | 10 tahun | 10% | Ya |

---

### 5. User Admin

| Field | Value |
|-------|-------|
| Name | Admin UMKM |
| Email | adminumkm@gmail.com |
| Password | password |

⚠️ **PENTING**: Segera ganti password setelah login pertama!

---

## 🏗️ Struktur Seeder

### InitialSetupSeeder.php
Seeder utama yang berisi:
- `seedCoa()` - Mengisi Chart of Accounts
- `seedSatuan()` - Mengisi Satuan
- `seedJenisAset()` - Mengisi Jenis Aset
- `seedKategoriAset()` - Mengisi Kategori Aset

### DatabaseSeeder.php
Orchestrator yang memanggil:
1. `InitialSetupSeeder` - Data master
2. `UserSeeder` - User admin

---

## 🔧 Troubleshooting

### Error: "SQLSTATE[42S02]: Base table or view not found"
**Solusi**: Jalankan migration terlebih dahulu
```bash
php artisan migrate
```

### Error: "Class 'Database\Seeders\InitialSetupSeeder' not found"
**Solusi**: Regenerate autoload
```bash
composer dump-autoload
```

### Error: "SQLSTATE[23000]: Integrity constraint violation"
**Solusi**: Reset database
```bash
php artisan migrate:fresh --seed
```

### Ingin Menambah Data COA Custom
Edit file `database/seeders/InitialSetupSeeder.php` di method `seedCoa()`:
```php
$coaData = [
    // ... existing data
    ['kode_akun' => 'XXX', 'nama_akun' => 'Nama Akun Baru', 'tipe_akun' => 'Aset', 'saldo_awal' => 0],
];
```

### Ingin Menambah Satuan Custom
Edit file `database/seeders/InitialSetupSeeder.php` di method `seedSatuan()`:
```php
$satuans = [
    // ... existing data
    ['kode' => 'XXX', 'nama' => 'Nama Satuan', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
];
```

---

## 📝 Catatan Penting

1. **Saldo Awal COA**: Hanya Kas Bank (Rp 100jt) dan Kas (Rp 75jt) yang memiliki saldo awal. Akun lainnya dimulai dari 0.

2. **Company ID**: Semua COA dibuat dengan `company_id = null` untuk template. Saat user membuat company, COA akan di-copy dengan company_id yang sesuai.

3. **Tarif Penyusutan**: Dihitung dengan rumus `100% / Umur Ekonomis`. Contoh: Bangunan 20 tahun = 5% per tahun.

4. **Faktor Konversi Satuan**: Digunakan untuk konversi antar satuan. Contoh: 1 Kg = 1000 Gram.

5. **Update Seeder**: Jika ada perubahan pada seeder, jalankan:
   ```bash
   composer dump-autoload
   php artisan migrate:fresh --seed
   ```

---

## 🎉 Selamat!

Database Anda sekarang sudah terisi dengan data master yang lengkap dan siap digunakan!

Untuk pertanyaan lebih lanjut, silakan hubungi tim development.

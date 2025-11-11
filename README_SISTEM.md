# SISTEM UMKM COE EADT - DOKUMENTASI LENGKAP

## 📋 DAFTAR ISI
1. [Tentang Sistem](#tentang-sistem)
2. [Fitur Utama](#fitur-utama)
3. [Teknologi](#teknologi)
4. [Instalasi](#instalasi)
5. [Struktur Database](#struktur-database)
6. [Alur Sistem](#alur-sistem)
7. [Testing](#testing)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 TENTANG SISTEM

Sistem UMKM COE EADT adalah aplikasi ERP (Enterprise Resource Planning) yang dirancang khusus untuk UMKM dengan fitur lengkap meliputi:
- Manajemen Master Data
- Transaksi Bisnis (Pembelian, Penjualan, Produksi)
- Akuntansi Terintegrasi
- Laporan Lengkap

### Keunggulan Sistem:
✅ **Terintegrasi Penuh** - Semua modul terhubung dengan akuntansi  
✅ **FIFO Stock Management** - Manajemen stok dengan metode FIFO  
✅ **Auto Journal Entry** - Jurnal akuntansi otomatis dari setiap transaksi  
✅ **Real-time Reporting** - Laporan real-time dan akurat  
✅ **User Friendly** - Interface mudah digunakan  

---

## 🚀 FITUR UTAMA

### 1. MASTER DATA
- **Pegawai** - Manajemen data pegawai dengan gaji dan tunjangan
- **Presensi** - Pencatatan kehadiran dan perhitungan jam kerja
- **Produk** - Master produk dengan HPP otomatis dari BOM
- **Vendor** - Master supplier dan customer
- **Bahan Baku** - Master bahan baku dengan manajemen stok FIFO
- **Satuan** - Master satuan dengan konversi
- **COA** - Chart of Accounts dengan hierarki
- **BOM** - Bill of Materials untuk perhitungan HPP produk
- **Aset** - Manajemen aset tetap dengan penyusutan otomatis

### 2. TRANSAKSI
- **Pembelian** - Pembelian bahan baku dengan update stok dan jurnal otomatis
- **Penjualan** - Penjualan produk dengan HPP FIFO dan jurnal otomatis
- **Retur** - Retur pembelian dan penjualan dengan approval workflow
- **Produksi** - Produksi dengan alokasi biaya (Bahan, BTKL, BOP)
- **Penggajian** - Penggajian pegawai berdasarkan presensi
- **Pembayaran Beban** - Pencatatan pembayaran beban operasional
- **Pelunasan Utang** - Pelunasan utang pembelian

### 3. LAPORAN
- **Laporan Stok** - Laporan stok dengan nilai persediaan
- **Laporan Pembelian** - Laporan pembelian dengan status pembayaran
- **Laporan Penjualan** - Laporan penjualan dengan laba kotor
- **Laporan Retur** - Laporan retur pembelian dan penjualan
- **Laporan Penggajian** - Laporan penggajian per periode
- **Laporan Aliran Kas** - Laporan cash flow
- **Laporan Penyusutan** - Laporan penyusutan aset

### 4. AKUNTANSI
- **Jurnal Umum** - Jurnal otomatis dari transaksi + manual entry
- **Buku Besar** - Buku besar per akun dengan saldo running
- **Neraca Saldo** - Neraca saldo per periode
- **Laba Rugi** - Laporan laba rugi

---

## 💻 TEKNOLOGI

### Backend
- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MySQL 8.0+

### Frontend
- **Template**: Bootstrap 5
- **JavaScript**: Vanilla JS + jQuery
- **Icons**: Font Awesome, Bootstrap Icons

### Libraries
- **PDF**: DomPDF / TCPDF
- **Excel**: PhpSpreadsheet
- **Charts**: Chart.js

---

## 📦 INSTALASI

### Prerequisites
```bash
- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & NPM (optional)
```

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone [repository-url]
cd COE_EADT_UMKM_COMPLETE
```

2. **Install Dependencies**
```bash
composer install
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Configuration**
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eadt_umkm
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run Migrations**
```bash
php artisan migrate
```

6. **Seed Database** (Optional)
```bash
php artisan db:seed
```

7. **Run Server**
```bash
php artisan serve
```

8. **Access Application**
```
http://127.0.0.1:8000
```

### Default Login
```
Email: admin@example.com
Password: password
```

---

## 🗄️ STRUKTUR DATABASE

### Master Data Tables
- `users` - User dan admin
- `pegawais` - Data pegawai
- `presensis` - Data presensi
- `produks` - Master produk
- `vendors` - Master vendor (supplier/customer)
- `bahan_bakus` - Master bahan baku
- `satuans` - Master satuan
- `coas` - Chart of Accounts
- `boms` - Bill of Materials
- `bom_details` - Detail BOM
- `asets` - Master aset tetap
- `jenis_asets` - Jenis aset
- `kategori_asets` - Kategori aset
- `jabatans` - Master jabatan

### Transaction Tables
- `pembelians` - Header pembelian
- `pembelian_details` - Detail pembelian
- `penjualans` - Header penjualan
- `penjualan_details` - Detail penjualan
- `returs` - Header retur
- `retur_details` - Detail retur
- `produksis` - Header produksi
- `produksi_details` - Detail produksi
- `penggajians` - Data penggajian
- `expense_payments` - Pembayaran beban
- `pelunasan_utangs` - Pelunasan utang

### Stock Management Tables
- `stock_layers` - Layer stok untuk FIFO
- `stock_movements` - Pergerakan stok

### Accounting Tables
- `journal_entries` - Header jurnal
- `journal_lines` - Detail jurnal (debit/kredit)

---

## 📊 ALUR SISTEM

Lihat file [SYSTEM_FLOW.md](SYSTEM_FLOW.md) untuk dokumentasi lengkap alur sistem.

### Alur Utama:

#### 1. Pembelian Bahan Baku
```
Input Pembelian → Validasi → Generate Nomor → Simpan Data
    ↓
Update Stok (FIFO) → Buat Stock Layer → Stock Movement
    ↓
Buat Jurnal Otomatis:
    Dr. Persediaan Bahan Baku
        Cr. Kas / Utang Usaha
```

#### 2. Produksi
```
Pilih Produk → Load BOM → Cek Stok Bahan
    ↓
Kurangi Stok Bahan (FIFO) → Hitung HPP
    ↓
Tambah Stok Produk → Alokasi BTKL & BOP
    ↓
Buat Jurnal Produksi:
    Dr. Barang Dalam Proses
        Cr. Persediaan Bahan Baku
    Dr. Barang Dalam Proses
        Cr. Gaji & Upah
    Dr. Barang Dalam Proses
        Cr. BOP
    Dr. Persediaan Produk Jadi
        Cr. Barang Dalam Proses
```

#### 3. Penjualan
```
Input Penjualan → Cek Stok → Validasi
    ↓
Kurangi Stok Produk (FIFO) → Hitung HPP
    ↓
Buat Jurnal Penjualan:
    Dr. Kas / Piutang
        Cr. Penjualan
    Dr. HPP
        Cr. Persediaan Produk
```

#### 4. Penyusutan Aset
```
Input Aset → Pilih Metode Penyusutan → Hitung Penyusutan
    ↓
Posting Penyusutan Bulanan:
    Dr. Beban Penyusutan
        Cr. Akumulasi Penyusutan
```

---

## 🧪 TESTING

Lihat file [TESTING_CHECKLIST.md](TESTING_CHECKLIST.md) untuk checklist testing lengkap.

### Testing Priority:

#### High Priority
1. ✅ Pembelian - Stock update & Journal entry
2. ✅ Penjualan - Stock update & HPP calculation
3. ✅ Produksi - BOM loading & Cost allocation
4. ✅ FIFO Logic - Stock layer & movement
5. ✅ Journal Entry - Auto journal from transactions

#### Medium Priority
1. ✅ Retur - Approval workflow
2. ✅ Penggajian - Salary calculation
3. ✅ Pelunasan Utang - Debt tracking
4. ✅ Laporan - Report accuracy

#### Low Priority
1. ✅ Master Data CRUD
2. ✅ UI/UX
3. ✅ Export features

---

## 🔧 TROUBLESHOOTING

### Issue 1: Migration Error
**Problem:** Migration gagal karena foreign key constraint

**Solution:**
```bash
# Drop semua tabel dan migrate ulang
php artisan migrate:fresh

# Atau migrate dengan force
php artisan migrate --force
```

### Issue 2: Stok Negatif
**Problem:** Stok menjadi negatif setelah transaksi

**Solution:**
- Cek validasi stok di controller
- Pastikan FIFO logic berjalan dengan benar
- Cek stock layer dan stock movement

### Issue 3: Jurnal Tidak Balance
**Problem:** Total debit ≠ Total kredit

**Solution:**
- Cek logic pembuatan jurnal di setiap transaksi
- Pastikan semua transaksi membuat jurnal lengkap
- Validasi sebelum save jurnal

### Issue 4: HPP Tidak Akurat
**Problem:** HPP produk tidak sesuai dengan biaya produksi

**Solution:**
- Cek BOM sudah dibuat dengan benar
- Pastikan FIFO mengambil harga dari stock layer yang benar
- Recalculate HPP setelah update BOM

### Issue 5: Penyusutan Tidak Terhitung
**Problem:** Penyusutan aset tidak terhitung otomatis

**Solution:**
- Cek metode penyusutan sudah dipilih
- Pastikan umur manfaat dan nilai residu sudah diisi
- Jalankan posting penyusutan manual jika perlu

---

## 📝 CATATAN PENTING

### 1. Backup Database
Selalu backup database sebelum:
- Update sistem
- Migrate database
- Hapus data massal

```bash
# Backup database
mysqldump -u root -p eadt_umkm > backup_$(date +%Y%m%d).sql

# Restore database
mysql -u root -p eadt_umkm < backup_20251110.sql
```

### 2. Maintenance Mode
Aktifkan maintenance mode saat update:
```bash
php artisan down
# Lakukan update
php artisan up
```

### 3. Clear Cache
Clear cache setelah update:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 4. Permission
Set permission yang benar:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📞 SUPPORT

Untuk bantuan dan support:
- **Email**: support@example.com
- **Phone**: +62 xxx xxxx xxxx
- **Documentation**: [Link to docs]

---

## 📄 LICENSE

[Your License Here]

---

## 👥 CONTRIBUTORS

- **Developer**: Tim Development COE EADT
- **Version**: 1.0
- **Last Update**: 10 November 2025

---

**© 2025 COE EADT UMKM System. All rights reserved.**

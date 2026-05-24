# 📋 Ringkasan Perubahan Terbaru

**Tanggal:** 25 Mei 2026

---

## ✅ Masalah yang Diselesaikan

### 1. **User Baru Masih Mendapat COA Jasuke**

**Masalah:**
- Saat user baru mendaftar, mereka mendapat COA Jasuke (Jagung, Susu, Keju)
- Padahal seharusnya mendapat COA Ayam Ketumbar

**Penyebab:**
- File `DefaultCoaSeeder.php` masih berisi COA Jasuke
- Listener `CreateDefaultUserData` memanggil `DefaultCoaSeeder` saat user registrasi

**Solusi:**
- ✅ Ganti isi `DefaultCoaSeeder.php` dengan COA Ayam Ketumbar
- ✅ User baru sekarang otomatis mendapat COA Ayam Ketumbar

---

### 2. **Tabel BTKL Memiliki Kolom yang Tidak Digunakan**

**Masalah:**
- Tabel `btkls` memiliki kolom `tarif_per_jam`, `satuan`, `kapasitas_per_jam`
- Kolom-kolom ini tidak digunakan lagi
- Sekarang menggunakan pembebanan per produk dari `jabatans.tarif_produk`

**Solusi:**
- ✅ Buat migrasi baru untuk menghapus kolom yang tidak digunakan
- ✅ Tabel `btkls` sekarang lebih sederhana dan sesuai kebutuhan

---

## 📁 File yang Diubah/Dibuat

### File yang Diubah:
1. ✅ `database/seeders/DefaultCoaSeeder.php` - Diganti dengan COA Ayam Ketumbar
2. ✅ `database/seeders/DatabaseSeeder.php` - Sudah menggunakan AyamKetumbarCoaSeeder
3. ✅ `PANDUAN_SETUP_COA_AYAM_KETUMBAR.md` - Update dengan info tabel BTKL

### File Baru yang Dibuat:
1. ✅ `database/migrations/2026_05_25_050411_remove_unused_columns_from_btkls_table.php`
2. ✅ `PERUBAHAN_COA_DAN_BTKL.md` - Dokumentasi lengkap perubahan
3. ✅ `RINGKASAN_PERUBAHAN_TERBARU.md` - File ini

---

## 🔄 Perubahan Database

### COA (Chart of Accounts)

**Sebelum:**
```
User Baru → DefaultCoaSeeder → COA Jasuke (Jagung, Susu, Keju)
```

**Sekarang:**
```
User Baru → DefaultCoaSeeder → COA Ayam Ketumbar (Ayam, Bumbu, dll)
```

### Tabel BTKL

**Sebelum:**
```sql
CREATE TABLE btkls (
    id,
    user_id,
    kode_proses,
    jabatan_id,
    tarif_per_jam,      -- ❌ Dihapus
    satuan,             -- ❌ Dihapus
    kapasitas_per_jam,  -- ❌ Dihapus
    deskripsi_proses,
    nama_btkl,
    is_active,
    timestamps
);
```

**Sekarang:**
```sql
CREATE TABLE btkls (
    id,
    user_id,
    kode_proses,
    jabatan_id,
    deskripsi_proses,
    nama_btkl,
    is_active,
    timestamps
);
```

**Tarif BTKL sekarang diambil dari:**
```sql
SELECT tarif_produk FROM jabatans WHERE id = btkls.jabatan_id;
```

---

## 🚀 Langkah untuk Tim

### Setelah `git pull`:

```bash
# 1. Jalankan migrasi baru (menghapus kolom BTKL yang tidak digunakan)
php artisan migrate

# 2. (Opsional) Jika ingin reset database total
php artisan migrate:fresh --seed
```

### Untuk User yang Sudah Ada:

Jika ingin mengganti COA lama ke COA Ayam Ketumbar:

```bash
php artisan tinker
```

```php
$userId = 1; // Ganti dengan user ID Anda
DB::table('coas')->where('user_id', $userId)->delete();
$seeder = new \Database\Seeders\AyamKetumbarCoaSeeder();
$seeder->run($userId);
```

---

## 📊 Struktur COA Ayam Ketumbar

### Aset (11xxx)
- **Bahan Baku Ayam** (1141-1144): Ayam Potong, Ayam Kampung, Bebek
- **Bahan Pendukung** (1150-1157): Air, Minyak, Tepung, Bumbu, Kemasan
- **Barang Jadi** (1161-1162): Ayam Crispy Macdi, Ayam Goreng Bundo

### Biaya Produksi (51x-55x)
- **BBB** (510-512): Biaya Bahan Baku Ayam
- **BTKL** (520-522): Perbumbuan, Penggorengan, Pengemasan
- **BOP** (530-538): Bahan Tidak Langsung, Air, Minyak, Tepung, Bumbu
- **BOP BTKTL** (540-546): Tenaga Kerja Tidak Langsung
- **BOP TL** (550-559): Listrik, Sewa, Penyusutan, dll

---

## ✅ Verifikasi

### Cek COA User Baru:
```sql
SELECT kode_akun, nama_akun, tipe_akun 
FROM coas 
WHERE user_id = 1 
ORDER BY kode_akun;
```

Pastikan ada akun seperti:
- `1141` - Pers. Bahan Baku ayam potong
- `1142` - Pers. Bahan Baku ayam kampung
- `1161` - Pers. Barang Jadi Ayam Crispy Macdi

### Cek Tabel BTKL:
```sql
DESCRIBE btkls;
```

Pastikan kolom `tarif_per_jam`, `satuan`, `kapasitas_per_jam` **TIDAK ADA**.

---

## 📝 Catatan Penting

### User Lama vs User Baru

| Aspek | User Lama | User Baru |
|-------|-----------|-----------|
| COA | Tetap menggunakan COA lama | Otomatis COA Ayam Ketumbar |
| BTKL | Kolom lama dihapus (tidak digunakan) | Struktur baru tanpa kolom tidak terpakai |
| Tarif BTKL | Dari `jabatans.tarif_produk` | Dari `jabatans.tarif_produk` |

### Keamanan Data

- ✅ Migrasi BTKL **AMAN** - hanya menghapus kolom yang tidak digunakan
- ✅ Data user lama **TIDAK TERPENGARUH** - COA mereka tetap ada
- ✅ Hanya user baru yang otomatis mendapat COA Ayam Ketumbar

---

## 🆘 Troubleshooting

### Error: "Column not found: tarif_per_jam"

**Solusi:**
```bash
php artisan migrate
```

### User Baru Masih Dapat COA Jasuke

**Solusi:**
```bash
# Pastikan DefaultCoaSeeder sudah terupdate
cat database/seeders/DefaultCoaSeeder.php | grep "Ayam"

# Jika tidak ada, pull ulang dari repository
git pull origin main
```

### Ingin Rollback Perubahan BTKL

```bash
php artisan migrate:rollback --step=1
```

---

## 📞 Kontak

Jika ada pertanyaan atau masalah:
- **Developer Lead:** [Nama Anda]
- **Email:** [Email Anda]

---

**Dibuat:** 25 Mei 2026  
**Versi:** 1.0

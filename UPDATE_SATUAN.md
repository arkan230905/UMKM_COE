# Update Data Satuan - Dokumentasi

## ✅ Status: SELESAI

Data Satuan sudah berhasil di-seed ke database dengan **15 satuan** sesuai kebutuhan.

---

## 📊 Data Satuan yang Tersedia

| Kode | Nama | Kategori | Faktor ke Dasar |
|------|------|----------|-----------------|
| **BNGKS** | Bungkus | jumlah | 1 |
| **EKOR** | Ekor | jumlah | 1 |
| **G** | Gram | berat | 1 |
| **GL** | Galon | volume | 19000 |
| **KG** | Kilogram | berat | 1000 |
| **LTR** | Liter | volume | 1000 |
| **ML** | Mililiter | volume | 1 |
| **ONS** | Ons | berat | 100 |
| **PCS** | Pieces | jumlah | 1 |
| **PTG** | Potong | jumlah | 1 |
| **SDM** | Sendok Makan | volume | 15 |
| **SDT** | Sendok Teh | volume | 5 |
| **SNG** | Siung | jumlah | 1 |
| **TBG** | Tabung | jumlah | 1 |
| **WATT** | Watt | jumlah | 1 |

---

## 🔧 Perubahan yang Dilakukan

### 1. File yang Dimodifikasi:

#### `database/seeders/DatabaseSeeder.php`
- Menambahkan `SatuanSeeder::class` ke dalam call list
- Urutan: CoaTemplateSeeder → SatuanSeeder → InitialSetupSeeder

#### `database/seeders/InitialSetupSeeder.php`
- Menghapus pemanggilan `$this->seedSatuan()`
- Fokus hanya pada Jenis Aset dan Kategori Aset

### 2. Seeder yang Digunakan:

#### `database/seeders/SatuanSeeder.php`
- Berisi 15 satuan lengkap
- Menggunakan `updateOrCreate` untuk menghindari duplikat
- Data global (tidak per company)

---

## 🚀 Cara Menggunakan

### Setup Awal (Fresh Install)

```bash
# Jalankan migration dan seeder
php artisan migrate:fresh --seed
```

Ini akan otomatis:
1. ✅ Membuat COA Template (81 akun)
2. ✅ Membuat Satuan (15 satuan)
3. ✅ Membuat Jenis Aset (3 jenis)
4. ✅ Membuat Kategori Aset (10 kategori)
5. ✅ Membuat User Admin

### Jika Satuan Kosong

```bash
# Jalankan seeder Satuan saja
php artisan db:seed --class=SatuanSeeder
```

### Verifikasi Data

```bash
# Cek jumlah satuan
php artisan tinker --execute="echo 'Total Satuan: ' . \App\Models\Satuan::count();"

# Atau jalankan script
php check_satuan.php
```

---

## 📝 Catatan Penting

### Satuan adalah Data Global

- **Tidak per company** - Semua company menggunakan satuan yang sama
- **Tidak perlu di-copy** saat user registrasi
- **Shared resource** untuk semua user

### Perbedaan dengan COA

| Aspek | COA | Satuan |
|-------|-----|--------|
| Scope | Per Company | Global |
| Saat Registrasi | Di-copy ke company baru | Tidak perlu copy |
| Jumlah | 81+ akun | 15 satuan |
| Template | Ada (company_id = null) | Tidak ada template |

---

## 🔍 Troubleshooting

### Satuan Tidak Muncul di Halaman

**Solusi:**
1. Refresh browser (Ctrl + F5)
2. Clear cache: `php artisan optimize:clear`
3. Jalankan seeder: `php artisan db:seed --class=SatuanSeeder`

### Error: "Duplicate entry for key 'kode'"

**Penyebab:** Satuan sudah ada di database

**Solusi:** Seeder menggunakan `updateOrCreate`, jadi tidak akan error. Jika tetap error, cek constraint di migration.

### Ingin Menambah Satuan Baru

Edit file `database/seeders/SatuanSeeder.php`:

```php
$satuans = [
    // ... existing data
    ['kode' => 'XXX', 'nama' => 'Nama Satuan', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
];
```

Jalankan ulang:
```bash
php artisan db:seed --class=SatuanSeeder
```

---

## ✅ Checklist Setup

- [x] COA Template (81 akun) ✓
- [x] Satuan (15 satuan) ✓
- [x] Jenis Aset (3 jenis) ✓
- [x] Kategori Aset (10 kategori) ✓
- [x] User Admin ✓
- [x] Event UserRegistered ✓
- [x] Listener SetupUserData ✓
- [x] Global Scope filterByCompany ✓

---

## 🎉 Sistem Siap Digunakan!

Semua data master sudah lengkap dan siap untuk:
- User baru registrasi
- Transaksi pembelian
- Transaksi penjualan
- Produksi
- Laporan

**Selamat menggunakan sistem! 🚀**

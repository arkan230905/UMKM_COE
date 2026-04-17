# 🚀 Quick Start - Master Data Lock

## Langkah Cepat (3 Menit)

### 1️⃣ Export Data Master Anda

```bash
php artisan master:export
```

**Output:**
```
✓ Export completed!
Total tables: 12
Total records: 450
Saved to: database/seeders/master_data/master_data_latest.json
```

### 2️⃣ Test (Opsional)

```bash
php artisan test:master-data --cleanup
```

**Output:**
```
✓ All data seeded successfully!
Tables matched: 12/12
Records seeded: 450/450
```

### 3️⃣ Selesai! ✅

Setiap owner baru yang register akan otomatis mendapat copy lengkap dari data Anda.

---

## 📋 Yang Akan Di-Copy ke Owner Baru

✅ **Semua data master Anda:**
- COA (Chart of Accounts)
- Satuan
- Jabatan
- Pegawai
- Jenis Aset & Kategori Aset
- Supplier
- Pelanggan
- Bahan Baku
- Bahan Pendukung
- Produk
- BOP

❌ **Yang TIDAK di-copy:**
- Data transaksi (pembelian, penjualan, dll)
- Data user lain
- Data perusahaan lain

---

## 🔄 Update Data Master

Jika Anda menambah/mengubah data dan ingin owner baru dapat data terbaru:

```bash
php artisan master:export
```

**Catatan:** Owner yang sudah register sebelumnya tidak akan ter-update.

---

## 🧪 Test Registrasi Owner Baru

### Manual Test:

1. Buka halaman registrasi
2. Daftar sebagai owner baru
3. Login dan cek apakah semua data ada

### Automated Test:

```bash
# Test tanpa cleanup (bisa login untuk cek manual)
php artisan test:master-data

# Test dengan cleanup (otomatis hapus setelah test)
php artisan test:master-data --cleanup
```

---

## 📊 Monitoring

### Cek Log:

```bash
tail -f storage/logs/laravel.log | grep "master data"
```

### Cek Database:

```sql
-- Cek perusahaan terakhir
SELECT * FROM perusahaan ORDER BY id DESC LIMIT 1;

-- Cek jumlah COA perusahaan baru (ganti 999 dengan perusahaan_id)
SELECT COUNT(*) FROM coa WHERE perusahaan_id = 999;
```

---

## ⚠️ Troubleshooting

### "Master data file not found"

```bash
php artisan master:export
```

### Owner baru tidak dapat data

1. Cek log: `tail -f storage/logs/laravel.log`
2. Cek file ada: `ls database/seeders/master_data/`
3. Test manual: `php artisan test:master-data`

---

## 📚 Dokumentasi Lengkap

Baca: [MASTER_DATA_LOCK_GUIDE.md](MASTER_DATA_LOCK_GUIDE.md)

---

## ✅ Checklist

- [ ] Export data master: `php artisan master:export`
- [ ] Test: `php artisan test:master-data --cleanup`
- [ ] Verifikasi file ada: `ls database/seeders/master_data/`
- [ ] Test registrasi owner baru
- [ ] Monitor log saat owner baru register

---

**Status:** ✅ Production Ready
**Dibuat:** 17 April 2026

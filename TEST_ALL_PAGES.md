# 🧪 PANDUAN TEST SEMUA HALAMAN

## 📋 CHECKLIST HALAMAN YANG HARUS DITEST

Silakan login ke website Anda dan test semua halaman berikut:

### ✅ 1. Halaman Profile
**URL:** https://jobcost.eadtmanufaktur.com/profile

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Data profil user yang login ditampilkan
- [ ] Bisa edit profil
- [ ] Bisa upload foto profil

---

### ✅ 2. Harga Pokok Produksi (BOM)
**URL:** https://jobcost.eadtmanufaktur.com/bom

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan BOM milik user yang login
- [ ] Tidak ada data BOM user lain

---

### ✅ 3. Laporan Penggajian
**URL:** https://jobcost.eadtmanufaktur.com/laporan/penggajian

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan data penggajian milik user yang login
- [ ] Filter tanggal berfungsi dengan baik
- [ ] Export PDF/Excel berfungsi

---

### ✅ 4. Laporan Pembayaran Beban
**URL:** https://jobcost.eadtmanufaktur.com/laporan/pembayaran-beban

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan pembayaran beban milik user yang login
- [ ] Filter tanggal berfungsi dengan baik

---

### ✅ 5. Laporan Pelunasan Utang
**URL:** https://jobcost.eadtmanufaktur.com/laporan/pelunasan-utang

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan pelunasan utang milik user yang login
- [ ] Filter tanggal berfungsi dengan baik

---

### ✅ 6. Laporan Kas dan Bank
**URL:** https://jobcost.eadtmanufaktur.com/laporan/kas-bank

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan transaksi kas/bank milik user yang login
- [ ] Filter tanggal dan akun berfungsi dengan baik

---

### ✅ 7. Jurnal Umum
**URL:** https://jobcost.eadtmanufaktur.com/akuntansi/jurnal-umum

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan jurnal milik user yang login
- [ ] Filter tanggal berfungsi dengan baik
- [ ] Export PDF/Excel berfungsi

---

### ✅ 8. Buku Besar
**URL:** https://jobcost.eadtmanufaktur.com/akuntansi/buku-besar

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan akun milik user yang login
- [ ] Filter bulan/tahun dan akun berfungsi dengan baik
- [ ] Saldo awal dan saldo akhir benar

---

### ✅ 9. Neraca Saldo
**URL:** https://jobcost.eadtmanufaktur.com/akuntansi/neraca-saldo

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan akun milik user yang login
- [ ] Filter bulan/tahun berfungsi dengan baik
- [ ] Total debit = Total kredit

---

### ✅ 10. Laporan Posisi Keuangan (Neraca)
**URL:** https://jobcost.eadtmanufaktur.com/akuntansi/laporan-posisi-keuangan

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan akun milik user yang login
- [ ] Filter periode berfungsi dengan baik
- [ ] Total Aset = Total Kewajiban + Ekuitas

---

### ✅ 11. Laba Rugi
**URL:** https://jobcost.eadtmanufaktur.com/akuntansi/laba-rugi

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Hanya menampilkan akun pendapatan/beban milik user yang login
- [ ] Filter periode berfungsi dengan baik
- [ ] Laba/Rugi = Total Pendapatan - Total Beban

---

### ✅ 12. Tentang Perusahaan
**URL:** https://jobcost.eadtmanufaktur.com/tentang-perusahaan

**Test:**
- [ ] Halaman bisa dibuka tanpa error 500
- [ ] Menampilkan data perusahaan milik user yang login
- [ ] Owner bisa edit data perusahaan
- [ ] Owner bisa edit informasi bank

---

## 🔍 CARA TEST MULTI-TENANT ISOLATION

### Test dengan 2 User Berbeda:

1. **Login sebagai User A**
   - Buat beberapa data (BOM, Penggajian, Jurnal, dll)
   - Catat ID atau nomor data yang dibuat

2. **Logout dan Login sebagai User B**
   - Buka halaman yang sama
   - **PASTIKAN:** Data User A **TIDAK MUNCUL**
   - Buat data baru untuk User B

3. **Login kembali sebagai User A**
   - **PASTIKAN:** Hanya data User A yang muncul
   - Data User B **TIDAK MUNCUL**

---

## ⚠️ JIKA MENEMUKAN ERROR

### Error 500
1. Cek log error:
   ```bash
   ssh simcost@103.134.154.77 "tail -100 /var/www/html/storage/logs/laravel.log"
   ```

2. Clear cache:
   ```bash
   ssh simcost@103.134.154.77 "cd /var/www/html && php artisan cache:clear && php artisan config:clear"
   ```

### Data User Lain Muncul
1. Segera laporkan halaman mana yang bermasalah
2. Catat URL dan screenshot
3. Saya akan segera perbaiki

---

## 📊 HASIL TEST

Setelah selesai test, mohon laporkan:

✅ **Halaman yang BERHASIL:** (list halaman yang berfungsi dengan baik)

❌ **Halaman yang BERMASALAH:** (list halaman yang masih error)

📝 **Catatan Tambahan:** (jika ada)

---

**Tanggal Test:** _____________
**Tester:** _____________
**Status:** [ ] PASS / [ ] FAIL

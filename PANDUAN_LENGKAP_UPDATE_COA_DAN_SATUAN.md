# 📋 PANDUAN LENGKAP UPDATE COA DAN SATUAN

## 🎯 MASALAH

### **COA di Hosting:**
- Data salah (Bank Kecil, Kas 1101, Bank BCA, dll)
- Kode akun tidak sesuai
- Ada "🔒 Tidak dapat diubah"

### **Satuan di Hosting:**
- Hanya 4 satuan (KG, ML, Sdm, Sdt)
- Kurang 12 satuan lainnya
- Ada "🔒 Tidak dapat diubah"

---

## ✅ SOLUSI LENGKAP

### **File yang Sudah Dibuat:**

1. **`database/seeders/DefaultCoaSeederBaru.php`**
   - 50 COA sesuai Jasuke

2. **`database/seeders/DefaultSatuanSeeder.php`**
   - 16 Satuan lengkap (sudah diupdate)

3. **`update_coa_dan_satuan_lengkap.php`**
   - Script update COA & Satuan sekaligus

4. **`update_coa_user_ke_jasuke.php`**
   - Script update COA saja

5. **`update_satuan_user_lengkap.php`**
   - Script update Satuan saja

---

## 🚀 CARA MENGGUNAKAN

### **OPSI 1: Update COA dan Satuan Sekaligus (RECOMMENDED)**

```bash
php update_coa_dan_satuan_lengkap.php
```

Ketik `yes` untuk konfirmasi.

**Output:**
```
==============================================
STEP 1: HAPUS DATA LAMA
==============================================

Menghapus semua COA...
  X COA dihapus

Menghapus semua Satuan...
  X Satuan dihapus

==============================================
STEP 2: BUAT DATA BARU
==============================================

User: MUHAMMAD ARKAN ABIYYU (ID: 1)

Membuat COA...
  ✅ 50 COA dibuat

Membuat Satuan...
  ✅ 16 Satuan dibuat

==============================================
SELESAI!
==============================================
```

---

### **OPSI 2: Update Terpisah**

#### **A. Update COA Saja:**
```bash
php update_coa_user_ke_jasuke.php
```

#### **B. Update Satuan Saja:**
```bash
php update_satuan_user_lengkap.php
```

---

## 📊 DATA YANG AKAN DIBUAT

### **COA (50 Akun):**

#### **Aset (23 akun):**
```
11    - Aset
111   - Kas Bank
112   - Kas
113   - Kas Kecil
114   - Pers. Bahan Baku
1141  - Pers. Bahan Baku Jagung
115   - Pers. Bahan Pendukung
1151  - Pers. Bahan Pendukung Susu
1152  - Pers. Bahan Pendukung Keju
1153  - Pers. Bahan Pendukung Kemasan (Cup)
116   - Pers. Barang Jadi
1161  - Pers. Barang Jadi Jasuke
117   - Pers. Barang dalam Proses
1171  - Pers. Barang Dalam Proses - BBB
1172  - Pers. Barang Dalam Proses - BTKL
1173  - Pers. Barang Dalam Proses - BOP
118   - Piutang
119   - Peralatan
120   - Akumulasi Penyusutan Peralatan
125   - Mesin
126   - Akumulasi Penyusutan Mesin
127   - PPN Masukkan
```

#### **Kewajiban (4 akun):**
```
21    - Hutang
210   - Hutang Usaha
211   - Hutang Gaji
212   - PPN Keluaran
```

#### **Modal (3 akun):**
```
31    - Modal
310   - Modal Usaha
311   - Prive
```

#### **Pendapatan (3 akun):**
```
41    - Penjualan
410   - Penjualan - Jasuke
42    - Retur Penjualan
```

#### **Biaya (17 akun):**
```
51    - BBB - Biaya Bahan Baku
510   - BBB - Jagung
513   - Beban Tunjangan
514   - Beban Asuransi
515   - Beban Bonus
516   - Potongan Gaji
52    - BTKL
520   - BTKL - Produksi Jasuke
53    - BOP
530   - BOP - Susu
531   - BOP - Keju
532   - BOP - Kemasan
54    - Beban Sewa
55    - BOP Lain
550   - BOP - Listrik
551   - BOP - Air
552   - BOP - Gas
553   - BOP - Penyusutan Peralatan
```

---

### **Satuan (16 Satuan):**

```
1.  ONS   - Ons
2.  KG    - Kilogram
3.  ML    - Mililiter
4.  G     - Gram
5.  LTR   - Liter
6.  PTG   - Potong
7.  EKOR  - Ekor
8.  SDT   - Sendok Teh
9.  SDM   - Sendok Makan
10. PCS   - Pieces
11. BNGKS - Bungkus
12. CUP   - Cup
13. GL    - Galon
14. TBG   - Tabung
15. SNG   - Siung
16. KLG   - Kaleng
```

---

## 📋 LANGKAH LENGKAP

### **STEP 1: Backup Database**

```bash
# Via phpMyAdmin atau command line
mysqldump -u root -p eadt_umkm > backup_before_update.sql
```

---

### **STEP 2: Update Data di Local**

```bash
# Update COA dan Satuan sekaligus
php update_coa_dan_satuan_lengkap.php

# Ketik: yes
```

---

### **STEP 3: Update Listener untuk User Baru**

Edit `app/Listeners/CreateDefaultUserData.php`:

```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Database\Seeders\DefaultCoaSeederBaru;
use Database\Seeders\DefaultSatuanSeeder;

class CreateDefaultUserData
{
    public function __construct()
    {
        //
    }

    public function handle(UserRegistered $event): void
    {
        // Ganti dengan seeder baru
        $coaSeeder = new DefaultCoaSeederBaru();
        $coaSeeder->run($event->user->id);
        
        // Satuan tetap sama (sudah diupdate)
        $satuanSeeder = new DefaultSatuanSeeder();
        $satuanSeeder->run($event->user->id);
    }
}
```

---

### **STEP 4: Test di Local**

1. Buka browser: `http://localhost/UMKM_COE/public`
2. Login
3. **Test COA:**
   - Buka: `/master-data/coa`
   - Cek: Ada 50 COA
   - Cek: Kode akun benar (11, 111, 112, 1141, dll)
   - Cek: Tidak ada "🔒 Tidak dapat diubah"
   - Coba: Edit, Hapus, Tambah data

4. **Test Satuan:**
   - Buka: `/master-data/satuan-dashboard`
   - Cek: Ada 16 Satuan
   - Cek: Kode satuan benar (ONS, KG, ML, G, LTR, dll)
   - Cek: Tidak ada "🔒 Tidak dapat diubah"
   - Coba: Edit, Hapus, Tambah data

---

### **STEP 5: Export Database**

```bash
# Perbaiki foreign key dulu
php perbaiki_foreign_key_sebelum_export.php
```

Lalu export dari phpMyAdmin:
- ✅ Pilih "Custom"
- ✅ Centang "Disable foreign key checks"
- ✅ Centang "Add DROP TABLE"
- ✅ Centang "Add IF NOT EXISTS"

---

### **STEP 6: Deploy ke Hosting**

#### **A. Upload Code via Git:**

```bash
git add .
git commit -m "Update COA to Jasuke format and complete Satuan data"
git push origin main
```

Jenkins akan otomatis deploy.

#### **B. Upload Database:**

1. Login ke cPanel/phpMyAdmin hosting
2. **Backup database lama dulu!**
3. Drop database lama
4. Buat database baru
5. Import file SQL dari local

---

### **STEP 7: Test di Hosting**

1. Buka website hosting
2. Login
3. Cek halaman COA dan Satuan
4. Pastikan:
   - ✅ COA: 50 akun
   - ✅ Satuan: 16 satuan
   - ✅ Tidak ada "Tidak dapat diubah"
   - ✅ Bisa edit/hapus/tambah

---

## ⚠️ PENTING

### **Sebelum Update:**
- [ ] Backup database
- [ ] Test di local dulu
- [ ] Pastikan tidak ada transaksi berjalan
- [ ] Catat jumlah data lama

### **Setelah Update:**
- [ ] Cek jumlah COA (harus 50)
- [ ] Cek jumlah Satuan (harus 16)
- [ ] Test edit/hapus/tambah
- [ ] Test buat transaksi baru
- [ ] Test dengan 2 user berbeda

---

## 🔍 TROUBLESHOOTING

### **Problem: Script Error "Access Denied"**

**Solusi:**
1. Cek `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=eadt_umkm
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. Pastikan MySQL running
3. Test: `php artisan tinker`

---

### **Problem: Data Lama Masih Ada**

**Solusi:**
1. Hapus manual via phpMyAdmin:
   ```sql
   DELETE FROM coas;
   DELETE FROM satuans;
   ```

2. Jalankan script lagi

---

### **Problem: User Baru Dapat Data Lama**

**Solusi:**
1. Cek `app/Listeners/CreateDefaultUserData.php`
2. Pastikan pakai `DefaultCoaSeederBaru`
3. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan event:clear
   ```

---

## ✅ CHECKLIST AKHIR

- [ ] Script berhasil dijalankan
- [ ] COA: 50 akun sesuai Jasuke
- [ ] Satuan: 16 satuan lengkap
- [ ] Tidak ada "Tidak dapat diubah"
- [ ] Bisa edit/hapus/tambah data
- [ ] Listener sudah diupdate
- [ ] Test di local berhasil
- [ ] Database di-export
- [ ] Code di-push ke Git
- [ ] Database di-import ke hosting
- [ ] Test di hosting berhasil

---

## 🎉 HASIL AKHIR

**Sebelum:**
```
COA: 11 akun (salah)
Satuan: 4 satuan (kurang)
Status: 🔒 Tidak dapat diubah
```

**Sesudah:**
```
COA: 50 akun (Jasuke) ✅
Satuan: 16 satuan (lengkap) ✅
Status: Bisa diubah ✅
```

---

*Panduan ini dibuat: 3 Mei 2026*

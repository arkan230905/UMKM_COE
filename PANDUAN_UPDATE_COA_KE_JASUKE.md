# 📋 PANDUAN UPDATE COA KE DATA JASUKE

## 🎯 MASALAH

Data COA di hosting berbeda dengan yang Anda inginkan:

**Yang Ada Sekarang:**
- Bank Kecil (102)
- Kas (1101)
- Bank BCA (1102)
- dll...

**Yang Anda Inginkan (Jasuke):**
- Aset (11)
- Kas Bank (111)
- Kas (112)
- Pers. Bahan Baku Jagung (1141)
- Pers. Bahan Pendukung Susu (1151)
- dll...

---

## ✅ SOLUSI

### **File yang Sudah Dibuat:**

1. **`database/seeders/DefaultCoaSeederBaru.php`**
   - Seeder COA baru sesuai data Jasuke (50 akun)

2. **`update_coa_user_ke_jasuke.php`**
   - Script untuk update COA user yang sudah ada

---

## 📋 LANGKAH-LANGKAH

### **CARA 1: Update di Local, Lalu Deploy (RECOMMENDED)**

#### **Step 1: Update Seeder di RegisterController**

Edit file `app/Listeners/CreateDefaultUserData.php`:

```php
public function handle(UserRegistered $event): void
{
    // Ganti seeder lama dengan yang baru
    $coaSeeder = new \Database\Seeders\DefaultCoaSeederBaru();
    $coaSeeder->run($event->user->id);
    
    // Satuan tetap sama
    $satuanSeeder = new DefaultSatuanSeeder();
    $satuanSeeder->run($event->user->id);
}
```

#### **Step 2: Update COA User yang Sudah Ada**

**PENTING:** Backup database dulu!

```bash
# Di local
php update_coa_user_ke_jasuke.php
```

Ketik `yes` untuk konfirmasi.

**Output:**
```
Menghapus semua COA...
  X COA dihapus

Membuat COA baru untuk 1 user...

User: MUHAMMAD ARKAN ABIYYU (ID: 1)
  50 COA dibuat

SELESAI
```

#### **Step 3: Test di Local**

1. Buka browser: `http://localhost/UMKM_COE/public`
2. Login
3. Buka `/master-data/coa`
4. **Cek:**
   - Ada 50 COA
   - Kode akun sesuai (11, 111, 112, 1141, 1151, dll)
   - Nama akun sesuai (Aset, Kas Bank, Pers. Bahan Baku Jagung, dll)
   - Tidak ada "🔒 Tidak dapat diubah"

#### **Step 4: Export Database**

```bash
php perbaiki_foreign_key_sebelum_export.php
```

Lalu export dari phpMyAdmin dengan:
- ✅ Centang "Disable foreign key checks"
- ✅ Centang "Add DROP TABLE"

#### **Step 5: Deploy ke Hosting**

**A. Upload Code:**
```bash
git add .
git commit -m "Update COA seeder to Jasuke format"
git push origin main
```

Jenkins akan otomatis deploy.

**B. Upload Database:**
1. Login ke cPanel/phpMyAdmin hosting
2. Backup database lama
3. Drop database
4. Buat database baru
5. Import file SQL dari local

---

### **CARA 2: Update Langsung di Hosting**

#### **Step 1: Upload Files ke Hosting**

Upload via MobaXterm/FTP:
- `database/seeders/DefaultCoaSeederBaru.php`
- `update_coa_user_ke_jasuke.php`

#### **Step 2: SSH ke Hosting**

```bash
ssh user@your-hosting.com
cd /path/to/your/project
```

#### **Step 3: Backup Database**

```bash
mysqldump -u username -p database_name > backup_before_update.sql
```

#### **Step 4: Run Script**

```bash
php update_coa_user_ke_jasuke.php
```

Ketik `yes` untuk konfirmasi.

#### **Step 5: Update Listener**

Edit `app/Listeners/CreateDefaultUserData.php` di hosting:

```php
use Database\Seeders\DefaultCoaSeederBaru;

public function handle(UserRegistered $event): void
{
    $coaSeeder = new DefaultCoaSeederBaru();
    $coaSeeder->run($event->user->id);
    
    $satuanSeeder = new DefaultSatuanSeeder();
    $satuanSeeder->run($event->user->id);
}
```

#### **Step 6: Clear Cache**

```bash
php artisan cache:clear
php artisan config:clear
php artisan event:clear
```

---

## 📊 DATA COA BARU (50 AKUN)

### **Aset (23 akun):**
- 11: Aset
- 111: Kas Bank
- 112: Kas
- 113: Kas Kecil
- 114: Pers. Bahan Baku
- 1141: Pers. Bahan Baku Jagung
- 115: Pers. Bahan Pendukung
- 1151: Pers. Bahan Pendukung Susu
- 1152: Pers. Bahan Pendukung Keju
- 1153: Pers. Bahan Pendukung Kemasan (Cup)
- 116: Pers. Barang Jadi
- 1161: Pers. Barang Jadi Jasuke
- 117: Pers. Barang dalam Proses
- 1171: Pers. Barang Dalam Proses - BBB
- 1172: Pers. Barang Dalam Proses - BTKL
- 1173: Pers. Barang Dalam Proses - BOP
- 118: Piutang
- 119: Peralatan
- 120: Akumulasi Penyusutan Peralatan
- 125: Mesin
- 126: Akumulasi Penyusutan Mesin
- 127: PPN Masukkan

### **Kewajiban (4 akun):**
- 21: Hutang
- 210: Hutang Usaha
- 211: Hutang Gaji
- 212: PPN Keluaran

### **Modal (3 akun):**
- 31: Modal
- 310: Modal Usaha
- 311: Prive

### **Pendapatan (3 akun):**
- 41: Penjualan
- 410: Penjualan - Jasuke
- 42: Retur Penjualan

### **Biaya (17 akun):**
- 51: BBB - Biaya Bahan Baku
- 510: BBB - Jagung
- 513: Beban Tunjangan
- 514: Beban Asuransi
- 515: Beban Bonus
- 516: Potongan Gaji
- 52: BTKL
- 520: BTKL - Produksi Jasuke
- 53: BOP
- 530: BOP - Susu
- 531: BOP - Keju
- 532: BOP - Kemasan
- 54: Beban Sewa
- 55: BOP Lain
- 550: BOP - Listrik
- 551: BOP - Air
- 552: BOP - Gas
- 553: BOP - Penyusutan Peralatan

**Total: 50 akun**

---

## ⚠️ PENTING

### **Sebelum Update:**
- [ ] Backup database
- [ ] Test di local dulu
- [ ] Pastikan tidak ada transaksi yang sedang berjalan

### **Setelah Update:**
- [ ] Cek jumlah COA (harus 50)
- [ ] Cek kode akun sesuai
- [ ] Cek nama akun sesuai
- [ ] Tidak ada "Tidak dapat diubah"
- [ ] Bisa edit/hapus/tambah data
- [ ] Test buat transaksi baru

---

## 🔍 TROUBLESHOOTING

### **Problem: Script Error "Access Denied"**

**Penyebab:** Koneksi database gagal

**Solusi:**
1. Cek file `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=eadt_umkm
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. Pastikan MySQL running
3. Test koneksi: `php artisan tinker`

---

### **Problem: COA Lama Masih Ada**

**Penyebab:** Script tidak menghapus COA lama

**Solusi:**
1. Hapus manual via phpMyAdmin:
   ```sql
   DELETE FROM coas;
   ```

2. Jalankan script lagi

---

### **Problem: User Baru Dapat COA Lama**

**Penyebab:** Listener masih pakai seeder lama

**Solusi:**
1. Edit `app/Listeners/CreateDefaultUserData.php`
2. Ganti `DefaultCoaSeeder` dengan `DefaultCoaSeederBaru`
3. Clear cache: `php artisan cache:clear`

---

## 📞 BANTUAN

Jika masih ada masalah:
1. Screenshot halaman COA
2. Screenshot error (jika ada)
3. Kirim ke developer

---

*Panduan ini dibuat: 3 Mei 2026*

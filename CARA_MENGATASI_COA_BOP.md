# Cara Mengatasi COA BOP yang Masih "210 - Hutang Usaha"

## Status: ✅ Data Sudah Diupdate

### ✅ Yang Sudah Dilakukan:
1. **Controller diperbaiki** - Menggunakan `coa_id` dari database
2. **Data BOP diupdate** - Semua komponen_bop sudah memiliki `coa_id`

---

## 🔧 Cara Mengatasi Tampilan Masih "210 - Hutang Usaha"

### **Langkah 1: Hard Refresh Browser**
```
Tekan: Ctrl + Shift + R
Atau: Ctrl + F5
```

### **Langkah 2: Clear Cache Laravel**
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### **Langkah 3: Buka di Incognito Window**
```
Chrome: Ctrl + Shift + N
Firefox: Ctrl + Shift + P
```

---

## 📊 Verifikasi Data di Database

### **Cek komponen_bop sudah memiliki coa_id:**
```bash
php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); \$bop = DB::table('bop_proses')->first(); \$komponen = json_decode(\$bop->komponen_bop, true); print_r(\$komponen[0]);"
```

**Output yang Benar:**
```
Array
(
    [component] => Listrik Mesin
    [rate_per_hour] => 208
    [rate_per_produk] => 208
    [description] => Biaya listrik mesin
    [coa_id] => 72  ← HARUS ADA
)
```

---

## ⚠️ Catatan Penting tentang COA

### **COA di Database Tidak Standar:**
Nama COA di database tidak sesuai dengan standar akuntansi:

| Kode | Seharusnya | Di Database |
|------|------------|-------------|
| 532 | BOP - Kemasan | BOP-Minyak Goreng |
| 533 | Biaya Penyusutan Mesin | BOP-Tepung Terigu |
| 534 | Biaya Maintenance | BOP-Tepung Maizena |
| 536 | Biaya Air & Kebersihan | BOP- Bubuk Kaldu |
| 551 | BOP - Air | BOP TL - Sewa Tempat |
| 552 | BOP - Gas | BOP TL - Biaya Penyusutan Gedung |

**Solusi:**
1. **Opsi 1 (Recommended):** Perbaiki nama COA di database agar sesuai standar
2. **Opsi 2:** Biarkan seperti ini, yang penting kode COA sudah benar

---

## 🔍 Troubleshooting

### **Masalah: Setelah refresh masih "210 - Hutang Usaha"**

**Penyebab Kemungkinan:**
1. Cache browser belum clear
2. Cache Laravel belum clear
3. Data `coa_id` belum tersimpan di database

**Solusi:**

#### **1. Cek data di database:**
```bash
php check_bop_coa_id.php
```

Buat file `check_bop_coa_id.php`:
```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$bopProses = DB::table('bop_proses')->get();

foreach ($bopProses as $bop) {
    echo "BOP: {$bop->nama_bop_proses}\n";
    $komponen = json_decode($bop->komponen_bop, true);
    
    foreach ($komponen as $k) {
        $coaId = $k['coa_id'] ?? 'TIDAK ADA';
        echo "  - {$k['component']}: coa_id = {$coaId}\n";
        
        if ($coaId !== 'TIDAK ADA') {
            $coa = DB::table('coas')->find($coaId);
            if ($coa) {
                echo "    COA: {$coa->kode_akun} - {$coa->nama_akun}\n";
            }
        }
    }
    echo "\n";
}
```

#### **2. Jika `coa_id` tidak ada, jalankan update lagi:**
```bash
php update_bop_coa_id.php
```

#### **3. Clear semua cache:**
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

#### **4. Hard refresh browser:**
```
Ctrl + Shift + R
```

---

## ✅ Hasil yang Diharapkan

Setelah perbaikan, tampilan BOP di halaman create produksi seharusnya:

### **Sebelum:**
```
Tepung Terigu: COA: 210 - Hutang Usaha (BOP Lain-lain)  ❌
Lada: COA: 210 - Hutang Usaha (BOP Lain-lain)  ❌
BTKTL: COA: 210 - Hutang Usaha (BOP Lain-lain)  ❌
```

### **Sesudah:**
```
Tepung Terigu: COA: 115 - Pers. Bahan Pendukung  ✅
Lada: COA: 115 - Pers. Bahan Pendukung  ✅
BTKTL: COA: 211 - Hutang Gaji  ✅
Listrik: COA: 550 - BOP TL - Biaya Listrik  ✅
Gas: COA: 552 - BOP TL - Biaya Penyusutan Gedung  ✅
Kemasan: COA: 532 - BOP-Minyak Goreng  ✅
```

---

## 📝 Catatan Tambahan

### **Komponen yang Seharusnya Tidak Ada di BOP:**
- **BTKTL** - Seharusnya tidak ada di BOP, tapi jika ada akan menggunakan COA 211 (Hutang Gaji)
- **Bahan Pendukung** (Tepung, Bumbu, dll) - Seharusnya di BBB, tapi jika ada di BOP akan menggunakan COA 115 (Pers. Bahan Pendukung)

### **Rekomendasi:**
1. Pindahkan BTKTL dari BOP ke BTKL yang sebenarnya
2. Pindahkan Bahan Pendukung dari BOP ke BBB
3. BOP seharusnya hanya berisi: Listrik, Gas, Air, Penyusutan, Maintenance, Kebersihan, Kemasan

---

**Status:** ✅ Data sudah diupdate, tinggal refresh browser
**Tanggal:** 25 Mei 2026

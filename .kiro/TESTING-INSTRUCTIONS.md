# Testing Instructions - Penggajian Auto-Tarif Feature

## Status
✅ **Code sudah di-push ke GitHub (branch: chindii2)**
✅ **Cache sudah di-clear di local**
✅ **Server sudah di-restart**

## Untuk Testing di Web

### Step 1: Pull Latest Code
```bash
git pull origin chindii2
```

### Step 2: Clear Cache (Jalankan di Terminal)
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Step 3: Clear Browser Cache
- **Chrome/Edge**: Ctrl+Shift+Delete → Clear All → Clear
- **Firefox**: Ctrl+Shift+Delete → semuanya → Clear Now
- **Safari**: Menu → Develop → Empty Web Storage

Atau gunakan **Private/Incognito Window** untuk fresh start

### Step 4: Restart Application
Jika menggunakan server production:
- Restart PHP application / PHP-FPM
- Clear opcache jika ada

Jika development:
```bash
php artisan serve
```

### Step 5: Test Form
1. Go to: `/transaksi/penggajian/create-produk`
2. Pilih pegawai yang punya kualifikasi dengan tarif > 0
3. Verifikasi:
   - ✅ Field "Tarif / Produk" auto-fill dengan nilai dari kualifikasi
   - ✅ Field "Tunjangan Jabatan" auto-fill
   - ✅ Field "Tunjangan Transport" auto-fill
   - ✅ Field "Tunjangan Konsumsi" auto-fill
   - ✅ Field "Asuransi BPJS" auto-fill

## API Endpoint untuk Debug

**URL**: `http://domain.com/api/pegawai/{pegawai_id}/data`

**Method**: GET

**Response JSON Sample**:
```json
{
  "tarif": 50000,
  "gaji_pokok": 3000000,
  "tunjangan_jabatan": 500000,
  "tunjangan_transport": 150000,
  "tunjangan_konsumsi": 375000,
  "total_tunjangan": 1025000,
  "asuransi": 500000,
  "nama": "Ahmad Rafi",
  "jabatan_nama": "Quality Control",
  "kategori": "BTKL"
}
```

## Jika Masih Tidak Berfungsi

### Check 1: Lihat Browser Console
- Buka DevTools (F12)
- Klik tab **Console**
- Pilih pegawai dan lihat error messages

### Check 2: Cek di Network Tab
- DevTools → Network tab
- Pilih pegawai
- Cari request ke `/api/pegawai/*/data`
- Lihat response status (should be 200)
- Lihat response JSON

### Check 3: Cek Database
Verifikasi pegawai memiliki relasi kualifikasi:
```sql
SELECT p.id, p.nama, p.jabatan_id, j.nama as kualifikasi, j.tarif_produk 
FROM pegawais p 
LEFT JOIN jabatans j ON p.jabatan_id = j.id 
WHERE p.user_id = 3 
LIMIT 10;
```

### Check 4: Test API Langsung
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     "http://localhost:8000/api/pegawai/1/data"
```

## File yang Berubah

```
app/Http/Controllers/PenggajianController.php
- Method: resolvePegawaiKualifikasi() (baru, menggantikan resolvePegawaiJabatan)
- Method: getEmployeeData() (sudah di-fix)
- Method: resolveProdukPayrollDetail() (sudah di-fix)

resources/views/transaksi/penggajian/create-produk.blade.php
- JavaScript updateTarif() function (call API)
- JavaScript updateTotalProduk() function (fetch produksi)

database/migrations/2026_06_10_add_beban_upah_gaji_to_all_tenants_coa.php
- COA accounts sudah ditambahkan
```

## Git Commit
```
789be6c5 - fix: Perbaiki logika penggajian - gunakan model Jabatan untuk kualifikasi
```

## Catatan Penting
1. Pastikan user sudah login (ada session auth)
2. Pastikan pegawai memiliki `jabatan_id` yang valid
3. Pastikan kualifikasi memiliki `tarif_produk` > 0
4. API endpoint protected dengan middleware auth

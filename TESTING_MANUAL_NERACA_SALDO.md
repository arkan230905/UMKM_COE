# Testing Manual - Neraca Saldo Berbasis Buku Besar

## Persiapan Testing

### 1. Pastikan Routes Terdaftar
```bash
php artisan route:list | findstr neraca-saldo-new
```

Harus menampilkan:
- `akuntansi/neraca-saldo-new` (GET)
- `akuntansi/neraca-saldo-new/api` (GET) 
- `akuntansi/neraca-saldo-new/pdf` (GET)

### 2. Pastikan User dengan Role Admin/Owner
Login dengan user yang memiliki role `admin` atau `owner`.

## Skenario Testing

### Skenario 1: Akses Halaman Neraca Saldo Baru

**URL:** `/akuntansi/neraca-saldo-new`

**Expected Result:**
- Halaman terbuka tanpa error
- Menampilkan form filter bulan/tahun
- Menampilkan tabel neraca saldo (mungkin kosong jika belum ada data)
- Ada tombol "Tampilkan", "Refresh", dan "Cetak PDF"

### Skenario 2: Test dengan Data COA Minimal

**Langkah:**
1. Pastikan ada minimal 2 COA di database:
   - 1 akun Aset (kode 1xxx)
   - 1 akun Modal (kode 3xxx)

2. Beri saldo awal pada COA tersebut

**SQL untuk insert test data:**
```sql
-- Insert COA Kas
INSERT INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
VALUES ('1101', 'Kas', 'ASET', 10000000, 1, NOW(), NOW());

-- Insert COA Modal
INSERT INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
VALUES ('3101', 'Modal Pemilik', 'MODAL', 10000000, 1, NOW(), NOW());
```

**Expected Result:**
- Tabel menampilkan 2 baris akun
- Kas: Rp 10.000.000 di kolom Debit
- Modal: Rp 10.000.000 di kolom Kredit
- Total Debit = Total Kredit = Rp 10.000.000
- Status: "BALANCED"

### Skenario 3: Test dengan Transaksi Jurnal

**Langkah:**
1. Buat journal entry dengan journal lines
2. Refresh halaman neraca saldo

**SQL untuk insert transaksi:**
```sql
-- Insert Journal Entry
INSERT INTO journal_entries (tanggal, ref_type, memo, created_at, updated_at) 
VALUES ('2026-04-15', 'manual', 'Test transaksi', NOW(), NOW());

-- Ambil ID journal entry yang baru dibuat
SET @journal_id = LAST_INSERT_ID();

-- Insert Journal Lines (Kas bertambah 5jt, Modal bertambah 5jt)
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, created_at, updated_at) 
VALUES 
(@journal_id, (SELECT id FROM coas WHERE kode_akun = '1101'), 5000000, 0, NOW(), NOW()),
(@journal_id, (SELECT id FROM coas WHERE kode_akun = '3101'), 0, 5000000, NOW(), NOW());
```

**Expected Result:**
- Kas: Rp 15.000.000 di kolom Debit (10jt + 5jt)
- Modal: Rp 15.000.000 di kolom Kredit (10jt + 5jt)
- Total tetap seimbang
- Status: "BALANCED"

### Skenario 4: Test Filter Periode

**Langkah:**
1. Ubah bulan/tahun di form filter
2. Klik "Tampilkan"

**Expected Result:**
- Data berubah sesuai periode yang dipilih
- URL berubah dengan parameter bulan/tahun
- Tabel di-refresh tanpa reload halaman (AJAX)

### Skenario 5: Test Export PDF

**Langkah:**
1. Klik tombol "Cetak PDF"

**Expected Result:**
- File PDF ter-download
- Nama file: `neraca-saldo-YYYY-MM.pdf`
- Isi PDF sesuai dengan data di halaman

### Skenario 6: Test API Endpoint

**URL:** `/akuntansi/neraca-saldo-new/api?bulan=04&tahun=2026`

**Expected Result:**
```json
{
    "success": true,
    "data": {
        "accounts": [
            {
                "kode_akun": "1101",
                "nama_akun": "Kas",
                "debit": 15000000,
                "kredit": 0
            },
            {
                "kode_akun": "3101", 
                "nama_akun": "Modal Pemilik",
                "debit": 0,
                "kredit": 15000000
            }
        ],
        "total_debit": 15000000,
        "total_kredit": 15000000,
        "is_balanced": true
    },
    "message": "Data berhasil diambil"
}
```

### Skenario 7: Test Neraca Tidak Seimbang

**Langkah:**
1. Buat journal entry yang tidak seimbang (hanya debit tanpa kredit)

**SQL:**
```sql
INSERT INTO journal_entries (tanggal, ref_type, memo, created_at, updated_at) 
VALUES ('2026-04-20', 'manual', 'Transaksi tidak seimbang', NOW(), NOW());

SET @journal_id = LAST_INSERT_ID();

-- Hanya debit, tanpa kredit
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, created_at, updated_at) 
VALUES (@journal_id, (SELECT id FROM coas WHERE kode_akun = '1101'), 1000000, 0, NOW(), NOW());
```

**Expected Result:**
- Total Debit ≠ Total Kredit
- Status: "TIDAK SEIMBANG" (warna merah)
- Menampilkan selisih
- Alert warning muncul di bawah tabel

### Skenario 8: Test Validasi Input

**Langkah:**
1. Coba akses dengan parameter invalid:
   - `/akuntansi/neraca-saldo-new?bulan=13`
   - `/akuntansi/neraca-saldo-new?tahun=2050`

**Expected Result:**
- Redirect kembali dengan error message
- Form validation error ditampilkan

### Skenario 9: Test Authorization

**Langkah:**
1. Login dengan user role selain admin/owner
2. Akses `/akuntansi/neraca-saldo-new`

**Expected Result:**
- HTTP 403 Forbidden
- Tidak bisa mengakses halaman

### Skenario 10: Test Performance dengan Data Banyak

**Langkah:**
1. Insert banyak COA dan transaksi
2. Akses neraca saldo

**SQL untuk generate data:**
```sql
-- Insert 100 COA
INSERT INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at)
SELECT 
    CONCAT('11', LPAD(n, 2, '0')) as kode_akun,
    CONCAT('Akun Test ', n) as nama_akun,
    'ASET' as tipe_akun,
    n * 100000 as saldo_awal,
    1 as user_id,
    NOW() as created_at,
    NOW() as updated_at
FROM (
    SELECT @row := @row + 1 as n
    FROM information_schema.columns c1, information_schema.columns c2, (SELECT @row := 0) r
    LIMIT 100
) numbers;
```

**Expected Result:**
- Halaman tetap responsive (< 3 detik)
- Data ditampilkan dengan benar
- Pagination jika diperlukan

## Checklist Testing

- [ ] Routes terdaftar dengan benar
- [ ] Halaman terbuka tanpa error
- [ ] Data COA ditampilkan dengan benar
- [ ] Perhitungan saldo akhir akurat
- [ ] Mapping ke kolom debit/kredit benar
- [ ] Balance check berfungsi
- [ ] Filter periode berfungsi
- [ ] AJAX refresh berfungsi
- [ ] Export PDF berfungsi
- [ ] API endpoint berfungsi
- [ ] Validasi input berfungsi
- [ ] Authorization berfungsi
- [ ] Performance acceptable
- [ ] Error handling berfungsi
- [ ] Logging audit trail berfungsi

## Troubleshooting

### Error: "Class TrialBalanceService not found"
**Solusi:** Jalankan `composer dump-autoload`

### Error: "Route not found"
**Solusi:** Clear route cache dengan `php artisan route:clear`

### Error: "View not found"
**Solusi:** Clear view cache dengan `php artisan view:clear`

### Error: "PDF generation failed"
**Solusi:** 
1. Pastikan package PDF sudah terinstall
2. Check log di `storage/logs/laravel.log`

### Data tidak muncul
**Solusi:**
1. Check apakah ada COA di database
2. Check apakah user_id sesuai (global scope)
3. Check periode yang dipilih

### Neraca tidak seimbang padahal seharusnya seimbang
**Solusi:**
1. Check journal_lines apakah ada yang tidak seimbang
2. Check perhitungan saldo akhir
3. Check mapping normal balance

## Monitoring & Logging

Implementasi ini mencatat aktivitas di log untuk audit trail:

```bash
# Check log
tail -f storage/logs/laravel.log | grep "Neraca Saldo"
```

Log yang dicatat:
- Akses halaman neraca saldo
- Download PDF
- Error saat perhitungan
- Performance metrics
# Perbaikan Error Pelunasan Utang - COA 1130

## Masalah
Saat melakukan pelunasan utang, sistem menampilkan error:
```
COA dengan kode 1130 tidak ditemukan. Silakan buat COA terlebih dahulu di master data.
```

## Penyebab
COA 1130 (PPN Masukan) tidak ada di database, padahal sistem membutuhkannya untuk mencatat PPN dari pembelian saat pelunasan utang.

## Solusi yang Diterapkan

### 1. Migration untuk Menambahkan COA 1130
File: `database/migrations/2026_04_20_add_coa_1130_ppn_masukan.php`

Migration ini menambahkan COA 1130 dengan detail:
- **Kode Akun**: 1130
- **Nama Akun**: PPN Masukan
- **Tipe Akun**: Asset
- **Kategori**: Asset
- **Saldo Normal**: Debit
- **Keterangan**: PPN Masukan dari pembelian

### 2. Update UpdatedCoaSeeder
File: `database/seeders/UpdatedCoaSeeder.php`

Menambahkan COA 1130 ke dalam seeder sehingga saat seeder dijalankan, COA 1130 akan otomatis dibuat.

Total accounts sekarang: **90** (sebelumnya 89)

## Struktur COA PPN

| Kode | Nama | Tipe | Fungsi |
|------|------|------|--------|
| 127 | PPN Masukan | Asset | PPN dari pembelian (legacy) |
| **1130** | **PPN Masukan** | **Asset** | **PPN dari pembelian (current)** |

## Penggunaan COA 1130

COA 1130 digunakan dalam beberapa proses:

### 1. Pembelian dengan PPN
Saat membuat pembelian dengan PPN, sistem akan membuat jurnal:
```
DEBIT:  1130 - PPN Masukan        Rp XXX.XXX
DEBIT:  114x - Persediaan Bahan   Rp XXX.XXX
KREDIT: 210 - Hutang Usaha                    Rp XXX.XXX
```

### 2. Pelunasan Utang
Saat melunasi utang, sistem akan membuat jurnal:
```
DEBIT:  210 - Hutang Usaha        Rp XXX.XXX
KREDIT: 111 - Kas Bank                       Rp XXX.XXX
```

## Verifikasi

Untuk memverifikasi COA 1130 sudah ada:

```bash
# Jalankan migration
php artisan migrate

# Atau jalankan seeder
php artisan db:seed --class=UpdatedCoaSeeder
```

Kemudian cek di Master Data → COA, cari kode 1130.

## Catatan Penting

1. **Duplikasi COA**: Ada dua COA untuk PPN Masukan (127 dan 1130). Ini adalah legacy dari sistem lama. Kedua-duanya berfungsi, tapi sistem sekarang menggunakan 1130.

2. **Backward Compatibility**: Jika ada data lama yang menggunakan COA 127, sistem masih akan berfungsi normal.

3. **Update Seeder**: Setelah perbaikan ini, pastikan untuk menjalankan:
   ```bash
   php artisan coa:update-seeder --force
   ```
   Agar seeder selalu up-to-date dengan COA terbaru.

## Testing

Untuk test pelunasan utang setelah perbaikan:

1. Buat pembelian dengan PPN
2. Buka halaman Pelunasan Utang
3. Pilih pembelian yang akan dilunasi
4. Klik "Simpan"
5. Sistem seharusnya berhasil membuat jurnal tanpa error

---

**Last Updated:** 2026-04-20  
**Status:** ✅ FIXED  
**Tested:** Yes

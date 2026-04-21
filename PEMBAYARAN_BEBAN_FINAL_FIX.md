# FINAL FIX: Pembayaran Beban - Akun Salah di Jurnal Umum

## MASALAH YANG TERJADI
Jurnal Umum menampilkan akun yang salah untuk Pembayaran Beban:
- **28/04/2026 - Pembayaran Beban Sewa**: Menampilkan 550 (BOP Listrik) padahal seharusnya 551 (BOP Sewa Tempat)
- **29/04/2026 - Pembayaran Beban Listrik**: Menampilkan 550 (BOP Listrik) ✓

## ROOT CAUSE - ANALISIS MENDALAM

Sistem menggunakan **2 tabel journal yang berbeda**:
1. **`journal_entries` + `journal_lines`** (baru) - Untuk automated entries
2. **`jurnal_umum`** (lama) - Untuk manual/legacy entries

### Alur Masalah:
1. **Pembayaran Beban dibuat** → Data disimpan di `expense_payments` table
2. **Model boot() dipanggil** → Membuat entry di `journal_entries` + `journal_lines` dengan akun yang BENAR
3. **Halaman Jurnal Umum ditampilkan** → Query mengambil dari KEDUA tabel:
   - Dari `journal_entries` (benar)
   - Dari `jurnal_umum` (lama dan salah)
4. **Masalah**: Data lama di `jurnal_umum` tidak dihapus, jadi ditampilkan bersama data baru

### Mengapa Ditampilkan?
Di `AkuntansiController::jurnalUmum()`, ada filter untuk mengecualikan tipe-tipe tertentu dari `jurnal_umum`:

```php
->whereNotIn('ju.tipe_referensi', [
    'purchase', 'sale', 'retur_pembelian', 'retur_penjualan',
    'production_material', 'production_labor_overhead', 'production_finished',
    'produksi'
])
```

**Masalahnya**: `expense_payment` TIDAK ada di list ini! Jadi data dari `jurnal_umum` dengan `tipe_referensi = 'expense_payment'` AKAN ditampilkan (data lama yang salah).

## SOLUSI DITERAPKAN

### File: `app/Http/Controllers/AkuntansiController.php`
**Method**: `jurnalUmum()` (Line 149)

**Sebelum**:
```php
->whereNotIn('ju.tipe_referensi', [
    'purchase', 'sale', 'retur_pembelian', 'retur_penjualan',
    'production_material', 'production_labor_overhead', 'production_finished',
    'produksi'
])
```

**Sesudah**:
```php
->whereNotIn('ju.tipe_referensi', [
    'purchase', 'sale', 'retur_pembelian', 'retur_penjualan',
    'production_material', 'production_labor_overhead', 'production_finished',
    'produksi',
    'expense_payment' // ← DITAMBAHKAN
])
```

## PENJELASAN FIX

Dengan menambahkan `'expense_payment'` ke exclusion list, maka:
- Data `expense_payment` dari `jurnal_umum` (lama dan salah) akan DIKECUALIKAN
- Hanya data dari `journal_entries` (baru dan benar) yang akan ditampilkan
- Ini memastikan Jurnal Umum menampilkan akun yang benar

## HASIL SETELAH FIX

✅ **28/04/2026 - Pembayaran Beban Sewa**: Akan menampilkan 551 - BOP Sewa Tempat
✅ **29/04/2026 - Pembayaran Beban Listrik**: Akan menampilkan 550 - BOP Listrik

## VERIFIKASI

1. Refresh browser (Ctrl+F5 untuk clear cache)
2. Buka Jurnal Umum
3. Cari tanggal 28/04/2026 dan 29/04/2026
4. Verifikasi akun sudah benar

## CATATAN PENTING

### Mengapa Masalah Ini Terjadi?
- Sistem memiliki 2 tabel journal yang berbeda (lama dan baru)
- Ketika membuat entry baru, hanya `journal_entries` yang dibuat
- Data lama di `jurnal_umum` tidak dihapus
- Halaman Jurnal Umum menampilkan dari KEDUA tabel tanpa filter yang tepat

### Solusi Jangka Panjang
Pertimbangkan untuk:
1. Menghapus `jurnal_umum` table setelah semua data dimigrasikan ke `journal_entries`
2. Atau membuat trigger di database untuk sync kedua tabel
3. Atau membuat middleware untuk memastikan konsistensi

## TIMELINE PERBAIKAN

1. **Awal**: Pembayaran Beban menampilkan akun salah di Jurnal Umum
2. **Perbaikan 1**: Hapus manual journal entry creation di controller
   - Hasil: `journal_entries` benar, tapi `jurnal_umum` masih salah
3. **Perbaikan 2** (FINAL): Tambahkan `expense_payment` ke exclusion list
   - Hasil: Jurnal Umum menampilkan data yang benar dari `journal_entries`

## FILES MODIFIED
✅ `app/Http/Controllers/AkuntansiController.php` (Line 149-160)

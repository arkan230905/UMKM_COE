# MASALAH: Pembayaran Beban Masih Menampilkan Akun Salah di Jurnal Umum

## SITUASI SAAT INI
Meskipun sudah memperbaiki controller, Jurnal Umum masih menampilkan akun yang salah:

**28/04/2026 - Pembayaran Beban Sewa:**
- Seharusnya: 551 - BOP Sewa Tempat
- Tapi tampil: 550 - BOP Listrik ✗

**29/04/2026 - Pembayaran Beban Listrik:**
- Seharusnya: 550 - BOP Listrik
- Tampil: 550 - BOP Listrik ✓

## ANALISIS MASALAH

### Penyebab Utama
Ada **2 sistem journal yang berbeda**:
1. **`journal_entries` + `journal_lines`** - Sistem baru (untuk automated entries)
2. **`jurnal_umum`** - Sistem lama (untuk manual entries)

Ketika memperbaiki controller, hanya `journal_entries` yang dibuat dengan benar. Tapi `jurnal_umum` masih berisi data lama yang salah.

### Mengapa Masih Salah?
1. Data lama di `jurnal_umum` tidak dihapus
2. Model boot() method membuat entry di `journal_entries` (benar)
3. Tapi `jurnal_umum` masih menampilkan data lama (salah)
4. Halaman Jurnal Umum menampilkan data dari `jurnal_umum` table

## SOLUSI

### Langkah 1: Hapus Data Lama
Jalankan script untuk menghapus semua pembayaran beban entries dari `jurnal_umum` untuk tanggal 28-29 April:

```php
DELETE FROM jurnal_umum
WHERE tanggal IN ('2026-04-28', '2026-04-29')
AND keterangan LIKE '%Pembayaran Beban%'
```

### Langkah 2: Buat Ulang dengan Data Benar
Jalankan script: `http://127.0.0.1:8000/fix-jurnal-umum-pembayaran-beban.php`

Script ini akan:
1. Menghapus semua pembayaran beban entries dari `jurnal_umum`
2. Membaca data yang benar dari `expense_payments` table
3. Membuat ulang entries di `jurnal_umum` dengan akun yang benar

## IMPLEMENTASI

### File yang Sudah Diperbaiki
✅ `app/Http/Controllers/ExpensePaymentController.php`
- Hapus manual journal entry creation
- Biarkan model boot() yang menangani

### File yang Perlu Dijalankan
1. `http://127.0.0.1:8000/fix-jurnal-umum-pembayaran-beban.php`

## VERIFIKASI SETELAH FIX

1. Jalankan script fix
2. Buka Jurnal Umum
3. Cari tanggal 28/04/2026 dan 29/04/2026
4. Verifikasi:
   - 28/04/2026: 551 - BOP Sewa Tempat ✓
   - 29/04/2026: 550 - BOP Listrik ✓

## CATATAN PENTING

Masalah ini terjadi karena sistem menggunakan 2 tabel journal yang berbeda:
- `journal_entries` (baru) - Dibuat oleh model boot()
- `jurnal_umum` (lama) - Harus diupdate manual

Untuk mencegah masalah ini di masa depan, pertimbangkan untuk:
1. Menghapus `jurnal_umum` table setelah semua data dimigrasikan ke `journal_entries`
2. Atau membuat trigger di database untuk sync kedua tabel
3. Atau membuat middleware untuk memastikan konsistensi

## TIMELINE

- **Sebelum fix**: Jurnal Umum menampilkan akun salah
- **Setelah fix controller**: `journal_entries` benar, tapi `jurnal_umum` masih salah
- **Setelah fix jurnal_umum**: Kedua tabel akan menampilkan akun yang benar

# ROOT CAUSE ANALYSIS: Pembayaran Beban Masih Salah

## MASALAH
Jurnal Umum masih menampilkan akun 550 (BOP Listrik) untuk Pembayaran Beban Sewa (28/04/2026), padahal seharusnya 551 (BOP Sewa Tempat).

## INVESTIGASI

### Kemungkinan 1: Data di `journal_entries` Tidak Ada
Jika `journal_entries` tidak ada untuk expense_payment ID 2 & 3, maka:
- Model boot() method tidak dipanggil
- Atau boot() method tidak membuat entry dengan benar
- Halaman Jurnal Umum hanya menampilkan data dari `jurnal_umum` (lama dan salah)

**Solusi**: Jalankan `http://127.0.0.1:8000/check-journal-entries-expense.php` untuk verifikasi

### Kemungkinan 2: Data di `journal_entries` Ada Tapi Salah
Jika `journal_entries` ada tapi dengan COA yang salah:
- Boot() method membuat entry dengan akun yang salah
- Atau ada bug di JournalService::createJournalFromExpensePayment()

**Solusi**: Cek data di `journal_entries` dan perbaiki

### Kemungkinan 3: Query di Halaman Jurnal Umum Masih Salah
Jika data di `journal_entries` benar tapi halaman masih menampilkan yang salah:
- Query tidak mengambil dari `journal_entries`
- Atau masih mengambil dari `jurnal_umum` yang lama

**Solusi**: Cek query di AkuntansiController::jurnalUmum()

## LANGKAH DEBUGGING

### 1. Cek apakah `journal_entries` ada
```bash
Jalankan: http://127.0.0.1:8000/check-journal-entries-expense.php
```

Jika hasilnya:
- ✓ Ada entries → Lanjut ke step 2
- ✗ Tidak ada entries → Jalankan step 3

### 2. Cek apakah data di `journal_entries` benar
Lihat output dari step 1, cek apakah COA sudah benar (551 untuk Sewa, 550 untuk Listrik)

Jika:
- ✓ Benar → Masalah di query halaman
- ✗ Salah → Masalah di JournalService

### 3. Buat `journal_entries` dari `expense_payments`
```bash
Jalankan: http://127.0.0.1:8000/create-journal-entries-from-expense.php
```

Script ini akan:
1. Menghapus journal_entries lama
2. Membuat journal_entries baru dengan data yang benar dari expense_payments

## SOLUSI CEPAT

Jika masalahnya adalah `journal_entries` tidak ada atau salah:

1. Jalankan: `http://127.0.0.1:8000/create-journal-entries-from-expense.php`
2. Refresh halaman Jurnal Umum
3. Verifikasi akun sudah benar

## CATATAN PENTING

Masalah ini terjadi karena:
1. **Dual Journal System**: Ada 2 tabel journal yang berbeda
2. **Inconsistent Data**: Data di `journal_entries` dan `jurnal_umum` tidak sinkron
3. **Query Logic**: Halaman Jurnal Umum mengambil dari KEDUA tabel

Untuk mencegah masalah ini di masa depan:
- Pastikan model boot() method selalu dipanggil saat membuat expense_payment
- Atau hapus `jurnal_umum` table setelah semua data dimigrasikan
- Atau buat trigger untuk sync kedua tabel

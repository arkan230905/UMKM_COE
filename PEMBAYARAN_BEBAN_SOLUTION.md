# SOLUSI: Pembayaran Beban - Akun Salah di Jurnal Umum

## MASALAH
Jurnal Umum menampilkan akun yang salah:
- **28/04/2026 - Pembayaran Beban Sewa**: Menampilkan 550 (BOP Listrik) ✗ seharusnya 551 (BOP Sewa Tempat)
- **29/04/2026 - Pembayaran Beban Listrik**: Menampilkan 550 (BOP Listrik) ✓

## PENYEBAB
Data di tabel `jurnal_umum` masih lama dan salah. Sistem memiliki 2 tabel journal:
- `journal_entries` (baru) - Mungkin benar atau tidak ada
- `jurnal_umum` (lama) - Masih berisi data lama yang salah

## SOLUSI LANGSUNG

### Langkah 1: Diagnosa (Opsional)
Jalankan script untuk melihat apa yang ada di database:
```
http://127.0.0.1:8000/full-diagnosis.php
```

### Langkah 2: Perbaiki (WAJIB)
Jalankan script untuk memperbaiki data di `jurnal_umum`:
```
http://127.0.0.1:8000/fix-jurnal-umum-direct.php
```

Script ini akan:
1. ✓ Menghapus semua data lama dari `jurnal_umum` untuk 28-29 April
2. ✓ Membuat entries baru dengan COA yang BENAR:
   - 28/04/2026: 551 - BOP Sewa Tempat (Rp 1.500.000)
   - 29/04/2026: 550 - BOP Listrik (Rp 2.030.000)
3. ✓ Menampilkan verifikasi hasil

### Langkah 3: Verifikasi
1. Refresh browser (Ctrl+F5)
2. Buka Jurnal Umum
3. Cari tanggal 28/04/2026 dan 29/04/2026
4. Verifikasi akun sudah benar

## HASIL YANG DIHARAPKAN

**Sebelum Fix:**
```
28/04/2026 - Pembayaran Beban Sewa
  550 - BOP Listrik (SALAH!)
  111 - Kas Bank

29/04/2026 - Pembayaran Beban Listrik
  550 - BOP Listrik (BENAR)
  111 - Kas Bank
```

**Sesudah Fix:**
```
28/04/2026 - Pembayaran Beban Sewa
  551 - BOP Sewa Tempat (BENAR!)
  111 - Kas Bank

29/04/2026 - Pembayaran Beban Listrik
  550 - BOP Listrik (BENAR)
  111 - Kas Bank
```

## CATATAN PENTING

### Mengapa Masalah Ini Terjadi?
1. Sistem memiliki 2 tabel journal yang berbeda (lama dan baru)
2. Data lama di `jurnal_umum` tidak dihapus saat membuat entry baru
3. Halaman Jurnal Umum menampilkan dari `jurnal_umum` (data lama)

### Mengapa Tidak Diperbaiki Sebelumnya?
1. Perbaikan di controller hanya membuat entry di `journal_entries` (baru)
2. Tapi `jurnal_umum` (lama) masih berisi data lama yang salah
3. Halaman Jurnal Umum mengambil dari `jurnal_umum`, bukan `journal_entries`

### Solusi Jangka Panjang
Pertimbangkan untuk:
1. Menghapus `jurnal_umum` table setelah semua data dimigrasikan
2. Atau membuat trigger untuk sync kedua tabel
3. Atau membuat middleware untuk memastikan konsistensi

## QUICK START

**Jalankan URL ini untuk memperbaiki masalah:**
```
http://127.0.0.1:8000/fix-jurnal-umum-direct.php
```

Selesai! Refresh halaman Jurnal Umum dan akun sudah benar.

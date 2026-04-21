# Panduan Analisis Duplikasi Journal Entries Pembayaran Beban

## Ringkasan
Dokumen ini menjelaskan cara menganalisis duplikasi journal entries untuk pembayaran beban pada tanggal 28/04/2026 dan 29/04/2026.

---

## Cara Mengakses Analisis

### Opsi 1: Via Browser (Paling Mudah)
Buka URL berikut di browser:
```
http://localhost/debug_pembayaran_beban.php
```

Halaman ini akan menampilkan:
1. Semua journal entries pada tanggal 28-29 April 2026
2. Detail lines untuk setiap entry
3. Analisis duplikasi berdasarkan tanggal dan deskripsi
4. Entries dengan nominal sama per akun
5. Summary statistik

### Opsi 2: Via Laravel Route
Buka URL berikut di browser:
```
http://localhost/debug/pembayaran-beban-analysis
```

### Opsi 3: Via MySQL Query Langsung
Jalankan file SQL berikut di MySQL client:
```bash
mysql -h 127.0.0.1 -u root eadt_umkm < query_pembayaran_beban.sql
```

---

## File-File yang Tersedia

### 1. `public/debug_pembayaran_beban.php`
- **Tujuan**: Analisis duplikasi via browser
- **Akses**: http://localhost/debug_pembayaran_beban.php
- **Keuntungan**: 
  - Tidak perlu setup Laravel
  - Tampilan HTML yang rapi
  - Bisa diakses langsung dari browser

### 2. `query_pembayaran_beban.sql`
- **Tujuan**: Query SQL untuk analisis manual
- **Isi**:
  - Query 1: Lihat semua entries dengan summary
  - Query 2: Lihat detail lines setiap entry
  - Query 3: Cari duplikasi berdasarkan tanggal + deskripsi
  - Query 4: Cari entries dengan nominal sama
  - Query 5: Summary statistik

### 3. `resources/views/debug/pembayaran_beban_analysis.blade.php`
- **Tujuan**: View Laravel untuk analisis
- **Akses**: http://localhost/debug/pembayaran-beban-analysis
- **Keuntungan**: Menggunakan Eloquent ORM Laravel

### 4. `analyze_pembayaran_beban_duplicates.php`
- **Tujuan**: Script standalone dengan Laravel
- **Cara Jalankan**: `php artisan tinker < analyze_pembayaran_beban_duplicates.php`

### 5. `query_pembayaran_beban_simple.php`
- **Tujuan**: Script PHP sederhana tanpa Laravel
- **Cara Jalankan**: `php query_pembayaran_beban_simple.php`

---

## Struktur Data yang Dianalisis

### Tabel: `journal_entries`
```
- id: ID entry
- entry_date: Tanggal entry
- ref_type: Tipe referensi (expense_payment, dll)
- description: Deskripsi entry
- created_at: Waktu dibuat
```

### Tabel: `journal_lines`
```
- id: ID line
- journal_entry_id: ID entry yang terkait
- account_id: ID akun
- debit: Nominal debit
- credit: Nominal credit
```

---

## Analisis yang Dilakukan

### 1. Duplikasi Berdasarkan Tanggal + Deskripsi
Mencari entries yang memiliki:
- Tanggal sama
- Deskripsi sama
- ID berbeda

**Indikasi**: Kemungkinan entry yang di-input dua kali

### 2. Entries dengan Nominal Sama Per Akun
Mencari entries yang memiliki:
- Tanggal sama
- Account ID sama
- Debit sama
- Credit sama

**Indikasi**: Kemungkinan duplikasi yang lebih pasti

### 3. Summary Statistik
- Total entries pada tanggal tersebut
- Total lines
- Jumlah duplikasi terdeteksi
- Jumlah entries dengan nominal sama

---

## Interpretasi Hasil

### Jika Tidak Ada Duplikasi
```
✓ Tidak ada duplikasi berdasarkan tanggal dan deskripsi yang sama.
✓ Tidak ada entries dengan nominal dan akun yang sama.
```
**Kesimpulan**: Data clean, tidak ada duplikasi

### Jika Ada Duplikasi
```
⚠ Duplikasi ditemukan: 2
Entry 1 dan Entry 2
  Tanggal: 2026-04-28
  Deskripsi: Pembayaran Beban Listrik
  Created: 2026-04-28 10:00:00 vs 2026-04-28 10:05:00
```
**Kesimpulan**: Ada duplikasi, perlu dihapus entry yang lebih baru

---

## Cara Membersihkan Duplikasi

### Jika Sudah Teridentifikasi Duplikasi

1. **Identifikasi entry mana yang harus dihapus**
   - Biasanya entry yang dibuat lebih belakangan (created_at lebih baru)

2. **Hapus entry duplikasi**
   ```sql
   DELETE FROM journal_lines WHERE journal_entry_id = [ID_ENTRY_DUPLIKASI];
   DELETE FROM journal_entries WHERE id = [ID_ENTRY_DUPLIKASI];
   ```

3. **Verifikasi hasil**
   - Jalankan analisis lagi
   - Pastikan duplikasi sudah hilang

---

## Contoh Query Manual

### Lihat semua entries pada tanggal 28-29 April
```sql
SELECT 
    je.id,
    je.entry_date,
    je.description,
    COUNT(jl.id) as line_count,
    SUM(jl.debit) as total_debit,
    SUM(jl.credit) as total_credit
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je.id
ORDER BY je.entry_date, je.id;
```

### Cari duplikasi
```sql
SELECT 
    je1.id as entry1_id,
    je2.id as entry2_id,
    je1.entry_date,
    je1.description,
    je1.created_at as created1,
    je2.created_at as created2
FROM journal_entries je1
JOIN journal_entries je2 ON 
    DATE(je1.entry_date) = DATE(je2.entry_date) AND
    je1.description = je2.description AND
    je1.id < je2.id
WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29';
```

---

## Troubleshooting

### Tidak Ada Data Ditemukan
- Pastikan tanggal di database menggunakan format YYYY-MM-DD
- Cek apakah ada entries pada tanggal tersebut dengan query:
  ```sql
  SELECT COUNT(*) FROM journal_entries WHERE DATE(entry_date) = '2026-04-28';
  ```

### Error Koneksi Database
- Pastikan MySQL running
- Cek konfigurasi di `.env`:
  - DB_HOST
  - DB_PORT
  - DB_DATABASE
  - DB_USERNAME
  - DB_PASSWORD

### Halaman Blank
- Cek error log di `storage/logs/laravel.log`
- Pastikan route sudah ditambahkan di `routes/web.php`

---

## Rekomendasi

1. **Jalankan analisis secara berkala** untuk memastikan tidak ada duplikasi
2. **Backup database** sebelum menghapus entries
3. **Verifikasi hasil** setelah membersihkan duplikasi
4. **Dokumentasikan** entries yang dihapus untuk audit trail

---

## Kontak & Support

Jika ada pertanyaan atau masalah, silakan hubungi tim development.

---

**Terakhir diupdate**: 2026-04-29
**Versi**: 1.0

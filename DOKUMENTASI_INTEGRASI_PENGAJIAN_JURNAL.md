# Dokumentasi Integrasi Penggajian dengan Jurnal Umum & Neraca Saldo

## 📋 Ringkasan Implementasi

Integrasi modul Penggajian dengan Jurnal Umum dan Neraca Saldo telah berhasil diimplementasikan. Setiap transaksi penggajian yang disetujui dapat diposting secara otomatis ke jurnal umum, yang kemudian akan terakumulasi di buku besar dan neraca saldo.

## 📁 File yang Diubah/Ditambah

### 1. Migrations
- **BARU**: `database/migrations/2026_04_11_000002_add_status_posting_to_penggajians_table.php`
  - Menambahkan field `status_posting` (default: 'belum_posting')
  - Menambahkan field `tanggal_posting` (nullable)

### 2. Models
- **DUBAH**: `app/Models/Penggajian.php`
  - Tambah field `status_posting` dan `tanggal_posting` ke fillable/casts
  - Tambah relasi `jurnalEntries()` ke JurnalUmum
  - Tambah method `isPosted()` untuk cek status posting
  - Tambah method `generateNoBukti()` untuk generate nomor bukti otomatis

### 3. Configuration
- **BARU**: `config/penggajian_journal.php`
  - Mapping akun COA untuk beban gaji (BTKL/BTKTL)
  - Mapping akun COA untuk tunjangan
  - Mapping akun COA untuk asuransi
  - Mapping akun COA untuk utang gaji dan kas/bank

### 4. Controller
- **DUBAH**: `app/Http/Controllers/PenggajianController.php`
  - Tambah import `JurnalUmum`
  - Tambah method `postToJournal($id)` untuk posting ke jurnal

### 5. Routes
- **DUBAH**: `routes/web.php`
  - Tambah route `POST /transaksi/penggajian/{id}/post-journal` dengan middleware role:owner,admin

### 6. Views
- **DUBAH**: `resources/views/transaksi/penggajian/show.blade.php`
  - Tambah badge status posting (Sudah/Belum Diposting)
  - Tambah tombol "Posting ke Jurnal" untuk owner/admin

## 🧱 Struktur Data Jurnal yang Digunakan

Sistem menggunakan tabel `jurnal_umum` yang sudah ada dengan struktur:
- `id` - Primary key
- `coa_id` - Foreign key ke tabel coas
- `tanggal` - Tanggal transaksi
- `keterangan` - Deskripsi transaksi
- `debit` - Nilai debit
- `kredit` - Nilai kredit
- `referensi` - ID penggajian
- `tipe_referensi` - Tipe referensi ('penggajian')
- `created_by` - User yang membuat
- `timestamps` - created_at, updated_at

## 🧮 Mapping Akun COA untuk Penggajian

Konfigurasi akun COA disimpan di `config/penggajian_journal.php`:

```php
'beban_gaji_btkl' => '512',      // Beban Gaji Borongan Tenaga Kerja Lepas
'beban_gaji_btklt' => '511',     // Beban Gaji Borongan Tenaga Kerja Tetap Lain
'beban_tunjangan' => '513',      // Beban Tunjangan
'beban_asuransi' => '514',       // Beban Asuransi/BPJS
'utang_gaji' => '211',           // Utang Gaji (jika belum dibayar)
'kas_bank_default' => '111',     // Kas/Bank default (jika langsung dibayar)
```

**CATATAN**: Pastikan COA dengan kode-kode tersebut sudah ada di tabel `coas`. Jika belum ada, buat terlebih dahulu atau sesuaikan konfigurasi.

## 📊 Logika Jurnal Penggajian

### Skema Jurnal

Saat penggajian diposting ke jurnal:

**DEBIT:**
- Beban Gaji (BTKL/BTKTL) = gaji_dasar
- Beban Tunjangan = total_tunjangan
- Beban Asuransi = asuransi

**KREDIT:**
- Kas/Bank (jika status_pembayaran = 'lunas') ATAU
- Utang Gaji (jika status_pembayaran = 'belum_lunas') = total_gaji

### Perhitungan Komponen

**BTKL (Borongan Tenaga Kerja Lepas):**
- gaji_dasar = tarif_per_jam × total_jam_kerja

**BTKTL (Borongan Tenaga Kerja Tetap Lain):**
- gaji_dasar = gaji_pokok

**Keduanya:**
- total_tunjangan = tunjangan_jabatan + tunjangan_transport + tunjangan_konsumsi
- total_gaji = gaji_dasar + total_tunjangan + asuransi + bonus - potongan

### Satu Jurnal Per Pegawai

Sistem menggunakan pendekatan **satu jurnal per pegawai** untuk:
- Audit trail yang lebih jelas
- Memudahkan tracing per pegawai
- Flexibility untuk reverse per pegawai jika diperlukan

## 🎯 Method `postToJournal()`

### Lokasi
`app/Http/Controllers/PenggajianController.php`

### Alur Kerja
1. **Permission Check**: Hanya owner/admin yang bisa posting
2. **Double Posting Check**: Cegah posting jika sudah diposting
3. **Transaction**: Gunakan DB transaction untuk atomic operation
4. **Hitung Komponen**: Ambil data gaji dari record penggajian
5. **Generate No Bukti**: Format `PGJ-YYYYMM-XXXX` (auto-increment per hari)
6. **Cari COA**: Ambil COA dari config berdasarkan kode akun
7. **Validasi COA**: Pastikan semua COA yang diperlukan tersedia
8. **Buat Jurnal Entries**:
   - DEBIT: Beban Gaji
   - DEBIT: Beban Tunjangan (jika > 0)
   - DEBIT: Beban Asuransi (jika > 0)
   - KREDIT: Kas/Bank atau Utang Gaji
9. **Update Status**: Set status_posting = 'posted' dan tanggal_posting = now()
10. **Commit**: Commit transaction jika berhasil, rollback jika gagal

## 🎨 UI / Aksi

### Halaman Detail Penggajian (`show.blade.php`)

**Status Posting Badge:**
- ✅ **Sudah Diposting ke Jurnal** (hijau) - jika status_posting = 'posted'
- ⏳ **Belum Diposting ke Jurnal** (kuning) - jika status_posting = 'belum_posting'

**Tombol Posting ke Jurnal:**
- Muncul hanya untuk role owner/admin
- Muncul hanya jika status_posting != 'posted'
- Konfirmasi sebelum posting
- POST ke route `transaksi.penggajian.post-journal`

## 🔗 Integrasi ke Neraca Saldo

Sistem Neraca Saldo/Buku Besar yang sudah ada akan otomatis membaca data dari tabel `jurnal_umum` berdasarkan COA.

Setelah jurnal penggajian dibuat:
- Saldo akun beban gaji akan bertambah (debit)
- Saldo akun beban tunjangan akan bertambah (debit)
- Saldo akun beban asuransi akan bertambah (debit)
- Saldo akun kas/bank akan berkurang (kredit) ATAU saldo utang gaji bertambah (kredit)

Semua akun ini akan terakumulasi di:
- Buku Besar (per akun)
- Neraca Saldo (semua akun)
- Laporan Laba Rugi (akun beban)
- Laporan Posisi Keuangan (akun kas/utang)

## 🧪 Cara Testing

### 1. Setup Awal
```bash
# Run migration untuk tambah field baru
php artisan migrate

# Pastikan COA yang diperlukan sudah ada
# Cek di tabel coas untuk kode: 511, 512, 513, 514, 211, 111
# Jika belum ada, buat via menu Master Data -> COA
```

### 2. Buat Penggajian Baru
1. Login sebagai owner/admin
2. Buka Transaksi → Penggajian
3. Klik "Tambah Penggajian"
4. Pilih pegawai (BTKL atau BTKTL)
5. Isi semua komponen gaji
6. Simpan penggajian

### 3. Posting ke Jurnal
1. Buka detail penggajian yang baru dibuat
2. Cek badge status: harus "Belum Diposting ke Jurnal" (kuning)
3. Klik tombol "Posting ke Jurnal"
4. Konfirmasi dialog
5. Status berubah menjadi "Sudah Diposting ke Jurnal" (hijau)

### 4. Verifikasi Jurnal
```sql
-- Cek jurnal entries yang dibuat
SELECT * FROM jurnal_umum 
WHERE tipe_referensi = 'penggajian' 
AND referensi = [ID_PENGAJIAN]
ORDER BY id;

-- Pastikan total_debit = total_kredit
SELECT 
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit
FROM jurnal_umum
WHERE tipe_referensi = 'penggajian'
AND referensi = [ID_PENGAJIAN];
```

### 5. Verifikasi Neraca Saldo
1. Buka Laporan → Buku Besar
2. Pilih akun beban gaji (kode 511/512)
3. Pastikan transaksi penggajian muncul
4. Buka Laporan → Neraca Saldo
5. Pastikan saldo akun beban gaji sudah bertambah

### 6. Cegah Double Posting
1. Coba klik tombol "Posting ke Jurnal" lagi
2. Harus muncul error: "Penggajian ini sudah diposting ke jurnal"

### 7. Test Role Permission
1. Login sebagai role selain owner/admin (misal: pegawai)
2. Buka detail penggajian
3. Tombol "Posting ke Jurnal" tidak boleh muncul
4. Coba akses langsung route via URL → harus 403 Forbidden

## ✅ Checklist Verifikasi

- [x] Migration berhasil dijalankan
- [x] Field status_posting dan tanggal_posting ditambah ke tabel penggajians
- [x] Config penggajian_journal.php dibuat dengan mapping COA
- [x] Model Penggajian memiliki relasi jurnalEntries
- [x] Method postToJournal() ditambah ke controller
- [x] Route post-journal ditambah dengan middleware
- [x] Tombol "Posting ke Jurnal" muncul di show view
- [x] Badge status posting ditampilkan
- [x] Tombol hanya muncul untuk owner/admin
- [x] Double posting dicegah
- [x] Jurnal entries dibuat dengan benar
- [x] Total debit = total kredit
- [x] Neraca saldo terakumulasi dengan benar

## 🔧 Troubleshooting

### Error: "COA untuk jurnal penggajian belum dikonfigurasi dengan benar"
**Solusi**: Pastikan semua COA dengan kode yang diset di config sudah ada di tabel coas:
- 511 (Beban Gaji BTKTL)
- 512 (Beban Gaji BTKL)
- 513 (Beban Tunjangan)
- 514 (Beban Asuransi)
- 211 (Utang Gaji)
- 111 (Kas/Bank)

### Error: "Penggajian ini sudah diposting ke jurnal"
**Solusi**: Normal, ini adalah fitur pencegahan double posting. Jika ingin re-post, hapus jurnal entries terkait dulu atau set status_posting = 'belum_posting'.

### Jurnal tidak muncul di Neraca Saldo
**Solusi**: Pastikan modul Neraca Saldo membaca dari tabel jurnal_umum dengan query yang benar. Cek query di controller/ view Neraca Saldo.

## 📝 Catatan Penting

1. **Tidak ada input jurnal manual** untuk penggajian biasa. Semua jurnal dibuat otomatis melalui tombol "Posting ke Jurnal".

2. **Audit trail**: Semua jurnal entries memiliki field created_by untuk tracking siapa yang melakukan posting.

3. **Referensi**: Jurnal entries terhubung ke penggajian melalui referensi (ID penggajian) dan tipe_referensi ('penggajian').

4. **No Bukti**: Format PGJ-YYYYMM-XXXX dengan auto-increment per hari untuk memudahkan identifikasi.

5. **Transaction**: Proses posting menggunakan DB transaction untuk memastikan atomic operation.

6. **Role-based**: Hanya owner/admin yang bisa posting ke jurnal untuk keamanan.

## 🎓 Skema Jurnal yang Digunakan

```
Penggajian BTKL (Sudah Dibayar via Bank):
-----------------------------------------
DEBIT  512  Beban Gaji BTKL         Rp [tarif_per_jam × jam_kerja]
DEBIT  513  Beban Tunjangan        Rp [total_tunjangan]
DEBIT  514  Beban Asuransi         Rp [asuransi]
KREDIT 111  Kas/Bank               Rp [total_gaji]

Penggajian BTKTL (Belum Dibayar):
---------------------------------
DEBIT  511  Beban Gaji BTKTL       Rp [gaji_pokok]
DEBIT  513  Beban Tunjangan        Rp [total_tunjangan]
DEBIT  514  Beban Asuransi         Rp [asuransi]
KREDIT 211  Utang Gaji             Rp [total_gaji]
```

## 📞 Dukungan

Jika ada masalah atau pertanyaan, cek:
1. Log Laravel: `storage/logs/laravel.log`
2. Database: Tabel jurnal_umum dan penggajians
3. Config: `config/penggajian_journal.php`
4. COA: Tabel coas untuk memastikan mapping benar

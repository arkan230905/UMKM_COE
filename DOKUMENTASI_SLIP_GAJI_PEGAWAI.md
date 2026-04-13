# Dokumentasi Fitur Slip Gaji untuk Pegawai

## 📋 Ringkasan Implementasi

Fitur "Slip Gaji" untuk role Pegawai telah berhasil diimplementasikan. Pegawai sekarang dapat melihat daftar slip gaji miliknya sendiri dan mendownload PDF tanpa bisa melihat gaji pegawai lain.

## 📁 File yang Diubah/Ditambah

### 1. Controller
- **DUBAH**: `app/Http/Controllers/PegawaiDashboardController.php`
  - Tambah import `Penggajian`
  - Tambah method `slipGajiIndex()` - daftar slip gaji pegawai
  - Tambah method `slipGajiShow($id)` - detail slip gaji
  - Tambah method `slipGajiPdf($id)` - generate PDF slip gaji

### 2. Routes
- **DUBAH**: `routes/web.php`
  - Tambah route group `/pegawai/slip-gaji` dengan 3 route:
    - `GET /pegawai/slip-gaji` → index (daftar slip gaji)
    - `GET /pegawai/slip-gaji/{id}` → show (detail slip gaji)
    - `GET /pegawai/slip-gaji/{id}/pdf` → pdf (download PDF)
  - Semua route dilindungi middleware `auth` + `role:pegawai`

### 3. Sidebar
- **DUBAH**: `resources/views/layouts/sidebar.blade.php`
  - Tambah section "PENGAJIAN" di bawah "PRESENSI"
  - Tambah menu "Slip Gaji" dengan icon `fa-file-invoice-dollar`

### 4. Views
- **BARU**: `resources/views/pegawai/slip-gaji/index.blade.php`
  - Daftar slip gaji pegawai
  - Filter periode (bulan/tahun)
  - Tabel dengan kolom: No, Periode, Tanggal, Total Gaji, Status, Aksi

- **BARU**: `resources/views/pegawai/slip-gaji/show.blade.php`
  - Detail slip gaji dengan layout lengkap
  - Informasi pegawai, status pembayaran, rincian gaji, total gaji

- **BARU**: `resources/views/pegawai/slip-gaji/pdf.blade.php`
  - Template PDF slip gaji untuk download

---

## 🧠 Alur Data

### Dari Penggajian (Owner/Admin) → Slip Gaji (Pegawai)

```
1. Owner/Admin meng-input penggajian:
   - Memilih pegawai
   - Mengisi komponen gaji (gaji_dasar, tunjangan, asuransi, bonus, potongan)
   - Menyimpan dengan status (disetujui/dibayar)

2. Data tersimpan di tabel `penggajians`:
   - pegawai_id (relasi ke pegawai)
   - tanggal_penggajian
   - gaji_pokok / tarif_per_jam
   - total_jam_kerja
   - tunjangan_jabatan, tunjangan_transport, tunjangan_konsumsi
   - total_tunjangan
   - asuransi, bonus, potongan
   - total_gaji
   - status_pembayaran (disetujui/lunas/dibatalkan)

3. Pegawai login dan mengakses menu "Slip Gaji":
   - Query: Penggajian::where('pegawai_id', $pegawai->id)
             ->whereIn('status_pembayaran', ['disetujui', 'lunas'])
   - Hanya penggajian milik pegawai login yang tampil
   - Hanya penggajian dengan status disetujui/lunas yang tampil

4. Pegawai melihat detail slip gaji:
   - Data diambil langsung dari tabel penggajians
   - Tidak ada perhitungan ulang
   - Komponen gaji ditampilkan lengkap
   - Total gaji dihitung dari komponen yang tersimpan

5. Pegawai download PDF:
   - PDF di-generate dari data yang sama
   - Format resmi untuk dokumentasi
```

---

## 🧮 Logika Query

### Index (Daftar Slip Gaji)

```php
$query = Penggajian::with('pegawai')
    ->where('pegawai_id', $pegawai->id)  // Hanya milik pegawai login
    ->whereIn('status_pembayaran', ['disetujui', 'lunas']);  // Hanya yang disetujui/dibayar

// Filter periode (opsional)
if ($request->has('month') && $request->has('year')) {
    $query->whereMonth('tanggal_penggajian', $request->month)
          ->whereYear('tanggal_penggajian', $request->year);
}

$penggajians = $query->orderBy('tanggal_penggajian', 'desc')->paginate(10);
```

### Show (Detail Slip Gaji)

```php
// Ambil penggajian dan pastikan milik pegawai login
$penggajian = Penggajian::with('pegawai')->findOrFail($id);

// Security: cek apakah penggajian milik pegawai login
if ($penggajian->pegawai_id !== $pegawai->id) {
    abort(403, 'Anda tidak memiliki akses ke slip gaji ini.');
}

// Cek status penggajian - hanya tampilkan yang sudah disetujui/dibayar
if (!in_array($penggajian->status_pembayaran, ['disetujui', 'lunas'])) {
    abort(403, 'Slip gaji belum tersedia. Penggajian ini belum disetujui atau dibayar.');
}

// Hitung komponen gaji (bukan hitung ulang, hanya ambil dari data tersimpan)
$jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');

if ($jenis === 'btkl') {
    $gajiDasar = (float)($penggajian->tarif_per_jam ?? 0) * (float)($penggajian->total_jam_kerja ?? 0);
} else {
    $gajiDasar = (float)($penggajian->gaji_pokok ?? 0);
}

$totalGajiHitung = $gajiDasar
    + (float)($penggajian->tunjangan ?? 0)
    + (float)($penggajian->asuransi ?? 0)
    + (float)($penggajian->bonus ?? 0)
    - (float)($penggajian->potongan ?? 0);
```

---

## 🔒 Security

### 1. Role-Based Access Control
- Route dilindungi middleware `role:pegawai`
- Hanya user dengan role `pegawai` yang bisa akses

### 2. Data Isolation
- Query selalu filter: `where('pegawai_id', $pegawai->id)`
- Pegawai hanya bisa melihat penggajian miliknya sendiri

### 3. Status Filter
- Hanya penggajian dengan status `disetujui` atau `lunas` yang tampil
- Penggajian dengan status lain (draft, dibatalkan) tidak tampil

### 4. ID Protection
- Di method `show()`, cek: `if ($penggajian->pegawai_id !== $pegawai->id) abort(403)`
- Mencegah pegawai mengakses slip gaji orang lain dengan mengubah URL ID

### 5. Pegawai Tidak Bisa Akses Route Admin
- Route penggajian admin: `/transaksi/penggajian/*` (middleware role:admin,owner)
- Route slip gaji pegawai: `/pegawai/slip-gaji/*` (middleware role:pegawai)
- Pegawai tidak bisa akses jurnal umum atau menu admin lain

---

## 🧪 Cara Testing

### 1. Login sebagai Owner/Admin
1. Buka **Transaksi → Penggajian**
2. Buat penggajian baru untuk salah satu pegawai (misal: Ahmad Suryanto)
3. Isi semua komponen gaji
4. Set status pembayaran: `disetujui` atau `lunas`
5. Simpan

### 2. Login sebagai Pegawai (Ahmad Suryanto)
1. Buka sidebar → menu **Slip Gaji** (di bawah Rekap Harian)
2. Cek daftar slip gaji:
   - Hanya penggajian milik Ahmad Suryanto yang tampil
   - Hanya penggajian dengan status disetujui/lunas yang tampil
3. Klik tombol "Lihat" pada salah satu slip gaji
4. Cek detail:
   - Informasi pegawai lengkap
   - Komponen gaji lengkap
   - Total gaji sesuai
5. Klik tombol "Download PDF"
6. PDF terdownload dengan format resmi

### 3. Coba Akses Slip Gaji Pegawai Lain
1. Login sebagai pegawai (misal: Budi Susanto)
2. Coba akses URL langsung: `/pegawai/slip-gaji/{id_penggajian_ahmad}`
3. Harus muncul error 403: "Anda tidak memiliki akses ke slip gaji ini"

### 4. Coba Akses Route Admin sebagai Pegawai
1. Login sebagai pegawai
2. Coba akses URL: `/transaksi/penggajian`
3. Harus redirect ke login atau 403 (karena bukan role admin/owner)

### 5. Filter Periode
1. Di halaman daftar slip gaji pegawai
2. Pilih bulan dan tahun
3. Klik "Filter"
4. Hanya slip gaji pada periode tersebut yang tampil

---

## ✅ Checklist Verifikasi

- [x] Menu "Slip Gaji" muncul di sidebar pegawai
- [x] Route slip gaji ditambah dengan middleware role:pegawai
- [x] Method slipGajiIndex() di controller
- [x] Method slipGajiShow() di controller
- [x] Method slipGajiPdf() di controller
- [x] View index slip gaji dibuat
- [x] View show slip gaji dibuat
- [x] View PDF slip gaji dibuat
- [x] Query filter pegawai_id dan status_pembayaran
- [x] Security check di show method (pegawai_id)
- [x] Pegawai hanya bisa lihat gaji sendiri
- [x] Pegawai tidak bisa akses gaji orang lain (403)
- [x] Hanya status disetujui/lunas yang tampil
- [x] Filter periode berfungsi
- [x] PDF download berfungsi
- [x] Data diambil dari tabel penggajians (bukan hitung ulang)

---

## 📊 Struktur Data

### Tabel `penggajians` (sumber data)

| Field | Keterangan |
|-------|------------|
| id | Primary key |
| pegawai_id | Foreign key ke pegawais (relasi ke pegawai) |
| tanggal_penggajian | Tanggal penggajian |
| gaji_pokok | Gaji pokok (untuk BTKTL) |
| tarif_per_jam | Tarif per jam (untuk BTKL) |
| total_jam_kerja | Total jam kerja (untuk BTKL) |
| tunjangan_jabatan | Tunjangan jabatan |
| tunjangan_transport | Tunjangan transportasi |
| tunjangan_konsumsi | Tunjangan konsumsi |
| total_tunjangan | Total tunjangan |
| asuransi | Asuransi/BPJS |
| bonus | Bonus |
| potongan | Potongan |
| total_gaji | Total gaji |
| status_pembayaran | Status (disetujui/lunas/dibatalkan) |
| tanggal_dibayar | Tanggal dibayar |
| metode_pembayaran | Metode pembayaran (transfer/tunai/cek) |
| status_posting | Status posting ke jurnal (posted/belum_posting) |
| tanggal_posting | Tanggal posting ke jurnal |

### Tabel `pegawais` (relasi)

| Field | Keterangan |
|-------|------------|
| id | Primary key |
| kode_pegawai | Kode pegawai (auto-generated: PGWXXXX) |
| nama | Nama pegawai |
| email | Email |
| no_telepon | Nomor telepon |
| alamat | Alamat |
| jenis_kelamin | Jenis kelamin (L/P) |
| jabatan | Nama jabatan (text) |
| jabatan_id | Foreign key ke jabatans |
| jenis_pegawai | Jenis pegawai (BTKL/BTKTL) |
| bank | Bank |
| nomor_rekening | Nomor rekening |
| nama_rekening | Nama rekening |

---

## 🎯 Alur Kerja Lengkap

### 1. Owner/Admin: Input Penggajian
```
Login (owner/admin) 
→ Transaksi → Penggajian 
→ Tambah Penggajian 
→ Pilih Pegawai 
→ Isi Komponen Gaji 
→ Set Status (disetujui/lunas) 
→ Simpan 
→ (Opsional) Posting ke Jurnal
```

### 2. Pegawai: Lihat Slip Gaji
```
Login (pegawai) 
→ Menu Slip Gaji 
→ Lihat Daftar Slip Gaji 
→ Filter Periode (opsional) 
→ Klik Lihat Detail 
→ Lihat Rincian Lengkap 
→ Download PDF (opsional)
```

### 3. Security Enforcement
```
Request ke /pegawai/slip-gaji/{id}
→ Middleware cek auth & role:pegawai 
→ Controller ambil user login 
→ Controller ambil pegawai terkait user 
→ Controller query penggajian by id 
→ Controller cek: penggajian->pegawai_id == pegawai->id? 
→ Jika tidak sama → abort(403) 
→ Jika sama → lanjut ke view
```

---

## 📝 Catatan Penting

1. **Data Source**: Slip gaji pegawai membaca data langsung dari tabel `penggajians` yang diinput oleh owner/admin. Tidak ada perhitungan ulang.

2. **Status Filter**: Hanya penggajian dengan status `disetujui` atau `lunas` yang tampil untuk pegawai. Status lain (draft, dibatalkan) tidak tampil.

3. **Data Isolation**: Pegawai hanya bisa melihat penggajian miliknya sendiri berdasarkan `pegawai_id`. Tidak bisa melihat gaji pegawai lain.

4. **Audit Trail**: Setiap penggajian memiliki `created_at` dan `status_posting` untuk tracking.

5. **PDF Generation**: PDF di-generate menggunakan domPDF dengan template khusus untuk format resmi.

6. **No Direct Access**: Pegawai tidak bisa akses route admin (transaksi/penggajian) karena middleware role berbeda.

7. **Backward Compatible**: Fitur ini tidak mengubah cara kerja penggajian yang sudah ada. Hanya menambahkan view untuk pegawai.

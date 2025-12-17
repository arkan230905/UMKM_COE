# Dokumentasi: Penambahan Bank & Nomor Rekening ke Slip Gaji

## Ringkasan Perubahan
Telah ditambahkan informasi Bank dan Nomor Rekening Pegawai ke Slip Gaji (format HTML dan PDF) untuk melengkapi identitas karyawan.

## File yang Dimodifikasi

### 1. `resources/views/transaksi/penggajian/slip.blade.php`
**Perubahan:** Tambah 2 baris di section "Data Pegawai"

**Lokasi:** Setelah field "Jenis Pegawai" (baris 53-60)

**Kode yang ditambahkan:**
```blade
<div class="col-md-6">
    <p class="text-dark small mb-1" style="font-weight: 600;">Bank</p>
    <p class="text-dark fw-bold" style="font-size: 16px;">{{ $penggajian->pegawai->bank ?? '-' }}</p>
</div>
<div class="col-md-6">
    <p class="text-dark small mb-1" style="font-weight: 600;">Nomor Rekening</p>
    <p class="text-dark fw-bold" style="font-size: 16px;">{{ $penggajian->pegawai->nomor_rekening ?? '-' }}</p>
</div>
```

**Fitur:**
- Menampilkan nama bank dari field `pegawai->bank`
- Menampilkan nomor rekening dari field `pegawai->nomor_rekening`
- Jika field kosong, menampilkan "-" (null coalescing operator)
- Styling konsisten dengan field lainnya (bold, font-size 16px)

---

### 2. `resources/views/transaksi/penggajian/slip-pdf.blade.php`
**Perubahan:** Tambah 2 baris di section "Employee Information"

**Lokasi:** Setelah field "Jenis Pegawai" (baris 252-267)

**Kode yang ditambahkan:**
```blade
<div class="employee-info-row">
    <div class="employee-info-cell">
        <span class="employee-info-label">Bank</span>
    </div>
    <div class="employee-info-cell">
        <span class="employee-info-value">{{ $penggajian->pegawai->bank ?? '-' }}</span>
    </div>
</div>
<div class="employee-info-row">
    <div class="employee-info-cell">
        <span class="employee-info-label">Nomor Rekening</span>
    </div>
    <div class="employee-info-cell">
        <span class="employee-info-value">{{ $penggajian->pegawai->nomor_rekening ?? '-' }}</span>
    </div>
</div>
```

**Fitur:**
- Menampilkan nama bank dan nomor rekening dalam format tabel
- Konsisten dengan styling employee information lainnya
- Responsive dan siap untuk cetak PDF

---

## Model & Database

### Model Pegawai (`app/Models/Pegawai.php`)
Field sudah tersedia di model:

```php
protected $fillable = [
    // ... field lainnya
    'bank',              // Nama Bank
    'nomor_rekening',    // Nomor Rekening
    'nama_rekening',     // Nama Rekening (opsional)
];
```

### Database Schema
Pastikan tabel `pegawais` memiliki kolom:
- `bank` (varchar, nullable)
- `nomor_rekening` (varchar, nullable)
- `nama_rekening` (varchar, nullable) - opsional

---

## Controller (`app/Http/Controllers/PenggajianController.php`)

**Tidak ada perubahan diperlukan** karena:
1. Method `showSlip()` sudah menggunakan `with('pegawai')` untuk load relasi
2. Method `exportSlipPdf()` sudah menggunakan data yang sama
3. Data bank dan nomor rekening otomatis tersedia melalui relasi `$penggajian->pegawai`

---

## Cara Menggunakan

### 1. Tampilkan Slip Gaji HTML
```
Buka: /transaksi/penggajian/{id}/slip
```
- Klik tombol "Cetak Slip" di halaman Penggajian
- Slip akan menampilkan Bank dan Nomor Rekening di bagian identitas

### 2. Download Slip Gaji PDF
```
Buka: /transaksi/penggajian/{id}/slip-pdf
```
- Klik tombol "Download PDF" di halaman Slip HTML
- PDF akan berisi informasi Bank dan Nomor Rekening

### 3. Cetak Slip Gaji
- Buka Slip HTML
- Klik tombol "Cetak Slip Gaji"
- Browser print dialog akan muncul
- Informasi Bank dan Nomor Rekening akan tercetak

---

## Contoh Output

### Slip HTML
```
Nama Pegawai    : Budi Santoso
Kode Pegawai    : PGW0001
Jabatan         : Manager
Jenis Pegawai   : BTKTL (Tetap)
Bank            : BCA
Nomor Rekening  : 1234567890
```

### Slip PDF
Sama dengan HTML, dengan styling yang dioptimalkan untuk cetak

---

## Validasi Data

Sebelum menggunakan fitur ini, pastikan:

1. ✅ Field `bank` dan `nomor_rekening` sudah ada di tabel `pegawais`
2. ✅ Data pegawai sudah diisi dengan informasi bank dan rekening
3. ✅ Model Pegawai sudah memiliki field di `$fillable`
4. ✅ Relasi `Penggajian belongsTo Pegawai` sudah benar

---

## Testing

### Test Case 1: Slip dengan Bank & Rekening Lengkap
1. Buat pegawai dengan bank dan nomor rekening
2. Buat penggajian untuk pegawai tersebut
3. Buka slip gaji
4. Verifikasi bank dan nomor rekening ditampilkan dengan benar

### Test Case 2: Slip dengan Bank/Rekening Kosong
1. Buat pegawai tanpa bank dan nomor rekening
2. Buat penggajian untuk pegawai tersebut
3. Buka slip gaji
4. Verifikasi menampilkan "-" untuk field kosong

### Test Case 3: Export PDF
1. Buka slip gaji HTML
2. Klik "Download PDF"
3. Verifikasi PDF berisi bank dan nomor rekening
4. Cetak PDF dan verifikasi output

---

## Catatan Penting

- Perubahan **minimal** dan **tidak mengubah struktur** slip gaji yang sudah ada
- Semua field menggunakan null coalescing (`??`) untuk handle data kosong
- Styling konsisten dengan desain slip gaji yang sudah ada
- Kompatibel dengan kedua format: HTML dan PDF
- Tidak ada perubahan di Controller atau Routes

---

## Status Implementasi

✅ **SELESAI**

- [x] Tambah field Bank ke slip HTML
- [x] Tambah field Nomor Rekening ke slip HTML
- [x] Tambah field Bank ke slip PDF
- [x] Tambah field Nomor Rekening ke slip PDF
- [x] Verifikasi Model Pegawai
- [x] Verifikasi relasi Penggajian-Pegawai
- [x] Dokumentasi lengkap

---

## Referensi

- **Model Pegawai:** `app/Models/Pegawai.php`
- **Controller:** `app/Http/Controllers/PenggajianController.php`
- **View HTML:** `resources/views/transaksi/penggajian/slip.blade.php`
- **View PDF:** `resources/views/transaksi/penggajian/slip-pdf.blade.php`
- **Routes:** `routes/web.php` (penggajian routes)

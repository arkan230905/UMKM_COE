# Dokumentasi Fitur Slip Gaji

## 📋 Ringkasan
Fitur Slip Gaji memungkinkan pengguna untuk melihat, mencetak, dan mengunduh slip gaji karyawan dalam format HTML dan PDF. Fitur ini terintegrasi dengan sistem penggajian yang sudah ada.

---

## 🎯 Fitur Utama

### 1. **Tampilkan Slip Gaji (HTML)**
- Menampilkan slip gaji dalam format HTML yang responsif
- Dapat dicetak langsung dari browser
- Menampilkan rincian lengkap pendapatan dan potongan

### 2. **Unduh Slip Gaji (PDF)**
- Export slip gaji ke format PDF
- Nama file otomatis: `slip-gaji-[nama-pegawai]-[tanggal].pdf`
- Format profesional dengan tanda tangan

### 3. **Tombol Aksi di Tabel**
- Tombol "Cetak Slip" (ikon PDF) di setiap baris penggajian
- Mudah diakses dari halaman Transaksi → Penggajian

---

## 📁 File-File yang Dibuat/Dimodifikasi

### 1. **Route** (`routes/web.php`)
```php
Route::get('/{id}/slip', [PenggajianController::class, 'showSlip'])->name('slip');
Route::get('/{id}/slip-pdf', [PenggajianController::class, 'exportSlipPdf'])->name('slip-pdf');
```

### 2. **Controller Methods** (`app/Http/Controllers/PenggajianController.php`)

#### `showSlip($id)`
- Menampilkan slip gaji dalam format HTML
- Route: `/transaksi/penggajian/{id}/slip`
- Return: View `transaksi.penggajian.slip`

#### `exportSlipPdf($id)`
- Export slip gaji ke PDF
- Route: `/transaksi/penggajian/{id}/slip-pdf`
- Return: PDF file download

#### `calculateSlipData($penggajian)` (Private)
- Menghitung rincian pendapatan dan potongan
- Mendukung dua jenis pegawai: BTKL dan BTKTL
- Return: Array dengan struktur:
  ```php
  [
      'pendapatan' => [...],
      'total_pendapatan' => float,
      'potongan' => [...],
      'total_potongan' => float,
      'total_akhir' => float,
      'jenis_pegawai' => string
  ]
  ```

### 3. **Views**

#### `resources/views/transaksi/penggajian/slip.blade.php`
- Tampilan HTML slip gaji
- Responsive design dengan Tailwind CSS
- Tombol Download PDF dan Cetak
- Tombol Kembali ke daftar penggajian

#### `resources/views/transaksi/penggajian/slip-pdf.blade.php`
- Template PDF slip gaji
- Format profesional dengan styling CSS
- Bagian tanda tangan untuk pegawai dan pimpinan

#### `resources/views/transaksi/penggajian/index.blade.php` (Modified)
- Tambahan tombol "Cetak Slip" di kolom aksi
- Ikon: `bi bi-file-earmark-pdf`
- Class: `btn btn-sm btn-success`

---

## 🔄 Alur Kerja

```
1. User membuka halaman Transaksi → Penggajian
   ↓
2. User melihat tabel daftar penggajian dengan tombol "Cetak Slip"
   ↓
3. User klik tombol "Cetak Slip" pada baris penggajian
   ↓
4. Sistem menampilkan slip gaji dalam format HTML
   ↓
5. User dapat:
   a. Cetak langsung (Ctrl+P atau tombol Cetak)
   b. Download PDF (tombol Download PDF)
   c. Kembali ke daftar (tombol Kembali)
```

---

## 📊 Struktur Data Slip Gaji

### Rincian Pendapatan

#### Untuk Pegawai BTKL (Borongan/Tarif):
- **Gaji Dasar** = Tarif per Jam × Total Jam Kerja
- **Tunjangan** (jika ada)
- **Bonus** (jika ada)

#### Untuk Pegawai BTKTL (Tetap):
- **Gaji Pokok**
- **Tunjangan** (jika ada)
- **Bonus** (jika ada)

### Rincian Potongan
- **Asuransi** (jika ada)
- **Potongan** (jika ada)

### Total Akhir
```
Total Akhir = Total Pendapatan - Total Potongan
```

---

## 🎨 Desain & Layout

### Tampilan HTML (slip.blade.php)
- **Header**: Judul "SLIP GAJI" dan periode
- **Data Pegawai**: Nama, Kode, Jabatan, Jenis Pegawai
- **Rincian Pendapatan**: Daftar item dengan nilai
- **Rincian Potongan**: Daftar item dengan nilai (jika ada)
- **Total Akhir**: Highlight dengan background hijau
- **Footer**: Informasi cetak dan keterangan
- **Tombol Aksi**: Download PDF, Cetak, Kembali

### Tampilan PDF (slip-pdf.blade.php)
- **Header**: Judul dan periode
- **Data Pegawai**: Tabel informasi
- **Rincian Pendapatan**: Daftar terstruktur
- **Rincian Potongan**: Daftar terstruktur (jika ada)
- **Total Akhir**: Highlight dengan background abu-abu
- **Tanda Tangan**: Bagian untuk pegawai dan pimpinan
- **Footer**: Informasi cetak

---

## 🔧 Cara Menggunakan

### 1. Melihat Slip Gaji (HTML)
```
1. Buka menu Transaksi → Penggajian
2. Cari baris penggajian yang ingin dilihat
3. Klik tombol "Cetak Slip" (ikon PDF hijau)
4. Slip gaji akan ditampilkan di halaman baru
```

### 2. Mencetak Slip Gaji
```
1. Buka slip gaji (lihat langkah di atas)
2. Klik tombol "Cetak" atau tekan Ctrl+P
3. Pilih printer dan klik Print
```

### 3. Download Slip Gaji (PDF)
```
1. Buka slip gaji (lihat langkah di atas)
2. Klik tombol "Download PDF"
3. File akan diunduh dengan nama: slip-gaji-[nama-pegawai]-[tanggal].pdf
```

---

## 📝 Catatan Teknis

### Dependencies
- **Laravel**: 12.x
- **Barryvdh/DomPDF**: Untuk export PDF
- **Tailwind CSS**: Untuk styling HTML
- **Bootstrap Icons**: Untuk ikon tombol

### Database
- Menggunakan tabel `penggajians` yang sudah ada
- Relasi dengan tabel `pegawais`

### Validasi
- Penggajian harus ada di database
- Pegawai harus memiliki data lengkap

### Error Handling
- Jika penggajian tidak ditemukan: 404 Not Found
- Jika pegawai tidak ditemukan: Error message

---

## 🚀 Pengembangan Lebih Lanjut

### Fitur Tambahan yang Bisa Ditambahkan:
1. **Email Slip Gaji**: Kirim slip gaji ke email pegawai
2. **Batch Export**: Export multiple slip gaji sekaligus
3. **Template Kustom**: Buat template slip gaji yang dapat dikustomisasi
4. **Approval Workflow**: Tambah approval sebelum slip dicetak
5. **Signature Digital**: Tanda tangan digital dari pimpinan
6. **History Slip**: Simpan history slip gaji yang sudah dicetak
7. **QR Code**: Tambah QR code untuk verifikasi

---

## 🐛 Troubleshooting

### Masalah: PDF tidak tergenerate
**Solusi:**
- Pastikan Barryvdh/DomPDF sudah terinstall: `composer require barryvdh/laravel-dompdf`
- Pastikan folder `storage/` memiliki permission write

### Masalah: Tombol "Cetak Slip" tidak muncul
**Solusi:**
- Pastikan route sudah ditambahkan di `routes/web.php`
- Refresh cache: `php artisan route:clear`

### Masalah: Data tidak muncul di slip
**Solusi:**
- Pastikan data penggajian lengkap (pegawai, tarif, jam kerja, dll)
- Check database untuk memastikan data tersimpan

---

## 📞 Support
Jika ada pertanyaan atau masalah, silakan hubungi tim development.

---

**Terakhir diupdate:** 10 Desember 2025

# Halaman Manajemen Satuan

## 📋 Overview

Halaman Manajemen Satuan menyediakan dua tab utama untuk mengelola satuan dan mengecek konversi satuan:

1. **Tab Satuan** - Master data satuan
2. **Tab Konversi** - Alat bantu konversi satuan

## 🔹 Tab 1 — Satuan

### Fungsi
Menampilkan daftar satuan yang tersedia di sistem sebagai master data referensi.

### Isi Tab
- **Tabel daftar satuan** dengan kolom:
  - Kode Satuan
  - Nama Satuan
  - Aksi (Edit/Delete)

### Contoh Tampilan
```
Kode   | Nama Satuan
-------------------
KG     | Kilogram
GR     | Gram
SDM    | Sendok Makan
PCS    | Pcs
L       | Liter
ML     | Mililiter
```

### Aturan
- ✅ Tidak ada konversi di tab ini
- ✅ Fokus hanya ke identitas satuan
- ✅ Digunakan sebagai referensi di seluruh aplikasi
- ✅ Tombol edit/delete (redirect ke halaman master data)

### Fitur Tambahan
- **Informasi Total**: Menampilkan jumlah total satuan
- **Kode Unik**: Validasi kode tidak duplikat
- **Nama Unik**: Validasi nama tidak duplikat
- **Panduan**: Informasi cara menggunakan tab

## 🔹 Tab 2 — Konversi

### Fungsi
Digunakan oleh user untuk mengecek informasi konversi satuan secara cepat dan mudah.

### Konsep Utama
- 🔒 **HANYA UNTUK CEK**
- 🔒 **Tidak mengubah data**
- 🔒 **Tidak menyimpan transaksi**
- 🔒 **Tidak memengaruhi stok atau costing**

### Tampilan Tab Konversi
```
Jumlah
[ 1 ]

Dari Satuan
[ Kilogram ▼ ]

Ke Satuan
[ Gram ▼ ]

Hasil
[ 1000 ]   (read-only)
```

### Contoh Penggunaan
- **1 Kilogram → Gram** → 1000
- **1 Gram → Sendok Makan** → 0.067
- **3 Sendok Makan → Gram** → 45

### Perilaku Sistem
#### Hasil Otomatis
Hasil muncul otomatis setiap kali:
- Jumlah diubah
- Satuan asal diubah
- Satuan tujuan diubah

#### Tidak Ada
- ❌ Tombol submit/konversi
- ❌ Status atau pesan error
- ❌ Validasi kompleks

#### Jika Konversi Tidak Tersedia
- Hasil tampil 0 atau kosong
- Tidak ada error message
- User bisa mencoba kombinasi lain

### Aturan UX
- ✅ Tampilan sederhana
- ✅ Fokus ke angka hasil
- ✅ User tidak perlu memahami proses konversi
- ✅ Bersifat read-only untuk hasil

### Fitur Tambahan
- **Auto-swap**: Double-click dropdown asal untuk tukar posisi
- **Keyboard Shortcuts**:
  - `Ctrl+S` - Focus ke input jumlah
  - `Ctrl+A` - Focus ke dropdown asal
  - `Ctrl+T` - Focus ke dropdown tujuan
- **Format Number**: Otomatis format (jt, rb, desimal)
- **Animasi**: Visual feedback saat konversi berhasil
- **Referensi**: Tabel konversi umum (berat, volume, pieces)

## 🔒 Batasan Halaman Konversi

### Tidak Bisa
- ❌ Menambah atau mengedit data satuan
- ❌ Mengatur relasi konversi
- ❌ Digunakan untuk perhitungan produksi atau costing
- ❌ Menyimpan hasil konversi ke database
- ❌ Memengaruhi transaksi lain

### Bisa
- ✅ Mengecek konversi antar satuan
- ✅ Referensi cepat untuk user
- ✅ Pemahaman konversi tanpa rumus manual
- ✅ Interface yang intuitif

## 🎯 Tujuan UX

1. **Memberikan referensi cepat** bagi user
2. **Tampilan sederhana dan intuitif**
3. **Menghindari kompleksitas konversi satuan**
4. **Memisahkan fungsi master data dan utilitas**
5. **Read-only untuk konversi (aman)**

## 📍 Implementasi Teknis

### Route
```
GET /master-data/satuan-dashboard
Controller: SatuanController@dashboard
View: master-data.satuan.dashboard
```

### Controller Method
```php
public function dashboard()
{
    $satuans = Satuan::orderBy('kode', 'asc')->get();
    return view('master-data.satuan.dashboard', compact('satuans'));
}
```

### View Structure
- Bootstrap 5 Tabs
- Responsive design
- JavaScript untuk konversi real-time
- Tidak ada form submission untuk konversi

### Konversi Logic
- JavaScript-based conversion factors
- Support berbagai satuan (kg, gram, liter, ml, pcs, dll)
- Fallback ke 1:1 jika satuan tidak dikenali
- Format number otomatis

## 🔄 Alur Kerja User

### Untuk Master Data Satuan:
1. User buka halaman Manajemen Satuan
2. Tab Satuan aktif secara default
3. User lihat daftar satuan yang tersedia
4. User bisa edit/delete (redirect ke halaman master data)

### Untuk Konversi:
1. User buka halaman Manajemen Satuan
2. User klik Tab Konversi
3. User input jumlah, pilih satuan asal dan tujuan
4. Hasil muncul otomatis
5. User bisa gunakan informasi untuk keperluan lain

## 🎨 Desain & UX

### Visual Design
- **Tab Navigation**: Clean dan jelas
- **Color Coding**: Biru untuk Satuan, Oranye untuk Konversi
- **Icons**: FontAwesome untuk visual clarity
- **Typography**: Hierarki visual yang jelas

### Responsive
- Mobile-friendly layout
- Touch-friendly controls
- Adaptive table untuk satuan list

### Accessibility
- Semantic HTML5
- ARIA labels untuk tabs
- Keyboard navigation support
- Screen reader friendly

---

## 📞 Panduan Penggunaan

### Quick Start:
1. **Buka**: Menu Master Data → Satuan
2. **Tab Satuan**: Lihat master data satuan
3. **Tab Konversi**: Cek konversi satuan
4. **Input**: Masukkan jumlah dan pilih satuan
5. **Hasil**: Lihat hasil konversi otomatis

### Tips:
- Double-click dropdown asal untuk swap posisi
- Gunakan keyboard shortcuts untuk speed
- Lihat referensi konversi umum di bawah
- Hasil otomatis, tidak perlu klik tombol

---

*Halaman ini dirancang untuk memisahkan fungsi master data dan utilitas konversi, memberikan pengalaman user yang lebih baik dan fokus.*

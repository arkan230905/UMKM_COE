# 📖 Cara Menggunakan File BPMN Draw.io

## ✅ File yang Sudah Dibuat

Saya sudah membuatkan 2 file Draw.io format BPMN dengan swimlane:

### 1. **BPMN_SISTEM_SAAT_INI.drawio**
📍 **Untuk Gambar 3.1 - Sistem Saat Ini (As-Is)**

**Isi:**
- Swimlane: Dokumen Fisik & Admin/Pemilik UMKM
- Proses manual dengan buku catatan
- Dokumen terpisah (Buku Catatan, Spreadsheet, Laporan)
- Alur: Cek Stok Manual → Hitung Manual → Update Manual
- Warna: Orange/Kuning (menunjukkan proses manual)
- Annotation: Masalah sistem (Rawan Kesalahan, Tidak Real-time, dll)

### 2. **BPMN_SISTEM_USULAN.drawio**
📍 **Untuk Gambar 3.2 - Sistem Usulan (To-Be)**

**Isi:**
- Swimlane: Database Terpusat, Admin, Sistem (Proses Otomatis)
- Proses otomatis dengan sistem UMKM COE
- Database terintegrasi (Master Data, Transaksi, Akuntansi)
- Alur: Input → Validasi Otomatis → Proses Otomatis → Laporan Real-time
- Warna: Hijau/Biru (menunjukkan sistem otomatis)
- Annotation: Keunggulan sistem (Validasi Otomatis, Real-time, dll)

---

## 🎯 Cara Membuka File di Draw.io

### Metode 1: Draw.io Online (Termudah!)

1. **Buka browser** dan kunjungi: **https://app.diagrams.net/**
2. **Klik** "Open Existing Diagram"
3. **Pilih** "Device" (dari komputer)
4. **Browse** dan pilih file:
   - `BPMN_SISTEM_SAAT_INI.drawio` atau
   - `BPMN_SISTEM_USULAN.drawio`
5. **Klik** "Open"
6. **Diagram langsung muncul!** 🎉

### Metode 2: Draw.io Desktop

1. **Buka aplikasi Draw.io** di komputer
2. **Klik** File → Open
3. **Pilih file** `.drawio`
4. **Diagram langsung muncul!**

### Metode 3: Double-Click

1. **Double-click** file `.drawio`
2. Jika Draw.io sudah terinstall, akan otomatis terbuka
3. Jika belum, Windows akan tanya "Open with?" → Pilih browser

---

## 📝 Cara Edit Diagram (Opsional)

### Mengubah Teks:
1. **Double-click** pada shape/kotak
2. **Edit** teks sesuai kebutuhan
3. **Klik** di luar untuk selesai

### Mengubah Warna:
1. **Klik** shape yang ingin diubah
2. **Klik** icon "Fill" di toolbar kanan
3. **Pilih** warna baru

### Menambah Shape:
1. **Drag** shape dari panel kiri
2. **Drop** ke canvas
3. **Connect** dengan arrow

### Mengubah Posisi:
1. **Klik** dan **drag** shape
2. **Lepas** di posisi baru
3. Arrow akan otomatis menyesuaikan

---

## 💾 Cara Export ke Gambar

### Export PNG (Untuk Word/PDF):

1. **Klik** File → Export as → PNG
2. **Setting:**
   - Zoom: 100%
   - Border Width: 10
   - Transparent Background: ❌ (uncheck)
3. **Klik** "Export"
4. **Save** file dengan nama:
   - `Gambar_3_1_BPMN_Sistem_Saat_Ini.png`
   - `Gambar_3_2_BPMN_Sistem_Usulan.png`
5. **Insert** ke Word

### Export PDF (Untuk Presentasi):

1. **Klik** File → Export as → PDF
2. **Setting:**
   - Fit to: One Page
   - Include: All Pages
3. **Klik** "Export"
4. **Save** file

### Export SVG (Untuk Edit Lanjutan):

1. **Klik** File → Export as → SVG
2. **Klik** "Export"
3. **Save** file
4. Bisa diedit di Illustrator/Inkscape

---

## 📊 Cara Insert ke Word

### Langkah-langkah:

1. **Export** diagram ke PNG (lihat cara di atas)
2. **Buka** dokumen Word Anda
3. **Klik** Insert → Pictures → This Device
4. **Pilih** file PNG yang sudah di-export
5. **Resize** jika perlu (jaga aspect ratio)
6. **Tambahkan caption:**
   ```
   Gambar 3.1 BPMN Sistem Saat Ini (As-Is) - Proses Produksi Manual
   Sumber: Hasil Analisis Penulis, 2025
   ```

### Tips Insert:
- Gunakan "In Line with Text" untuk mudah diatur
- Atau "Square" untuk lebih fleksibel
- Pastikan resolusi cukup (minimal 300 DPI)
- Jangan stretch, gunakan corner handle untuk resize

---

## 🎨 Penjelasan Warna & Simbol

### BPMN Sistem Saat Ini:
| Warna | Arti |
|-------|------|
| 🟡 Kuning/Orange | Proses manual (rawan error) |
| 🔴 Merah Muda | Masalah/Error |
| 📄 Dokumen Kuning | Dokumen fisik terpisah |

### BPMN Sistem Usulan:
| Warna | Arti |
|-------|------|
| 🟢 Hijau | Proses user (Admin) |
| 🟡 Kuning | Decision/Gateway |
| 🟣 Ungu | Proses otomatis sistem |
| 🔵 Biru | Database |
| 🔴 Merah | Error handling |

### Simbol BPMN:
| Simbol | Nama | Arti |
|--------|------|------|
| ⭕ Hijau | Start Event | Mulai proses |
| ⭕ Merah (tebal) | End Event | Selesai proses |
| ▭ | Task/Activity | Aktivitas/pekerjaan |
| ◇ | Gateway | Decision point |
| 📄 | Document | Dokumen |
| 🗄️ | Database | Penyimpanan data |
| → | Sequence Flow | Alur proses |
| ⇢ (putus) | Message Flow | Komunikasi antar lane |

---

## 📖 Penjelasan untuk Laporan

### Untuk Gambar 3.1 (Sistem Saat Ini):

```
Gambar 3.1 menunjukkan proses produksi yang masih dilakukan 
secara manual dengan beberapa kelemahan:

1. Pencatatan Terpisah: Admin harus mencatat di buku catatan 
   yang berbeda untuk stok bahan baku, proses produksi, dan 
   laporan.

2. Pengecekan Manual: Setiap kali akan produksi, admin harus 
   mengecek stok bahan baku secara manual di buku catatan, 
   yang memakan waktu 10-15 menit.

3. Perhitungan Manual: Perhitungan HPP dilakukan manual dengan 
   kalkulator berdasarkan perkiraan, sehingga rawan kesalahan.

4. Update Terpisah: Setelah produksi, admin harus update stok 
   bahan baku dan stok produk jadi di buku yang berbeda secara 
   manual.

5. Laporan Manual: Pembuatan laporan dilakukan manual di akhir 
   periode dengan menghitung ulang semua transaksi.

Proses ini tidak efisien, rawan kesalahan, dan tidak real-time.
```

### Untuk Gambar 3.2 (Sistem Usulan):

```
Gambar 3.2 menunjukkan proses produksi dengan sistem UMKM COE 
yang mengotomatisasi seluruh proses:

1. Input Sederhana: Admin hanya perlu input data produksi 
   (produk, tanggal, qty) melalui form web.

2. Validasi Otomatis: Sistem otomatis validasi BOM dan cek 
   stok real-time dari database.

3. Proses Otomatis: Sistem otomatis melakukan:
   - Consume stok bahan dengan metode FIFO
   - Hitung HPP berdasarkan data BOM, BTKL, dan BOP
   - Update stok produk jadi

4. Posting Jurnal Otomatis: Sistem otomatis posting 3 jurnal 
   akuntansi sesuai standar.

5. Laporan Real-time: Laporan tersedia real-time dan dapat 
   diakses kapan saja.

Sistem ini efisien, akurat, dan terintegrasi dengan database 
terpusat.
```

---

## ✅ Checklist

- [ ] Buka file `BPMN_SISTEM_SAAT_INI.drawio` di Draw.io
- [ ] Export ke PNG sebagai `Gambar_3_1.png`
- [ ] Buka file `BPMN_SISTEM_USULAN.drawio` di Draw.io
- [ ] Export ke PNG sebagai `Gambar_3_2.png`
- [ ] Insert kedua gambar ke Word
- [ ] Tambahkan caption dan sumber
- [ ] Tulis penjelasan gambar
- [ ] Cek kualitas gambar (harus jelas)

---

## 🆘 Troubleshooting

### Diagram tidak muncul?
- Pastikan file `.drawio` tidak corrupt
- Coba buka di browser: https://app.diagrams.net/
- Drag & drop file ke browser

### Gambar blur saat di-export?
- Tingkatkan zoom ke 200% sebelum export
- Atau export ke PDF lalu convert ke PNG

### Tidak bisa edit?
- Pastikan Anda klik shape yang ingin diedit
- Double-click untuk edit teks
- Single-click untuk edit properties

### File terlalu besar?
- Export dengan zoom 100%
- Compress PNG dengan TinyPNG.com
- Atau export ke PDF (lebih kecil)

---

## 🎉 Kesimpulan

Anda sekarang punya:
✅ 2 file BPMN Draw.io siap pakai
✅ Format swimlane profesional
✅ Warna dan simbol yang jelas
✅ Sesuai dengan deskripsi sistem Anda
✅ Siap di-export dan insert ke Word

**Tinggal buka, export, dan masukkan ke laporan!** 🚀

---

## 💡 Tips Tambahan

1. **Konsistensi**: Gunakan warna yang sama untuk elemen yang sama
2. **Clarity**: Pastikan teks mudah dibaca (font size minimal 10pt)
3. **Simplicity**: Jangan terlalu banyak detail, fokus pada alur utama
4. **Legend**: Tambahkan legend jika perlu (opsional)
5. **Quality**: Export dengan resolusi tinggi untuk print

Selamat mengerjakan laporan! 📝

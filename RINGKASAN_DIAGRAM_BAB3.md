# 📊 Ringkasan Semua Diagram untuk BAB 3

## ✅ File yang Sudah Dibuat

### 1. **BAB3_USECASE_DIAGRAM.md**
📍 **Untuk Sub Bab 3.2.2 - Gambar 3.3**
- Use Case Diagram lengkap
- 2 Aktor: Admin & Owner
- 22 Use Case utama
- Relasi include/extend
- Skenario detail UC-10 (Produksi) dan UC-22 (Post Periode)

### 2. **BAB3_BPMN_AS_IS_TO_BE.md**
📍 **Untuk Sub Bab 3.2.1 & 3.2.2 - Gambar 3.1 & 3.2**
- BPMN Sistem Saat Ini (As-Is) → **Gambar 3.1**
- BPMN Sistem Usulan (To-Be) → **Gambar 3.2**
- Perbandingan 5 proses bisnis
- Tabel perbandingan waktu proses
- Analisis masalah dan solusi

### 3. **BAB3_ERD_DATABASE.md**
📍 **Untuk Sub Bab 3.2.2 - Gambar 3.5**
- ERD lengkap 25+ tabel
- Penjelasan entitas
- Kardinalitas relasi
- Normalisasi database
- Indeks dan constraint

### 4. **BAB3_ACTIVITY_CLASS_DIAGRAM.md**
📍 **Untuk Sub Bab 3.2.2 - Gambar 3.4 & 3.6**
- Activity Diagram Produksi → **Gambar 3.4**
- Activity Diagram Laporan
- Class Diagram → **Gambar 3.6**
- Penjelasan arsitektur
- Design patterns

### 5. **BPMN_PROSES_PRODUKSI.md** (Bonus)
📍 **Untuk penjelasan detail**
- BPMN detail produksi
- Sequence diagram
- State diagram
- Relasi database

### 6. **PANDUAN_LENGKAP_BAB3.md**
📍 **Panduan penggunaan**
- Cara pakai semua file
- Template penulisan
- Checklist lengkap
- Tips export diagram

---

## 🎯 Mapping Diagram ke BAB 3

### Sub Bab 3.2.1: Gambar Sistem Saat Ini

**Gambar 3.1: BPMN Sistem Saat Ini (As-Is)**
- File: `BAB3_BPMN_AS_IS_TO_BE.md`
- Bagian: "1. BPMN Sistem Saat Ini (As-Is)"
- Menunjukkan: Proses manual dengan buku catatan

**Tabel 3.2: Area Fungsional** ✅ (Sudah ada di draft Anda)

---

### Sub Bab 3.2.2: Pengembangan Sistem

**Gambar 3.2: BPMN Sistem Usulan (To-Be)**
- File: `BAB3_BPMN_AS_IS_TO_BE.md`
- Bagian: "2. BPMN Sistem Usulan (To-Be)"
- Menunjukkan: Proses otomatis dengan sistem

**Gambar 3.3: Use Case Diagram**
- File: `BAB3_USECASE_DIAGRAM.md`
- Bagian: "Use Case Diagram Lengkap"
- Menunjukkan: Aktor dan fungsi sistem

**Gambar 3.4: Activity Diagram Produksi**
- File: `BAB3_ACTIVITY_CLASS_DIAGRAM.md`
- Bagian: "1. Activity Diagram - Proses Produksi Lengkap"
- Menunjukkan: Alur aktivitas detail

**Gambar 3.5: Entity Relationship Diagram**
- File: `BAB3_ERD_DATABASE.md`
- Bagian: "ERD Lengkap Sistem"
- Menunjukkan: Struktur database

**Gambar 3.6: Class Diagram**
- File: `BAB3_ACTIVITY_CLASS_DIAGRAM.md`
- Bagian: "3. Class Diagram - Struktur Aplikasi"
- Menunjukkan: Struktur class MVC

**Tabel 3.X: Perbandingan Waktu Proses**
- File: `BAB3_BPMN_AS_IS_TO_BE.md`
- Bagian: "Perbandingan Waktu Proses"
- Menunjukkan: Efisiensi sistem baru

---

## 📥 Cara Export Diagram

### Metode 1: Screenshot dari HTML (Termudah!)
1. Buka `LIHAT_BPMN_PRODUKSI.html` di browser
2. Screenshot diagram
3. Crop dan save
4. Insert ke Word

### Metode 2: Via Mermaid Live
1. Buka https://mermaid.live/
2. Copy code dari file .md (yang di dalam ` ```mermaid `)
3. Paste ke editor
4. Download PNG/SVG
5. Insert ke Word

### Metode 3: Via Draw.io
1. Buka https://app.diagrams.net/
2. Import file atau paste code
3. Edit jika perlu
4. Export PNG/PDF
5. Insert ke Word

### Metode 4: Via VS Code
1. Install extension "Markdown Preview Mermaid Support"
2. Buka file .md
3. Ctrl+Shift+V (Preview)
4. Screenshot
5. Insert ke Word

---

## 📝 Contoh Penulisan di Laporan

### Format Gambar:
```
3.2.1 Gambar Sistem Saat Ini

Sistem yang berjalan saat ini masih menggunakan metode 
konvensional dalam pengelolaan bahan baku dan biaya produksi. 
Proses bisnis yang berjalan dapat dilihat pada Gambar 3.1 
berikut.

[INSERT GAMBAR 3.1 DI SINI]

Gambar 3.1 BPMN Sistem Saat Ini (As-Is) - Proses Produksi Manual
Sumber: Hasil Analisis Penulis, 2025

Berdasarkan Gambar 3.1 di atas, terlihat bahwa proses produksi 
masih dilakukan secara manual dengan beberapa tahapan yang 
terpisah. Admin harus melakukan pengecekan stok bahan secara 
manual di buku catatan, menghitung HPP menggunakan kalkulator, 
dan memperbarui stok produk di buku yang berbeda. Proses ini 
memakan waktu dan rentan terhadap kesalahan pencatatan.

Area fungsional bisnis pada sistem saat ini dapat dilihat 
pada Tabel 3.2 berikut.

[INSERT TABEL 3.2 DI SINI]
```

### Format untuk Sistem Usulan:
```
3.2.2 Pengembangan Sistem

Untuk mengatasi permasalahan pada sistem saat ini, dikembangkan 
sistem informasi berbasis web bernama UMKM COE. Alur proses 
bisnis dengan sistem usulan dapat dilihat pada Gambar 3.2 berikut.

[INSERT GAMBAR 3.2 DI SINI]

Gambar 3.2 BPMN Sistem Usulan (To-Be) - Proses Produksi dengan UMKM COE
Sumber: Hasil Analisis Penulis, 2025

Sistem usulan mengotomatisasi seluruh proses produksi mulai dari 
validasi BOM, pengecekan stok real-time, perhitungan HPP otomatis, 
hingga posting jurnal akuntansi. Sistem melakukan validasi di setiap 
tahap untuk mencegah kesalahan input dan memastikan integritas data.

Interaksi pengguna dengan sistem dapat dilihat pada use case diagram 
berikut.

[INSERT GAMBAR 3.3 DI SINI]

Gambar 3.3 Use Case Diagram Sistem UMKM COE
Sumber: Hasil Analisis Penulis, 2025

Use case diagram menunjukkan bahwa terdapat dua aktor utama yaitu 
Admin dan Owner. Admin memiliki akses penuh untuk mengelola master 
data, transaksi, dan laporan. Sedangkan Owner fokus pada monitoring 
bisnis melalui berbagai laporan yang tersedia.

Alur aktivitas detail proses produksi dapat dilihat pada activity 
diagram berikut.

[INSERT GAMBAR 3.4 DI SINI]

Gambar 3.4 Activity Diagram Proses Produksi
Sumber: Hasil Analisis Penulis, 2025

Activity diagram menggambarkan alur lengkap dari login hingga selesai 
produksi, termasuk validasi BOM, pengecekan stok, konsumsi bahan 
dengan metode FIFO, perhitungan biaya, dan posting jurnal akuntansi.

Struktur database sistem dirancang dengan normalisasi hingga 3NF 
untuk menjaga integritas data. Entity Relationship Diagram dapat 
dilihat pada Gambar 3.5 berikut.

[INSERT GAMBAR 3.5 DI SINI]

Gambar 3.5 Entity Relationship Diagram (ERD) Database
Sumber: Hasil Analisis Penulis, 2025

ERD menunjukkan relasi antar tabel dalam database. Terdapat 25+ tabel 
yang saling berelasi dengan kardinalitas yang jelas. Tabel utama 
meliputi master data (produk, bahan baku, COA), transaksi (pembelian, 
penjualan, produksi), dan akuntansi (jurnal, periode COA).

Struktur class aplikasi menggunakan arsitektur MVC (Model-View-Controller) 
dengan tambahan service layer untuk business logic. Class diagram dapat 
dilihat pada Gambar 3.6 berikut.

[INSERT GAMBAR 3.6 DI SINI]

Gambar 3.6 Class Diagram Struktur Aplikasi
Sumber: Hasil Analisis Penulis, 2025

Class diagram menunjukkan pemisahan yang jelas antara controller, 
service, dan model layer. Service layer seperti StockService dan 
JournalService menangani business logic yang kompleks, sementara 
controller fokus pada handling HTTP request/response.

Perbandingan efisiensi waktu proses antara sistem lama dan sistem 
baru dapat dilihat pada Tabel 3.X berikut.

[INSERT TABEL PERBANDINGAN WAKTU]

Berdasarkan tabel di atas, terlihat bahwa sistem baru memberikan 
peningkatan efisiensi yang signifikan dengan penghematan waktu 
85-99% pada berbagai proses bisnis.
```

---

## ✅ Checklist Final

- [ ] Export Gambar 3.1 (BPMN As-Is)
- [ ] Export Gambar 3.2 (BPMN To-Be)
- [ ] Export Gambar 3.3 (Use Case)
- [ ] Export Gambar 3.4 (Activity Diagram)
- [ ] Export Gambar 3.5 (ERD)
- [ ] Export Gambar 3.6 (Class Diagram)
- [ ] Buat Tabel Perbandingan Waktu
- [ ] Tulis penjelasan setiap gambar
- [ ] Cek penomoran gambar & tabel
- [ ] Cek referensi silang
- [ ] Proofread

---

## 🎉 Kesimpulan

Anda sekarang memiliki:
✅ 6 file dokumentasi lengkap
✅ 10+ diagram siap pakai
✅ Penjelasan detail
✅ Template penulisan
✅ Panduan export
✅ Contoh penulisan laporan

**Semua materi BAB 3 sudah lengkap dan siap digunakan!**

Tinggal export diagram ke gambar dan masukkan ke dokumen Word Anda! 🚀

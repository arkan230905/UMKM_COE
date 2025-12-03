# 📊 Panduan Lengkap Activity Diagram Login

## 🎯 File yang Tersedia

1. **ACTIVITY_DIAGRAM_LOGIN.md** - Dokumentasi lengkap dengan penjelasan
2. **ACTIVITY_LOGIN.puml** - Diagram standar (tanpa swimlane)
3. **ACTIVITY_LOGIN_SWIMLANE.puml** - Diagram dengan swimlane (Pengguna & Sistem)

---

## 🚀 Cara Menggunakan

### Metode 1: Online PlantUML (Paling Mudah)

1. **Buka website PlantUML:**
   ```
   http://www.plantuml.com/plantuml/uml/
   ```

2. **Copy isi file `.puml`:**
   - Buka `ACTIVITY_LOGIN.puml` atau `ACTIVITY_LOGIN_SWIMLANE.puml`
   - Copy semua isinya (Ctrl+A, Ctrl+C)

3. **Paste ke PlantUML Web:**
   - Paste di text area
   - Diagram akan muncul otomatis

4. **Download hasil:**
   - Klik "PNG" untuk download gambar
   - Klik "SVG" untuk vector (bisa di-edit)
   - Klik "PDF" untuk dokumen

---

### Metode 2: VS Code (Recommended untuk Edit)

1. **Install Extension:**
   - Buka VS Code
   - Tekan `Ctrl+Shift+X`
   - Cari "PlantUML"
   - Install extension by jebbs

2. **Install Java (diperlukan):**
   - Download Java JRE: https://www.java.com/download/
   - Install dan restart VS Code

3. **Buka file `.puml`:**
   - Buka `ACTIVITY_LOGIN.puml`
   - Tekan `Alt+D` untuk preview
   - Atau klik kanan > "Preview Current Diagram"

4. **Export diagram:**
   - Klik kanan pada preview
   - Pilih "Export Current Diagram"
   - Pilih format: PNG, SVG, PDF

---

### Metode 3: Draw.io (Untuk Edit Manual)

1. **Buka Draw.io:**
   ```
   https://app.diagrams.net/
   ```

2. **Import PlantUML:**
   - Klik "Insert" > "Advanced" > "PlantUML..."
   - Paste kode dari file `.puml`
   - Klik "Insert"

3. **Edit manual:**
   - Diagram akan muncul
   - Bisa di-edit seperti biasa
   - Save sebagai `.drawio` atau export PNG/PDF

---

### Metode 4: IntelliJ IDEA / Android Studio

1. **Install Plugin:**
   - File > Settings > Plugins
   - Cari "PlantUML integration"
   - Install dan restart

2. **Buka file `.puml`:**
   - Diagram akan preview otomatis
   - Klik kanan > "Copy/Export Diagram"

---

## 📝 Cara Edit Diagram

### Mengubah Warna

```plantuml
skinparam activity {
  BackgroundColor #FFF8DC  // Ganti warna background
  BorderColor #8B4513      // Ganti warna border
}
```

### Menambah Activity Baru

```plantuml
:Activity baru;
note right
  Penjelasan activity
end note
```

### Menambah Decision

```plantuml
if (Kondisi?) then (ya)
  :Activity jika ya;
else (tidak)
  :Activity jika tidak;
endif
```

### Menambah Swimlane

```plantuml
|Actor Baru|
:Activity di swimlane baru;
```

---

## 🎨 Customisasi untuk Skripsi

### Ganti Judul

```plantuml
title **Judul Anda**\n**Sub Judul**
```

### Ganti Warna Tema

**Tema Biru:**
```plantuml
skinparam activity {
  BackgroundColor #E6F3FF
  BorderColor #0066CC
}
```

**Tema Hijau:**
```plantuml
skinparam activity {
  BackgroundColor #E8F5E9
  BorderColor #2E7D32
}
```

**Tema Merah:**
```plantuml
skinparam activity {
  BackgroundColor #FFEBEE
  BorderColor #C62828
}
```

### Tambah Logo/Watermark

```plantuml
header
  **Nama Universitas**
  Fakultas Teknik
end header

footer
  Dibuat oleh: [Nama Anda]
  Tanggal: %date%
end footer
```

---

## 📤 Export untuk Dokumen

### Untuk Word

1. Export sebagai **PNG** (resolusi tinggi)
2. Insert ke Word: Insert > Pictures
3. Atur size: klik gambar > Format > Size

### Untuk LaTeX

1. Export sebagai **PDF** atau **SVG**
2. Include di LaTeX:
   ```latex
   \begin{figure}[h]
     \centering
     \includegraphics[width=0.8\textwidth]{activity_login.pdf}
     \caption{Activity Diagram Proses Login}
     \label{fig:activity_login}
   \end{figure}
   ```

### Untuk PowerPoint

1. Export sebagai **SVG** (bisa di-edit)
2. Insert ke PowerPoint
3. Ungroup untuk edit (klik kanan > Group > Ungroup)

---

## ✅ Checklist Kualitas Diagram

Pastikan diagram Anda memenuhi:

- [ ] **Judul jelas** - Sesuai dengan sistem
- [ ] **Start dan Stop** - Ada titik awal dan akhir
- [ ] **Alur logis** - Mengikuti proses sebenarnya
- [ ] **Decision jelas** - Kondisi ya/tidak terdefinisi
- [ ] **Note informatif** - Penjelasan teknis ada
- [ ] **Warna konsisten** - Tidak terlalu ramai
- [ ] **Font terbaca** - Ukuran minimal 10pt
- [ ] **Resolusi tinggi** - Minimal 300 DPI untuk print

---

## 🔧 Troubleshooting

### Diagram tidak muncul di VS Code

**Solusi:**
1. Pastikan Java sudah terinstall
2. Restart VS Code
3. Check extension settings: `plantuml.java`

### Error "Syntax Error"

**Solusi:**
1. Cek tanda `;` di akhir setiap activity
2. Pastikan `if` dan `endif` berpasangan
3. Cek tanda kutip `"` sudah lengkap

### Diagram terlalu besar

**Solusi:**
```plantuml
skinparam dpi 150  // Kurangi DPI
skinparam fontSize 9  // Kecilkan font
```

### Teks terpotong

**Solusi:**
```plantuml
skinparam wrapWidth 200  // Atur lebar wrap
skinparam maxMessageSize 150  // Atur max message
```

---

## 📚 Referensi Tambahan

### PlantUML Documentation
- Official: https://plantuml.com/
- Activity Diagram: https://plantuml.com/activity-diagram-beta

### Tutorial Video
- YouTube: "PlantUML Tutorial"
- YouTube: "Activity Diagram with PlantUML"

### Contoh Diagram Lain
- Use Case Diagram: https://plantuml.com/use-case-diagram
- Sequence Diagram: https://plantuml.com/sequence-diagram
- Class Diagram: https://plantuml.com/class-diagram

---

## 💡 Tips untuk Skripsi

1. **Konsisten dengan kode:**
   - Pastikan nama route, controller sesuai
   - Validasi sesuai dengan yang di kode
   - Error message sama dengan sistem

2. **Tambahkan penjelasan:**
   - Setiap decision harus ada note
   - Jelaskan teknologi yang digunakan
   - Cantumkan nama file terkait

3. **Buat variasi:**
   - Diagram standar untuk overview
   - Diagram swimlane untuk detail
   - Diagram dengan note untuk teknis

4. **Dokumentasi lengkap:**
   - Simpan source `.puml`
   - Export PNG untuk dokumen
   - Export SVG untuk presentasi

---

## 🎓 Contoh Penggunaan di Skripsi

### BAB 3 - Analisis dan Perancangan

**3.4.1 Activity Diagram**

Activity Diagram menggambarkan alur aktivitas dalam sistem. Berikut adalah Activity Diagram untuk proses login:

[Insert Gambar: ACTIVITY_LOGIN.png]

**Gambar 3.X Activity Diagram Proses Login**

Penjelasan alur:
1. Pengguna membuka website sistem
2. Sistem menampilkan halaman login
3. Pengguna mengisi form dengan email dan password
4. Sistem melakukan validasi input
5. Jika valid, sistem mengecek kredensial di database
6. Jika kredensial benar, sistem membuat session
7. Pengguna diarahkan ke dashboard

---

## ✨ Kesimpulan

Anda sekarang punya:
- ✅ 3 file diagram siap pakai
- ✅ Panduan lengkap cara menggunakan
- ✅ Tips customisasi untuk skripsi
- ✅ Troubleshooting common issues

**Diagram sudah sesuai dengan kode sistem Anda dan siap digunakan untuk dokumentasi atau skripsi!** 🎉

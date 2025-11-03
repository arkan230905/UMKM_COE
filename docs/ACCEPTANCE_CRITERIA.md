# Acceptance Criteria - Modul Aset

## Overview
Dokumen ini berisi acceptance criteria untuk memverifikasi bahwa modul Aset berfungsi sempurna sesuai spesifikasi.

---

## 1. Master Data Aset

### AC 1.1: Tambah Aset Baru
**Given** User membuka halaman Aset di Filament
**When** User mengklik tombol "Create" dan mengisi form dengan data:
- Nama Aset: "Kursi Salon"
- Kategori: "Furniture & Fixtures"
- Tanggal Perolehan: 02/11/2022
- Harga Perolehan: 4.000.000
- Nilai Sisa: 2.500.000
- Umur Ekonomis: 4 tahun
- Metode Penyusutan: Garis Lurus
- Lokasi: Salon Utama
- Nomor Serial: SL-001

**Then**
- Aset berhasil disimpan
- Kode aset auto-generated dengan format AST-YYYYMM-XXXX
- Nilai buku otomatis terisi = Harga Perolehan (4.000.000)
- Akumulasi penyusutan = 0
- Status = "Aktif"
- Data muncul di tabel dengan benar

### AC 1.2: Edit Aset
**Given** Aset "Kursi Salon" sudah ada di sistem
**When** User mengklik tombol "Edit" dan mengubah:
- Lokasi: "Salon Cabang"
- Status: "Tidak Aktif"

**Then**
- Data berhasil diupdate
- Perubahan langsung terlihat di tabel
- Audit trail mencatat siapa yang mengubah dan kapan

### AC 1.3: Lihat Detail Aset
**Given** Aset "Kursi Salon" sudah ada
**When** User mengklik tombol "View" pada aset

**Then**
- Halaman detail menampilkan:
  - Semua informasi aset
  - Depreciation schedule (jika ada)
  - Grafik nilai buku vs akumulasi penyusutan
  - Tombol untuk generate schedule

### AC 1.4: Validasi Penghapusan Aset
**Given** Aset "Kursi Salon" dengan akumulasi penyusutan = 0
**When** User mengklik tombol "Delete"

**Then**
- Aset berhasil dihapus

**Given** Aset "Kursi Salon" dengan akumulasi penyusutan > 0
**When** User mencoba menghapus aset

**Then**
- Sistem menampilkan error: "Tidak bisa menghapus aset yang sudah memiliki akumulasi penyusutan"
- Aset tidak dihapus

### AC 1.5: Search & Filter Aset
**Given** Ada 10 aset di sistem
**When** User menggunakan filter:
- Status: "Aktif"
- Metode Penyusutan: "Garis Lurus"
- Search: "Kursi"

**Then**
- Tabel hanya menampilkan aset yang sesuai filter
- Pagination berfungsi dengan benar

---

## 2. Perhitungan Penyusutan

### AC 2.1: Metode Garis Lurus - Kursi Salon
**Given** Aset Kursi Salon dengan:
- Harga: 4.000.000
- Nilai Sisa: 2.500.000
- Umur: 4 tahun
- Metode: Garis Lurus

**When** Generate schedule bulanan untuk 12 bulan

**Then**
- Beban penyusutan per bulan = 31.250
- Beban penyusutan per tahun = 375.000
- Akumulasi setelah 12 bulan = 375.000
- Nilai buku setelah 12 bulan = 3.625.000
- Perhitungan konsisten untuk setiap bulan

### AC 2.2: Metode Garis Lurus - Gedung
**Given** Aset Gedung dengan:
- Harga: 30.000.000
- Nilai Sisa: 20.000.000
- Umur: 4 tahun
- Metode: Garis Lurus

**When** Generate schedule tahunan untuk 4 tahun

**Then**
- Beban penyusutan per tahun = 2.500.000
- Akumulasi setelah 4 tahun = 10.000.000
- Nilai buku setelah 4 tahun = 20.000.000 (= nilai sisa)
- Nilai buku tidak boleh kurang dari nilai sisa

### AC 2.3: Metode Saldo Menurun
**Given** Aset dengan:
- Harga: 10.000.000
- Nilai Sisa: 4.000.000
- Umur: 4 tahun
- Metode: Saldo Menurun

**When** Generate schedule

**Then**
- Persentase penyusutan otomatis dihitung
- Beban penyusutan menurun setiap tahun
- Tahun 1: ~2.649.000
- Tahun 2: ~1.946.000
- Tahun 3: ~1.430.000
- Tahun 4: ~1.052.000 (dengan adjustment agar = nilai sisa)

### AC 2.4: Metode Sum of Years Digits
**Given** Aset dengan:
- Harga: 10.000.000
- Nilai Sisa: 4.000.000
- Umur: 4 tahun
- Metode: Sum of Years Digits

**When** Generate schedule

**Then**
- Total digit = 4 × 5 / 2 = 10
- Tahun 1: (4/10) × 6.000.000 = 2.400.000
- Tahun 2: (3/10) × 6.000.000 = 1.800.000
- Tahun 3: (2/10) × 6.000.000 = 1.200.000
- Tahun 4: (1/10) × 6.000.000 = 600.000
- Total akumulasi = 6.000.000 (nilai terdepresiasi)

---

## 3. Depreciation Schedule

### AC 3.1: Generate Schedule Preview
**Given** Aset "Kursi Salon" sudah dibuat
**When** User mengklik "Generate Schedule" dan mengisi:
- Tanggal Mulai: 02/11/2022
- Tanggal Akhir: 02/11/2026
- Periodisitas: Bulanan

**Then**
- Sistem menampilkan preview schedule 48 bulan
- Setiap baris menampilkan: periode, nilai awal, beban, akumulasi, nilai buku
- Data belum disimpan ke database

### AC 3.2: Save Schedule
**Given** Preview schedule sudah ditampilkan
**When** User mengklik tombol "Save Schedule"

**Then**
- Semua baris schedule disimpan ke tabel depreciation_schedules
- Status setiap schedule = "draft"
- Sistem menampilkan notifikasi: "Schedule berhasil disimpan (48 records)"

### AC 3.3: View Schedule Table
**Given** Schedule sudah disimpan
**When** User melihat halaman depreciation schedule

**Then**
- Tabel menampilkan semua schedule dengan kolom:
  - Periode
  - Nilai Awal
  - Beban Penyusutan
  - Akumulasi Penyusutan
  - Nilai Buku
  - Status
- Pagination berfungsi
- Dapat filter berdasarkan status (draft, posted, reversed)

---

## 4. Posting Jurnal Otomatis

### AC 4.1: Post Schedule & Generate Jurnal
**Given** Schedule "Kursi Salon" bulan November 2022 dengan status "draft"
**When** User mengklik tombol "Post" pada schedule

**Then**
- Jurnal otomatis dibuat dengan:
  - Nomor Jurnal: JUR-YYYYMM-XXXX (auto-generated)
  - Tanggal: 30/11/2022 (periode akhir)
  - Deskripsi: "Penyusutan Aset: Kursi Salon (AST-202511-0001)"
  - Debit: Beban Penyusutan = 31.250
  - Kredit: Akumulasi Penyusutan = 31.250
- Schedule status berubah menjadi "posted"
- Aset nilai_buku dan akumulasi_penyusutan terupdate
- Jurnal status = "posted"
- Audit trail mencatat posted_by dan posted_at

### AC 4.2: Multiple Schedule Posting
**Given** 12 schedule untuk Kursi Salon (1 tahun)
**When** User post semua schedule satu per satu

**Then**
- Setiap post membuat jurnal terpisah
- Total akumulasi penyusutan = 375.000
- Nilai buku = 3.625.000
- 12 jurnal terpisah di sistem

### AC 4.3: Reverse Schedule & Jurnal
**Given** Schedule sudah di-post dengan jurnal
**When** User mengklik "Reverse" dan memberikan alasan: "Koreksi data"

**Then**
- Jurnal reverse otomatis dibuat:
  - Nomor: JUR-YYYYMM-XXXX (baru)
  - Deskripsi: "Reverse: Penyusutan Aset..."
  - Debit: Akumulasi Penyusutan = 31.250 (terbalik)
  - Kredit: Beban Penyusutan = 31.250 (terbalik)
- Schedule status berubah menjadi "reversed"
- Aset akumulasi_penyusutan berkurang 31.250
- Nilai buku kembali ke nilai sebelumnya
- Audit trail mencatat reversed_by, reversed_at, dan alasan

---

## 5. UI & UX

### AC 5.1: Form Aset
**Given** User membuka form tambah/edit aset
**Then**
- Form terorganisir dalam 4 section:
  1. Informasi Dasar (kode, nama, kategori, COA, serial, lokasi)
  2. Nilai & Tanggal (tanggal, harga, nilai sisa, nilai buku)
  3. Penyusutan (umur, metode, persentase, akumulasi)
  4. Status & Keterangan
- Field yang read-only: kode_aset, nilai_buku, akumulasi_penyusutan
- Validasi real-time
- Help text untuk field yang kompleks

### AC 5.2: Tabel Aset
**Given** User melihat daftar aset
**Then**
- Kolom: Kode, Nama, Kategori, Harga, Nilai Buku, Akumulasi, Status
- Status ditampilkan dengan badge warna:
  - Hijau: Aktif
  - Kuning: Tidak Aktif
  - Merah: Dihapus
- Sortable pada semua kolom
- Searchable pada Kode, Nama, Kategori
- Action buttons: View, Edit, Delete

### AC 5.3: Detail Aset Page
**Given** User membuka halaman detail aset
**Then**
- Menampilkan:
  - Informasi lengkap aset
  - Grafik nilai buku vs akumulasi penyusutan (line chart)
  - Tabel depreciation schedule (jika ada)
  - Tombol: Generate Schedule, Edit, Back

### AC 5.4: Depreciation Schedule Page
**Given** User membuka halaman depreciation schedule
**Then**
- Menampilkan:
  - Tabel schedule dengan kolom: Periode, Nilai Awal, Beban, Akumulasi, Nilai Buku, Status
  - Filter: Status, Aset, Periode
  - Action buttons: View, Post, Reverse (conditional berdasarkan status)
  - Export to CSV button

---

## 6. API Endpoints

### AC 6.1: GET /api/asets
**Given** Ada 3 aset di sistem
**When** Call GET /api/asets

**Then**
- Response 200 OK
- Return array of asets dengan pagination
- Setiap aset memiliki: id, kode_aset, nama_aset, kategori, harga_perolehan, nilai_buku, akumulasi_penyusutan, status

### AC 6.2: POST /api/asets
**Given** User authenticated
**When** Call POST /api/asets dengan valid data

**Then**
- Response 201 Created
- Aset berhasil dibuat
- Kode aset auto-generated

### AC 6.3: POST /api/asets/{id}/generate-schedule
**Given** Aset dengan id=1
**When** Call POST dengan tanggal_mulai, tanggal_akhir, periodisitas

**Then**
- Response 200 OK
- Return array of schedules (preview, belum disimpan)

### AC 6.4: POST /api/asets/{id}/save-schedule
**Given** Aset dengan id=1
**When** Call POST dengan parameter schedule

**Then**
- Response 200 OK
- Schedule disimpan ke database
- Return message: "Schedule berhasil disimpan (N records)"

### AC 6.5: POST /api/depreciation-schedules/{id}/post
**Given** Schedule dengan id=1, status=draft
**When** Call POST

**Then**
- Response 200 OK
- Jurnal otomatis dibuat
- Schedule status = posted
- Return updated schedule data

### AC 6.6: POST /api/depreciation-schedules/{id}/reverse
**Given** Schedule dengan id=1, status=posted
**When** Call POST dengan alasan

**Then**
- Response 200 OK
- Reverse jurnal dibuat
- Schedule status = reversed
- Return updated schedule data

---

## 7. Role-Based Access Control

### AC 7.1: Owner Access
**Given** User dengan role "Owner"
**When** User mengakses modul Aset

**Then**
- Dapat view semua aset
- Dapat create, edit, delete aset
- Dapat generate, post, reverse schedule
- Dapat view audit trail

### AC 7.2: Pegawai Access
**Given** User dengan role "Pegawai"
**When** User mengakses modul Aset

**Then**
- Dapat view semua aset
- Dapat create aset (dengan approval?)
- Tidak dapat delete aset
- Tidak dapat post/reverse schedule
- Dapat view schedule

---

## 8. Audit Trail

### AC 8.1: Create Audit
**Given** User membuat aset baru
**When** Aset disimpan

**Then**
- Tabel asets mencatat: created_by, created_at
- Audit trail menampilkan: "User X membuat aset Kursi Salon pada 02/11/2025 12:00"

### AC 8.2: Update Audit
**Given** User mengedit aset
**When** Perubahan disimpan

**Then**
- Tabel asets mencatat: updated_by, updated_at
- Audit trail menampilkan: "User X mengubah aset Kursi Salon pada 02/11/2025 13:00"

### AC 8.3: Post Audit
**Given** User post schedule
**When** Schedule di-post

**Then**
- depreciation_schedules mencatat: posted_by, posted_at
- Audit trail menampilkan: "User X mempost schedule Kursi Salon periode Nov 2022 pada 02/11/2025 14:00"

---

## 9. Data Validation

### AC 9.1: Required Fields
**Given** User membuka form tambah aset
**When** User mencoba submit tanpa mengisi field required

**Then**
- Sistem menampilkan error untuk setiap field kosong
- Form tidak bisa disubmit

### AC 9.2: Numeric Validation
**Given** User mengisi Harga Perolehan
**When** User memasukkan nilai non-numeric atau negatif

**Then**
- Sistem menampilkan error: "Harga Perolehan harus berupa angka positif"

### AC 9.3: Date Validation
**Given** User mengisi Tanggal Perolehan
**When** User memasukkan tanggal di masa depan

**Then**
- Sistem menampilkan warning atau error sesuai business rule

### AC 9.4: Nilai Sisa Validation
**Given** User mengisi Nilai Sisa
**When** User memasukkan nilai sisa > harga perolehan

**Then**
- Sistem menampilkan error: "Nilai Sisa tidak boleh lebih besar dari Harga Perolehan"

---

## 10. Edge Cases

### AC 10.1: Aset dengan Nilai Sisa = 0
**Given** User membuat aset dengan nilai sisa = 0
**When** Generate schedule

**Then**
- Beban penyusutan = harga perolehan / umur
- Nilai buku akhir = 0
- Sistem berfungsi normal

### AC 10.2: Aset dengan Umur 1 Tahun
**Given** User membuat aset dengan umur ekonomis = 1 tahun
**When** Generate schedule bulanan

**Then**
- Beban penyusutan per bulan = (harga - nilai sisa) / 12
- Schedule berhasil dibuat untuk 12 bulan

### AC 10.3: Partial Month Depreciation
**Given** Aset perolehan tanggal 15/11/2022, generate schedule mulai 02/11/2022
**When** Generate schedule

**Then**
- Sistem menghitung dengan benar
- Periode pertama: 02/11 - 30/11 (29 hari)
- Beban penyusutan disesuaikan dengan jumlah hari (jika ada business rule)

### AC 10.4: Multiple Reverse
**Given** Schedule sudah di-post dan di-reverse
**When** User mencoba reverse lagi

**Then**
- Sistem menampilkan error: "Schedule sudah di-reverse, tidak bisa di-reverse lagi"

---

## 11. Performance & Scalability

### AC 11.1: Large Dataset
**Given** Sistem memiliki 1000 aset
**When** User membuka halaman daftar aset

**Then**
- Halaman load dalam < 2 detik
- Pagination berfungsi dengan baik
- Search/filter responsif

### AC 11.2: Schedule Generation
**Given** Aset dengan umur 10 tahun
**When** Generate schedule bulanan (120 bulan)

**Then**
- Proses selesai dalam < 5 detik
- Semua 120 record berhasil disimpan

---

## 12. Data Integrity

### AC 12.1: Concurrent Update
**Given** 2 user membuka form edit aset yang sama
**When** User 1 save, kemudian User 2 save

**Then**
- Sistem menampilkan warning: "Data telah diubah oleh user lain"
- User 2 harus refresh dan edit ulang

### AC 12.2: Transaction Rollback
**Given** User post schedule
**When** Terjadi error saat membuat jurnal

**Then**
- Transaksi rollback
- Schedule tetap status "draft"
- Aset tidak terupdate

---

## Testing Checklist

- [ ] Semua AC 1.x (Master Data) terverifikasi
- [ ] Semua AC 2.x (Perhitungan) terverifikasi
- [ ] Semua AC 3.x (Schedule) terverifikasi
- [ ] Semua AC 4.x (Jurnal) terverifikasi
- [ ] Semua AC 5.x (UI/UX) terverifikasi
- [ ] Semua AC 6.x (API) terverifikasi
- [ ] Semua AC 7.x (RBAC) terverifikasi
- [ ] Semua AC 8.x (Audit) terverifikasi
- [ ] Semua AC 9.x (Validation) terverifikasi
- [ ] Semua AC 10.x (Edge Cases) terverifikasi
- [ ] Semua AC 11.x (Performance) terverifikasi
- [ ] Semua AC 12.x (Data Integrity) terverifikasi

---

## Sign-Off

- **QA Lead**: _________________ Date: _______
- **Product Owner**: _________________ Date: _______
- **Development Lead**: _________________ Date: _______

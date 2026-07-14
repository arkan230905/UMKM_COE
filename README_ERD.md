# ERD (Entity Relationship Diagram) - Notasi Chen
## Sistem Pengelolaan Biaya Produksi dan HPP

---

## 📁 FILE YANG TELAH DIBUAT

### 1. **STRUKTUR_ENTITAS_LENGKAP_DENGAN_ATRIBUT.md**
Dokumentasi lengkap berisi:
- ✅ Struktur 14 entitas inti dengan semua atribut
- ✅ Primary Key (PK) dan Foreign Key (FK) lengkap
- ✅ Tipe data setiap atribut
- ✅ Penjelasan 20+ relasi antar entitas
- ✅ Catatan tentang tabel HPP unified

**📖 Gunakan file ini untuk:**
- Referensi lengkap struktur database
- Implementasi database
- Dokumentasi laporan

---

### 2. **ERD_Lengkap_Dengan_Atribut.drawio**
File ERD dengan notasi Chen yang lengkap, mencakup:
- ✅ 14 entitas inti (companies, users, produks, bahan_bakus, kualifikasis, accounts, biaya_bahan_baku, btkls, bops, komponen_bops, bop_proses/hpp, produksis, produksi_details, stock_movements, jurnal_umum)
- ✅ Semua atribut dalam bentuk oval (ellipse)
- ✅ Primary Key dengan garis bawah
- ✅ Foreign Key ditandai dengan (FK)
- ✅ 20 relasi (diamond shapes) dengan kardinalitas (1 dan n)
- ✅ Warna-coding: Master Data (hijau), Input Biaya (kuning), HPP (merah), Transaksi (ungu), Laporan (orange)
- ✅ Font size 20px untuk tulisan di entitas
- ✅ Font size 18px untuk atribut
- ✅ Annotations dan catatan penting

**🎨 Cara membuka:**
1. Buka https://app.diagrams.net
2. File → Open From → Device
3. Pilih file `ERD_Lengkap_Dengan_Atribut.drawio`
4. Edit atau export ke PNG/PDF

---

## 📊 RINGKASAN ENTITAS

### Master Data (6 entitas - Hijau)
1. **companies** - Data perusahaan (multi-tenant)
2. **users** - Pengguna sistem
3. **produks** - Master produk yang diproduksi
4. **bahan_bakus** - Master bahan baku
5. **kualifikasis** (jabatan) - Master jabatan/kualifikasi BTKL
6. **accounts** (COA) - Chart of Accounts

### Input Biaya (5 entitas - Kuning)
7. **biaya_bahan_baku** - Pencatatan BBB per produk
8. **kualifikasi_detail** - Detail bahan baku yang digunakan (opsional, bisa merged ke biaya_bahan_baku)
9. **btkls** - Biaya Tenaga Kerja Langsung per proses
10. **bops** - Master Biaya Overhead Pabrik
11. **komponen_bops** - Detail komponen BOP (listrik, gas, dll)

### HPP - Unified (1 entitas - Merah)
12. **bop_proses** - Kalkulasi HPP (menyimpan hasil BBB + BTKL + BOP)
    - Alternatif: Bisa dibuat tabel `hpp` terpisah yang unified

### Transaksi Produksi (2 entitas - Ungu)
13. **produksis** - Header transaksi produksi
14. **produksi_details** - Detail bahan yang digunakan dalam produksi

### Laporan (2 entitas - Orange)
15. **stock_movements** - Mutasi stok bahan baku/produk
16. **jurnal_umum** - Jurnal akuntansi (auto-posting)

---

## 🔗 RELASI UTAMA (20 Relasi)

### Master Data
1. companies (1) ──── mempekerjakan ──── (n) users
2. users (1) ──── mengelola ──── (n) produks

### Input Biaya
3. produks (1) ──── memiliki_biaya_bbb ──── (n) biaya_bahan_baku
4. bahan_bakus (1) ──── digunakan_dalam ──── (n) biaya_bahan_baku
5. produks (1) ──── memiliki_biaya_btkl ──── (n) btkls
6. kualifikasis (1) ──── menentukan_tarif ──── (n) btkls
7. accounts (1) ──── akun_bop ──── (n) bops
8. bops (1) ──── memiliki_komponen ──── (n) komponen_bops

### Kalkulasi HPP
9. biaya_bahan_baku (1) ──── menghitung_HPP_BBB ──── (n) bop_proses (hpp)
10. btkls (1) ──── menghitung_HPP_BTKL ──── (n) bop_proses (hpp)
11. bops (1) ──── menghitung_HPP_BOP ──── (n) bop_proses (hpp)
12. produks (1) ──── memiliki_hpp ──── (n) bop_proses (hpp)

### Transaksi Produksi
13. bop_proses/hpp (1) ──── digunakan_dalam ──── (n) produksis
14. produks (1) ──── diproduksi ──── (n) produksis
15. produksis (1) ──── memiliki_detail ──── (n) produksi_details
16. bahan_bakus (1) ──── digunakan ──── (n) produksi_details

### Laporan
17. bahan_bakus (1) ──── mutasi_stok ──── (n) stock_movements
18. produksis (1) ──── mengkonsumsi ──── (n) stock_movements
19. accounts (1) ──── posting_ke ──── (n) jurnal_umum
20. produksis (1) ──── autoposting ──── (n) jurnal_umum

---

## 🎯 FLOW INTI PROSES

```
┌─────────────────┐
│  INPUT BIAYA    │
│  ├─ BBB         │
│  ├─ BTKL        │
│  └─ BOP         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  KALKULASI HPP  │
│  (bop_proses)   │
│  BBB+BTKL+BOP   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  TRANSAKSI      │
│  PRODUKSI       │
│  (uses HPP)     │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│  AUTO-POSTING           │
│  ├─ Jurnal Umum        │
│  └─ Stock Movements    │
└─────────────────────────┘
```

---

## ✅ CATATAN PENTING

### Notasi Chen yang Digunakan:
- **Entitas** = Kotak persegi panjang
- **Atribut** = Oval/ellipse terhubung ke entitas
- **Primary Key** = Atribut dengan garis bawah (&lt;u&gt;id&lt;/u&gt;)
- **Foreign Key** = Ditandai dengan (FK)
- **Relasi** = Diamond (belah ketupat)
- **Kardinalitas** = 1 atau n pada garis penghubung

### Tentang Tabel HPP:
Database actual tidak memiliki tabel `hpp` terpisah yang unified. HPP dihitung dari:
- **BBB**: `biaya_bahan_baku` (sum subtotal)
- **BTKL**: `btkls` (biaya_per_produk)
- **BOP**: `bop_proses` (total_bop_per_produk)

Total HPP = BBB + BTKL + BOP, kemudian disimpan di `produks.harga_pokok`

### Simplifikasi vs Detail:
- **ERD Inti (14 entitas)**: Fokus pada alur HPP dan Transaksi Produksi
- **Database Actual (27+ tabel)**: Mencakup semua fitur sistem lengkap

---

## 📝 SUMBER DATA

File ERD ini dibuat berdasarkan:
- **Source**: `eadt_umkm.sql` (SQL dump database)
- **Date**: 2026-07-14
- **Created by**: Kiro AI Assistant

---

## 🚀 CARA MENGGUNAKAN

### Untuk Laporan Tugas Akhir:
1. Buka file **ERD_Lengkap_Dengan_Atribut.drawio** di diagrams.net
2. Export ke PNG dengan resolusi tinggi (300 DPI)
3. Masukkan ke laporan BAB 3 (Perancangan)
4. Gunakan file **STRUKTUR_ENTITAS_LENGKAP_DENGAN_ATRIBUT.md** untuk penjelasan paragraf

### Untuk Presentasi:
1. Export ERD ke PDF atau PNG
2. Fokuskan pada flow: Input → Kalkulasi → Transaksi → Laporan
3. Highlight warna-coding untuk mempermudah pemahaman

### Untuk Implementasi:
1. Gunakan file **STRUKTUR_ENTITAS_LENGKAP_DENGAN_ATRIBUT.md**
2. Implementasikan struktur tabel sesuai atribut dan FK yang tercantum
3. Pastikan foreign key constraints diterapkan

---

## 📞 SUPPORT

Jika ada pertanyaan atau perlu modifikasi ERD:
1. Buka file DrawIO dan edit langsung
2. Simpan dan export ulang
3. Pastikan notasi Chen tetap konsisten (diamond untuk relasi, oval untuk atribut)

---

**© 2026 - ERD untuk Sistem Pengelolaan Biaya Produksi dan HPP**  
**Notasi: Peter Chen, 1976**

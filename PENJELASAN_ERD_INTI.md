# ENTITAS INTI untuk HPP dan Transaksi Produksi

## RINGKASAN
ERD yang disederhanakan ini fokus pada **INTI PROSES** dari perhitungan HPP dan transaksi produksi dengan **14 entitas** (bukan 27 entitas lengkap).

---

## FLOW INTI PROSES

```
INPUT BIAYA → KALKULASI HPP → TRANSAKSI PRODUKSI → LAPORAN
```

### 1️⃣ INPUT BIAYA (5 entitas)
- **biaya_bahan_baku** (kualifikasi) - Header biaya BBB per produk per periode
- **kualifikasi_detail** - Detail bahan baku yang digunakan (qty, harga, subtotal)
- **btkl** - Biaya Tenaga Kerja Langsung per produk per periode
- **bop** - Master Biaya Overhead Pabrik
- **komponen_bop** - Detail komponen BOP (budget bulanan)

### 2️⃣ KALKULASI HPP (1 entitas - UNIFIED)
- **hpp** - **Entitas tunggal** yang menyimpan hasil kalkulasi HPP dari BBB + BTKL + BOP
  - Kolom: `total_bbb`, `hpp_bbb_per_unit`
  - Kolom: `total_btkl`, `hpp_btkl_per_unit`
  - Kolom: `total_bop`, `hpp_bop_per_unit`
  - Kolom: `hpp_total_per_unit` (BBB + BTKL + BOP)

**PERBEDAAN dari versi lengkap:**
- Versi lengkap: 3 tabel terpisah (`hpp_bbb`, `hpp_btkl`, `hpp_bop`)
- Versi INTI: 1 tabel `hpp` yang menggabungkan ketiganya

### 3️⃣ TRANSAKSI PRODUKSI (2 entitas)
- **proses_produksi** - Header transaksi produksi (tanggal, shift, status)
- **produksi_detail** - Detail produksi yang **MENGGUNAKAN** HPP yang sudah dihitung
  - Kolom: `qty_produksi`, `hpp_per_unit` (diambil dari tabel `hpp`)
  - Kolom: `total_hpp` = qty × hpp_per_unit

**CATATAN PENTING:**
- Transaksi produksi **TIDAK MENGHITUNG** HPP
- Transaksi produksi **MENGAMBIL** HPP yang sudah jadi dari tabel `hpp`
- Flow: HPP (kalkulasi) → Transaksi Produksi (pakai HPP)

### 4️⃣ LAPORAN (2 entitas)
- **stock_movements** - Mutasi stok bahan baku (in/out)
- **jurnal_umum** - Auto-posting dari transaksi produksi dan pembayaran beban

---

## ENTITAS PENDUKUNG (6 entitas)

### Master Data Umum
- **companies** - Data perusahaan (multi-tenant)
- **users** - Pengguna sistem (created_by)
- **produk** - Master data produk yang diproduksi
- **bahan_baku** - Master data bahan baku
- **jabatan** - Master jabatan (untuk tarif BTKL)
- **coa** - Chart of Accounts (akun akuntansi)

---

## RELASI UTAMA (20 relasi)

### Relasi Input Biaya
1. companies (1) ─── (n) produk
2. produk (1) ─── (n) biaya_bahan_baku
3. biaya_bahan_baku (1) ─── (n) kualifikasi_detail
4. bahan_baku (1) ─── (n) kualifikasi_detail
5. produk (1) ─── (n) btkl
6. jabatan (1) ─── (n) btkl
7. coa (1) ─── (n) bop
8. bop (1) ─── (n) komponen_bop

### Relasi Kalkulasi HPP
9. biaya_bahan_baku (1) ─── (n) hpp [menghitung]
10. btkl (1) ─── (n) hpp [menghitung]
11. bop (1) ─── (n) hpp [menghitung]
12. produk (1) ─── (n) hpp [memiliki_hpp]

### Relasi Transaksi Produksi
13. hpp (1) ─── (n) proses_produksi [digunakan_dalam]
14. proses_produksi (1) ─── (n) produksi_detail
15. produk (1) ─── (n) produksi_detail [diproduksi]

### Relasi Laporan
16. bahan_baku (1) ─── (n) stock_movements [mutasi_stok]
17. proses_produksi (1) ─── (n) stock_movements [mengkonsumsi]
18. coa (1) ─── (n) jurnal_umum [posting_ke]
19. proses_produksi (1) ─── (n) jurnal_umum [autoposting]
20. companies (1) ─── (n) users [mempekerjakan]

---

## KEUNTUNGAN VERSI SIMPLIFIED

### ✅ Lebih Sederhana
- 14 entitas vs 27 entitas lengkap
- Fokus pada INTI proses HPP dan produksi
- Mudah dipahami untuk presentasi/laporan

### ✅ Menghilangkan Redundansi
- **hpp** unified: Tidak perlu 3 tabel terpisah (`hpp_bbb`, `hpp_btkl`, `hpp_bop`)
- Menghapus tabel intermediate yang tidak esensial:
  - ❌ `target_produksi` & `target_produksi_detail` (sudah ada di modul lain)
  - ❌ `bom_job`, `bom_job_detail`, `bom_proses` (terlalu detail untuk ERD inti)
  - ❌ `btkl_proses` (detail proses BTKL - optional)
  - ❌ `bom_proses_bop` (alokasi BOP per proses - bisa diintegrasikan ke `komponen_bop`)
  - ❌ `pembayaran_beban` (transaksi pembayaran BOP - sudah tercakup di jurnal_umum)

### ✅ Tetap Lengkap Fungsionalitas
- **Semua modul tercakup:** Input Biaya → Kalkulasi HPP → Transaksi → Laporan
- **Semua relasi penting** tetap ada
- **Scalable:** Bisa diperluas ke versi lengkap kapan saja

---

## PERBANDINGAN

| Aspek | Versi LENGKAP | Versi INTI (Simplified) |
|-------|---------------|-------------------------|
| **Jumlah Entitas** | 27 entitas | 14 entitas |
| **HPP** | 3 tabel terpisah | 1 tabel unified |
| **Target Produksi** | ✅ Ada (2 tabel) | ❌ Tidak (fokus HPP & Produksi) |
| **BOM Detail** | ✅ Ada (3 tabel) | ❌ Tidak (disederhanakan) |
| **Kompleksitas ERD** | Sangat Detail | Sederhana & Fokus |
| **Cocok untuk** | Implementasi teknis | Laporan & Presentasi |

---

## REKOMENDASI

### Gunakan **Versi INTI (14 entitas)** untuk:
- ✅ Laporan Tugas Akhir (BAB 3 - Perancangan)
- ✅ Presentasi Sidang
- ✅ Penjelasan konsep kepada dosen/penguji
- ✅ Fokus pada alur HPP dan Transaksi Produksi

### Gunakan **Versi LENGKAP (27 entitas)** untuk:
- ✅ Dokumentasi teknis implementasi
- ✅ Database schema actual
- ✅ Lampiran (jika diperlukan detail lengkap)

---

## FILE OUTPUT

- **ERD_Modul_Arkan_CORE.drawio** - File DrawIO dengan notasi Chen, font 20px, fokus pada 14 entitas inti
- **ERD_Modul_Arkan.drawio** - File DrawIO versi lengkap (incomplete) dengan 27 entitas

**Cara membuka:**
1. Buka https://app.diagrams.net
2. File → Open From → Device
3. Pilih file `.drawio`
4. Edit/export sesuai kebutuhan

---

**Created by:** Kiro AI Assistant  
**Date:** 2026-07-14  
**Purpose:** Simplified ERD for HPP and Production Transaction modules

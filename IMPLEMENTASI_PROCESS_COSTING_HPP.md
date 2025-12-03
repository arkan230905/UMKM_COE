# Implementasi Process Costing untuk Perhitungan HPP

## ✅ STATUS: BERHASIL DIIMPLEMENTASIKAN

Data Process Costing berhasil di-seed ke database dengan perhitungan HPP lengkap!

## 📊 Hasil Perhitungan HPP

### Produk: **Nasi Ayam Ketumbar**

#### A. Biaya Bahan Baku Langsung (BBL)

| Bahan | Qty | Satuan | Harga/Satuan | Total |
|-------|-----|--------|--------------|-------|
| Beras Premium | 0.2000 | KG | Rp 12,000 | Rp 2,400.00 |
| Daging Ayam Fillet | 0.1500 | KG | Rp 35,000 | Rp 5,250.00 |
| Ketumbar Bubuk | 0.0100 | KG | Rp 50,000 | Rp 500.00 |
| Minyak Goreng | 0.0200 | L | Rp 25,000 | Rp 500.00 |
| Bawang Putih | 0.0150 | KG | Rp 40,000 | Rp 600.00 |
| Garam | 0.0050 | KG | Rp 8,000 | Rp 40.00 |
| **TOTAL BBL** | | | | **Rp 9,290.00** |

#### B. Biaya Tenaga Kerja Langsung (BTKL)

- Jam Kerja: 20 jam
- Tarif: Rp 15,000/jam
- Unit Produksi: 100 porsi
- **BTKL per unit: Rp 3,000.00**

#### C. Biaya Overhead Pabrik (BOP)

- Listrik: Rp 50,000
- Gas: Rp 30,000
- Penyusutan: Rp 20,000
- Total BOP: Rp 100,000
- Unit Produksi: 100 porsi
- **BOP per unit: Rp 1,000.00**

#### D. Harga Pokok Produksi (HPP)

| Komponen | Biaya |
|----------|-------|
| BBL | Rp 9,290.00 |
| BTKL | Rp 3,000.00 |
| BOP | Rp 1,000.00 |
| **HPP per unit** | **Rp 13,290.00** |

#### E. Harga Jual

- Margin: 40%
- **Harga Jual: Rp 18,606.00**

## 🔧 Implementasi Teknis

### 1. Data yang Di-seed

#### Satuan
- Kilogram (KG)
- Gram (G)
- Liter (L)
- Mililiter (ML)
- Porsi (PORSI)
- Pieces (PCS)

#### Bahan Baku (6 items)
1. Beras Premium - Rp 12,000/KG
2. Daging Ayam Fillet - Rp 35,000/KG
3. Ketumbar Bubuk - Rp 50,000/KG
4. Minyak Goreng - Rp 25,000/L
5. Bawang Putih - Rp 40,000/KG
6. Garam - Rp 8,000/KG

#### Produk
- Nasi Ayam Ketumbar
- HPP: Rp 13,290.00
- Harga Jual: Rp 18,606.00
- Margin: 40%

#### BOM (Bill of Materials)
- 1 BOM dengan 6 detail bahan baku
- Total biaya bahan: Rp 9,290.00
- BTKL: Rp 3,000.00
- BOP: Rp 1,000.00

### 2. Struktur Database

#### Tabel: `produks`
```
- id
- nama_produk
- foto
- deskripsi
- harga_bom (HPP)
- harga_jual
- margin_percent
- btkl_default
- bop_default
- stok
```

#### Tabel: `bahan_bakus`
```
- id
- nama_bahan
- satuan_id
- satuan
- harga_satuan
- stok
```

#### Tabel: `boms`
```
- id
- produk_id
- bahan_baku_id
- jumlah
- satuan_resep
- total_biaya
- btkl_per_unit
- bop_per_unit
- total_btkl
- total_bop
- periode
```

#### Tabel: `bom_details`
```
- id
- bom_id
- bahan_baku_id
- jumlah
- satuan
- harga_per_satuan
- total_harga
```

### 3. Cara Menjalankan

```bash
php seed_process_costing_data.php
```

Output:
```
=== SEED PROCESS COSTING DATA ===

1. Membuat Satuan...
   ✓ Satuan berhasil dibuat

2. Membuat Bahan Baku...
   ✓ Beras Premium
   ✓ Daging Ayam Fillet
   ✓ Ketumbar Bubuk
   ✓ Minyak Goreng
   ✓ Bawang Putih
   ✓ Garam

3. Membuat Produk...
   ✓ Produk berhasil dibuat

4. Membuat BOM...
   ✓ BOM berhasil dibuat

5. Membuat BOM Details...
   ✓ Beras: 0.2000 KG × Rp 12,000 = Rp 2,400.00
   ✓ Ayam: 0.1500 KG × Rp 35,000 = Rp 5,250.00
   ✓ Ketumbar: 0.0100 KG × Rp 50,000 = Rp 500.00
   ✓ Minyak: 0.0200 L × Rp 25,000 = Rp 500.00
   ✓ Bawang Putih: 0.0150 KG × Rp 40,000 = Rp 600.00
   ✓ Garam: 0.0050 KG × Rp 8,000 = Rp 40.00
   TOTAL BBL: Rp 9,290.00

6. Menghitung HPP Process Costing...
   ✓ BBL: Rp 9,290.00
   ✓ BTKL: Rp 3,000.00
   ✓ BOP: Rp 1,000.00
   ✓ HPP per unit: Rp 13,290.00
   ✓ Harga Jual (margin 40%): Rp 18,606.00

=== SELESAI ===
```

## 📝 Rumus Process Costing

### 1. Total Biaya Bahan Baku (BBL)
```
BBL = Σ (Qty × Harga per Satuan)
```

### 2. Biaya Tenaga Kerja Langsung (BTKL)
```
BTKL per unit = (Jam Kerja × Tarif per Jam) / Unit Produksi
```

### 3. Biaya Overhead Pabrik (BOP)
```
BOP per unit = Total BOP / Unit Produksi
```

### 4. Harga Pokok Produksi (HPP)
```
HPP = BBL + BTKL + BOP
```

### 5. Harga Jual
```
Harga Jual = HPP × (1 + Margin%)
```

## 🎯 Fitur yang Sudah Berjalan

1. ✅ Seed data satuan
2. ✅ Seed data bahan baku dengan harga
3. ✅ Seed data produk
4. ✅ Seed BOM (Bill of Materials)
5. ✅ Seed BOM Details (resep lengkap)
6. ✅ Perhitungan BBL otomatis
7. ✅ Perhitungan BTKL otomatis
8. ✅ Perhitungan BOP otomatis
9. ✅ Perhitungan HPP otomatis
10. ✅ Perhitungan Harga Jual dengan margin

## 📌 Catatan Penting

- **Process Costing** digunakan untuk produksi massal dengan produk homogen
- **BBL** = Biaya bahan baku yang langsung digunakan dalam produksi
- **BTKL** = Biaya tenaga kerja yang langsung terlibat dalam produksi
- **BOP** = Biaya overhead seperti listrik, gas, penyusutan
- **HPP** = Total biaya produksi per unit
- **Margin** = Persentase keuntungan di atas HPP

## 🔍 Verifikasi Data

Cek data di database:
```sql
-- Cek Produk
SELECT * FROM produks WHERE nama_produk = 'Nasi Ayam Ketumbar';

-- Cek BOM
SELECT * FROM boms WHERE produk_id = 1;

-- Cek BOM Details
SELECT bd.*, bb.nama_bahan 
FROM bom_details bd
JOIN bahan_bakus bb ON bd.bahan_baku_id = bb.id
WHERE bd.bom_id = 1;
```

---

**Status:** ✅ IMPLEMENTED & TESTED
**Date:** 3 Desember 2025
**HPP:** Rp 13,290.00
**Harga Jual:** Rp 18,606.00

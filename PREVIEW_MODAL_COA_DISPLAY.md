# Preview: Modal Detail BOP Proses dengan COA

## Tampilan Modal yang Akan Dilihat User

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          Detail BOP Proses                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Informasi Proses                                                           │
│  ┌──────────────────────────────┬──────────────────────────────┐           │
│  │ Nama BOP Proses              │ Kapasitas                    │           │
│  │ Pengemasan Dan Pengtopingan  │ 1 pcs/jam                    │           │
│  └──────────────────────────────┴──────────────────────────────┘           │
│                                                                             │
│  Komponen BOP                                                               │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │ No │ Komponen │ Rp/produk │ COA Debit      │ COA Kredit      │ Ket │   │
│  ├────┼──────────┼───────────┼────────────────┼─────────────────┼─────┤   │
│  │ 1  │ Listrik  │ Rp 278    │ 1173           │ 210             │  -  │   │
│  │    │          │           │ BDP-BOP        │ Hutang Usaha    │     │   │
│  ├────┼──────────┼───────────┼────────────────┼─────────────────┼─────┤   │
│  │ 2  │ Susu     │ Rp 649    │ 1173           │ 1151            │  -  │   │
│  │    │          │           │ BDP-BOP        │ Pers. Bahan     │     │   │
│  │    │          │           │                │ Pendukung Susu  │     │   │
│  ├────┼──────────┼───────────┼────────────────┼─────────────────┼─────┤   │
│  │ 3  │ Keju     │ Rp 1.000  │ 1173           │ 1152            │  -  │   │
│  │    │          │           │ BDP-BOP        │ Pers. Bahan     │     │   │
│  │    │          │           │                │ Pendukung Keju  │     │   │
│  ├────┼──────────┼───────────┼────────────────┼─────────────────┼─────┤   │
│  │ 4  │ Cup      │ Rp 400    │ 1173           │ 1153            │  -  │   │
│  │    │          │           │ BDP-BOP        │ Pers. Bahan     │     │   │
│  │    │          │           │                │ Pendukung       │     │   │
│  │    │          │           │                │ Kemasan         │     │   │
│  ├────┴──────────┴───────────┴────────────────┴─────────────────┴─────┤   │
│  │ Total BOP / produk                                    Rp 2.327      │   │
│  └───────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │ ℹ️ Jurnal Produksi: Setiap komponen akan membuat jurnal dengan     │   │
│  │    COA Debit dan COA Kredit yang sudah ditentukan di atas.         │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  Ringkasan Biaya                                                            │
│  ┌──────────────────────────────┬──────────────────────────────┐           │
│  │ Total BOP / produk           │ Biaya / produk               │           │
│  │ Rp 2.327                     │ Rp 2.327                     │           │
│  └──────────────────────────────┴──────────────────────────────┘           │
│                                                                             │
│                                                    [Tutup]                  │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Penjelasan Kolom COA

### COA Debit
- **Kode**: 1173 (untuk semua komponen BOP)
- **Nama**: Pers. Barang Dalam Proses - BOP
- **Fungsi**: Menambah nilai BDP-BOP (Work in Process)

### COA Kredit
Berbeda untuk setiap jenis komponen:

#### 1. Komponen Operasional (Listrik, Gas, Air)
- **Kode**: 210
- **Nama**: Hutang Usaha
- **Fungsi**: Mencatat hutang untuk biaya operasional

#### 2. Komponen Bahan Pendukung (Susu, Keju, Cup)
- **Kode**: 1151, 1152, 1153
- **Nama**: Pers. Bahan Pendukung [Nama Bahan]
- **Fungsi**: Mengurangi persediaan bahan pendukung

---

## Contoh Jurnal yang Akan Dibuat

Ketika produksi diproses, sistem akan membuat jurnal seperti ini:

### Untuk Komponen "Susu" (Rp 649)
```
Debit:  1173 - Pers. Barang Dalam Proses - BOP    Rp 649
Kredit: 1151 - Pers. Bahan Pendukung Susu         Rp 649
```

### Untuk Komponen "Listrik" (Rp 278)
```
Debit:  1173 - Pers. Barang Dalam Proses - BOP    Rp 278
Kredit: 210  - Hutang Usaha                       Rp 278
```

---

## Keunggulan Tampilan Ini

### 1. Transparansi ✅
User dapat melihat dengan jelas akun mana yang akan digunakan untuk setiap komponen BOP.

### 2. Verifikasi Mudah ✅
Sebelum produksi, user dapat memverifikasi bahwa:
- Bahan pendukung menggunakan akun Persediaan (115x) ✓
- Biaya operasional menggunakan akun Hutang Usaha (210) ✓

### 3. Dokumentasi Lengkap ✅
Modal detail menjadi dokumentasi lengkap yang mencakup:
- Nama komponen
- Nilai per produk
- Akun debit dan kredit
- Keterangan

### 4. Info Alert ✅
Alert box menjelaskan bahwa COA yang ditampilkan adalah COA yang akan digunakan di jurnal produksi, memberikan kepastian kepada user.

---

## Cara Akses

1. **Login** ke sistem
2. **Menu**: Master Data → BOP
3. **Klik**: Tombol "Detail" (ikon mata) pada baris BOP Proses
4. **Lihat**: Modal akan muncul dengan tampilan seperti di atas

---

## Perbandingan: Sebelum vs Sesudah

### ❌ Sebelum (Tanpa COA)
```
| No | Komponen | Rp/produk | Keterangan |
|----|----------|-----------|------------|
| 1  | Susu     | Rp 649    | -          |
```
**Masalah**: User tidak tahu akun apa yang akan digunakan

### ✅ Sesudah (Dengan COA)
```
| No | Komponen | Rp/produk | COA Debit | COA Kredit | Keterangan |
|----|----------|-----------|-----------|------------|------------|
| 1  | Susu     | Rp 649    | 1173      | 1151       | -          |
|    |          |           | BDP-BOP   | Pers. Bahan|            |
|    |          |           |           | Pendukung  |            |
|    |          |           |           | Susu       |            |
```
**Solusi**: User dapat melihat dan memverifikasi akun yang akan digunakan

---

## Testing Checklist untuk User

Saat membuka modal detail, pastikan:

- [ ] Kolom "COA Debit" muncul di tabel
- [ ] Kolom "COA Kredit" muncul di tabel
- [ ] Kode akun ditampilkan (contoh: 1173, 1151)
- [ ] Nama akun ditampilkan (contoh: BDP-BOP, Pers. Bahan Pendukung Susu)
- [ ] Info alert muncul di bawah tabel
- [ ] Info alert menjelaskan penggunaan COA di jurnal produksi
- [ ] Tidak ada error saat membuka modal
- [ ] Data COA sesuai dengan yang diinput saat setup BOP

---

**Status**: ✅ Siap untuk Testing  
**Tanggal**: 30 April 2026  
**File**: `resources/views/master-data/bop/show-proses-modal.blade.php`

# Sistem Konversi Bertingkat - Menggunakan Data Master

## ✅ SUDAH DIPERBAIKI!

Sistem sekarang menggunakan **data sub satuan yang sesungguhnya** dari master data bahan baku/pendukung.

## 📋 Cara Kerja Sistem:

### 1. Data Sub Satuan dari Master Data
- Sistem mengambil sub satuan dari konfigurasi di halaman master bahan baku/pendukung
- Hanya menampilkan sub satuan yang sudah dikonfigurasi
- Menampilkan faktor konversi dari database

### 2. Input Manual dengan Data Asli
- **Nama Sub Satuan**: Diambil dari database (misal: "Potong", "Siung")
- **Faktor Konversi**: Ditampilkan dari database (readonly)
- **Total Hasil**: User bisa edit manual sesuai kondisi aktual

## 🎯 Contoh Penggunaan:

### Langkah 1: Pilih Bahan Baku
1. **Pilih Bahan Baku**: Ayam Kampung
2. **Isi Jumlah**: 10
3. **Pilih Satuan Pembelian**: Kilogram (KG)
4. **Isi Konversi ke Satuan Utama**: 8 (10 KG = 8 Ekor)

### Langkah 2: Sistem Menampilkan Sub Satuan
Jika Ayam Kampung memiliki sub satuan "Potong" dengan faktor 4:

```
Potong
├── Total Potong: [32] (bisa diedit manual)
├── Faktor: 4 Potong/Ekor (readonly, dari database)
└── Rumus: 8 Ekor × 4 Potong/Ekor = 32 Potong
```

### Langkah 3: Edit Manual Sesuai Kondisi
User bisa mengubah total dari 32 menjadi 30 jika kondisi aktual berbeda:

```
Rumus: 8 Ekor × 3.75 Potong/Ekor = 30 Potong
```

## 🔧 Keuntungan Sistem Baru:

1. **Data Konsisten**: Menggunakan sub satuan yang sudah dikonfigurasi
2. **Fleksibilitas**: User tetap bisa edit total sesuai kondisi aktual
3. **Transparansi**: Menampilkan faktor konversi dari database
4. **Audit Trail**: Rumus perhitungan tersimpan untuk referensi

## 📊 Yang Tersimpan:

- **Satuan ID**: ID dari master satuan
- **Satuan Nama**: Nama sub satuan dari database
- **Jumlah Konversi**: Total yang diinput user (bisa berbeda dari otomatis)
- **Keterangan**: "Konversi manual sub satuan X"

## ⚠️ Catatan Penting:

- **Sub satuan hanya muncul jika sudah dikonfigurasi** di master data
- **Faktor konversi diambil dari database** dan tidak bisa diedit
- **Total hasil bisa diedit manual** untuk menyesuaikan kondisi aktual
- **Rumus berubah real-time** saat user mengedit total
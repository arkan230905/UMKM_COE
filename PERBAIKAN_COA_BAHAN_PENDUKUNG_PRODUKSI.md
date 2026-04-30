# Perbaikan COA Bahan Pendukung di Halaman Produksi

## Masalah

Di halaman `/transaksi/produksi/create`, sistem tidak membaca COA persediaan bahan pendukung dari master data. Semua bahan pendukung menggunakan akun Hutang Usaha (210) padahal seharusnya menggunakan akun persediaan yang sudah diset di master data.

### Contoh Masalah (SEBELUM):
```
Barang dalam proses - BOP 1173
Pers. Barang Dalam Proses - BOP 1173 Rp 279.240

Pengemasan Dan Pengtopingan — Listrik 210
Hutang Usaha 210 Rp 33.360

Pengemasan Dan Pengtopingan — Susu 210
Hutang Usaha 210 Rp 77.880

Pengemasan Dan Pengtopingan — Keju 210
Hutang Usaha 210 Rp 120.000

Pengemasan Dan Pengtopingan — Cup 210
Hutang Usaha 210 Rp 48.000
```

**Masalah:** Susu, Keju, Cup seharusnya menggunakan akun persediaan (1151, 1152, 1153), bukan Hutang Usaha (210).

## Solusi

### 1. Modifikasi Fungsi `resolveBopKredit()`

Menambahkan prioritas pencarian COA:

**PRIORITAS 1:** Cari dari master data `bahan_pendukungs` berdasarkan nama komponen
- Jika ditemukan bahan pendukung dengan nama yang cocok
- Dan bahan tersebut memiliki `coa_persediaan_id`
- Gunakan COA persediaan tersebut

**PRIORITAS 2:** Fallback ke keyword matching (logika lama)
- Cari COA berdasarkan nama komponen
- Cari COA berdasarkan kata kunci
- Fallback ke parent account

### 2. Update Keyword Map

Menambahkan keyword untuk bahan pendukung yang umum digunakan:

```php
'Susu'     => ['kredit_prefix' => '113'], // Akan dicari dari master data
'Keju'     => ['kredit_prefix' => '113'],
'Cup'      => ['kredit_prefix' => '113'],
'Toples'   => ['kredit_prefix' => '113'],
'Botol'    => ['kredit_prefix' => '113'],
'Plastik'  => ['kredit_prefix' => '113'],
'Kardus'   => ['kredit_prefix' => '113'],
'Label'    => ['kredit_prefix' => '113'],
'Listrik'  => ['kredit_prefix' => '113'], // Bisa jadi bahan pendukung atau hutang
```

## Hasil Setelah Perbaikan

### Contoh Jurnal (SESUDAH):
```
Barang dalam proses - BOP 1173
Pers. Barang Dalam Proses - BOP 1173 Rp 279.240

Pengemasan Dan Pengtopingan — Listrik 210
Hutang Usaha 210 Rp 33.360
(Listrik tidak ada di master bahan pendukung, jadi tetap ke Hutang Usaha)

Pengemasan Dan Pengtopingan — Susu 1151
Pers. Bahan Pendukung Susu 1151 Rp 77.880

Pengemasan Dan Pengtopingan — Keju 1152
Pers. Bahan Pendukung Keju 1152 Rp 120.000

Pengemasan Dan Pengtopingan — Cup 1153
Pers. Bahan Pendukung Kemasan (Cup) 1153 Rp 48.000
```

## Cara Kerja Sistem

### 1. Saat Membuat Produksi Baru

Ketika user membuat produksi baru di `/transaksi/produksi/create`:

1. Sistem membaca BOM Job Costing untuk produk tersebut
2. Untuk setiap komponen BOP, sistem memanggil `resolveBopKredit()`
3. Fungsi ini mencari COA kredit yang tepat dengan urutan:
   - Cari di master data `bahan_pendukungs` berdasarkan nama
   - Jika ditemukan dan memiliki `coa_persediaan_id`, gunakan COA tersebut
   - Jika tidak, gunakan keyword matching
   - Fallback ke Hutang Usaha (210)

### 2. Persyaratan di Master Data

Agar sistem bekerja dengan benar, setiap bahan pendukung di master data harus memiliki:

- **Field `coa_persediaan_id`** yang diisi dengan kode akun COA persediaan
- Contoh:
  - Susu → `coa_persediaan_id` = 1151
  - Keju → `coa_persediaan_id` = 1152
  - Cup → `coa_persediaan_id` = 1153

### 3. Struktur COA Persediaan Bahan Pendukung

```
115  - Pers. Bahan Pendukung (Parent)
1150 - Pers. Bahan Pendukung Air
1151 - Pers. Bahan Pendukung Susu
1152 - Pers. Bahan Pendukung Keju
1153 - Pers. Bahan Pendukung Kemasan (Cup)
1154 - Pers. Bahan Pendukung Lada
1155 - Pers. Bahan Pendukung Bubuk Kaldu
1156 - Pers. Bahan Pendukung Bubuk Bawang Putih
1157 - Pers. Bahan Pendukung Kemasan
```

## Verifikasi

### Script Pemeriksaan

Gunakan script `check_bahan_pendukung_coa.php` untuk memeriksa:
- Status COA persediaan setiap bahan pendukung
- Bahan pendukung yang belum memiliki COA persediaan
- Daftar COA persediaan yang tersedia

```bash
php check_bahan_pendukung_coa.php
```

### Hasil Pemeriksaan

✅ **Susu** - COA 1151 (Pers. Bahan Pendukung Susu)
✅ **Keju** - COA 1152 (Pers. Bahan Pendukung Keju)
✅ **Cup** - COA 1153 (Pers. Bahan Pendukung Kemasan (Cup))
⚠️ **Listrik** - Tidak ada di master bahan pendukung (menggunakan Hutang Usaha 210)

## File yang Dimodifikasi

1. **app/Http/Controllers/ProduksiController.php**
   - Fungsi `resolveBopKredit()` - Menambahkan prioritas pencarian dari master data
   - Fungsi `getBopCoaKeywordMap()` - Menambahkan keyword bahan pendukung

## Catatan Penting

1. **Listrik** tidak ditemukan di master data bahan pendukung, sehingga tetap menggunakan Hutang Usaha (210). Ini benar karena listrik biasanya adalah biaya operasional, bukan persediaan.

2. Jika ada bahan pendukung baru yang perlu ditambahkan:
   - Tambahkan di master data bahan pendukung
   - Set field `coa_persediaan_id` dengan COA yang sesuai
   - Sistem akan otomatis menggunakan COA tersebut

3. Keyword map hanya digunakan sebagai fallback jika bahan tidak ditemukan di master data atau tidak memiliki `coa_persediaan_id`.

## Testing

Untuk menguji perbaikan ini:

1. Buka halaman `/transaksi/produksi/create`
2. Pilih produk yang menggunakan bahan pendukung (Susu, Keju, Cup)
3. Isi form dan submit
4. Periksa jurnal yang terbentuk
5. Pastikan bahan pendukung menggunakan COA persediaan yang benar (1151, 1152, 1153), bukan Hutang Usaha (210)

## Status

✅ **SELESAI** - Sistem sekarang membaca COA persediaan dari master data bahan pendukung dengan benar.

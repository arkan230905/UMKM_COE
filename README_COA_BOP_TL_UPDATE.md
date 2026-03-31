# Update COA BOP TL (BOP Tidak Langsung Lainnya)

## Ringkasan Perubahan
Telah berhasil mengupdate struktur COA untuk bagian BOP TL (BOP Tidak Langsung Lainnya) sesuai permintaan user dengan menambahkan akun penyusutan yang lebih detail dan menyesuaikan urutan kode akun.

## Struktur BOP TL yang Baru

| Kode Akun | Nama Akun | Tipe | Kategori |
|-----------|-----------|------|----------|
| 55 | BOP TL - BOP Tidak Langsung Lainnya | Expense | BOP Tidak Langsung Lainnya |
| 550 | BOP TL - Biaya Listrik | Expense | BOP Tidak Langsung Lainnya |
| 551 | BOP TL - Sewa Tempat | Expense | BOP Tidak Langsung Lainnya |
| 552 | BOP TL - Biaya Penyusutan Gedung | Expense | BOP Tidak Langsung Lainnya |
| 553 | BOP TL - Biaya Penyusutan Peralatan | Expense | BOP Tidak Langsung Lainnya |
| 554 | BOP TL - Biaya Penyusutan Kendaraan | Expense | BOP Tidak Langsung Lainnya |
| 555 | BOP TL - Biaya Penyusutan Mesin | Expense | BOP Tidak Langsung Lainnya |
| 556 | BOP TL - Biaya Air | Expense | BOP Tidak Langsung Lainnya |
| 557 | BOP TL - Lainnya | Expense | BOP Tidak Langsung Lainnya |
| 558 | Beban Transport Pembelian | Expense | BOP Tidak Langsung Lainnya |
| 559 | Diskon Pembelian | Expense | BOP Tidak Langsung Lainnya |

## Perubahan yang Dilakukan

### 1. Penambahan Akun Penyusutan
Menambahkan akun penyusutan yang lebih spesifik:
- **553**: BOP TL - Biaya Penyusutan Peralatan (sebelumnya: BOP TL - Biaya Air)
- **554**: BOP TL - Biaya Penyusutan Kendaraan (baru)
- **555**: BOP TL - Biaya Penyusutan Mesin (baru)

### 2. Penyesuaian Urutan Kode Akun
- **556**: BOP TL - Biaya Air (dipindah dari kode 553)
- **557**: BOP TL - Lainnya (dipindah dari kode 554)
- **558**: Beban Transport Pembelian (dipindah dari kode 555)
- **559**: Diskon Pembelian (dipindah dari kode 556)

### 3. File yang Diupdate
Semua seeder COA telah diupdate untuk konsistensi:

1. **CoaSeederAdaptive.php** - Seeder utama yang adaptif dengan struktur database
2. **CoaSeederFinal.php** - Seeder dengan struktur lengkap
3. **CoaSeederCompatible.php** - Seeder untuk database dengan kolom extended

## Manfaat Perubahan

### 1. Akuntansi Penyusutan yang Lebih Detail
Dengan adanya akun penyusutan yang terpisah untuk setiap jenis aset:
- Gedung (552)
- Peralatan (553)
- Kendaraan (554)
- Mesin (555)

Sistem dapat mencatat dan melacak penyusutan setiap kategori aset secara terpisah.

### 2. Integrasi dengan Master Data Aset
Perubahan ini mendukung integrasi COA dengan master data aset yang telah dibuat sebelumnya, dimana setiap aset dapat memiliki:
- Akun aset yang spesifik
- Akun akumulasi penyusutan yang sesuai
- Akun beban penyusutan yang tepat

### 3. Laporan yang Lebih Akurat
Dengan struktur yang lebih detail, laporan keuangan akan menampilkan:
- Beban penyusutan per kategori aset
- Analisis biaya overhead yang lebih spesifik
- Tracking biaya operasional yang lebih baik

## Cara Menjalankan Update

### Untuk Database Baru
```bash
php artisan db:seed --class=CoaSeederAdaptive
```

### Untuk Database yang Sudah Ada
Seeder akan melakukan `updateOrCreate`, sehingga:
- Akun yang sudah ada akan diupdate nama dan strukturnya
- Akun baru akan ditambahkan
- Tidak ada data yang hilang

## Kompatibilitas

### Database dengan Kolom Extended
Seeder adaptive akan otomatis mendeteksi kolom yang tersedia dan mengisi:
- `kode_induk` - untuk hierarki akun
- `is_akun_header` - untuk menandai akun header
- `keterangan` - untuk keterangan tambahan
- `tanggal_saldo_awal` - untuk tanggal saldo awal
- `posted_saldo_awal` - untuk status posting

### Database dengan Kolom Minimal
Seeder akan tetap berjalan dengan kolom minimal yang diperlukan:
- `kode_akun`
- `nama_akun`
- `tipe_akun`
- `kategori_akun`
- `saldo_normal`

## Status
✅ **SELESAI** - Update COA BOP TL berhasil diterapkan pada semua seeder dan siap digunakan

Total akun COA: **80 akun** (bertambah 3 akun baru untuk penyusutan)
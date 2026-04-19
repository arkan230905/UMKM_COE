# COA Seeder untuk Sistem Manufaktur - Final Version

## Deskripsi
Seeder ini membuat struktur Chart of Accounts (COA) yang lengkap untuk sistem manufaktur makanan, khususnya untuk usaha pengolahan ayam dan produk sejenis. Versi final ini mencakup implementasi lengkap BTKTL dengan dual-purpose accounts.

## Struktur COA

### 1. ASET (Asset)
- **Kas & Bank**: Kas, Kas Bank, Kas Kecil
- **Persediaan Bahan Baku**: Ayam potong, ayam kampung, bebek, ayam lainnya
- **Persediaan Bahan Pendukung**: Sequential 1150-1159, kemudian 11510 (Air, Minyak Goreng, Gas 30 Kg, Tepung Terigu, Tepung Maizena, Lada, Bubuk Kaldu, Listrik, Bubuk Bawang Putih, Kemasan, Cabe Merah)
- **Persediaan Lainnya**: Barang Jadi Ayam Ketumbar, Barang dalam Proses
- **Aset Tetap**: Peralatan, Gedung, Kendaraan, Mesin + Akumulasi Penyusutan
- **Lainnya**: Piutang, PPN Masukan

### 2. KEWAJIBAN (Liability)
- **Hutang Usaha**: Hutang kepada supplier
- **Hutang Gaji**: Hutang gaji karyawan
- **PPN Keluaran**: Pajak pertambahan nilai

### 3. MODAL (Equity)
- **Modal Usaha**: Modal pemilik
- **Prive**: Pengambilan pribadi pemilik

### 4. PENDAPATAN (Revenue)
- **Penjualan**: Penjualan Produk Ayam Ketumbar

### 5. BIAYA (Expense)
#### BBB (Biaya Bahan Baku):
- **BBB**: Ayam potong, ayam kampung, bebek

#### BTKL (Biaya Tenaga Kerja Langsung):
- **BTKL**: Chef 1, Chef 2, Chef 3

#### BOP (Biaya Overhead Pabrik):
- **Bahan Pendukung**: Air, Minyak Goreng, Gas 30 Kg, Ketumbar Bubuk, Bawang Putih, Tepung Maizena, Merica Bubuk, Listrik, Bawang Merah, Kemasan, Cabe Merah, Lada Hitam
- **BTKTL**: Biaya Tenaga Kerja Tidak Langsung dengan dual-purpose accounts
- **BOP TL**: BOP Tidak Langsung Lainnya (Listrik, Sewa, Penyusutan, Air, Lainnya, Transport, Diskon)

## Cara Menggunakan

### 1. Melalui Artisan Command (Recommended)
```bash
php artisan db:seed --class=CoaSeederFinal
```

### 2. Melalui Update Script
```bash
php update_coa_final_with_btktl.php
```

### 3. Melalui DatabaseSeeder
Tambahkan di `database/seeders/DatabaseSeeder.php`:
```php
public function run()
{
    $this->call([
        CoaSeederFinal::class,
    ]);
}
```

Kemudian jalankan:
```bash
php artisan db:seed
```

### 4. Reset dan Seed Ulang
```bash
php artisan migrate:fresh --seed
```

## Fitur Seeder

- **UpdateOrCreate**: Tidak akan membuat duplikat, akan update jika sudah ada
- **Saldo Normal**: Sudah diset sesuai dengan tipe akun
- **Kategori**: Sudah dikategorikan untuk memudahkan laporan
- **Saldo Awal**: Diset 0 untuk semua akun baru
- **Dual-Purpose Accounts**: Akun 540-542 melayani BOP dan BTKTL sekaligus

## Total Akun
- **79 akun** COA lengkap untuk manufaktur
- **33 akun** Aset (termasuk persediaan detail)
- **37 akun** Biaya (termasuk BBB, BTKL, BOP, BTKTL detail)
- **4 akun** Kewajiban
- **3 akun** Modal
- **2 akun** Pendapatan

## Catatan Penting

1. **Kode Akun**: Menggunakan sistem hierarki yang konsisten
2. **Persediaan Bahan Pendukung**: Kode akun berurutan 1150-1159, kemudian 11510 sesuai permintaan
3. **Pengurutan**: Sistem menggunakan pengurutan numerik (CAST AS UNSIGNED) untuk menampilkan urutan yang benar
4. **Saldo Normal**: 
   - Asset & Expense: Debit
   - Liability, Equity & Revenue: Kredit
   - Akumulasi Penyusutan: Kredit (contra asset)
5. **Kategori**: Memudahkan pembuatan laporan keuangan
6. **Manufaktur**: Struktur khusus untuk industri makanan dengan fokus produk Ayam Ketumbar
7. **BTKTL Implementation**: 
   - Akun 540-542: Dual-purpose untuk BOP dan BTKTL
   - Akun 543-546: Khusus untuk BTKTL
   - Akun 54: Induk BTKTL

## Detail Dual-Purpose Accounts (BOP & BTKTL)
- **540**: BOP-Kemasan / BOP BTKTL - Biaya Pegawai Pemasaran
- **541**: BOP-Cabe Merah / BOP BTKTL - Biaya Pegawai Kemasan
- **542**: BOP-Lada Hitam / BOP BTKTL - Biaya Satpam Pabrik

## Detail Dedicated BTKTL Accounts
- **543**: BOP BTKTL - Biaya Cleaning Service
- **544**: BOP BTKTL - Biaya Mandor
- **545**: BOP BTKTL - Biaya Pegawai Keuangan
- **546**: BOP BTKTL - BTKTL Lainnya

## Detail Persediaan Bahan Pendukung (Sequential Order)
- 1150: Air
- 1151: Minyak Goreng  
- 1152: Gas 30 Kg
- 1153: Tepung Terigu
- 1154: Tepung Maizena
- 1155: Lada
- 1156: Bubuk Kaldu
- 1157: Listrik
- 1158: Bubuk Bawang Putih
- 1159: Kemasan
- 11510: Cabe Merah

## Penggunaan dalam Sistem

Setelah seeder dijalankan, COA ini dapat digunakan untuk:
- Jurnal transaksi pembelian bahan baku
- Jurnal proses produksi (BBB, BTKL, BOP)
- Jurnal BTKTL dengan dual-purpose accounts
- Jurnal penjualan dan HPP
- Laporan keuangan (Laporan Posisi Keuangan, Laba Rugi)
- Analisis biaya produksi

## Maintenance

Untuk menambah akun baru, edit file `database/seeders/CoaSeederFinal.php` dan tambahkan ke array `$coaData`, kemudian jalankan seeder lagi.

## Files Terkait
- `database/seeders/CoaSeederFinal.php` - Seeder utama (recommended)
- `update_coa_final_with_btktl.php` - Script update langsung
- `database/seeders/CoaSeeder.php` - Seeder versi lama
- `app/Filament/Resources/CoaResource.php` - Resource dengan numeric sorting
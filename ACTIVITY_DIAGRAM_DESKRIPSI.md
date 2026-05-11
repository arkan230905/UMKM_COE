# Deskripsi Activity Diagram Sistem SIMCOST

Berikut merupakan Activity diagram aplikasi Sistem Pengelolaan Produksi dan Perhitungan Harga Pokok Produksi (SIMCOST) berbasis web yang dibuat.

## a. Activity Diagram Login Sistem

Berikut merupakan aliran aktivitas yang terjadi pada aktivitas login sistem yaitu:

1. **Start** - Pengguna memulai aplikasi
2. **Login Sistem** - Pengguna memasukkan username dan password
3. **Validasi Login** - Sistem memvalidasi kredensial pengguna
4. **Menu Utama** - Pengguna diarahkan ke menu utama berdasarkan role (Admin/Pemilik atau Owner)

## b. Activity Diagram Modul Biaya Bahan Baku

Berikut merupakan aliran aktivitas yang terjadi pada modul Biaya Bahan Baku yaitu:

1. **Pilih Menu Biaya Bahan Baku** - Pengguna memilih menu Biaya Bahan Baku dari Menu Utama
2. **Input Data Bahan Baku** - Pengguna menginput data bahan baku termasuk nama, harga, satuan, dan spesifikasi
3. **Konversi Satuan** - Sistem melakukan konversi satuan jika diperlukan untuk perhitungan harga
4. **Hitung Harga Rata-rata** - Sistem menghitung harga rata-rata bahan baku berdasarkan data historis
5. **Simpan Data** - Data bahan baku disimpan ke database

## c. Activity Diagram Modul BTKL (Biaya Tenaga Kerja Langsung)

Berikut merupakan aliran aktivitas yang terjadi pada modul BTKL yaitu:

1. **Pilih Menu BTKL** - Pengguna memilih menu BTKL dari Menu Utama
2. **Input Tarif per Jam** - Pengguna menginput tarif upah per jam untuk setiap jenis pekerjaan
3. **Input Jam Kerja** - Pengguna menginput jumlah jam kerja yang dibutuhkan untuk setiap proses
4. **Kalkulasi Biaya BTKL** - Sistem menghitung total biaya BTKL = Tarif × Jam Kerja
5. **Simpan Data** - Data BTKL disimpan ke database

## d. Activity Diagram Modul BOP (Biaya Overhead Produksi)

Berikut merupakan aliran aktivitas yang terjadi pada modul BOP yaitu:

1. **Pilih Menu BOP** - Pengguna memilih menu BOP dari Menu Utama
2. **Input Budget BOP** - Pengguna menginput anggaran BOP yang direncanakan
3. **Input Aktual BOP** - Pengguna menginput biaya BOP aktual yang terjadi
4. **Tracking vs Budget** - Sistem membandingkan aktual dengan budget dan menampilkan selisih
5. **Simpan Data** - Data BOP disimpan ke database

## e. Activity Diagram Modul Harga Pokok Produksi (HPP)

Berikut merupakan aliran aktivitas yang terjadi pada modul Harga Pokok Produksi yaitu:

1. **Pilih Menu HPP** - Pengguna memilih menu HPP dari Menu Utama
2. **Get Data Biaya Bahan** - Sistem mengambil data biaya bahan baku dari database
3. **Get Data BTKL** - Sistem mengambil data biaya tenaga kerja langsung dari database
4. **Get Data BOP** - Sistem mengambil data biaya overhead produksi dari database
5. **Calculate HPP** - Sistem menghitung HPP = Biaya Bahan + BTKL + BOP
6. **Simpan HPP** - Data HPP disimpan ke database untuk produk yang bersangkutan

## f. Activity Diagram Modul Produksi

Berikut merupakan aliran aktivitas yang terjadi pada modul Produksi yaitu:

1. **Pilih Menu Produksi** - Pengguna memilih menu Produksi dari Menu Utama
2. **Input Data Produksi** - Pengguna menginput data produksi (produk, tanggal, jumlah)
3. **Get HPP Data** - Sistem mengambil data HPP untuk produk yang diproduksi
4. **Validasi Stok Bahan** - Sistem memvalidasi ketersediaan stok bahan baku (FIFO)
5. **Decision: Stok Cukup?** - Sistem memeriksa apakah stok bahan mencukupi
   - **Ya**: Lanjut ke proses produksi
   - **Tidak**: Tampilkan error "Stok Tidak Cukup"
6. **Memulai Produksi** - Sistem mencatat proses produksi dan mengurangi stok bahan
7. **Update Stok Produk** - Sistem menambah stok produk jadi
8. **Simpan Transaksi** - Data produksi disimpan ke database

## g. Activity Diagram Modul Laporan Stok

Berikut merupakan aliran aktivitas yang terjadi pada modul Laporan Stok yaitu:

1. **Pilih Menu Laporan Stok** - Pengguna memilih menu Laporan Stok dari Menu Utama
2. **Real-time Inventory Status** - Sistem menampilkan status stok terkini
3. **Stock Movement Tracking** - Sistem menampilkan histori pergerakan stok
4. **Generate Reports** - Sistem membuat laporan stok berdasarkan periode yang dipilih
5. **Export/Print** - Pengguna dapat mengekspor atau mencetak laporan

## h. Activity Diagram Keluar Sistem

Berikut merupakan aliran aktivitas yang terjadi pada aktivitas keluar sistem yaitu:

1. **Pilih Logout** - Pengguna memilih menu logout
2. **Konfirmasi Logout** - Sistem meminta konfirmasi logout
3. **Session Clear** - Sistem menghapus session pengguna
4. **End** - Pengguna keluar dari sistem

## i. Aliran Antar Modul

Activity diagram ini menunjukkan aliran data antar modul:

- **Biaya Bahan Baku** → **HPP** (data biaya bahan untuk perhitungan HPP)
- **BTKL** → **HPP** (data biaya tenaga kerja untuk perhitungan HPP)
- **BOP** → **HPP** (data biaya overhead untuk perhitungan HPP)
- **HPP** → **Produksi** (data HPP untuk proses produksi)
- **Produksi** → **Laporan Stok** (data produksi untuk update stok)
- **Semua Modul** → **Database** (penyimpanan data terpusat)

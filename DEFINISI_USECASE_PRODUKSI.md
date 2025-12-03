# Definisi Aktor dan Use Case - Sistem Pengelolaan Produksi

## Tabel 3-4 Definisi Aktor

| No | Aktor | Deskripsi |
|----|-------|-----------|
| 1 | Admin/Pemilik | Pengguna yang memiliki akses penuh untuk mengelola master data Bill of Materials (BOM), mengelola data bahan baku, mengelola data produk jadi, dan mengatur konfigurasi sistem produksi. |
| 2 | Staff Produksi | Pengguna yang memiliki akses untuk melakukan proses produksi, monitoring stok bahan baku, dan mencatat hasil produksi harian. |
| 3 | Manajer | Pengguna yang memiliki akses untuk melihat laporan produksi, monitoring stok bahan baku, dan melihat perhitungan Harga Pokok Produksi (HPP) produk. |

## Tabel 3-5 Definisi Use Case

| No | Use Case | Deskripsi |
|----|----------|-----------|
| 1 | Kelola Bill of Materials (BOM) | Merupakan proses pengelolaan data BOM termasuk menambah, mengubah, dan menghapus data resep produksi yang berisi daftar bahan baku dan jumlah yang dibutuhkan untuk membuat satu unit produk jadi. |
| 2 | Kelola Bahan Baku | Merupakan proses pengelolaan data bahan baku termasuk menambah, mengubah, dan menghapus data bahan baku produksi beserta harga, satuan, dan spesifikasi bahan. |
| 3 | Kelola Produk Jadi | Merupakan proses pengelolaan data produk jadi termasuk menambah, mengubah, dan menghapus data produk hasil produksi beserta informasi harga jual dan spesifikasi produk. |
| 4 | Proses Produksi | Merupakan proses pencatatan aktivitas produksi termasuk input jumlah produksi, penggunaan bahan baku, dan hasil produksi yang mengacu pada BOM dan mengurangi stok bahan baku secara otomatis. |
| 5 | Monitoring Stok Bahan Baku | Merupakan proses pemantauan ketersediaan stok bahan baku termasuk melihat jumlah stok tersedia, stok minimum, dan riwayat penggunaan bahan baku dalam proses produksi. |
| 6 | Laporan Produksi | Merupakan proses melihat laporan produksi berdasarkan periode tertentu yang mencakup jumlah produksi, penggunaan bahan baku, efisiensi produksi, dan analisis produktivitas. |
| 7 | Hitung HPP Produk | Merupakan proses perhitungan Harga Pokok Produksi (HPP) produk berdasarkan data BOM dan harga bahan baku yang digunakan untuk menentukan biaya produksi per unit produk. |

## Relasi Antar Use Case

### Include Relationship

| Use Case Utama | Include | Use Case yang Disertakan | Penjelasan |
|----------------|---------|--------------------------|------------|
| Proses Produksi | <<include>> | Kelola Bill of Materials (BOM) | Proses produksi memerlukan data BOM untuk mengetahui komposisi bahan baku yang dibutuhkan. |
| Proses Produksi | <<include>> | Monitoring Stok Bahan Baku | Proses produksi harus mengecek ketersediaan stok bahan baku sebelum produksi dilakukan. |
| Hitung HPP Produk | <<include>> | Kelola Bill of Materials (BOM) | Perhitungan HPP memerlukan data BOM untuk mengetahui komposisi dan jumlah bahan baku yang digunakan. |

# Definisi Aktor dan Use Case Sistem Pengelolaan Produksi SIMCOST

## Definisi Aktor

Terdapat actor dalam use case diagram pengelolaan produksi pada aplikasi berbasis web yang dibuat dengan didefinisikan sebagai berikut.

| No | Aktor | Deskripsi |
|-----|-------|-----------|
| 1 | Admin/Pemilik | Pengguna yang memiliki akses penuh untuk mengelola master data Bill of Materials (BOM), mengelola data bahan baku, mengelola data produk jadi, melakukan proses produksi, monitoring stok bahan baku, mencatat hasil produksi harian, dan mengatur konfigurasi sistem produksi. |
| 2 | Owner | Pengguna yang memiliki akses tertinggi untuk mengelola seluruh sistem termasuk master data, proses produksi, laporan, profil perusahaan, dan laporan keuangan. |

*Table 3.5 Definisi Aktor*

## Definisi Use Case

Berikut merupakan definisi dari use case aplikasi pengelolaan produksi berbasis web yang dibuat.

| No | Use Case | Deskripsi |
|-----|----------|----------|
| 1 | Kelola Bill of Materials (BOM) | Merupakan proses pengelolaan data BOM termasuk menambah, mengubah, dan menghapus data resep produksi yang berisi daftar bahan baku dan jumlah yang dibutuhkan untuk membuat satu unit produk jadi. |
| 2 | Kelola Bahan Baku | Merupakan proses pengelolaan data bahan baku termasuk menambah, mengubah, dan menghapus data bahan baku produksi beserta harga, satuan, dan spesifikasi bahan. |
| 3 | Kelola Produk Jadi | Merupakan proses pengelolaan data produk jadi termasuk menambah, mengubah, dan menghapus data produk hasil produksi beserta informasi harga jual dan spesifikasi produk. |
| 4 | Proses Produksi | Merupakan proses pencatatan aktivitas produksi termasuk input jumlah produksi, penggunaan bahan baku, dan hasil produksi yang mengacu pada BOM dan mengurangi stok bahan baku secara otomatis. |
| 5 | Monitoring Stok Bahan Baku | Merupakan proses pemantauan ketersediaan stok bahan baku termasuk melihat jumlah stok tersedia, stok minimum, dan riwayat penggunaan bahan baku dalam proses produksi. |
| 6 | Catat Hasil Produksi Harian | Merupakan proses pencatatan hasil produksi harian termasuk jumlah produk yang dihasilkan, waktu produksi, dan catatan kualitas produk. |
| 7 | Laporan Produksi | Merupakan proses melihat laporan produksi berdasarkan periode tertentu yang mencakup jumlah produksi, penggunaan bahan baku, efisiensi produksi, dan analisis produktivitas. |
| 8 | Monitoring Stok Bahan Baku | Merupakan proses pemantauan ketersediaan stok bahan baku termasuk melihat jumlah stok tersedia, stok minimum, dan riwayat penggunaan bahan baku dalam proses produksi. |
| 9 | Hitung HPP Produk | Merupakan proses perhitungan Harga Pokok Produksi (HPP) produk berdasarkan data BOM dan harga bahan baku yang digunakan untuk menentukan biaya produksi per unit produk. |
| 10 | Konfigurasi Sistem Produksi | Merupakan proses pengaturan parameter sistem produksi termasuk setting stok minimum, satuan produksi, dan konfigurasi lainnya. |
| 11 | Analisis Produktivitas | Merupakan proses analisis produktivitas produksi berdasarkan data historis untuk identifikasi tren dan perbaikan proses. |
| 12 | Kelola Profil Perusahaan | Merupakan proses pengelolaan data profil perusahaan termasuk informasi perusahaan, alamat, kontak, dan pengaturan bisnis. |
| 13 | Laporan Keuangan | Merupakan proses melihat laporan keuangan perusahaan termasuk laporan laba rugi, arus kas, dan laporan keuangan lainnya. |

*Table 3.6 Definisi Use Case*

## Hubungan Antar Use Case

### Include Relationships:
- **Proses Produksi** «include» Monitoring Stok Bahan Baku
- **Proses Produksi** «include» Catat Hasil Produksi Harian
- **Laporan Produksi** «include» Analisis Produktivitas
- **Hitung HPP Produk** «include» Kelola Bahan Baku
- **Laporan Keuangan** «include» Kelola Profil Perusahaan

### Akses Aktor:
- **Admin/Pemilik**: Kelola BOM, Kelola Bahan Baku, Kelola Produk Jadi, Proses Produksi, Monitoring Stok Bahan Baku, Catat Hasil Produksi Harian, Konfigurasi Sistem Produksi
- **Owner**: Kelola BOM, Kelola Bahan Baku, Kelola Produk Jadi, Proses Produksi, Monitoring Stok Bahan Baku, Catat Hasil Produksi Harian, Laporan Produksi, Hitung HPP Produk, Analisis Produktivitas, Kelola Profil Perusahaan, Laporan Keuangan

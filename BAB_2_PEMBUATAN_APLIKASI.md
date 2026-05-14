# BAB 2
# PEMBUATAN APLIKASI

## 2.1 Pendahuluan Pembuatan Aplikasi

Pembuatan aplikasi merupakan kegiatan utama selama masa magang di tempat kerja. Proyek yang dibuat adalah Aplikasi Web SIMCOST untuk Pengelolaan Biaya Produksi dan Perhitungan Harga Pokok Produksi dengan metode Job Costing berbasis web yang membantu pelaku usaha UMKM dan manufaktur mengelola data produk, transaksi, dan laporan keuangan. Sebagai tim magang, penulis bertanggung jawab atas pembuatan fitur-fitur utama dalam aplikasi yang mencakup analisis kebutuhan sistem, perancangan arsitektur aplikasi, pengembangan modul, dan pengujian fungsionalitas.

Aplikasi SIMCOST dibuat menggunakan framework Laravel dengan metode Agile Development. Sebagai tim magang, penulis bertanggung jawab atas pembuatan fitur-fitur utama dalam aplikasi yang mendukung UMKM dan manufaktur dengan metode Job Costing. Proses pembuatan dilakukan secara bertahap melalui siklus perencanaan, implementasi, dan pengujian. Setiap modul yang dibuat diuji untuk memastikan fungsinya berjalan sesuai kebutuhan sebelum diintegrasikan ke sistem utama.

## 2.2 Arsitektur Sistem

### 2.2.1 Arsitektur Aplikasi

Aplikasi dikembangkan dengan arsitektur Model-View-Controller (MVC) menggunakan framework Laravel 10. Arsitektur ini memisahkan logika bisnis (Model), antarmuka pengguna (View), dan kontrol alur data (Controller) untuk memudahkan pengembangan dan pemeliharaan sistem.

**Komponen Arsitektur:**
- **Model Layer:** Mengelola interaksi dengan database MySQL
- **View Layer:** Menampilkan antarmuka pengguna menggunakan Blade template engine
- **Controller Layer:** Mengatur alur logika bisnis dan request-response

### 2.2.2 Database Design

Database dirancang menggunakan MySQL dengan struktur relasional untuk mendukung multi-tenant architecture. Setiap pengguna memiliki data yang terisolasi untuk menjaga keamanan dan privasi data.

**Tabel Utama:**
- `users`: Manajemen pengguna dan autentikasi
- `produks`: Master data produk
- `bahan_bakus`: Master data bahan baku
- `biaya_bahan_bakus`: Perhitungan biaya bahan baku per produk
- `btkls`: Data biaya tenaga kerja langsung
- `bops`: Data biaya overhead produksi
- `bom_job_costings`: Bill of Materials dan job costing
- `produksis`: Transaksi produksi
- `pembayaran_bebans`: Pengeluaran operasional
- `coas`: Chart of accounts untuk akuntansi

## 2.3 Pengembangan Modul

### 2.3.1 Modul Biaya Bahan Baku

Modul Biaya Bahan Baku dikembangkan untuk mengelola biaya material yang digunakan dalam produksi. Fitur utama meliputi:

**Fitur Utama:**
- Input biaya bahan baku per produk
- Konversi satuan otomatis (kg, gram, liter, ml)
- Perhitungan harga rata-rata
- Tracking penggunaan material

**Implementasi:**
- Controller: `BiayaBahanController.php`
- Model: `BiayaBahanBaku.php`
- Views: `resources/views/master-data/biaya-bahan/`
- Route: `/biaya-bahan` dengan middleware auth

### 2.3.2 Modul BTKL

Modul Biaya Tenaga Kerja Langsung (BTKL) mengelola biaya tenaga kerja yang terlibat langsung dalam proses produksi.

**Fitur Utama:**
- Manajemen proses produksi
- Perhitungan tarif per unit
- Integrasi data pegawai dan jabatan
- Tracking waktu kerja per proses

**Implementasi:**
- Controller: `BtklController.php`
- Model: `Btkl.php`
- Views: `resources/views/master-data/btkl/`
- Route: `/btkl` dengan middleware auth

### 2.3.3 Modul BOP

Modul Biaya Overhead Produksi (BOP) mengelola biaya tidak langsung yang diperlukan dalam proses produksi.

**Fitur Utama:**
- Budget management untuk BOP
- Tracking aktual vs budget
- Integrasi dengan COA accounts
- Recalculation otomatis

**Implementasi:**
- Controller: `BopController.php`
- Model: `Bop.php`
- Views: `resources/views/master-data/bop/`
- Route: `/bop` dengan middleware auth

### 2.3.4 Modul Harga Pokok Produksi

Modul HPP merupakan fitur unggulan yang mengintegrasikan ketiga komponen biaya untuk menghitung harga pokok produksi secara otomatis.

**Fitur Utama:**
- Dashboard HPP dengan formula HPP = BBB + BTKL + BOP
- Perhitungan otomatis per produk
- Analisis margin keuntungan
- Re-calculation dinamis
- Export laporan HPP

**Implementasi:**
- Controller: `HppController.php` (NEW)
- Model: `BomJobCosting.php`
- Views: `resources/views/hpp/`
- Routes: `/hpp` dengan middleware auth

### 2.3.5 Modul Produksi

Modul Produksi mengelola transaksi produksi dan tracking stok produk.

**Fitur Utama:**
- Input transaksi produksi
- Integrasi dengan HPP
- Update stok otomatis
- Generate jurnal produksi

**Implementasi:**
- Controller: `ProduksiController.php`
- Model: `Produksi.php`
- Views: `resources/views/produksi/`
- Route: `/produksi` dengan middleware auth

### 2.3.6 Modul Pembayaran Beban

Modul Pembayaran Beban mengelola pengeluaran operasional perusahaan.

**Fitur Utama:**
- Input pengeluaran operasional
- Kategorisasi beban
- Integrasi dengan COA
- Tracking pembayaran

**Implementasi:**
- Controller: `PembayaranBebanController.php`
- Model: `PembayaranBeban.php`
- Views: `resources/views/transaksi/pembayaran-beban/`
- Route: `/pembayaran-beban` dengan middleware auth

### 2.3.7 Modul Laporan

Modul Laporan menyediakan berbagai laporan keuangan dan operasional untuk manajemen.

**Fitur Utama:**
- Laporan Stok: Monitoring persediaan barang
- Laporan Kas & Bank: Tracking arus kas
- Jurnal Umum: Pencatatan transaksi akuntansi
- Laporan Pembayaran Beban: Monitoring pengeluaran

**Implementasi:**
- Controller: Masing-masing laporan memiliki controller tersendiri
- Views: `resources/views/laporan/`
- Routes: `/laporan/*` dengan middleware auth

### 2.3.8 Modul Kelola Catalog

Modul Kelola Catalog mengelola master data produk dan informasi terkait.

**Fitur Utama:**
- Manajemen produk
- Upload foto produk
- Kategori produk
- Harga jual dan stok

**Implementasi:**
- Controller: `CatalogController.php`
- Model: `Produk.php`
- Views: `resources/views/catalog/`
- Route: `/catalog` dengan middleware auth

## 2.4 Teknologi dan Tools

### 2.4.1 Backend Development

**Framework dan Library:**
- Laravel 10: PHP framework untuk backend development
- MySQL: Database management system
- Eloquent ORM: Object-relational mapping
- Blade Template Engine: Template rendering

**Development Tools:**
- Composer: Dependency management
- Artisan: Command line interface
- Migration: Database version control

### 2.4.2 Frontend Development

**UI Framework:**
- Bootstrap 5: CSS framework untuk responsive design
- DataTables: Server-side processing untuk tabel data
- Chart.js: Visualisasi data dashboard
- Font Awesome: Icon library

**JavaScript:**
- jQuery: JavaScript library
- AJAX: Asynchronous data processing
- Vanilla JavaScript: DOM manipulation

### 2.4.3 Development Environment

**Local Development:**
- XAMPP: Web server dengan Apache dan MySQL
- Laravel Valet: Local development environment
- Git: Version control system

**Deployment:**
- Shared hosting dengan cPanel
- MySQL database
- PHP 8.1+ requirement

## 2.5 Metodologi Pengembangan

### 2.5.1 Agile Development

Proses pengembangan menggunakan metode Agile dengan sprint cycle 2 minggu. Setiap sprint meliputi:

**Sprint Planning:**
- Penentuan fitur yang akan dikembangkan
- Estimasi waktu dan effort
- Prioritasi fitur berdasarkan kebutuhan

**Sprint Execution:**
- Implementasi fitur sesuai prioritas
- Code review dan refactoring
- Unit testing untuk setiap modul

**Sprint Review:**
- Demo fitur yang telah selesai
- Feedback dari pembimbing
- Adjustments untuk sprint berikutnya

### 2.5.2 Testing Methodology

**Black Box Testing:**
- Functional testing untuk setiap modul
- User acceptance testing
- Integration testing antar modul
- Performance testing untuk load testing

**Test Scenarios:**
- Input validation testing
- Workflow testing
- Error handling testing
- Security testing basic

## 2.6 Implementasi Multi-Tenant

### 2.6.1 Data Isolation

Sistem mengimplementasikan multi-tenant architecture untuk mendukung multiple company dalam satu aplikasi.

**Implementasi:**
- Setiap tabel memiliki `user_id` untuk filtering
- Middleware untuk otentikasi dan autorisasi
- Query scopes untuk data filtering otomatis

### 2.6.2 Security Features

**Keamanan Data:**
- Password hashing dengan bcrypt
- CSRF protection
- Input validation dan sanitization
- SQL injection prevention

## 2.7 Deployment dan Maintenance

### 2.7.1 Deployment Process

**Production Deployment:**
- Upload files ke shared hosting
- Database migration
- Environment configuration
- Cache optimization

### 2.7.2 Maintenance Strategy

**Maintenance Plan:**
- Regular backup database
- Update security patches
- Performance monitoring
- User support dan training

---

**Kesimpulan Pembuatan Aplikasi:**

Aplikasi berhasil dikembangkan dengan semua modul sesuai kebutuhan. Implementasi multi-tenant architecture memungkinkan sistem digunakan oleh multiple perusahaan dengan data yang terisolasi. Penggunaan Laravel framework dan Agile development methodology memastikan aplikasi berkualitas dan mudah dikembangkan lebih lanjut.

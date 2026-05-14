# Dokumentasi Tugas Akhir
## "Aplikasi Web untuk Pengelolaan Biaya Produksi dan Perhitungan Harga Pokok Produksi"

### Ringkasan Proyek
Aplikasi UMKM_COE adalah sistem berbasis web Laravel yang dirancang khusus untuk pengelolaan biaya produksi dan perhitungan harga pokok produksi (HPP) pada Usaha Mikro Kecil Menengah (UMKM). Aplikasi ini mengintegrasikan seluruh komponen biaya produksi mulai dari bahan baku hingga produk jadi.

---

## 🎯 Fitur Utama yang Telah Diimplementasikan

### 1. **Biaya Bahan Baku (BBB)**
- **Controller**: `BiayaBahanController.php`
- **Views**: `resources/views/master-data/biaya-bahan/`
- **Fitur**:
  - Input biaya bahan baku per produk
  - Konversi satuan otomatis
  - Perhitungan subtotal otomatis
  - Multi-tenant (filter per user)

### 2. **Biaya Tenaga Kerja Langsung (BTKL)**
- **Controller**: `MasterData/BtklController.php`
- **Views**: `resources/views/master-data/btkl/`
- **Fitur**:
  - Manajemen proses produksi dan jabatan
  - Perhitungan tarif per unit
  - Integrasi dengan data pegawai
  - Statistik lengkap

### 3. **Biaya Overhead Produksi (BOP)**
- **Controller**: `BopController.php`, `BopBudgetController.php`
- **Views**: `resources/views/master-data/bop/`
- **Fitur**:
  - Budget management per periode
  - Integrasi dengan COA (Chart of Accounts)
  - Perhitungan otomatis beban gaji BTKTL
  - Tracking aktual vs budget

### 4. **Perhitungan Harga Pokok Produksi (HPP)**
- **Controller**: `HppController.php` (NEW)
- **Views**: `resources/views/hpp/` (NEW)
- **Fitur**:
  - Dashboard HPP lengkap
  - Perhitungan otomatis BBB + BTKL + BOP
  - Analisis margin keuntungan
  - Re-kalkulasi otomatis
  - API endpoint untuk integrasi

### 5. **Manajemen Produksi**
- **Controller**: `ProduksiController.php`
- **Views**: `resources/views/transaksi/produksi/`
- **Fitur**:
  - Tracking produksi harian
  - Integrasi dengan HPP
  - Stock management otomatis
  - Journal entries otomatis

### 6. **Sistem Pembayaran Beban**
- **Controller**: `Transaksi/PembayaranBebanController.php`
- **Views**: `resources/views/transaksi/pembayaran-beban/`
- **Fitur**:
  - Pembayaran beban operasional
  - Integrasi COA
  - Filter dan laporan

### 7. **Laporan Keuangan**
- **Laporan Stok**: `resources/views/laporan/stok/`
- **Laporan Kas & Bank**: `resources/views/laporan/kas-bank/`
- **Jurnal Umum**: `resources/views/akuntansi/jurnal-umum/`
- **Laporan Pembayaran Beban**: `resources/views/laporan/pembayaran-beban/`

### 8. **Kelola Catalog**
- **Controller**: `CatalogController.php`
- **Views**: `resources/views/catalog/`, `resources/views/kelola-catalog/`
- **Fitur**:
  - Manajemen produk catalog
  - Upload foto produk
  - SEO optimization

---

## 🏗️ Arsitektur Sistem

### Database Structure
```
├── Produk (Master Data Produk)
├── Bahan Baku (Raw Materials)
├── Bahan Pendukung (Support Materials)
├── Biaya Bahan Baku (Material Costs)
├── BTKL (Direct Labor Costs)
├── BOP (Overhead Production Costs)
├── Bom Job Costing (HPP Calculations)
├── Produksi (Production Records)
├── Stock Movements (Inventory Tracking)
├── COA (Chart of Accounts)
└── Jurnal (Financial Records)
```

### Multi-Tenant Architecture
- Semua model memiliki `user_id` filter
- Isolasi data per user
- Security layers pada setiap level

---

## 🚀 Cara Penggunaan

### 1. Setup Awal
1. Login ke sistem
2. Setup COA (Chart of Accounts)
3. Input data master (produk, bahan, pegawai)

### 2. Perhitungan HPP
1. **Input Biaya Bahan Baku**
   - Menu: Master Data → Biaya Bahan
   - Pilih produk dan input bahan-bahan

2. **Setup BTKL**
   - Menu: Master Data → BTKL
   - Define proses dan jabatan

3. **Setup BOP**
   - Menu: Master Data → BOP
   - Input budget overhead

4. **Hitung HPP**
   - Menu: HPP → Dashboard
   - Klik "Hitung HPP" untuk produk yang dipilih

### 3. Produksi
1. **Record Produksi**
   - Menu: Transaksi → Produksi
   - Input jumlah produksi

2. **Stock Update Otomatis**
   - Sistem otomatis update stok
   - Generate journal entries

### 4. Laporan
1. **Laporan HPP**: Dashboard HPP
2. **Laporan Stok**: Laporan → Stok
3. **Laporan Keuangan**: Akuntansi → Jurnal Umum

---

## 📊 Formul HPP

```
HPP per Unit = BBB per Unit + BTKL per Unit + BOP per Unit

Dimana:
- BBB = Total biaya bahan baku / jumlah produk
- BTKL = Total biaya tenaga kerja langsung / jumlah produk  
- BOP = Total biaya overhead produksi / jumlah produk

Margin = ((Harga Jual - HPP) / Harga Jual) × 100%
```

---

## 🔧 Teknologi

### Backend
- **Framework**: Laravel 9+
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Auth
- **Multi-tenant**: User-based filtering

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Bootstrap 5
- **JavaScript**: jQuery, DataTables
- **Icons**: Font Awesome

### Libraries
- **PDF Generation**: DomPDF
- **Excel Export**: Laravel Excel
- **Date/Time**: Carbon
- **Validation**: Laravel Validator

---

## 📁 Struktur File Penting

### Controllers
```
app/Http/Controllers/
├── HppController.php (NEW)
├── BiayaBahanController.php
├── MasterData/BtklController.php
├── BopController.php
├── ProduksiController.php
├── Transaksi/PembayaranBebanController.php
└── AkuntansiController.php
```

### Views
```
resources/views/
├── hpp/ (NEW)
│   ├── index.blade.php
│   ├── show.blade.php
│   └── create.blade.php
├── master-data/
│   ├── biaya-bahan/
│   ├── btkl/
│   └── bop/
├── transaksi/
│   ├── produksi/
│   └── pembayaran-beban/
└── laporan/
```

### Models
```
app/Models/
├── BomJobCosting.php
├── BiayaBahanBaku.php
├── Btkl.php
├── Bop.php
├── Produksi.php
└── PembayaranBeban.php
```

---

## 🎨 UI/UX Features

### Dashboard
- Summary cards dengan statistik real-time
- Quick access buttons
- Visual indicators untuk status HPP

### Forms
- Dynamic form validation
- Auto-calculation features
- Responsive design
- User-friendly inputs

### Reports
- Export PDF/Excel
- Filterable data tables
- Interactive charts
- Print-friendly layouts

---

## 🔐 Security Features

1. **Authentication**
   - User login/logout
   - Session management
   - Password hashing

2. **Authorization**
   - Role-based access control
   - Multi-tenant data isolation
   - User-specific data filtering

3. **Data Validation**
   - Input sanitization
   - Server-side validation
   - SQL injection prevention

---

## 🚀 Deployment

### Local Development
```bash
# Clone repository
git clone <repository-url>
cd UMKM_COE

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Start server
php artisan serve
```

### Production
- Web server: Apache/Nginx
- PHP 8.0+
- MySQL 5.7+
- SSL certificate recommended

---

## 📈 Performance Optimization

1. **Database Indexing**
   - Proper indexes on user_id columns
   - Composite indexes for complex queries

2. **Caching**
   - Query caching for reports
   - Session caching
   - Asset optimization

3. **Code Optimization**
   - Eager loading relationships
   - Efficient queries
   - Memory management

---

## 🧪 Testing

### Manual Testing Checklist
- [ ] User registration/login
- [ ] Input biaya bahan baku
- [ ] Setup BTKL dan BOP
- [ ] Perhitungan HPP
- [ ] Record produksi
- [ ] Generate laporan
- [ ] Multi-tenant isolation

### Automated Testing
```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter HppTest
```

---

## 🔄 Future Enhancements

1. **Advanced Analytics**
   - Trend analysis
   - Cost variance analysis
   - Profit optimization

2. **Mobile App**
   - React Native app
   - Offline capabilities
   - Push notifications

3. **Integration**
   - Accounting software API
   - Payment gateway
   - Inventory scanners

4. **AI Features**
   - Cost prediction
   - Demand forecasting
   - Price optimization

---

## 👥 Tim Pengembang

- **Developer**: [Your Name]
- **Project Type**: Tugas Akhir
- **Institution**: [Your Institution]
- **Year**: 2026

---

## 📞 Support

Untuk bantuan teknis:
- Email: [your-email@example.com]
- Documentation: `DOKUMENTASI_TUGAS_AKHIR.md`
- Issue Tracker: GitHub Issues

---

## 📜 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

**Status: ✅ COMPLETED - Ready for Production**

*Aplikasi ini telah selesai dikembangkan dan siap digunakan untuk pengelolaan biaya produksi dan perhitungan harga pokok produksi pada UMKM.*

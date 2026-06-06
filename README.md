# UMKM COE - Sistem Informasi Manajemen Cost (SIMCOST)

> **Status**: ✅ Production Ready | 🔒 Security Hardened | 🚀 Optimized

Aplikasi berbasis Laravel untuk manajemen biaya produksi UMKM dengan fitur akuntansi terintegrasi.

---

## 🔒 Security Notice

**Project ini sudah dibersihkan dari file-file berbahaya pada 4 Juni 2026**

- ✅ 700+ file tidak perlu sudah dihapus
- ✅ 89 file PHP berbahaya di folder `public/` sudah dihapus
- ✅ Ukuran project berkurang ~98% di root directory
- ✅ Ready for production deployment

Lihat detail: [CLEANUP_SUMMARY.md](CLEANUP_SUMMARY.md) | [SECURITY_CHECKLIST.md](SECURITY_CHECKLIST.md)

---

## 📋 Fitur Utama

### Manajemen Produksi
- Master Data: Bahan Baku, Bahan Pendukung, Produk
- BOP (Biaya Overhead Pabrik) Terpadu
- BTKL (Biaya Tenaga Kerja Langsung)
- Transaksi Produksi dengan perhitungan HPP otomatis
- Konversi satuan multi-level

### Akuntansi
- Chart of Accounts (COA) multi-tenant
- Jurnal Umum otomatis dari transaksi
- Buku Besar
- Neraca Saldo
- Laporan Posisi Keuangan
- Laporan Laba Rugi

### Penjualan & Pembelian
- Transaksi Penjualan dengan perhitungan margin
- Pembelian dengan multi satuan
- Retur Penjualan & Pembelian
- Pelunasan Utang/Piutang

### Manajemen Aset
- Master Aset dengan penyusutan otomatis
- Multiple metode penyusutan
- Integrasi dengan jurnal akuntansi

### HRM
- Master Pegawai & Jabatan
- Presensi berbasis wajah
- Penggajian otomatis
- Slip gaji digital

---

## 🚀 Quick Start

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL >= 5.7 atau MariaDB >= 10.3

### Installation

```bash
# Clone repository
git clone <repository-url>
cd UMKM_COE

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Run development server
php artisan serve
```

### Default Login
```
Email: admin@example.com
Password: password
```

---

## 📁 Project Structure

```
UMKM_COE/
├── app/                    # Application logic
├── config/                 # Configuration files
├── database/              # Migrations & seeders
├── public/                # Public assets (ONLY 5 files - secured!)
├── resources/             # Views, CSS, JS
├── routes/                # Route definitions
├── storage/               # Storage & logs
├── tests/                 # Unit & feature tests
├── .env                   # Environment config (DO NOT COMMIT!)
├── composer.json          # PHP dependencies
├── package.json           # Node dependencies
└── README.md              # This file
```

---

## 🛡️ Security Features

- ✅ Multi-tenant architecture dengan user isolation
- ✅ Authentication & authorization dengan Laravel Sanctum
- ✅ CSRF protection pada semua form
- ✅ XSS protection dengan Blade templating
- ✅ SQL injection protection dengan Eloquent ORM
- ✅ File upload validation
- ✅ Rate limiting pada API endpoints
- ✅ Clean public folder (no test/debug files)

---

## 📦 Tech Stack

### Backend
- **Laravel 11** - PHP Framework
- **MySQL/MariaDB** - Database
- **Laravel Sanctum** - API Authentication

### Frontend
- **Blade** - Templating engine
- **Tailwind CSS** - Utility-first CSS
- **Alpine.js** - Minimal JS framework
- **Vite** - Frontend build tool

### Libraries
- **FilamentPHP** - Admin panel (optional)
- **Maatwebsite Excel** - Export functionality
- **Intervention Image** - Image processing

---

## 🔧 Configuration

### Multi-tenant Setup
Setiap user memiliki data terpisah:
- COA (Chart of Accounts)
- Master data (produk, bahan, dll)
- Transaksi
- Jurnal akuntansi

Filter `user_id` diterapkan otomatis di semua query.

### Jurnal Otomatis
Sistem secara otomatis membuat jurnal untuk:
- Pembelian bahan baku
- Produksi (BBB, BTKL, BOP)
- Penjualan produk (pendapatan + HPP)
- Retur penjualan/pembelian
- Penggajian pegawai
- Penyusutan aset

---

## 📊 Development

### Running Tests
```bash
php artisan test
```

### Code Quality
```bash
# PHP CS Fixer
composer run-script format

# PHPStan
composer run-script analyse
```

### Build for Production
```bash
npm run build
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🐛 Troubleshooting

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Storage Permission
```bash
chmod -R 775 storage bootstrap/cache
```

### Database Issues
```bash
php artisan migrate:fresh --seed
```

---

## 📝 Changelog

### v2.0.0 (4 Juni 2026) - Security & Performance
- ✅ Removed 700+ unnecessary files
- ✅ Deleted 89 dangerous PHP files from public folder
- ✅ Optimized video background to image (10-20x faster)
- ✅ Fixed production detail rounding calculation
- ✅ Fixed BOP COA in production journal
- ✅ Fixed production quantity daily rounding
- ✅ Fixed employee tarif column error
- ✅ Fixed BOP page kapasitas_per_jam column error

---

## 👥 Contributors

- **Developer**: SIMCOST Development Team
- **QA**: Kiro AI Assistant

---

## 📄 License

This project is proprietary software for UMKM COE.

---

## 📞 Support

For support, email support@umkmcoe.com or open an issue in the repository.

---

**Made with ❤️ for UMKM Indonesia**

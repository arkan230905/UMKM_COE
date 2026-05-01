# UMKM COE - Multi-Tenant Hosting Guide

## Overview
This document provides comprehensive instructions for hosting the UMKM COE application with multi-tenant architecture. Each company's data is completely isolated to prevent data leakage between organizations.

## Multi-Tenant Architecture

### Data Isolation Strategy
- **Application-Level Filtering**: Data is filtered by `company_id` and `user_id` columns
- **Company-Based Segregation**: Each company has its own dataset
- **User Authentication**: Users can only access their own company's data
- **Secure Data Access**: All queries include company filtering

### Database Schema
- **Companies**: Each company has unique ID and user associations
- **Users**: Linked to specific companies via `perusahaan_id` and `company_id`
- **COA Accounts**: 50 default accounts per company (as requested)
- **Satuan Units**: 16 default units per company (as requested)
- **Critical Tables**: All have `user_id` or `company_id` for isolation

## Pre-Hosting Setup

### 1. Database Configuration
```sql
-- Create database for hosting
CREATE DATABASE umkm_coe_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with appropriate permissions
CREATE USER 'umkm_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON umkm_coe_production.* TO 'umkm_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_DATABASE=umkm_coe_production
DB_USERNAME=umkm_user
DB_PASSWORD=strong_password

# Multi-tenant settings
TENANT_MODE=multi
DEFAULT_COMPANY_SETUP=true
```

### 3. Run Migrations and Seeders
```bash
# Run all migrations
php artisan migrate --force

# Run multi-tenant seeders
php artisan db:seed --force

# Or run specific seeders
php artisan db:seed --class=CompanySeeder --force
php artisan db:seed --class=CoaDefaultSeeder --force
php artisan db:seed --class=SatuanDefaultSeeder --force
```

## Multi-Tenant Data Structure

### Company Setup
Each company gets:
- **Unique Company ID**: For data segregation
- **Admin User**: Company owner account
- **COA Accounts**: 50 default accounts (see list below)
- **Satuan Units**: 16 default units (see list below)
- **Isolated Database**: All data filtered by company

### Default COA Accounts (50 Accounts)

#### ASET (21 Accounts)
| No | Nama Akun | Kode Akun | Tipe | Saldo Awal |
|----|-----------|-----------|------|------------|
| 1 | Aset | 11 | Aset | 0 |
| 2 | Kas Bank | 111 | Aset | 100.000.000 |
| 3 | Kas | 112 | Aset | 75.000.000 |
| 4 | Kas Kecil | 113 | Aset | 0 |
| 5 | Pers. Bahan Baku | 114 | Aset | 0 |
| 6 | Pers. Bahan Baku Jagung | 1141 | Aset | 0 |
| 7 | Pers. Bahan Pendukung | 115 | Aset | 0 |
| 8 | Pers. Bahan Pendukung Susu | 1151 | Aset | 0 |
| 9 | Pers. Bahan Pendukung Keju | 1152 | Aset | 0 |
| 10 | Pers. Bahan Pendukung Kemasan (Cup) | 1153 | Aset | 0 |
| 11 | Pers. Barang Jadi | 116 | Aset | 0 |
| 12 | Pers. Barang Jadi Jasuke | 1161 | Aset | 0 |
| 13 | Pers. Barang dalam Proses | 117 | Aset | 0 |
| 14 | Pers. Barang Dalam Proses - BBB | 1171 | Aset | 0 |
| 15 | Pers. Barang Dalam Proses - BTKL | 1172 | Aset | 0 |
| 16 | Pers. Barang Dalam Proses - BOP | 1173 | Aset | 0 |
| 17 | Piutang | 118 | Aset | 0 |
| 18 | Peralatan | 119 | Aset | 0 |
| 19 | Akumulasi Penyusutan Peralatan | 120 | Aset | 0 |
| 20 | Mesin | 125 | Aset | 0 |
| 21 | Akumulasi Penyusutan Mesin | 126 | Aset | 0 |
| 22 | PPN Masukkan | 127 | Aset | 0 |

#### KEWAJIBAN (4 Accounts)
| No | Nama Akun | Kode Akun | Tipe | Saldo Awal |
|----|-----------|-----------|------|------------|
| 23 | Hutang | 21 | Kewajiban | 0 |
| 24 | Hutang Usaha | 210 | Kewajiban | 0 |
| 25 | Hutang Gaji | 211 | Kewajiban | 0 |
| 26 | PPN Keluaran | 212 | Kewajiban | 0 |

#### EKUITAS/MODAL (3 Accounts)
| No | Nama Akun | Kode Akun | Tipe | Saldo Awal |
|----|-----------|-----------|------|------------|
| 27 | Modal | 31 | Equity | 0 |
| 28 | Modal Usaha | 310 | Equity | 176.164.000 |
| 29 | Prive | 311 | Modal | 0 |

#### PENDAPATAN (3 Accounts)
| No | Nama Akun | Kode Akun | Tipe | Saldo Awal |
|----|-----------|-----------|------|------------|
| 30 | Penjualan | 41 | Pendapatan | 0 |
| 31 | Penjualan - Jasuke | 410 | Pendapatan | 0 |
| 32 | Retur Penjualan | 42 | Pendapatan | 0 |

#### BIAYA (19 Accounts)
| No | Nama Akun | Kode Akun | Tipe | Saldo Awal |
|----|-----------|-----------|------|------------|
| 33 | BBB - Biaya Bahan Baku | 51 | Biaya | 0 |
| 34 | BBB - Jagung | 510 | Biaya | 0 |
| 35 | Beban Tunjangan | 513 | Equity | 0 |
| 36 | Beban Asuransi | 514 | Equity | 0 |
| 37 | Beban Bonus | 515 | Equity | 0 |
| 38 | Potongan Gaji | 516 | Equity | 0 |
| 39 | BTKL | 52 | Biaya | 0 |
| 40 | BTKL - Produksi Jasuke | 520 | Biaya | 0 |
| 41 | BOP | 53 | Biaya | 0 |
| 42 | BOP - Susu | 530 | Biaya | 0 |
| 43 | BOP - Keju | 531 | Biaya | 0 |
| 44 | BOP - Kemasan | 532 | Biaya | 0 |
| 45 | Beban Sewa | 54 | Expense | 0 |
| 46 | BOP Lain | 55 | Biaya | 0 |
| 47 | BOP - Listrik | 550 | Biaya | 0 |
| 48 | BOP - Air | 551 | Biaya | 0 |
| 49 | BOP - Gas | 552 | Biaya | 0 |
| 50 | BOP - Penyusutan Peralatan | 553 | Biaya | 0 |

### Default Satuan Units (16 Units)
| Kode | Nama Satuan |
|------|-------------|
| ONS | Ons |
| KG | Kilogram |
| ML | Mililiter |
| G | Gram |
| LTR | Liter |
| PTG | Potong |
| EKOR | Ekor |
| SDT | Sendok Teh |
| SDM | Sendok Makan |
| PCS | Pieces |
| BNGKS | Bungkus |
| CUP | Cup |
| GL | Galon |
| TBG | Tabung |
| SNG | Siung |
| KLG | Kaleng |

## Security Implementation

### Data Access Control
```php
// Example of data filtering in controllers
public function index()
{
    $user = auth()->user();
    $coas = Coa::where('company_id', $user->company_id)->get();
    return response()->json($coas);
}
```

### Authentication Flow
1. **User Login**: User authenticates with email/password
2. **Company Check**: System verifies user's company association
3. **Data Filtering**: All queries automatically filter by user's company
4. **Session Management**: User session includes company context

### Query Filtering Examples
```php
// COA Queries
Coa::where('company_id', auth()->user()->company_id)->get();

// Product Queries
Produk::where('user_id', auth()->user()->id)->get();

// Journal Queries
JurnalUmum::where('user_id', auth()->user()->id)->get();
```

## Hosting Deployment

### 1. Upload Files
```bash
# Upload all application files to server
rsync -avz ./ user@server:/var/www/html/umkm-coe/

# Set appropriate permissions
chmod -R 755 /var/www/html/umkm-coe/
chmod -R 777 /var/www/html/umkm-coe/storage/
```

### 2. Install Dependencies
```bash
cd /var/www/html/umkm-coe/
composer install --no-dev --optimize-autoloader
```

### 3. Environment Setup
```bash
# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Update database and other settings
# Edit .env file with production values
```

### 4. Run Setup Commands
```bash
# Clear and cache configurations
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Create storage link
php artisan storage:link
```

### 5. Web Server Configuration

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/umkm-coe/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Multi-Tenant Verification

### Test Data Isolation
```bash
# Run verification script
php test_multi_tenant_application_level.php

# Expected output:
# - Each company has 50 COA accounts
# - Each company has 16 satuan units
# - Data is properly isolated by company
```

### Manual Testing
1. **Login as Company A**: Verify only Company A data visible
2. **Login as Company B**: Verify only Company B data visible
3. **Cross-Company Test**: Ensure Company A cannot access Company B data
4. **Data Creation**: Test creating data stays within company scope

## Troubleshooting

### Common Issues

1. **Data Leakage**: Check if queries include company filtering
   ```php
   // Wrong: No company filtering
   $data = Coa::all();
   
   // Correct: With company filtering
   $data = Coa::where('company_id', auth()->user()->company_id)->get();
   ```

2. **Missing Company Association**: Verify user has perusahaan_id
   ```php
   $user = User::find($userId);
   if (!$user->perusahaan_id) {
       // Link user to company
       $user->update(['perusahaan_id' => $companyId]);
   }
   ```

3. **Seeder Issues**: Run seeders in correct order
   ```bash
   php artisan db:seed --class=CompanySeeder --force
   php artisan db:seed --class=CoaDefaultSeeder --force
   php artisan db:seed --class=SatuanDefaultSeeder --force
   ```

### Performance Considerations
- **Database Indexing**: Ensure company_id columns are indexed
- **Query Optimization**: Use company filtering in all queries
- **Caching**: Implement per-company caching strategies
- **Connection Pooling**: Consider database connection management

## Support and Maintenance

### Regular Tasks
1. **Database Backups**: Per-company backup strategies
2. **User Management**: Monitor user-company associations
3. **Data Auditing**: Regular checks for data isolation
4. **Performance Monitoring**: Track query performance per company

### Scaling Considerations
- **Database Sharding**: Consider per-company databases for large scale
- **Load Balancing**: Distribute load across multiple servers
- **CDN Integration**: Static content delivery optimization

## Summary

The UMKM COE application is now ready for multi-tenant hosting with:
- **Complete Data Isolation**: Each company's data is completely separated
- **Standard COA Setup**: 50 accounts per company as requested
- **Complete Satuan Setup**: 16 units per company as requested
- **Security Implementation**: Application-level data filtering
- **Hosting Ready**: All migrations and seeders prepared

The application ensures that **data perusahaan tidak akan kecampur campur antar perusahaan** and is ready for global hosting deployment.

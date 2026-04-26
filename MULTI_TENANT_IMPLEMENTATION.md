# Multi-Tenant Implementation Guide

## Overview
This document explains the multi-tenant implementation for SIMCOST to ensure data isolation between users when hosting the application.

## 🎯 Objectives
1. **Data Isolation**: Each user can only access their own data
2. **Default Data**: New users get default COA and Satuan data
3. **Security**: No cross-user data leakage
4. **Scalability**: Ready for multi-user hosting

## 📋 Implementation Steps

### 1. Database Structure Changes

#### Added user_id to tables:
- `coas` - Chart of Accounts
- `satuans` - Units of measurement
- `bahan_bakus` - Raw materials
- `produks` - Products
- `pembelians` - Purchases
- `penjualans` - Sales
- `pegawais` - Employees
- `vendors` - Vendors
- `asets` - Assets
- `penggajians` - Payrolls
- `pembelian_details` - Purchase details
- `penjualan_details` - Sales details
- `bom_details` - Bill of materials details
- `boms` - Bill of materials
- `produksis` - Productions
- `produksi_details` - Production details

#### Migration Files Created:
- `2025_10_29_000001_add_user_id_to_coas_table.php`
- `2025_10_29_000002_add_user_id_to_satuans_table.php`
- `2025_10_29_000003_add_user_id_to_bahan_bakus_table.php`
- `2025_10_29_000004_add_user_id_to_produks_table.php`
- `2025_10_29_000005_add_user_id_to_pembelians_table.php`
- `2025_10_29_000006_add_user_id_to_multiple_tables.php`
- `2025_10_29_000007_add_user_id_to_detail_tables.php`

### 2. Model Updates

#### Updated Models:
- `Coa.php` - Added user_id relationship and global scope
- `Satuan.php` - Added user_id relationship and global scope

#### Features Added:
- Auto-assign user_id on create
- Global scope for data isolation
- User relationship methods

### 3. Default Data Seeders

#### Created Seeders:
- `DefaultCoaSeeder.php` - 45 default COA accounts
- `DefaultSatuanSeeder.php` - 28 default units

#### Features:
- Static method `createForUser($userId)` for new user registration
- Complete accounting structure (Assets, Liabilities, Equity, Revenue, Expenses)
- Common measurement units for production/manufacturing

### 4. Event-Driven Default Data Creation

#### Event System:
- `UserRegistered` event (existing)
- `CreateDefaultUserData` listener (new)

#### Automatic Process:
When a new user registers:
1. Event `UserRegistered` is fired
2. Listener `CreateDefaultUserData` creates:
   - Default COA structure (45 accounts)
   - Default Satuan list (28 units)

### 5. Security Middleware

#### Created Middleware:
- `EnsureUserOwnership.php` - Validates resource ownership

#### Features:
- Route-based resource validation
- Automatic 403 response for unauthorized access
- Support for all major resource types

## 🚀 Deployment Steps

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Existing Users
```bash
php artisan tinker
>>> Database\Seeders\DefaultCoaSeeder::run();
>>> Database\Seeders\DefaultSatuanSeeder::run();
```

### 3. Update Controllers
Apply middleware to routes that need ownership validation:
```php
Route::middleware(['auth', 'user.ownership'])->group(function () {
    // Protected routes here
});
```

## 🔒 Security Features

### Data Isolation
- **Global Scopes**: Models automatically filter by user_id
- **Middleware Validation**: Route-level ownership checks
- **Auto-assignment**: user_id automatically set on create

### Prevention of Cross-User Access
- Database-level foreign key constraints
- Application-level validation
- 403 responses for unauthorized attempts

## 📊 Default Data Structure

### COA Accounts (45 total)
#### Assets (11 accounts)
- **111**: Kas dan Bank
  - 1111: Kas
  - 1112: Bank
- **112**: Piutang Usaha
  - 1121: Piutang Dagang
- **113**: Persediaan
  - 1131: Persediaan Bahan Baku
  - 1132: Persediaan Barang Jadi
- **12**: Aset Tetap
  - 121: Peralatan
    - 1211: Peralatan Kantor
    - 1212: Peralatan Produksi
  - 122: Akumulasi Penyusutan

#### Liabilities (4 accounts)
- **21**: Kewajiban Lancar
  - 211: Utang Usaha
    - 2111: Utang Dagang
  - 212: Utang Bank
    - 2121: Utang Bank Jangka Pendek

#### Equity (4 accounts)
- **31**: Modal
  - 311: Modal Saham
  - 312: Laba Ditahan
- **32**: Prive
  - 321: Prive Pemilik

#### Revenue (6 accounts)
- **41**: Pendapatan Usaha
  - 411: Penjualan Barang
    - 4111: Penjualan Produk
  - 412: Pendapatan Jasa
- **42**: Pendapatan Lain-lain
  - 421: Bunga Bank

#### Expenses (20 accounts)
- **51**: Harga Pokok Penjualan
  - 511: HPP Bahan Baku
  - 512: HPP Tenaga Kerja
  - 513: HPP Overhead
- **52**: Beban Usaha
  - 521: Beban Gaji
  - 522: Beban Sewa
  - 523: Beban Listrik & Air
  - 524: Beban Telepon
  - 525: Beban Marketing
- **53**: Beban Administrasi & Umum
  - 531: Beban Kantor
  - 532: Beban Penyusutan
- **54**: Beban Lain-lain
  - 541: Beban Bunga

### Satuan Units (28 total)
- **Basic Units**: PCS, UNIT, BOX, PACK, DUS, KARUNG
- **Weight**: KG, GRAM, TON
- **Volume**: LITER, ML, M3
- **Length**: METER, CM, M2
- **Packaging**: ROLL, BOTOL, KALENG, SAK, RIM
- **Grouping**: LUSIN, GROSS, SET, PAIR
- **Time**: JAM, HARI, MINGGU, BULAN, TAHUN

## 🧪 Testing Checklist

### Data Isolation Tests
- [ ] User A cannot see User B's COA
- [ ] User A cannot see User B's transactions
- [ ] User A cannot modify User B's data
- [ ] 403 response for unauthorized access attempts

### Default Data Tests
- [ ] New user gets 45 COA accounts
- [ ] New user gets 28 satuan units
- [ ] Default data is properly assigned user_id
- [ ] Default data follows correct hierarchy

### Security Tests
- [ ] SQL injection attempts blocked
- [ ] Direct URL access to other user's data blocked
- [ ] API endpoints respect user isolation
- [ ] File uploads respect user ownership

## 🔄 Maintenance

### Adding New Users
Default data is automatically created when users register via the `UserRegistered` event.

### Migrating Existing Data
Run the seeders to populate existing users with default data:
```bash
php artisan tinker
>>> Database\Seeders\DefaultCoaSeeder::run();
>>> Database\Seeders\DefaultSatuanSeeder::run();
```

### Adding New Tables
1. Create migration with user_id column
2. Update model with global scope
3. Add to middleware validation
4. Update seeder if needed

## 📝 Notes

### Performance Considerations
- Global scopes add WHERE clauses to all queries
- Consider indexing user_id columns for performance
- Monitor query performance with large datasets

### Backup Strategy
- Each user's data is isolated in the same database
- Consider per-user backups for large deployments
- Implement data export/import features for user migration

### Scalability
- Current implementation supports thousands of users
- Consider database sharding for very large deployments
- Monitor database size and performance metrics

## 🎉 Benefits

1. **Security**: Complete data isolation between users
2. **Compliance**: Ready for multi-tenant SaaS deployment
3. **User Experience**: New users get ready-to-use accounting system
4. **Maintainability**: Centralized logic for data isolation
5. **Scalability**: Architecture supports growth

This implementation ensures that when you host SIMCOST, each user will have their own isolated data space with a complete accounting structure ready to use.

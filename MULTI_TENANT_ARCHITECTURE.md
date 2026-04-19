# Arsitektur Multi-Tenant - Sistem UMKM

## Overview

Aplikasi ini menggunakan arsitektur **multi-tenant** yang memungkinkan banyak pebisnis (tenant) menggunakan aplikasi yang sama dengan data yang terisolasi dan data master yang shared.

## Strategi Data

### 1. Data Per Company (Isolated)
Data yang bersifat unik untuk setiap perusahaan:
- **Chart of Accounts (COA)** - Setiap perusahaan punya struktur akun sendiri
- **Transaksi** (Pembelian, Penjualan, Produksi, dll)
- **Laporan Keuangan**

### 2. Data Global (Shared)
Data master yang digunakan bersama oleh semua perusahaan:
- **Satuan** (Kg, Liter, Pcs, dll) - 15 satuan standar
- **Jabatan/Kualifikasi Tenaga Kerja** - 8 jabatan (BTKL & BTKTL)
- **Pegawai Template** - 5 pegawai contoh

## Flow Registrasi User Baru

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER REGISTRASI                              │
│                                                                 │
│  1. User mengisi form registrasi                               │
│  2. Sistem membuat:                                            │
│     - Record User                                              │
│     - Record Perusahaan                                        │
│  3. Trigger Event: UserRegistered                             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│              LISTENER: SetupUserData                            │
│                                                                 │
│  Menangkap event dan menjalankan seeder:                       │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. CoaTemplateSeeder                                     │  │
│  │    - Copy template COA untuk company ini                 │  │
│  │    - Bersifat PER COMPANY                                │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 2. JabatanSeeder                                         │  │
│  │    - Seed 8 jabatan standar (jika belum ada)            │  │
│  │    - Bersifat GLOBAL (shared)                            │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 3. PegawaiSeeder                                         │  │
│  │    - Seed 5 pegawai contoh (jika belum ada)             │  │
│  │    - Bersifat GLOBAL (shared)                            │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Note: SatuanSeeder sudah dijalankan saat db:seed awal        │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│                  HASIL AKHIR                                    │
│                                                                 │
│  User baru langsung memiliki:                                  │
│  ✓ COA lengkap (per company)                                   │
│  ✓ 15 Satuan standar (global)                                  │
│  ✓ 8 Jabatan standar (global)                                  │
│  ✓ 5 Pegawai contoh (global)                                   │
│                                                                 │
│  Siap digunakan tanpa setup manual!                            │
└─────────────────────────────────────────────────────────────────┘
```

## Struktur Database

### Tabel dengan Company ID (Isolated)
```
coas
├── id
├── company_id          ← Isolasi per company
├── kode_akun
├── nama_akun
└── ...

pembelian
├── id
├── company_id          ← Isolasi per company
├── tanggal
└── ...

penjualan
├── id
├── company_id          ← Isolasi per company
├── tanggal
└── ...
```

### Tabel Global (Shared)
```
satuans
├── id
├── kode                ← Tidak ada company_id
├── nama
└── ...

jabatans
├── id
├── kode_jabatan        ← Tidak ada company_id
├── nama
├── kategori (btkl/btktl)
└── ...

pegawais
├── id
├── kode_pegawai        ← Tidak ada company_id
├── nama
├── jabatan_id
└── ...
```

## Keuntungan Arsitektur Ini

### 1. Onboarding Cepat
- User baru langsung bisa menggunakan aplikasi
- Tidak perlu setup manual data master
- Mengurangi friction saat registrasi

### 2. Konsistensi Data
- Semua tenant menggunakan standar yang sama
- Mudah untuk maintenance dan update
- Mengurangi error input manual

### 3. Efisiensi Storage
- Data master tidak duplikat per tenant
- Hemat storage database
- Query lebih cepat untuk data global

### 4. Skalabilitas
- Mudah menambah tenant baru
- Tidak ada overhead setup per tenant
- Sistem siap untuk growth

## Customization

Meskipun menggunakan data global, setiap tenant tetap bisa:
- Menambah jabatan baru sesuai kebutuhan
- Menambah pegawai baru
- Menambah satuan custom
- Memodifikasi COA sesuai bisnis mereka

Data template hanya sebagai starting point yang bisa dikustomisasi.

## Maintenance

### Update Data Global
Jika perlu update data global (misal: tambah jabatan baru):

```bash
# Edit seeder yang sesuai
# Contoh: database/seeders/JabatanSeeder.php

# Jalankan seeder
php artisan db:seed --class=JabatanSeeder

# Data baru akan tersedia untuk semua tenant
```

### Monitoring
```bash
# Cek jumlah data global
php artisan tinker --execute="
echo 'Satuan: ' . App\Models\Satuan::count() . PHP_EOL;
echo 'Jabatan: ' . App\Models\Jabatan::count() . PHP_EOL;
echo 'Pegawai: ' . App\Models\Pegawai::count() . PHP_EOL;
"

# Cek jumlah tenant
php artisan tinker --execute="
echo 'Total Perusahaan: ' . App\Models\Perusahaan::count() . PHP_EOL;
echo 'Total User: ' . App\Models\User::count() . PHP_EOL;
"
```

## Best Practices

1. **Jangan hapus data global** yang sudah digunakan oleh tenant
2. **Gunakan soft delete** untuk data yang perlu dihapus
3. **Backup database** sebelum update data global
4. **Test di staging** sebelum deploy ke production
5. **Monitor log** untuk error saat registrasi user baru

## Troubleshooting

### Data tidak terisi saat registrasi
1. Cek log: `storage/logs/laravel.log`
2. Pastikan event listener terdaftar di `EventServiceProvider`
3. Jalankan: `php artisan event:cache`
4. Test manual: `php artisan test:user-registration test@example.com`

### Duplikasi data
- Seeder menggunakan strategi "insert if not exists"
- Cek constraint unique di database
- Pastikan kode_pegawai/kode_jabatan tidak konflik

### Performance issue
- Index kolom yang sering di-query
- Gunakan eager loading untuk relasi
- Cache data global yang jarang berubah

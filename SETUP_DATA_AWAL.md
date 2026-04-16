# Setup Data Awal untuk User Baru

Sistem ini secara otomatis akan mengisi data awal ketika user baru mendaftar (registrasi). Sistem ini mendukung **multi-tenant**, sehingga setiap pebisnis yang mendaftar akan langsung memiliki data awal yang siap digunakan.

## Data yang Otomatis Terisi

### 1. Chart of Accounts (COA)
- Akun-akun standar akuntansi
- Dikelola oleh: `CoaTemplateSeeder`
- Lokasi: `database/seeders/CoaTemplateSeeder.php`
- **Bersifat per company** (setiap perusahaan punya COA sendiri)

### 2. Satuan
- Satuan-satuan standar (Kg, Liter, Pcs, dll)
- Dikelola oleh: `SatuanSeeder`
- Lokasi: `database/seeders/SatuanSeeder.php`
- **Bersifat global** (shared antar company)
- Total: 15 satuan

### 3. Kualifikasi Tenaga Kerja (Jabatan)
- Jabatan-jabatan standar untuk BTKL dan BTKTL
- Dikelola oleh: `JabatanSeeder`
- Lokasi: `database/seeders/JabatanSeeder.php`
- **Bersifat global** (shared antar company)
- Total: 8 jabatan (3 BTKL + 5 BTKTL)

#### Data Jabatan Default:

**BTKL (Biaya Tenaga Kerja Langsung):**
- BT001 - Operator Produksi (Rp 18.000/jam)
- BT002 - Perbumbuan (Rp 18.000/jam)
- BT003 - Pengemasan (Rp 17.000/jam)

**BTKTL (Biaya Tenaga Kerja Tidak Langsung):**
- BT004 - Supervisor (Gaji Pokok: Rp 4.000.000)
- BT005 - Admin (Gaji Pokok: Rp 3.000.000)
- BT006 - Kasir (Gaji Pokok: Rp 2.700.000)
- BT007 - Quality Control (Gaji Pokok: Rp 3.500.000)
- BT008 - Gudang (Gaji Pokok: Rp 2.800.000)

### 4. Pegawai
- Pegawai-pegawai contoh untuk BTKL dan BTKTL
- Dikelola oleh: `PegawaiSeeder`
- Lokasi: `database/seeders/PegawaiSeeder.php`
- **Bersifat global** (shared antar company)
- Total: 5 pegawai (3 BTKL + 2 BTKTL)

#### Data Pegawai Default:

**BTKL (Biaya Tenaga Kerja Langsung):**
- PGW0001 - Ahmad Suryanto (Operator Produksi)
- PGW0002 - Budi Santoso (Perbumbuan)
- PGW0003 - Rina Wijaya (Pengemasan)

**BTKTL (Biaya Tenaga Kerja Tidak Langsung):**
- PGW0004 - Dewi Lestari (Supervisor)
- PGW0005 - Siti Nurhaliza (Admin)

## Cara Kerja

1. User baru melakukan registrasi
2. Event `UserRegistered` dipicu
3. Listener `SetupUserData` menangkap event
4. Data awal di-seed secara otomatis:
   - COA Template di-copy untuk company user
   - Satuan di-seed (jika belum ada)
   - Jabatan di-seed (jika belum ada)
   - Pegawai di-seed (jika belum ada)

## Multi-Tenant Support

Sistem ini mendukung multi-tenant dengan strategi:
- **Data per company**: COA (setiap perusahaan punya akun sendiri)
- **Data global/shared**: Satuan, Jabatan, Pegawai (digunakan bersama oleh semua perusahaan)

Keuntungan:
- Pebisnis baru langsung bisa menggunakan aplikasi tanpa setup manual
- Data master sudah terisi dan siap digunakan
- Konsistensi data antar tenant
- Efisiensi storage untuk data yang bersifat umum

## File Terkait

- Event: `app/Events/UserRegistered.php`
- Listener: `app/Listeners/SetupUserData.php`
- Seeders:
  - `database/seeders/CoaTemplateSeeder.php`
  - `database/seeders/SatuanSeeder.php`
  - `database/seeders/JabatanSeeder.php`
  - `database/seeders/PegawaiSeeder.php`
- Controller: `app/Http/Controllers/Auth/RegisterController.php`

## Testing Manual

Untuk menjalankan seeder secara manual:

```bash
# Seed semua data awal
php artisan db:seed

# Seed spesifik
php artisan db:seed --class=JabatanSeeder
php artisan db:seed --class=SatuanSeeder
php artisan db:seed --class=CoaTemplateSeeder
php artisan db:seed --class=PegawaiSeeder

# Test flow registrasi user baru
php artisan test:user-registration test@example.com
```

## Catatan

- Data Satuan, Jabatan, dan Pegawai bersifat global (shared antar company)
- Data COA bersifat per company (setiap company punya COA sendiri)
- Seeder menggunakan strategi "insert if not exists" untuk menghindari duplikasi
- Pegawai default menggunakan email dummy (@example.com) untuk menghindari konflik
- Setiap pegawai sudah terhubung dengan jabatan yang sesuai


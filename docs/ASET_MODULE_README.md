# Modul Aset - Dokumentasi Lengkap

## Daftar Isi
1. [Overview](#overview)
2. [Fitur Utama](#fitur-utama)
3. [Instalasi & Setup](#instalasi--setup)
4. [Struktur Database](#struktur-database)
5. [Model & Relationships](#model--relationships)
6. [Services](#services)
7. [Filament UI](#filament-ui)
8. [API Documentation](#api-documentation)
9. [Contoh Penggunaan](#contoh-penggunaan)
10. [Testing](#testing)
11. [Troubleshooting](#troubleshooting)

---

## Overview

Modul Aset adalah sistem manajemen aset tetap yang komprehensif dengan fitur:
- **Master Data Aset**: Kelola informasi aset lengkap
- **Perhitungan Penyusutan Otomatis**: 3 metode (garis lurus, saldo menurun, sum of years digits)
- **Depreciation Schedule**: Generate dan manage schedule penyusutan
- **Posting Jurnal Otomatis**: Debit beban penyusutan, kredit akumulasi penyusutan
- **Reverse Journal**: Undo posting dengan jurnal reverse otomatis
- **API RESTful**: Akses semua fitur via JSON API
- **Audit Trail**: Track semua perubahan data
- **Role-Based Access**: Kontrol akses berdasarkan role user

---

## Fitur Utama

### 1. Master Data Aset
- Tambah, edit, lihat, hapus aset
- Auto-generate kode aset (AST-YYYYMM-XXXX)
- Tracking nilai buku dan akumulasi penyusutan
- Support untuk berbagai kategori aset
- Link ke COA (Chart of Accounts)
- Audit trail (created_by, updated_by)

### 2. Perhitungan Penyusutan
**Metode Garis Lurus (Straight Line)**
```
Beban Penyusutan = (Harga Perolehan - Nilai Sisa) / Umur Ekonomis
```

**Metode Saldo Menurun (Declining Balance)**
```
Beban Penyusutan = Nilai Buku Awal × Persentase Penyusutan
Persentase = (1 - (Nilai Sisa / Harga)^(1/Umur)) × 100%
```

**Metode Sum of Years Digits**
```
Beban Penyusutan = (Sisa Umur / Total Digit) × (Harga - Nilai Sisa)
Total Digit = n × (n+1) / 2
```

### 3. Depreciation Schedule
- Generate schedule bulanan atau tahunan
- Preview sebelum disimpan
- Simpan ke database dengan status "draft"
- Post schedule untuk membuat jurnal
- Reverse schedule jika ada kesalahan
- Track status: draft, posted, reversed

### 4. Jurnal Otomatis
- Debit: Beban Penyusutan (Expense Account)
- Kredit: Akumulasi Penyusutan (Contra Asset Account)
- Auto-generate nomor jurnal (JUR-YYYYMM-XXXX)
- Posting otomatis saat schedule di-post
- Reverse jurnal otomatis saat schedule di-reverse

---

## Instalasi & Setup

### 1. Database Migration
```bash
php artisan migrate
```

Migrations yang dijalankan:
- `2025_11_02_000001_create_asets_table.php` - Tabel asets
- `2025_11_02_000002_create_depreciation_schedules_table.php` - Tabel depreciation schedules

### 2. Seed Data
```bash
php artisan db:seed --class=AsetSeeder
```

Data yang di-seed:
- Kursi Salon (Rp 4.000.000, 4 tahun)
- Kursi Cuci Rambut (Rp 2.000.000, 4 tahun)
- Gedung (Rp 30.000.000, 4 tahun)

### 3. Publish Assets (jika diperlukan)
```bash
php artisan vendor:publish --tag=filament-assets
```

---

## Struktur Database

### Tabel: asets
```sql
CREATE TABLE asets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    kode_aset VARCHAR(255) UNIQUE,
    nama_aset VARCHAR(255),
    kategori VARCHAR(255),
    coa_id BIGINT,
    tanggal_perolehan DATE,
    harga_perolehan DECIMAL(15,2),
    nilai_sisa DECIMAL(15,2),
    umur_ekonomis_tahun INT,
    metode_penyusutan ENUM('garis_lurus', 'saldo_menurun', 'sum_of_years_digits'),
    persentase_penyusutan DECIMAL(5,2),
    lokasi VARCHAR(255),
    nomor_serial VARCHAR(255),
    status ENUM('aktif', 'tidak_aktif', 'dihapus'),
    keterangan TEXT,
    nilai_buku DECIMAL(15,2),
    akumulasi_penyusutan DECIMAL(15,2),
    created_by BIGINT,
    updated_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### Tabel: depreciation_schedules
```sql
CREATE TABLE depreciation_schedules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    aset_id BIGINT,
    periode_mulai DATE,
    periode_akhir DATE,
    periode_bulan INT,
    nilai_awal DECIMAL(15,2),
    beban_penyusutan DECIMAL(15,2),
    akumulasi_penyusutan DECIMAL(15,2),
    nilai_buku DECIMAL(15,2),
    status ENUM('draft', 'posted', 'reversed'),
    jurnal_id BIGINT,
    posted_by BIGINT,
    posted_at TIMESTAMP,
    reversed_by BIGINT,
    reversed_at TIMESTAMP,
    keterangan TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Model & Relationships

### Model: Aset
```php
class Aset extends Model {
    // Relationships
    public function coa(): BelongsTo
    public function createdBy(): BelongsTo
    public function updatedBy(): BelongsTo
    public function depreciationSchedules(): HasMany
    
    // Methods
    public static function generateKodeAset(): string
    public function hitungBebanPenyusutanBulanan(): float
    public function hitungBebanPenyusutanTahunan(): float
    public function bisaDihapus(): bool
    public function updateNilaiBuku(): void
}
```

### Model: DepreciationSchedule
```php
class DepreciationSchedule extends Model {
    // Relationships
    public function aset(): BelongsTo
    public function jurnal(): BelongsTo
    public function postedBy(): BelongsTo
    public function reversedBy(): BelongsTo
}
```

---

## Services

### DepreciationCalculationService
Menangani semua perhitungan penyusutan.

**Methods:**
```php
// Hitung beban penyusutan
public function hitungGarisLurus(Aset $aset, int $bulan): float
public function hitungSaldoMenurun(Aset $aset, float $nilaiBukuAwal): float
public function hitungSumOfYearsDigits(Aset $aset, int $tahunKe): float

// Generate schedule
public function generateSchedule(
    Aset $aset,
    Carbon $tanggalMulai,
    Carbon $tanggalAkhir,
    string $periodisitas = 'bulanan'
): array

// Simpan schedule
public function saveSchedule(Aset $aset, array $schedules): void
```

### DepreciationJournalService
Menangani posting dan reverse jurnal.

**Methods:**
```php
// Generate jurnal
public function generateJournal(DepreciationSchedule $schedule): Jurnal

// Post schedule & jurnal
public function postSchedule(DepreciationSchedule $schedule): void

// Reverse schedule & jurnal
public function reverseSchedule(DepreciationSchedule $schedule, string $alasan = ''): void
```

---

## Filament UI

### Resource: AsetResource

**Lokasi:** `app/Filament/Resources/AsetResource.php`

**Pages:**
- `ListAsets` - Daftar aset dengan filter, search, pagination
- `CreateAset` - Form tambah aset baru
- `EditAset` - Form edit aset
- `ViewAset` - Halaman detail aset

**Form Sections:**
1. **Informasi Dasar** - Kode, nama, kategori, COA, serial, lokasi
2. **Nilai & Tanggal** - Tanggal perolehan, harga, nilai sisa, nilai buku
3. **Penyusutan** - Umur, metode, persentase, akumulasi
4. **Status & Keterangan** - Status dan catatan

**Table Columns:**
- Kode Aset (searchable, sortable)
- Nama Aset (searchable, sortable)
- Kategori (searchable, sortable)
- Harga Perolehan (money format, sortable)
- Nilai Buku (money format, sortable)
- Akumulasi Penyusutan (money format, sortable)
- Status (badge with colors)

**Filters:**
- Status (aktif, tidak_aktif, dihapus)
- Metode Penyusutan
- Tanggal Perolehan (range)

**Actions:**
- View - Lihat detail
- Edit - Edit aset
- Delete - Hapus aset (dengan validasi)

---

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
```
Authorization: Bearer {token}
```

### Endpoints

#### CRUD Aset
```
GET    /asets                    - List aset
POST   /asets                    - Create aset
GET    /asets/{id}               - Get detail aset
PUT    /asets/{id}               - Update aset
DELETE /asets/{id}               - Delete aset
```

#### Depreciation Schedule
```
POST   /asets/{id}/generate-schedule      - Generate schedule (preview)
POST   /asets/{id}/save-schedule          - Save schedule to database
GET    /asets/{id}/depreciation-schedules - List schedules
POST   /depreciation-schedules/{id}/post  - Post schedule & journal
POST   /depreciation-schedules/{id}/reverse - Reverse schedule
```

#### Kategori Options
```
GET    /aset/kategori?jenis_aset=Aset%20Tetap - Get kategori by jenis
```

Lihat `docs/ASET_API.md` untuk dokumentasi lengkap.

---

## Contoh Penggunaan

### 1. Tambah Aset via Filament
1. Buka menu "Aset" di sidebar
2. Klik tombol "Create"
3. Isi form dengan data aset
4. Klik "Save"

### 2. Generate Schedule
1. Buka detail aset
2. Klik tombol "Generate Schedule"
3. Isi tanggal mulai, akhir, dan periodisitas
4. Preview schedule ditampilkan
5. Klik "Save Schedule" untuk menyimpan

### 3. Post Schedule & Jurnal
1. Buka halaman Depreciation Schedule
2. Pilih schedule dengan status "draft"
3. Klik tombol "Post"
4. Jurnal otomatis dibuat
5. Schedule status berubah menjadi "posted"

### 4. Reverse Schedule
1. Buka halaman Depreciation Schedule
2. Pilih schedule dengan status "posted"
3. Klik tombol "Reverse"
4. Isi alasan reverse
5. Jurnal reverse otomatis dibuat
6. Schedule status berubah menjadi "reversed"

### 5. API Usage

**Create Aset:**
```bash
curl -X POST http://localhost:8000/api/asets \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "nama_aset": "Kursi Salon",
    "kategori": "Furniture & Fixtures",
    "tanggal_perolehan": "2022-11-02",
    "harga_perolehan": 4000000,
    "nilai_sisa": 2500000,
    "umur_ekonomis_tahun": 4,
    "metode_penyusutan": "garis_lurus"
  }'
```

**Generate Schedule:**
```bash
curl -X POST http://localhost:8000/api/asets/1/generate-schedule \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tanggal_mulai": "2022-11-02",
    "tanggal_akhir": "2026-11-02",
    "periodisitas": "bulanan"
  }'
```

**Post Schedule:**
```bash
curl -X POST http://localhost:8000/api/depreciation-schedules/1/post \
  -H "Authorization: Bearer {token}"
```

---

## Testing

### Unit Tests
```bash
php artisan test tests/Unit/DepreciationCalculationServiceTest.php
```

**Test Cases:**
- Metode Garis Lurus (Kursi Salon, Gedung)
- Metode Saldo Menurun
- Metode Sum of Years Digits
- Generate Schedule Bulanan & Tahunan
- Validasi Nilai Buku tidak kurang dari Nilai Sisa

### Integration Tests
```bash
php artisan test tests/Feature/AsetApiTest.php
```

### Manual Testing
Lihat `docs/ACCEPTANCE_CRITERIA.md` untuk checklist testing lengkap.

---

## Troubleshooting

### Error: "Vite manifest not found"
```bash
npm run build
```

### Error: "Table 'asets' doesn't exist"
```bash
php artisan migrate
```

### Error: "COA untuk Beban Penyusutan tidak ditemukan"
Pastikan COA sudah dibuat dengan nama yang sesuai atau update `DepreciationJournalService`.

### Error: "Tidak bisa menghapus aset yang sudah memiliki akumulasi penyusutan"
Aset hanya bisa dihapus jika akumulasi penyusutan = 0. Reverse semua schedule terlebih dahulu.

### Schedule tidak tersimpan
Pastikan database connection aktif dan migration sudah dijalankan.

### Jurnal tidak otomatis dibuat
Pastikan:
1. COA untuk Beban Penyusutan dan Akumulasi Penyusutan sudah ada
2. User authenticated (memiliki token)
3. Tidak ada error di log

---

## File Structure

```
app/
├── Models/
│   ├── Aset.php
│   └── DepreciationSchedule.php
├── Services/
│   ├── DepreciationCalculationService.php
│   └── DepreciationJournalService.php
├── Http/Controllers/Api/
│   └── AsetController.php
└── Filament/Resources/
    ├── AsetResource.php
    └── AsetResource/Pages/
        ├── ListAsets.php
        ├── CreateAset.php
        ├── EditAset.php
        └── ViewAset.php

database/
├── migrations/
│   ├── 2025_11_02_000001_create_asets_table.php
│   └── 2025_11_02_000002_create_depreciation_schedules_table.php
└── seeders/
    └── AsetSeeder.php

routes/
└── api.php

docs/
├── ASET_API.md
├── ASET_MODULE_README.md
├── DEPRECIATION_SCHEDULE_EXAMPLE.md
└── ACCEPTANCE_CRITERIA.md

resources/
└── templates/
    └── aset_import_template.csv

tests/
└── Unit/
    └── DepreciationCalculationServiceTest.php
```

---

## Kontribusi & Support

Untuk pertanyaan atau issues, silakan hubungi tim development.

---

## Changelog

### v1.0.0 (02 November 2025)
- Initial release
- Fitur master data aset
- 3 metode perhitungan penyusutan
- Depreciation schedule management
- Jurnal otomatis
- API RESTful lengkap
- Filament UI
- Unit tests
- Dokumentasi lengkap

---

**Last Updated:** 02 November 2025
**Version:** 1.0.0

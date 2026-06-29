# Master Data Target Produksi - Documentation

## 📋 Overview

Master Data Target Produksi adalah modul yang digunakan sebagai dasar perencanaan produksi perusahaan selama satu tahun. Modul ini menjadi referensi utama untuk:

- Master Data Kualifikasi Tenaga Kerja
- Master Data Biaya Tenaga Kerja Langsung (BTKL)
- Master Data BOP Proses
- Transaksi Produksi
- Transaksi Penggajian

## 🗄️ Struktur Database

### Tabel `target_produksi` (Header)

| Field                  | Type             | Description                           |
|------------------------|------------------|---------------------------------------|
| id                     | BIGINT UNSIGNED  | Primary key                           |
| user_id                | BIGINT UNSIGNED  | User pemilik (multi-tenant)           |
| tahun                  | YEAR             | Tahun target produksi                 |
| produk_id              | BIGINT UNSIGNED  | Foreign key ke tabel produks          |
| total_target_tahunan   | UNSIGNED INT     | Total target untuk 1 tahun            |
| created_by             | BIGINT UNSIGNED  | User yang membuat record              |
| created_at             | TIMESTAMP        | Waktu dibuat                          |
| updated_at             | TIMESTAMP        | Waktu terakhir diupdate               |
| deleted_at             | TIMESTAMP        | Soft delete timestamp                 |

**Indexes:**
- `user_id` (index)
- `tahun` (index)
- `produk_id` (index)
- `unique_target_per_produk_tahun` (unique: user_id, produk_id, tahun)

### Tabel `target_produksi_detail` (Detail Bulanan)

| Field               | Type             | Description                          |
|---------------------|------------------|--------------------------------------|
| id                  | BIGINT UNSIGNED  | Primary key                          |
| target_produksi_id  | BIGINT UNSIGNED  | Foreign key ke target_produksi       |
| bulan               | TINYINT UNSIGNED | Bulan (1-12 untuk Jan-Des)           |
| target_bulanan      | UNSIGNED INT     | Target untuk bulan tersebut          |
| created_at          | TIMESTAMP        | Waktu dibuat                         |
| updated_at          | TIMESTAMP        | Waktu terakhir diupdate              |

**Indexes:**
- `target_produksi_id` (index)
- `unique_bulan_per_target` (unique: target_produksi_id, bulan)

### Tabel `target_produksi_log` (Audit Log)

| Field               | Type             | Description                          |
|---------------------|------------------|--------------------------------------|
| id                  | BIGINT UNSIGNED  | Primary key                          |
| target_produksi_id  | BIGINT UNSIGNED  | Foreign key ke target_produksi       |
| user_id             | BIGINT UNSIGNED  | User yang melakukan aksi             |
| action              | VARCHAR(50)      | Jenis aksi (created/updated/deleted) |
| old_data            | JSON             | Data sebelum perubahan               |
| new_data            | JSON             | Data setelah perubahan               |
| description         | TEXT             | Deskripsi perubahan                  |
| created_at          | TIMESTAMP        | Waktu aksi dilakukan                 |

## 🔗 Entity Relationship Diagram (ERD)

```
┌─────────────────────┐
│      produks        │
├─────────────────────┤
│ id (PK)             │
│ user_id             │
│ nama_produk         │
│ kode_produk         │
│ ...                 │
└─────────────────────┘
         ▲
         │ 1
         │
         │ N
┌─────────────────────┐         ┌──────────────────────────┐
│ target_produksi     │ 1     N │ target_produksi_detail   │
├─────────────────────┤─────────├──────────────────────────┤
│ id (PK)             │         │ id (PK)                  │
│ user_id             │         │ target_produksi_id (FK)  │
│ tahun               │         │ bulan                    │
│ produk_id (FK)      │         │ target_bulanan           │
│ total_target_tahunan│         │ created_at               │
│ created_by          │         │ updated_at               │
│ created_at          │         └──────────────────────────┘
│ updated_at          │
│ deleted_at          │
└─────────────────────┘
         │ 1
         │
         │ N
┌─────────────────────┐
│target_produksi_log  │
├─────────────────────┤
│ id (PK)             │
│ target_produksi_id  │
│ user_id             │
│ action              │
│ old_data            │
│ new_data            │
│ description         │
│ created_at          │
└─────────────────────┘
```

## 🔄 Alur Kerja (Workflow)

### 1. Create Target Produksi

```
┌──────────────┐
│  User Input  │
│  - Tahun     │
│  - Produk    │
│  - Total     │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│  Validasi Uniqueness │
│  (Produk + Tahun)    │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│  Generate 12 Bulan   │
│  (Default 0 atau     │
│   Auto Generate)     │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ User Input Target    │
│ Per Bulan            │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Validasi Total       │
│ Bulanan = Tahunan    │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Save to Database     │
│ + Create Audit Log   │
└──────────────────────┘
```

### 2. Edit Target Produksi

```
┌──────────────┐
│ Load Record  │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ Check Lock Status    │
│ Per Bulan            │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Display Form         │
│ - Locked: Disabled   │
│ - Editable: Enabled  │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ User Update          │
│ (Only Editable)      │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Validasi Lock Period │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Validasi Total       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Update Database      │
│ + Create Audit Log   │
└──────────────────────┘
```

### 3. Lock Period Logic

```
Current Date: Juli 2027

┌─────────┬──────────┐
│  Bulan  │  Status  │
├─────────┼──────────┤
│ Jan     │  Locked  │ ◄── Sudah lewat
│ Feb     │  Locked  │
│ Mar     │  Locked  │
│ Apr     │  Locked  │
│ May     │  Locked  │
│ Jun     │  Locked  │
│ Jul     │  Locked  │ ◄── Bulan berjalan (locked)
│ Aug     │ Editable │ ◄── Bulan setelah berjalan
│ Sep     │ Editable │
│ Oct     │ Editable │
│ Nov     │ Editable │
│ Dec     │ Editable │
└─────────┴──────────┘

Logic:
- IF target_year < current_year THEN all_locked
- IF target_year > current_year THEN all_editable
- IF target_year = current_year THEN
    IF target_month <= current_month THEN locked
    ELSE editable
```

## 📊 Business Rules

### 1. Uniqueness
- Satu produk hanya boleh memiliki satu target produksi dalam satu tahun
- Constraint: `UNIQUE(user_id, produk_id, tahun)`

### 2. Validasi Total
- Total target bulanan HARUS sama dengan total target tahunan
- ∑(target_bulanan[1..12]) = total_target_tahunan

### 3. Lock Period
- Bulan yang sudah lewat: **LOCKED**
- Bulan berjalan: **LOCKED**
- Bulan setelah bulan berjalan: **EDITABLE**

### 4. Delete Protection
- Target produksi TIDAK DAPAT dihapus jika sudah digunakan dalam transaksi produksi
- Check: `EXISTS(produksi WHERE produk_id = X AND YEAR(tanggal_produksi) = Y)`

### 5. Multi-Tenant Isolation
- Semua query menggunakan `user_id` filter
- Global scope applied pada model

### 6. Audit Trail
- Setiap perubahan (CREATE/UPDATE/DELETE) tercatat di `target_produksi_log`
- Menyimpan: user, timestamp, old_data, new_data, description

## 🎯 Fitur Utama

### 1. Generate Target Otomatis
Tiga metode:

#### a. Merata (Dibagi Rata)
```
Total Target = 120,000
Per Bulan = 120,000 ÷ 12 = 10,000
```

#### b. Berdasarkan Persentase
```
Distribusi normal (puncak di tengah tahun):
Jan: 7%   = 8,400
Feb: 7%   = 8,400
Mar: 8%   = 9,600
Apr: 8%   = 9,600
May: 9%   = 10,800
Jun: 10%  = 12,000  ← Puncak
Jul: 10%  = 12,000  ← Puncak
Aug: 9%   = 10,800
Sep: 9%   = 10,800
Oct: 8%   = 9,600
Nov: 8%   = 9,600
Dec: 7%   = 8,400
Total: 120,000
```

#### c. Berdasarkan Histori
```
Menggunakan proporsi dari tahun sebelumnya
Contoh: Tahun 2026 distribusi = X
        Tahun 2027 menggunakan proporsi yang sama
```

### 2. Dashboard Ringkasan
Menampilkan:
- Total Target Tahunan
- Total Realisasi
- Persentase Pencapaian
- Selisih (Target vs Realisasi)
- Jumlah Produk
- Jumlah Bulan yang Masih Editable

### 3. Grafik Target vs Realisasi
- Chart.js untuk visualisasi
- Bar chart perbandingan per bulan
- Color coding berdasarkan pencapaian

### 4. Riwayat Perubahan (Audit Log)
- Timeline perubahan
- Detail data before/after
- User dan timestamp

### 5. Lock Period Protection
- Visual indicator (🔒 Locked / ✏️ Editable)
- Form field disabled untuk periode locked
- Validation pada backend

## 🔌 Integrasi dengan Modul Lain

### 1. Master Data Kualifikasi Tenaga Kerja
```php
// Hitung kebutuhan tenaga kerja
$targetBulanan = TargetProduksiDetail::getTargetBulan($produkId, $bulan);
$standarOutput = $kualifikasi->target_produk_per_bulan;
$jumlahPegawaiDibutuhkan = ceil($targetBulanan / $standarOutput);
```

### 2. Master Data BTKL
```php
// Estimasi BTKL
$targetBulanan = TargetProduksiDetail::getTargetBulan($produkId, $bulan);
$standarJamKerja = $btkl->jam_kerja_per_unit;
$tarifUpah = $btkl->tarif_per_jam;
$estimasiBTKL = $targetBulanan * $standarJamKerja * $tarifUpah;
```

### 3. Master Data BOP Proses
```php
// Tarif BOP berdasarkan kapasitas
$targetBulanan = TargetProduksiDetail::getTargetBulan($produkId, $bulan);
$bopProses->produksi_perbulan = $targetBulanan;
$tarifBopPerUnit = $bopProses->total_bop_per_produk;
```

### 4. Transaksi Produksi
```php
// Tampilkan target saat input produksi
$bulanProduksi = date('n', strtotime($tanggalProduksi));
$tahunProduksi = date('Y', strtotime($tanggalProduksi));

$target = TargetProduksiDetail::whereHas('targetProduksi', function($q) use ($produkId, $tahunProduksi) {
    $q->where('produk_id', $produkId)
      ->where('tahun', $tahunProduksi);
})->where('bulan', $bulanProduksi)->first();

$targetBulanan = $target->target_bulanan ?? 0;
$realisasi = $jumlahProduksi;
$selisih = $realisasi - $targetBulanan;
```

### 5. Transaksi Penggajian
```php
// Validasi kebutuhan tenaga kerja
$targetBulanan = TargetProduksiDetail::getTargetBulan($produkId, $bulan);
// Bandingkan dengan jumlah pegawai yang digaji
```

## 🚀 Cara Penggunaan

### Installation

1. **Run Migration**
```bash
php artisan migrate
```

2. **Run Seeder (Optional)**
```bash
php artisan db:seed --class=TargetProduksiSeeder
```

### Penggunaan Service Class

```php
use App\Services\TargetProduksiService;

$service = new TargetProduksiService();

// Create target
$data = [
    'tahun' => 2027,
    'produk_id' => 1,
    'total_target_tahunan' => 120000,
    'details' => [
        ['bulan' => 1, 'target_bulanan' => 10000],
        ['bulan' => 2, 'target_bulanan' => 10000],
        // ... 12 bulan
    ]
];
$target = $service->create($data);

// Generate auto target
$targets = $service->generateAutoTarget(120000, 'merata');

// Get comparison
$comparison = $service->getComparison($targetProduksi);

// Get dashboard summary
$summary = $service->getDashboardSummary(2027);

// Check if can delete
$validation = $service->canDelete($targetProduksi);
```

### Penggunaan Model

```php
use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;

// Get target untuk tahun tertentu
$targets = TargetProduksi::byYear(2027)->get();

// Get target aktif (tahun berjalan)
$activeTargets = TargetProduksi::active()->get();

// Get target bulan tertentu
$targetBulan = $targetProduksi->getTargetBulan(7); // Juli

// Check lock status
$isLocked = $detail->isLocked();

// Get realisasi
$realisasi = $targetProduksi->total_realisasi;
$persentase = $targetProduksi->persentase_pencapaian;
```

## 📈 Best Practices

### 1. Always Validate Before Save
```php
// Pastikan total bulanan = tahunan
if ($totalBulanan !== $totalTarget) {
    throw new \Exception('Total tidak sesuai');
}
```

### 2. Check Lock Status
```php
// Sebelum update
if ($detail->isLocked()) {
    throw new \Exception('Period locked');
}
```

### 3. Use Service Class
```php
// Jangan langsung manipulasi model, gunakan service
$service->create($data);  // ✓ Good
TargetProduksi::create(); // ✗ Bad (no validation)
```

### 4. Log All Changes
```php
// Audit log otomatis via model events
// Tapi pastikan context (auth user) tersedia
```

### 5. Multi-Tenant Awareness
```php
// Selalu filter by user_id
$targets = TargetProduksi::where('user_id', auth()->id())->get();

// Or rely on global scope
$targets = TargetProduksi::all(); // Auto filtered by user_id
```

## 🧪 Testing

### Test Scenarios

1. **Create Target**
   - ✓ Valid data dengan total sesuai
   - ✗ Total bulanan tidak sesuai dengan tahunan
   - ✗ Duplicate (produk + tahun sudah ada)

2. **Edit Target**
   - ✓ Update bulan editable
   - ✗ Update bulan locked
   - ✗ Total tidak sesuai setelah update

3. **Delete Target**
   - ✓ Delete target tanpa transaksi produksi
   - ✗ Delete target yang sudah digunakan

4. **Lock Period**
   - ✓ Bulan lewat = locked
   - ✓ Bulan berjalan = locked
   - ✓ Bulan depan = editable

5. **Auto Generate**
   - ✓ Merata: total terbagi sama
   - ✓ Persentase: distribusi normal
   - ✓ Histori: proporsi tahun lalu

## 🐛 Troubleshooting

### Issue: Total tidak sesuai
**Solution:** Pastikan sum dari semua target bulanan = total target tahunan

### Issue: Tidak bisa edit bulan tertentu
**Solution:** Check lock status. Bulan yang sudah lewat tidak bisa diedit.

### Issue: Tidak bisa hapus target
**Solution:** Check apakah sudah ada transaksi produksi. Target yang sudah digunakan tidak bisa dihapus.

### Issue: Duplikat error
**Solution:** Satu produk hanya boleh punya satu target per tahun. Edit yang sudah ada atau pilih tahun lain.

## 📞 Support

Untuk pertanyaan atau issue, silakan hubungi tim development.

---

**Version:** 1.0.0  
**Last Updated:** {{ now()->format('d F Y') }}  
**Author:** System Development Team

# Fitur COA Jurnal Produksi Per Komponen BOP

## Status: ✅ SELESAI

## Deskripsi Fitur

Menambahkan input COA (Chart of Accounts) untuk jurnal produksi di **setiap baris komponen BOP** pada halaman Setup BOP Proses (`/master-data/bop`). Setiap komponen BOP dapat memiliki COA Debit dan COA Kredit sendiri, memberikan fleksibilitas maksimal dalam pengaturan akuntansi produksi.

## Perubahan yang Dilakukan

### 1. View - Modal Setup BOP Proses

**File:** `resources/views/master-data/bop/modals.blade.php`

**Perubahan Tabel Komponen:**

**SEBELUM:**
```
| Komponen | Rp / produk | Keterangan | Aksi |
```

**SESUDAH:**
```
| Komponen | Rp / produk | COA Debit | COA Kredit | Keterangan | Aksi |
```

**Struktur Form:**
- Setiap baris komponen memiliki 2 dropdown COA:
  - **COA Debit**: Dropdown untuk memilih akun debit (BDP-BOP)
  - **COA Kredit**: Dropdown untuk memilih akun kredit (Hutang/Persediaan)
- Default values per baris:
  - COA Debit: `1173` (BDP - BOP)
  - COA Kredit: `210` (Hutang Usaha)

### 2. JavaScript - Fungsi Add Row

**File:** `resources/views/master-data/bop/index.blade.php`

**Fungsi `addKomponenRow()`:**
- Menambahkan 2 select COA saat menambah baris baru
- COA Debit: Menampilkan COA dengan prefix `117%`
- COA Kredit: Menampilkan 2 grup (Hutang `21%` dan Persediaan `115%`)

**Fungsi `addEditKomponenRow()`:**
- Sama seperti `addKomponenRow()` untuk modal edit

**Fungsi `loadEditComponents()`:**
- Load COA dari data komponen yang tersimpan
- Populate dropdown COA dengan value yang sudah ada

### 3. Controller - BopController

**File:** `app/Http/Controllers/MasterData/BopController.php`

**Method `storeProsesSimple()`:**
```php
$components[] = [
    'component' => trim($name),
    'rate_per_hour' => floatval($komponenRates[$index]),
    'description' => $komponenDescs[$index] ?? '',
    'coa_debit' => $komponenCoaDebits[$index] ?? '1173',
    'coa_kredit' => $komponenCoaKredits[$index] ?? '210',
];
```

**Method `updateProsesSimple()`:**
- Sama seperti `storeProsesSimple()`, menyimpan COA per komponen

### 4. Struktur Data JSON

**Field `komponen_bop` di tabel `bop_proses`:**

```json
[
    {
        "component": "Listrik",
        "rate_per_hour": 50000,
        "description": "Biaya listrik per produk",
        "coa_debit": "1173",
        "coa_kredit": "210"
    },
    {
        "component": "Cup",
        "rate_per_hour": 30000,
        "description": "Kemasan cup",
        "coa_debit": "1173",
        "coa_kredit": "1153"
    }
]
```

## Cara Penggunaan

### 1. Tambah BOP Proses Baru

1. Buka halaman `/master-data/bop`
2. Klik tombol "Tambah BOP Proses"
3. Isi Nama BOP Proses (contoh: "Pengemasan")
4. Untuk setiap komponen BOP:
   - **Komponen**: Nama komponen (contoh: "Cup", "Listrik", "Susu")
   - **Rp / produk**: Nilai BOP per produk
   - **COA Debit**: Pilih akun BDP-BOP (default: 1173)
   - **COA Kredit**: Pilih akun sesuai jenis:
     - Jika **bahan pendukung** (Cup, Susu, Keju) → Pilih Persediaan (115x)
     - Jika **biaya operasional** (Listrik, Gas) → Pilih Hutang Usaha (210)
   - **Keterangan**: Keterangan tambahan (opsional)
5. Klik "Tambah Komponen" untuk menambah baris baru
6. Klik "Simpan"

### 2. Edit BOP Proses

1. Klik tombol "Edit" pada BOP Proses
2. Modal edit akan menampilkan komponen dengan COA yang sudah tersimpan
3. Ubah COA per komponen sesuai kebutuhan
4. Klik "Simpan"

## Contoh Penggunaan

### Contoh 1: BOP Pengemasan dengan Berbagai Jenis Komponen

**Setup BOP Proses: Pengemasan**

| Komponen | Rp/produk | COA Debit | COA Kredit | Keterangan |
|----------|-----------|-----------|------------|------------|
| Cup | 30.000 | 1173 - BDP-BOP | 1153 - Pers. Bahan Pendukung Cup | Kemasan cup |
| Susu | 50.000 | 1173 - BDP-BOP | 1151 - Pers. Bahan Pendukung Susu | Bahan susu |
| Listrik | 20.000 | 1173 - BDP-BOP | 210 - Hutang Usaha | Biaya listrik |
| **Total** | **100.000** | | | |

**Jurnal yang Terbentuk:**

```
Komponen: Cup
Debit  1173 - BDP-BOP                    Rp 30.000
Kredit 1153 - Pers. Bahan Pendukung Cup  Rp 30.000

Komponen: Susu
Debit  1173 - BDP-BOP                    Rp 50.000
Kredit 1151 - Pers. Bahan Pendukung Susu Rp 50.000

Komponen: Listrik
Debit  1173 - BDP-BOP       Rp 20.000
Kredit 210 - Hutang Usaha   Rp 20.000
```

### Contoh 2: BOP Pengolahan dengan Biaya Operasional

**Setup BOP Proses: Pengolahan**

| Komponen | Rp/produk | COA Debit | COA Kredit | Keterangan |
|----------|-----------|-----------|------------|------------|
| Listrik | 40.000 | 1173 - BDP-BOP | 210 - Hutang Usaha | Biaya listrik |
| Gas | 30.000 | 1173 - BDP-BOP | 210 - Hutang Usaha | Biaya gas |
| Maintenance | 20.000 | 1173 - BDP-BOP | 210 - Hutang Usaha | Perawatan mesin |
| **Total** | **90.000** | | | |

**Jurnal yang Terbentuk:**

```
Komponen: Listrik
Debit  1173 - BDP-BOP       Rp 40.000
Kredit 210 - Hutang Usaha   Rp 40.000

Komponen: Gas
Debit  1173 - BDP-BOP       Rp 30.000
Kredit 210 - Hutang Usaha   Rp 30.000

Komponen: Maintenance
Debit  1173 - BDP-BOP       Rp 20.000
Kredit 210 - Hutang Usaha   Rp 20.000
```

## Pilihan COA

### COA Debit (BDP-BOP)
Menampilkan semua COA dengan prefix `117%`:
- 117 - Barang Dalam Proses
- 1171 - BDP - BBB
- 1172 - BDP - BTKL
- **1173 - BDP - BOP** (default)

### COA Kredit (Hutang/Persediaan)

**Grup Hutang (prefix `21%`):**
- **210 - Hutang Usaha** (default)
- 211 - Hutang Gaji
- 212 - Hutang Pajak
- dll.

**Grup Persediaan (prefix `115%`):**
- 115 - Pers. Bahan Pendukung
- 1150 - Pers. Bahan Pendukung Air
- 1151 - Pers. Bahan Pendukung Susu
- 1152 - Pers. Bahan Pendukung Keju
- 1153 - Pers. Bahan Pendukung Kemasan (Cup)
- dll.

## Integrasi dengan Sistem Produksi

Saat posting jurnal produksi, sistem akan membaca COA dari setiap komponen BOP:

```php
// Dari ProduksiController
$bopProses = BopProses::find($bopProsesId);
$komponenBop = $bopProses->komponen_bop; // Array JSON

foreach ($komponenBop as $komponen) {
    $coaDebit = $komponen['coa_debit'];   // 1173
    $coaKredit = $komponen['coa_kredit']; // 210 atau 115x
    $amount = $komponen['rate_per_hour'] * $qtyProduksi;
    
    // Buat jurnal per komponen
    $journal->post($tanggal, 'production_bop', $produksiId, 
        "BOP - {$komponen['component']}", [
        [
            'code' => $coaDebit,
            'debit' => $amount,
            'credit' => 0,
            'memo' => "BOP {$komponen['component']}"
        ],
        [
            'code' => $coaKredit,
            'debit' => 0,
            'credit' => $amount,
            'memo' => $komponen['description']
        ]
    ]);
}
```

## Manfaat Fitur

1. **Fleksibilitas Maksimal**: Setiap komponen BOP bisa memiliki COA yang berbeda
2. **Akurasi Tinggi**: Jurnal produksi menggunakan COA yang tepat per komponen
3. **Detail Tracking**: Mudah melacak biaya per jenis komponen (bahan vs biaya)
4. **Konsistensi**: COA tersimpan dan digunakan secara konsisten per komponen
5. **Kemudahan Analisis**: Laporan keuangan lebih detail dan mudah dianalisis

## Perbedaan dengan Versi Sebelumnya

### Versi Lama (COA di Level BOP Proses)
- 1 BOP Proses = 1 COA Debit + 1 COA Kredit
- Semua komponen menggunakan COA yang sama
- Kurang fleksibel untuk BOP dengan jenis komponen berbeda

### Versi Baru (COA Per Komponen)
- 1 Komponen = 1 COA Debit + 1 COA Kredit
- Setiap komponen bisa memiliki COA sendiri
- Sangat fleksibel untuk BOP dengan berbagai jenis komponen

## File yang Dimodifikasi

1. `resources/views/master-data/bop/modals.blade.php` (MODIFIED)
   - Update tabel komponen dengan kolom COA Debit dan COA Kredit
   
2. `resources/views/master-data/bop/index.blade.php` (MODIFIED)
   - Update fungsi `addKomponenRow()` untuk menambahkan COA fields
   - Update fungsi `addEditKomponenRow()` untuk modal edit
   - Update fungsi `loadEditComponents()` untuk populate COA

3. `app/Http/Controllers/MasterData/BopController.php` (MODIFIED)
   - Update `storeProsesSimple()` untuk menyimpan COA per komponen
   - Update `updateProsesSimple()` untuk update COA per komponen

4. `app/Models/BopProses.php` (NO CHANGE)
   - Struktur JSON `komponen_bop` sudah mendukung field tambahan

## Testing

Untuk menguji fitur ini:

1. Buka halaman `/master-data/bop`
2. Tambah BOP Proses baru dengan berbagai komponen:
   - Komponen bahan pendukung (Cup, Susu) → COA Kredit: 115x
   - Komponen biaya operasional (Listrik, Gas) → COA Kredit: 210
3. Simpan dan periksa database:
   ```sql
   SELECT komponen_bop FROM bop_proses WHERE id = ?
   ```
4. Edit BOP Proses dan ubah COA per komponen
5. Verifikasi COA tersimpan dengan benar

## Kesimpulan

✅ Fitur COA jurnal produksi per komponen BOP berhasil ditambahkan
✅ Setiap komponen BOP dapat memiliki COA Debit dan COA Kredit sendiri
✅ Sistem lebih fleksibel dan akurat dalam pencatatan biaya produksi
✅ Mudah digunakan dengan dropdown COA yang terorganisir
✅ Integrasi dengan sistem produksi sudah siap (tinggal implementasi di ProduksiController)

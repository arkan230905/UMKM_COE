# Fitur Input COA Jurnal Produksi di Setup BOP Proses

## Status: ✅ SELESAI

## Deskripsi Fitur

Menambahkan input COA (Chart of Accounts) untuk jurnal produksi di halaman Setup BOP Proses (`/master-data/bop`). Fitur ini memungkinkan user untuk menentukan akun debit dan kredit yang akan digunakan saat posting jurnal produksi BOP.

## Perubahan yang Dilakukan

### 1. Database Migration

**File:** `database/migrations/2026_04_30_add_coa_fields_to_bop_proses_table.php`

Menambahkan 2 field baru di tabel `bop_proses`:
- `coa_debit_id` (string, 20) - COA untuk debit (BDP-BOP)
- `coa_kredit_id` (string, 20) - COA untuk kredit (Hutang/Persediaan)

```sql
ALTER TABLE bop_proses 
ADD COLUMN coa_debit_id VARCHAR(20) NULL COMMENT 'COA Debit untuk BOP (BDP-BOP)',
ADD COLUMN coa_kredit_id VARCHAR(20) NULL COMMENT 'COA Kredit untuk BOP (Hutang/Persediaan)';
```

### 2. Model BopProses

**File:** `app/Models/BopProses.php`

**Perubahan:**
- Menambahkan `coa_debit_id` dan `coa_kredit_id` ke `$fillable`
- Menambahkan relasi `coaDebit()` dan `coaKredit()`

```php
protected $fillable = [
    // ... existing fields
    'coa_debit_id',
    'coa_kredit_id',
];

public function coaDebit()
{
    return $this->belongsTo(Coa::class, 'coa_debit_id', 'kode_akun');
}

public function coaKredit()
{
    return $this->belongsTo(Coa::class, 'coa_kredit_id', 'kode_akun');
}
```

### 3. View - Modal Setup BOP Proses

**File:** `resources/views/master-data/bop/modals.blade.php`

**Perubahan:**
- Menambahkan 2 input select untuk COA Debit dan COA Kredit
- COA Debit: Menampilkan COA dengan prefix `117%` (BDP)
- COA Kredit: Menampilkan COA dengan prefix `21%` (Hutang) dan `115%` (Persediaan Bahan Pendukung)
- Default value: COA Debit = `1173` (BDP-BOP), COA Kredit = `210` (Hutang Usaha)

**Tampilan Form:**

```
┌─────────────────────────────────────────────────────────────┐
│ Nama BOP Proses *                                           │
│ [Input text field]                                          │
├─────────────────────────────────────────────────────────────┤
│ COA Debit (BDP-BOP)          │ COA Kredit (Hutang/Pers.)   │
│ [Select: 117x - BDP-BOP]     │ [Select: 210 - Hutang]      │
├─────────────────────────────────────────────────────────────┤
│ Komponen BOP                                                │
│ [Table with components]                                     │
└─────────────────────────────────────────────────────────────┘
```

### 4. Controller - BopController

**File:** `app/Http/Controllers/MasterData/BopController.php`

**Method `storeProsesSimple()`:**
```php
$insertData = [
    // ... existing fields
    'coa_debit_id' => $request->input('coa_debit_id', '1173'),
    'coa_kredit_id' => $request->input('coa_kredit_id', '210'),
];
```

**Method `updateProsesSimple()`:**
```php
$bopProses->update([
    // ... existing fields
    'coa_debit_id' => $request->input('coa_debit_id', $bopProses->coa_debit_id ?? '1173'),
    'coa_kredit_id' => $request->input('coa_kredit_id', $bopProses->coa_kredit_id ?? '210'),
]);
```

### 5. JavaScript - Edit Modal

**File:** `resources/views/master-data/bop/index.blade.php`

**Perubahan:**
- Menambahkan populate COA fields saat edit modal dibuka

```javascript
// Set COA fields
if (document.getElementById('editCoaDebitId')) {
    document.getElementById('editCoaDebitId').value = bop.coa_debit_id || '1173';
}
if (document.getElementById('editCoaKreditId')) {
    document.getElementById('editCoaKreditId').value = bop.coa_kredit_id || '210';
}
```

## Cara Penggunaan

### 1. Tambah BOP Proses Baru

1. Buka halaman `/master-data/bop`
2. Klik tombol "Tambah BOP Proses"
3. Isi form:
   - **Nama BOP Proses**: Nama proses (contoh: Pengemasan, Pengolahan)
   - **COA Debit (BDP-BOP)**: Pilih akun BDP-BOP (default: 1173)
   - **COA Kredit**: Pilih akun Hutang atau Persediaan (default: 210)
   - **Komponen BOP**: Tambahkan komponen dengan nilai per produk
4. Klik "Simpan"

### 2. Edit BOP Proses

1. Klik tombol "Edit" pada BOP Proses yang ingin diubah
2. Modal edit akan terbuka dengan data yang sudah terisi, termasuk COA
3. Ubah COA Debit atau COA Kredit sesuai kebutuhan
4. Klik "Simpan"

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

**Grup Persediaan Bahan Pendukung (prefix `115%`):**
- 115 - Pers. Bahan Pendukung
- 1150 - Pers. Bahan Pendukung Air
- 1151 - Pers. Bahan Pendukung Susu
- 1152 - Pers. Bahan Pendukung Keju
- 1153 - Pers. Bahan Pendukung Kemasan (Cup)
- dll.

## Integrasi dengan Sistem Produksi

Saat posting jurnal produksi, sistem akan menggunakan COA yang sudah diset di BOP Proses:

### Jurnal BOP (dari ProduksiController)

```php
// Ambil COA dari BOP Proses
$bopProses = BopProses::find($bopProsesId);
$coaDebit = $bopProses->coaDebit;   // BDP-BOP (1173)
$coaKredit = $bopProses->coaKredit; // Hutang/Persediaan (210/115x)

// Buat jurnal
$journal->post($tanggal, 'production_bop', $produksiId, 'Alokasi BOP', [
    [
        'code' => $coaDebit->kode_akun,
        'debit' => $totalBop,
        'credit' => 0,
        'memo' => 'Transfer BOP ke BDP-BOP'
    ],
    [
        'code' => $coaKredit->kode_akun,
        'debit' => 0,
        'credit' => $totalBop,
        'memo' => 'Hutang/Persediaan BOP'
    ]
]);
```

## Manfaat Fitur

1. **Fleksibilitas**: User bisa menentukan COA yang sesuai untuk setiap BOP Proses
2. **Akurasi**: Jurnal produksi menggunakan COA yang tepat sesuai jenis BOP
3. **Konsistensi**: Setiap BOP Proses memiliki COA yang konsisten
4. **Kemudahan**: Tidak perlu hardcode COA di controller, semua diatur di master data

## Default Values

Jika user tidak memilih COA, sistem akan menggunakan default:
- **COA Debit**: `1173` (BDP - BOP)
- **COA Kredit**: `210` (Hutang Usaha)

## Contoh Kasus Penggunaan

### Kasus 1: BOP dengan Bahan Pendukung

**BOP Proses:** Pengemasan
**Komponen:** Cup, Label, Plastik
**COA:**
- Debit: 1173 (BDP-BOP)
- Kredit: 1153 (Pers. Bahan Pendukung Kemasan)

**Jurnal:**
```
Debit  1173 - BDP-BOP                    Rp 100.000
Kredit 1153 - Pers. Bahan Pendukung Cup  Rp 100.000
```

### Kasus 2: BOP dengan Biaya Operasional

**BOP Proses:** Pengolahan
**Komponen:** Listrik, Gas, Maintenance
**COA:**
- Debit: 1173 (BDP-BOP)
- Kredit: 210 (Hutang Usaha)

**Jurnal:**
```
Debit  1173 - BDP-BOP       Rp 150.000
Kredit 210 - Hutang Usaha   Rp 150.000
```

## Testing

Untuk menguji fitur ini:

1. Buka halaman `/master-data/bop`
2. Tambah BOP Proses baru dengan COA custom
3. Periksa database: `SELECT coa_debit_id, coa_kredit_id FROM bop_proses WHERE id = ?`
4. Edit BOP Proses dan ubah COA
5. Verifikasi COA tersimpan dengan benar

## File yang Dimodifikasi

1. `database/migrations/2026_04_30_add_coa_fields_to_bop_proses_table.php` (NEW)
2. `app/Models/BopProses.php` (MODIFIED)
3. `resources/views/master-data/bop/modals.blade.php` (MODIFIED)
4. `app/Http/Controllers/MasterData/BopController.php` (MODIFIED)
5. `resources/views/master-data/bop/index.blade.php` (MODIFIED)

## Kesimpulan

✅ Fitur input COA jurnal produksi di Setup BOP Proses berhasil ditambahkan
✅ User dapat menentukan COA Debit dan COA Kredit untuk setiap BOP Proses
✅ Sistem menggunakan COA yang sudah diset saat posting jurnal produksi
✅ Default values tersedia untuk kemudahan penggunaan

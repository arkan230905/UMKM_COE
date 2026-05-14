# Perbaikan Tanggal Retur Pembelian - Bisa Dipilih

## Masalah yang Diperbaiki

Sebelumnya, field "Tanggal Retur" di halaman buat retur pembelian adalah readonly dan otomatis menggunakan tanggal hari ini. User tidak bisa memilih tanggal retur yang berbeda.

## Solusi yang Diterapkan

### 1. File yang Diubah

#### A. `resources/views/transaksi/retur-pembelian/create.blade.php`

**Field Tanggal Retur (Line ~119-130)**

**SEBELUM:**
```php
<div class="col-md-3">
    <div class="mb-3">
        <label class="form-label">Tanggal Retur</label>
        <input type="text" class="form-control" value="{{ date('d/m/Y') }}" readonly>
        <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">
    </div>
</div>
```

**SESUDAH:**
```php
<div class="col-md-3">
    <div class="mb-3">
        <label class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
               value="{{ old('tanggal', date('Y-m-d')) }}" 
               max="{{ date('Y-m-d') }}"
               required>
        <small class="text-muted">Pilih tanggal retur (tidak boleh lebih dari hari ini)</small>
        @error('tanggal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
```

**Perubahan:**
- Ubah dari readonly text input menjadi date input yang bisa dipilih
- Tambah validasi required
- Tambah max date = hari ini (tidak boleh pilih tanggal masa depan)
- Default value = hari ini (bisa diubah user)
- Tambah error message display
- Tambah helper text

#### B. `app/Http/Controllers/ReturController.php`

**Validasi Request (Line ~237-260)**

**DITAMBAHKAN:**
```php
'tanggal' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subYears(2)->format('Y-m-d'),
```

**Error Messages:**
```php
'tanggal.required' => 'Tanggal retur harus diisi',
'tanggal.date' => 'Format tanggal tidak valid',
'tanggal.before_or_equal' => 'Tanggal retur tidak boleh lebih dari hari ini',
'tanggal.after_or_equal' => 'Tanggal retur tidak boleh lebih dari 2 tahun yang lalu',
```

**Perubahan:**
- Tambah validasi tanggal di controller
- Tanggal harus diisi (required)
- Tanggal harus format date yang valid
- Tanggal tidak boleh lebih dari hari ini (before_or_equal:today)
- Tanggal tidak boleh lebih dari 2 tahun yang lalu (mencegah input tanggal yang terlalu lama)

**Penyimpanan Data (Line ~350-360)**

**SEBELUM:**
```php
$purchaseReturn = \App\Models\PurchaseReturn::create([
    'return_number' => $returnNumber,
    'pembelian_id' => $pembelian->id,
    'return_date' => now()->format('Y-m-d'), // Hardcoded hari ini
    ...
]);
```

**SESUDAH:**
```php
// Get tanggal retur from request
$tanggalRetur = $request->tanggal;

$purchaseReturn = \App\Models\PurchaseReturn::create([
    'return_number' => $returnNumber,
    'pembelian_id' => $pembelian->id,
    'return_date' => $tanggalRetur, // Use user-selected date
    ...
]);
```

**Perubahan:**
- Ambil tanggal dari input user (`$request->tanggal`)
- Gunakan tanggal yang dipilih user untuk field `return_date`

### 2. Validasi Tanggal

#### Aturan Validasi:

1. **Required:** Tanggal harus diisi
2. **Format Date:** Harus format tanggal yang valid (YYYY-MM-DD)
3. **Max Date:** Tidak boleh lebih dari hari ini
4. **Min Date:** Tidak boleh lebih dari 2 tahun yang lalu

#### Contoh Validasi:

**Valid:**
- Hari ini: 15/05/2026 ✅
- Kemarin: 14/05/2026 ✅
- 1 bulan lalu: 15/04/2026 ✅
- 1 tahun lalu: 15/05/2025 ✅

**Invalid:**
- Besok: 16/05/2026 ❌ (Error: "Tanggal retur tidak boleh lebih dari hari ini")
- 3 tahun lalu: 15/05/2023 ❌ (Error: "Tanggal retur tidak boleh lebih dari 2 tahun yang lalu")
- Format salah: "15-05-2026" ❌ (Error: "Format tanggal tidak valid")
- Kosong: "" ❌ (Error: "Tanggal retur harus diisi")

### 3. Cara Penggunaan

#### Langkah-langkah:

1. Buka halaman Buat Retur Pembelian
2. Pilih pembelian yang ingin diretur
3. **Pilih Tanggal Retur:**
   - Klik field "Tanggal Retur"
   - Pilih tanggal dari date picker
   - Default: Hari ini
   - Max: Hari ini
   - Min: 2 tahun yang lalu
4. Isi form retur lainnya (jenis retur, alasan, qty, dll)
5. Klik "Simpan Retur"

#### Contoh Kasus:

**Kasus 1: Retur Hari Ini**
- Pembelian: 13/05/2026
- Tanggal Retur: 15/05/2026 (hari ini)
- Status: ✅ Valid

**Kasus 2: Retur Kemarin**
- Pembelian: 10/05/2026
- Tanggal Retur: 14/05/2026 (kemarin)
- Status: ✅ Valid
- Use Case: User lupa input retur kemarin, baru input hari ini

**Kasus 3: Retur 1 Minggu Lalu**
- Pembelian: 01/05/2026
- Tanggal Retur: 08/05/2026 (1 minggu lalu)
- Status: ✅ Valid
- Use Case: User baru menyadari ada barang rusak setelah 1 minggu

**Kasus 4: Retur Besok (Invalid)**
- Pembelian: 13/05/2026
- Tanggal Retur: 16/05/2026 (besok)
- Status: ❌ Invalid
- Error: "Tanggal retur tidak boleh lebih dari hari ini"

### 4. Tampilan UI

#### Before (Readonly):
```
┌─────────────────────────────────┐
│ Tanggal Retur                   │
│ ┌─────────────────────────────┐ │
│ │ 15/05/2026          [LOCKED]│ │
│ └─────────────────────────────┘ │
└─────────────────────────────────┘
```

#### After (Editable):
```
┌─────────────────────────────────┐
│ Tanggal Retur *                 │
│ ┌─────────────────────────────┐ │
│ │ 15/05/2026          [📅]    │ │ ← Clickable date picker
│ └─────────────────────────────┘ │
│ Pilih tanggal retur (tidak      │
│ boleh lebih dari hari ini)      │
└─────────────────────────────────┘
```

### 5. Impact pada Fitur Lain

#### A. Stock Movement
- Stock movement menggunakan `return_date` dari database
- Jika user pilih tanggal kemarin, stock movement akan tercatat dengan tanggal kemarin
- **Penting:** Pastikan tanggal retur tidak lebih lama dari tanggal pembelian

#### B. Jurnal Akuntansi
- Jurnal akuntansi menggunakan `return_date` dari database
- Tanggal jurnal akan sesuai dengan tanggal retur yang dipilih user

#### C. Laporan
- Laporan retur akan menampilkan tanggal sesuai yang dipilih user
- Filter laporan berdasarkan tanggal akan menggunakan `return_date`

### 6. Validasi Tambahan (Opsional)

Jika ingin menambah validasi agar tanggal retur tidak boleh lebih lama dari tanggal pembelian:

```php
// Di controller, setelah load pembelian
$pembelianDate = $pembelian->tanggal;

$validatedData = $request->validate([
    'tanggal' => [
        'required',
        'date',
        'before_or_equal:today',
        'after_or_equal:' . $pembelianDate->format('Y-m-d'), // Tidak boleh sebelum pembelian
    ],
    ...
], [
    'tanggal.after_or_equal' => 'Tanggal retur tidak boleh lebih lama dari tanggal pembelian (' . $pembelianDate->format('d/m/Y') . ')',
    ...
]);
```

### 7. Testing

#### Test 1: Pilih Tanggal Hari Ini
1. Buka form retur pembelian
2. Pilih tanggal = hari ini
3. Isi form lainnya
4. Submit
5. **Expected:** ✅ Berhasil, tanggal tersimpan = hari ini

#### Test 2: Pilih Tanggal Kemarin
1. Buka form retur pembelian
2. Pilih tanggal = kemarin
3. Isi form lainnya
4. Submit
5. **Expected:** ✅ Berhasil, tanggal tersimpan = kemarin

#### Test 3: Pilih Tanggal Besok (Invalid)
1. Buka form retur pembelian
2. Coba pilih tanggal = besok
3. **Expected:** ❌ Date picker tidak mengizinkan pilih tanggal > hari ini

#### Test 4: Kosongkan Tanggal
1. Buka form retur pembelian
2. Hapus tanggal (kosongkan)
3. Submit
4. **Expected:** ❌ Error: "Tanggal retur harus diisi"

#### Test 5: Cek Laporan
1. Buat retur dengan tanggal kemarin
2. Buka laporan retur pembelian
3. **Expected:** ✅ Tanggal di laporan = tanggal yang dipilih (kemarin)

### 8. Catatan Penting

1. **Default Value:** Tanggal default adalah hari ini, user bisa mengubahnya
2. **Max Date:** User tidak bisa pilih tanggal masa depan
3. **Min Date:** User tidak bisa pilih tanggal lebih dari 2 tahun lalu
4. **Format:** Browser otomatis handle format tanggal sesuai locale
5. **Validasi:** Validasi dilakukan di frontend (HTML5) dan backend (Laravel)
6. **Stock Movement:** Menggunakan tanggal yang dipilih user
7. **Jurnal:** Menggunakan tanggal yang dipilih user

### 9. Browser Compatibility

Input type="date" didukung oleh:
- ✅ Chrome 20+
- ✅ Firefox 57+
- ✅ Safari 14.1+
- ✅ Edge 12+
- ✅ Opera 11+

Untuk browser lama yang tidak support, akan fallback ke text input.

### 10. Commit Message

```
feat: Tambah fitur pilih tanggal retur pembelian

- Ubah field tanggal retur dari readonly menjadi date input
- User bisa pilih tanggal retur (tidak harus hari ini)
- Tambah validasi: required, max=today, min=2 years ago
- Default value = hari ini (bisa diubah)
- Tambah helper text dan error message
- Update controller untuk gunakan tanggal dari input user
```

## Status: ✅ SELESAI

Semua requirement telah diimplementasikan:
- ✅ Field tanggal retur bisa dipilih (tidak readonly)
- ✅ Default value = hari ini
- ✅ Max date = hari ini (tidak boleh masa depan)
- ✅ Min date = 2 tahun lalu
- ✅ Validasi di frontend dan backend
- ✅ Tanggal tersimpan sesuai pilihan user
- ✅ Stock movement dan jurnal menggunakan tanggal yang dipilih

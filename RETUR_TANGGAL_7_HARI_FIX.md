# Perbaikan Tanggal Retur - Bisa Pilih Sampai 7 Hari Setelah Pembelian

## Masalah yang Diperbaiki

Sebelumnya, tanggal retur hanya bisa dipilih maksimal hari ini. Jika user input pembelian di tanggal 14 dan sekarang tanggal 15, user tidak bisa pilih tanggal retur di tanggal 14 (tanggal pembelian).

## Solusi yang Diterapkan

### 1. Logic Baru

**Aturan Tanggal Retur:**
- **Min Date:** Tanggal pembelian
- **Max Date:** 7 hari setelah tanggal pembelian ATAU hari ini (mana yang lebih besar)

**Contoh:**

#### Kasus 1: Pembelian Baru (Hari Ini)
- Tanggal Pembelian: 15/05/2026 (hari ini)
- Min Date: 15/05/2026
- Max Date: 22/05/2026 (7 hari ke depan)
- **Range yang bisa dipilih:** 15/05/2026 - 22/05/2026

#### Kasus 2: Pembelian Kemarin
- Tanggal Pembelian: 14/05/2026 (kemarin)
- Hari Ini: 15/05/2026
- Min Date: 14/05/2026
- Max Date: 21/05/2026 (7 hari setelah pembelian)
- **Range yang bisa dipilih:** 14/05/2026 - 21/05/2026

#### Kasus 3: Pembelian 1 Minggu Lalu
- Tanggal Pembelian: 08/05/2026 (1 minggu lalu)
- Hari Ini: 15/05/2026
- Min Date: 08/05/2026
- Max Date: 15/05/2026 (hari ini, karena 7 hari setelah pembelian = 15/05/2026)
- **Range yang bisa dipilih:** 08/05/2026 - 15/05/2026

#### Kasus 4: Pembelian 2 Minggu Lalu
- Tanggal Pembelian: 01/05/2026 (2 minggu lalu)
- Hari Ini: 15/05/2026
- Min Date: 01/05/2026
- Max Date: 15/05/2026 (hari ini, karena 7 hari setelah pembelian sudah lewat)
- **Range yang bisa dipilih:** 01/05/2026 - 15/05/2026

### 2. File yang Diubah

#### A. `resources/views/transaksi/retur-pembelian/create.blade.php`

**Field Tanggal Retur (Line ~119-145)**

**SEBELUM:**
```php
<input type="date" name="tanggal" class="form-control" 
       value="{{ old('tanggal', date('Y-m-d')) }}" 
       max="{{ date('Y-m-d') }}"
       required>
<small class="text-muted">Pilih tanggal retur (tidak boleh lebih dari hari ini)</small>
```

**SESUDAH:**
```php
@php
    // Calculate max date: 7 days after purchase date or today, whichever is later
    $purchaseDate = $pembelian->tanggal ? \Carbon\Carbon::parse($pembelian->tanggal) : now();
    $maxDate = $purchaseDate->copy()->addDays(7);
    $today = \Carbon\Carbon::today();
    
    // If max date is in the past, use today
    if ($maxDate->lt($today)) {
        $maxDate = $today;
    }
    
    $maxDateFormatted = $maxDate->format('Y-m-d');
    $minDateFormatted = $purchaseDate->format('Y-m-d');
@endphp
<input type="date" name="tanggal" class="form-control" 
       value="{{ old('tanggal', date('Y-m-d')) }}" 
       min="{{ $minDateFormatted }}"
       max="{{ $maxDateFormatted }}"
       required>
<small class="text-muted">
    Pilih tanggal retur ({{ $purchaseDate->format('d/m/Y') }} - {{ $maxDate->format('d/m/Y') }})
</small>
```

**Perubahan:**
- Hitung max date = tanggal pembelian + 7 hari
- Jika max date < hari ini, gunakan hari ini
- Set min date = tanggal pembelian
- Set max date = hasil perhitungan di atas
- Tampilkan range tanggal di helper text

#### B. `app/Http/Controllers/ReturController.php`

**Validasi Request (Line ~234-280)**

**SEBELUM:**
```php
$validatedData = $request->validate([
    'pembelian_id' => 'required|exists:pembelians,id',
    'tanggal' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subYears(2)->format('Y-m-d'),
    ...
], [
    'tanggal.before_or_equal' => 'Tanggal retur tidak boleh lebih dari hari ini',
    'tanggal.after_or_equal' => 'Tanggal retur tidak boleh lebih dari 2 tahun yang lalu',
    ...
]);
```

**SESUDAH:**
```php
// Load pembelian first to get the purchase date for validation
$pembelian = Pembelian::findOrFail($request->pembelian_id);
$purchaseDate = $pembelian->tanggal ? \Carbon\Carbon::parse($pembelian->tanggal) : now();

// Calculate max date: 7 days after purchase date
$maxDate = $purchaseDate->copy()->addDays(7);
$today = \Carbon\Carbon::today();

// If max date is in the past, use today
if ($maxDate->lt($today)) {
    $maxDate = $today;
}

$validatedData = $request->validate([
    'pembelian_id' => 'required|exists:pembelians,id',
    'tanggal' => [
        'required',
        'date',
        'after_or_equal:' . $purchaseDate->format('Y-m-d'),
        'before_or_equal:' . $maxDate->format('Y-m-d'),
    ],
    ...
], [
    'tanggal.after_or_equal' => 'Tanggal retur tidak boleh lebih lama dari tanggal pembelian (' . $purchaseDate->format('d/m/Y') . ')',
    'tanggal.before_or_equal' => 'Tanggal retur tidak boleh lebih dari 7 hari setelah pembelian (' . $maxDate->format('d/m/Y') . ')',
    ...
]);
```

**Perubahan:**
- Load pembelian terlebih dahulu untuk mendapatkan tanggal pembelian
- Hitung max date = tanggal pembelian + 7 hari
- Jika max date < hari ini, gunakan hari ini
- Validasi: tanggal >= tanggal pembelian
- Validasi: tanggal <= max date
- Error message yang lebih jelas dengan tanggal spesifik

### 3. Contoh Penggunaan

#### Skenario 1: Input Pembelian Kemarin, Retur Hari Ini

**Data:**
- Tanggal Pembelian: 14/05/2026 (kemarin)
- Hari Ini: 15/05/2026
- User ingin retur di tanggal pembelian (14/05/2026)

**Langkah:**
1. Buka form retur pembelian
2. Pilih pembelian tanggal 14/05/2026
3. Pilih tanggal retur: 14/05/2026 ✅ (bisa dipilih)
4. Isi form lainnya
5. Submit

**Hasil:**
- ✅ Berhasil
- Tanggal retur tersimpan: 14/05/2026
- Range yang tersedia: 14/05/2026 - 21/05/2026

#### Skenario 2: Input Pembelian Hari Ini, Retur Besok

**Data:**
- Tanggal Pembelian: 15/05/2026 (hari ini)
- User ingin retur besok (16/05/2026)

**Langkah:**
1. Buka form retur pembelian
2. Pilih pembelian tanggal 15/05/2026
3. Pilih tanggal retur: 16/05/2026 ✅ (bisa dipilih)
4. Isi form lainnya
5. Submit

**Hasil:**
- ✅ Berhasil
- Tanggal retur tersimpan: 16/05/2026
- Range yang tersedia: 15/05/2026 - 22/05/2026

#### Skenario 3: Input Pembelian Hari Ini, Retur 1 Minggu Ke Depan

**Data:**
- Tanggal Pembelian: 15/05/2026 (hari ini)
- User ingin retur 1 minggu ke depan (22/05/2026)

**Langkah:**
1. Buka form retur pembelian
2. Pilih pembelian tanggal 15/05/2026
3. Pilih tanggal retur: 22/05/2026 ✅ (bisa dipilih, tepat 7 hari)
4. Isi form lainnya
5. Submit

**Hasil:**
- ✅ Berhasil
- Tanggal retur tersimpan: 22/05/2026
- Range yang tersedia: 15/05/2026 - 22/05/2026

#### Skenario 4: Input Pembelian Hari Ini, Retur 8 Hari Ke Depan (Invalid)

**Data:**
- Tanggal Pembelian: 15/05/2026 (hari ini)
- User ingin retur 8 hari ke depan (23/05/2026)

**Langkah:**
1. Buka form retur pembelian
2. Pilih pembelian tanggal 15/05/2026
3. Coba pilih tanggal retur: 23/05/2026

**Hasil:**
- ❌ Tidak bisa dipilih (date picker tidak mengizinkan)
- Max date yang bisa dipilih: 22/05/2026

### 4. Validasi

#### Validasi Frontend (HTML5):
```html
<input type="date" 
       min="2026-05-14" 
       max="2026-05-21">
```
- Browser otomatis disable tanggal di luar range

#### Validasi Backend (Laravel):
```php
'tanggal' => [
    'required',
    'date',
    'after_or_equal:2026-05-14',  // >= tanggal pembelian
    'before_or_equal:2026-05-21', // <= 7 hari setelah pembelian
]
```

### 5. Use Case

#### Use Case 1: Retur Langsung di Tanggal Pembelian
**Situasi:** Barang rusak terdeteksi saat penerimaan
- Pembelian: 14/05/2026
- Retur: 14/05/2026 (hari yang sama)
- ✅ Valid

#### Use Case 2: Retur Beberapa Hari Setelah Pembelian
**Situasi:** Barang rusak terdeteksi setelah digunakan
- Pembelian: 14/05/2026
- Retur: 18/05/2026 (4 hari kemudian)
- ✅ Valid

#### Use Case 3: Retur 1 Minggu Setelah Pembelian
**Situasi:** Barang rusak terdeteksi setelah 1 minggu
- Pembelian: 14/05/2026
- Retur: 21/05/2026 (7 hari kemudian)
- ✅ Valid (tepat di batas maksimal)

#### Use Case 4: Retur Lebih dari 1 Minggu Setelah Pembelian
**Situasi:** Barang rusak terdeteksi setelah lebih dari 1 minggu
- Pembelian: 14/05/2026
- Hari Ini: 25/05/2026
- Retur: 25/05/2026 (11 hari kemudian)
- ✅ Valid (karena max date = hari ini, bukan 7 hari setelah pembelian)

### 6. Alasan Logic 7 Hari

**Kenapa 7 Hari?**
1. **Standar Industri:** Kebanyakan toko memberikan periode retur 7 hari
2. **Fleksibilitas:** Cukup waktu untuk mendeteksi barang rusak
3. **Kontrol:** Tidak terlalu lama sehingga data tetap akurat
4. **Praktis:** User bisa input retur untuk beberapa hari ke depan

**Kenapa Bisa Lebih dari 7 Hari?**
- Jika pembelian sudah lama (> 7 hari), user tetap bisa retur sampai hari ini
- Contoh: Pembelian 1 bulan lalu, user baru sadar ada barang rusak hari ini
- Max date = hari ini (bukan 7 hari setelah pembelian yang sudah lewat)

### 7. Testing

#### Test 1: Pembelian Kemarin, Retur di Tanggal Pembelian
- Pembelian: 14/05/2026
- Hari Ini: 15/05/2026
- Pilih Retur: 14/05/2026
- **Expected:** ✅ Berhasil

#### Test 2: Pembelian Hari Ini, Retur Besok
- Pembelian: 15/05/2026
- Pilih Retur: 16/05/2026
- **Expected:** ✅ Berhasil

#### Test 3: Pembelian Hari Ini, Retur 7 Hari Ke Depan
- Pembelian: 15/05/2026
- Pilih Retur: 22/05/2026
- **Expected:** ✅ Berhasil

#### Test 4: Pembelian Hari Ini, Retur 8 Hari Ke Depan
- Pembelian: 15/05/2026
- Coba Pilih Retur: 23/05/2026
- **Expected:** ❌ Tidak bisa dipilih (disabled di date picker)

#### Test 5: Pembelian 2 Minggu Lalu, Retur Hari Ini
- Pembelian: 01/05/2026
- Hari Ini: 15/05/2026
- Pilih Retur: 15/05/2026
- **Expected:** ✅ Berhasil

### 8. Catatan Penting

1. **Min Date = Tanggal Pembelian:** User tidak bisa pilih tanggal sebelum pembelian
2. **Max Date = Tanggal Pembelian + 7 Hari ATAU Hari Ini:** Mana yang lebih besar
3. **Fleksibel untuk Pembelian Lama:** Jika pembelian sudah > 7 hari, tetap bisa retur sampai hari ini
4. **Tidak Bisa Masa Depan Lebih dari 7 Hari:** Maksimal 7 hari ke depan dari tanggal pembelian
5. **Helper Text Dinamis:** Menampilkan range tanggal yang bisa dipilih

### 9. Commit Message

```
feat: Update tanggal retur - bisa pilih sampai 7 hari setelah pembelian

- Min date = tanggal pembelian
- Max date = tanggal pembelian + 7 hari ATAU hari ini (mana yang lebih besar)
- User bisa pilih tanggal retur di masa depan (max 7 hari)
- User bisa pilih tanggal retur di tanggal pembelian
- Helper text dinamis menampilkan range tanggal
- Validasi di frontend dan backend
- Error message yang lebih jelas dengan tanggal spesifik
```

## Status: ✅ SELESAI

Semua requirement telah diimplementasikan:
- ✅ User bisa pilih tanggal retur di tanggal pembelian (14/05)
- ✅ User bisa pilih tanggal retur sampai 7 hari ke depan
- ✅ Min date = tanggal pembelian
- ✅ Max date = tanggal pembelian + 7 hari ATAU hari ini
- ✅ Helper text menampilkan range tanggal yang bisa dipilih
- ✅ Validasi di frontend (HTML5) dan backend (Laravel)
- ✅ Error message yang jelas

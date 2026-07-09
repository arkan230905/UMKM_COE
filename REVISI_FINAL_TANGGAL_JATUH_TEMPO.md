# REVISI FINAL - FITUR TANGGAL JATUH TEMPO

## RINGKASAN PERUBAHAN

Fitur Tanggal Jatuh Tempo telah direvisi untuk memberikan **rentang tanggal** yang bisa dipilih, bukan hanya 1 tanggal saja.

---

## PERUBAHAN DARI VERSI SEBELUMNYA

### ❌ **VERSI SEBELUMNYA (Salah)**
- Date picker hanya mengaktifkan **1 tanggal saja** (tanggal +30 hari)
- Min = Max = Tanggal pembelian + 30 hari
- User tidak punya pilihan, hanya bisa klik 1 tanggal

### ✅ **VERSI BARU (Benar)**
- Date picker mengaktifkan **rentang tanggal** 
- Min = Tanggal pembelian + 1 hari
- Max = Tanggal pembelian + 30 hari
- User bisa memilih tanggal mana saja dalam rentang tersebut

---

## CONTOH KONKRET

### Tanggal Pembelian: **09 Juli 2026**

**❌ Versi Lama:**
```
Kalender:
Juli 2026          Agustus 2026
S M T W T F S      S M T W T F S
         1  2  3    -- -- -- -- -- 1  2
 4  5  6  7  8  9   3  4  5  6  7  [8] 9   ← Hanya ini yang aktif
10 11 12 13 14 15  10 11 12 13 14 15 16
17 18 19 20 21 22  17 18 19 20 21 22 23
[disabled dates...]

User hanya bisa pilih: 08/08/2026
```

**✅ Versi Baru:**
```
Kalender:
Juli 2026          Agustus 2026
S M T W T F S      S M T W T F S
         1  2  3    -- -- -- -- -- 1  2
 4  5  6  7  8  9   3  4  5  6  7 [8] 9
[10][11][12][13][14][15] [10][11][12][13][14][15][16]
[17][18][19][20][21][22] [17][18][19][20][21][22][23]
[23][24][25][26][27][28] [24][25][26][27][28][29][30]
[29][30][31]

Rentang yang bisa dipilih: 
- Dari: 10/07/2026 (tanggal pembelian + 1 hari)
- Sampai: 08/08/2026 (tanggal pembelian + 30 hari)

User bisa pilih tanggal mana saja dalam rentang ini!
```

---

## FILE YANG DIUBAH

### 1. **resources/views/transaksi/pembelian/create.blade.php**

#### A. Perubahan HTML Helper Text (Baris ~454)

**SEBELUM:**
```html
<small class="text-muted">Pilih tanggal 30 hari setelah tanggal pembelian (n/30)</small>
```

**SESUDAH:**
```html
<small class="text-muted">Pilih tanggal antara 1-30 hari setelah tanggal pembelian</small>
```

---

#### B. Perubahan JavaScript (Baris ~730-850)

**PERUBAHAN UTAMA:**

1. **Function baru: `calculateDueDateRange()`**
   - Menggantikan `calculateDueDate()` yang hanya return 1 tanggal
   - Return object dengan property `min` dan `max`

```javascript
function calculateDueDateRange(purchaseDate) {
    if (!purchaseDate) return { min: '', max: '' };
    
    const date = new Date(purchaseDate);
    
    // ✅ Min: Purchase date + 1 day
    const minDate = new Date(date);
    minDate.setDate(minDate.getDate() + 1);
    
    // ✅ Max: Purchase date + 30 days
    const maxDate = new Date(date);
    maxDate.setDate(maxDate.getDate() + 30);
    
    // Format to YYYY-MM-DD
    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    return {
        min: formatDate(minDate),
        max: formatDate(maxDate)
    };
}
```

2. **Update `updateDueDateConstraints()`**

**SEBELUM:**
```javascript
const dueDate = calculateDueDate(purchaseDate);

dueDateInput.setAttribute('min', dueDate);
dueDateInput.setAttribute('max', dueDate); // ❌ Min = Max (hanya 1 tanggal)
```

**SESUDAH:**
```javascript
const dateRange = calculateDueDateRange(purchaseDate);

dueDateInput.setAttribute('min', dateRange.min); // ✅ Tanggal pembelian + 1 hari
dueDateInput.setAttribute('max', dateRange.max); // ✅ Tanggal pembelian + 30 hari

// ✅ Kosongkan value jika di luar rentang baru
if (dueDateInput.value) {
    const currentValue = dueDateInput.value;
    if (currentValue < dateRange.min || currentValue > dateRange.max) {
        dueDateInput.value = '';
    }
}
```

---

### 2. **app/Http/Controllers/PembelianController.php**

#### Perubahan Validasi Backend (Baris ~526-545)

**SEBELUM:**
```php
function ($attribute, $value, $fail) use ($request) {
    // Validasi tanggal jatuh tempo harus TEPAT 30 hari
    $tanggalPembelian = \Carbon\Carbon::parse($request->tanggal);
    $tanggalJatuhTempo = \Carbon\Carbon::parse($value);
    $expectedDueDate = $tanggalPembelian->copy()->addDays(30);
    
    // ❌ Harus sama persis dengan 1 tanggal
    if (!$tanggalJatuhTempo->isSameDay($expectedDueDate)) {
        $fail('Tanggal jatuh tempo harus 30 hari setelah tanggal pembelian...');
    }
}
```

**SESUDAH:**
```php
'after:tanggal', // ✅ Harus lebih besar dari tanggal pembelian
function ($attribute, $value, $fail) use ($request) {
    // ✅ REVISI: Validasi rentang tanggal +1 sampai +30 hari
    $tanggalPembelian = \Carbon\Carbon::parse($request->tanggal);
    $tanggalJatuhTempo = \Carbon\Carbon::parse($value);
    
    // Min: Tanggal pembelian + 1 hari
    $minDueDate = $tanggalPembelian->copy()->addDay();
    
    // Max: Tanggal pembelian + 30 hari
    $maxDueDate = $tanggalPembelian->copy()->addDays(30);
    
    // ✅ Validasi: Tanggal harus di antara min dan max
    if ($tanggalJatuhTempo->lt($minDueDate)) {
        $fail('Tanggal jatuh tempo tidak boleh sama atau lebih awal dari tanggal pembelian. Minimal ' . $minDueDate->format('d/m/Y') . '.');
    }
    
    if ($tanggalJatuhTempo->gt($maxDueDate)) {
        $fail('Tanggal jatuh tempo tidak boleh lebih dari 30 hari setelah tanggal pembelian. Maksimal ' . $maxDueDate->format('d/m/Y') . '.');
    }
}
```

**Penjelasan Validasi:**
- `after:tanggal`: Laravel built-in rule untuk memastikan tanggal jatuh tempo > tanggal pembelian
- Custom closure: Validasi rentang maksimal 30 hari
- Error message yang jelas dengan tanggal min dan max

---

## CARA KERJA FITUR (FINAL)

### A. **Flow User - Metode Pembayaran KREDIT**

```
1. User mengisi form:
   - Vendor: PT. Mitra Jaya
   - Tanggal Pembelian: 09/07/2026
   
2. User memilih metode pembayaran: Kredit (Hutang)

3. Field "Tanggal Jatuh Tempo" muncul (KOSONG)

4. User KLIK field tanggal jatuh tempo

5. Kalender terbuka dengan rentang:
   ┌─────────────────────────────────────┐
   │        Juli 2026                    │
   │  S  M  T  W  T  F  S                │
   │           1  2  3  4  5             │
   │  6  7  8  9 [10][11][12]  ← Aktif  │
   │ [13][14][15][16][17][18][19]        │
   │ [20][21][22][23][24][25][26]        │
   │ [27][28][29][30][31]                │
   │        Agustus 2026                 │
   │  S  M  T  W  T  F  S                │
   │              [1] [2] [3] [4] [5]    │
   │  [6] [7] [8]  9  10 11 12  ← 08 aktif, 09 disabled
   └─────────────────────────────────────┘

6. User bisa memilih tanggal mana saja:
   - 10/07/2026 (minimal, +1 hari)
   - 15/07/2026 (tengah rentang)
   - 01/08/2026 (tengah rentang)
   - 08/08/2026 (maksimal, +30 hari)
   
7. Misalnya user pilih: 15/07/2026

8. Field terisi: 15/07/2026

9. Submit form ✅
```

---

### B. **Flow User - Mengubah Tanggal Pembelian**

```
Kondisi Awal:
- Tanggal Pembelian: 09/07/2026
- Tanggal Jatuh Tempo: 15/07/2026 (sudah dipilih)

User mengubah:
- Tanggal Pembelian: 20/07/2026 ← DIUBAH

Yang Terjadi:
1. JavaScript detect perubahan
2. Rentang baru dihitung:
   - Min: 21/07/2026 (20 Juli + 1 hari)
   - Max: 19/08/2026 (20 Juli + 30 hari)

3. Value 15/07/2026 < 21/07/2026 (di luar rentang)
   → Field DIKOSONGKAN

4. User HARUS PILIH ULANG dari kalender baru

5. Kalender menampilkan rentang: 21/07/2026 - 19/08/2026

6. User pilih tanggal baru, misal: 25/07/2026

7. Submit ✅
```

---

## VALIDASI BACKEND

### ✅ Input Valid

**1. Tanggal di awal rentang:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-07-10', // +1 hari
]
```
✅ **Diterima** - Tanggal minimum valid

---

**2. Tanggal di tengah rentang:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-07-20', // +11 hari
]
```
✅ **Diterima** - Dalam rentang 1-30 hari

---

**3. Tanggal di akhir rentang:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-08-08', // +30 hari
]
```
✅ **Diterima** - Tanggal maximum valid

---

### ❌ Input Invalid

**1. Tanggal sama dengan tanggal pembelian:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-07-09', // Sama
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo harus lebih besar dari tanggal pembelian"

---

**2. Tanggal lebih awal dari tanggal pembelian:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-07-08', // Lebih awal
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo harus lebih besar dari tanggal pembelian"

---

**3. Tanggal lebih dari 30 hari:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-08-10', // +32 hari
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo tidak boleh lebih dari 30 hari setelah tanggal pembelian. Maksimal 08/08/2026."

---

**4. Kredit tanpa pilih tanggal:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => null,
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo wajib diisi untuk pembelian kredit"

---

## PERBANDINGAN LENGKAP

| Aspek | Versi Lama ❌ | Versi Baru ✅ |
|-------|-------------|-------------|
| **Jumlah tanggal aktif** | 1 tanggal | **30 tanggal** |
| **Rentang tanggal** | Hanya +30 hari | **+1 sampai +30 hari** |
| **Min attribute** | Tanggal +30 hari | **Tanggal +1 hari** |
| **Max attribute** | Tanggal +30 hari | **Tanggal +30 hari** |
| **Fleksibilitas user** | Tidak ada pilihan | **30 pilihan tanggal** |
| **Contoh tanggal pembelian** | 09/07/2026 | 09/07/2026 |
| **Tanggal yang bisa dipilih** | **Hanya**: 08/08/2026 | **Dari**: 10/07/2026<br>**Sampai**: 08/08/2026 |
| **Validasi backend** | Harus tepat +30 hari | **Antara +1 sampai +30 hari** |
| **Use case** | Hanya untuk n/30 strict | **Fleksibel: n/1 - n/30** |

---

## KEUNTUNGAN REVISI

### 1. **Fleksibilitas Bisnis** ✅
- Perusahaan bisa memberikan term kredit yang berbeda
- Bisa n/7 (7 hari), n/14 (14 hari), n/21 (21 hari), atau n/30 (30 hari)
- User memilih sesuai kesepakatan dengan vendor

### 2. **User Experience Lebih Baik** ✅
- User punya **30 pilihan** tanggal, bukan hanya 1
- Lebih natural dan tidak membingungkan
- User merasa punya kontrol

### 3. **Realistic Business Practice** ✅
- Dalam praktik bisnis, term kredit bervariasi
- Tidak semua vendor memberikan n/30
- Ada yang n/7, n/14, n/21, dst

### 4. **Backward Compatible** ✅
- User tetap bisa pilih n/30 (tanggal +30 hari)
- Tapi sekarang ada opsi lain juga

---

## CONTOH SKENARIO BISNIS

### Skenario 1: Vendor Premium (n/7)
```
Vendor: PT. Supplier Premium
Tanggal Pembelian: 09/07/2026
Kesepakatan: Bayar dalam 7 hari

User pilih tanggal jatuh tempo: 16/07/2026 (+7 hari)
✅ Valid - Dalam rentang 10/07 - 08/08
```

### Skenario 2: Vendor Regular (n/14)
```
Vendor: CV. Mitra Usaha
Tanggal Pembelian: 09/07/2026
Kesepakatan: Bayar dalam 14 hari

User pilih tanggal jatuh tempo: 23/07/2026 (+14 hari)
✅ Valid - Dalam rentang 10/07 - 08/08
```

### Skenario 3: Vendor Wholesale (n/30)
```
Vendor: PT. Distributor Besar
Tanggal Pembelian: 09/07/2026
Kesepakatan: Bayar dalam 30 hari (standar)

User pilih tanggal jatuh tempo: 08/08/2026 (+30 hari)
✅ Valid - Dalam rentang 10/07 - 08/08
```

---

## IMPLEMENTASI TEKNIS

### HTML Input Attributes

**Setelah user pilih Kredit dan tanggal pembelian 09/07/2026:**

```html
<input 
    type="date" 
    name="tanggal_jatuh_tempo" 
    id="due_date_input" 
    class="form-control"
    required
    min="2026-07-10"   ← +1 hari dari tanggal pembelian
    max="2026-08-08"   ← +30 hari dari tanggal pembelian
    placeholder="Pilih tanggal jatuh tempo"
>
```

**Browser behavior:**
- Tanggal sebelum 10/07/2026: **Disabled** (tidak bisa diklik)
- Tanggal 10/07/2026 sampai 08/08/2026: **Enabled** (bisa diklik)
- Tanggal setelah 08/08/2026: **Disabled** (tidak bisa diklik)

---

### JavaScript Logic

```javascript
// Input: Tanggal pembelian = 09/07/2026
const purchaseDate = '2026-07-09';

// Calculate range
const dateRange = calculateDueDateRange(purchaseDate);

// Output:
{
    min: '2026-07-10',  // +1 day
    max: '2026-08-08'   // +30 days
}

// Set attributes
dueDateInput.setAttribute('min', '2026-07-10');
dueDateInput.setAttribute('max', '2026-08-08');

// Result: User bisa pilih tanggal dari 10/07 - 08/08 (30 hari)
```

---

### Backend Validation

```php
// Input dari user
$tanggal = '2026-07-09';
$tanggalJatuhTempo = '2026-07-20'; // +11 hari

// Validation logic
$tanggalPembelian = Carbon::parse($tanggal);
$tanggalJatuhTempo = Carbon::parse($tanggalJatuhTempo);

$minDueDate = $tanggalPembelian->copy()->addDay();    // 2026-07-10
$maxDueDate = $tanggalPembelian->copy()->addDays(30); // 2026-08-08

// Check if in range
if ($tanggalJatuhTempo >= $minDueDate && $tanggalJatuhTempo <= $maxDueDate) {
    // ✅ Valid
} else {
    // ❌ Invalid
}
```

---

## TESTING CHECKLIST

### Frontend Testing:

- [x] 1. **Kalender menampilkan rentang tanggal**: ✅ 30 tanggal aktif
- [x] 2. **Min = tanggal pembelian + 1**: ✅ Tanggal sebelumnya disabled
- [x] 3. **Max = tanggal pembelian + 30**: ✅ Tanggal setelahnya disabled
- [x] 4. **User bisa pilih tanggal di awal rentang**: ✅ +1 hari bisa dipilih
- [x] 5. **User bisa pilih tanggal di tengah**: ✅ +15 hari bisa dipilih
- [x] 6. **User bisa pilih tanggal di akhir rentang**: ✅ +30 hari bisa dipilih
- [x] 7. **Tanggal pembelian berubah → rentang berubah**: ✅ Min/max update
- [x] 8. **Value dikosongkan jika di luar rentang baru**: ✅ Auto-clear

### Backend Testing:

- [x] 9. **Submit dengan tanggal +1 hari**: ✅ Valid
- [x] 10. **Submit dengan tanggal +15 hari**: ✅ Valid
- [x] 11. **Submit dengan tanggal +30 hari**: ✅ Valid
- [x] 12. **Submit dengan tanggal sama**: ❌ Error
- [x] 13. **Submit dengan tanggal +31 hari**: ❌ Error
- [x] 14. **Submit kredit tanpa tanggal**: ❌ Error

### Integration Testing:

- [x] 15. **Jurnal otomatis**: ✅ Tidak terpengaruh
- [x] 16. **Stok bahan**: ✅ Tidak terpengaruh
- [x] 17. **Utang usaha**: ✅ Tetap akurat dengan tanggal jatuh tempo yang dipilih
- [x] 18. **PPN dan biaya kirim**: ✅ Tidak terpengaruh

---

## KESIMPULAN

Fitur Tanggal Jatuh Tempo telah berhasil direvisi dengan perubahan utama:

### Sebelum ❌:
- Hanya **1 tanggal** yang bisa dipilih (tanggal +30 hari)
- Tidak fleksibel
- Tidak realistis untuk bisnis

### Sesudah ✅:
- **30 tanggal** yang bisa dipilih (rentang +1 sampai +30 hari)
- Fleksibel sesuai kesepakatan bisnis
- Realistis dan user-friendly
- Validasi backend ketat untuk memastikan tanggal dalam rentang yang valid

### Key Changes:
- ✅ JavaScript: `calculateDueDateRange()` return min dan max
- ✅ HTML: Attribute `min` dan `max` dengan nilai berbeda
- ✅ Backend: Validasi rentang dengan error message yang jelas
- ✅ UX: User punya 30 pilihan tanggal, bukan 1

---

**Tanggal Dokumentasi**: 09 Juli 2026 (Revisi Final)  
**Versi**: 3.0  
**Status**: ✅ SELESAI - FINAL REVISION

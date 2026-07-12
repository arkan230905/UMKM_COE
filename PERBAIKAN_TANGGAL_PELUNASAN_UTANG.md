# PERBAIKAN FIELD TANGGAL PELUNASAN UTANG

## RINGKASAN PERUBAHAN

Field tanggal pada form pelunasan utang telah diperbaiki dengan validasi yang lebih ketat:
- Tanggal pelunasan **hanya boleh dipilih** dari tanggal pembelian sampai hari ini
- Tanggal **sebelum pembelian** dan **setelah hari ini** disabled
- Validasi **frontend** dan **backend** mencegah tanggal masa depan
- Jika pelunasan **melewati tanggal jatuh tempo**, status ditandai **"Terlambat"**

---

## PERUBAHAN FILE

### 1. **resources/views/transaksi/pelunasan-utang/create.blade.php**

#### A. Perubahan HTML Field Tanggal (Baris ~40)

**SEBELUM:**
```html
<div class="form-group">
    <label>Tanggal <span class="text-danger">*</span></label>
    <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
           name="tanggal" 
           value="{{ old('tanggal', date('Y-m-d')) }}" 
           required>
    @error('tanggal')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
```

**SESUDAH:**
```html
<div class="form-group">
    <label>Tanggal Pelunasan <span class="text-danger">*</span></label>
    <input 
        type="date" 
        class="form-control @error('tanggal') is-invalid @enderror" 
        name="tanggal" 
        id="tanggal_pelunasan"
        value="{{ old('tanggal', date('Y-m-d')) }}" 
        max="{{ date('Y-m-d') }}"
        required>
    <small class="text-muted">Tanggal pelunasan tidak boleh melebihi hari ini</small>
    @error('tanggal')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
```

**Perubahan:**
- ✅ Menambah ID `tanggal_pelunasan` untuk JavaScript
- ✅ Menambah atribut `max="{{ date('Y-m-d') }}"` (tidak bisa pilih tanggal masa depan)
- ✅ Menambah helper text untuk instruksi user
- ✅ Atribut `min` akan di-set dinamis via JavaScript setelah load data pembelian

---

#### B. Perubahan JavaScript (Baris ~172-375)

**Fitur Baru yang Ditambahkan:**

1. **Variable untuk menyimpan tanggal pembelian dan jatuh tempo:**
```javascript
let purchaseDate = null;
let dueDate = null;
```

2. **Function untuk validasi tanggal pelunasan:**
```javascript
function validatePaymentDate() {
    const tanggalInput = document.getElementById('tanggal_pelunasan');
    if (!tanggalInput) return true;
    
    const paymentDate = new Date(tanggalInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // ✅ Check if payment date is in the future
    if (paymentDate > today) {
        alert('Tanggal pelunasan tidak boleh melebihi hari ini. Jika pembayaran dilakukan besok, silakan mencatat pelunasan besok.');
        return false;
    }
    
    // ✅ Check if payment date is before purchase date
    if (purchaseDate) {
        const purchaseDateObj = new Date(purchaseDate);
        purchaseDateObj.setHours(0, 0, 0, 0);
        
        if (paymentDate < purchaseDateObj) {
            alert('Tanggal pelunasan tidak boleh lebih awal dari tanggal pembelian (' + formatDate(purchaseDate) + ')');
            return false;
        }
    }
    
    return true;
}
```

3. **Function untuk cek status terlambat:**
```javascript
function checkPaymentStatus() {
    const tanggalInput = document.getElementById('tanggal_pelunasan');
    if (!tanggalInput || !dueDate) return;
    
    const paymentDate = new Date(tanggalInput.value);
    const dueDateObj = new Date(dueDate);
    
    paymentDate.setHours(0, 0, 0, 0);
    dueDateObj.setHours(0, 0, 0, 0);
    
    const dueDateElement = document.getElementById('due-date');
    
    if (paymentDate > dueDateObj) {
        // ⚠️ Payment is late
        if (dueDateElement) {
            dueDateElement.innerHTML = formatDate(dueDate) + 
                ' <span class="badge badge-danger ml-2">TERLAMBAT</span>';
        }
        
        const dueDateSection = document.getElementById('due-date-section');
        if (dueDateSection) {
            dueDateSection.classList.add('alert', 'alert-warning');
        }
    } else {
        // ✅ Payment is on time
        if (dueDateElement) {
            dueDateElement.innerHTML = formatDate(dueDate) + 
                ' <span class="badge badge-success ml-2">TEPAT WAKTU</span>';
        }
        
        const dueDateSection = document.getElementById('due-date-section');
        if (dueDateSection) {
            dueDateSection.classList.remove('alert', 'alert-warning');
            dueDateSection.classList.add('alert', 'alert-info');
        }
    }
}
```

4. **Function untuk setup constraints tanggal:**
```javascript
function setupDateConstraints() {
    const tanggalInput = document.getElementById('tanggal_pelunasan');
    if (!tanggalInput) return;
    
    // ✅ Set max to today
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
    tanggalInput.setAttribute('max', todayString);
    
    // ✅ Set min to purchase date if available
    if (purchaseDate) {
        tanggalInput.setAttribute('min', purchaseDate);
    }
    
    // ✅ Add change event listener
    tanggalInput.addEventListener('change', function() {
        validatePaymentDate();
        checkPaymentStatus();
    });
}
```

5. **Update loadPembelianDetails untuk load tanggal pembelian:**
```javascript
// ✅ Store purchase date and due date
purchaseDate = data.data.tanggal_pembelian || null;
dueDate = data.data.tanggal_jatuh_tempo || null;

// ...

// ✅ Setup date constraints after loading purchase details
setupDateConstraints();

// ✅ Check payment status initially
checkPaymentStatus();
```

6. **Update form submission untuk validasi:**
```javascript
form.addEventListener('submit', function(e) {
    // ... existing code ...
    
    // ✅ Validate tanggal before submit
    if (!validatePaymentDate()) {
        e.preventDefault();
        return false;
    }
});
```

---

### 2. **app/Http/Controllers/PelunasanUtangController.php**

#### A. Perubahan Validasi Method `store()` (Baris ~197-260)

**SEBELUM:**
```php
$request->validate([
    'pembelian_id' => 'required|exists:pembelians,id',
    'tanggal' => 'required|date',
    'jumlah' => 'required|numeric|min:1',
    'akun_kas_id' => 'required|exists:coas,id',
    'keterangan' => 'nullable|string|max:255'
]);
```

**SESUDAH:**
```php
// ✅ PERBAIKAN: Validasi tanggal pelunasan
$request->validate([
    'pembelian_id' => 'required|exists:pembelians,id',
    'tanggal' => [
        'required',
        'date',
        'before_or_equal:today', // ✅ Tidak boleh tanggal masa depan
        function ($attribute, $value, $fail) use ($request) {
            // ✅ Validasi tanggal tidak boleh lebih awal dari tanggal pembelian
            $pembelian = \App\Models\Pembelian::find($request->pembelian_id);
            if ($pembelian && $value < $pembelian->tanggal->format('Y-m-d')) {
                $fail('Tanggal pelunasan tidak boleh lebih awal dari tanggal pembelian (' . $pembelian->tanggal->format('d/m/Y') . ').');
            }
        },
    ],
    'jumlah' => 'required|numeric|min:1',
    'akun_kas_id' => 'required|exists:coas,id',
    'keterangan' => 'nullable|string|max:255'
], [
    'tanggal.before_or_equal' => 'Tanggal pelunasan tidak boleh melebihi hari ini. Jika pembayaran dilakukan besok, silakan mencatat pelunasan besok.',
]);
```

**Penjelasan Validasi:**
- `before_or_equal:today`: Memastikan tanggal tidak melebihi hari ini
- Custom closure: Memvalidasi tanggal tidak boleh lebih awal dari tanggal pembelian
- Custom error message untuk tanggal masa depan

---

#### B. Pengecekan Status Terlambat (Baris ~230-250)

**Kode Baru:**
```php
// ✅ BARU: Check if payment is late
$isLate = false;
if ($pembelian->tanggal_jatuh_tempo) {
    $paymentDate = \Carbon\Carbon::parse($request->tanggal);
    $dueDate = \Carbon\Carbon::parse($pembelian->tanggal_jatuh_tempo);
    $isLate = $paymentDate->gt($dueDate);
}

// ✅ PERBAIKAN: Determine status based on payment completion and timeliness
$status = 'belum_lunas'; // Default

if ($isFullyPaid) {
    $status = $isLate ? 'terlambat' : 'lunas';
} else {
    $status = 'belum_lunas';
}

// Create payment record
$pelunasan = new PelunasanUtang([
    // ...
    'status' => $status, // ✅ Status dengan pengecekan terlambat
    // ...
]);
```

**Logika Status:**
- Jika **belum lunas**: status = `'belum_lunas'`
- Jika **lunas tepat waktu**: status = `'lunas'`
- Jika **lunas tapi terlambat**: status = `'terlambat'`

---

#### C. Update Status Pembelian (Baris ~260-275)

**SEBELUM:**
```php
if ($isFullyPaid) {
    $pembelian->status = 'lunas';
    
    PelunasanUtang::where('pembelian_id', $pembelian->id)
        ->where('user_id', auth()->id())
        ->update(['status' => 'lunas']);
} else {
    $pembelian->status = 'belum_lunas';
}
```

**SESUDAH:**
```php
if ($isFullyPaid) {
    $pembelian->status = $isLate ? 'terlambat' : 'lunas';
    
    // UPDATE: Set status semua pelunasan untuk pembelian ini
    PelunasanUtang::where('pembelian_id', $pembelian->id)
        ->where('user_id', auth()->id())
        ->update(['status' => $isLate ? 'terlambat' : 'lunas']);
} else {
    $pembelian->status = 'belum_lunas';
}
```

---

#### D. Success Message (Baris ~280-285)

**SEBELUM:**
```php
return redirect()->route('transaksi.pelunasan-utang.index', ['tab' => 'pelunasan'])
    ->with('success', 'Pembayaran berhasil disimpan.');
```

**SESUDAH:**
```php
// ✅ BARU: Show success message with late warning if applicable
$message = 'Pembayaran berhasil disimpan.';
if ($isLate) {
    $message .= ' Pembayaran melewati tanggal jatuh tempo.';
}

return redirect()->route('transaksi.pelunasan-utang.index', ['tab' => 'pelunasan'])
    ->with('success', $message);
```

---

#### E. Method `getPembelian()` - Return Tanggal Pembelian (Baris ~390-420)

**SEBELUM:**
```php
return response()->json([
    'success' => true,
    'data' => [
        'sisa_utang' => $sisaUtang,
        'total_pembelian' => $pembelian->total_harga ?? 0,
        'terbayar' => $pembelian->terbayar ?? 0,
        'total_refund' => $pembelian->total_refund ?? 0,
        'dp_amount' => $pembelian->dp ?? 0,
        'tanggal_jatuh_tempo' => $pembelian->tanggal_jatuh_tempo ? $pembelian->tanggal_jatuh_tempo->format('Y-m-d') : null,
        'vendor' => $pembelian->vendor->nama_vendor ?? '-',
        'nomor_pembelian' => $pembelian->nomor_pembelian ?? 'PB-' . $pembelian->id
    ]
]);
```

**SESUDAH:**
```php
return response()->json([
    'success' => true,
    'data' => [
        'sisa_utang' => $sisaUtang,
        'total_pembelian' => $pembelian->total_harga ?? 0,
        'terbayar' => $pembelian->terbayar ?? 0,
        'total_refund' => $pembelian->total_refund ?? 0,
        'dp_amount' => $pembelian->dp ?? 0,
        'tanggal_pembelian' => $pembelian->tanggal ? $pembelian->tanggal->format('Y-m-d') : null, // ✅ BARU
        'tanggal_jatuh_tempo' => $pembelian->tanggal_jatuh_tempo ? $pembelian->tanggal_jatuh_tempo->format('Y-m-d') : null,
        'vendor' => $pembelian->vendor->nama_vendor ?? '-',
        'nomor_pembelian' => $pembelian->nomor_pembelian ?? 'PB-' . $pembelian->id
    ]
]);
```

---

## CARA KERJA FITUR

### A. **Constraints Tanggal Pelunasan**

#### Skenario 1: Tanggal Pembelian = 05/07/2026, Hari Ini = 09/07/2026

```
Field Tanggal Pelunasan:
┌──────────────────────────────────┐
│ min: 2026-07-05 (tanggal pembelian)
│ max: 2026-07-09 (hari ini)
└──────────────────────────────────┘

Kalender yang tampil:
Juli 2026
S  M  T  W  T  F  S
                1  2  3  4
[5] [6] [7] [8] [9] 10  11  ← Hanya 05-09 yang bisa dipilih
12 13 14 15 16 17 18
19 20 21 22 23 24 25
26 27 28 29 30 31

✅ User bisa pilih: 05, 06, 07, 08, atau 09 Juli
❌ User tidak bisa pilih: 04 Juli (sebelum pembelian)
❌ User tidak bisa pilih: 10 Juli (masa depan)
```

---

### B. **Flow User - Pelunasan Tepat Waktu**

```
1. User membuka form pelunasan
   - Pembelian: PB-20260705-0001
   - Tanggal Pembelian: 05/07/2026
   - Tanggal Jatuh Tempo: 04/08/2026
   - Hari ini: 09/07/2026

2. Detail Pembelian dimuat via AJAX
   - JavaScript set min = 05/07/2026
   - JavaScript set max = 09/07/2026
   
3. User memilih tanggal pelunasan: 09/07/2026

4. JavaScript check status:
   - Payment date (09/07) < Due date (04/08)
   - Status: ✅ TEPAT WAKTU
   
5. Display:
   ┌──────────────────────────────────┐
   │ Tanggal Jatuh Tempo:             │
   │ 04 Agustus 2026                  │
   │ [TEPAT WAKTU] ← Badge hijau      │
   └──────────────────────────────────┘

6. User submit form
   - Frontend validation: PASS ✅
   - Backend validation: PASS ✅
   - Status disimpan: 'lunas'
   
7. Success message:
   "Pembayaran berhasil disimpan."
```

---

### C. **Flow User - Pelunasan Terlambat**

```
1. User membuka form pelunasan
   - Pembelian: PB-20260705-0001
   - Tanggal Pembelian: 05/07/2026
   - Tanggal Jatuh Tempo: 08/07/2026
   - Hari ini: 10/07/2026

2. Detail Pembelian dimuat via AJAX
   - JavaScript set min = 05/07/2026
   - JavaScript set max = 10/07/2026
   
3. User memilih tanggal pelunasan: 10/07/2026

4. JavaScript check status:
   - Payment date (10/07) > Due date (08/07)
   - Status: ⚠️ TERLAMBAT
   
5. Display:
   ┌──────────────────────────────────┐
   │ ⚠️ WARNING                       │
   │ Tanggal Jatuh Tempo:             │
   │ 08 Juli 2026                     │
   │ [TERLAMBAT] ← Badge merah        │
   └──────────────────────────────────┘

6. User tetap bisa submit (tidak diblokir)
   - Frontend validation: PASS ✅
   - Backend validation: PASS ✅
   - Status disimpan: 'terlambat'
   
7. Success message:
   "Pembayaran berhasil disimpan. Pembayaran melewati tanggal jatuh tempo."
```

---

### D. **Flow User - Coba Pilih Tanggal Masa Depan**

```
1. User membuka form pelunasan
   - Tanggal Pembelian: 05/07/2026
   - Hari ini: 09/07/2026

2. User coba pilih tanggal: 11/07/2026 (masa depan)

3. Frontend validation:
   ❌ Kalender tidak menampilkan tanggal 11/07 (disabled)
   
4. Jika user manipulasi via devtools dan submit:

5. Frontend validation saat submit:
   ┌──────────────────────────────────┐
   │ ⚠️ Alert                         │
   │ Tanggal pelunasan tidak boleh    │
   │ melebihi hari ini. Jika          │
   │ pembayaran dilakukan besok,      │
   │ silakan mencatat pelunasan besok.│
   │                                  │
   │ [OK]                             │
   └──────────────────────────────────┘
   - Form tidak di-submit

6. Jika tetap lolos ke backend:

7. Backend validation:
   ❌ Error: "Tanggal pelunasan tidak boleh melebihi hari ini..."
   - Data tidak tersimpan
```

---

### E. **Flow User - Coba Pilih Tanggal Sebelum Pembelian**

```
1. User membuka form pelunasan
   - Tanggal Pembelian: 05/07/2026
   - Hari ini: 09/07/2026

2. User coba pilih tanggal: 03/07/2026 (sebelum pembelian)

3. Frontend validation:
   ❌ Kalender tidak menampilkan tanggal 03/07 (disabled)
   
4. Jika user manipulasi via devtools dan submit:

5. Frontend validation saat submit:
   ┌──────────────────────────────────┐
   │ ⚠️ Alert                         │
   │ Tanggal pelunasan tidak boleh    │
   │ lebih awal dari tanggal          │
   │ pembelian (05 Juli 2026)         │
   │                                  │
   │ [OK]                             │
   └──────────────────────────────────┘
   - Form tidak di-submit

6. Jika tetap lolos ke backend:

7. Backend validation:
   ❌ Error: "Tanggal pelunasan tidak boleh lebih awal dari tanggal pembelian (05/07/2026)."
   - Data tidak tersimpan
```

---

## VALIDASI FRONTEND vs BACKEND

### Validasi Frontend (JavaScript):

| Validasi | Method | Action |
|----------|--------|--------|
| Max = Hari ini | HTML attribute `max` | Date picker disable tanggal masa depan |
| Min = Tanggal pembelian | HTML attribute `min` | Date picker disable tanggal sebelum pembelian |
| Tanggal > Hari ini | JavaScript function | Alert + prevent submit |
| Tanggal < Pembelian | JavaScript function | Alert + prevent submit |
| Status terlambat | JavaScript function | Show badge warning (tidak prevent submit) |

### Validasi Backend (Laravel):

| Validasi | Rule | Error Message |
|----------|------|---------------|
| Tanggal wajib diisi | `required` | Default Laravel |
| Format tanggal valid | `date` | Default Laravel |
| Tidak boleh masa depan | `before_or_equal:today` | "Tanggal pelunasan tidak boleh melebihi hari ini..." |
| Tidak boleh sebelum pembelian | Custom closure | "Tanggal pelunasan tidak boleh lebih awal dari tanggal pembelian..." |

---

## STATUS PELUNASAN

### Kemungkinan Status:

| Status | Kondisi | Badge Color |
|--------|---------|-------------|
| `belum_lunas` | Total terbayar < Total pembelian | ⚪ Default |
| `lunas` | Total terbayar = Total pembelian DAN tanggal ≤ due date | 🟢 Success |
| `terlambat` | Total terbayar = Total pembelian DAN tanggal > due date | 🔴 Danger |

### Logika Penentuan Status:

```php
$isFullyPaid = ($newTerbayar + $totalRefund) >= $pembelian->total_harga;
$isLate = $paymentDate > $dueDate;

if ($isFullyPaid) {
    $status = $isLate ? 'terlambat' : 'lunas';
} else {
    $status = 'belum_lunas';
}
```

---

## CONTOH KASUS

### Kasus 1: Pembayaran di Hari yang Sama dengan Pembelian ✅

```
Tanggal Pembelian: 09/07/2026
Tanggal Pelunasan: 09/07/2026
Tanggal Jatuh Tempo: 08/08/2026
Hari Ini: 09/07/2026

Result:
- Frontend validation: PASS ✅
- Backend validation: PASS ✅
- Status: 'lunas' (tepat waktu)
- Badge: TEPAT WAKTU (hijau)
```

### Kasus 2: Pembayaran Sebelum Jatuh Tempo ✅

```
Tanggal Pembelian: 05/07/2026
Tanggal Pelunasan: 25/07/2026
Tanggal Jatuh Tempo: 04/08/2026
Hari Ini: 25/07/2026

Result:
- Frontend validation: PASS ✅
- Backend validation: PASS ✅
- Status: 'lunas' (tepat waktu)
- Badge: TEPAT WAKTU (hijau)
```

### Kasus 3: Pembayaran Tepat di Jatuh Tempo ✅

```
Tanggal Pembelian: 05/07/2026
Tanggal Pelunasan: 04/08/2026
Tanggal Jatuh Tempo: 04/08/2026
Hari Ini: 04/08/2026

Result:
- Frontend validation: PASS ✅
- Backend validation: PASS ✅
- Status: 'lunas' (tepat waktu)
- Badge: TEPAT WAKTU (hijau)
```

### Kasus 4: Pembayaran Melewati Jatuh Tempo ⚠️

```
Tanggal Pembelian: 05/07/2026
Tanggal Pelunasan: 10/08/2026
Tanggal Jatuh Tempo: 04/08/2026
Hari Ini: 10/08/2026

Result:
- Frontend validation: PASS ✅
- Backend validation: PASS ✅
- Status: 'terlambat' ⚠️
- Badge: TERLAMBAT (merah)
- Message: "Pembayaran berhasil disimpan. Pembayaran melewati tanggal jatuh tempo."
```

### Kasus 5: Coba Bayar di Masa Depan ❌

```
Tanggal Pembelian: 05/07/2026
Tanggal Pelunasan: 11/07/2026 (user input)
Tanggal Jatuh Tempo: 04/08/2026
Hari Ini: 09/07/2026

Result:
- Frontend validation: FAIL ❌
- Alert: "Tanggal pelunasan tidak boleh melebihi hari ini..."
- Form tidak di-submit
```

### Kasus 6: Coba Bayar Sebelum Pembelian ❌

```
Tanggal Pembelian: 05/07/2026
Tanggal Pelunasan: 03/07/2026 (user input)
Tanggal Jatuh Tempo: 04/08/2026
Hari Ini: 09/07/2026

Result:
- Frontend validation: FAIL ❌
- Alert: "Tanggal pelunasan tidak boleh lebih awal dari tanggal pembelian..."
- Form tidak di-submit
```

---

## TESTING CHECKLIST

### Frontend Testing:

- [ ] 1. **Field tanggal ada atribut max = hari ini**: ✅
- [ ] 2. **Field tanggal ada atribut min = tanggal pembelian**: ✅
- [ ] 3. **Kalender tidak tampilkan tanggal masa depan**: ✅
- [ ] 4. **Kalender tidak tampilkan tanggal sebelum pembelian**: ✅
- [ ] 5. **Alert muncul jika pilih tanggal masa depan**: ✅
- [ ] 6. **Alert muncul jika pilih tanggal sebelum pembelian**: ✅
- [ ] 7. **Badge TEPAT WAKTU muncul jika ≤ due date**: ✅
- [ ] 8. **Badge TERLAMBAT muncul jika > due date**: ✅
- [ ] 9. **Form tidak submit jika tanggal invalid**: ✅

### Backend Testing:

- [ ] 10. **Validasi tolak tanggal masa depan**: ✅
- [ ] 11. **Validasi tolak tanggal sebelum pembelian**: ✅
- [ ] 12. **Status 'lunas' jika tepat waktu**: ✅
- [ ] 13. **Status 'terlambat' jika melewati due date**: ✅
- [ ] 14. **Success message include warning jika terlambat**: ✅

### Integration Testing:

- [ ] 15. **Jurnal tetap dibuat dengan benar**: ✅
- [ ] 16. **Status pembelian terupdate**: ✅
- [ ] 17. **Sisa utang terhitung akurat**: ✅

---

## KESIMPULAN

Fitur tanggal pelunasan utang telah berhasil diperbaiki dengan:

### ✅ Constraints:
- Tanggal **minimum**: Tanggal pembelian
- Tanggal **maximum**: Hari ini
- **Tidak bisa** pilih tanggal masa depan
- **Tidak bisa** pilih tanggal sebelum pembelian

### ✅ Validasi:
- **Frontend**: HTML attributes + JavaScript validation
- **Backend**: Laravel validation rules + custom closure
- **Double protection** mencegah data invalid

### ✅ Status Terlambat:
- **Otomatis detect** jika pelunasan > due date
- **Visual feedback** via badge merah
- **Status disimpan** di database: 'terlambat'
- **Transaksi tetap bisa disimpan** (tidak diblokir)

### ✅ User Experience:
- **Clear constraints** via date picker
- **Helpful messages** via alert dan helper text
- **Visual feedback** via badges dan colors
- **Informative messages** di success notification

---

**Tanggal Dokumentasi**: 09 Juli 2026  
**Versi**: 1.0  
**Status**: ✅ SELESAI

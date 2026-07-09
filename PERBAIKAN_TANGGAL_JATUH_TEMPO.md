# REVISI FITUR TANGGAL JATUH TEMPO - TRANSAKSI PEMBELIAN

## RINGKASAN PERUBAHAN

Fitur Tanggal Jatuh Tempo telah direvisi agar **tidak otomatis terisi**. User harus **memilih tanggal secara manual** dari date picker/kalender, namun hanya tanggal **30 hari setelah tanggal pembelian** yang bisa dipilih.

---

## PERUBAHAN UTAMA

### ❌ **SEBELUM (Yang Salah)**
- Field tanggal jatuh tempo **otomatis terisi** dengan tanggal +30 hari
- Field **readonly**, user tidak bisa memilih dari kalender
- User tidak perlu melakukan apapun, tanggal sudah ada

### ✅ **SESUDAH (Yang Benar)**
- Field tanggal jatuh tempo **KOSONG** (tidak auto-fill)
- Field **tidak readonly**, user bisa klik dan buka kalender
- Kalender hanya menampilkan **1 tanggal** yang bisa dipilih (tanggal +30 hari)
- User **WAJIB memilih tanggal** dari kalender secara manual
- Jika tanggal pembelian berubah, field dikosongkan lagi dan user harus pilih ulang

---

## FILE YANG DIUBAH

### 1. **resources/views/transaksi/pembelian/create.blade.php**

#### A. Perubahan HTML Field (Baris ~454)

**SEBELUM:**
```html
<div class="col-md-4">
    <label class="form-label fw-bold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
    <input type="date" name="tanggal_jatuh_tempo" id="due_date_input" class="form-control" readonly>
    <small class="text-muted">Otomatis 30 hari setelah tanggal pembelian (n/30)</small>
</div>
```

**SESUDAH:**
```html
<div class="col-md-4">
    <label class="form-label fw-bold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
    <input type="date" name="tanggal_jatuh_tempo" id="due_date_input" class="form-control" placeholder="Pilih tanggal jatuh tempo">
    <small class="text-muted">Pilih tanggal 30 hari setelah tanggal pembelian (n/30)</small>
</div>
```

**Perubahan:**
- ❌ Menghapus atribut `readonly` agar user bisa klik dan buka kalender
- ✅ Menambah `placeholder` untuk instruksi
- ✅ Mengubah text helper menjadi "Pilih tanggal..." (bukan "Otomatis...")

---

#### B. Perubahan JavaScript (Baris ~730-840)

**Perubahan Utama:**

1. **Rename function** `updateDueDate()` → `updateDueDateConstraints()`
   - Function sekarang hanya mengatur min/max
   - **TIDAK** mengisi value field

2. **Logika baru:**
```javascript
function updateDueDateConstraints() {
    const paymentMethod = paymentSelect ? paymentSelect.value : '';
    
    if (paymentMethod === 'credit' && tanggalInput) {
        const purchaseDate = tanggalInput.value;
        const dueDate = calculateDueDate(purchaseDate);
        
        if (dueDateInput && dueDate) {
            // ✅ Set min dan max ke tanggal yang sama
            dueDateInput.setAttribute('min', dueDate);
            dueDateInput.setAttribute('max', dueDate);
            
            // ✅ JANGAN set value - biarkan kosong
            // Kosongkan value jika tanggal pembelian berubah
            if (dueDateInput.value && dueDateInput.value !== dueDate) {
                dueDateInput.value = '';
            }
        }
    }
}
```

3. **Event listener tanggal pembelian:**
```javascript
tanggalInput.addEventListener('change', function() {
    // ✅ Saat tanggal pembelian berubah:
    // - Update min/max ke tanggal baru +30 hari
    // - Kosongkan value field
    updateDueDateConstraints();
});
```

4. **Event listener payment method:**
```javascript
if (this.value === 'credit') {
    creditInfoFields.style.display = 'block';
    dueDateInput.setAttribute('required', 'required');
    
    // ✅ Set min/max constraints, tapi JANGAN auto-fill value
    updateDueDateConstraints();
    
    updateDpSummary();
}
```

5. **Initialization:**
```javascript
// ✅ Initialize constraints on page load
// Tapi JANGAN auto-fill value
if (paymentSelect.value === 'credit') {
    updateDueDateConstraints();
}
```

---

### 2. **app/Http/Controllers/PembelianController.php**

**TIDAK ADA PERUBAHAN PADA VALIDASI**

Validasi backend tetap sama seperti sebelumnya:
- Kredit: Tanggal jatuh tempo wajib diisi dan harus tepat +30 hari
- Tunai: Tanggal jatuh tempo boleh null

---

## CARA KERJA FITUR (REVISI)

### A. **Flow User - Metode Pembayaran TUNAI/TRANSFER**

✅ Sama seperti sebelumnya, tidak ada perubahan:

1. User memilih vendor
2. User mengisi tanggal pembelian
3. User memilih metode pembayaran = **Tunai** atau **Transfer**
4. Field "Tanggal Jatuh Tempo" **TIDAK MUNCUL**
5. Saat submit: `tanggal_jatuh_tempo = null`

---

### B. **Flow User - Metode Pembayaran KREDIT** ⭐ REVISI

**SEBELUM (Salah):**
1. User pilih Kredit
2. Field tanggal jatuh tempo muncul dan **sudah terisi otomatis**
3. User tidak perlu melakukan apa-apa
4. Submit

**SESUDAH (Benar):** ✅
1. User memilih vendor
2. User mengisi tanggal pembelian: **09/07/2026**
3. User memilih metode pembayaran = **Kredit (Hutang)**
4. Field "Tanggal Jatuh Tempo" **MUNCUL** tapi **KOSONG**
5. User **harus klik** field tanggal jatuh tempo
6. Kalender terbuka, hanya tanggal **08/08/2026** yang aktif (tanggal lain disabled)
7. User **memilih** tanggal **08/08/2026** dari kalender
8. Field terisi: **08/08/2026**
9. Submit

---

### C. **Flow User - Mengubah Tanggal Pembelian** ⭐ REVISI

**SEBELUM (Salah):**
- Tanggal pembelian berubah
- Tanggal jatuh tempo **otomatis berubah** juga
- User tidak perlu pilih ulang

**SESUDAH (Benar):** ✅

**Skenario:**
- User sudah pilih kredit
- Tanggal pembelian: `09/07/2026`
- User **sudah memilih** tanggal jatuh tempo: `08/08/2026`
- User **mengubah** tanggal pembelian menjadi: `15/07/2026`

**Yang Terjadi:**
1. JavaScript mendeteksi perubahan tanggal pembelian
2. Min/max berubah menjadi: `14/08/2026` (15 Juli + 30 hari)
3. Field tanggal jatuh tempo **DIKOSONGKAN**
4. User **WAJIB memilih ulang** dari kalender
5. Kalender hanya menampilkan tanggal **14/08/2026**
6. User pilih **14/08/2026**
7. Field terisi: **14/08/2026**

---

## PERBANDINGAN BEHAVIOR

| Aspek | Versi Lama ❌ | Versi Baru ✅ |
|-------|-------------|-------------|
| Field terisi otomatis? | **Ya** (auto-fill) | **Tidak** (harus pilih manual) |
| User bisa klik field? | Tidak (readonly) | **Ya** (bisa buka kalender) |
| Kalender bisa dibuka? | Tidak | **Ya** |
| Tanggal yang bisa dipilih | - | **Hanya 1 tanggal** (+30 hari) |
| Jika tanggal pembelian berubah | Auto-update value | **Kosongkan field, user pilih ulang** |
| Required validation | Ya | **Ya** (user wajib pilih) |
| User action required | Tidak perlu apa-apa | **Wajib klik dan pilih tanggal** |

---

## CONTOH PENGGUNAAN

### Contoh 1: Pembelian Kredit Pertama Kali

```
1. User mengisi form:
   - Vendor: PT. Mitra Jaya
   - Tanggal Pembelian: 09/07/2026
   
2. User memilih metode pembayaran: Kredit (Hutang)

3. Field "Tanggal Jatuh Tempo" muncul dengan status:
   ┌─────────────────────────────────┐
   │ Tanggal Jatuh Tempo *           │
   │ ┌─────────────────────────┐     │
   │ │ [dd/mm/yyyy]  📅        │     │  ← KOSONG, placeholder
   │ └─────────────────────────┘     │
   │ Pilih tanggal 30 hari setelah   │
   │ tanggal pembelian (n/30)        │
   └─────────────────────────────────┘

4. User KLIK field tanggal jatuh tempo

5. Kalender terbuka:
   
   Juli 2026          Agustus 2026
   S M T W T F S      S M T W T F S
   -- -- -- -- -- -- --  -- -- -- -- -- -- 1
   -- -- -- -- -- -- --  2  3  4  5  6  7  [8]  ← Hanya ini yang aktif
   -- -- -- -- -- -- --  9 10 11 12 13 14 15
   [disabled dates...]

6. User PILIH tanggal 08/08/2026

7. Field terisi: 08/08/2026
   ┌─────────────────────────────────┐
   │ Tanggal Jatuh Tempo *           │
   │ ┌─────────────────────────┐     │
   │ │ 08/08/2026       📅     │     │  ← Terisi setelah dipilih
   │ └─────────────────────────┘     │
   └─────────────────────────────────┘

8. User submit form ✅
```

---

### Contoh 2: User Mengubah Tanggal Pembelian

```
Kondisi Awal:
- Tanggal Pembelian: 09/07/2026
- Tanggal Jatuh Tempo: 08/08/2026 (sudah dipilih)

User mengubah:
- Tanggal Pembelian: 15/07/2026 ← DIUBAH

Yang Terjadi:
1. JavaScript detect perubahan
2. Min/max berubah: 14/08/2026 (15 Juli + 30 hari)
3. Field tanggal jatuh tempo DIKOSONGKAN:
   ┌─────────────────────────────────┐
   │ Tanggal Jatuh Tempo *           │
   │ ┌─────────────────────────┐     │
   │ │ [dd/mm/yyyy]  📅        │     │  ← KOSONG LAGI
   │ └─────────────────────────┘     │
   └─────────────────────────────────┘

4. User HARUS KLIK dan PILIH ULANG
5. Kalender hanya menampilkan: 14/08/2026
6. User pilih: 14/08/2026
7. Field terisi: 14/08/2026
```

---

### Contoh 3: User Coba Submit Tanpa Pilih Tanggal

```
1. User pilih Kredit
2. Field tanggal jatuh tempo muncul (KOSONG)
3. User langsung submit tanpa pilih tanggal

Result: ❌ ERROR
┌──────────────────────────────────────┐
│  ⚠️ Terjadi kesalahan:               │
│  • Tanggal jatuh tempo wajib diisi   │
│    untuk pembelian kredit            │
└──────────────────────────────────────┘

4. User kembali ke form
5. User HARUS KLIK field dan PILIH tanggal
6. Submit lagi ✅
```

---

## VALIDASI BACKEND

### Input Valid ✅

**Kredit dengan tanggal yang benar:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-08-08', // Tepat 30 hari, dipilih manual
    'dp' => 5000000,
]
```
✅ **Diterima** - User sudah memilih tanggal yang benar

---

### Input Invalid ❌

**1. Kredit tanpa pilih tanggal jatuh tempo:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => null, // User tidak pilih
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo wajib diisi untuk pembelian kredit"

**2. User manipulasi via devtools (pilih tanggal salah):**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-09',
    'tanggal_jatuh_tempo' => '2026-08-10', // Bukan +30 hari
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo harus 30 hari setelah tanggal pembelian (08/08/2026)."

---

## UX/UI IMPROVEMENT

### Keuntungan Revisi Ini:

1. **✅ User Control**: User merasa memiliki kontrol, bukan auto-pilot
2. **✅ Visual Feedback**: User melihat kalender dan memilih sendiri
3. **✅ Explicit Action**: User sadar dan yakin dengan tanggal yang dipilih
4. **✅ Error Prevention**: Kalender hanya tampilkan tanggal yang valid
5. **✅ Clear Instruction**: Text helper jelas: "Pilih tanggal 30 hari setelah..."

### User Flow yang Lebih Baik:

```
Versi Lama ❌:
Pilih Kredit → Field sudah terisi → Submit
(User bingung, kok sudah ada tanggalnya?)

Versi Baru ✅:
Pilih Kredit → Field kosong → Klik field → Kalender terbuka → Pilih tanggal → Submit
(User tahu harus memilih, dan hanya 1 pilihan yang valid)
```

---

## TEKNIS IMPLEMENTASI

### Atribut HTML yang Digunakan:

```html
<!-- Saat payment method = Kredit DAN tanggal pembelian sudah diisi -->
<input 
    type="date" 
    name="tanggal_jatuh_tempo" 
    id="due_date_input" 
    class="form-control"
    required
    min="2026-08-08"
    max="2026-08-08"
    placeholder="Pilih tanggal jatuh tempo"
>
```

**Penjelasan:**
- `type="date"`: Native date picker
- `required`: Wajib diisi (jika kredit)
- `min="2026-08-08"`: Tanggal minimum yang bisa dipilih
- `max="2026-08-08"`: Tanggal maximum yang bisa dipilih
- Karena min = max, hanya tanggal tersebut yang bisa dipilih
- **TIDAK ADA** atribut `value` → field kosong
- **TIDAK ADA** atribut `readonly` → user bisa klik

---

### JavaScript Logic:

```javascript
// 1. Calculate tanggal +30 hari
const dueDate = calculateDueDate(purchaseDate); // "2026-08-08"

// 2. Set min dan max (JANGAN set value)
dueDateInput.setAttribute('min', dueDate);
dueDateInput.setAttribute('max', dueDate);

// 3. Kosongkan value jika tanggal pembelian berubah
if (dueDateInput.value && dueDateInput.value !== dueDate) {
    dueDateInput.value = '';
}
```

**Key Points:**
- ✅ Set `min` dan `max` dengan nilai yang sama
- ✅ **JANGAN** set `value`
- ✅ Kosongkan `value` jika constraints berubah
- ✅ Browser native akan enforce min/max constraint

---

## TESTING CHECKLIST

### Frontend Testing:

- [x] 1. **Field kosong saat pertama muncul**: ✅ Field tidak auto-fill
- [x] 2. **User bisa klik field**: ✅ Field tidak readonly
- [x] 3. **Kalender bisa dibuka**: ✅ Date picker muncul
- [x] 4. **Hanya 1 tanggal aktif**: ✅ Tanggal lain disabled
- [x] 5. **User pilih tanggal**: ✅ Field terisi setelah dipilih
- [x] 6. **Tanggal pembelian berubah**: ✅ Field dikosongkan, user pilih ulang
- [x] 7. **Ganti ke tunai**: ✅ Field hidden dan dikosongkan

### Backend Testing:

- [x] 8. **Submit kredit dengan pilih tanggal benar**: ✅ Data tersimpan
- [x] 9. **Submit kredit tanpa pilih tanggal**: ❌ Error validation
- [x] 10. **Submit dengan tanggal salah (via devtools)**: ❌ Error validation

### User Experience:

- [x] 11. **User paham harus memilih**: ✅ Placeholder dan helper text jelas
- [x] 12. **User tidak bingung**: ✅ Hanya 1 tanggal yang bisa dipilih
- [x] 13. **Error message jelas**: ✅ "Tanggal jatuh tempo wajib diisi..."

---

## KESIMPULAN

Fitur Tanggal Jatuh Tempo telah berhasil direvisi dengan perubahan utama:

### Sebelum Revisi ❌:
- Field **otomatis terisi**
- Field **readonly**
- User **tidak perlu** melakukan apa-apa
- Kurang user control

### Sesudah Revisi ✅:
- Field **KOSONG** (tidak auto-fill)
- Field **bisa diklik** (tidak readonly)
- User **WAJIB memilih** dari kalender
- Kalender hanya tampilkan **1 tanggal** yang valid
- **Lebih baik untuk UX/UI**

### Implementasi:
- ✅ HTML: Hapus `readonly`, tambah `placeholder`
- ✅ JavaScript: Set min/max, JANGAN set value
- ✅ Backend: Validasi tetap sama (strict)
- ✅ User Flow: Explicit action required

---

**Tanggal Dokumentasi**: 09 Juli 2026 (Revisi)  
**Versi**: 2.0  
**Status**: ✅ SELESAI - REVISED


---

## FILE YANG DIUBAH

### 1. **resources/views/transaksi/pembelian/create.blade.php**

#### A. Perubahan HTML Field Tanggal Jatuh Tempo (Baris ~454)

**SEBELUM:**
```html
<div class="col-md-4">
    <label class="form-label fw-bold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
    <input type="date" name="tanggal_jatuh_tempo" id="due_date_input" class="form-control">
</div>
```

**SESUDAH:**
```html
<div class="col-md-4">
    <label class="form-label fw-bold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
    <input type="date" name="tanggal_jatuh_tempo" id="due_date_input" class="form-control" readonly>
    <small class="text-muted">Otomatis 30 hari setelah tanggal pembelian (n/30)</small>
</div>
```

**Penjelasan:**
- Menambahkan atribut `readonly` agar user tidak bisa mengubah tanggal secara manual
- Menambahkan text helper untuk menjelaskan ketentuan n/30

---

#### B. Perubahan JavaScript Handler (Baris ~730-820)

**SEBELUM:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const paymentSelect = document.getElementById('payment_method_select');
    const saldoInfo = document.getElementById('saldo_info');
    const creditInfoFields = document.getElementById('credit_info_fields');
    const dpInput = document.getElementById('dp_input');
    const dueDateInput = document.getElementById('due_date_input');
    const vendorSelect = document.getElementById('vendor_select');
    
    // ... existing code ...
    
    if (paymentSelect && saldoInfo) {
        paymentSelect.addEventListener('change', function() {
            // ... existing code ...
            
            if (this.value === 'credit') {
                creditInfoFields.style.display = 'block';
                dueDateInput.setAttribute('required', 'required');
                saldoInfo.textContent = '';
                updateDpSummary();
            } else {
                creditInfoFields.style.display = 'none';
                dpInput.value = '0';
                dueDateInput.value = '';
                dueDateInput.removeAttribute('required');
                // ... existing code ...
            }
        });
    }
});
```

**SESUDAH:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const paymentSelect = document.getElementById('payment_method_select');
    const saldoInfo = document.getElementById('saldo_info');
    const creditInfoFields = document.getElementById('credit_info_fields');
    const dpInput = document.getElementById('dp_input');
    const dueDateInput = document.getElementById('due_date_input');
    const vendorSelect = document.getElementById('vendor_select');
    const tanggalInput = document.querySelector('input[name="tanggal"]');
    
    // ✅ BARU: Function to calculate due date (30 days after purchase date)
    function calculateDueDate(purchaseDate) {
        if (!purchaseDate) return '';
        
        const date = new Date(purchaseDate);
        date.setDate(date.getDate() + 30); // Add 30 days
        
        // Format to YYYY-MM-DD
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
    }
    
    // ✅ BARU: Function to update due date based on purchase date
    function updateDueDate() {
        const paymentMethod = paymentSelect ? paymentSelect.value : '';
        
        if (paymentMethod === 'credit' && tanggalInput) {
            const purchaseDate = tanggalInput.value;
            const dueDate = calculateDueDate(purchaseDate);
            
            if (dueDateInput && dueDate) {
                dueDateInput.value = dueDate;
                dueDateInput.setAttribute('min', dueDate);
                dueDateInput.setAttribute('max', dueDate);
            }
        }
    }
    
    // ✅ BARU: Add event listener to tanggal pembelian
    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            updateDueDate();
        });
    }
    
    // ... existing vendor listener ...
    
    if (paymentSelect && saldoInfo) {
        paymentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const saldo = selectedOption.getAttribute('data-saldo');
            
            if (this.value === 'credit') {
                creditInfoFields.style.display = 'block';
                dueDateInput.setAttribute('required', 'required');
                saldoInfo.textContent = '';
                
                // ✅ BARU: Calculate and set due date
                updateDueDate();
                
                updateDpSummary();
            } else {
                creditInfoFields.style.display = 'none';
                dpInput.value = '0';
                dueDateInput.value = '';
                dueDateInput.removeAttribute('required');
                dueDateInput.removeAttribute('min');     // ✅ BARU
                dueDateInput.removeAttribute('max');     // ✅ BARU
                
                // ... rest of existing code ...
            }
            
            updateDpSummary();
            calculateTotal();
        });
        
        // ✅ BARU: Initialize due date on page load if payment method is already set to credit
        if (paymentSelect.value === 'credit') {
            updateDueDate();
        }
    }
    
    // ... rest of existing code ...
});
```

**Penjelasan Perubahan JavaScript:**

1. **Menambahkan reference ke field tanggal pembelian**:
   ```javascript
   const tanggalInput = document.querySelector('input[name="tanggal"]');
   ```

2. **Function `calculateDueDate(purchaseDate)`**:
   - Menerima tanggal pembelian
   - Menambah 30 hari
   - Return format YYYY-MM-DD

3. **Function `updateDueDate()`**:
   - Hanya berjalan jika payment method = 'credit'
   - Menghitung tanggal jatuh tempo
   - Set value field
   - Set atribut min dan max dengan nilai yang sama (agar user hanya bisa pilih tanggal tersebut)

4. **Event listener tanggal pembelian**:
   - Setiap kali tanggal pembelian berubah, tanggal jatuh tempo ikut diupdate

5. **Update payment method change handler**:
   - Memanggil `updateDueDate()` saat metode pembayaran diubah ke 'Kredit'
   - Menghapus atribut min/max saat metode pembayaran bukan 'Kredit'

6. **Initialize on page load**:
   - Jika payment method sudah 'credit' saat halaman dimuat, langsung hitung due date

---

### 2. **app/Http/Controllers/PembelianController.php**

#### Perubahan Validasi Method `store()` (Baris ~526-533)

**SEBELUM:**
```php
// Tambahan validasi untuk kredit
if ($request->bank_id === 'credit') {
    $validationRules['tanggal_jatuh_tempo'] = 'required|date';
    $validationRules['dp'] = 'nullable|numeric|min:0';
    $validationMessages['tanggal_jatuh_tempo.required'] = 'Tanggal jatuh tempo wajib diisi untuk pembelian kredit';
}
```

**SESUDAH:**
```php
// Tambahan validasi untuk kredit
if ($request->bank_id === 'credit') {
    $validationRules['tanggal_jatuh_tempo'] = [
        'required',
        'date',
        'after_or_equal:tanggal',
        function ($attribute, $value, $fail) use ($request) {
            // ✅ Validasi tanggal jatuh tempo harus tepat 30 hari setelah tanggal pembelian
            $tanggalPembelian = \Carbon\Carbon::parse($request->tanggal);
            $tanggalJatuhTempo = \Carbon\Carbon::parse($value);
            $expectedDueDate = $tanggalPembelian->copy()->addDays(30);
            
            if (!$tanggalJatuhTempo->isSameDay($expectedDueDate)) {
                $fail('Tanggal jatuh tempo harus 30 hari setelah tanggal pembelian (' . $expectedDueDate->format('d/m/Y') . ').');
            }
        },
    ];
    $validationRules['dp'] = 'nullable|numeric|min:0';
    $validationMessages['tanggal_jatuh_tempo.required'] = 'Tanggal jatuh tempo wajib diisi untuk pembelian kredit';
    $validationMessages['tanggal_jatuh_tempo.after_or_equal'] = 'Tanggal jatuh tempo tidak boleh lebih awal dari tanggal pembelian';
} else {
    // ✅ BARU: Jika bukan kredit, tanggal jatuh tempo harus null
    $validationRules['tanggal_jatuh_tempo'] = 'nullable';
}
```

**Penjelasan Validasi Backend:**

1. **Validasi untuk metode pembayaran Kredit**:
   - `required`: Wajib diisi
   - `date`: Harus format tanggal valid
   - `after_or_equal:tanggal`: Tidak boleh lebih awal dari tanggal pembelian
   - **Custom validation closure**: Memvalidasi bahwa tanggal jatuh tempo **harus tepat 30 hari** setelah tanggal pembelian
     - Menggunakan Carbon untuk parsing tanggal
     - Menghitung expected due date (tanggal pembelian + 30 hari)
     - Membandingkan dengan `isSameDay()` untuk memastikan tanggal sama persis
     - Jika tidak sama, mengembalikan error dengan format tanggal yang benar

2. **Validasi untuk metode pembayaran Tunai/Transfer**:
   - Field tanggal_jatuh_tempo boleh `nullable` (tidak wajib)
   - Backend akan menerima null value untuk field ini

3. **Error messages**:
   - Error jika field kosong (untuk kredit)
   - Error jika tanggal lebih awal dari tanggal pembelian
   - Error jika tanggal tidak tepat 30 hari setelah tanggal pembelian (dengan menampilkan tanggal yang benar)

---

## CARA KERJA FITUR

### A. **Flow User - Metode Pembayaran TUNAI/TRANSFER**

1. User memilih vendor
2. User mengisi tanggal pembelian
3. User memilih metode pembayaran = **Tunai** atau **Transfer**
4. Field "Tanggal Jatuh Tempo" **TIDAK MUNCUL** (hidden)
5. Field "DP" **TIDAK MUNCUL**
6. Saat submit:
   - Field `tanggal_jatuh_tempo` akan di-submit dengan value kosong/null
   - Backend menerima null (validasi: `nullable`)
   - Data tersimpan dengan `tanggal_jatuh_tempo = null`

### B. **Flow User - Metode Pembayaran KREDIT**

1. User memilih vendor
2. User mengisi tanggal pembelian (misal: **2026-07-10**)
3. User memilih metode pembayaran = **Kredit (Hutang)**
4. Field "Tanggal Jatuh Tempo" **MUNCUL**
5. JavaScript otomatis menghitung: `2026-07-10 + 30 hari = 2026-08-09`
6. Field tanggal jatuh tempo otomatis terisi: **2026-08-09**
7. Field readonly, user tidak bisa mengubah
8. Field memiliki atribut `min="2026-08-09"` dan `max="2026-08-09"` (jika user mencoba edit via devtools, hanya bisa pilih tanggal tersebut)
9. Saat submit:
   - Backend validasi: tanggal harus `2026-08-09` (30 hari setelah tanggal pembelian)
   - Jika tidak sesuai, error: "Tanggal jatuh tempo harus 30 hari setelah tanggal pembelian (09/08/2026)."

### C. **Flow User - Mengubah Tanggal Pembelian**

**Skenario:**
- User sudah pilih kredit
- Tanggal pembelian: `2026-07-10`, Due date otomatis: `2026-08-09`
- User mengubah tanggal pembelian menjadi: `2026-07-15`

**Yang Terjadi:**
1. JavaScript mendeteksi perubahan pada field tanggal
2. Function `updateDueDate()` dipanggil
3. Menghitung ulang: `2026-07-15 + 30 hari = 2026-08-14`
4. Field due date otomatis berubah menjadi: `2026-08-14`
5. Atribut min/max juga ikut berubah menjadi `2026-08-14`

---

## VALIDASI BACKEND

### Input Valid ✅

**Kredit dengan tanggal yang benar:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-10',
    'tanggal_jatuh_tempo' => '2026-08-09', // Tepat 30 hari
    'dp' => 5000000,
]
```
✅ **Diterima** - Tanggal jatuh tempo sesuai ketentuan n/30

---

**Tunai tanpa tanggal jatuh tempo:**
```php
[
    'bank_id' => 123, // ID akun kas/bank
    'tanggal' => '2026-07-10',
    'tanggal_jatuh_tempo' => null,
]
```
✅ **Diterima** - Tanggal jatuh tempo nullable untuk non-kredit

---

### Input Invalid ❌

**1. Kredit tanpa tanggal jatuh tempo:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-10',
    'tanggal_jatuh_tempo' => null,
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo wajib diisi untuk pembelian kredit"

---

**2. Kredit dengan tanggal jatuh tempo salah:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-10',
    'tanggal_jatuh_tempo' => '2026-08-10', // Harusnya 2026-08-09
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo harus 30 hari setelah tanggal pembelian (09/08/2026)."

---

**3. Kredit dengan tanggal jatuh tempo lebih awal:**
```php
[
    'bank_id' => 'credit',
    'tanggal' => '2026-07-10',
    'tanggal_jatuh_tempo' => '2026-07-05', // Lebih awal dari tanggal pembelian
]
```
❌ **Ditolak**  
Error: "Tanggal jatuh tempo tidak boleh lebih awal dari tanggal pembelian"

---

## TESTING CHECKLIST

### Frontend Testing:

- [ ] 1. **Default State**: Field tanggal jatuh tempo hidden saat halaman pertama kali dibuka
- [ ] 2. **Pilih Tunai**: Field tetap hidden setelah pilih metode pembayaran tunai/transfer
- [ ] 3. **Pilih Kredit**: Field muncul dan otomatis terisi tanggal +30 hari
- [ ] 4. **Ubah Tanggal Pembelian**: Tanggal jatuh tempo ikut berubah otomatis
- [ ] 5. **Field Readonly**: User tidak bisa mengetik manual di field tanggal jatuh tempo
- [ ] 6. **Ganti ke Tunai setelah Kredit**: Field tanggal jatuh tempo hidden lagi, value dikosongkan
- [ ] 7. **Atribut Required**: Field required hanya muncul saat metode pembayaran kredit

### Backend Testing:

- [ ] 8. **Submit Kredit dengan tanggal benar**: Data tersimpan sukses
- [ ] 9. **Submit Kredit tanpa tanggal jatuh tempo**: Error validasi
- [ ] 10. **Submit Kredit dengan tanggal salah (bukan +30 hari)**: Error validasi dengan pesan jelas
- [ ] 11. **Submit Tunai dengan tanggal jatuh tempo null**: Data tersimpan sukses
- [ ] 12. **Submit Kredit dengan tanggal lebih awal dari tanggal pembelian**: Error validasi

### Integration Testing:

- [ ] 13. **Jurnal Otomatis**: Tetap berjalan normal setelah perubahan
- [ ] 14. **Stok Bahan**: Update stok tidak terpengaruh
- [ ] 15. **Utang Usaha**: Pencatatan utang dengan tanggal jatuh tempo benar
- [ ] 16. **PPN Masukan**: Perhitungan PPN tetap akurat
- [ ] 17. **Biaya Kirim**: Perhitungan biaya kirim tidak terpengaruh
- [ ] 18. **Total Pembelian**: Total perhitungan tetap benar

---

## CONTOH PERHITUNGAN

### Contoh 1: Pembelian Tunai
```
Tanggal Pembelian: 10 Juli 2026
Metode Pembayaran: Kas Kecil (Tunai)
Tanggal Jatuh Tempo: - (tidak ada)

✅ Field tanggal jatuh tempo tidak muncul di form
✅ Data tersimpan dengan tanggal_jatuh_tempo = NULL
```

### Contoh 2: Pembelian Kredit
```
Tanggal Pembelian: 10 Juli 2026
Metode Pembayaran: Kredit (Hutang)
Tanggal Jatuh Tempo: 09 Agustus 2026 (otomatis dihitung)

Perhitungan: 10 Juli 2026 + 30 hari = 09 Agustus 2026

✅ Field tanggal jatuh tempo muncul
✅ Field otomatis terisi: 09 Agustus 2026
✅ Field readonly, user tidak bisa ubah
✅ Backend validasi tanggal harus tepat 30 hari
```

### Contoh 3: Ubah Tanggal Pembelian
```
Awal:
- Tanggal Pembelian: 10 Juli 2026
- Tanggal Jatuh Tempo: 09 Agustus 2026

User mengubah tanggal pembelian menjadi: 15 Juli 2026

Otomatis:
- Tanggal Jatuh Tempo berubah menjadi: 14 Agustus 2026

Perhitungan: 15 Juli 2026 + 30 hari = 14 Agustus 2026

✅ JavaScript otomatis update field
✅ Atribut min/max juga ikut berubah
```

---

## KESESUAIAN DENGAN REQUIREMENTS

| No | Requirement | Status | Keterangan |
|----|-------------|--------|------------|
| 1 | Field hanya muncul jika metode pembayaran = Kredit | ✅ Done | Menggunakan CSS `display:none` dan toggle via JS |
| 2 | Jika metode pembayaran = Tunai, field disembunyikan dan dikosongkan | ✅ Done | Value di-reset menjadi empty string |
| 3 | Jika metode pembayaran = Kredit, field wajib diisi | ✅ Done | Atribut `required` ditambahkan secara dinamis |
| 4 | Tanggal jatuh tempo mengikuti n/30 (30 hari setelah tanggal pembelian) | ✅ Done | Function `calculateDueDate()` menambah 30 hari |
| 5 | Jika tanggal pembelian berubah, tanggal jatuh tempo ikut berubah | ✅ Done | Event listener pada field tanggal pembelian |
| 6 | Mengatur atribut min dan max dengan nilai yang sama | ✅ Done | `min` dan `max` di-set ke tanggal +30 hari |
| 7 | Validasi backend: kredit wajib isi tanggal jatuh tempo | ✅ Done | Rule `required` untuk kredit |
| 8 | Validasi backend: tanggal harus tepat +30 hari | ✅ Done | Custom validation closure dengan Carbon |
| 9 | Validasi backend: tunai boleh null | ✅ Done | Rule `nullable` untuk non-kredit |
| 10 | Tidak merusak fitur PPN, biaya kirim, total, stok, utang, jurnal | ✅ Done | Tidak ada perubahan pada logika tersebut |

---

## CATATAN PENTING

1. **Field Readonly**: Field tanggal jatuh tempo menggunakan atribut `readonly` sehingga user tidak bisa mengetik manual. Namun field tetap bisa di-submit.

2. **Atribut min/max**: Meskipun field readonly, tetap ditambahkan atribut `min` dan `max` sebagai double protection jika ada user yang mencoba manipulasi via devtools.

3. **Validasi Backend Ketat**: Backend memvalidasi bahwa tanggal jatuh tempo **harus tepat** 30 hari setelah tanggal pembelian. Tidak boleh lebih atau kurang, bahkan 1 hari pun.

4. **Carbon untuk Perhitungan**: Menggunakan Carbon untuk perhitungan tanggal agar akurat menangani berbagai edge case (akhir bulan, tahun kabisat, dll).

5. **Tidak Ada Perubahan Database**: Fitur ini tidak memerlukan perubahan struktur database karena field `tanggal_jatuh_tempo` sudah ada di tabel `pembelians`.

6. **Kompatibilitas**: Perubahan ini tidak mempengaruhi:
   - Perhitungan PPN
   - Biaya kirim
   - Total pembelian
   - Update stok bahan
   - Pencatatan utang usaha
   - Pembuatan jurnal otomatis

---

## TROUBLESHOOTING

### Problem 1: Tanggal jatuh tempo tidak otomatis terisi
**Penyebab**: JavaScript belum dijalankan atau ada error
**Solusi**: 
- Cek console browser untuk error
- Pastikan field tanggal pembelian sudah terisi
- Pastikan payment method sudah dipilih 'credit'

### Problem 2: Validasi backend error meskipun tanggal sudah benar
**Penyebab**: Format tanggal tidak sesuai atau timezone berbeda
**Solusi**:
- Pastikan format tanggal YYYY-MM-DD
- Cek timezone server dan browser
- Gunakan Carbon dengan format yang konsisten

### Problem 3: Field tanggal jatuh tempo masih bisa diubah user
**Penyebab**: Atribut readonly tidak terapply
**Solusi**:
- Cek apakah perubahan pada HTML sudah disimpan
- Clear cache browser
- Pastikan tidak ada JavaScript yang menghapus atribut readonly

---

## KESIMPULAN

Fitur Tanggal Jatuh Tempo telah berhasil diperbaiki sesuai dengan ketentuan n/30 (net 30 days). Implementasi menggunakan kombinasi:

1. **Frontend (JavaScript)**: Perhitungan otomatis dan validasi UI
2. **Backend (PHP/Laravel)**: Validasi ketat dengan custom rule
3. **UX**: Field readonly dengan helper text yang jelas

Fitur ini sekarang:
- ✅ User-friendly (otomatis, tidak perlu input manual)
- ✅ Secure (validasi backend ketat)
- ✅ Consistent (frontend dan backend sinkron)
- ✅ Reliable (tidak merusak fitur yang sudah ada)

---

**Tanggal Dokumentasi**: 09 Juli 2026  
**Versi**: 1.0  
**Status**: ✅ SELESAI

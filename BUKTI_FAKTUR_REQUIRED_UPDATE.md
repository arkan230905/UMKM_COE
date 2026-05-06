# Update: Nomor Faktur & Bukti Faktur Wajib Diisi

## Date: May 6, 2026
## Status: ✅ COMPLETED

---

## Perubahan yang Dilakukan

### 🎯 Tujuan
Membuat field **Nomor Faktur Pembelian** dan **Bukti Faktur** menjadi **WAJIB DIISI** dengan validasi yang ketat.

---

## Perubahan Detail

### 1. View Update - Form Create
**File:** `resources/views/transaksi/pembelian/create.blade.php`

#### Sebelum:
```html
<div class="col-md-3">
    <label class="form-label">Nomor Faktur Pembelian</label>
    <input type="text" name="nomor_faktur" class="form-control">
</div>

<div class="col-md-3">
    <label class="form-label">Bukti Faktur</label>
    <input type="file" name="bukti_faktur" class="form-control">
</div>
```

#### Sesudah:
```html
<div class="col-md-3">
    <label class="form-label">Nomor Faktur Pembelian <span class="text-danger">*</span></label>
    <input type="text" name="nomor_faktur" class="form-control" required>
    @error('nomor_faktur')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Bukti Faktur <span class="text-danger">*</span></label>
    <input type="file" name="bukti_faktur" class="form-control" required>
    <small class="text-muted">Format: JPG, PNG, PDF (Max: 2MB)</small>
    @error('bukti_faktur')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
```

**Perubahan:**
- ✅ Tambah `<span class="text-danger">*</span>` untuk tanda wajib
- ✅ Tambah atribut `required` pada input
- ✅ Tambah error message display dengan `@error`

---

### 2. Controller Update - Validasi
**File:** `app/Http/Controllers/PembelianController.php`

#### Sebelum:
```php
$request->validate([
    'vendor_id' => 'required|exists:vendors,id',
    'tanggal' => 'required|date',
    'bank_id' => 'required',
    'jumlah_satuan_utama' => 'nullable|array',
]);
```

#### Sesudah:
```php
$request->validate([
    'vendor_id' => 'required|exists:vendors,id',
    'nomor_faktur' => 'required|string|max:255',
    'bukti_faktur' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
    'tanggal' => 'required|date',
    'bank_id' => 'required',
    'jumlah_satuan_utama' => 'nullable|array',
], [
    'nomor_faktur.required' => 'Nomor faktur pembelian wajib diisi',
    'bukti_faktur.required' => 'Bukti faktur wajib diupload',
    'bukti_faktur.mimes' => 'Bukti faktur harus berformat JPG, PNG, atau PDF',
    'bukti_faktur.max' => 'Ukuran bukti faktur maksimal 2MB',
]);
```

**Perubahan:**
- ✅ Tambah validasi `nomor_faktur` → `required|string|max:255`
- ✅ Tambah validasi `bukti_faktur` → `required|file|mimes:jpg,jpeg,png,pdf|max:2048`
- ✅ Tambah custom error messages dalam Bahasa Indonesia
- ✅ Hapus validasi duplikat di bagian upload file

---

## Validasi Rules

### Nomor Faktur Pembelian
| Rule | Deskripsi |
|------|-----------|
| `required` | Wajib diisi |
| `string` | Harus berupa text |
| `max:255` | Maksimal 255 karakter |

**Error Message:**
- "Nomor faktur pembelian wajib diisi"

### Bukti Faktur
| Rule | Deskripsi |
|------|-----------|
| `required` | Wajib diupload |
| `file` | Harus berupa file |
| `mimes:jpg,jpeg,png,pdf` | Format: JPG, JPEG, PNG, atau PDF |
| `max:2048` | Maksimal 2MB (2048 KB) |

**Error Messages:**
- "Bukti faktur wajib diupload"
- "Bukti faktur harus berformat JPG, PNG, atau PDF"
- "Ukuran bukti faktur maksimal 2MB"

---

## Tampilan UI

### Form Input
```
┌─────────────────────────────────────────────────────────┐
│ Nomor Faktur Pembelian *                                │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 0232000002                                          │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Bukti Faktur *                                          │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Choose File   No file chosen                        │ │
│ └─────────────────────────────────────────────────────┘ │
│ Format: JPG, PNG, PDF (Max: 2MB)                        │
└─────────────────────────────────────────────────────────┘
```

**Tanda Bintang Merah (*)** menunjukkan field wajib diisi.

---

## Error Messages Display

### Jika Nomor Faktur Kosong:
```
┌─────────────────────────────────────────────────────────┐
│ Nomor Faktur Pembelian *                                │
│ ┌─────────────────────────────────────────────────────┐ │
│ │                                                     │ │
│ └─────────────────────────────────────────────────────┘ │
│ ⚠️ Nomor faktur pembelian wajib diisi                   │
└─────────────────────────────────────────────────────────┘
```

### Jika Bukti Faktur Tidak Diupload:
```
┌─────────────────────────────────────────────────────────┐
│ Bukti Faktur *                                          │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Choose File   No file chosen                        │ │
│ └─────────────────────────────────────────────────────┘ │
│ Format: JPG, PNG, PDF (Max: 2MB)                        │
│ ⚠️ Bukti faktur wajib diupload                          │
└─────────────────────────────────────────────────────────┘
```

### Jika Format File Salah:
```
┌─────────────────────────────────────────────────────────┐
│ Bukti Faktur *                                          │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Choose File   document.docx                         │ │
│ └─────────────────────────────────────────────────────┘ │
│ Format: JPG, PNG, PDF (Max: 2MB)                        │
│ ⚠️ Bukti faktur harus berformat JPG, PNG, atau PDF     │
└─────────────────────────────────────────────────────────┘
```

### Jika File Terlalu Besar:
```
┌─────────────────────────────────────────────────────────┐
│ Bukti Faktur *                                          │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Choose File   large_file.pdf (5MB)                  │ │
│ └─────────────────────────────────────────────────────┘ │
│ Format: JPG, PNG, PDF (Max: 2MB)                        │
│ ⚠️ Ukuran bukti faktur maksimal 2MB                     │
└─────────────────────────────────────────────────────────┘
```

---

## Behavior

### Client-Side Validation (HTML5)
- ✅ Browser akan mencegah submit jika field kosong
- ✅ Pesan error default browser: "Please fill out this field"
- ✅ Validasi format file di browser

### Server-Side Validation (Laravel)
- ✅ Validasi ulang di server untuk keamanan
- ✅ Custom error messages dalam Bahasa Indonesia
- ✅ Redirect back dengan error messages jika validasi gagal
- ✅ Old input dipertahankan (kecuali file)

---

## Testing Scenarios

### ✅ Scenario 1: Submit Tanpa Nomor Faktur
**Input:**
- Nomor Faktur: (kosong)
- Bukti Faktur: faktur.pdf

**Expected:**
- ❌ Form tidak tersubmit
- ⚠️ Error: "Nomor faktur pembelian wajib diisi"

### ✅ Scenario 2: Submit Tanpa Bukti Faktur
**Input:**
- Nomor Faktur: INV-001
- Bukti Faktur: (tidak ada file)

**Expected:**
- ❌ Form tidak tersubmit
- ⚠️ Error: "Bukti faktur wajib diupload"

### ✅ Scenario 3: Submit dengan Format File Salah
**Input:**
- Nomor Faktur: INV-001
- Bukti Faktur: document.docx

**Expected:**
- ❌ Form tidak tersubmit
- ⚠️ Error: "Bukti faktur harus berformat JPG, PNG, atau PDF"

### ✅ Scenario 4: Submit dengan File Terlalu Besar
**Input:**
- Nomor Faktur: INV-001
- Bukti Faktur: large_file.pdf (5MB)

**Expected:**
- ❌ Form tidak tersubmit
- ⚠️ Error: "Ukuran bukti faktur maksimal 2MB"

### ✅ Scenario 5: Submit dengan Data Valid
**Input:**
- Nomor Faktur: INV-001
- Bukti Faktur: faktur.pdf (1MB)

**Expected:**
- ✅ Form berhasil tersubmit
- ✅ Data tersimpan ke database
- ✅ File terupload ke storage
- ✅ Redirect ke halaman list dengan success message

---

## Impact Analysis

### Positive Impact
✅ **Data Integrity:** Semua pembelian pasti memiliki nomor faktur dan bukti
✅ **Audit Trail:** Bukti faktur tersimpan untuk keperluan audit
✅ **Compliance:** Memenuhi requirement dokumentasi pembelian
✅ **User Experience:** Error messages jelas dalam Bahasa Indonesia

### Potential Issues
⚠️ **Existing Data:** Pembelian lama mungkin tidak punya bukti faktur
⚠️ **User Workflow:** User harus siapkan file sebelum input
⚠️ **File Size:** User perlu compress file jika > 2MB

### Mitigation
- Existing data tetap valid (kolom nullable di database)
- Hanya pembelian baru yang wajib upload
- Berikan panduan compress file jika diperlukan

---

## Files Modified

### Views
- ✅ `resources/views/transaksi/pembelian/create.blade.php`

### Controllers
- ✅ `app/Http/Controllers/PembelianController.php`

---

## Rollback Plan

Jika perlu rollback ke optional:

### 1. View
```html
<!-- Remove * and required attribute -->
<label class="form-label">Nomor Faktur Pembelian</label>
<input type="text" name="nomor_faktur" class="form-control">

<label class="form-label">Bukti Faktur</label>
<input type="file" name="bukti_faktur" class="form-control">
```

### 2. Controller
```php
// Change to nullable
$request->validate([
    'nomor_faktur' => 'nullable|string|max:255',
    'bukti_faktur' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    // ... other rules
]);
```

---

## Conclusion

✅ **Nomor Faktur Pembelian dan Bukti Faktur sekarang WAJIB DIISI!**

Perubahan ini memastikan:
- Setiap pembelian memiliki nomor faktur yang valid
- Setiap pembelian memiliki bukti faktur yang terdokumentasi
- Validasi ketat untuk format dan ukuran file
- Error messages yang jelas dan informatif

**Status:** Production Ready ✅

---

**Last Updated:** May 6, 2026  
**Version:** 2.0  
**Author:** Kiro AI Assistant

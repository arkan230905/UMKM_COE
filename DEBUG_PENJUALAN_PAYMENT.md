# 🔍 DEBUG: Tombol Bayar Tidak Mengarah ke Tahap Pembayaran

## 🎯 Problem

Saat klik tombol "Bayar" di halaman `/transaksi/penjualan/create`, sistem tidak mengarah ke tahap pembayaran.

## 🔍 Analisis Kode

### 1. Tombol Bayar
**File**: `resources/views/transaksi/penjualan/create.blade.php` (line ~250)

```html
<button type="button" class="btn btn-primary" id="btn-bayar" onclick="submitForPayment()">Bayar</button>
```

✅ Tombol sudah benar:
- Type: `button` (tidak submit form)
- Event: `onclick="submitForPayment()"`

### 2. Fungsi submitForPayment()
**File**: `resources/views/transaksi/penjualan/create.blade.php` (line ~253-310)

```javascript
function submitForPayment() {
    // Validate form
    const form = document.getElementById('form-penjualan');
    
    // Check if there are items in the table
    const tableRows = document.querySelectorAll('#detailTableJual tbody tr');
    if (tableRows.length === 0) {
        alert('Tambahkan minimal satu produk');
        return;
    }
    
    // ... validation code ...
    
    // Prepare data for payment
    const paymentData = {
        tanggal: document.querySelector('input[name="tanggal"]').value,
        waktu: document.querySelector('input[name="waktu"]').value,
        payment_method: paymentMethod,
        sumber_dana: document.getElementById('sumber_dana_jual').value,
        subtotal_produk: parseCurrency(document.querySelector('input[name="subtotal_produk"]').value),
        biaya_ongkir: parseFloat(document.getElementById('biaya_ongkir').value) || 0,
        biaya_service: parseFloat(document.getElementById('biaya_service').value) || 0,
        ppn_persen: parseFloat(document.getElementById('ppn_persen').value) || 0,
        total_ppn: parseCurrency(document.getElementById('total_ppn').value),
        total: total,
        items: tableData
    };
    
    // Store in session and redirect to payment page
    fetch('{{ route("transaksi.penjualan.prepare-payment") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to payment page
            window.location.href = data.redirect_url;
        } else {
            alert('Error: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    });
}
```

### 3. Route
**File**: `routes/web.php`

```php
Route::post('penjualan/prepare-payment', [PenjualanController::class, 'preparePayment'])
    ->name('penjualan.prepare-payment');
```

✅ Route sudah ada dan benar

### 4. Controller Method
**File**: `app/Http/Controllers/PenjualanController.php`

```php
public function preparePayment(Request $request)
{
    $paymentData = $request->all();
    
    // Validate payment data
    if (empty($paymentData['items']) || count($paymentData['items']) === 0) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada item dalam pesanan'
        ], 422);
    }
    
    if ($paymentData['total'] <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Total pembayaran harus lebih dari 0'
        ], 422);
    }
    
    // Store in session
    session(['penjualan_payment_data' => $paymentData]);
    
    return response()->json([
        'success' => true,
        'redirect_url' => route('transaksi.penjualan.payment')
    ]);
}
```

✅ Method sudah ada dan benar

## 🐛 Kemungkinan Penyebab

### 1. ❌ JavaScript Error (Paling Mungkin)
Kemungkinan ada error JavaScript yang mencegah fungsi `submitForPayment()` berjalan.

**Cara Cek**:
1. Buka halaman `/transaksi/penjualan/create`
2. Buka Developer Tools (F12)
3. Buka tab "Console"
4. Tambahkan produk
5. Klik tombol "Bayar"
6. Lihat apakah ada error di console

**Kemungkinan Error**:
- `parseCurrency is not defined`
- `document.querySelector('meta[name="csrf-token"]')` returns null
- AJAX request gagal (CORS, 500 error, dll)

### 2. ❌ CSRF Token Tidak Ada
Jika meta tag CSRF tidak ada di layout, AJAX request akan gagal.

**Cara Cek**:
```html
<!-- Harus ada di resources/views/layouts/app.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

✅ **SUDAH ADA** - Verified di `resources/views/layouts/app.blade.php` line 23

### 3. ❌ Validasi Gagal
Fungsi `submitForPayment()` memiliki beberapa validasi yang bisa menghentikan proses:

```javascript
// Check 1: Apakah ada item di tabel?
if (tableRows.length === 0) {
    alert('Tambahkan minimal satu produk');
    return; // STOP HERE
}

// Check 2: Apakah ada item yang valid?
if (!hasValidItem) {
    alert('Tambahkan minimal satu produk');
    return; // STOP HERE
}

// Check 3: Apakah total > 0?
if (total <= 0) {
    alert('Total pembayaran harus lebih dari 0');
    return; // STOP HERE
}
```

### 4. ❌ Fungsi parseCurrency() Tidak Terdefinisi
Fungsi `parseCurrency()` dipanggil di dalam `submitForPayment()`:

```javascript
const total = parseCurrency(totalInput.value);
```

**Lokasi Definisi**: Line ~360 di file yang sama
```javascript
function parseCurrency(formattedValue) {
    if (!formattedValue) return 0;
    return parseFloat(formattedValue.toString().replace(/[^\d]/g, '')) || 0;
}
```

✅ **SUDAH ADA** - Fungsi sudah didefinisikan

## 🔧 Solusi

### Solusi 1: Tambahkan Console Log untuk Debug

Tambahkan console.log di fungsi `submitForPayment()` untuk tracking:

```javascript
function submitForPayment() {
    console.log('submitForPayment() called'); // DEBUG
    
    const form = document.getElementById('form-penjualan');
    console.log('Form:', form); // DEBUG
    
    const tableRows = document.querySelectorAll('#detailTableJual tbody tr');
    console.log('Table rows:', tableRows.length); // DEBUG
    
    if (tableRows.length === 0) {
        alert('Tambahkan minimal satu produk');
        return;
    }
    
    // ... rest of code ...
    
    console.log('Payment data:', paymentData); // DEBUG
    
    fetch('{{ route("transaksi.penjualan.prepare-payment") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(paymentData)
    })
    .then(response => {
        console.log('Response status:', response.status); // DEBUG
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // DEBUG
        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert('Error: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error); // Already there
        alert('Terjadi kesalahan: ' + error.message);
    });
}
```

### Solusi 2: Cek Browser Console

1. Buka `/transaksi/penjualan/create`
2. Tekan F12 (Developer Tools)
3. Buka tab "Console"
4. Tambahkan produk ke tabel
5. Klik tombol "Bayar"
6. Lihat output console

**Yang Harus Dicek**:
- Apakah ada error merah?
- Apakah `submitForPayment() called` muncul?
- Apakah ada alert yang muncul?
- Apakah AJAX request berhasil?

### Solusi 3: Cek Network Tab

1. Buka Developer Tools (F12)
2. Buka tab "Network"
3. Klik tombol "Bayar"
4. Lihat apakah ada request ke `/transaksi/penjualan/prepare-payment`
5. Klik request tersebut
6. Lihat:
   - **Status Code**: Harus 200
   - **Response**: Harus `{"success": true, "redirect_url": "..."}`
   - **Headers**: Harus ada `X-CSRF-TOKEN`

### Solusi 4: Pastikan Produk Sudah Ditambahkan

Fungsi akan berhenti jika tidak ada produk yang valid:

```javascript
let hasValidItem = false;
tableRows.forEach(row => {
    const produkSelect = row.querySelector('.produk-select');
    if (produkSelect && produkSelect.value) {
        hasValidItem = true;
    }
});

if (!hasValidItem) {
    alert('Tambahkan minimal satu produk'); // ALERT INI MUNCUL?
    return;
}
```

**Cara Test**:
1. Tambahkan produk ke tabel
2. Pastikan dropdown produk sudah dipilih (tidak kosong)
3. Pastikan qty > 0
4. Klik tombol "Bayar"

## 📋 Checklist Debugging

Silakan cek satu per satu:

- [ ] Buka halaman `/transaksi/penjualan/create`
- [ ] Buka Developer Tools (F12) → Tab Console
- [ ] Tambahkan minimal 1 produk ke tabel
- [ ] Pastikan produk terpilih di dropdown
- [ ] Pastikan qty > 0
- [ ] Pastikan total > 0
- [ ] Klik tombol "Bayar"
- [ ] Lihat apakah ada:
  - [ ] Alert "Tambahkan minimal satu produk"?
  - [ ] Alert "Total pembayaran harus lebih dari 0"?
  - [ ] Error di console (merah)?
  - [ ] Request di Network tab?
  - [ ] Response success dari server?

## 🎯 Langkah Selanjutnya

Setelah Anda cek console browser, beritahu saya:

1. **Apakah ada error di console?** (screenshot atau copy error message)
2. **Apakah ada alert yang muncul?** (apa isi alertnya?)
3. **Apakah ada request di Network tab?** (status code berapa?)
4. **Apakah tombol "Bayar" diklik tapi tidak ada reaksi sama sekali?**

Dengan informasi ini, saya bisa memberikan solusi yang lebih spesifik.

---

**Date**: May 6, 2026  
**Status**: 🔍 DEBUGGING IN PROGRESS

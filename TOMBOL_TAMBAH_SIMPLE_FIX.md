# Fix Tombol Tambah - Solusi Sederhana

## Masalah
Tombol "Tambah Bahan Baku" dan "Tambah Bahan Pendukung" tidak berfungsi.

## Solusi
Saya sudah mengubah button dari menggunakan ID + addEventListener menjadi onclick handler langsung.

## Yang Sudah Diubah

### 1. Button HTML
```html
<!-- SEBELUM -->
<button type="button" class="btn btn-sm btn-success" id="addBahanBaku">

<!-- SESUDAH -->
<button type="button" class="btn btn-sm btn-success" onclick="addBahanBakuRow()">
```

### 2. JavaScript
Sekarang menggunakan fungsi global `window.addBahanBakuRow` dan `window.addBahanPendukungRow`

## Cara Test

1. **Hard Refresh**: `Ctrl + F5`

2. **Buka Console** (F12)

3. **Test Manual di Console**:
```javascript
// Test apakah fungsi ada
console.log(typeof addBahanBakuRow);  // harus return "function"
console.log(typeof addBahanPendukungRow);  // harus return "function"

// Test manual call
addBahanBakuRow();  // harus menambah baris
```

4. **Klik Tombol**
   - Klik "Tambah Bahan Baku"
   - Harus muncul log di console: "=== addBahanBakuRow called ==="
   - Harus ada baris baru di tabel

## Jika Masih Tidak Berfungsi

### Cek 1: Apakah fungsi terdefinisi?
```javascript
console.log(window.addBahanBakuRow);
```
Jika `undefined`, berarti JavaScript tidak load.

### Cek 2: Apakah ada error?
Lihat tab Console, cari error merah.

### Cek 3: Apakah button ada?
```javascript
const buttons = document.querySelectorAll('button[onclick*="addBahan"]');
console.log('Found buttons:', buttons.length);
```
Harus return minimal 2 (Bahan Baku + Bahan Pendukung).

### Cek 4: Test onclick langsung
Klik kanan pada tombol → Inspect → Lihat apakah ada attribute `onclick="addBahanBakuRow()"`

## Screenshot Console yang Benar

Saat halaman load:
```
Biaya Bahan Create - Script loaded
=== DOM Ready ===
Auto-adding first Bahan Baku row...
=== addBahanBakuRow called ===
Row added: bahanBaku_1234567890
Row numbers updated - Baku: 1 Pendukung: 0
=== Initialization Complete ===
```

Saat klik "Tambah Bahan Baku":
```
=== addBahanBakuRow called ===
Row added: bahanBaku_1234567891
Row numbers updated - Baku: 2 Pendukung: 0
```

## Jika Fungsi Tidak Terdefinisi

Kemungkinan:
1. JavaScript tidak di-include
2. Ada syntax error
3. File tidak tersimpan

**Solusi**: 
- Clear cache browser
- Hard refresh: `Ctrl + Shift + R`
- Restart Laravel server

## Contact Point

Jika masih tidak berfungsi, berikan screenshot:
1. Console log (F12 → Console tab)
2. Network tab (cek apakah file JS/CSS load)
3. Elements tab (inspect button, lihat onclick attribute)

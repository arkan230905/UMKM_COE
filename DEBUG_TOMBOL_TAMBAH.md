# Debug Tombol Tambah - Step by Step

## Langkah 1: Buka Console (F12)

## Langkah 2: Jalankan Command Ini

### Test 1: Cek apakah fungsi terdefinisi
```javascript
console.log('=== TEST 1: Cek Fungsi ===');
console.log('tambahBahanBaku:', typeof tambahBahanBaku);
console.log('tambahBahanPendukung:', typeof tambahBahanPendukung);
console.log('hapusBaris:', typeof hapusBaris);
console.log('hitungTotal:', typeof hitungTotal);
```

**Expected**: Semua harus return "function"
**Jika "undefined"**: JavaScript tidak load atau ada error

---

### Test 2: Cek apakah data tersedia
```javascript
console.log('=== TEST 2: Cek Data ===');
console.log('bahanBakus:', bahanBakus ? bahanBakus.length : 'undefined');
console.log('bahanPendukungs:', bahanPendukungs ? bahanPendukungs.length : 'undefined');
console.log('satuans:', satuans ? satuans.length : 'undefined');
```

**Expected**: Harus ada angka (jumlah data)
**Jika "undefined"**: Data tidak di-pass dari controller

---

### Test 3: Cek apakah button ada
```javascript
console.log('=== TEST 3: Cek Button ===');
const buttons = document.querySelectorAll('button[onclick*="tambah"]');
console.log('Found buttons:', buttons.length);
buttons.forEach((btn, i) => {
    console.log(`Button ${i+1}:`, btn.onclick);
});
```

**Expected**: Harus ada 2 button
**Jika 0**: Button tidak ada atau onclick tidak terpasang

---

### Test 4: Cek apakah tbody ada
```javascript
console.log('=== TEST 4: Cek Tbody ===');
console.log('bahanBakuBody:', document.getElementById('bahanBakuBody'));
console.log('bahanPendukungBody:', document.getElementById('bahanPendukungBody'));
```

**Expected**: Harus return element (bukan null)
**Jika null**: ID tidak ada di HTML

---

### Test 5: Manual trigger fungsi
```javascript
console.log('=== TEST 5: Manual Trigger ===');
try {
    tambahBahanBaku();
    console.log('✅ tambahBahanBaku() berhasil');
} catch(e) {
    console.error('❌ tambahBahanBaku() error:', e.message);
}
```

**Expected**: Harus ada log "berhasil" dan baris baru muncul
**Jika error**: Lihat error message

---

### Test 6: Cek apakah baris ditambahkan
```javascript
console.log('=== TEST 6: Cek Baris ===');
const bahanBakuRows = document.querySelectorAll('#bahanBakuBody tr');
const bahanPendukungRows = document.querySelectorAll('#bahanPendukungBody tr');
console.log('Bahan Baku rows:', bahanBakuRows.length);
console.log('Bahan Pendukung rows:', bahanPendukungRows.length);
```

**Expected**: Minimal 1 baris Bahan Baku (auto-add)
**Jika 0**: Auto-add tidak berjalan

---

## Langkah 3: Screenshot & Kirim Hasil

Tolong screenshot atau copy-paste hasil dari console untuk semua test di atas.

---

## Kemungkinan Masalah & Solusi

### Jika fungsi "undefined":
**Masalah**: JavaScript tidak load
**Solusi**: 
1. Hard refresh: `Ctrl + Shift + R`
2. Clear cache browser
3. Cek apakah ada error merah di console

### Jika data "undefined":
**Masalah**: Data tidak di-pass dari controller
**Solusi**: Cek method `create()` di controller

### Jika button tidak ada:
**Masalah**: HTML tidak render dengan benar
**Solusi**: View page source, cari "onclick="

### Jika tbody null:
**Masalah**: ID salah atau tidak ada
**Solusi**: Cek HTML, pastikan ada `id="bahanBakuBody"`

### Jika manual trigger error:
**Masalah**: Ada bug di fungsi
**Solusi**: Lihat error message, fix bug tersebut

---

## Quick Fix Commands

Jika semua test PASS tapi tombol tetap tidak berfungsi, coba:

```javascript
// Force attach onclick
document.querySelectorAll('button').forEach(btn => {
    if (btn.textContent.includes('Tambah Bahan Baku')) {
        btn.onclick = tambahBahanBaku;
        console.log('✅ Attached onclick to Tambah Bahan Baku');
    }
    if (btn.textContent.includes('Tambah Bahan Pendukung')) {
        btn.onclick = tambahBahanPendukung;
        console.log('✅ Attached onclick to Tambah Bahan Pendukung');
    }
});
```

---

## Tolong Berikan Info Ini:

1. **Hasil dari Test 1-6** (screenshot atau copy-paste)
2. **Apakah ada error merah di console?** (screenshot)
3. **Apakah halaman sudah di-refresh?** (Ctrl + F5)
4. **Browser apa yang digunakan?** (Chrome/Edge/Firefox)

Dengan info ini saya bisa bantu fix dengan tepat! 🔍

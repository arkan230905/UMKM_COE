# Fix Kode Perusahaan Issue - Field Tidak Terkirim

## 🎯 Masalah

Meskipun sudah mengisi kode perusahaan, masih muncul error:
```
Error: Kode perusahaan wajib diisi.
```

## 🔍 Penyebab

Ada **DUA field dengan name yang sama** `kode_perusahaan`:
1. Field untuk role normal (owner, admin, pegawai, kasir)
2. Field untuk role presensi

Ketika form di-submit, browser mengirim **SEMUA field dengan name yang sama**, termasuk yang hidden. Jika field yang hidden tidak di-disable, nilainya (kosong) akan ikut terkirim dan bisa menimpa nilai yang benar.

## ✅ Solusi

### 1. Disable Field yang Tidak Digunakan
Menambahkan `disabled` attribute pada field yang tidak aktif:

```javascript
// Disable semua field dulu
kodeInput.disabled = true;
presensiKodeInput.disabled = true;

// Enable hanya yang dibutuhkan
if (role === 'admin') {
    kodeInput.disabled = false; // Enable field yang digunakan
}
```

**Catatan:** Field yang `disabled` tidak akan dikirim saat form submit.

### 2. Update JavaScript untuk Enable/Disable
Script sekarang akan:
- Disable semua field saat role berubah
- Enable hanya field yang dibutuhkan untuk role tersebut
- Memastikan hanya satu field `kode_perusahaan` yang aktif

### 3. Tambah Debug Logging
Menambahkan console log untuk melihat status field:
```javascript
console.log('Kode Perusahaan field status:');
console.log('  Regular kode: value="XXX", disabled=false');
console.log('  Presensi kode: value="", disabled=true');
```

## 🧪 Cara Test

### Test 1: Login Admin
1. Akses: `http://127.0.0.1:8000/login`
2. Buka Console (F12)
3. Pilih role: **Admin**
4. Isi email: `abiyyu123@gmail.com`
5. Isi kode perusahaan: `UMKM-COE12`
6. **SEBELUM klik LOGIN**, lihat console log
7. Klik LOGIN
8. Lihat console log untuk memastikan field yang benar terkirim

### Console Log yang Benar:
```
Role changed to: admin
=== FORM SUBMIT EVENT TRIGGERED ===
Selected role: admin
Form data:
  _token: xxx
  login_role: admin
  email: abiyyu123@gmail.com
  kode_perusahaan: UMKM-COE12
Kode Perusahaan field status:
  Regular kode: value="UMKM-COE12", disabled=false
  Presensi kode: value="", disabled=true
Form validation passed, allowing submission...
```

**Penting:** 
- Regular kode harus `disabled=false` dan ada valuenya
- Presensi kode harus `disabled=true`
- Form data harus menunjukkan `kode_perusahaan: UMKM-COE12`

### Test 2: Login Owner
1. Pilih role: **Owner**
2. Isi email: `arkan230905@gmail.com`
3. Isi kode perusahaan: `UMKM-COE12`
4. Isi password
5. Lihat console log
6. Klik LOGIN

### Test 3: Login Pelanggan (Tanpa Kode)
1. Pilih role: **Pelanggan**
2. Isi email: `abiyyu@gmail.com`
3. Isi password
4. Lihat console log - **TIDAK boleh ada** kode_perusahaan
5. Klik LOGIN

## 🔍 Debugging

### Jika Masih Error "Kode perusahaan wajib diisi"

1. **Buka Console (F12)**
2. **Pilih role dan isi form**
3. **Klik LOGIN**
4. **Lihat console log** dan cari:
   ```
   Kode Perusahaan field status:
     Regular kode: value="???", disabled=???
     Presensi kode: value="???", disabled=???
   ```

5. **Periksa:**
   - Apakah field yang benar sudah `disabled=false`?
   - Apakah field yang benar ada valuenya?
   - Apakah field yang tidak digunakan sudah `disabled=true`?

### Jika Field Tidak Terisi
Kemungkinan:
1. JavaScript tidak berjalan dengan benar
2. Ada error di console yang mencegah script
3. Field tidak di-enable dengan benar

### Jika Masih Mengirim Field Kosong
Kemungkinan:
1. Field yang salah tidak di-disable
2. Ada duplikasi field dengan name yang sama
3. Browser cache - coba hard refresh (Ctrl+Shift+R)

## 📝 Perubahan File

### `resources/views/auth/login.blade.php`

**Perubahan 1: Tambah disabled pada field**
```html
<!-- Field untuk role normal -->
<input id="kode_perusahaan" name="kode_perusahaan" disabled>

<!-- Field untuk presensi -->
<input id="presensi_kode_perusahaan" name="kode_perusahaan" disabled>
```

**Perubahan 2: JavaScript enable/disable field**
```javascript
// Disable semua dulu
kodeInput.disabled = true;
presensiKodeInput.disabled = true;

// Enable yang dibutuhkan
if (role === 'admin') {
    kodeInput.disabled = false;
}
```

**Perubahan 3: Tambah debug logging**
```javascript
console.log('Kode Perusahaan field status:');
console.log('  Regular kode:', kodeInput ? `value="${kodeInput.value}", disabled=${kodeInput.disabled}` : 'not found');
console.log('  Presensi kode:', presensiKodeInput ? `value="${presensiKodeInput.value}", disabled=${presensiKodeInput.disabled}` : 'not found');
```

## ⚠️ Catatan Penting

1. **Disabled field tidak dikirim** - Ini adalah behavior HTML standar
2. **Hanya satu field aktif** - Pastikan hanya field yang dibutuhkan yang enabled
3. **Console log sangat penting** - Gunakan untuk debug masalah field
4. **Hard refresh** - Jika perubahan tidak terlihat, coba Ctrl+Shift+R

## 🚀 Next Steps

1. ✅ Clear browser cache (Ctrl+Shift+R)
2. ✅ Buka console (F12)
3. ✅ Test login dengan role Admin
4. ✅ Periksa console log untuk memastikan field yang benar terkirim
5. ✅ Jika masih error, screenshot console log dan laporkan

## 📊 Status Field per Role

| Role | Field Kode Regular | Field Kode Presensi |
|------|-------------------|---------------------|
| Owner | ✅ Enabled | ❌ Disabled |
| Admin | ✅ Enabled | ❌ Disabled |
| Pegawai | ✅ Enabled | ❌ Disabled |
| Kasir | ✅ Enabled | ❌ Disabled |
| Pelanggan | ❌ Disabled | ❌ Disabled |
| Presensi | ❌ Disabled | ✅ Enabled |

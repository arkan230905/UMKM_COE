# Debug Kode Perusahaan Issue

## 🔍 Masalah

Meskipun sudah mengisi kode perusahaan dengan benar, masih muncul error:
```
Kode perusahaan wajib diisi.
```

## 🧪 Langkah Debugging

### Step 1: Gunakan Login Debug Mode

Akses halaman debug khusus:
```
http://127.0.0.1:8000/login-debug
```

Halaman ini akan menampilkan:
- ✅ Form login sederhana
- ✅ Status field (visible/hidden)
- ✅ Console log real-time
- ✅ Preview data yang akan dikirim

### Step 2: Test dengan Login Debug

1. Buka `http://127.0.0.1:8000/login-debug`
2. Pilih role: **Admin**
3. Lihat di panel kanan:
   - Field Status harus menunjukkan "Kode Perusahaan: VISIBLE"
   - Form Data Preview harus menunjukkan `kode_perusahaan: UMKM-COE12`
4. Klik LOGIN
5. Lihat Console Log untuk melihat data yang dikirim

### Step 3: Periksa Laravel Log

Setelah klik LOGIN, buka Laravel log:
```bash
type storage\logs\laravel.log
```

Cari log terbaru dengan tag `=== LOGIN REQUEST DEBUG ===`

Anda harus melihat:
```
[timestamp] local.INFO: === LOGIN REQUEST DEBUG ===
[timestamp] local.INFO: All Input: {"_token":"xxx","login_role":"admin","kode_perusahaan":"UMKM-COE12","email":"abiyyu123@gmail.com"}
[timestamp] local.INFO: Kode Perusahaan: {"value":"UMKM-COE12","exists":true}
[timestamp] local.INFO: Role: {"role":"admin"}
[timestamp] local.INFO: Validation Rules: {"email":"required|email","kode_perusahaan":"required|string"}
```

## 📊 Analisis Kemungkinan Masalah

### Kemungkinan 1: Field Tidak Terkirim
**Gejala:** Log menunjukkan `kode_perusahaan: null` atau tidak ada
**Penyebab:** Field hidden atau disabled
**Solusi:** Gunakan login-debug untuk memastikan field visible

### Kemungkinan 2: Field Terkirim Kosong
**Gejala:** Log menunjukkan `kode_perusahaan: ""`
**Penyebab:** Value field kosong meskipun terlihat terisi
**Solusi:** Periksa JavaScript yang mungkin mengosongkan field

### Kemungkinan 3: Ada Field Duplikat
**Gejala:** Log menunjukkan dua kode_perusahaan
**Penyebab:** Masih ada field duplikat yang tidak di-remove
**Solusi:** Periksa HTML untuk field dengan name="kode_perusahaan"

### Kemungkinan 4: Browser Cache
**Gejala:** Perubahan tidak terlihat
**Penyebab:** Browser masih menggunakan JavaScript lama
**Solusi:** Hard refresh (Ctrl+Shift+R) atau clear cache

### Kemungkinan 5: Validasi Salah
**Gejala:** Field terkirim dengan benar tapi tetap error
**Penyebab:** Validasi rule salah atau ada middleware yang mengubah data
**Solusi:** Periksa log untuk melihat validation rules

## 🔧 Solusi Berdasarkan Log

### Jika Log Menunjukkan: `kode_perusahaan: null`
```
Masalah: Field tidak terkirim
Solusi:
1. Periksa apakah field visible di browser
2. Periksa apakah field ada attribute disabled
3. Gunakan login-debug untuk test
```

### Jika Log Menunjukkan: `kode_perusahaan: ""`
```
Masalah: Field terkirim tapi kosong
Solusi:
1. Periksa value field di browser (inspect element)
2. Periksa JavaScript yang mungkin mengosongkan field
3. Test dengan login-debug (sudah ada default value)
```

### Jika Log Menunjukkan: `kode_perusahaan: "UMKM-COE12"` tapi tetap error
```
Masalah: Validasi atau logic error
Solusi:
1. Periksa validation rules di log
2. Periksa apakah ada middleware yang mengubah request
3. Periksa apakah ada custom validation
```

## 📝 Checklist Debugging

Ikuti checklist ini secara berurutan:

- [ ] **Hard refresh browser** (Ctrl+Shift+R)
- [ ] **Akses login-debug** (`/login-debug`)
- [ ] **Pilih role Admin**
- [ ] **Periksa Field Status** - Harus "VISIBLE"
- [ ] **Periksa Form Data Preview** - Harus ada kode_perusahaan
- [ ] **Klik LOGIN**
- [ ] **Periksa Console Log** - Lihat data yang dikirim
- [ ] **Periksa Laravel Log** - Cari "LOGIN REQUEST DEBUG"
- [ ] **Screenshot log** jika masih error
- [ ] **Laporkan hasil**

## 🚨 Jika Masih Tidak Bisa

### Opsi 1: Test dengan cURL
```bash
curl -X POST http://127.0.0.1:8000/login ^
  -H "Content-Type: application/x-www-form-urlencoded" ^
  -d "_token=test" ^
  -d "login_role=admin" ^
  -d "email=abiyyu123@gmail.com" ^
  -d "kode_perusahaan=UMKM-COE12"
```

### Opsi 2: Disable Validasi Sementara
Edit `LoginController.php`, comment validasi kode_perusahaan:
```php
// $rules['kode_perusahaan'] = 'required|string';
```

Test apakah bisa login. Jika bisa, masalahnya di validasi.

### Opsi 3: Gunakan dd() untuk Debug
Tambahkan di controller sebelum validasi:
```php
dd($request->all());
```

Ini akan menampilkan semua data yang diterima.

## 📂 File untuk Debugging

1. **Login Debug**: `http://127.0.0.1:8000/login-debug`
2. **Laravel Log**: `storage/logs/laravel.log`
3. **Controller**: `app/Http/Controllers/Auth/LoginController.php`
4. **View**: `resources/views/auth/login.blade.php`

## 🎯 Next Steps

1. ✅ Akses `/login-debug`
2. ✅ Test login dengan Admin
3. ✅ Screenshot Console Log di halaman debug
4. ✅ Screenshot Laravel Log (bagian LOGIN REQUEST DEBUG)
5. ✅ Kirim screenshot untuk analisis lebih lanjut

---

**PENTING:** Gunakan `/login-debug` untuk melihat secara real-time apa yang terjadi!

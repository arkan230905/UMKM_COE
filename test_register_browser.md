# Test Registrasi di Browser

## ✅ PERBAIKAN YANG SUDAH DILAKUKAN:

### 1. JavaScript - Disable Field Company
Field company sekarang akan **disabled** saat role = pelanggan, sehingga tidak akan dikirim ke server.

```javascript
// Saat role BUKAN owner
document.getElementById('company_nama').disabled = true;
document.getElementById('company_alamat').disabled = true;
document.getElementById('company_email').disabled = true;
document.getElementById('company_telepon').disabled = true;
```

### 2. Bahasa Indonesia
File `resources/lang/id/validation.php` sudah dibuat dengan pesan error dalam bahasa Indonesia.

### 3. Config Locale
```php
'locale' => 'id',
'fallback_locale' => 'id',
```

## 🧪 CARA TEST DI BROWSER:

1. **Buka halaman registrasi:**
   ```
   http://127.0.0.1:8000/register
   ```

2. **Pilih role "Pelanggan"**

3. **Isi form:**
   - Nama Lengkap: Test Pelanggan
   - Username: testpelanggan
   - Email: test@pelanggan.com
   - Kata Sandi: password123
   - Konfirmasi Kata Sandi: password123
   - Nomor Telepon: 081234567890
   - ✅ Centang "Saya setuju dengan syarat dan ketentuan"

4. **Klik "Daftar Sekarang"**

## ✅ HASIL YANG DIHARAPKAN:

- ✅ Tidak ada error tentang company fields
- ✅ Registrasi berhasil
- ✅ Redirect ke `/pelanggan/dashboard`
- ✅ Jika ada error, pesan dalam bahasa Indonesia

## 🔍 JIKA MASIH ERROR:

1. **Buka Developer Tools (F12)**
2. **Tab Network**
3. **Submit form**
4. **Klik request "register"**
5. **Lihat "Payload" atau "Form Data"**
6. **Pastikan TIDAK ADA field:**
   - company_nama
   - company_alamat
   - company_email
   - company_telepon
   - kode_perusahaan

## 📝 CATATAN:

Jika field company masih terkirim, kemungkinan:
1. Browser cache belum clear (tekan Ctrl+Shift+R untuk hard refresh)
2. Ada JavaScript error (cek Console di Developer Tools)
3. Form di-submit sebelum JavaScript selesai disable field

**Solusi:** Hard refresh browser dengan Ctrl+Shift+R atau Ctrl+F5

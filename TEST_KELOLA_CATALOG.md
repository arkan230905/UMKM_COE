# Test Manual untuk Kelola Catalog

## Persiapan
1. ✅ Pastikan server Laravel berjalan: `php artisan serve`
2. ✅ Pastikan database terkoneksi
3. ✅ Pastikan sudah login sebagai user dengan perusahaan_id

## Test Case 1: Upload Foto Cover

### Langkah:
1. Buka browser ke `/kelola-catalog`
2. Scroll ke section "Cover Section"
3. Klik area preview foto atau tombol "Upload Foto"
4. Pilih file gambar (contoh: logo.jpg, max 5MB)
5. Tunggu proses upload

### Expected Result:
- ✅ Loading spinner muncul
- ✅ Notifikasi "Foto cover berhasil diupload dan tersimpan" muncul
- ✅ Preview foto muncul di area preview
- ✅ Tombol "Hapus" muncul
- ✅ File tersimpan di `storage/app/public/company-photos/`
- ✅ Database tabel `perusahaan` field `foto` terupdate

### Cara Verifikasi:
```bash
# Cek file di storage
ls -la storage/app/public/company-photos/

# Cek database
php artisan tinker
>>> $company = App\Models\Perusahaan::find(1);
>>> $company->foto;
```

---

## Test Case 2: Upload Foto Anggota Tim

### Langkah:
1. Scroll ke section "Team Section"
2. Klik area foto anggota pertama atau tombol kamera
3. Pilih file gambar (contoh: person1.jpg, max 5MB)
4. Tunggu proses upload
5. Ulangi untuk anggota tim kedua

### Expected Result:
- ✅ Loading spinner muncul
- ✅ Notifikasi "Foto anggota tim berhasil diupload dan tersimpan" muncul
- ✅ Preview foto muncul di area foto anggota
- ✅ File tersimpan di `storage/app/public/team-photos/`

### Cara Verifikasi:
```bash
# Cek file di storage
ls -la storage/app/public/team-photos/
```

---

## Test Case 3: Simpan Semua Data

### Langkah:
1. Isi semua field di form:
   - Nama Perusahaan: "PT Test Company"
   - Tagline: "Your Trusted Partner"
   - Deskripsi: "Lorem ipsum dolor sit amet..."
   - Nama Anggota 1: "John Doe"
   - Jabatan Anggota 1: "CEO"
   - Deskripsi Anggota 1: "Experienced leader..."
2. Scroll ke bawah
3. Klik tombol "Update Semua Data"
4. Tunggu proses penyimpanan

### Expected Result:
- ✅ Loading spinner muncul di tombol
- ✅ Notifikasi "Semua data catalog berhasil tersimpan!" muncul
- ✅ Dialog dengan opsi "OK" dan "Lihat Catalog"
- ✅ Data tersimpan di tabel `catalog_sections`
- ✅ Data perusahaan terupdate di tabel `perusahaan`

### Cara Verifikasi:
```bash
# Cek database
php artisan tinker
>>> $company = App\Models\Perusahaan::find(1);
>>> $company->nama;
>>> $company->catalog_description;
>>> $sections = $company->catalogSections;
>>> $sections->count();
>>> $sections->first()->content;
```

---

## Test Case 4: Validasi File

### Test 4.1: File Terlalu Besar
**Langkah:**
1. Coba upload file > 5MB

**Expected Result:**
- ✅ Notifikasi error "Ukuran file maksimal 5MB"
- ✅ Upload dibatalkan

### Test 4.2: Format File Salah
**Langkah:**
1. Coba upload file PDF atau DOCX

**Expected Result:**
- ✅ Notifikasi error "File harus berupa gambar"
- ✅ Upload dibatalkan

---

## Test Case 5: Error Handling

### Test 5.1: Upload Tanpa Login
**Langkah:**
1. Logout dari sistem
2. Coba akses `/kelola-catalog`

**Expected Result:**
- ✅ Redirect ke halaman login

### Test 5.2: Upload Tanpa Perusahaan
**Langkah:**
1. Login dengan user yang tidak punya perusahaan_id
2. Coba upload foto

**Expected Result:**
- ✅ Notifikasi error "Perusahaan tidak ditemukan"

---

## Test Case 6: Multiple Team Members

### Langkah:
1. Klik tombol "Tambah Anggota" di section Team
2. Isi data anggota baru
3. Upload foto untuk anggota baru
4. Klik "Update Semua Data"

### Expected Result:
- ✅ Anggota baru muncul di form
- ✅ Foto anggota baru terupload
- ✅ Semua anggota tersimpan di database

---

## Test Case 7: Hapus Foto Cover

### Langkah:
1. Upload foto cover
2. Klik tombol "Hapus"

### Expected Result:
- ✅ Preview foto hilang
- ✅ Kembali ke state awal (no-photo)
- ✅ Tombol "Hapus" hilang

---

## Test Case 8: Preview Catalog

### Langkah:
1. Setelah save semua data
2. Klik tombol "Lihat Catalog" di dialog sukses
3. Atau klik tombol "Preview Catalog" di header

### Expected Result:
- ✅ Halaman catalog terbuka di tab baru
- ✅ Foto cover muncul
- ✅ Data perusahaan muncul
- ✅ Foto anggota tim muncul
- ✅ Produk muncul (jika ada)

---

## Checklist Akhir

### Functionality
- [ ] Upload foto cover berfungsi
- [ ] Upload foto team berfungsi
- [ ] Preview foto muncul
- [ ] Validasi file berfungsi
- [ ] Error handling berfungsi
- [ ] Save all data berfungsi
- [ ] Data tersimpan di database
- [ ] Foto dapat diakses via URL

### UI/UX
- [ ] Loading state muncul
- [ ] Notifikasi sukses muncul
- [ ] Notifikasi error muncul
- [ ] Button disabled saat loading
- [ ] Preview foto responsive
- [ ] Form responsive di mobile

### Performance
- [ ] Upload cepat (< 3 detik untuk file 2MB)
- [ ] Save data cepat (< 2 detik)
- [ ] No memory leak
- [ ] No console errors

### Security
- [ ] CSRF token valid
- [ ] File validation berfungsi
- [ ] User authentication checked
- [ ] File path sanitized
- [ ] SQL injection prevented (using Eloquent)

---

## Browser Testing

Test di berbagai browser:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Edge (latest)
- [ ] Safari (latest)
- [ ] Mobile Chrome
- [ ] Mobile Safari

---

## Hasil Test

**Tanggal Test**: _________________
**Tester**: _________________
**Browser**: _________________
**OS**: _________________

**Status**: 
- [ ] PASS - Semua test berhasil
- [ ] FAIL - Ada test yang gagal (sebutkan di bawah)

**Catatan**:
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

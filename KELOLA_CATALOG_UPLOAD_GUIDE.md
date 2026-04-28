# Panduan Upload Foto di Kelola Catalog

## Fitur yang Sudah Diperbaiki

### 1. Upload Foto Cover Perusahaan
- **Lokasi**: Section "Cover Section" di halaman Kelola Catalog
- **Cara Kerja**:
  1. Klik area preview foto atau tombol "Upload Foto"
  2. Pilih file gambar (JPG, PNG, GIF, max 5MB)
  3. Foto akan langsung diupload ke server
  4. Preview foto akan muncul setelah upload berhasil
  5. Foto tersimpan otomatis di database (field `foto` di tabel `perusahaan`)

### 2. Upload Foto Anggota Tim
- **Lokasi**: Section "Team Section" di halaman Kelola Catalog
- **Cara Kerja**:
  1. Klik area foto anggota tim atau tombol kamera
  2. Pilih file gambar (JPG, PNG, GIF, max 5MB)
  3. Foto akan langsung diupload ke server
  4. Preview foto akan muncul setelah upload berhasil
  5. Foto tersimpan dan akan disertakan saat klik "Update Semua Data"

### 3. Simpan Semua Data
- **Tombol**: "Update Semua Data" di bagian bawah halaman
- **Fungsi**: Menyimpan semua inputan termasuk:
  - Nama perusahaan, tagline, deskripsi
  - Data tim (nama, jabatan, deskripsi, foto)
  - Judul section produk
  - Lokasi, alamat, telepon, email, maps link

## Perubahan Teknis

### Controller (KelolaCatalogController.php)
1. **uploadCoverPhoto()**: 
   - Validasi file (image, max 5MB)
   - Hapus foto lama jika ada
   - Simpan ke `storage/app/public/company-photos/`
   - Update field `foto` di tabel `perusahaan`
   - Return JSON dengan URL foto

2. **uploadTeamPhoto()**:
   - Validasi file (image, max 5MB)
   - Simpan ke `storage/app/public/team-photos/`
   - Return JSON dengan URL foto
   - Foto disimpan sementara, akan digunakan saat save all

3. **saveSections()**:
   - Menyimpan semua section ke tabel `catalog_sections`
   - Update data perusahaan (nama, deskripsi, kontak)
   - Menggunakan transaction untuk keamanan data

### View (kelola-catalog/index.blade.php)
1. **JavaScript Functions**:
   - `triggerCoverPhotoUpload()`: Trigger file input untuk cover
   - `handleCoverPhotoChange()`: Handle upload cover photo
   - `triggerMemberPhotoUpload()`: Trigger file input untuk team member
   - `handleMemberPhotoChange()`: Handle upload team photo
   - Tracking foto yang sudah diupload dengan data attributes

2. **AJAX Upload**:
   - Upload langsung saat file dipilih
   - Tampilkan loading state
   - Tampilkan preview setelah berhasil
   - Error handling dengan SweetAlert2

### Routes (web.php)
```php
Route::post('/builder/upload-cover-photo', [KelolaCatalogController::class, 'uploadCoverPhoto'])
    ->name('builder.upload-cover-photo');
Route::post('/builder/upload-team-photo', [KelolaCatalogController::class, 'uploadTeamPhoto'])
    ->name('builder.upload-team-photo');
Route::post('/builder/save', [KelolaCatalogController::class, 'saveSections'])
    ->name('builder.save');
```

## Folder Storage
- `storage/app/public/company-photos/` - Foto cover perusahaan
- `storage/app/public/team-photos/` - Foto anggota tim
- Akses via: `public/storage/` (symbolic link)

## Testing Checklist
- [ ] Upload foto cover berhasil
- [ ] Preview foto cover muncul
- [ ] Upload foto anggota tim berhasil
- [ ] Preview foto anggota tim muncul
- [ ] Tombol "Update Semua Data" menyimpan semua inputan
- [ ] Data tersimpan di database dengan benar
- [ ] Foto dapat diakses via URL public

## Troubleshooting

### Foto tidak muncul setelah upload
1. Pastikan symbolic link storage sudah dibuat: `php artisan storage:link`
2. Cek permission folder storage: `chmod -R 775 storage`
3. Cek log Laravel: `storage/logs/laravel.log`

### Upload gagal
1. Cek ukuran file (max 5MB)
2. Cek format file (hanya JPG, PNG, GIF)
3. Cek permission folder storage
4. Cek log browser console untuk error JavaScript

### Data tidak tersimpan
1. Cek log Laravel untuk error database
2. Pastikan tabel `catalog_sections` dan `perusahaan` ada
3. Cek CSRF token valid
4. Cek network tab di browser untuk response error

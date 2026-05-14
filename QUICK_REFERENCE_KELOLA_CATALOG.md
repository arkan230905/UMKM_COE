# Quick Reference - Kelola Catalog Upload Foto

## 🚀 Quick Start

```bash
# 1. Pastikan storage link ada
php artisan storage:link

# 2. Buat folder yang diperlukan (sudah otomatis dibuat)
# storage/app/public/company-photos/
# storage/app/public/team-photos/

# 3. Set permission (jika di Linux/Mac)
chmod -R 775 storage
chmod -R 775 public/storage

# 4. Test route
php artisan route:list | grep "kelola-catalog.builder"

# 5. Jalankan server
php artisan serve
```

## 📍 Endpoint API

### Upload Cover Photo
```
POST /kelola-catalog/builder/upload-cover-photo
Content-Type: multipart/form-data

Parameters:
- foto: File (required, image, max:5MB)
- _token: CSRF token (required)

Response:
{
  "success": true,
  "photo_url": "http://localhost/storage/company-photos/cover_1_1234567890.jpg",
  "photo_path": "company-photos/cover_1_1234567890.jpg",
  "message": "Foto cover berhasil diupload dan tersimpan."
}
```

### Upload Team Photo
```
POST /kelola-catalog/builder/upload-team-photo
Content-Type: multipart/form-data

Parameters:
- photo: File (required, image, max:5MB)
- _token: CSRF token (required)

Response:
{
  "success": true,
  "photo_url": "http://localhost/storage/team-photos/team_1_1234567890_abc123.jpg",
  "photo_path": "team-photos/team_1_1234567890_abc123.jpg",
  "message": "Foto anggota tim berhasil diupload."
}
```

### Save All Sections
```
POST /kelola-catalog/builder/save
Content-Type: application/x-www-form-urlencoded

Parameters:
- sections[cover][company_name]: string
- sections[cover][company_tagline]: string
- sections[cover][company_description]: string
- sections[team][title]: string
- sections[team][description]: string
- sections[team][members][0][name]: string
- sections[team][members][0][position]: string
- sections[team][members][0][description]: string
- sections[team][members][0][photo]: string (URL)
- sections[products][title]: string
- sections[location][title]: string
- sections[location][name]: string
- sections[location][address]: string
- sections[location][phone]: string
- sections[location][email]: string
- sections[location][maps_link]: string
- _token: CSRF token (required)

Response:
{
  "success": true,
  "message": "Semua data catalog berhasil tersimpan!"
}
```

## 🔧 JavaScript Functions

### Trigger Upload
```javascript
// Cover photo
triggerCoverPhotoUpload();

// Team photo
triggerMemberPhotoUpload(element);
```

### Handle Upload
```javascript
// Cover photo
handleCoverPhotoChange(inputElement);

// Team photo
handleMemberPhotoChange(inputElement);
```

### Remove Photo
```javascript
removeCoverPhoto();
```

## 💾 Database

### Tabel: perusahaan
```sql
UPDATE perusahaan 
SET foto = 'company-photos/cover_1_1234567890.jpg'
WHERE id = 1;
```

### Tabel: catalog_sections
```sql
INSERT INTO catalog_sections (
  perusahaan_id, 
  section_type, 
  title, 
  content, 
  order, 
  is_active
) VALUES (
  1,
  'team',
  'THE TEAM.',
  '{"title":"THE TEAM.","description":"...","members":[...]}',
  2,
  1
);
```

## 🐛 Debug Commands

### Check Storage Link
```bash
ls -la public/storage
# Should show: storage -> ../storage/app/public
```

### Check Uploaded Files
```bash
# Cover photos
ls -la storage/app/public/company-photos/

# Team photos
ls -la storage/app/public/team-photos/
```

### Check Database
```bash
php artisan tinker

# Check company foto
>>> $company = App\Models\Perusahaan::find(1);
>>> $company->foto;

# Check catalog sections
>>> $sections = App\Models\CatalogSection::where('perusahaan_id', 1)->get();
>>> $sections->count();
>>> $sections->pluck('section_type');
>>> $sections->where('section_type', 'team')->first()->content;
```

### Check Logs
```bash
# Laravel log
tail -f storage/logs/laravel.log

# Filter upload errors
grep "Upload" storage/logs/laravel.log
grep "Error uploading" storage/logs/laravel.log
```

## 🔍 Common Issues

### Issue: Upload returns 404
**Solution:**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Check route exists
php artisan route:list | grep upload-cover-photo
```

### Issue: Upload returns 419 (CSRF)
**Solution:**
```javascript
// Make sure CSRF token is included
formData.append('_token', '{{ csrf_token() }}');

// Or check meta tag
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue: File not found after upload
**Solution:**
```bash
# Recreate storage link
php artisan storage:link

# Check file exists
ls -la storage/app/public/company-photos/
```

### Issue: Permission denied
**Solution:**
```bash
# Linux/Mac
chmod -R 775 storage
chown -R www-data:www-data storage

# Windows
# Right-click folder > Properties > Security > Edit
```

## 📦 File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── KelolaCatalogController.php
│           ├── uploadCoverPhoto()
│           ├── uploadTeamPhoto()
│           └── saveSections()
├── Models/
│   ├── Perusahaan.php
│   └── CatalogSection.php

resources/
└── views/
    └── kelola-catalog/
        └── index.blade.php
            ├── JavaScript functions
            └── AJAX handlers

routes/
└── web.php
    └── kelola-catalog.builder.* routes

storage/
└── app/
    └── public/
        ├── company-photos/
        └── team-photos/

public/
└── storage/ -> ../storage/app/public
```

## 🎨 CSS Classes

```css
.cover-photo-upload          /* Container for cover photo */
.member-photo-upload         /* Container for member photo */
.photo-preview               /* Preview area */
.no-photo                    /* Empty state */
.loading-photo               /* Loading state */
.team-member-item            /* Team member card */
```

## 🧪 Test with cURL

### Upload Cover Photo
```bash
curl -X POST http://localhost:8000/kelola-catalog/builder/upload-cover-photo \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -F "foto=@/path/to/image.jpg" \
  -b "laravel_session=your-session-cookie"
```

### Upload Team Photo
```bash
curl -X POST http://localhost:8000/kelola-catalog/builder/upload-team-photo \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -F "photo=@/path/to/image.jpg" \
  -b "laravel_session=your-session-cookie"
```

## 📊 Performance Tips

1. **Optimize Images Before Upload**
   - Resize to max 1920x1080
   - Compress to 80-85% quality
   - Use WebP format if possible

2. **Lazy Load Images**
   - Use `loading="lazy"` attribute
   - Implement progressive image loading

3. **Cache Control**
   - Set proper cache headers
   - Use CDN for static assets

4. **Database Optimization**
   - Index `perusahaan_id` in catalog_sections
   - Use eager loading for relationships

## 🔐 Security Checklist

- [x] CSRF protection enabled
- [x] File type validation (client + server)
- [x] File size validation (client + server)
- [x] User authentication required
- [x] File path sanitization
- [x] SQL injection prevention (Eloquent)
- [x] XSS prevention (Blade escaping)
- [x] Directory traversal prevention

## 📝 Code Snippets

### Get Uploaded Photo URL
```javascript
const photoUrl = document.querySelector('#coverPhotoPreview img')?.src;
```

### Check if Photo Uploaded
```javascript
const hasPhoto = document.querySelector('#coverPhotoPreview img') !== null;
```

### Get All Team Photos
```javascript
const teamPhotos = [];
document.querySelectorAll('.team-member-item').forEach(item => {
  const photoUrl = item.querySelector('.photo-preview img')?.src;
  if (photoUrl) teamPhotos.push(photoUrl);
});
```

## 🎯 Next Steps

1. Add image cropping feature
2. Add multiple file upload
3. Add drag & drop upload
4. Add image filters/effects
5. Add photo gallery view
6. Add photo reordering
7. Add photo captions
8. Add photo metadata (alt text, title)

---

**Last Updated**: 2026-04-28
**Version**: 1.0.0
**Status**: Production Ready ✅

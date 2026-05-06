# 🚀 QUICK REFERENCE GUIDE

## 📌 Storage Helper Usage

### Display Images in Blade Templates

```blade
<!-- ✅ CORRECT - Use storage_url() helper -->
<img src="{{ storage_url($produk->foto) }}" alt="{{ $produk->nama_produk }}">

<!-- ❌ WRONG - Don't use these -->
<img src="{{ asset('storage/' . $produk->foto) }}">
<img src="{{ Storage::url($produk->foto) }}">
```

### Check if File Exists

```blade
@if(storage_exists($produk->foto))
    <img src="{{ storage_url($produk->foto) }}">
@else
    <img src="/images/no-image.png">
@endif
```

### In Controllers

```php
// Get storage URL
$url = storage_url($produk->foto);

// Check if file exists
if (storage_exists($produk->foto)) {
    // File exists
}

// Get full file path
$path = storage_path('app/public/' . $produk->foto);
```

---

## 🔒 Multi-Tenant Security

### Always Filter by user_id

```php
// ✅ CORRECT - Filter by user_id
$produks = Produk::where('user_id', auth()->id())->get();
$produk = Produk::where('user_id', auth()->id())->findOrFail($id);

// ❌ WRONG - No user_id filter
$produks = Produk::all();
$produk = Produk::findOrFail($id);
```

### Model Setup

```php
class YourModel extends Model
{
    // 1. Add user_id to fillable
    protected $fillable = ['user_id', 'name', ...];
    
    // 2. Auto-fill user_id on create
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }
}
```

---

## 🧪 Testing Commands

```bash
# Test storage route
php artisan storage:test

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart server (if needed)
# Press Ctrl+C to stop, then:
php artisan serve
```

---

## 🐛 Troubleshooting

### Foto tidak tampil?

1. **Clear cache**:
   ```bash
   php artisan view:clear
   ```

2. **Check file exists**:
   ```bash
   ls storage/app/public/produk/
   ```

3. **Test storage route**:
   ```bash
   curl http://127.0.0.1:8000/storage/produk/filename.jpg
   ```

4. **Check in browser console** (F12):
   - Look for 404 or 403 errors
   - Check Network tab for failed requests

### Error 403 Forbidden?

- Make sure `config/filesystems.php` has `'serve' => false`
- Make sure `routes/storage.php` exists
- Restart server: `php artisan serve`

### Error "Call to undefined function storage_url"?

- Make sure `app/Helpers/helpers.php` exists
- Make sure it's loaded in `composer.json`:
  ```json
  "autoload": {
      "files": [
          "app/Helpers/helpers.php"
      ]
  }
  ```
- Run: `composer dump-autoload`

---

## 📁 File Locations

### Storage Files
- **Uploaded files**: `storage/app/public/`
- **Product photos**: `storage/app/public/produk/`
- **Bukti faktur**: `storage/app/public/bukti_faktur/`
- **Catalog photos**: `storage/app/public/catalog/`

### Helper Files
- **Storage helper**: `app/Helpers/helpers.php`
- **Storage class**: `app/Helpers/StorageHelper.php`
- **Storage route**: `routes/storage.php`
- **Test command**: `app/Console/Commands/TestStorageAccess.php`

### Configuration
- **Filesystem config**: `config/filesystems.php`

---

## 🔗 Quick Links

### Documentation
- `SUMMARY_ALL_FIXES.md` - Complete summary of all fixes
- `FOTO_PRODUK_FIX_COMPLETE.md` - Detailed foto fix documentation
- `TEST_FOTO_DISPLAY.md` - Testing guide
- `DATABASE_PENJUALAN_STRUCTURE.md` - Database documentation

### Key Files
- `routes/storage.php` - Custom storage route
- `app/Helpers/helpers.php` - Helper functions
- `config/filesystems.php` - Filesystem configuration

---

## ✅ Checklist for New Features

When adding new features that involve file uploads:

- [ ] Use `storage_url()` to display files in views
- [ ] Use `storage_exists()` to check if file exists
- [ ] Store files in `storage/app/public/` directory
- [ ] Save only the relative path in database (e.g., `produk/filename.jpg`)
- [ ] Add `user_id` filter for multi-tenant data
- [ ] Add `user_id` to model fillable
- [ ] Add `user_id` auto-fill in model boot method
- [ ] Test file upload and display
- [ ] Clear view cache after changes

---

**Last Updated**: May 6, 2026  
**Status**: ✅ Ready to Use

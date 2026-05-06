# Storage Quick Guide

## 🚀 Quick Start

### Untuk Developer Baru

Jika Anda baru join project ini dan mengalami error 403 saat akses file storage:

```bash
# 1. Clear cache
php artisan optimize:clear

# 2. Restart server
php artisan serve

# 3. Test
# Buka: http://127.0.0.1:8000/transaksi/pembelian/1
# Klik tombol "Lihat Bukti"
```

## 📝 Cara Menggunakan di View

### ❌ JANGAN Gunakan Ini:
```blade
<!-- JANGAN GUNAKAN asset() untuk storage -->
<img src="{{ asset('storage/' . $path) }}">
<a href="{{ asset('storage/' . $file) }}">Download</a>
```

### ✅ GUNAKAN Ini:

#### Opsi 1: Menggunakan url() helper (Recommended)
```blade
<img src="{{ url('/storage/' . $path) }}">
<a href="{{ url('/storage/' . $file) }}" target="_blank">Lihat</a>
```

#### Opsi 2: Menggunakan storage_url() helper (Lebih Bersih)
```blade
<img src="{{ storage_url($path) }}">
<a href="{{ storage_url($file) }}" target="_blank">Lihat</a>
```

## 🎯 Contoh Penggunaan

### Bukti Faktur Pembelian
```blade
@if($pembelian->bukti_faktur)
    <a href="{{ storage_url($pembelian->bukti_faktur) }}" target="_blank">
        <i class="bi bi-file-earmark-text"></i> Lihat Bukti
    </a>
@endif
```

### Bukti Pembayaran Penjualan
```blade
@foreach($buktiPembayaran as $bukti)
    <img src="{{ storage_url($bukti->file_path) }}" 
         class="img-fluid" 
         alt="Bukti Pembayaran">
    
    <a href="{{ storage_url($bukti->file_path) }}" 
       target="_blank" 
       class="btn btn-primary">
        Lihat Bukti
    </a>
@endforeach
```

### Foto Produk
```blade
@if($produk->foto)
    <img src="{{ storage_url($produk->foto) }}" 
         alt="{{ $produk->nama_produk }}">
@endif
```

### Check File Exists
```blade
@if(storage_exists($pembelian->bukti_faktur))
    <a href="{{ storage_url($pembelian->bukti_faktur) }}">Lihat</a>
@else
    <span class="text-muted">Tidak ada bukti</span>
@endif
```

## 🔧 Cara Upload File

### Di Controller
```php
// Upload file
if ($request->hasFile('bukti_faktur')) {
    $file = $request->file('bukti_faktur');
    $userId = auth()->id();
    
    // Store dengan path: storage/app/public/bukti_faktur/{user_id}/
    $path = $file->store("bukti_faktur/{$userId}", 'public');
    
    // Save path ke database (tanpa 'storage/app/public/')
    $pembelian->bukti_faktur = $path;
    $pembelian->save();
}
```

### Di View (Form Upload)
```blade
<form method="POST" enctype="multipart/form-data">
    @csrf
    
    <input type="file" 
           name="bukti_faktur" 
           accept="image/*,application/pdf" 
           required>
    
    @error('bukti_faktur')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</form>
```

## 🛡️ Security

File types yang diperbolehkan:
- Images: png, jpg, jpeg, gif
- Documents: pdf, doc, docx, xls, xlsx

Untuk menambah file type baru, edit `routes/storage.php`:
```php
$allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'webp'];
```

## 🐛 Troubleshooting

### Error 403 Forbidden
```bash
# Clear semua cache
php artisan optimize:clear

# Restart server
# Ctrl+C untuk stop, lalu:
php artisan serve
```

### File tidak muncul (404)
```bash
# Check file exists
ls storage/app/public/bukti_faktur/1/

# Check database
php artisan tinker
>>> \App\Models\Pembelian::find(1)->bukti_faktur
```

### Gambar tidak load
1. Buka browser console (F12)
2. Check tab Network untuk error
3. Pastikan URL benar: `/storage/bukti_faktur/1/file.png`
4. Pastikan file extension ada di allowed list

## 📚 Helper Functions

### storage_url($path)
Generate URL untuk file storage
```php
storage_url('bukti_faktur/1/file.png')
// Returns: http://localhost:8000/storage/bukti_faktur/1/file.png
```

### storage_exists($path)
Check apakah file exists
```php
if (storage_exists($path)) {
    // File exists
}
```

### StorageHelper Class
Untuk penggunaan advanced:
```php
use App\Helpers\StorageHelper;

// Get URL
$url = StorageHelper::url($path);

// Check exists
$exists = StorageHelper::exists($path);

// Get full path
$fullPath = StorageHelper::path($path);

// Get file size
$size = StorageHelper::size($path);

// Get mime type
$mime = StorageHelper::mimeType($path);

// Check if image
$isImage = StorageHelper::isImage($path);

// Check extension allowed
$allowed = StorageHelper::isAllowedExtension('file.png');
```

## 📞 Need Help?

1. Baca dokumentasi lengkap: `STORAGE_FIX_DOCUMENTATION.md`
2. Run test: `php test_storage_complete.php`
3. Check Laravel logs: `storage/logs/laravel.log`

## ✅ Checklist untuk Feature Baru

Saat membuat feature baru yang menggunakan file upload:

- [ ] Upload file ke `storage/app/public/folder_name/`
- [ ] Simpan path relatif ke database (tanpa `storage/app/public/`)
- [ ] Di view, gunakan `storage_url()` atau `url('/storage/' . $path)`
- [ ] Validasi file type di controller
- [ ] Test upload dan display
- [ ] Test download (jika perlu)

## 🎓 Best Practices

1. **Selalu gunakan `storage_url()` atau `url('/storage/')`** - Jangan gunakan `asset('storage/')`
2. **Simpan path relatif di database** - Format: `bukti_faktur/1/file.png` (tanpa `storage/app/public/`)
3. **Validasi file type** - Gunakan validation rules di controller
4. **Check file exists** - Sebelum display, check dengan `storage_exists()`
5. **Gunakan target="_blank"** - Untuk link view file, buka di tab baru
6. **Handle missing files** - Tampilkan placeholder atau pesan jika file tidak ada

---

**Last Updated:** 2026-05-06  
**Version:** 1.0  
**Status:** ✅ Production Ready

# Fitur Kelola E-Catalog

## Deskripsi
Fitur kelola e-catalog memungkinkan setiap user (termasuk admin) untuk mengelola catalog produk perusahaan mereka sendiri. Setiap perusahaan memiliki catalog yang terpisah dan dapat di-customize sesuai kebutuhan.

## Akses
- **URL**: `/kelola-catalog`
- **Role yang bisa akses**: Semua user yang sudah login (termasuk admin, owner, pegawai)
- **Middleware**: `auth`

## Fitur Utama

### 1. Kelola Produk di Catalog
**URL**: `/kelola-catalog`

**Fitur**:
- Melihat semua produk
- Toggle visibility produk di catalog (show/hide)
- Bulk update visibility (pilih banyak produk sekaligus)
- Update deskripsi catalog per produk
- Filter produk berdasarkan:
  - Stok (tersedia, habis, stok rendah)
  - Harga (< 10k, 10k-50k, 50k-100k, > 100k)
- Search produk

**Cara Kerja**:
- Setiap user hanya bisa mengelola produk dari perusahaan mereka sendiri
- Data diambil berdasarkan `perusahaan_id` dari user yang login
- Produk yang di-hide tidak akan muncul di catalog publik

### 2. Preview Catalog
**URL**: `/kelola-catalog/preview`

**Fitur**:
- Melihat tampilan catalog seperti yang dilihat customer
- Hanya menampilkan produk yang visible (`show_in_catalog = true`)
- Inline editing untuk update deskripsi catalog
- Search produk

### 3. Pengaturan Catalog
**URL**: `/kelola-catalog/settings`

**Fitur**:
- Update informasi perusahaan:
  - Nama perusahaan
  - Alamat
  - Email
  - Telepon
  - Logo perusahaan
  - Deskripsi catalog
- Update pengaturan peta:
  - Link Google Maps
  - Latitude & Longitude
  - Embed map di catalog

### 4. Kelola Foto Catalog
**URL**: `/kelola-catalog/photos`

**Fitur**:
- Upload foto untuk banner/slider catalog
- Edit judul dan deskripsi foto
- Toggle active/inactive foto
- Reorder foto (drag & drop)
- Delete foto
- Kompresi otomatis untuk foto besar (max 8MB)

## Database Schema

### Table: `produks`
Kolom tambahan untuk catalog:
- `show_in_catalog` (boolean): Apakah produk ditampilkan di catalog
- `deskripsi_catalog` (text): Deskripsi khusus untuk catalog (bisa berbeda dari deskripsi produk)

### Table: `perusahaans`
Kolom tambahan untuk catalog:
- `catalog_description` (text): Deskripsi perusahaan untuk catalog
- `maps_link` (string): Link Google Maps
- `latitude` (decimal): Koordinat latitude
- `longitude` (decimal): Koordinat longitude

### Table: `catalog_photos`
Struktur:
- `id` (bigint): Primary key
- `perusahaan_id` (bigint): Foreign key ke perusahaans
- `judul` (string): Judul foto
- `foto` (string): Path foto
- `deskripsi` (text): Deskripsi foto
- `urutan` (integer): Urutan tampilan
- `is_active` (boolean): Status aktif/tidak
- `created_at`, `updated_at`

## Model

### CatalogPhoto Model
**Path**: `app/Models/CatalogPhoto.php`

**Relationships**:
- `belongsTo(Perusahaan::class)`: Setiap foto milik satu perusahaan

**Scopes**:
- `active()`: Hanya foto yang aktif

## Controller

### KelolaCatalogController
**Path**: `app/Http/Controllers/KelolaCatalogController.php`

**Methods**:
1. `index()`: Halaman utama kelola catalog
2. `preview()`: Preview catalog
3. `settings()`: Halaman pengaturan
4. `updateSettings()`: Update pengaturan perusahaan
5. `updateCatalogSettings()`: Update pengaturan catalog & peta
6. `updateCompanyInfo()`: Update informasi perusahaan
7. `toggleVisibility($id)`: Toggle visibility satu produk
8. `bulkUpdateVisibility()`: Toggle visibility banyak produk
9. `updateProductCatalog($id)`: Update info catalog produk
10. `photos()`: Halaman kelola foto
11. `storePhoto()`: Upload foto baru
12. `updatePhoto($id)`: Update info foto
13. `deletePhoto($id)`: Hapus foto
14. `reorderPhotos()`: Ubah urutan foto

## Views

### Lokasi: `resources/views/kelola-catalog/`

**Files**:
1. `index.blade.php`: Halaman utama kelola produk
2. `preview.blade.php`: Preview catalog
3. `settings.blade.php`: Pengaturan catalog
4. `photos.blade.php`: Kelola foto catalog

## Keamanan & Isolasi Data

### Per-Company Isolation
Setiap user hanya bisa mengakses data perusahaan mereka sendiri:

```php
$user = Auth::user();
$company = Perusahaan::find($user->perusahaan_id);
```

Semua query menggunakan `perusahaan_id` dari user yang login, sehingga:
- User perusahaan A tidak bisa melihat/edit data perusahaan B
- Admin dari perusahaan A hanya bisa mengelola catalog perusahaan A
- Data catalog setiap perusahaan terpisah dan aman

### Validasi
- Upload foto: max 8MB, format: jpeg, png, jpg, gif, webp
- Kompresi otomatis untuk foto > 1MB
- Resize otomatis ke max 1920x1080 (HD)
- Validasi input untuk semua form

## Cara Penggunaan

### Untuk Admin/Owner:

1. **Mengelola Produk di Catalog**:
   - Login ke sistem
   - Klik menu "Kelola Catalog"
   - Toggle switch untuk show/hide produk
   - Atau pilih banyak produk dan klik "Tampilkan di Catalog" / "Sembunyikan dari Catalog"

2. **Mengatur Informasi Perusahaan**:
   - Klik "Pengaturan" di menu kelola catalog
   - Update nama, alamat, kontak, logo
   - Tambahkan deskripsi perusahaan
   - Simpan perubahan

3. **Menambah Foto Banner**:
   - Klik "Kelola Foto" di menu kelola catalog
   - Upload foto (max 8MB)
   - Edit judul dan deskripsi
   - Drag & drop untuk mengubah urutan
   - Toggle active/inactive

4. **Preview Catalog**:
   - Klik "Preview" untuk melihat tampilan catalog
   - Pastikan semua informasi sudah benar
   - Share link catalog ke customer

## Integrasi dengan Fitur Lain

### Produk
- Menggunakan data dari table `produks`
- Menampilkan HPP dari `bom_job_costings`
- Menghitung margin otomatis

### Perusahaan
- Menggunakan data dari table `perusahaans`
- Logo perusahaan untuk branding catalog
- Informasi kontak untuk customer

### User Management
- Setiap user terikat ke satu perusahaan (`perusahaan_id`)
- Multi-company support: setiap perusahaan punya catalog sendiri

## Catatan Penting

1. **Setiap perusahaan punya catalog sendiri**: Data catalog tidak shared antar perusahaan
2. **Admin bisa akses**: Admin dari perusahaan A bisa mengelola catalog perusahaan A
3. **Customizable**: Setiap perusahaan bisa customize tampilan dan konten catalog mereka
4. **Foto otomatis dikompresi**: Untuk menghemat storage dan mempercepat loading
5. **Responsive**: Tampilan catalog responsive untuk mobile dan desktop

## Troubleshooting

### Foto tidak muncul
- Pastikan foto sudah di-upload dengan benar
- Cek status `is_active` = true
- Cek permission folder `storage/app/public/produk`

### Produk tidak muncul di catalog
- Pastikan `show_in_catalog` = true
- Cek di halaman preview apakah produk sudah muncul

### User tidak bisa akses
- Pastikan user sudah login
- Pastikan user punya `perusahaan_id` yang valid
- Cek middleware `auth` di route

## Future Enhancements

1. **Tema Catalog**: Pilihan tema/template untuk catalog
2. **Custom Domain**: Setiap perusahaan bisa punya subdomain sendiri
3. **Analytics**: Tracking views, clicks, dan konversi
4. **SEO**: Meta tags dan sitemap untuk catalog
5. **Export**: Export catalog ke PDF atau print
6. **Social Sharing**: Share catalog ke social media
7. **QR Code**: Generate QR code untuk catalog
8. **Multi-language**: Support bahasa Indonesia dan Inggris

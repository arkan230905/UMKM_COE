# FITUR CATALOG BUILDER - INLINE EDITING

## Deskripsi
Fitur Catalog Builder memungkinkan user untuk mengedit catalog perusahaan secara langsung (inline editing) dengan desain yang modern dan profesional sesuai dengan template yang diminta.

## Struktur Catalog
Catalog terdiri dari 4 section utama:

### 1. COVER SECTION
- **Foto Perusahaan**: Background image dengan overlay
- **Nama Perusahaan**: Judul besar yang dapat diedit
- **Tagline**: Subtitle yang dapat diedit (default: "BRANDING PRODUCT.")
- **Deskripsi**: Paragraf deskripsi perusahaan yang dapat diedit
- **Tombol Explore**: Text yang dapat diedit

### 2. TEAM SECTION
- **Judul Section**: "THE TEAM." (dapat diedit)
- **Deskripsi Team**: Paragraf penjelasan tentang tim
- **Anggota Tim**: 
  - Nama anggota (dapat diedit)
  - Jabatan (dapat diedit)
  - Deskripsi anggota (dapat diedit)
  - Foto anggota (dapat diupload)
  - Tombol hapus anggota
- **Tombol Tambah Anggota**: Untuk menambah anggota tim baru

### 3. PRODUCTS SECTION
- **Judul Section**: "PRODUCT MATERIAL." (dapat diedit)
- **Grid Produk**: Menampilkan produk yang sudah diatur di kelola catalog
- Produk ditampilkan dengan foto, nama, deskripsi, dan harga

### 4. LOCATION SECTION
- **Judul Section**: "LOKASI KAMI." (dapat diedit)
- **Informasi Lokasi**:
  - Nama perusahaan (dapat diedit)
  - Alamat (dapat diedit)
  - Nomor telepon (dapat diedit)
  - Email (dapat diedit)
- **Peta**: Menggunakan Google Maps dari pengaturan perusahaan

## Fitur Editing

### Inline Editing
- Semua text dapat diedit langsung dengan klik
- Hover effect untuk menunjukkan area yang dapat diedit
- Focus effect dengan highlight biru

### Upload Foto Team
- Modal upload untuk foto anggota tim
- Support format: JPG, PNG, GIF
- Maksimal ukuran: 2MB
- Foto disimpan di folder `storage/team-photos/`

### Manajemen Anggota Tim
- Tambah anggota baru dengan tombol "Tambah Anggota"
- Hapus anggota dengan tombol trash (minimal 1 anggota)
- Setiap anggota memiliki foto, nama, jabatan, dan deskripsi

### Tombol Save
- Tombol "Update Semua Data" di bagian bawah
- Loading state dengan text "Menyimpan..."
- Notifikasi sukses menggunakan SweetAlert
- Menyimpan semua perubahan ke database

## Database Structure

### Table: catalog_sections
```sql
- id (bigint, primary key)
- perusahaan_id (bigint, foreign key ke table perusahaan)
- section_type (enum: 'cover', 'team', 'products', 'location', 'custom')
- title (varchar, nullable)
- content (json, nullable) - untuk menyimpan data fleksibel
- image (varchar, nullable)
- order (integer, default 0)
- is_active (boolean, default true)
- created_at, updated_at (timestamps)
```

### Content Structure (JSON)
**Cover Section:**
```json
{
  "company_name": "Nama Perusahaan",
  "company_tagline": "BRANDING PRODUCT.",
  "company_description": "Deskripsi perusahaan...",
  "explore_text": "Explore"
}
```

**Team Section:**
```json
{
  "description": "Deskripsi tentang tim...",
  "members": [
    {
      "name": "Nama Anggota",
      "position": "Jabatan",
      "description": "Deskripsi anggota...",
      "photo": "url/path/to/photo.jpg"
    }
  ]
}
```

**Products Section:**
```json
{
  "show_products": true
}
```

**Location Section:**
```json
{
  "name": "Nama Perusahaan",
  "address": "Alamat lengkap",
  "phone": "Nomor telepon",
  "email": "Email perusahaan"
}
```

## Routes
- `GET /kelola-catalog/builder` - Halaman builder
- `POST /kelola-catalog/builder/save` - Simpan semua sections
- `POST /kelola-catalog/builder/upload-team-photo` - Upload foto team

## Views
- `resources/views/kelola-catalog/builder.blade.php` - Halaman builder dengan inline editing
- `resources/views/catalog/builder-view.blade.php` - Tampilan catalog publik berdasarkan data builder
- `resources/views/catalog/index.blade.php` - Tampilan catalog default (fallback)

## Controller Methods
- `KelolaCatalogController@builder()` - Menampilkan halaman builder
- `KelolaCatalogController@saveSections()` - Menyimpan data sections
- `KelolaCatalogController@uploadTeamPhoto()` - Upload foto team
- `ProdukController@catalog()` - Menampilkan catalog publik (otomatis pilih view berdasarkan data)

## Desain & Styling
- Desain modern dengan typography yang kuat
- Color scheme: Hitam, putih, biru (#007bff)
- Responsive design untuk mobile dan desktop
- Hover effects dan transitions yang smooth
- Grid layout untuk produk
- Professional layout untuk team section

## Cara Penggunaan
1. Masuk ke menu "Kelola Catalog"
2. Klik tombol "Catalog Builder"
3. Edit setiap section dengan klik langsung pada text
4. Upload foto team dengan klik tombol camera
5. Tambah/hapus anggota tim sesuai kebutuhan
6. Klik "Update Semua Data" untuk menyimpan
7. Preview hasil di catalog publik

## Integrasi dengan Sistem Existing
- Menggunakan data perusahaan yang sudah ada
- Menggunakan produk dari kelola catalog existing
- Menggunakan foto catalog yang sudah diupload
- Menggunakan pengaturan maps dari settings
- Backward compatible dengan catalog lama

## Keamanan
- CSRF protection pada semua form
- File upload validation (type, size)
- User authentication required
- Company-specific data isolation
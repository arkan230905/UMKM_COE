# Implementasi Location Section - Catalog

## Ringkasan
Telah berhasil menambahkan **Location Section** yang menampilkan informasi lokasi dan kontak perusahaan di halaman public catalog (`/catalog`).

## Perubahan yang Dilakukan

### 1. File: `resources/views/catalog/index.blade.php`

#### A. Menambahkan Data Location Section (Baris ~578-588)
```php
$locationSection = ($sections && $sections->isNotEmpty()) ? $sections->firstWhere('section_type', 'location') : null;

$locationData = [
    'title'     => 'LOKASI KAMI.',
    'name'      => $company->nama ?? '',
    'address'   => $company->alamat ?? '',
    'phone'     => $company->telepon ?? '',
    'email'     => $company->email ?? '',
    'maps_link' => $company->maps_link ?? '',
];
if ($locationSection && $locationSection->content) $locationData = array_merge($locationData, $locationSection->content);
```

#### B. Menambahkan CSS untuk Location Section (Baris ~399-497)
- `.location-section` - Container utama dengan padding 100px
- `.location-content` - Grid 2 kolom untuk info dan map
- `.contact-item` - Item kontak dengan icon dan text
- `.contact-icon` - Icon bulat dengan background hitam
- `.location-map` - Container untuk Google Maps iframe
- Responsive design untuk mobile (1 kolom)

#### C. Menambahkan HTML Location Section (Setelah Products Section, sebelum CTA)
```html
<!-- LOCATION SECTION -->
@if(!empty($locationData['name']) || !empty($locationData['address']) || !empty($locationData['phone']) || !empty($locationData['email']) || !empty($locationData['maps_link']))
<section class="location-section">
    <div class="container">
        <div class="location-header">
            <h2 class="section-title">{{ $locationData['title'] }}</h2>
            <div class="section-line"></div>
        </div>
        <div class="location-content">
            <div class="location-info">
                <!-- Nama Lokasi -->
                <!-- Alamat dengan icon -->
                <!-- Telepon dengan icon dan link tel: -->
                <!-- Email dengan icon dan link mailto: -->
                <!-- Link Google Maps -->
            </div>
            <div class="location-map">
                <!-- Google Maps iframe embed -->
            </div>
        </div>
    </div>
</section>
@endif
```

## Fitur Location Section

### Informasi yang Ditampilkan:
1. **Judul Section** - Default: "LOKASI KAMI." (dapat diubah di kelola-catalog)
2. **Nama Lokasi** - Nama perusahaan
3. **Alamat** - Alamat lengkap dengan icon map marker
4. **Nomor Telepon** - Dengan icon phone dan clickable link (tel:)
5. **Email** - Dengan icon envelope dan clickable link (mailto:)
6. **Link Google Maps** - Link eksternal "Lihat di Google Maps"
7. **Google Maps Embed** - Peta interaktif di sisi kanan

### Desain:
- **Layout**: 2 kolom (info kiri, map kanan)
- **Icons**: Font Awesome dengan background bulat hitam
- **Typography**: Konsisten dengan section lain (uppercase titles, section line)
- **Responsive**: Otomatis menjadi 1 kolom di mobile
- **Map Height**: 380px desktop, 260px mobile

### Conditional Rendering:
Section hanya muncul jika minimal salah satu field terisi:
- Nama lokasi
- Alamat
- Telepon
- Email
- Maps link

## Cara Menggunakan

### 1. Di Halaman Kelola Catalog (`/kelola-catalog`)
1. Scroll ke bagian **"Location Section"**
2. Isi form:
   - **Judul Section**: Judul yang akan ditampilkan (default: "LOKASI KAMI.")
   - **Nama Lokasi**: Nama perusahaan/lokasi
   - **Alamat**: Alamat lengkap
   - **Nomor Telepon**: Format bebas (contoh: 0812-3456-7890)
   - **Email**: Email perusahaan
   - **Link Google Maps**: Paste link embed dari Google Maps
3. Klik tombol **"Update Semua Data"**

### 2. Mendapatkan Link Google Maps Embed
1. Buka [Google Maps](https://maps.google.com)
2. Cari lokasi perusahaan Anda
3. Klik tombol **"Share"** atau **"Bagikan"**
4. Pilih tab **"Embed a map"** atau **"Sematkan peta"**
5. Copy link yang ada di dalam tag `<iframe src="...">` 
6. Paste ke field **"Link Google Maps"** di kelola-catalog

Contoh link embed:
```
https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d...
```

### 3. Preview di Catalog Public (`/catalog`)
- Buka halaman `/catalog` atau klik tombol **"Preview Catalog"**
- Location section akan muncul setelah Products section
- Semua informasi yang diisi akan ditampilkan dengan desain yang konsisten

## Status Implementasi

### ✅ Sudah Selesai:
1. **Kelola Catalog** - Form editor location section sudah ada dan berfungsi
2. **Backend** - Controller `saveSections()` sudah menyimpan data location ke database
3. **Database** - Tabel `catalog_sections` sudah support section_type='location'
4. **Public Catalog** - Location section sudah ditampilkan dengan desain lengkap
5. **Responsive Design** - Mobile-friendly layout
6. **Google Maps Integration** - Embed iframe berfungsi
7. **Conditional Display** - Hanya muncul jika ada data

### 📋 Data Flow:
```
Kelola Catalog Form 
  → JavaScript collects location data
  → AJAX POST to /kelola-catalog/builder/save
  → KelolaCatalogController::saveSections()
  → Save to catalog_sections table (section_type='location')
  → Update perusahaan table (maps_link, telepon, email, alamat)
  → Public Catalog loads sections
  → Display location section with data
```

## Testing Checklist

- [ ] Buka `/kelola-catalog`
- [ ] Isi semua field di Location Section
- [ ] Paste link Google Maps embed
- [ ] Klik "Update Semua Data"
- [ ] Verifikasi success message
- [ ] Buka `/catalog` atau klik "Preview Catalog"
- [ ] Verifikasi location section muncul setelah products
- [ ] Verifikasi semua data tampil dengan benar
- [ ] Verifikasi Google Maps embed berfungsi
- [ ] Test link telepon (klik harus buka dialer)
- [ ] Test link email (klik harus buka email client)
- [ ] Test link "Lihat di Google Maps" (buka di tab baru)
- [ ] Test responsive di mobile (inspect element)

## Catatan Teknis

### Database Schema:
```sql
-- catalog_sections table
section_type = 'location'
content = {
    "title": "LOKASI KAMI.",
    "name": "Nama Perusahaan",
    "address": "Alamat lengkap...",
    "phone": "0812-3456-7890",
    "email": "info@perusahaan.com",
    "maps_link": "https://www.google.com/maps/embed?pb=..."
}
```

### Fallback Data:
Jika `catalog_sections` kosong, data diambil dari tabel `perusahaan`:
- `name` → `perusahaan.nama`
- `address` → `perusahaan.alamat`
- `phone` → `perusahaan.telepon`
- `email` → `perusahaan.email`
- `maps_link` → `perusahaan.maps_link`

## Troubleshooting

### Maps tidak muncul:
- Pastikan menggunakan link **embed** bukan link biasa
- Link harus mengandung `google.com/maps/embed`
- Cek browser console untuk error CORS

### Data tidak tersimpan:
- Cek browser console untuk error AJAX
- Cek Laravel log: `storage/logs/laravel.log`
- Pastikan CSRF token valid

### Section tidak muncul di catalog:
- Pastikan minimal 1 field terisi
- Cek `is_active = true` di database
- Clear browser cache

---

**Implementasi Selesai** ✅
Tanggal: 30 April 2026

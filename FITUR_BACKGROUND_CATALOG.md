# Fitur Kustomisasi Background E-Catalog

## Perubahan yang Dilakukan

### 1. UI/UX Improvements
- ✅ **Hapus icon di tombol**: Tombol "Update Semua Perubahan" sekarang tanpa icon, lebih clean
- ✅ **Tombol dipindah ke bawah**: Tombol update dipindahkan ke paling bawah halaman untuk menyimpan semua perubahan sekaligus
- ✅ **Tombol full-width**: Tombol menggunakan lebar penuh dengan ukuran besar (btn-lg) untuk kemudahan klik

### 2. Fitur Kustomisasi Background Baru

#### A. Warna Solid
- Pilih warna solid untuk background catalog
- Color picker untuk memilih warna custom
- Preset warna: Putih, Abu Terang, Abu, Krem, Biru Muda, Hijau Muda, Pink Muda
- Preview real-time

#### B. Gradient
- Pilih 2 warna untuk membuat gradient
- Pilih arah gradient:
  - Kiri ke Kanan
  - Kanan ke Kiri
  - Atas ke Bawah
  - Bawah ke Atas
  - Diagonal (Kiri Atas ke Kanan Bawah)
  - Diagonal (Kanan Atas ke Kiri Bawah)
- Preset gradient: Purple, Pink, Blue, Green, Sunset
- Preview real-time

#### C. Gambar Background
- Upload gambar custom (max 5MB)
- Format: JPG, PNG, GIF, WEBP
- Slider transparansi overlay (0-100%)
- Overlay gelap untuk meningkatkan keterbacaan teks
- Preview real-time

### 3. Database Schema

**Table**: `perusahaan`

**Kolom Baru**:
```sql
background_type         ENUM('color', 'gradient', 'image') DEFAULT 'color'
background_color        VARCHAR(7) DEFAULT '#ffffff'
gradient_color_1        VARCHAR(7) NULL
gradient_color_2        VARCHAR(7) NULL
gradient_direction      VARCHAR(50) DEFAULT 'to right'
background_image        VARCHAR(255) NULL
background_opacity      INT DEFAULT 50
```

### 4. File yang Dimodifikasi

**View**:
- `resources/views/kelola-catalog/settings.blade.php`
  - Tambah section "Kustomisasi Latar Belakang Catalog"
  - Tambah preview background real-time
  - Pindahkan tombol ke bawah
  - Hapus icon di tombol

**Controller**:
- `app/Http/Controllers/KelolaCatalogController.php`
  - Update method `updateSettings()` untuk handle background settings
  - Tambah validasi untuk background fields
  - Handle upload background image

**Model**:
- `app/Models/Perusahaan.php`
  - Tambah kolom background ke `$fillable`

**Migration**:
- `database/migrations/2026_04_28_141341_add_background_settings_to_perusahaans_table.php`
  - Tambah 7 kolom baru untuk background customization

### 5. Cara Menggunakan

#### Untuk User/Admin:

1. **Login** ke sistem
2. Buka **Kelola Catalog** → **Pengaturan**
3. Scroll ke section **"Kustomisasi Latar Belakang Catalog"**
4. Pilih tipe background:
   - **Warna Solid**: Pilih warna dari color picker atau preset
   - **Gradient**: Pilih 2 warna dan arah gradient
   - **Gambar**: Upload gambar dan atur transparansi overlay
5. Lihat **preview** di bawah untuk melihat hasil
6. Scroll ke **paling bawah**
7. Klik tombol **"Update Semua Perubahan"**
8. Tunggu konfirmasi sukses
9. Buka catalog publik untuk melihat hasil

### 6. Fitur Preview Real-Time

- Preview otomatis update saat user mengubah pengaturan
- Menampilkan nama perusahaan dan contoh teks
- Membantu user melihat hasil sebelum menyimpan

### 7. Keamanan & Validasi

**Validasi**:
- Background type: harus 'color', 'gradient', atau 'image'
- Color codes: max 7 karakter (format hex: #ffffff)
- Background image: max 5MB, format: jpeg, png, jpg, gif, webp
- Opacity: 0-100

**Storage**:
- Background images disimpan di `storage/app/public/catalog-backgrounds/`
- Nama file: `bg_{company_id}_{timestamp}.{ext}`
- Old background image otomatis dihapus saat upload baru

### 8. Integrasi dengan Catalog Publik

Background settings akan diterapkan di:
- Halaman catalog publik (`/catalog`)
- Hero section
- Product listing section
- About section

**Implementasi di View Catalog**:
```php
@if($company->background_type == 'color')
    <style>
        .catalog-bg {
            background-color: {{ $company->background_color }};
        }
    </style>
@elseif($company->background_type == 'gradient')
    <style>
        .catalog-bg {
            background: linear-gradient(
                {{ $company->gradient_direction }}, 
                {{ $company->gradient_color_1 }}, 
                {{ $company->gradient_color_2 }}
            );
        }
    </style>
@elseif($company->background_type == 'image' && $company->background_image)
    <style>
        .catalog-bg {
            background-image: 
                linear-gradient(
                    rgba(0,0,0,{{ $company->background_opacity / 100 }}), 
                    rgba(0,0,0,{{ $company->background_opacity / 100 }})
                ), 
                url({{ asset('storage/'.$company->background_image) }});
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
@endif
```

### 9. Manfaat Fitur Ini

1. **Branding**: Setiap perusahaan bisa punya identitas visual unik
2. **Kreativitas**: User bebas berkreasi dengan warna dan gambar
3. **Profesional**: Catalog terlihat lebih profesional dan menarik
4. **Diferensiasi**: Membedakan catalog satu perusahaan dengan lainnya
5. **User-Friendly**: Interface mudah digunakan dengan preview real-time

### 10. Tips untuk User

**Warna Solid**:
- Gunakan warna terang untuk background agar teks mudah dibaca
- Sesuaikan dengan warna brand perusahaan

**Gradient**:
- Pilih kombinasi warna yang harmonis
- Gunakan preset untuk inspirasi
- Gradient diagonal memberikan kesan modern

**Gambar Background**:
- Gunakan gambar berkualitas tinggi
- Atur opacity overlay agar teks tetap terbaca
- Gambar produk atau tempat usaha bisa jadi pilihan bagus
- Hindari gambar yang terlalu ramai/kompleks

### 11. Troubleshooting

**Gambar tidak muncul**:
- Pastikan ukuran file < 5MB
- Cek format file (harus jpeg, png, jpg, gif, atau webp)
- Cek permission folder `storage/app/public/catalog-backgrounds`

**Warna tidak berubah**:
- Pastikan sudah klik tombol "Update Semua Perubahan"
- Clear browser cache
- Refresh halaman catalog

**Preview tidak update**:
- Refresh halaman settings
- Cek console browser untuk error JavaScript

### 12. Future Enhancements

1. **Pattern Background**: Tambah pilihan pattern (stripes, dots, etc.)
2. **Video Background**: Support video sebagai background
3. **Animated Gradient**: Gradient yang bergerak/animated
4. **Multiple Images**: Slideshow background dengan multiple images
5. **Seasonal Themes**: Template background untuk musim/event tertentu
6. **AI Suggestions**: AI yang suggest kombinasi warna yang bagus
7. **Import from Brand**: Import color palette dari logo perusahaan

## Kesimpulan

Fitur kustomisasi background ini memberikan fleksibilitas penuh kepada setiap perusahaan untuk membuat catalog mereka unik dan sesuai dengan identitas brand mereka. Dengan interface yang user-friendly dan preview real-time, user dapat dengan mudah bereksperimen dan menemukan tampilan yang paling sesuai untuk catalog mereka.

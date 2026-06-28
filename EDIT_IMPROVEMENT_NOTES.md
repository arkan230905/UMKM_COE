# Perbaikan Halaman Edit Pembelian

## Tujuan
Menyamakan tampilan halaman Edit dengan halaman Detail (show.blade.php)

## Perubahan yang Diperlukan:

### 1. Struktur Header
- ✅ Sudah sama - menggunakan form-section dengan section-header

### 2. Detail Item Layout  
- **Show**: Menggunakan card dengan `border-info` dan `card-header bg-light`
- **Edit**: Menggunakan `item-row` dengan background `#f8f9fa`
- **Action**: Ubah item-row menjadi card border-info

### 3. Info Display
- **Show**: Menggunakan class `info-display` untuk menampilkan data read-only
- **Edit**: Menggunakan input fields langsung
- **Action**: Pertahankan input fields tapi tambahkan styling yang lebih mirip

### 4. Perhitungan Section
- ✅ Sudah sama - menggunakan calculation-section

### 5. Total Section  
- ✅ Sudah sama - menggunakan total-section dengan card

## Rekomendasi
Karena edit page harus tetap editable, yang perlu disamakan adalah:
1. Visual styling (colors, borders, spacing)
2. Card structure untuk detail items
3. Icon consistency

File edit.blade.php terlalu besar (>1000 lines) untuk di-update sekaligus.
Perlu membuat versi baru atau update secara bertahap.

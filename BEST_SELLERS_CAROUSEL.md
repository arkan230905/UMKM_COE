# Best Sellers Carousel di Dashboard Pelanggan

## Fitur
Menampilkan produk best seller dalam bentuk carousel yang menarik di halaman dashboard pelanggan.

## Perubahan yang Dilakukan

### 1. Update View Dashboard (`pelanggan/dashboard.blade.php`)
- Ganti gambar "Produk UMKM" dengan carousel best sellers
- Menampilkan:
  - Badge "Best Seller" dengan warna pink
  - Foto produk
  - Nama produk
  - Harga
  - Rating dan jumlah terjual
  - Tombol "Tambah ke Keranjang"

### 2. Carousel Features
- **Auto-rotate**: Carousel otomatis berganti setiap 5 detik
- **Navigation**: Tombol prev/next untuk manual navigation
- **Dots Indicator**: Titik-titik untuk menunjukkan posisi slide
- **Smooth Animation**: Transisi yang halus antar slide
- **Responsive**: Menyesuaikan dengan ukuran layar

### 3. JavaScript Functions
```javascript
nextSlide()      // Ke slide berikutnya
prevSlide()      // Ke slide sebelumnya
goToSlide(index) // Ke slide tertentu
updateCarousel() // Update tampilan carousel
```

### 4. Data dari Controller
Controller `DashboardController` sudah menyediakan:
- `$bestSellers` - Koleksi produk best seller (max 6 produk)
- Diambil dari `PenjualanDetail` berdasarkan jumlah terjual
- Fallback ke produk terbaru jika belum ada penjualan

## Styling
- Background gradient biru yang menarik
- Card design dengan shadow
- Pink accent color untuk best seller badge
- Brown color untuk tombol (sesuai tema)

## Responsive Design
- Bekerja di desktop, tablet, dan mobile
- Carousel buttons tersembunyi jika hanya 1 produk
- Dots indicator tersembunyi jika hanya 1 produk

## Performance
- Best sellers di-cache selama 60 detik
- Mengurangi query database
- Auto-refresh cache setiap 1 menit

## Fallback
Jika belum ada data penjualan:
- Menampilkan produk terbaru
- Hanya produk dengan stok > 0
- Max 6 produk

---

**Status:** ✅ Implemented
**Date:** 2026-05-17

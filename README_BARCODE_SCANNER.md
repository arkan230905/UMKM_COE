# 📦 Sistem Barcode Scanner - Quick Reference

## 🚀 Quick Start

### Cara Menggunakan:

1. **Buka halaman Tambah Penjualan**
   ```
   URL: /transaksi/penjualan/create
   ```

2. **Scan Barcode**
   - Arahkan scanner ke barcode produk
   - Sistem otomatis mendeteksi dan menambahkan produk
   - Dengar beep sukses dan lihat notifikasi hijau

3. **Atau Ketik Manual**
   - Ketik nama produk atau barcode
   - Pilih dari hasil pencarian
   - Klik tombol [+ Tambah]

## ⌨️ Keyboard Shortcuts

| Shortcut | Fungsi |
|----------|--------|
| **F2** | Fokus ke input scanner |
| **Escape** | Clear input dan tutup search |
| **Arrow Down** | Navigasi hasil pencarian (ke bawah) |
| **Arrow Up** | Navigasi hasil pencarian (ke atas) |
| **Enter** | Pilih produk yang di-highlight |

## 🎨 Status Indicator

| Warna | Status | Arti |
|-------|--------|------|
| 🟢 Hijau | **Siap Scan** | Ready untuk scan barcode |
| 🟡 Kuning | **Memproses...** | Sedang memproses barcode |
| 🟢 Hijau | **✓ Nama Produk** | Produk berhasil ditambahkan |
| 🔴 Merah | **Produk tidak ditemukan** | Barcode tidak valid |
| 🟡 Kuning | **Stok habis!** | Produk tidak tersedia |

## 🔊 Audio Feedback

| Beep | Arti |
|------|------|
| **Beep tunggal** (high-pitched) | ✅ Produk berhasil ditambahkan |
| **Beep ganda** (low-pitched) | ❌ Error (produk tidak ada / stok habis) |

## 📱 Notifikasi Toast

| Warna | Icon | Arti |
|-------|------|------|
| 🟢 Hijau | ✅ | Sukses - Produk ditambahkan |
| 🔴 Merah | ❌ | Error - Produk tidak ditemukan |
| 🟡 Kuning | ⚠️ | Warning - Stok habis/tidak cukup |

## 🛒 Cart Counter

Badge biru di kanan atas menampilkan total item di keranjang:
```
🛒 5 item
```
- Update otomatis saat produk ditambahkan/dihapus
- Animasi pulse saat berubah

## 🎯 Fitur Utama

### 1. Scan Barcode Otomatis
✅ Deteksi otomatis dari scanner fisik  
✅ Timeout 80ms untuk deteksi akhir scan  
✅ Langsung tambah ke keranjang  

### 2. Validasi Stok
✅ Cek stok sebelum tambah produk  
✅ Notifikasi jika stok habis  
✅ Notifikasi jika quantity melebihi stok  

### 3. Auto-increment Quantity
✅ Jika produk sudah ada, quantity +1  
✅ Notifikasi menampilkan quantity baru  
✅ Validasi stok saat increment  

### 4. Pencarian Real-time
✅ Ketik untuk mencari produk  
✅ Hasil muncul setelah 200ms  
✅ Highlight barcode yang cocok  
✅ Maksimal 10 hasil  

### 5. Visual Feedback
✅ Highlight baris produk (hijau)  
✅ Box shadow dan scale animation  
✅ Toast notification dengan slide-in/out  

## 🐛 Troubleshooting

### Barcode tidak terdeteksi?
1. Pastikan produk memiliki barcode di database
2. Cek format barcode (minimal 8 karakter)
3. Tekan F2 untuk fokus ke scanner
4. Coba scan ulang

### Audio tidak bunyi?
1. Cek volume browser
2. Cek apakah browser support Web Audio API
3. Refresh halaman

### Focus tidak kembali ke scanner?
1. Tekan F2 untuk fokus manual
2. Tutup modal/popup jika ada
3. Refresh halaman

## 📚 Dokumentasi Lengkap

- **Dokumentasi Teknis**: `BARCODE_SCANNER_DOCUMENTATION.md`
- **Panduan Testing**: `BARCODE_SCANNER_TESTING_GUIDE.md`
- **Ringkasan Implementasi**: `BARCODE_SCANNER_IMPLEMENTATION_SUMMARY.md`

## 🎓 Tips & Tricks

### Untuk Kasir:
1. **Selalu fokus pada scanner** - Tekan F2 jika focus hilang
2. **Dengarkan beep** - Beep sukses = produk masuk, beep error = coba lagi
3. **Lihat status indicator** - Hijau = OK, Merah = Error, Kuning = Warning
4. **Cek cart counter** - Pastikan jumlah item sesuai
5. **Gunakan pencarian** - Jika barcode rusak, ketik nama produk

### Untuk Developer:
1. **Cek console log** - Untuk debug barcode detection
2. **Test dengan berbagai barcode** - Pastikan semua format didukung
3. **Monitor performance** - Scan detection harus < 100ms
4. **Update productData** - Saat ada produk baru dengan barcode

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Baca dokumentasi lengkap di `BARCODE_SCANNER_DOCUMENTATION.md`
2. Cek panduan testing di `BARCODE_SCANNER_TESTING_GUIDE.md`
3. Hubungi tim IT/developer

---

**Version**: 1.0.0  
**Status**: ✅ Production Ready  
**Last Updated**: 29 April 2026

# 📸 Cara Pakai Lightbox Foto Produk (Seperti WhatsApp)

## Fitur Lightbox

Sistem foto produk sekarang menggunakan **lightbox** yang bekerja seperti foto profil WhatsApp:

### ✨ Cara Kerja:

1. **Klik foto kecil** (35x35px) di tabel produk
2. **Foto membesar fullscreen** dengan background hitam transparan
3. **Klik di mana saja** atau tombol **X** untuk menutup
4. **Tekan ESC** untuk menutup
5. **Foto kembali** ke ukuran semula

---

## 🎯 Fitur Lengkap

### Di Halaman Index (Daftar Produk):
- ✅ Thumbnail foto 35x35px
- ✅ Hover effect dengan icon kaca pembesar
- ✅ Klik untuk membuka lightbox fullscreen
- ✅ Foto besar (95% viewport)
- ✅ Background hitam transparan (95% opacity)
- ✅ Nama produk ditampilkan di atas
- ✅ Tombol X di pojok kanan atas
- ✅ Klik di luar foto untuk menutup
- ✅ Tombol ESC untuk menutup

### Animasi:
- Fade in saat membuka (0.3s)
- Fade out saat menutup (0.3s)
- Smooth transition

---

## 🔧 Implementasi Teknis

### HTML Structure:
```html
<!-- Thumbnail di tabel -->
<div class="product-image-wrapper" onclick="showImageModal(url, name)">
    <img src="foto-kecil.jpg">
    <div class="image-overlay">
        <i class="fas fa-search-plus"></i>
    </div>
</div>

<!-- Lightbox fullscreen -->
<div id="imageLightbox" onclick="closeLightbox()">
    <div onclick="closeLightbox()">&times;</div>
    <div id="lightboxTitle"></div>
    <img id="lightboxImage">
</div>
```

### JavaScript Functions:
```javascript
// Buka lightbox
function showImageModal(imageUrl, productName) {
    lightbox.style.display = 'block';
    lightbox.style.opacity = '1';
}

// Tutup lightbox
function closeLightbox() {
    lightbox.style.opacity = '0';
    setTimeout(() => lightbox.style.display = 'none', 300);
}

// ESC key handler
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
});
```

---

## 📱 Responsive Design

- **Desktop**: Foto maksimal 95% viewport
- **Mobile**: Foto menyesuaikan layar
- **Tablet**: Foto optimal di semua orientasi

---

## 🎨 Styling

### Thumbnail (35x35px):
- Border radius: 4px
- Box shadow: subtle
- Hover: scale 1.1
- Cursor: pointer

### Lightbox:
- Background: rgba(0,0,0,0.95)
- Z-index: 9999
- Position: fixed fullscreen
- Cursor: pointer

### Foto Besar:
- Max-width: 95%
- Max-height: 95%
- Object-fit: contain
- Border-radius: 8px
- Box-shadow: glow effect

---

## 🚀 Demo

Buka file `DEMO_LIGHTBOX_FOTO.html` di browser untuk melihat demo interaktif!

---

## ✅ Status

**SELESAI** - Lightbox foto produk sudah berfungsi seperti WhatsApp:
- Foto kecil di tabel ✅
- Klik untuk membesar ✅
- Fullscreen dengan background hitam ✅
- Klik di luar untuk menutup ✅
- Tombol ESC untuk menutup ✅
- Animasi smooth ✅

---

## 📝 Catatan

- Lightbox menggunakan JavaScript vanilla (tanpa library)
- Tidak bergantung pada Bootstrap modal
- Lebih ringan dan cepat
- Compatible dengan semua browser modern
- Mobile-friendly

---

## 🎯 Cara Test

1. Buka halaman **Master Data > Produk**
2. Upload foto produk (jika belum ada)
3. Klik pada foto kecil di tabel
4. Foto akan membesar fullscreen
5. Klik di mana saja atau tekan ESC untuk menutup

**Enjoy!** 🎉

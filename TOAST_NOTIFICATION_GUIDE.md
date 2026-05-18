# Toast Notification System

Sistem notifikasi yang menarik dan auto-hilang telah diimplementasikan di aplikasi.

## Fitur

✨ **Notifikasi Kecil di Atas Kanan**
- Posisi fixed di top-right corner
- Animasi slide-in yang smooth
- Auto-hilang setelah beberapa detik
- Bisa ditutup manual dengan tombol ×

🎨 **4 Tipe Notifikasi**
- `success` - Hijau (3 detik)
- `error` - Merah (4 detik)
- `info` - Biru (3 detik)
- `warning` - Kuning (3.5 detik)

## Cara Menggunakan

### 1. Dari JavaScript (Client-side)

```javascript
// Success notification
showToast('Produk ditambahkan ke keranjang', 'success', 2500);

// Error notification
showToast('Gagal menambahkan ke keranjang', 'error', 3000);

// Info notification
showToast('Informasi penting', 'info', 3000);

// Warning notification
showToast('Perhatian!', 'warning', 3500);
```

Parameter:
- `message` (string) - Pesan yang ditampilkan
- `type` (string) - Tipe notifikasi: 'success', 'error', 'info', 'warning'
- `duration` (number) - Durasi tampil dalam ms (0 = tidak auto-hilang)

### 2. Dari Laravel (Server-side)

Gunakan session flash messages di controller:

```php
// Success
return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');

// Error
return redirect()->back()->with('error', 'Terjadi kesalahan!');

// Info
return redirect()->back()->with('info', 'Informasi penting');

// Warning
return redirect()->back()->with('warning', 'Perhatian!');
```

Notifikasi akan otomatis ditampilkan saat halaman dimuat.

## Contoh Implementasi

### Di Dashboard (Sudah Diterapkan)

```javascript
function addToCart(produkId) {
    // ... kode lainnya ...
    
    fetch("{{ route('pelanggan.cart.ajax.store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            produk_id: produkId,
            qty: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cartItems[produkId].id = data.cart_id;
            showToast('Produk ditambahkan ke keranjang', 'success', 2500);
        }
    })
    .catch(error => {
        console.error('Background sync error:', error);
        showToast('Gagal menambahkan ke keranjang', 'error', 3000);
    });
}
```

## Styling

Notifikasi memiliki:
- Border kiri berwarna sesuai tipe
- Gradient background yang subtle
- Icon yang sesuai dengan tipe
- Shadow yang halus
- Responsive design (max-width 350px)

## Animasi

- **Masuk**: Slide dari kanan (0.3s)
- **Keluar**: Slide ke kanan (0.3s)
- **Smooth**: Menggunakan CSS transitions

## Tips

1. Gunakan durasi lebih lama untuk error (4000ms)
2. Gunakan durasi lebih pendek untuk success (2500ms)
3. Untuk notifikasi penting, set duration = 0 dan biarkan user menutup manual
4. Jangan tampilkan lebih dari 3 notifikasi sekaligus

---

**Implementasi Selesai!** ✓

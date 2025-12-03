# Implementasi E-Commerce untuk Pelanggan

## 📋 Fitur Lengkap

### 1. Dashboard Pelanggan
- Daftar produk yang dijual
- Stok setiap produk
- Foto produk
- Harga produk
- Tombol "Tambah ke Keranjang"

### 2. Keranjang Belanja
- Daftar produk di keranjang
- Qty produk
- Subtotal per item
- Total keseluruhan
- Tombol hapus item
- Tombol update qty
- Tombol checkout

### 3. Checkout & Pembayaran
- Form data pengiriman
- Pilih metode pembayaran:
  - QRIS (Midtrans)
  - Virtual Account (BCA, BNI, BRI, Mandiri)
  - Transfer Bank
- Ringkasan pesanan
- Total pembayaran

### 4. Notifikasi
- Notifikasi pesanan dibuat
- Notifikasi pembayaran pending
- Notifikasi pembayaran berhasil
- Notifikasi pembayaran gagal
- Notifikasi pesanan diproses
- Notifikasi pesanan dikirim
- Notifikasi pesanan selesai

## 🗄️ Database Schema

### Tabel: `carts` (Keranjang)
```sql
- id
- user_id (pelanggan)
- produk_id
- qty
- harga
- subtotal
- created_at
- updated_at
```

### Tabel: `orders` (Pesanan)
```sql
- id
- user_id (pelanggan)
- nomor_order
- total_amount
- status (pending, paid, processing, shipped, completed, cancelled)
- payment_method (qris, va_bca, va_bni, va_bri, va_mandiri, transfer)
- payment_status (pending, paid, failed, expired)
- midtrans_order_id
- midtrans_transaction_id
- snap_token
- nama_penerima
- alamat_pengiriman
- telepon_penerima
- catatan
- paid_at
- created_at
- updated_at
```

### Tabel: `order_items` (Detail Pesanan)
```sql
- id
- order_id
- produk_id
- qty
- harga
- subtotal
- created_at
- updated_at
```

### Tabel: `notifications` (Notifikasi)
```sql
- id
- user_id
- type (order_created, payment_pending, payment_success, payment_failed, order_processing, order_shipped, order_completed)
- title
- message
- data (JSON)
- read_at
- created_at
- updated_at
```

## 🔧 Setup Midtrans

### 1. Install Midtrans SDK
```bash
composer require midtrans/midtrans-php
```

### 2. Konfigurasi `.env`
```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

### 3. Config File `config/midtrans.php`
```php
return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];
```

## 📁 Struktur File

```
app/
├── Http/
│   └── Controllers/
│       ├── Pelanggan/
│       │   ├── DashboardController.php
│       │   ├── CartController.php
│       │   ├── CheckoutController.php
│       │   └── OrderController.php
│       └── MidtransController.php
├── Models/
│   ├── Cart.php
│   ├── Order.php
│   ├── OrderItem.php
│   └── Notification.php
└── Services/
    └── MidtransService.php

resources/
└── views/
    └── pelanggan/
        ├── dashboard.blade.php
        ├── cart.blade.php
        ├── checkout.blade.php
        ├── order-detail.blade.php
        └── notifications.blade.php

routes/
└── web.php (tambah route pelanggan)

database/
└── migrations/
    ├── xxxx_create_carts_table.php
    ├── xxxx_create_orders_table.php
    ├── xxxx_create_order_items_table.php
    └── xxxx_create_notifications_table.php
```

## 🚀 Flow Pembelian

1. **Pelanggan Login** → Dashboard
2. **Lihat Produk** → Pilih produk → Tambah ke keranjang
3. **Keranjang** → Review items → Update qty → Checkout
4. **Checkout** → Isi data pengiriman → Pilih metode pembayaran
5. **Pembayaran** → Redirect ke Midtrans Snap → Bayar (QRIS/VA/Transfer)
6. **Callback** → Midtrans kirim notifikasi → Update status order
7. **Notifikasi** → Pelanggan dapat notifikasi real-time
8. **Selesai** → Order completed

## 📱 Metode Pembayaran Midtrans

### 1. QRIS
- Scan QR code
- Bayar via e-wallet (GoPay, OVO, Dana, dll)

### 2. Virtual Account
- BCA VA
- BNI VA
- BRI VA
- Mandiri Bill

### 3. Transfer Bank
- Manual transfer
- Upload bukti transfer

## 🔔 Sistem Notifikasi

### Real-time Notification
- Menggunakan Laravel Echo + Pusher (optional)
- Atau polling setiap 30 detik
- Badge notifikasi di navbar
- Dropdown notifikasi

### Email Notification
- Email konfirmasi order
- Email pembayaran berhasil
- Email order dikirim

## 🎨 UI/UX

- Responsive design (mobile-friendly)
- Card layout untuk produk
- Badge stok (Tersedia/Habis)
- Loading state saat proses
- Toast notification
- Modal konfirmasi

## 📊 Status Order

1. **pending** - Menunggu pembayaran
2. **paid** - Sudah dibayar
3. **processing** - Sedang diproses
4. **shipped** - Sedang dikirim
5. **completed** - Selesai
6. **cancelled** - Dibatalkan

## 🔐 Middleware

```php
Route::middleware(['auth', 'role:pelanggan'])->group(function () {
    // Routes pelanggan
});
```

## 📝 Catatan Implementasi

- Gunakan transaction untuk checkout
- Validasi stok sebelum checkout
- Lock stok saat order dibuat
- Release stok jika payment expired
- Log semua transaksi Midtrans
- Handle webhook Midtrans dengan baik
- Sanitize input user
- Rate limiting untuk API

---

**Status:** 🚧 READY TO IMPLEMENT
**Estimasi:** File lengkap akan dibuat step by step

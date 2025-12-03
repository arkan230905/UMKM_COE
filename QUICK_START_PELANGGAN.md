# 🚀 QUICK START - Halaman Pelanggan

## ✅ STATUS: 100% SIAP DIGUNAKAN

Halaman pelanggan sudah lengkap dan siap digunakan!

---

## 📋 YANG SUDAH TERSEDIA

✅ **10 Routes** - Semua endpoint pelanggan  
✅ **4 Database Tables** - carts, orders, order_items, notifications  
✅ **5 Controllers** - Dashboard, Cart, Checkout, Order, Midtrans  
✅ **4 Models** - Cart, Order, OrderItem, Notification  
✅ **5 Views** - Dashboard, Cart, Checkout, Orders, Order Detail  
✅ **Midtrans Package** - Payment gateway terinstall  
✅ **1 User Pelanggan** - abiyyu@gmail.com  
✅ **1 Produk** - Nasi Ayam Ketumbar (Stok: 100)

---

## 🎯 CARA MENGGUNAKAN (3 LANGKAH)

### 1️⃣ Login sebagai Pelanggan

```
URL: http://127.0.0.1:8000/login

Kredensial:
Email: abiyyu@gmail.com
Password: (password yang diset saat registrasi)
```

**Atau buat akun baru:**
```
URL: http://127.0.0.1:8000/register

Pilih role: Pelanggan
Isi form dan submit
```

### 2️⃣ Belanja di Dashboard

Setelah login, Anda akan otomatis redirect ke:
```
http://127.0.0.1:8000/pelanggan/dashboard
```

**Fitur yang bisa digunakan:**
- ✅ Lihat katalog produk
- ✅ Tambah ke keranjang
- ✅ Update qty
- ✅ Hapus item
- ✅ Checkout
- ✅ Bayar via Midtrans
- ✅ Lihat pesanan

### 3️⃣ Checkout & Bayar

```
1. Klik "Tambah ke Keranjang" di produk
2. Klik icon keranjang di header
3. Klik "Checkout"
4. Isi data pengiriman
5. Pilih metode pembayaran
6. Klik "Proses Pembayaran"
7. Bayar via Midtrans
8. ✅ Selesai!
```

---

## 🔗 DAFTAR URL PELANGGAN

| URL | Fungsi |
|-----|--------|
| `/pelanggan/dashboard` | Katalog produk |
| `/pelanggan/cart` | Keranjang belanja |
| `/pelanggan/checkout` | Form checkout |
| `/pelanggan/orders` | Daftar pesanan |
| `/pelanggan/orders/{id}` | Detail pesanan |

---

## 💳 METODE PEMBAYARAN

Tersedia 5 metode pembayaran via Midtrans:

1. **QRIS** - Scan & Pay (GoPay, OVO, Dana, dll)
2. **BCA Virtual Account**
3. **BNI Virtual Account**
4. **BRI Virtual Account**
5. **Mandiri Virtual Account**

### Test Card (Sandbox):
```
Card Number: 4811 1111 1111 1114
CVV: 123
Exp Date: 01/25
OTP: 112233
```

---

## ⚙️ KONFIGURASI MIDTRANS (OPTIONAL)

Jika ingin test pembayaran real, update `.env`:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_KEY
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_KEY
MIDTRANS_IS_PRODUCTION=false
```

**Cara mendapatkan key:**
1. Daftar di https://dashboard.midtrans.com/
2. Pilih Environment: Sandbox
3. Copy Server Key & Client Key

---

## 🧪 TEST CHECKLIST

Gunakan checklist ini untuk memastikan semua berfungsi:

- [ ] Login sebagai pelanggan berhasil
- [ ] Dashboard menampilkan produk
- [ ] Tambah ke keranjang berhasil
- [ ] Badge keranjang bertambah
- [ ] Lihat keranjang menampilkan item
- [ ] Update qty berhasil
- [ ] Hapus item berhasil
- [ ] Checkout form muncul
- [ ] Isi data pengiriman
- [ ] Pilih metode pembayaran
- [ ] Proses pembayaran berhasil
- [ ] Redirect ke detail pesanan
- [ ] Popup Midtrans muncul (jika key sudah diset)
- [ ] Lihat daftar pesanan
- [ ] Detail pesanan lengkap

---

## 🐛 TROUBLESHOOTING

### Masalah: Tidak bisa login
**Solusi:**
```bash
# Reset password via database
php artisan tinker
>>> $user = App\Models\User::where('email', 'abiyyu@gmail.com')->first();
>>> $user->password = bcrypt('password123');
>>> $user->save();
```

### Masalah: Produk tidak muncul
**Solusi:**
```bash
# Tambah stok produk
php add_stok_produk.php
```

### Masalah: Error 404 di route pelanggan
**Solusi:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Masalah: Midtrans popup tidak muncul
**Solusi:**
1. Cek browser console untuk error
2. Pastikan MIDTRANS_CLIENT_KEY sudah diset
3. Disable ad blocker
4. Coba browser lain

---

## 📊 DATA YANG TERSEDIA

### User Pelanggan:
```
Nama: abiyyu arkan
Email: abiyyu@gmail.com
Role: pelanggan
```

### Produk:
```
Nama: Nasi Ayam Ketumbar
Harga: Rp 18.606
Stok: 100
```

---

## 🎉 SELESAI!

Halaman pelanggan sudah 100% siap digunakan!

**Langkah selanjutnya:**
1. ✅ Login dan test semua fitur
2. ✅ Tambahkan lebih banyak produk
3. ✅ Upload foto produk
4. ✅ Set Midtrans keys (jika ingin test pembayaran)
5. ✅ Deploy ke production (jika sudah siap)

---

**Dokumentasi Lengkap:** Lihat `HALAMAN_PELANGGAN_100_PERSEN.md`  
**Test Script:** Jalankan `php test_pelanggan_complete.php`

**Dibuat:** 3 Desember 2025  
**Status:** ✅ COMPLETE & READY

# 🔑 Cara Mengisi Midtrans Keys

## Langkah-langkah:

### 1. Buka Midtrans Dashboard
Anda sudah login di: https://dashboard.sandbox.midtrans.com/

### 2. Ambil Access Keys
1. Klik menu **Settings** di sidebar kiri
2. Klik **Access Keys**
3. Anda akan melihat:
   - **Server Key** (dimulai dengan `SB-Mid-server-...`)
   - **Client Key** (dimulai dengan `SB-Mid-client-...`)

### 3. Copy Keys ke .env
Buka file `.env` di root project, lalu ganti:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-GANTI_DENGAN_SERVER_KEY_ANDA
MIDTRANS_CLIENT_KEY=SB-Mid-client-GANTI_DENGAN_CLIENT_KEY_ANDA
```

Dengan keys yang Anda copy dari dashboard.

### 4. Clear Cache
Setelah update .env, jalankan:
```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Test Pembayaran
1. Login sebagai pelanggan: http://127.0.0.1:8000/login
2. Tambah produk ke keranjang
3. Checkout
4. Pilih metode pembayaran
5. Popup Midtrans akan muncul
6. Gunakan test card untuk testing:
   - Card: 4811 1111 1111 1114
   - CVV: 123
   - Exp: 01/25
   - OTP: 112233

## ✅ Selesai!

Setelah keys diisi, sistem pembayaran akan berfungsi dengan baik.

**Catatan:** 
- Ini adalah Sandbox (testing), tidak ada uang real
- Untuk production, ganti ke Production keys dan set `MIDTRANS_IS_PRODUCTION=true`

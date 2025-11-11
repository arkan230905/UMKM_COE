# Fix Pembayaran Beban 404 Not Found

## Masalah
URL `http://127.0.0.1:8000/transaksi/expense-payment` menampilkan 404 Not Found.

## Penyebab
1. **Route duplikat**: Ada dua route untuk pembayaran beban
   - `transaksi/pembayaran-beban` (yang benar)
   - `transaksi/expense-payment` (duplikat, tidak lengkap)

2. **View menggunakan route name yang salah**: View menggunakan `transaksi.expense-payment.*` tapi route yang benar adalah `transaksi.pembayaran-beban.*`

3. **Route password duplikat**: Ada duplikat route `password.request` yang menyebabkan error saat cache route

## Solusi yang Diterapkan

### 1. Hapus Route Duplikat
**File:** `routes/web.php`

Menghapus route duplikat `expense-payment`:
```php
// SEBELUM (SALAH - Ada 2 route)
Route::prefix('pembayaran-beban')->name('pembayaran-beban.')->group(...);
Route::prefix('expense-payment')->name('expense-payment.')->group(...); // DUPLIKAT!

// SESUDAH (BENAR - Hanya 1 route)
Route::prefix('pembayaran-beban')->name('pembayaran-beban.')->group(...);
```

### 2. Lengkapi Route Pembayaran Beban
Menambahkan route edit, update, dan delete yang hilang:
```php
Route::prefix('pembayaran-beban')->name('pembayaran-beban.')->group(function() {
    Route::get('/', [ExpensePaymentController::class, 'index'])->name('index');
    Route::get('/create', [ExpensePaymentController::class, 'create'])->name('create');
    Route::post('/', [ExpensePaymentController::class, 'store'])->name('store');
    Route::get('/{id}', [ExpensePaymentController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ExpensePaymentController::class, 'edit'])->name('edit'); // BARU
    Route::put('/{id}', [ExpensePaymentController::class, 'update'])->name('update'); // BARU
    Route::delete('/{id}', [ExpensePaymentController::class, 'destroy'])->name('destroy'); // BARU
    Route::get('/print/{id}', [ExpensePaymentController::class, 'print'])->name('print');
});
```

### 3. Perbaiki View
**File:** `resources/views/transaksi/expense-payment/index.blade.php`

```php
// SEBELUM
route('transaksi.expense-payment.create')
route('transaksi.expense-payment.show', $r->id)

// SESUDAH
route('transaksi.pembayaran-beban.create')
route('transaksi.pembayaran-beban.show', $r->id)
```

**File:** `resources/views/transaksi/expense-payment/create.blade.php`

```php
// SEBELUM
route('transaksi.expense-payment.store')

// SESUDAH
route('transaksi.pembayaran-beban.store')
```

### 4. Hapus Route Password Duplikat
**File:** `routes/web.php`

Menghapus route password yang duplikat dengan auth.php:
```php
// SEBELUM (DUPLIKAT)
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request'); // DUPLIKAT dengan auth.php!

// SESUDAH (DIHAPUS)
// Route password sudah ada di auth.php, tidak perlu duplikat
```

### 5. Clear dan Cache Route
```bash
php artisan route:clear
php artisan route:cache
```

## URL yang Benar

### Sebelum (SALAH)
```
❌ http://127.0.0.1:8000/transaksi/expense-payment
```

### Sesudah (BENAR)
```
✅ http://127.0.0.1:8000/transaksi/pembayaran-beban
```

## Daftar Route Pembayaran Beban

| Method | URL | Name | Action |
|--------|-----|------|--------|
| GET | /transaksi/pembayaran-beban | transaksi.pembayaran-beban.index | index |
| GET | /transaksi/pembayaran-beban/create | transaksi.pembayaran-beban.create | create |
| POST | /transaksi/pembayaran-beban | transaksi.pembayaran-beban.store | store |
| GET | /transaksi/pembayaran-beban/{id} | transaksi.pembayaran-beban.show | show |
| GET | /transaksi/pembayaran-beban/{id}/edit | transaksi.pembayaran-beban.edit | edit |
| PUT | /transaksi/pembayaran-beban/{id} | transaksi.pembayaran-beban.update | update |
| DELETE | /transaksi/pembayaran-beban/{id} | transaksi.pembayaran-beban.destroy | destroy |
| GET | /transaksi/pembayaran-beban/print/{id} | transaksi.pembayaran-beban.print | print |

## Testing

### 1. Test Route List
```bash
php artisan route:list --name=pembayaran-beban
```

Harus menampilkan 10 route (termasuk laporan).

### 2. Test Akses URL
```
1. Buka: http://127.0.0.1:8000/transaksi/pembayaran-beban
2. Harus menampilkan halaman index pembayaran beban
3. Klik "Tambah" - harus buka form create
4. Isi form dan submit - harus berhasil
```

### 3. Test Update BOP Aktual
```
1. Input pembayaran beban
2. Buka Master Data → BOP
3. Cek kolom "Aktual" - harus terisi
4. Cek kolom "Sisa" - harus menampilkan selisih
```

## Kesimpulan

Masalah 404 Not Found sudah diperbaiki:
- ✅ Route duplikat dihapus
- ✅ Route lengkap dengan edit, update, delete
- ✅ View menggunakan route name yang benar
- ✅ Route password duplikat dihapus
- ✅ Cache route berhasil

**URL yang benar:**
```
http://127.0.0.1:8000/transaksi/pembayaran-beban
```

**Refresh browser dan test sekarang!** 🎉

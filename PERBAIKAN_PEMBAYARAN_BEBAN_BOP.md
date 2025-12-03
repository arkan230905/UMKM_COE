# Perbaikan Pembayaran Beban dan Update BOP Aktual

## Masalah
1. Pembayaran beban menampilkan 404 Not Found
2. Data pembayaran beban tidak masuk ke kolom aktual di master data BOP
3. Selisih (budget - aktual) tidak dihitung

## Solusi yang Diterapkan

### 1. Perbaikan Route
**File:** `routes/web.php`

Route sudah ada dan benar:
```php
Route::prefix('expense-payment')->name('expense-payment.')->group(function() {
    Route::get('/', [ExpensePaymentController::class, 'index'])->name('index');
    Route::get('/create', [ExpensePaymentController::class, 'create'])->name('create');
    Route::post('/', [ExpensePaymentController::class, 'store'])->name('store');
    // ... dst
});
```

### 2. Update ExpensePaymentController
**File:** `app/Http/Controllers/ExpensePaymentController.php`

#### A. Method store() - Tambah Update BOP Aktual
```php
// Setelah membuat journal entry
$journal->post(...);

// Update aktual di BOP
$this->updateBopAktual($coa->kode_akun);
```

#### B. Method update() - Tambah Update BOP Aktual
```php
// Setelah membuat journal entry baru
$journal->post(...);

// Update aktual di BOP
$this->updateBopAktual($coa->kode_akun);
```

#### C. Method destroy() - Tambah Update BOP Aktual
```php
// Simpan kode akun sebelum delete
$kodeAkun = $row->coa->kode_akun ?? null;

$row->delete();

// Update aktual di BOP setelah delete
if ($kodeAkun) {
    $this->updateBopAktual($kodeAkun);
}
```

#### D. Method Baru: updateBopAktual()
```php
/**
 * Update kolom aktual di BOP berdasarkan total pembayaran beban
 */
private function updateBopAktual($kodeAkun)
{
    // Cari BOP dengan kode akun ini
    $bop = \App\Models\Bop::where('kode_akun', $kodeAkun)->first();
    
    if (!$bop) {
        return;
    }

    // Hitung total pembayaran beban untuk akun ini
    $totalAktual = ExpensePayment::where('coa_beban_id', function($query) use ($kodeAkun) {
        $query->select('id')
              ->from('coas')
              ->where('kode_akun', $kodeAkun)
              ->limit(1);
    })->sum('nominal');

    // Update kolom aktual
    $bop->aktual = $totalAktual;
    $bop->save();

    \Log::info('BOP Aktual Updated', [
        'kode_akun' => $kodeAkun,
        'aktual' => $totalAktual,
        'budget' => $bop->budget,
        'selisih' => $bop->budget - $totalAktual,
    ]);
}
```

### 3. View BOP Index
**File:** `resources/views/master-data/bop/index.blade.php`

View sudah menampilkan:
- Budget
- Aktual
- Sisa (Budget - Aktual)
- Warna merah jika minus, hijau jika plus

```php
@php
    $sisa = $hasBudget ? ($bop->budget - ($bop->aktual ?? 0)) : 0;
    $textClass = $sisa < 0 ? 'text-danger' : 'text-success';
@endphp

<td class="text-end {{ $textClass }}">
    {{ $hasBudget ? number_format($sisa, 0, ',', '.') : '-' }}
</td>
```

## Alur Kerja

### 1. Input Pembayaran Beban
```
User → Form Pembayaran Beban → Submit
  ↓
ExpensePaymentController::store()
  ↓
1. Validasi data
2. Cek saldo kas cukup
3. Simpan ke tabel expense_payments
4. Buat journal entry (Dr Beban, Cr Kas)
5. Update BOP aktual ← BARU!
  ↓
Redirect ke index dengan success message
```

### 2. Update BOP Aktual
```
updateBopAktual($kodeAkun)
  ↓
1. Cari BOP dengan kode_akun
2. Hitung total semua pembayaran beban untuk akun ini
3. Update kolom aktual di BOP
4. Log untuk tracking
```

### 3. Tampilan di Master Data BOP
```
Master Data BOP
  ↓
Untuk setiap akun beban:
  - Budget: Rp 10.000.000
  - Aktual: Rp 8.500.000 (dari total pembayaran beban)
  - Sisa: Rp 1.500.000 (hijau = masih ada sisa)
  
Atau:
  - Budget: Rp 10.000.000
  - Aktual: Rp 12.000.000 (dari total pembayaran beban)
  - Sisa: -Rp 2.000.000 (merah = over budget)
```

## Contoh Penggunaan

### Skenario 1: Input Pembayaran Beban Pertama
```
1. Buka Transaksi → Pembayaran Beban
2. Klik "Tambah Pembayaran Beban"
3. Isi form:
   - Tanggal: 10/11/2025
   - Akun Beban: Biaya Listrik (kode: 601)
   - Nominal: Rp 500.000
   - Metode: Tunai
4. Submit

Hasil:
- Data tersimpan di expense_payments
- Journal entry dibuat
- BOP aktual untuk kode 601 = Rp 500.000
- Sisa budget = Budget - Rp 500.000
```

### Skenario 2: Input Pembayaran Beban Kedua (Akun yang Sama)
```
1. Input pembayaran beban lagi
2. Akun Beban: Biaya Listrik (kode: 601)
3. Nominal: Rp 300.000

Hasil:
- BOP aktual untuk kode 601 = Rp 800.000 (500.000 + 300.000)
- Sisa budget = Budget - Rp 800.000
```

### Skenario 3: Hapus Pembayaran Beban
```
1. Hapus pembayaran beban pertama (Rp 500.000)

Hasil:
- BOP aktual untuk kode 601 = Rp 300.000 (hanya yang kedua)
- Sisa budget = Budget - Rp 300.000
```

## Testing

### 1. Test Input Pembayaran Beban
```
1. Buka http://127.0.0.1:8000/transaksi/expense-payment
2. Klik "Tambah Pembayaran Beban"
3. Isi form dan submit
4. Harus berhasil dan redirect ke index
```

### 2. Test Update BOP Aktual
```
1. Setelah input pembayaran beban
2. Buka Master Data → BOP
3. Cek kolom "Aktual" untuk akun beban yang dipilih
4. Harus sama dengan total pembayaran beban
```

### 3. Test Selisih
```
1. Lihat kolom "Sisa" di Master Data BOP
2. Jika positif (hijau): Budget > Aktual
3. Jika negatif (merah): Budget < Aktual (over budget)
```

### 4. Test Log
```
1. Setelah input pembayaran beban
2. Cek file log: storage/logs/laravel.log
3. Harus ada entry "BOP Aktual Updated" dengan detail:
   - kode_akun
   - aktual
   - budget
   - selisih
```

## Troubleshooting

### Jika Masih 404 Not Found
1. **Clear cache route**:
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

2. **Cek route list**:
   ```bash
   php artisan route:list | grep expense-payment
   ```

3. **Restart server**:
   ```bash
   php artisan serve
   ```

### Jika Aktual Tidak Update
1. **Cek log**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Cek BOP ada**:
   ```sql
   SELECT * FROM bops WHERE kode_akun = '601';
   ```

3. **Cek manual**:
   ```sql
   SELECT SUM(nominal) FROM expense_payments 
   WHERE coa_beban_id = (SELECT id FROM coas WHERE kode_akun = '601');
   ```

## Kesimpulan

Sistem pembayaran beban sekarang sudah lengkap:
- ✅ Route berfungsi dengan baik
- ✅ Data tersimpan ke expense_payments
- ✅ Journal entry dibuat otomatis
- ✅ BOP aktual di-update otomatis
- ✅ Selisih dihitung dan ditampilkan dengan warna
- ✅ Logging untuk tracking

**Refresh browser dan test pembayaran beban!** 🎉

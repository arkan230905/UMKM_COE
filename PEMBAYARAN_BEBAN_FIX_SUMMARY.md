# FIX: Pembayaran Beban - Akun Salah di Jurnal Umum

## MASALAH
Pembayaran Beban menampilkan akun yang salah di Jurnal Umum:
- **28/04/2026 - Pembayaran Beban Sewa**:
  - Di halaman transaksi: 551 - BOP Sewa Tempat ✓
  - Di Jurnal Umum: 550 - BOP Listrik ✗

- **29/04/2026 - Pembayaran Beban Listrik**:
  - Di halaman transaksi: 550 - BOP Listrik ✓
  - Di Jurnal Umum: 550 - BOP Listrik ✓

## ROOT CAUSE
Ada **2 tempat yang membuat journal entry** untuk pembayaran beban:

### 1. ExpensePaymentController::store() (SALAH)
```php
// Membuat journal entry dengan akun dari DROPDOWN
$journal->post(
    $request->tanggal, 
    'expense_payment', 
    (int)$pembayaran->id, 
    'Pembayaran Beban: ' . $bebanOperasional->nama_beban, 
    [
        ['code' => $akunBeban->kode_akun, 'debit' => $request->jumlah, 'credit' => 0],
        ['code' => $akunKas->kode_akun, 'debit' => 0, 'credit' => $request->jumlah],
    ]
);
```

### 2. ExpensePayment Model boot() (BENAR)
```php
// Membuat journal entry dengan akun dari DATABASE
public static function createJournalFromExpensePayment($expensePayment): void
{
    // Menggunakan $expensePayment->coa_beban_id dari database
    $lines[] = [
        'code' => $expenseAccount,
        'debit' => $amount,
        'credit' => 0,
    ];
}
```

**Masalahnya**: Jika user memilih akun yang berbeda di dropdown, akan ada **2 journal entries yang berbeda**!

## SOLUSI

### 1. Hapus Manual Journal Entry Creation di Controller
**File**: `app/Http/Controllers/ExpensePaymentController.php`

**Sebelum** (di method `store()`):
```php
// Jurnal: Dr Expense ; Cr Cash/Bank
$journal->post(
    $request->tanggal, 
    'expense_payment', 
    (int)$pembayaran->id, 
    'Pembayaran Beban: ' . $bebanOperasional->nama_beban, 
    [
        ['code' => $akunBeban->kode_akun, 'debit' => $request->jumlah, 'credit' => 0],
        ['code' => $akunKas->kode_akun, 'debit' => 0, 'credit' => $request->jumlah],
    ]
);
```

**Sesudah**:
```php
// NOTE: Journal entry akan dibuat otomatis oleh ExpensePayment model boot() method
// Jangan membuat journal entry di sini untuk menghindari double entry
```

**Sebelum** (di method `update()`):
```php
// Hapus jurnal lama dan buat baru
$acc = \App\Models\Account::where('code', $oldCashCode)->first();
if ($acc) {
    \App\Models\JournalEntry::where('ref_type', 'expense_payment')
        ->where('ref_id', $row->id)
        ->delete();
}

// Jurnal baru: Dr Expense ; Cr Cash/Bank
$journal->post($request->tanggal, 'expense_payment', (int)$row->id, 'Pembayaran Beban - '.$bebanOperasional->coa->nama_akun, [
    ['code'=>$bebanOperasional->coa->kode_akun, 'debit'=>(float)$request->nominal_pembayaran, 'credit'=>0],
    ['code'=>$request->coa_kasbank, 'debit'=>0, 'credit'=>(float)$request->nominal_pembayaran],
]);
```

**Sesudah**:
```php
// NOTE: Journal entry akan diupdate otomatis oleh ExpensePayment model boot() method
// Jangan membuat journal entry di sini untuk menghindari double entry
```

### 2. Perbaiki Data yang Sudah Salah
Jalankan script: `http://127.0.0.1:8000/fix-pembayaran-beban-journals.php`

Script ini akan:
1. Menghapus journal entries yang salah
2. Membuat journal entries yang benar berdasarkan data di `expense_payments` table

## FILE YANG DIPERBAIKI
✅ `app/Http/Controllers/ExpensePaymentController.php`
- Method `store()` - Hapus manual journal entry creation
- Method `update()` - Hapus manual journal entry creation

## VERIFIKASI
1. Jalankan: `http://127.0.0.1:8000/fix-pembayaran-beban-journals.php`
2. Buka Jurnal Umum
3. Cari Pembayaran Beban 28/04/2026 dan 29/04/2026
4. Verifikasi akun sudah benar:
   - 28/04/2026: 551 - BOP Sewa Tempat ✓
   - 29/04/2026: 550 - BOP Listrik ✓

## DAMPAK
- ✅ Pembayaran Beban akan menampilkan akun yang benar di Jurnal Umum
- ✅ Tidak ada lagi double journal entries
- ✅ Buku Besar akan akurat
- ✅ Laporan keuangan akan benar

## CATATAN PENTING
Ini adalah **bug di logic**, bukan masalah data. Masalahnya adalah ada 2 tempat yang membuat journal entry dengan logika yang berbeda. Solusinya adalah menghapus salah satu dan biarkan hanya 1 tempat yang membuat journal entry (model boot method).

Setelah fix ini, pastikan:
1. Jangan pernah membuat journal entry manual di controller untuk expense_payment
2. Biarkan model boot() method yang menangani semua journal entry creation
3. Ini memastikan konsistensi antara data di `expense_payments` dan `jurnal_umum`

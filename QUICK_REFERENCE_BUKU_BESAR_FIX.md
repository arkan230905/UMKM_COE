# QUICK REFERENCE: Buku Besar Fix

## THE ISSUE
❌ Penggajian Dedi Gunawan tidak muncul di Buku Besar Kas

## THE CAUSE
Code mengecualikan semua penggajian dari tampilan Buku Besar:
```php
->whereNotIn('ju.tipe_referensi', [..., 'penggajian'])  // ❌ WRONG
```

## THE FIX
Hapus 'penggajian' dari exclusion list:
```php
->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment'])  // ✅ CORRECT
```

## FILES CHANGED
| File | Line | Status |
|------|------|--------|
| `app/Http/Controllers/AkuntansiController.php` | 578 | ✅ Fixed |
| `app/Exports/BukuBesarExport.php` | 200 | ✅ Fixed |

## VERIFICATION
Run: `http://127.0.0.1:8000/verify-dedi-fix.php`

Or check manually:
1. Buka Buku Besar Kas (COA 112)
2. Cari 26/04/2026
3. Penggajian Dedi Gunawan harus muncul ✅

## RESULT
✅ Penggajian Dedi Gunawan akan muncul di Buku Besar
✅ Buku Besar sekarang sesuai dengan Jurnal Umum
✅ Laporan keuangan akan akurat

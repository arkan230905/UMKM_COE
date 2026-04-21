# Visual Step-by-Step Completion Guide

## 🎯 Complete Both Tasks in 5 Minutes

---

## STEP 1: Open Status Dashboard

```
Open your browser and go to:
http://127.0.0.1:8000/status-check.php
```

**What you'll see:**
```
┌─────────────────────────────────────────────────────────┐
│  🔍 System Status Check                                 │
│  Last updated: 2026-04-21 XX:XX:XX                      │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  📋 TASK 3: Penjualan Payment Flow                      │
│  Status: ✗ PENDING                                      │
│  ┌─────────────────────────────────────────────────┐   │
│  │ bukti_pembayaran:    ✗ MISSING                  │   │
│  │ catatan_pembayaran:  ✗ MISSING                  │   │
│  └─────────────────────────────────────────────────┘   │
│  [Run Migration Now]                                    │
│                                                         │
│  📊 TASK 4: BTKL & BOP Journal Fix                      │
│  Status: ✗ NEEDS FIX                                    │
│  Found 4 entries with incorrect debit values            │
│  [Fix BTKL & BOP Positions Now]                         │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## STEP 2: Complete Task 3 - Payment Flow Migration

### Click the "Run Migration Now" Button

```
[Run Migration Now]
```

**What happens:**
1. Page redirects to: `http://127.0.0.1:8000/check-migration.php`
2. Script checks for missing columns
3. Script automatically adds them
4. You see success message:

```
✓ Update berhasil!
Baris yang diupdate: 2

✓ Selesai! Refresh halaman jurnal-umum untuk melihat perubahan.
```

**Result:**
```
✅ bukti_pembayaran column ADDED
✅ catatan_pembayaran column ADDED
```

---

## STEP 3: Complete Task 4 - BTKL & BOP Journal Fix

### Go Back to Status Dashboard

```
http://127.0.0.1:8000/status-check.php
```

### Click the "Fix BTKL & BOP Positions Now" Button

```
[Fix BTKL & BOP Positions Now]
```

**What happens:**
1. Page redirects to: `http://127.0.0.1:8000/check-btkl-bop.php`
2. Script checks for incorrect entries
3. Script automatically fixes them
4. You see success message:

```
✓ Fixed 4 entries

After fix:
═══════════════════════════════════════════════════════════
Tanggal      | Memo                    | Kode | Debit | Kredit
═══════════════════════════════════════════════════════════
2026-04-17   | Alokasi BTKL & BOP      | 52   | 0     | 132.800
2026-04-17   | Alokasi BTKL & BOP      | 53   | 0     | 545.118
2026-04-17   | Alokasi BTKL & BOP      | 117  | 677.918 | 0
...
═══════════════════════════════════════════════════════════
```

**Result:**
```
✅ BTKL (52) moved to KREDIT column
✅ BOP (53) moved to KREDIT column
✅ Barang Dalam Proses (117) in DEBIT column
```

---

## STEP 4: Verify Everything Works

### Go Back to Status Dashboard

```
http://127.0.0.1:8000/status-check.php
```

**What you should see now:**
```
┌─────────────────────────────────────────────────────────┐
│  🔍 System Status Check                                 │
│  Last updated: 2026-04-21 XX:XX:XX                      │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  📋 TASK 3: Penjualan Payment Flow                      │
│  Status: ✓ COMPLETE                                     │
│  ┌─────────────────────────────────────────────────┐   │
│  │ bukti_pembayaran:    ✓ EXISTS (VARCHAR 255)     │   │
│  │ catatan_pembayaran:  ✓ EXISTS (LONGTEXT)        │   │
│  └─────────────────────────────────────────────────┘   │
│  ✓ All payment proof columns are in place               │
│                                                         │
│  📊 TASK 4: BTKL & BOP Journal Fix                      │
│  Status: ✓ COMPLETE                                     │
│  ✓ All BTKL & BOP entries are correctly positioned      │
│                                                         │
│  📈 Overall Status                                      │
│  ✓ ALL TASKS COMPLETE                                   │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## STEP 5: Test the Features

### Test Payment Flow

1. Open: `http://127.0.0.1:8000/transaksi/penjualan/create`

2. Add some items to the cart

3. Click the "Bayar" button

4. You should see the payment confirmation page:
```
┌─────────────────────────────────────────────────────────┐
│  💳 Konfirmasi Pembayaran                               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Ringkasan Pesanan                                      │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Produk | Qty | Harga | Subtotal                │   │
│  │ ...                                             │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│  Metode Pembayaran: [Tunai] atau [Transfer Bank]       │
│                                                         │
│  Pembayaran Tunai:                                      │
│  Jumlah Uang Diterima: [_____________]                 │
│  Kembalian: Rp 0                                        │
│  [Kembali] [Konfirmasi Pembayaran]                      │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

5. Test both payment methods:
   - **Cash**: Enter amount, verify kembalian calculates
   - **Transfer**: Upload file, verify preview shows

### Test Journal Fix

1. Open: `http://127.0.0.1:8000/akuntansi/jurnal-umum`

2. Filter by "Produksi - BTKL & BOP"

3. Look for entries from 17/04/2026 and 18/04/2026

4. Verify the positions:
```
Alokasi BTKL & BOP ke Produksi:
├─ Debit:  117 (Barang Dalam Proses)     Rp 677.918
├─ Kredit: 52 (BTKL)                     Rp 132.800
└─ Kredit: 53 (BOP)                      Rp 545.118
```

---

## ✅ Completion Checklist

After completing all steps, verify:

### Task 3: Payment Flow
- [ ] Status page shows ✓ COMPLETE
- [ ] Can access payment page after clicking "Bayar"
- [ ] Cash payment method works
- [ ] Transfer payment method works
- [ ] File upload works for transfer
- [ ] Image preview shows uploaded file

### Task 4: Journal Fix
- [ ] Status page shows ✓ COMPLETE
- [ ] Journal entries show correct positions
- [ ] BTKL (52) in KREDIT column
- [ ] BOP (53) in KREDIT column
- [ ] Barang Dalam Proses (117) in DEBIT column

---

## 🎉 You're Done!

All tasks are now complete. The system is ready for production use.

---

## 📊 Time Breakdown

| Step | Task | Time |
|------|------|------|
| 1 | Open status dashboard | 30 sec |
| 2 | Run migration | 1 min |
| 3 | Fix journal entries | 1 min |
| 4 | Verify on dashboard | 30 sec |
| 5 | Test features | 2 min |
| **Total** | **All tasks** | **~5 min** |

---

## 🆘 Troubleshooting

### If web scripts don't work:
1. Check if MySQL is running
2. Verify database connection
3. Use phpMyAdmin to run SQL manually

### If changes don't appear:
1. Refresh page (Ctrl+F5)
2. Clear browser cache
3. Check status page again

### If you see errors:
1. Read error message carefully
2. Check database connection
3. Try manual SQL via phpMyAdmin

---

## 📞 Need Help?

1. Check: `COMPLETION_GUIDE.md` - Detailed instructions
2. Check: `TASK_STATUS_SUMMARY.md` - Status details
3. Check: `QUICK_REFERENCE.md` - Quick lookup

---

**Ready? Open your browser and go to:**
```
http://127.0.0.1:8000/status-check.php
```

**Then click the "Run Now" buttons!**

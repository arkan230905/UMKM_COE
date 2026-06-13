# CHECKLIST & TROUBLESHOOTING: Jurnal Penjualan Tidak Masuk ke Jurnal Umum

## Quick Diagnosis Checklist

### Step 1: Verifikasi Data Penjualan
- [ ] Cek apakah penjualan dengan `payment_status='paid'` ada di database?
  ```sql
  SELECT id, nomor_penjualan, payment_status FROM penjualans WHERE payment_status='paid';
  ```

- [ ] Cek apakah penjualan memiliki detail (items)?
  ```sql
  SELECT COUNT(*) FROM penjualan_details WHERE penjualan_id = ?;
  ```

- [ ] Cek grand_total dan jumlah penjualan
  ```sql
  SELECT id, nomor_penjualan, grand_total, biaya_ppn, biaya_ongkir FROM penjualans WHERE id = ?;
  ```

### Step 2: Verifikasi Jurnal Masuk
- [ ] Cek apakah jurnal sudah ada?
  ```sql
  SELECT COUNT(*) FROM jurnal_umum WHERE referensi = ? AND tipe_referensi = 'sale';
  ```

- [ ] Lihat detail jurnal entries
  ```sql
  SELECT id, tanggal, keterangan, debit, kredit FROM jurnal_umum 
  WHERE referensi = ? AND tipe_referensi = 'sale' ORDER BY id;
  ```

- [ ] Verifikasi balance (debit = kredit)
  ```sql
  SELECT SUM(debit) as total_debit, SUM(kredit) as total_kredit 
  FROM jurnal_umum WHERE referensi = ? AND tipe_referensi = 'sale';
  ```

### Step 3: Jika Jurnal TIDAK Ada - Cek Error Log
- [ ] Cek Laravel log untuk error
  ```bash
  tail -200 storage/logs/laravel.log | grep -i "journal\|penjualan\|error"
  ```

- [ ] Cari pesan spesifik
  ```bash
  grep "Failed to create journal" storage/logs/laravel.log
  grep "Journal validation failed" storage/logs/laravel.log
  grep "Akun berikut belum tersedia" storage/logs/laravel.log
  ```

### Step 4: Verifikasi COA (Kode Akun)
- [ ] Cek apakah COA untuk Kas ada?
  ```sql
  SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%Kas%' AND tipe_akun='Asset';
  ```

- [ ] Cek apakah COA untuk Penjualan ada?
  ```sql
  SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%Penjualan%' AND tipe_akun='Revenue';
  ```

- [ ] Cek COA untuk HPP
  ```sql
  SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%HPP%' AND tipe_akun='Expense';
  ```

- [ ] Cek COA untuk Persediaan
  ```sql
  SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%Persediaan%' AND tipe_akun='Asset';
  ```

- [ ] Cek COA untuk PPN Keluaran
  ```sql
  SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%PPN%' AND tipe_akun='Liability';
  ```

### Step 5: Cek Status Pembayaran
- [ ] Verifikasi payment_status diubah dari pending menjadi paid?
  ```sql
  SELECT id, nomor_penjualan, payment_status, created_at, updated_at FROM penjualans WHERE id = ?;
  ```

- [ ] Cek payment_confirmed_at timestamp
  ```sql
  SELECT payment_confirmed_at FROM penjualans WHERE id = ?;
  ```

---

## Common Problems & Solutions

### ❌ Problem 1: Jurnal Tidak Ada (COA Missing)
**Error Message:**
```
Jurnal penjualan tidak dapat dibuat. Akun berikut belum tersedia:
• Akun Kas
• Akun Penjualan
```

**Root Cause:** Salah satu atau lebih akun COA tidak terdaftar atau tidak lengkap

**Solution:**
1. Buka Filament → COA Management
2. Pastikan akun berikut ada:
   - Kas (Asset, kode_akun: 112)
   - Penjualan (Revenue)
   - HPP (Expense)
   - Persediaan (Asset)
   - PPN Keluaran (Liability)

3. Rebuild jurnal:
   ```bash
   php artisan rebuild:jurnal-penjualan {penjualan_id}
   ```

---

### ❌ Problem 2: Payment Status Masih 'pending'
**Indikasi:** Penjualan ada tapi `payment_status` masih 'pending'

**Root Cause:** Pembayaran belum dikonfirmasi di halaman payment confirmation

**Solution:**
1. Go to: Transaksi → Penjualan → Pilih penjualan
2. Click "Konfirmasi Pembayaran"
3. Isi detail pembayaran dan klik "Confirm"
4. Sistem akan otomatis membuat jurnal

---

### ❌ Problem 3: Detail Penjualan Kosong
**Indikasi:** `penjualan_details` tidak memiliki record

**Root Cause:** Item produk tidak tersimpan saat membuat penjualan

**Solution:**
1. Cek apakah ada error saat menambah item
2. Pastikan produk terdaftar dan memiliki stok
3. Manual insert jika diperlukan:
   ```sql
   INSERT INTO penjualan_details (penjualan_id, produk_id, jumlah, harga_satuan, subtotal)
   VALUES (?, ?, quantity, price, subtotal);
   ```
4. Rebuild jurnal

---

### ❌ Problem 4: Jurnal Imbalance (Debit ≠ Kredit)
**Indikasi:** Total Debit ≠ Total Kredit

**Root Cause:** Error dalam perhitungan journal entries atau manual delete

**Solution:**
1. Identifikasi baris mana yang bermasalah
2. Delete jurnal lama dan rebuild:
   ```sql
   DELETE FROM jurnal_umum WHERE referensi = ? AND tipe_referensi = 'sale';
   ```
   ```bash
   php artisan rebuild:jurnal-penjualan {penjualan_id}
   ```

---

### ❌ Problem 5: User_id Tidak Konsisten
**Indikasi:** Jurnal dengan user_id berbeda atau NULL

**Root Cause:** Multi-tenant isolation issue atau permission problem

**Solution:**
1. Cek user_id di penjualan
2. Ensure penjualan memiliki user_id yang sama dengan user yang login
3. Audit dan fix jika ada orphaned records

---

## Automated Verification Command

Gunakan command yang sudah dibikin:

```bash
# Verify semua penjualan dengan payment_status='paid'
php artisan verify:penjualan-journal

# Filter by user_id
php artisan verify:penjualan-journal --user-id=10

# Show detailed journal entries
php artisan verify:penjualan-journal --show-details

# Combine options
php artisan verify:penjualan-journal --user-id=10 --show-details
```

**Output Expected:**
```
✅ Penjualan ID 2 (SJ-20260611-001): OK
   Jurnal entries for Penjualan SJ-20260611-001:
     • Penerimaan tunai penjualan Ayam Crispy Macdi - Kas | D: Rp 1,942,500.00 | K: -
     • Pendapatan penjualan - Ayam Crispy Macdi | D: - | K: Rp 1,750,000.00
     ...
```

---

## Flow Diagram: Pembuatan Jurnal Penjualan

```
┌─────────────────────────────────────────┐
│ User Submit Form: Konfirmasi Pembayaran │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ PenjualanController::confirmPayment()   │
│  1. Validate payment data               │
│  2. Create penjualan record             │
│  3. Create penjualan_details            │
│  4. Update payment_status = 'paid'      │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ Penjualan Model Observer (updated)      │
│  Detect: payment_status changed to paid │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ JournalService::createJournalFromPenjualan
│                                         │
│  1. Validate COA accounts               │
│  2. Prepare journal lines:              │
│     - Debit: Kas/Bank/Piutang          │
│     - Credit: Penjualan                │
│     - Credit: PPN Keluaran             │
│     - Debit: HPP                       │
│     - Credit: Persediaan               │
│  3. Verify balance (debit = credit)    │
│  4. Post to jurnal_umum                │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ ✅ Jurnal Successfully Posted!          │
│ Entries visible in jurnal_umum table    │
└─────────────────────────────────────────┘
```

---

## Testing Checklist

### For Developers

- [ ] Create test penjualan with payment_method='cash'
- [ ] Verify journal created with correct debit (Kas) and credit (Penjualan)
- [ ] Test with payment_method='transfer'
- [ ] Test with payment_method='credit'
- [ ] Test dengan PPN calculation
- [ ] Test dengan diskon
- [ ] Test dengan ongkir
- [ ] Verify HPP entries untuk produk

### For Data Entry

- [ ] Ensure semua COA sudah terdaftar
- [ ] Test pada demo environment dulu
- [ ] Verify hasil jurnal di jurnal_umum setelah penjualan
- [ ] Report jika ada error atau unexpected result

---

## Emergency: Manual Journal Creation

Jika sistem gagal membuat journal, bisa dibuat manual:

```sql
-- Step 1: Verify penjualan data
SELECT * FROM penjualans WHERE id = 2;
SELECT * FROM penjualan_details WHERE penjualan_id = 2;

-- Step 2: Get COA IDs
SELECT id, kode_akun, nama_akun FROM coas 
WHERE kode_akun IN ('112', '4001', '3001', '1401', '2101');

-- Step 3: Create journal entries manually
INSERT INTO jurnal_umum (user_id, coa_id, tanggal, keterangan, debit, kredit, tipe_referensi, referensi, created_at, updated_at)
VALUES 
  (10, (SELECT id FROM coas WHERE kode_akun='112'), '2026-06-11', 'Penerimaan tunai penjualan', 1942500.00, 0, 'sale', 2, NOW(), NOW()),
  (10, (SELECT id FROM coas WHERE kode_akun='4001'), '2026-06-11', 'Pendapatan penjualan', 0, 1750000.00, 'sale', 2, NOW(), NOW()),
  -- ... additional entries
;

-- Step 4: Verify balance
SELECT SUM(debit), SUM(kredit) FROM jurnal_umum WHERE referensi=2 AND tipe_referensi='sale';
```

⚠️ **Note:** Manual entry hanya sebagai backup. Sebaiknya gunakan automated method.

---

## Contact & Support

Untuk bantuan lebih lanjut:
1. Cek documentation ini
2. Run verify command
3. Check Laravel logs
4. Report issue dengan detail informasi penjualan_id dan error message


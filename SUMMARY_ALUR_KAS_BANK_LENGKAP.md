# 📊 SUMMARY LENGKAP ALUR KAS & BANK

## Tujuan
Memastikan SEMUA transaksi yang melibatkan kas/bank tercatat dengan benar di Laporan Kas dan Bank.

---

## ✅ ALUR YANG SUDAH BENAR

### 1. PEMBELIAN
**Uang Keluar** (Kas/Bank Berkurang)

**Jurnal:**
```
Dr 1104 (Persediaan Bahan Baku) Rp X
Cr [Sumber Dana yang dipilih] Rp X
```

**Pilihan Sumber Dana:**
- Tunai: 1101 (Kas Kecil) atau 101 (Kas)
- Transfer: 1102 (Kas di Bank) atau 102 (Bank)
- Kredit: 2101 (Hutang Usaha)

**Efek di Laporan Kas Bank:**
- Transaksi Keluar: +Rp X
- Saldo Akhir: -Rp X

**Status:** ✅ SUDAH BENAR

---

### 2. PENJUALAN
**Uang Masuk** (Kas/Bank Bertambah)

**Jurnal:**
```
Dr [Sumber Dana yang dipilih] Rp X
Cr 4101 (Pendapatan) Rp X

Dr 5001 (HPP) Rp Y
Cr 1107 (Persediaan Barang Jadi) Rp Y
```

**Pilihan Sumber Dana:**
- Tunai: 1101 (Kas Kecil) atau 101 (Kas)
- Transfer: 1102 (Kas di Bank) atau 102 (Bank)
- Kredit: 1103 (Piutang Usaha)

**Efek di Laporan Kas Bank:**
- Transaksi Masuk: +Rp X
- Saldo Akhir: +Rp X

**Status:** ✅ SUDAH BENAR

---

## ⚠️ ALUR YANG PERLU DICEK

### 3. PEMBAYARAN BEBAN
**Uang Keluar** (Kas/Bank Berkurang)

**Jurnal:**
```
Dr [Akun Beban] Rp X
Cr [coa_kasbank dari form] Rp X
```

**Form:**
- Metode Bayar: Cash atau Bank
- COA Kas/Bank: Dropdown (101, 102, 1101, 1102, 1103)

**Yang Perlu Dicek:**
1. Apakah jurnal tercatat? → Cek Jurnal Umum
2. Akun kredit-nya apa? → Harusnya sesuai pilihan
3. Apakah akun tersebut ada di tabel accounts? → Cek database
4. Apakah Laporan Kas Bank membaca akun tersebut? → Cek filter

**Kemungkinan Masalah:**
- ❌ Akun yang dipilih tidak ada di filter Laporan Kas Bank
- ❌ Akun yang dipilih belum ada di tabel accounts
- ❌ Jurnal tidak tercatat

---

### 4. PELUNASAN UTANG
**Uang Keluar** (Kas/Bank Berkurang)

**Jurnal:**
```
Dr 2101 (Hutang Usaha) Rp X
Cr [Kas/Bank] Rp X
```

**Status:** ⚠️ PERLU DICEK

---

### 5. PENGGAJIAN
**Uang Keluar** (Kas/Bank Berkurang)

**Jurnal:**
```
Dr 2103 (Hutang Gaji BTKL) atau 2104 (Hutang Gaji BTKTL) Rp X
Cr [Kas/Bank] Rp X
```

**Status:** ⚠️ PERLU DICEK

---

## 🔍 DEBUGGING CHECKLIST

### Jika Nominal Tidak Berkurang di Laporan Kas Bank:

#### Step 1: Cek Jurnal Umum
```
http://127.0.0.1:8000/akuntansi/jurnal-umum
```
- [ ] Apakah ada jurnal dengan ref_type = "expense_payment"?
- [ ] Akun kredit-nya apa?
- [ ] Nominal-nya berapa?

#### Step 2: Cek Akun di Database
```sql
SELECT * FROM accounts WHERE code IN ('101', '102', '1101', '1102', '1103');
```
- [ ] Apakah semua akun ada?
- [ ] Apakah nama akun benar?

#### Step 3: Cek Journal Lines
```sql
SELECT jl.*, a.code, a.name, je.tanggal, je.ref_type
FROM journal_lines jl
JOIN accounts a ON jl.account_id = a.id
JOIN journal_entries je ON jl.journal_entry_id = je.id
WHERE a.code IN ('101', '102', '1102')
AND je.ref_type = 'expense_payment'
ORDER BY je.id DESC
LIMIT 10;
```
- [ ] Apakah ada transaksi?
- [ ] Apakah credit > 0?

#### Step 4: Cek Filter Laporan Kas Bank
```php
// Di LaporanKasBankController.php
$akunKasBank = Coa::where(function($query) {
    $query->whereIn('kode_akun', ['1101', '101', '1102', '102']);
})
```
- [ ] Apakah filter sudah benar?
- [ ] Apakah akun yang dipakai ada di filter?

---

## 🚀 SOLUSI CEPAT

### Jika Akun Bank (102 atau 1102) Tidak Muncul di Laporan:

**Kemungkinan:** Akun tersebut tidak ada di COA atau tidak ada di filter.

**Solusi:**
```bash
# 1. Pastikan akun ada di COA
php artisan db:seed --class=CompleteCoaSeeder

# 2. Sync ke accounts
php artisan db:seed --class=SyncAccountsFromCoaSeeder

# 3. Pastikan akun kas/bank ada
php artisan db:seed --class=EnsureKasBankAccountsSeeder

# 4. Clear cache
php artisan cache:clear
php artisan route:clear
```

---

## 📝 CATATAN PENTING

### Akun Kas & Bank yang Harus Ada:
1. **1101** - Kas Kecil
2. **101** - Kas
3. **1102** - Kas di Bank ← **PENTING UNTUK TRANSFER**
4. **102** - Bank ← **PENTING UNTUK TRANSFER**
5. **1103** - Piutang Usaha (untuk penjualan kredit)

### Filter Laporan Kas Bank:
```php
whereIn('kode_akun', ['1101', '101', '1102', '102'])
```

**TIDAK termasuk:**
- ❌ 1103 (Piutang Usaha) - Bukan kas/bank
- ❌ 1104 (Persediaan Bahan Baku) - Bukan kas/bank
- ❌ 1107 (Persediaan Barang Jadi) - Bukan kas/bank

---

## 🎯 ACTION PLAN

### Untuk Memastikan Pembayaran Beban Bekerja:

1. **Cek Jurnal Umum** - Pastikan transaksi tercatat
2. **Cek Akun** - Pastikan akun bank ada
3. **Cek Filter** - Pastikan filter Laporan Kas Bank benar
4. **Test Ulang** - Buat pembayaran beban baru

### Jika Masih Tidak Berkurang:

**Kemungkinan:**
- Akun yang dipilih di form bukan akun kas/bank (misal: akun beban)
- Akun yang dipilih tidak ada di filter Laporan Kas Bank
- Jurnal tidak tercatat (error saat post)

**Solusi:**
- Pastikan pilih akun 102 (Bank) atau 1102 (Kas di Bank)
- Cek Jurnal Umum untuk memastikan transaksi tercatat
- Refresh Laporan Kas Bank

---

## 🔧 COMMAND LENGKAP

```bash
# Jalankan semua ini untuk memastikan sistem benar
php artisan db:seed --class=CompleteCoaSeeder
php artisan db:seed --class=SyncAccountsFromCoaSeeder
php artisan db:seed --class=EnsureKasBankAccountsSeeder
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

Setelah itu test lagi!

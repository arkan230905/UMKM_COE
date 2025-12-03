# CRITICAL FIX: Payment Method & Akuntansi

## 🚨 MASALAH KRITIS YANG DITEMUKAN

### Masalah Saat Ini:
```php
// Di PenjualanController.php line 149 & 226
$cashOrReceivable = $request->payment_method === 'credit' ? '102' : '101';
```

**SALAH TOTAL!** Ini menyebabkan:
1. Payment method hanya 2 pilihan: `cash` atau `credit`
2. Tidak ada pilihan `transfer` untuk Bank
3. Kode akun `102` digunakan untuk `credit`, padahal `102` adalah Bank!
4. Semua transaksi transfer masuk ke Piutang, bukan Bank

### Struktur Akun yang Benar:
```
101 = Kas (Cash)
102 = Bank (Transfer/Bank)
103 = Piutang Usaha (Credit/Kredit)
```

### Yang Seharusnya Terjadi:

#### Penjualan Cash (Tunai):
```
Dr. Kas (101)              Rp 1.000.000
Cr. Pendapatan Penjualan   Rp 1.000.000
```

#### Penjualan Transfer (Bank):
```
Dr. Bank (102)             Rp 1.000.000
Cr. Pendapatan Penjualan   Rp 1.000.000
```

#### Penjualan Kredit (Piutang):
```
Dr. Piutang Usaha (103)    Rp 1.000.000
Cr. Pendapatan Penjualan   Rp 1.000.000
```

## ✅ SOLUSI LENGKAP

### 1. Update Migration untuk Payment Method

Tambahkan pilihan `transfer`:

```php
// database/migrations/xxxx_update_payment_method_columns.php
Schema::table('penjualans', function (Blueprint $table) {
    $table->enum('payment_method', ['cash', 'transfer', 'credit'])->change();
});

Schema::table('pembelians', function (Blueprint $table) {
    $table->enum('payment_method', ['cash', 'transfer', 'credit'])->change();
});
```

### 2. Update PenjualanController

```php
// SEBELUM (SALAH):
$cashOrReceivable = $request->payment_method === 'credit' ? '102' : '101';

// SESUDAH (BENAR):
$accountCode = match($request->payment_method) {
    'cash' => '101',      // Kas
    'transfer' => '102',  // Bank
    'credit' => '103',    // Piutang Usaha
    default => '101'
};

$journal->post($tanggal, 'sale', (int)$penjualan->id, 'Penjualan Produk', [
    ['code' => $accountCode, 'debit' => (float)$penjualan->total, 'credit' => 0],
    ['code' => '401', 'debit' => 0, 'credit' => (float)$penjualan->total],
]);
```

### 3. Update PembelianController

```php
// SEBELUM (SALAH):
$cashOrPayable = $request->payment_method === 'credit' ? '201' : '101';

// SESUDAH (BENAR):
$accountCode = match($request->payment_method) {
    'cash' => '101',      // Kas
    'transfer' => '102',  // Bank
    'credit' => '201',    // Utang Usaha
    default => '101'
};

$journal->post($tanggal, 'purchase', (int)$pembelian->id, 'Pembelian Bahan Baku', [
    ['code' => '121', 'debit' => (float)$pembelian->total_harga, 'credit' => 0],
    ['code' => $accountCode, 'debit' => 0, 'credit' => (float)$pembelian->total_harga],
]);
```

### 4. Update Form View

Tambahkan pilihan Transfer di form:

```blade
<select name="payment_method" class="form-control" required>
    <option value="">-- Pilih Metode Pembayaran --</option>
    <option value="cash">Cash (Tunai)</option>
    <option value="transfer">Transfer (Bank)</option>
    <option value="credit">Kredit (Tempo)</option>
</select>
```

### 5. Update Validation

```php
$request->validate([
    'payment_method' => 'required|in:cash,transfer,credit',
    // ...
]);
```

## 📊 Dampak Perbaikan

### Sebelum Perbaikan:
```
Penjualan Transfer Rp 10.000.000
❌ Masuk ke: Piutang Usaha (102) - SALAH!
❌ Laporan Kas & Bank: Bank = 0
```

### Setelah Perbaikan:
```
Penjualan Transfer Rp 10.000.000
✅ Masuk ke: Bank (102) - BENAR!
✅ Laporan Kas & Bank: Bank = Rp 10.000.000
```

## 🔧 Implementasi Step-by-Step

### Step 1: Buat Migration
```bash
php artisan make:migration update_payment_method_to_include_transfer
```

### Step 2: Isi Migration
```php
public function up()
{
    DB::statement("ALTER TABLE penjualans MODIFY COLUMN payment_method ENUM('cash', 'transfer', 'credit') NOT NULL");
    DB::statement("ALTER TABLE pembelians MODIFY COLUMN payment_method ENUM('cash', 'transfer', 'credit') NOT NULL");
}
```

### Step 3: Jalankan Migration
```bash
php artisan migrate
```

### Step 4: Update Controller
- PenjualanController.php
- PembelianController.php
- PenggajianController.php
- ExpensePaymentController.php
- PelunasanUtangController.php

### Step 5: Update View
- resources/views/transaksi/penjualan/create.blade.php
- resources/views/transaksi/pembelian/create.blade.php
- Dan form lainnya

### Step 6: Test
1. Buat penjualan cash → Cek Kas bertambah
2. Buat penjualan transfer → Cek Bank bertambah
3. Buat penjualan kredit → Cek Piutang bertambah
4. Lihat Laporan Kas & Bank → Harus akurat

## ⚠️ Data Lama

Untuk data yang sudah ada, perlu di-review:
```sql
-- Cek data penjualan dengan payment_method = 'credit'
SELECT * FROM penjualans WHERE payment_method = 'credit';

-- Jika seharusnya transfer, update:
UPDATE penjualans SET payment_method = 'transfer' WHERE id = [id_yang_seharusnya_transfer];

-- Kemudian re-generate jurnal
```

## 🎯 Checklist Perbaikan

- [ ] Buat migration untuk update enum
- [ ] Jalankan migration
- [ ] Update PenjualanController
- [ ] Update PembelianController
- [ ] Update PenggajianController
- [ ] Update ExpensePaymentController
- [ ] Update PelunasanUtangController
- [ ] Update semua form view
- [ ] Update validation rules
- [ ] Test penjualan cash
- [ ] Test penjualan transfer
- [ ] Test penjualan kredit
- [ ] Test pembelian cash
- [ ] Test pembelian transfer
- [ ] Test pembelian kredit
- [ ] Verify Laporan Kas & Bank
- [ ] Review data lama
- [ ] Re-generate jurnal jika perlu

---

**CRITICAL:** Ini adalah bug fundamental yang mempengaruhi seluruh akuntansi!
**PRIORITY:** HIGHEST - Harus diperbaiki segera!
**IMPACT:** Semua transaksi transfer salah masuk ke Piutang, bukan Bank!

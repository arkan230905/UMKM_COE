# 🔍 Debug: Jurnal Penjualan Tidak Masuk

## Perubahan yang Sudah Dilakukan

### 1. Enhanced Logging
- ✅ Setiap event (created/updated) di-log dengan detail lengkap
- ✅ Setiap journal creation di-log dengan trace penuh
- ✅ Error di-throw agar terlihat oleh user (tidak silent fail)

### 2. Improved Error Handling
- ✅ `confirmPayment` sekarang THROW exception jika journal gagal
- ✅ User akan tahu jika ada masalah
- ✅ Log detail tersimpan untuk debugging

### 3. Model Event Enhancement
- ✅ Log setiap event trigger dengan context lengkap
- ✅ Log perubahan payment_status
- ✅ Log hasil journal creation

---

## 🧪 Langkah Testing

### **Step 1: Pull Latest Code di Server**

```bash
cd /var/www/html
git pull origin main
```

### **Step 2: Clear Cache**

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### **Step 3: Set Log Permission**

```bash
chmod 777 storage/logs/laravel.log
# atau
truncate -s 0 storage/logs/laravel.log  # Clear log untuk fresh start
```

### **Step 4: Monitor Log Real-time**

Buka terminal baru dan jalankan:

```bash
tail -f /var/www/html/storage/logs/laravel.log
```

Biarkan terminal ini tetap terbuka untuk monitoring.

### **Step 5: Lakukan Transaksi Penjualan**

1. Buka aplikasi web
2. Buat transaksi penjualan baru
3. Konfirmasi pembayaran
4. Perhatikan log yang muncul di terminal

---

## 📊 Analisis Log

### **Log yang HARUS Muncul (Success Flow)**

```
[timestamp] local.INFO: Penjualan created event fired {"penjualan_id":20,"payment_status":"pending","user_id":47}
[timestamp] local.INFO: Penjualan updated event fired {"penjualan_id":20,"payment_status":"paid","payment_status_changed":true,"user_id":47}
[timestamp] local.INFO: Starting journal creation for penjualan {"penjualan_id":20,"nomor_penjualan":"SJ-20260617-002","user_id":47,"grand_total":100000}
[timestamp] local.INFO: Journal explicitly created for penjualan in confirmPayment {"penjualan_id":20,"nomor_penjualan":"SJ-20260617-002","user_id":47,"grand_total":100000}
[timestamp] local.INFO: Journal created successfully from updated event {"penjualan_id":20,"nomor_penjualan":"SJ-20260617-002"}
```

### **Log Jika Ada Error**

Jika journal GAGAL dibuat, akan ada error seperti:

```
[timestamp] local.ERROR: CRITICAL: Failed to create journal for penjualan in confirmPayment {"penjualan_id":20,"error":"COA dengan kode 'XXX' tidak ditemukan","trace":"..."}
```

atau

```
[timestamp] local.ERROR: Journal validation failed for penjualan {"penjualan_id":20,"missing_accounts":["Kas","Pendapatan Penjualan"],"error":"..."}
```

---

## 🔧 Troubleshooting Berdasarkan Log

### **Case 1: Event Tidak Ter-trigger**

**Log:**
```
[timestamp] local.INFO: Penjualan created event fired ...
# TAPI TIDAK ADA: Penjualan updated event fired
```

**Penyebab:** Event `updated` tidak ter-trigger

**Solusi:**
- Cek apakah `$penjualan->update(['payment_status' => 'paid'])` dipanggil
- Kemungkinan ada middleware atau observer yang block

### **Case 2: COA Tidak Ditemukan**

**Log:**
```
[timestamp] local.ERROR: Journal validation failed ... "missing_accounts":["Kas","Pendapatan Penjualan"]
```

**Penyebab:** Akun COA yang diperlukan belum dibuat

**Solusi:**
```bash
# Cek COA yang ada
php artisan tinker
```

```php
// Cek Kas (111, 112, 113)
\App\Models\Coa::whereIn('kode_akun', ['111', '112', '113'])->get(['id', 'kode_akun', 'nama_akun', 'user_id']);

// Cek Pendapatan Penjualan (410, 400)
\App\Models\Coa::whereIn('kode_akun', ['410', '400'])->get(['id', 'kode_akun', 'nama_akun', 'user_id']);

// Cek HPP (56, 560, 510)
\App\Models\Coa::whereIn('kode_akun', ['56', '560', '510'])->get(['id', 'kode_akun', 'nama_akun', 'user_id']);

// Cek Persediaan (116, 114)
\App\Models\Coa::whereIn('kode_akun', ['116', '114'])->get(['id', 'kode_akun', 'nama_akun', 'user_id']);
```

**Buat COA yang hilang:**

```php
// Contoh: Buat Kas jika tidak ada
\App\Models\Coa::create([
    'user_id' => 47,  // Ganti dengan user_id Anda
    'kode_akun' => '112',
    'nama_akun' => 'Kas',
    'tipe_akun' => 'Asset',
    'kategori_akun' => 'Kas & Bank',
    'saldo_normal' => 'Debit',
]);

// Contoh: Buat Pendapatan Penjualan
\App\Models\Coa::create([
    'user_id' => 47,
    'kode_akun' => '410',
    'nama_akun' => 'Pendapatan Penjualan',
    'tipe_akun' => 'Revenue',
    'kategori_akun' => 'Pendapatan',
    'saldo_normal' => 'Kredit',
]);

// Contoh: Buat HPP
\App\Models\Coa::create([
    'user_id' => 47,
    'kode_akun' => '56',
    'nama_akun' => 'Harga Pokok Penjualan',
    'tipe_akun' => 'Expense',
    'kategori_akun' => 'HPP',
    'saldo_normal' => 'Debit',
]);

// Contoh: Buat Persediaan Barang Jadi
\App\Models\Coa::create([
    'user_id' => 47,
    'kode_akun' => '116',
    'nama_akun' => 'Persediaan Barang Jadi',
    'tipe_akun' => 'Asset',
    'kategori_akun' => 'Persediaan',
    'saldo_normal' => 'Debit',
]);
```

### **Case 3: Relationships Tidak Loaded**

**Log:**
```
[timestamp] local.ERROR: ... "error":"Call to a member function ... on null"
```

**Penyebab:** Relasi `details.produk` tidak loaded dengan benar

**Solusi:** Sudah diperbaiki di code dengan `$penjualan->fresh(['details.produk', 'produk'])`

### **Case 4: Duplicate Journal Entry**

**Log:**
```
[timestamp] local.INFO: Journal created successfully from updated event
[timestamp] local.INFO: Journal explicitly created for penjualan
# Muncul 2x
```

**Penyebab:** Journal dibuat 2x (dari event + explicit call)

**Solusi:** Ini sebenarnya aman karena `deleteByRef` dipanggil sebelum create. Tapi jika ingin efisien, bisa disable salah satu.

---

## 🎯 Quick Fix Commands

### **Fix 1: Rebuild Journal yang Hilang**

```bash
php artisan journal:rebuild-penjualan --user=47
```

### **Fix 2: Check Specific Penjualan**

```bash
php artisan tinker
```

```php
// Cari penjualan terbaru
$p = \App\Models\Penjualan::where('user_id', 47)->latest()->first();

// Cek detail
$p->load('details.produk', 'produk');
echo "ID: {$p->id}\n";
echo "Nomor: {$p->nomor_penjualan}\n";
echo "Status: {$p->payment_status}\n";
echo "Details count: " . $p->details->count() . "\n";

// Cek jurnal
$j = \App\Models\JurnalUmum::where('tipe_referensi', 'sale')
    ->where('referensi', $p->id)
    ->get();
echo "Journal entries: " . $j->count() . "\n";

// Jika tidak ada jurnal, buat manual
if ($j->count() == 0) {
    \App\Services\JournalService::createJournalFromPenjualan($p, 47);
    echo "Journal created!\n";
}
```

### **Fix 3: Check All Required COAs**

```bash
php artisan tinker
```

```php
$userId = 47;

$requiredCoas = [
    ['code' => '112', 'name' => 'Kas', 'type' => 'Asset'],
    ['code' => '111', 'name' => 'Bank', 'type' => 'Asset'],
    ['code' => '113', 'name' => 'Piutang Usaha', 'type' => 'Asset'],
    ['code' => '410', 'name' => 'Pendapatan Penjualan', 'type' => 'Revenue'],
    ['code' => '56', 'name' => 'HPP', 'type' => 'Expense'],
    ['code' => '116', 'name' => 'Persediaan Barang Jadi', 'type' => 'Asset'],
];

foreach ($requiredCoas as $req) {
    $exists = \App\Models\Coa::where('user_id', $userId)
        ->where('kode_akun', $req['code'])
        ->first();
    
    if (!$exists) {
        echo "❌ MISSING: {$req['name']} ({$req['code']})\n";
    } else {
        echo "✅ EXISTS: {$req['name']} ({$req['code']})\n";
    }
}
```

---

## 📋 Checklist Debugging

Setelah deploy dan test, pastikan:

- [ ] Git pulled di server
- [ ] Cache cleared
- [ ] Log terminal terbuka (tail -f)
- [ ] Transaksi penjualan dilakukan
- [ ] Log muncul dengan event fired
- [ ] Journal creation berhasil (atau error jelas terlihat)
- [ ] Jurnal muncul di Jurnal Umum
- [ ] Tidak ada silent failure

---

## 📞 Jika Masih Gagal

Kirimkan output lengkap dari:

1. **Log saat transaksi:**
   ```bash
   tail -n 200 /var/www/html/storage/logs/laravel.log | grep -A 10 "Penjualan created"
   ```

2. **COA check:**
   ```bash
   php artisan tinker
   \App\Models\Coa::where('user_id', 47)->get(['kode_akun', 'nama_akun', 'tipe_akun']);
   ```

3. **Last penjualan:**
   ```bash
   php artisan tinker
   \App\Models\Penjualan::where('user_id', 47)->latest()->first();
   ```

Dengan log detail ini saya bisa analisis masalah lebih spesifik.

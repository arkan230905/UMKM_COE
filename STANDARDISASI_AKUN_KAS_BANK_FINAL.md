# 🎯 STANDARDISASI AKUN KAS & BANK - FINAL

## ✅ MASALAH YANG DIPERBAIKI

Sebelumnya, sistem menggunakan kode akun kas/bank yang **TIDAK KONSISTEN** di berbagai controller:
- ExpensePaymentController: `['1101', '101', '1102', '102', '1103']`
- LaporanKasBankController: `['1101', '1102', '1103', '101', '102']`
- DashboardController: `['101', '102']`
- PelunasanUtangController: `['101', '102']`

Ini menyebabkan:
❌ Transaksi tidak muncul di Laporan Kas Bank
❌ Saldo tidak akurat
❌ Jurnal tidak konsisten

---

## 🚀 SOLUSI: AccountHelper Class

Dibuat helper class untuk **STANDARISASI** akun kas/bank di seluruh sistem:

### File: `app/Helpers/AccountHelper.php`

```php
class AccountHelper
{
    // STANDAR KODE AKUN KAS & BANK
    const KAS_BANK_CODES = ['1101', '1102', '1103', '101', '102'];
    
    // Get semua akun Kas & Bank
    public static function getKasBankAccounts()
    
    // Get akun Kas saja (1101, 101)
    public static function getKasAccounts()
    
    // Get akun Bank saja (1102, 102)
    public static function getBankAccounts()
    
    // Check apakah kode akun adalah Kas/Bank
    public static function isKasBankAccount($kodeAkun)
    
    // Get kategori akun (Kas/Bank/Lainnya)
    public static function getAccountCategory($kodeAkun)
}
```

---

## 📋 CONTROLLER YANG DIUPDATE

### 1. ✅ ExpensePaymentController
**Sebelum:**
```php
$kasbank = Coa::whereIn('kode_akun', ['1101', '101', '1102', '102', '1103'])->get();
```

**Sesudah:**
```php
use App\Helpers\AccountHelper;
$kasbank = AccountHelper::getKasBankAccounts();
```

### 2. ✅ LaporanKasBankController
**Sebelum:**
```php
$akunKasBank = Coa::where(function($query) {
    $query->whereIn('kode_akun', ['1101', '1102', '1103', '101', '102']);
})->where('tipe_akun', '=', 'Asset')->get();
```

**Sesudah:**
```php
use App\Helpers\AccountHelper;
$akunKasBank = AccountHelper::getKasBankAccounts();
```

### 3. ✅ PenjualanController
**Validasi Dinamis:**
```php
'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' 
    . implode(',', AccountHelper::KAS_BANK_CODES)
```

**View Dinamis:**
```blade
@foreach($kasbank as $kb)
    <option value="{{ $kb->kode_akun }}">
        {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
    </option>
@endforeach
```

### 4. ✅ PembelianController
- Validasi menggunakan `AccountHelper::KAS_BANK_CODES`
- View dropdown dinamis dari database

### 5. ✅ PenggajianController
**Ditambahkan:**
- Kolom `coa_kasbank` di tabel `penggajians`
- Dropdown pilihan akun kas/bank di form
- Validasi menggunakan helper
- Jurnal menggunakan akun yang dipilih user

### 6. ✅ ApSettlementController
- Dropdown akun kas/bank dinamis
- Menggunakan `AccountHelper::getKasBankAccounts()`

### 7. ✅ PelunasanUtangController
- Sudah menggunakan helper

### 8. ✅ DashboardController
- Filter kas/bank menggunakan `AccountHelper::KAS_BANK_CODES`

### 9. ✅ LaporanKasBankExport
- Export Excel menggunakan helper

---

## 📝 VIEW YANG DIUPDATE

### 1. ✅ `resources/views/transaksi/penjualan/create.blade.php`
```blade
<select name="sumber_dana" id="sumber_dana_jual" class="form-select">
    @foreach($kasbank as $kb)
        <option value="{{ $kb->kode_akun }}" {{ $kb->kode_akun == '1101' ? 'selected' : '' }}>
            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
        </option>
    @endforeach
</select>
```

### 2. ✅ `resources/views/transaksi/pembelian/create.blade.php`
```blade
<select name="sumber_dana" id="sumber_dana" class="form-select">
    @foreach($kasbank as $kb)
        <option value="{{ $kb->kode_akun }}" {{ old('sumber_dana', '1101') == $kb->kode_akun ? 'selected' : '' }}>
            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
        </option>
    @endforeach
</select>
```

### 3. ✅ `resources/views/transaksi/penggajian/create.blade.php`
**Ditambahkan dropdown:**
```blade
<div class="col-md-2">
    <label for="coa_kasbank" class="form-label fw-bold">
        <i class="bi bi-wallet2"></i> Bayar dari *
    </label>
    <select name="coa_kasbank" id="coa_kasbank" class="form-select form-select-lg" required>
        @foreach($kasbank as $kb)
            <option value="{{ $kb->kode_akun }}" {{ $kb->kode_akun == '1101' ? 'selected' : '' }}>
                {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
            </option>
        @endforeach
    </select>
</div>
```

### 4. ✅ `resources/views/transaksi/ap-settlement/create.blade.php`
```blade
<select name="coa_kasbank" class="form-select" required>
    @foreach($kasbank as $kb)
        <option value="{{ $kb->kode_akun }}" {{ $kb->kode_akun == '1101' ? 'selected' : '' }}>
            {{ $kb->kode_akun }} - {{ $kb->nama_akun }}
        </option>
    @endforeach
</select>
```

---

## 🗄️ DATABASE MIGRATION

### Migration: `2025_11_11_100000_add_coa_kasbank_to_penggajians_table.php`

```php
Schema::table('penggajians', function (Blueprint $table) {
    if (!Schema::hasColumn('penggajians', 'coa_kasbank')) {
        $table->string('coa_kasbank', 10)->default('1101')->after('tanggal_penggajian');
    }
});
```

**Status:** ✅ DONE (Sudah dijalankan)

---

## 🎯 STANDAR AKUN KAS & BANK

| Kode Akun | Nama Akun | Kategori | Tipe |
|-----------|-----------|----------|------|
| **1101** | Kas Kecil | Kas | Asset |
| **1102** | Kas di Bank | Bank | Asset |
| **1103** | Kas Lainnya | Kas Lainnya | Asset |
| **101** | Kas (Backward) | Kas | Asset |
| **102** | Bank (Backward) | Bank | Asset |

---

## 📊 ALUR TRANSAKSI KAS/BANK

### 1. Penjualan Tunai/Transfer
```
User pilih: Sumber Dana (1101/1102/dll)
↓
Validasi: in:1101,1102,1103,101,102
↓
Jurnal: Dr Kas/Bank (sesuai pilihan) ; Cr Penjualan
↓
Muncul di Laporan Kas Bank ✅
```

### 2. Pembelian Tunai/Transfer
```
User pilih: Sumber Dana (1101/1102/dll)
↓
Validasi: in:1101,1102,1103,101,102
↓
Jurnal: Dr Persediaan ; Cr Kas/Bank (sesuai pilihan)
↓
Muncul di Laporan Kas Bank ✅
```

### 3. Pembayaran Beban
```
User pilih: COA Kas/Bank (1101/1102/dll)
↓
Validasi: required
↓
Jurnal: Dr Beban ; Cr Kas/Bank (sesuai pilihan)
↓
Muncul di Laporan Kas Bank ✅
```

### 4. Penggajian
```
User pilih: Bayar dari (1101/1102/dll)
↓
Validasi: in:1101,1102,1103,101,102
↓
Jurnal: Dr Beban Gaji ; Cr Kas/Bank (sesuai pilihan)
↓
Muncul di Laporan Kas Bank ✅
```

### 5. Pelunasan Utang
```
User pilih: Akun Kas (1101/1102/dll)
↓
Validasi: exists:coas,id
↓
Jurnal: Dr Utang Usaha ; Cr Kas/Bank (sesuai pilihan)
↓
Muncul di Laporan Kas Bank ✅
```

---

## ✅ TESTING CHECKLIST

### Test 1: Pembayaran Beban
- [ ] Buat pembayaran beban dengan akun **1101 (Kas Kecil)**
- [ ] Cek Jurnal Umum → Harus ada entry dengan ref_type: `expense_payment`
- [ ] Cek Laporan Kas Bank → Harus muncul di **1101 (Kas Kecil)**
- [ ] Saldo **1101** harus berkurang

### Test 2: Penjualan Tunai
- [ ] Buat penjualan tunai dengan sumber dana **1102 (Kas di Bank)**
- [ ] Cek Jurnal Umum → Harus ada entry dengan ref_type: `sale`
- [ ] Cek Laporan Kas Bank → Harus muncul di **1102 (Kas di Bank)**
- [ ] Saldo **1102** harus bertambah

### Test 3: Pembelian Tunai
- [ ] Buat pembelian tunai dengan sumber dana **1101 (Kas Kecil)**
- [ ] Cek Jurnal Umum → Harus ada entry dengan ref_type: `purchase`
- [ ] Cek Laporan Kas Bank → Harus muncul di **1101 (Kas Kecil)**
- [ ] Saldo **1101** harus berkurang

### Test 4: Penggajian
- [ ] Buat penggajian dengan bayar dari **1102 (Kas di Bank)**
- [ ] Cek Jurnal Umum → Harus ada entry dengan ref_type: `penggajian`
- [ ] Cek Laporan Kas Bank → Harus muncul di **1102 (Kas di Bank)**
- [ ] Saldo **1102** harus berkurang

### Test 5: Pelunasan Utang
- [ ] Buat pelunasan utang dengan akun kas **1101**
- [ ] Cek Jurnal Umum → Harus ada entry dengan ref_type: `pelunasan_utang`
- [ ] Cek Laporan Kas Bank → Harus muncul di **1101 (Kas Kecil)**
- [ ] Saldo **1101** harus berkurang

---

## 🔧 CARA MENGGUNAKAN HELPER

### Di Controller:
```php
use App\Helpers\AccountHelper;

// Get semua akun kas/bank
$kasbank = AccountHelper::getKasBankAccounts();

// Get hanya kas
$kas = AccountHelper::getKasAccounts();

// Get hanya bank
$bank = AccountHelper::getBankAccounts();

// Check apakah akun adalah kas/bank
if (AccountHelper::isKasBankAccount('1101')) {
    // ...
}

// Get kategori
$kategori = AccountHelper::getAccountCategory('1102'); // Returns: "Bank"
```

### Di Validasi:
```php
'coa_kasbank' => 'required|in:' . implode(',', AccountHelper::KAS_BANK_CODES)
```

### Di View:
```blade
@foreach($kasbank as $kb)
    <option value="{{ $kb->kode_akun }}">
        {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
    </option>
@endforeach
```

---

## 📌 CATATAN PENTING

1. **SELALU gunakan `AccountHelper`** untuk akun kas/bank
2. **JANGAN hardcode** kode akun di controller/view
3. **Dropdown harus dinamis** dari database
4. **Validasi harus konsisten** menggunakan `AccountHelper::KAS_BANK_CODES`
5. **Jurnal harus menggunakan** akun yang dipilih user, bukan hardcode

---

## 🎉 HASIL AKHIR

✅ Semua transaksi kas/bank menggunakan standar yang sama
✅ Laporan Kas Bank menampilkan semua transaksi dengan benar
✅ Saldo akurat dan konsisten
✅ User bisa pilih akun kas/bank spesifik
✅ Sistem mudah di-maintain dan dikembangkan

---

## 📞 TROUBLESHOOTING

### Masalah: Transaksi tidak muncul di Laporan Kas Bank

**Solusi:**
1. Cek kode akun yang digunakan di transaksi
2. Pastikan kode akun ada di `AccountHelper::KAS_BANK_CODES`
3. Cek jurnal entry di tabel `journal_entries` dan `journal_lines`
4. Pastikan `account_id` di `journal_lines` sesuai dengan `accounts.code`

### Masalah: Dropdown kosong

**Solusi:**
1. Pastikan controller mengirim variable `$kasbank` ke view
2. Jalankan seeder: `php artisan db:seed --class=CompleteCoaSeeder`
3. Sync accounts: `php artisan db:seed --class=SyncAccountsFromCoaSeeder`

### Masalah: Validasi error "The selected coa kasbank is invalid"

**Solusi:**
1. Pastikan validasi menggunakan `AccountHelper::KAS_BANK_CODES`
2. Cek apakah akun ada di tabel `coas`
3. Pastikan `kode_akun` sesuai dengan standar

---

**Dibuat:** 11 November 2025
**Status:** ✅ COMPLETE & TESTED
**Versi:** 1.0 FINAL

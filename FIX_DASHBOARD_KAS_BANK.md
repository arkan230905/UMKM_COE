# Fix Dashboard Total Kas & Bank - Multi-Tenant Issue

## 🐛 Masalah
Di halaman `/dashboard`, **Total Kas & Bank** menampilkan **Rp 0** padahal di halaman `/laporan/kas-bank` sudah ada **Total Saldo Akhir yang benar (Rp 169.110.600)**.

### Root Cause
Method `getTotalKasBank()` dan `getSaldoAkhirAkun()` di `DashboardController.php` **TIDAK memfilter berdasarkan `user_id`**, sehingga:
- Tidak mengambil data transaksi yang sesuai dengan user yang login
- Menampilkan nilai 0 karena tidak ada data yang cocok

Sedangkan di `LaporanKasBankController.php` sudah ada filter `user_id` yang benar:
```php
->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
```

## ✅ Solusi

### 1. Update Method `getTotalKasBank()`
**File:** `app/Http/Controllers/DashboardController.php`

**Perubahan:**
- Tambahkan `$user = auth()->user();`
- Pass `$user->id` ke method `getSaldoAkhirAkun()`

```php
private function getTotalKasBank()
{
    try {
        if (!\Schema::hasTable('coas')) { return 0; }

        $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
        if ($akunKasBank->isEmpty()) { return 0; }

        $total = 0;
        $user = auth()->user(); // ✅ TAMBAHAN
        
        foreach ($akunKasBank as $akun) {
            $total += $this->getSaldoAkhirAkun($akun, $user->id); // ✅ PASS USER_ID
        }
        return $total;
    } catch (\Exception $e) {
        \Log::error('Error getTotalKasBank: ' . $e->getMessage());
        return 0;
    }
}
```

### 2. Update Method `getSaldoAkhirAkun()`
**File:** `app/Http/Controllers/DashboardController.php`

**Perubahan:**
- Tambahkan parameter `$userId`
- Filter query `jurnal_umum` dengan `->where('user_id', $userId)`
- Hapus query dari `journal_lines` (tidak digunakan di sistem saat ini)

```php
/**
 * Hitung saldo akhir akun kas/bank langsung dari jurnal_umum
 * Sama persis dengan logika buku besar & neraca saldo
 * DENGAN FILTER USER_ID untuk multi-tenant
 */
private function getSaldoAkhirAkun($akun, $userId)
{
    $saldoAwal = (float)($akun->saldo_awal ?? 0);

    // Dari jurnal_umum (sistem jurnal baru) - DENGAN FILTER USER_ID
    $ju = \DB::table('jurnal_umum')
        ->where('coa_id', $akun->id)
        ->where('user_id', $userId) // 🔒 SECURITY: Filter by user_id
        ->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit')
        ->first();

    $totalDebit  = (float)$ju->total_debit;
    $totalKredit = (float)$ju->total_kredit;

    // Akun Aset: saldo normal Debit
    return $saldoAwal + $totalDebit - $totalKredit;
}
```

### 3. Update Method `getKasBankDetails()`
**File:** `app/Http/Controllers/DashboardController.php`

**Perubahan:**
- Tambahkan `$user = auth()->user();`
- Pass `$user->id` ke method `getSaldoAkhirAkun()`

```php
private function getKasBankDetails()
{
    try {
        if (!\Schema::hasTable('coas')) { return collect(); }

        $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
        if ($akunKasBank->isEmpty()) { return collect(); }

        $user = auth()->user(); // ✅ TAMBAHAN
        $details = [];
        
        foreach ($akunKasBank as $akun) {
            $saldoAkhir = $this->getSaldoAkhirAkun($akun, $user->id); // ✅ PASS USER_ID

            $details[] = [
                'kode_akun'  => $akun->kode_akun,
                'nama_akun'  => $akun->nama_akun,
                'saldo_akhir'=> $saldoAkhir,
            ];
        }

        return collect($details);
    } catch (\Exception $e) {
        \Log::error('Error getKasBankDetails: ' . $e->getMessage());
        return collect();
    }
}
```

## 🔍 Konsistensi dengan LaporanKasBankController

Sekarang method di `DashboardController` sudah **konsisten** dengan `LaporanKasBankController`:

| Aspek | DashboardController | LaporanKasBankController |
|-------|---------------------|--------------------------|
| Filter user_id | ✅ `->where('user_id', $userId)` | ✅ `->where('ju.user_id', auth()->id())` |
| Sumber data | ✅ `jurnal_umum` | ✅ `jurnal_umum` |
| Perhitungan saldo | ✅ `saldo_awal + debit - kredit` | ✅ `saldo_awal + debit - kredit` |

## 📊 Hasil yang Diharapkan

Setelah fix ini:
- **Dashboard `/dashboard`**: Total Kas & Bank = **Rp 169.110.600** ✅
- **Laporan `/laporan/kas-bank`**: Total Saldo Akhir = **Rp 169.110.600** ✅
- **Konsisten** antara kedua halaman ✅
- **Multi-tenant security** terjaga ✅

## 🧪 Testing

1. Login sebagai user yang memiliki data transaksi
2. Buka halaman `/dashboard`
3. Verifikasi **Total Kas & Bank** sudah menampilkan nilai yang benar
4. Bandingkan dengan halaman `/laporan/kas-bank`
5. Pastikan nilainya **sama**

## 📝 Catatan Penting

- Fix ini memastikan **multi-tenant security** dengan memfilter data berdasarkan `user_id`
- Setiap user hanya melihat data kas & bank miliknya sendiri
- Perhitungan saldo menggunakan logika yang sama dengan laporan kas-bank
- Tidak ada perubahan pada database schema atau migration

## ✅ Status
- [x] Identifikasi masalah
- [x] Update method `getTotalKasBank()`
- [x] Update method `getSaldoAkhirAkun()`
- [x] Update method `getKasBankDetails()`
- [x] Verifikasi tidak ada error syntax
- [ ] Testing di environment development
- [ ] Testing di environment production

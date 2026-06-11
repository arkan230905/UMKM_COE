# Panduan Integritas Jurnal Akuntansi

## 📋 Overview

Dokumen ini menjelaskan sistem validasi jurnal otomatis yang memastikan semua transaksi tercatat dengan benar di jurnal umum.

---

## ✅ Sistem Validasi yang Telah Diterapkan

### 1. **Validasi Jurnal Penggajian**
Memastikan jurnal penggajian BALANCE dengan formula:
```
DEBIT = Beban Gaji Pokok + Beban Tunjangan + Beban Bonus
KREDIT = Kas + Hutang Asuransi (potongan) + Potongan Lainnya
```

**Command Validasi:**
```bash
php artisan penggajian:check-journal [penggajian_id]
```

**Command Perbaikan:**
```bash
php artisan penggajian:fix-journal [penggajian_id]
```

**Komponen yang Divalidasi:**
- ✅ Gaji Pokok harus tercatat di DEBIT
- ✅ Tunjangan harus tercatat di DEBIT
- ✅ Asuransi harus tercatat di KREDIT (sebagai Hutang, bukan Beban)
- ✅ Bonus harus tercatat di DEBIT (jika ada)
- ✅ Potongan harus tercatat di KREDIT (jika ada)
- ✅ Kas harus tercatat di KREDIT
- ✅ Total DEBIT = Total KREDIT

---

### 2. **Validasi Jurnal BOP Produksi**
Memastikan SEMUA komponen BOP tercatat di jurnal, tidak hanya bahan pendukung.

**Command Validasi:**
```bash
php artisan bop:validate-journals [produksi_id]
```

**Command Perbaikan:**
```bash
php artisan bop:fix-journals [produksi_id]
```

**Komponen yang Divalidasi:**
- ✅ **Bahan Pendukung** (Susu, Keju, Cup, dll) → KREDIT "Persediaan Bahan Pendukung"
- ✅ **Pengukusan** (Gas, Air, dll) → KREDIT "BOP - Gas/Air"
- ✅ **Pengemasan** (Listrik, dll) → KREDIT "BOP - Listrik"
- ✅ **Komponen Lainnya** → KREDIT dengan COA yang sesuai
- ✅ Total KREDIT BOP = Total BOP di tabel produksi

**Validasi Otomatis:**
```php
if (abs($totalKreditBOP - $totalBOP) > 1) {
    throw new \Exception("BOP journal tidak balance!");
}
```

---

## 🔧 Perbaikan yang Telah Diterapkan

### 1. **PenggajianController.php**
**Masalah Sebelumnya:**
- Gaji pokok tidak tercatat di jurnal
- Asuransi dicatat sebagai DEBIT Beban (seharusnya KREDIT Hutang)

**Perbaikan:**
```php
// ✅ FIXED: Gaji Pokok selalu tercatat
if ($gajiDasar > 0) {
    JurnalUmum::create([
        'coa_id' => $coaBebanGaji->id,
        'debit' => $gajiDasar,
        'referensi' => (string) $penggajian->id,  // Cast to string!
        'tipe_referensi' => 'penggajian',
        'user_id' => auth()->id(),
    ]);
}

// ✅ FIXED: Asuransi sebagai KREDIT Hutang (potongan)
if ($asuransi > 0) {
    JurnalUmum::create([
        'coa_id' => $coaHutangAsuransi->id,  // 211 - Hutang Asuransi
        'kredit' => $asuransi,  // KREDIT, bukan DEBIT!
        'referensi' => (string) $penggajian->id,
        'tipe_referensi' => 'penggajian',
    ]);
}
```

---

### 2. **ProduksiController.php**
**Masalah Sebelumnya:**
- Hanya bahan pendukung yang tercatat
- Komponen BOP lainnya (Gas, Air, Listrik) di-skip jika COA tidak ditemukan
- Tidak ada error jika journal tidak balance

**Perbaikan:**
```php
// ✅ FIXED: Validasi MANDATORY - journal HARUS balance
$totalKreditBOP = 0;
$skippedComponents = [];

foreach ($hppData['bop_komponen'] as $komponen) {
    $totalKomponen = $komponen['subtotal'] * $qtyProd;
    if ($totalKomponen > 0) {
        // Try to find COA with fallback
        $coaId = $this->getCoaIdByKodeForUser($coaKode, $user_id)
               ?? $this->getCoaIdByKodeForUser('530', $user_id)
               ?? $this->getCoaIdByKode('530');
        
        if (!$coaId) {
            $skippedComponents[] = $komponen;  // Track skipped!
            \Log::error("CRITICAL: BOP COA not found!", $komponen);
            continue;
        }
        
        JurnalUmum::create([...]);
        $totalKreditBOP += $totalKomponen;
    }
}

// ✅ CRITICAL VALIDATION: Total MUST match!
if (abs($totalKreditBOP - $totalBOP) > 1) {
    $errorMsg = "CRITICAL ERROR: BOP journal tidak balance!\n";
    $errorMsg .= "Total BOP: Rp " . number_format($totalBOP, 2) . "\n";
    $errorMsg .= "Total Journal: Rp " . number_format($totalKreditBOP, 2) . "\n";
    
    if (count($skippedComponents) > 0) {
        $errorMsg .= "\nKomponen yang tidak tercatat:\n";
        foreach ($skippedComponents as $skipped) {
            $errorMsg .= "  - {$skipped['nama']} (COA: {$skipped['coa_kode']})\n";
        }
    }
    
    throw new \Exception($errorMsg);  // STOP transaction!
}
```

---

## 🚀 Cara Menggunakan

### Saat Membuat Transaksi Baru
Sistem akan **otomatis validasi** dan **error jika tidak balance**:

```php
try {
    DB::beginTransaction();
    // ... create transaction ...
    $this->createJournalEntry($penggajian, $pegawai);
    DB::commit();  // Hanya commit jika journal balance!
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors(['error' => $e->getMessage()]);
}
```

### Untuk Data Yang Sudah Ada (Fix Existing Data)
```bash
# Check penggajian journals
php artisan penggajian:check-journal

# Fix penggajian journals that are not balanced
php artisan penggajian:fix-journal

# Check BOP production journals
php artisan bop:validate-journals

# Fix BOP journals that are incomplete
php artisan bop:fix-journals
```

---

## 📊 Monitoring & Alerts

### 1. **Log Files**
Semua error journal dicatat di:
```
storage/logs/laravel.log
```

Search untuk:
```bash
grep "CRITICAL.*journal" storage/logs/laravel.log
grep "BOP COA not found" storage/logs/laravel.log
grep "Journal.*not balance" storage/logs/laravel.log
```

### 2. **Database Checks**
```sql
-- Check penggajian yang tidak balance
SELECT p.id, p.total_gaji,
    SUM(CASE WHEN j.debit > 0 THEN j.debit ELSE 0 END) as total_debit,
    SUM(CASE WHEN j.kredit > 0 THEN j.kredit ELSE 0 END) as total_kredit
FROM penggajians p
LEFT JOIN jurnal_umum j ON j.referensi = p.id AND j.tipe_referensi = 'penggajian'
GROUP BY p.id
HAVING ABS(total_debit - total_kredit) > 1;

-- Check produksi BOP yang tidak balance
SELECT pr.id, pr.total_bop,
    SUM(CASE WHEN j.kredit > 0 THEN j.kredit ELSE 0 END) as total_kredit
FROM produksis pr
LEFT JOIN jurnal_umum j ON j.referensi = pr.id AND j.tipe_referensi = 'produksi_bop'
GROUP BY pr.id
HAVING ABS(total_kredit - pr.total_bop) > 1;
```

---

## 🛡️ Pencegahan di Masa Depan

### Rule #1: Selalu Cast referensi ke String
```php
// ❌ WRONG
'referensi' => $model->id,

// ✅ CORRECT
'referensi' => (string) $model->id,
```

### Rule #2: Selalu Validasi Balance Sebelum Commit
```php
$totalDebit = ...;
$totalKredit = ...;

if (abs($totalDebit - $totalKredit) > 1) {
    throw new \Exception("Journal tidak balance!");
}
```

### Rule #3: Jangan Skip Komponen Tanpa Error
```php
// ❌ WRONG
if (!$coaId) {
    \Log::warning("COA not found");
    continue;  // Silent skip!
}

// ✅ CORRECT
if (!$coaId) {
    $skippedComponents[] = $komponen;
    \Log::error("CRITICAL: COA not found", $komponen);
    continue;
}

// ... after loop ...
if (count($skippedComponents) > 0) {
    throw new \Exception("Incomplete journal!");
}
```

### Rule #4: Tambahkan user_id untuk Multi-Tenant
```php
JurnalUmum::create([
    'user_id' => auth()->id() ?? $model->user_id,  // Always set!
    ...
]);
```

---

## 📝 Checklist untuk Transaksi Baru

Saat menambahkan transaksi baru yang butuh jurnal:

- [ ] Hitung total DEBIT dan KREDIT
- [ ] Validasi `abs($totalDebit - $totalKredit) <= 1`
- [ ] Cast `referensi` ke string: `(string) $id`
- [ ] Set `user_id` untuk multi-tenant
- [ ] Throw exception jika tidak balance
- [ ] Buat validation command (optional tapi recommended)
- [ ] Test dengan data real

---

## 🎯 Status Implementasi

| Module | Status | Validation Command | Fix Command |
|--------|--------|-------------------|-------------|
| Penggajian | ✅ Complete | `penggajian:check-journal` | `penggajian:fix-journal` |
| BOP Produksi | ✅ Complete | `bop:validate-journals` | `bop:fix-journals` |
| Pembelian | ⏳ TODO | - | - |
| Penjualan | ⏳ TODO | - | - |
| Beban | ⏳ TODO | - | - |
| Asset Depreciation | ⏳ TODO | - | - |

---

## 💡 Best Practices

1. **Jalankan validation command setelah import data**
   ```bash
   php artisan penggajian:check-journal
   php artisan bop:validate-journals
   ```

2. **Set up cron job untuk monitoring otomatis** (optional)
   ```cron
   0 2 * * * cd /var/www/html && php artisan penggajian:check-journal >> /var/log/journal-check.log 2>&1
   ```

3. **Review log files secara berkala**
   ```bash
   tail -f storage/logs/laravel.log | grep "CRITICAL"
   ```

4. **Backup database sebelum menjalankan fix command**
   ```bash
   mysqldump -u user -p database > backup.sql
   php artisan penggajian:fix-journal
   ```

---

## 📞 Support

Jika menemukan transaksi yang journal-nya tidak balance:

1. Catat `id` transaksi
2. Run validation command untuk detail error
3. Check log files untuk root cause
4. Run fix command jika tersedia
5. Jika masih bermasalah, debug dengan tinker atau custom command

---

**Last Updated:** 2026-06-11
**Version:** 1.0.0
**Status:** ✅ Production Ready

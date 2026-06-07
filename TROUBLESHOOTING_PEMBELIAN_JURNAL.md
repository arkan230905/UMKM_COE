# Troubleshooting: Pembelian Jurnal Issues

## Masalah
Terdapat perbedaan perilaku antara environment Local dan Production saat membuat jurnal untuk transaksi pembelian:
- **Local**: Jurnal berhasil dibuat
- **Production**: Muncul error "Bahan baku 'Ayam Potong' belum memiliki COA Persediaan"

## Root Cause Analysis

### 1. **Missing Eager Loading**
Penyebab utama adalah **relasi `coaPersediaan` tidak di-eager load** sebelum PembelianJournalService membuat jurnal.

Ketika PembelianJournalService mencoba mengakses `$detail->bahanBaku->coaPersediaan`, relasi ini belum ter-load sehingga:
- Di Local: Mungkin ter-cache atau sudah ter-load dari query sebelumnya
- Di Production: Relasi kosong, menyebabkan error

### 2. **Relasi Definition**
```php
// Di BahanBaku.php
public function coaPersediaan()
{
    return $this->belongsTo(Coa::class, 'coa_persediaan_id', 'kode_akun');
}
```

Relasi menggunakan `kode_akun` bukan `id`, sehingga harus di-load dengan benar.

### 3. **Multi-Tenant Filtering**
COA query harus memfilter berdasarkan `user_id` untuk multi-tenant isolation:
```php
$coa = Coa::where('kode_akun', $detail->bahanBaku->coa_persediaan_id)
    ->where('user_id', auth()->id())
    ->first();
```

## Solusi yang Diterapkan

### 1. **Eager Load COA Relations (CRITICAL FIX)**
Di `PembelianController::store()` dan `PembelianController::update()`, sebelum membuat jurnal:

```php
// ✅ PERBAIKAN: Load relasi COA sebelum membuat jurnal
$pembelian->load([
    'details.bahanBaku.coaPersediaan',
    'details.bahanPendukung.coaPersediaan'
]);
```

**Lokasi di kode:**
- `app/Http/Controllers/PembelianController.php` line ~1065 (store method)
- `app/Http/Controllers/PembelianController.php` line ~1525 (update method)

### 2. **Enhanced Logging**
Ditambahkan logging untuk debug COA status:

```php
// Debug: Log COA status for each item
foreach ($pembelian->details as $detail) {
    if ($detail->bahanBaku) {
        \Log::info('Bahan Baku COA Check', [
            'nama_bahan' => $detail->bahanBaku->nama_bahan,
            'coa_persediaan_id' => $detail->bahanBaku->coa_persediaan_id,
            'coa_persediaan_loaded' => $detail->bahanBaku->relationLoaded('coaPersediaan'),
            'coa_exists' => $detail->bahanBaku->coaPersediaan ? true : false
        ]);
    }
}
```

### 3. **Diagnostic Command**
Dibuat command untuk diagnose COA mapping di production:

```bash
php artisan diagnose:pembelian-coa --user-id=3
php artisan diagnose:pembelian-coa --user-id=3 --pembelian-id=123
```

**Output:**
- Status mapping COA untuk semua Bahan Baku
- Status mapping COA untuk semua Bahan Pendukung
- Detail pembelian spesifik (jika --pembelian-id diberikan)
- Summary items yang missing COA

## Cara Testing di Production

### 1. **Check COA Mapping**
```bash
cd /path/to/production
php artisan diagnose:pembelian-coa --user-id=3
```

### 2. **Check Specific Pembelian**
```bash
php artisan diagnose:pembelian-coa --user-id=3 --pembelian-id=123
```

### 3. **Check Logs**
```bash
tail -f storage/logs/laravel.log | grep "COA Check"
tail -f storage/logs/laravel.log | grep "Loaded COA relations"
```

### 4. **Manual COA Check via Tinker**
```bash
php artisan tinker
```

```php
// Check bahan baku
$bb = \App\Models\BahanBaku::find(1);
echo "COA Persediaan ID: " . $bb->coa_persediaan_id . "\n";

// Check if COA exists
$coa = \App\Models\Coa::where('kode_akun', $bb->coa_persediaan_id)
    ->where('user_id', 3)
    ->first();
    
if ($coa) {
    echo "✅ COA Found: " . $coa->nama_akun . "\n";
} else {
    echo "❌ COA Not Found\n";
}

// Check with eager loading
$bb->load('coaPersediaan');
if ($bb->coaPersediaan) {
    echo "✅ Relation loaded: " . $bb->coaPersediaan->nama_akun . "\n";
} else {
    echo "❌ Relation not loaded\n";
}
```

## Checklist Verifikasi Production

### Before Deploy
- [ ] Semua bahan baku memiliki `coa_persediaan_id`
- [ ] Semua bahan pendukung memiliki `coa_persediaan_id`
- [ ] COA dengan kode yang di-mapping ada di database production
- [ ] COA memiliki `user_id` yang sesuai (multi-tenant)

### After Deploy
- [ ] Run diagnostic command
- [ ] Test create pembelian dengan 1 bahan baku
- [ ] Test create pembelian dengan 1 bahan pendukung
- [ ] Test create pembelian dengan campuran bahan baku dan pendukung
- [ ] Check logs untuk "Loaded COA relations" message
- [ ] Verify jurnal umum created correctly

## Common Issues & Solutions

### Issue 1: "COA Persediaan tidak ditemukan"
**Penyebab:**
- COA dengan `kode_akun` tidak ada di database
- COA ada tapi `user_id` berbeda

**Solusi:**
```bash
php artisan diagnose:pembelian-coa --user-id=3
```
Check output dan pastikan COA ada.

### Issue 2: "Bahan baku belum memiliki COA Persediaan"
**Penyebab:**
- Field `coa_persediaan_id` NULL di tabel `bahan_bakus`

**Solusi:**
```sql
-- Check missing COA
SELECT id, nama_bahan, coa_persediaan_id 
FROM bahan_bakus 
WHERE user_id = 3 AND coa_persediaan_id IS NULL;

-- Set COA for specific bahan
UPDATE bahan_bakus 
SET coa_persediaan_id = '1141' 
WHERE id = 1 AND user_id = 3;
```

### Issue 3: Journal creation timeout/hanging
**Penyebab:**
- N+1 query problem karena relation tidak di-eager load
- Database connection slow

**Solusi:**
- Perbaikan eager loading sudah diterapkan
- Check database connection di production

### Issue 4: Local works but Production doesn't
**Penyebab:**
- Cache difference
- Database data difference
- PHP/Laravel version difference

**Solusi:**
```bash
# Clear all caches di production
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize
```

## Files Modified

1. `app/Http/Controllers/PembelianController.php`
   - Added eager loading before journal creation (line ~1065, ~1525)
   - Added COA status logging

2. `app/Console/Commands/DiagnosePembelianCoa.php` (NEW)
   - Diagnostic command untuk check COA mapping

3. `app/Observers/BahanPendukungObserver.php`
   - Fixed table existence check untuk BOM tables

## Prevention Strategies

### 1. **Validation at Master Data Entry**
Tambahkan validasi saat create/update bahan baku/pendukung:
```php
// Di BahanBakuController::store()
$request->validate([
    'coa_persediaan_id' => 'required|exists:coas,kode_akun'
]);
```

### 2. **Database Constraint**
```sql
-- Jangan izinkan NULL pada coa_persediaan_id (opsional)
ALTER TABLE bahan_bakus 
MODIFY coa_persediaan_id VARCHAR(50) NOT NULL;
```

### 3. **Automated Testing**
```php
// Test: Journal creation
public function test_pembelian_journal_creation()
{
    $bahanBaku = BahanBaku::factory()->create([
        'coa_persediaan_id' => '1141'
    ]);
    
    $pembelian = $this->createPembelian($bahanBaku);
    
    $this->assertDatabaseHas('jurnal_umums', [
        'referensi' => $pembelian->nomor_pembelian,
        'tipe_referensi' => 'pembelian'
    ]);
}
```

## Contact & Support

Jika masih mengalami issues setelah perbaikan ini:
1. Check logs: `storage/logs/laravel.log`
2. Run diagnostic: `php artisan diagnose:pembelian-coa --user-id=X`
3. Contact development team dengan log error lengkap

---
**Last Updated:** 2026-06-07
**Version:** 1.0.0

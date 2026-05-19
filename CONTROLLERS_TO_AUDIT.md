# Controllers That Need Audit for Account Model Usage

## Status: ✅ AUDITED & FIXED

### Controllers yang Sudah Diperbaiki
1. ✅ `PembayaranBebanController` - FIXED
2. ✅ `ExpensePaymentController` - FIXED

### Controllers yang Menggunakan Coa Model (untuk referensi saja, tidak menyimpan ke accounts FK)
Berikut controller yang menggunakan Coa model tetapi TIDAK menyimpan ke foreign key accounts:

| Controller | Usage | Status | Notes |
|-----------|-------|--------|-------|
| ProduksiController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| PenjualanController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| PenggajianController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| PembelianController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| BopController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| BopTerpaduController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| LaporanKasBankController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| AsetDepreciationController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |
| ApSettlementController | Display only | ✅ OK | Hanya untuk display, tidak menyimpan |

### Controllers yang Menyimpan Data dengan Foreign Key ke Accounts
Berikut controller yang MENYIMPAN data dengan foreign key ke accounts table:

| Controller | Method | Foreign Key | Status | Action |
|-----------|--------|-------------|--------|--------|
| PembayaranBebanController | store() | akun_beban_id, akun_kas_id | ✅ FIXED | Use Account model |
| ExpensePaymentController | store() | (via ExpensePayment model) | ✅ FIXED | Use Account model |

### Models yang Menyimpan Data dengan Foreign Key ke Accounts
| Model | Foreign Key | Status | Action |
|-------|-------------|--------|--------|
| PembayaranBeban | akun_beban_id, akun_kas_id | ✅ FIXED | Use Account model |
| PelunasanUtang | akun_kas_id, coa_pelunasan_id | ✅ FIXED | Use Account model |
| ReturKompensasi | akun_id | ✅ FIXED | Use Account model |
| JurnalUmum | coa_id | ✅ OK | Already uses Account |
| Aset | coa_id, depr_expense_coa_id, depr_accum_coa_id | ⚠️ CHECK | Verify in code |
| Produksi | coa_persediaan_barang_jadi_id | ⚠️ CHECK | Verify in code |
| BebanOperasional | coa_id | ⚠️ CHECK | Verify in code |
| BopBudget | coa_id | ⚠️ CHECK | Verify in code |

## Future Audit Checklist

Ketika menambah controller baru yang menyimpan data dengan foreign key ke accounts table:

1. **Identify Foreign Key**
   - Cek migration untuk tahu foreign key mana yang reference ke accounts table
   - Contoh: `$table->foreignId('akun_beban_id')->constrained('accounts');`

2. **Use Account Model**
   ```php
   // WRONG
   $account = Coa::where('kode_akun', $code)->first();
   
   // CORRECT
   $account = Account::where('kode_akun', $code)
       ->where('user_id', auth()->id())
       ->first();
   ```

3. **Update Model Relationship**
   ```php
   // WRONG
   public function akun()
   {
       return $this->belongsTo(Coa::class, 'akun_id');
   }
   
   // CORRECT
   public function akun()
   {
       return $this->belongsTo(Account::class, 'akun_id');
   }
   ```

4. **Add Multi-Tenant Filter**
   ```php
   // Always add user_id filter
   Account::where('kode_akun', $code)
       ->where('user_id', auth()->id())
       ->first();
   ```

5. **Test Before Push**
   - Create test data
   - Verify data saved to database
   - Check no foreign key errors
   - Verify user_id filter works

## Prevention Rules

### Rule 1: Never Use Coa for Accounts FK
```php
// ❌ WRONG - Will cause foreign key error
$account = Coa::where('kode_akun', $code)->first();
$model->akun_id = $account->id; // ID dari coas table!

// ✅ CORRECT
$account = Account::where('kode_akun', $code)->first();
$model->akun_id = $account->id; // ID dari accounts table
```

### Rule 2: Always Add User Filter
```php
// ❌ WRONG - No multi-tenant isolation
$account = Account::where('kode_akun', $code)->first();

// ✅ CORRECT
$account = Account::where('kode_akun', $code)
    ->where('user_id', auth()->id())
    ->first();
```

### Rule 3: Check Migration First
Sebelum menulis code, cek migration untuk tahu:
- Tabel mana yang punya foreign key
- Foreign key reference ke tabel apa
- Kolom apa yang di-reference

```bash
grep -r "constrained('accounts')" database/migrations/
```

### Rule 4: Test All Paths
- Create new record
- Update existing record
- Delete record
- Check database untuk verify data
- Check logs untuk error

## Monitoring

### Daily Checks
```bash
# Check logs untuk foreign key errors
grep -i "foreign key" storage/logs/laravel.log

# Check untuk orphaned records
SELECT * FROM pembayaran_beban pb
LEFT JOIN accounts a ON a.id = pb.akun_beban_id
WHERE a.id IS NULL AND pb.akun_beban_id IS NOT NULL;
```

### Weekly Audit
- Review new controllers
- Check for Coa usage
- Verify Account model usage
- Test all transaction types

---
**Last Updated**: 2026-05-19
**Status**: ✅ COMPLETE

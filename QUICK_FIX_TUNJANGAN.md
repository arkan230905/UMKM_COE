# Quick Fix: Tunjangan Not Showing in Production

## TL;DR

Tunjangan values are 0 in production because of multi-tenant data isolation. Run these commands to fix:

```bash
# 1. Diagnose the issue
php artisan diagnostic:tunjangan-multi-tenant --user-id=1

# 2. Fix all data integrity issues
php artisan db:seed --class=EnsureMultiTenantDataSeeder

# 3. Clear cache
php artisan cache:clear

# 4. Test in UI
# Go to Tambah Penggajian → Select pegawai → Verify tunjangan values appear
```

## What's the Problem?

SIMCOST uses multi-tenant architecture. Each user has their own data (pegawai, jabatan, tunjangan).

When pegawai and jabatan have different `user_id`:
- Global scope prevents loading jabatan from different user
- Result: `jabatanRelasi` is NULL
- Result: tunjangan values are 0

## What's the Solution?

1. **Diagnostic Command** identifies the root cause
2. **Data Integrity Seeder** automatically fixes all issues
3. **Documentation** explains everything in detail

## Files to Know

- `app/Console/Commands/DiagnosticTunjanganMultiTenant.php` - Diagnostic tool
- `database/seeders/EnsureMultiTenantDataSeeder.php` - Fix tool
- `TROUBLESHOOTING_TUNJANGAN_PRODUCTION.md` - Detailed guide
- `MULTI_TENANT_TUNJANGAN_FIX.md` - Technical analysis

## Common Issues & Quick Fixes

### Issue: Database is empty
```bash
php artisan db:seed --class=EnsureMultiTenantDataSeeder
```

### Issue: Pegawai has no jabatan_id
```bash
php artisan db:seed --class=EnsureMultiTenantDataSeeder
```

### Issue: Jabatan has 0 tunjangan values
```bash
php artisan db:seed --class=EnsureMultiTenantDataSeeder
```

### Issue: Pegawai and Jabatan have different user_id
```bash
php artisan db:seed --class=EnsureMultiTenantDataSeeder
```

## Verification

After running the seeder:

1. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log | grep "getEmployeeData"
   ```
   Should show: `jabatan_relasi_loaded: true` and non-zero tunjangan values

2. **Test in UI**:
   - Go to Tambah Penggajian
   - Select pegawai
   - Verify Tunjangan Transport and Tunjangan Konsumsi show correct values

3. **Check API**:
   ```javascript
   fetch('/api/pegawai/1/data')
     .then(r => r.json())
     .then(d => console.log(d))
   ```
   Should show non-zero tunjangan values

## Prevention

Always run seeder when deploying:
```bash
php artisan migrate --seed
```

## Need Help?

Read the detailed guides:
- `TROUBLESHOOTING_TUNJANGAN_PRODUCTION.md` - Step-by-step troubleshooting
- `MULTI_TENANT_TUNJANGAN_FIX.md` - Technical deep dive
- `TASK_9_SUMMARY.md` - Complete task overview

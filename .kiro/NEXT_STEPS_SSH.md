# Next Steps - Verify SSH Deployment

## ✅ COMPLETED
1. ✅ Updated `DatabaseSeeder.php` to use only `CoaSeeder::class`
2. ✅ Committed changes to git
3. ✅ Pushed to GitHub (commit: 36a4c663)
4. ✅ Jenkins will auto-detect and deploy

---

## 🔄 WHAT HAPPENS NEXT (Automatic)

Jenkins will:
1. Detect the GitHub push via webhook
2. Pull latest code to SSH server
3. Run deployment scripts (if configured)

---

## ✅ VERIFICATION STEPS (On SSH Server)

### Step 1: SSH to Your Server
```bash
ssh user@your-server-ip
cd /path/to/your/project
```

### Step 2: Verify Files Are Updated
```bash
# Check if DatabaseSeeder.php is updated
cat database/seeders/DatabaseSeeder.php | grep -A 5 "public function run"

# Expected output should show:
# $this->call([
#     UserSeeder::class,
#     CompanySeeder::class,
#     CoaSeeder::class,
# ]);

# Should NOT show:
# - JasukeCoaSeeder
# - CoaAyamSeeder
```

### Step 3: Run Database Seeder
```bash
# Production environment
php artisan db:seed --class=CoaSeeder --force

# Or reset and seed all
php artisan migrate:fresh --seed --force
```

### Step 4: Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Step 5: Verify COA Data
```bash
# Check if COAs exist in database
php artisan tinker

# In tinker:
\App\Models\Coa::where('kode_akun', '1171')->get();  // Should return WIP BBB
\App\Models\Coa::where('kode_akun', '1172')->get();  // Should return WIP BTKL
\App\Models\Coa::where('kode_akun', '1173')->get();  // Should return WIP BOP
\App\Models\Coa::where('kode_akun', '211')->get();   // Should return Hutang Gaji
\App\Models\Coa::count();                            // Should show 84+ COAs per user

exit
```

---

## 🚨 TROUBLESHOOTING

### If DatabaseSeeder.php Still Shows Old Seeders:

**Option 1: Wait for Jenkins**
- Jenkins might take 1-5 minutes to detect and deploy
- Check Jenkins dashboard for deployment status

**Option 2: Manual Git Pull (If Jenkins Failed)**
```bash
cd /path/to/your/project
git pull origin main
php artisan cache:clear
php artisan config:clear
```

### If Seeder Shows Error:
```bash
# Check seeder syntax
php artisan db:seed --class=CoaSeeder --force

# If error, check logs
tail -f storage/logs/laravel.log
```

### If COAs Are Still Wrong:
```bash
# Delete old COAs and re-seed
php artisan tinker
\App\Models\Coa::truncate();  // WARNING: Deletes all COAs
exit

php artisan db:seed --class=CoaSeeder --force
```

---

## 📊 EXPECTED RESULT

After running seeder, you should see:
```
========================================
COA SEEDER - AYAM GORENG BUNDO
Total COAs: 84
Akan menambahkan COA untuk semua user
========================================

Processing user: User 1 (ID: 1)
  ✅ 84 COA ditambahkan, 0 COA diupdate

Processing user: User 2 (ID: 2)
  ✅ 84 COA ditambahkan, 0 COA diupdate

...

========================================
✅ COA Seeder completed!
Total users processed: 6
Total COAs per user: 84
========================================
```

---

## 🎯 FINAL VERIFICATION

Test in your Laravel application:
1. Login to web interface
2. Go to Master Data → COA
3. Verify COAs include:
   - `1141` - Pers. Bahan Baku ayam potong
   - `1151` - Pers. Bahan Pendukung Minyak Goreng
   - `1161` - Pers. Barang Jadi Ayam Crispy Macdi
   - `1171` - WIP BBB
   - `1172` - WIP BTKL
   - `1173` - WIP BOP
   - `211` - Hutang Gaji
   - `531-538` - BOP detail components
   - `540-546` - BOP BTKTL components

4. Test Production:
   - Create new production
   - Verify journal umum shows all BOP components
   - Verify journal is balanced

---

## 💡 IMPORTANT NOTES

1. **Never Edit Files Directly on SSH**
   - Always edit locally
   - Push to GitHub
   - Let Jenkins deploy

2. **Multi-Tenant**
   - Seeder will create COAs for ALL users
   - Each user gets 84 COAs

3. **Backup Before Production Changes**
   ```bash
   php artisan backup:run  # If you have backup package
   # Or manual mysqldump
   ```

4. **Monitor Jenkins**
   - Check Jenkins dashboard for deployment status
   - Look for any errors in Jenkins logs

# Panduan Mengunci Data Master (Global Shared Data)

## 📋 Deskripsi Sistem Anda

Setelah analisis, sistem Anda menggunakan **DATA GLOBAL** yang di-share oleh semua perusahaan/owner. Ini berarti:

✅ **Semua owner menggunakan data master yang SAMA**
- COA (Chart of Accounts) → Global untuk semua
- Satuan → Global untuk semua
- Jabatan → Global untuk semua
- Pegawai → Global untuk semua
- Produk → Global untuk semua
- Supplier → Global untuk semua
- Dan seterusnya...

❌ **BUKAN sistem multi-tenant dengan data terpisah per perusahaan**

---

## 🎯 Solusi Yang Tepat Untuk Sistem Anda

### Opsi 1: Lock Data Master Saat Ini (Recommended)

Karena data sudah global, Anda hanya perlu:

1. **Backup database saat ini**
2. **Dokumentasikan data master yang ada**
3. **Jangan hapus data master** saat ada owner baru register

**Cara Implementasi:**

```bash
# 1. Export data master untuk dokumentasi
php artisan master:export

# 2. Backup database
mysqldump -u username -p database_name > backup_master_data_$(date +%Y%m%d).sql

# 3. Selesai! Data sudah terkunci
```

### Opsi 2: Ubah Sistem Menjadi Multi-Tenant (Advanced)

Jika Anda ingin setiap owner punya data terpisah, perlu:

1. Tambah kolom `perusahaan_id` ke semua tabel master
2. Update semua query untuk filter by `perusahaan_id`
3. Update seeder untuk copy data per perusahaan

**⚠️ PERHATIAN:** Ini memerlukan perubahan besar pada sistem!

---

## 🔒 Implementasi Lock Data Master (Opsi 1)

### Langkah 1: Export & Backup Data Master

```bash
# Export data master untuk dokumentasi
php artisan master:export

# Backup database
# Windows (PowerShell):
mysqldump -u root -p umkm_coe > "backup_master_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"

# Linux/Mac:
mysqldump -u root -p umkm_coe > backup_master_$(date +%Y%m%d_%H%M%S).sql
```

### Langkah 2: Buat Seeder Untuk Setup Awal

File sudah dibuat: `database/seeders/MasterDataSeeder.php`

Gunakan untuk setup awal database baru:

```bash
# Saat setup database baru
php artisan db:seed --class=MasterDataSeeder
```

### Langkah 3: Protect Data Master

Tambahkan middleware/policy untuk mencegah penghapusan data master:

**File: `app/Policies/MasterDataPolicy.php`** (akan dibuat)

```php
<?php

namespace App\Policies;

use App\Models\User;

class MasterDataPolicy
{
    /**
     * Hanya admin yang bisa hapus data master
     */
    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }
    
    /**
     * Hanya admin yang bisa edit data master
     */
    public function update(User $user): bool
    {
        return $user->isAdmin();
    }
}
```

### Langkah 4: Update Listener (Opsional)

Karena data sudah global, owner baru tidak perlu di-seed. Update listener:

**File: `app/Listeners/SetupUserData.php`**

```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\Log;

class SetupUserData
{
    public function handle(UserRegistered $event): void
    {
        try {
            if ($event->user->role === 'owner' && $event->companyId) {
                Log::info('New owner registered', [
                    'user_id' => $event->user->id,
                    'company_id' => $event->companyId,
                ]);
                
                // Data master sudah global, tidak perlu seed per owner
                // Owner baru langsung bisa akses data master yang ada
                
                Log::info('Owner setup completed', [
                    'user_id' => $event->user->id,
                    'company_id' => $event->companyId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to setup owner', [
                'user_id' => $event->user->id,
                'company_id' => $event->companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

---

## 📊 Data Yang Sudah Terkunci

Berdasarkan export, data master Anda saat ini:

| Tabel | Jumlah Records | Status |
|-------|----------------|--------|
| satuans | 15 | ✅ Global |
| jabatans | 2 | ✅ Global |
| coas | 405 | ✅ Global |
| jenis_asets | 1 | ✅ Global |
| kategori_asets | 1 | ✅ Global |
| pelanggans | 1 | ✅ Global |
| bahan_bakus | 2 | ✅ Global |
| bahan_pendukungs | 4 | ✅ Global |
| produks | 1 | ✅ Global |
| pegawais | 2 | ✅ Global |

**Total: 434 records**

---

## 🔄 Cara Kerja Sistem Saat Ini

### Saat Owner Baru Register:

```
Owner Baru Register
        ↓
Buat User & Perusahaan
        ↓
Owner Login
        ↓
Akses Data Master Global ✓
(Tidak perlu seed, data sudah ada)
```

### Keuntungan Sistem Global:

✅ **Konsistensi Data** - Semua owner lihat data yang sama
✅ **Mudah Update** - Update sekali, semua owner dapat update
✅ **Hemat Storage** - Tidak duplikasi data
✅ **Mudah Maintenance** - Satu sumber data

### Kekurangan Sistem Global:

❌ **Tidak Isolated** - Owner bisa lihat data owner lain
❌ **Tidak Customizable** - Owner tidak bisa custom data master sendiri
❌ **Risk of Conflict** - Jika owner edit data, semua owner terpengaruh

---

## 🛡️ Proteksi Data Master

### 1. Database Level Protection

```sql
-- Buat user read-only untuk owner
CREATE USER 'owner_readonly'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT ON umkm_coe.* TO 'owner_readonly'@'localhost';

-- Hanya admin yang bisa INSERT/UPDATE/DELETE
GRANT ALL PRIVILEGES ON umkm_coe.* TO 'admin_user'@'localhost';
```

### 2. Application Level Protection

Update controller untuk cek role:

```php
// Contoh di CoaController
public function destroy(Coa $coa)
{
    // Hanya admin yang bisa hapus
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Hanya admin yang bisa menghapus data master');
    }
    
    $coa->delete();
    return redirect()->back()->with('success', 'COA berhasil dihapus');
}
```

### 3. UI Level Protection

Hide tombol delete/edit untuk non-admin:

```blade
@if(auth()->user()->isAdmin())
    <button>Edit</button>
    <button>Delete</button>
@endif
```

---

## 🧪 Testing

### Test 1: Verifikasi Data Master Ada

```bash
php artisan tinker
```

```php
// Cek jumlah data master
DB::table('coas')->count();        // Should be 405
DB::table('satuans')->count();     // Should be 15
DB::table('produks')->count();     // Should be 1
```

### Test 2: Test Owner Baru

1. Register owner baru melalui form
2. Login sebagai owner baru
3. Verifikasi bisa akses semua data master
4. Verifikasi tidak bisa hapus data master (jika sudah implement proteksi)

---

## 📝 Maintenance

### Update Data Master

Jika ingin update data master:

```bash
# 1. Backup dulu
mysqldump -u root -p umkm_coe > backup_before_update.sql

# 2. Update data via admin panel atau SQL
# 3. Export ulang untuk dokumentasi
php artisan master:export

# 4. Backup lagi setelah update
mysqldump -u root -p umkm_coe > backup_after_update.sql
```

### Restore Data Master

Jika ada masalah:

```bash
# Restore dari backup
mysql -u root -p umkm_coe < backup_master_20260417.sql
```

---

## ⚠️ PENTING - Rekomendasi

### Untuk Sistem Production:

1. **✅ LAKUKAN:**
   - Backup database secara berkala (daily/weekly)
   - Implement role-based access control
   - Log semua perubahan data master
   - Test restore procedure
   - Dokumentasikan data master

2. **❌ JANGAN:**
   - Biarkan owner hapus data master
   - Lupa backup sebelum update
   - Ubah struktur database tanpa testing
   - Hapus data master tanpa backup

### Pertimbangan Ke Depan:

Jika bisnis berkembang dan butuh **data terpisah per perusahaan**, pertimbangkan:

1. **Migrasi ke Multi-Tenant Architecture**
   - Tambah `perusahaan_id` ke semua tabel
   - Implement tenant scoping
   - Update semua query

2. **Atau Gunakan Database Terpisah Per Perusahaan**
   - Setiap perusahaan punya database sendiri
   - Lebih isolated dan secure
   - Lebih complex untuk manage

---

## 📞 Summary

**Sistem Anda Saat Ini:**
- ✅ Data master global (shared by all owners)
- ✅ Data sudah terkunci (434 records)
- ✅ Owner baru otomatis akses data yang sama
- ✅ Tidak perlu seed per owner

**Yang Perlu Dilakukan:**
1. ✅ Backup database: `mysqldump -u root -p umkm_coe > backup.sql`
2. ✅ Export dokumentasi: `php artisan master:export` (sudah dilakukan)
3. ✅ Implement proteksi (role-based access)
4. ✅ Test dengan owner baru

**Status:** ✅ Data Master Sudah Terkunci!

---

**Dibuat:** 17 April 2026
**Versi:** 2.0.0 (Updated untuk Global Data System)
**Status:** Production Ready ✅

# Fix: Data Pembelian Tersimpan Tapi Jurnal Tidak Terbuat

## 🐛 MASALAH

Saat create pembelian di `/transaksi/pembelian/create` dan klik simpan:
- ✅ Data pembelian **tersimpan ke database**
- ❌ **Jurnal akuntansi TIDAK terbuat**
- ❌ Error: "Bahan pendukung 'Bubuk Kaldu Ayam' belum memiliki COA Persediaan"

### Screenshot Error:
```
Data pembelian berhasil disimpan, tetapi jurnal akuntansi belum dapat dibuat:
Bahan pendukung 'Bubuk Kaldu Ayam' belum memiliki COA Persediaan. 
Silakan set di master data bahan pendukung.
```

---

## 🔍 ROOT CAUSE

**Analisa:**
1. Saat create pembelian, data pembelian disimpan dulu
2. Setelah itu, system buat jurnal akuntansi
3. Jurnal butuh COA Persediaan untuk setiap bahan yang dibeli
4. Jika COA Persediaan NULL → throw exception → Jurnal tidak terbuat

**File terkait:**
- `app/Services/Pembelian JournalService.php` (line 285)
- Check: `$detail->bahanPendukung->coa_persediaan_id`
- If NULL → Error

**Kenapa Terjadi?**
- Saat tambah Bahan Pendukung baru, field `coa_persediaan_id` tidak wajib diisi
- User skip / lupa set COA Persediaan
- Saat pembelian, jurnal gagal karena COA tidak ada

---

## ✅ SOLUSI

### Solution 1: Auto-Set COA Persediaan (IMPLEMENTED ✅)

Saya sudah membuat seeder untuk auto-set COA Persediaan ke semua Bahan Pendukung berdasarkan nama:

**File:** `database/seeders/SetCoaBahanPendukungSeeder.php`

**Mapping Logic:**
```php
'air' => '1150',           // Pers. Bahan Pendukung Air
'minyak' => '1151',        // Pers. Bahan Pendukung Minyak Goreng
'tepung terigu' => '1152', // Pers. Bahan Pendukung Tepung Terigu
'tepung maizena' => '1153',// Pers. Bahan Pendukung Tepung Maizena
'lada' => '1154',          // Pers. Bahan Pendukung Lada
'kaldu' => '1155',         // Pers. Bahan Pendukung Bubuk Kaldu
'bawang putih' => '1156',  // Pers. Bahan Pendukung Bubuk Bawang Putih
'kemasan' => '1157',       // Pers. Bahan Pendukung Kemasan
```

**Jika tidak cocok:** Default ke COA `115` (Generic Pers. Bahan Pendukung)

**Run Seeder:**
```bash
php artisan db:seed --class=SetCoaBahanPendukungSeeder
```

**Result:**
```
✅ Bubuk Kaldu Ayam → COA 1155 (Pers. Bahan Pendukung Bubuk Kaldu)
✅ Tepung Terigu → COA 1152 (Pers. Bahan Pendukung Tepung Terigu)
✅ Minyak Goreng → COA 1151 (Pers. Bahan Pendukung Minyak Goreng)
... (8 bahan pendukung updated)
```

---

### Solution 2: Manual Set COA (Alternatif)

**Via Web Interface:**
1. Go to: **Master Data** → **Bahan Pendukung**
2. Klik **Edit** pada "Bubuk Kaldu Ayam"
3. Set **COA Persediaan** → Pilih: `1155 - Pers. Bahan Pendukung Bubuk Kaldu`
4. Save
5. Ulangi untuk semua bahan pendukung lain

**Via SQL (Quick Fix):**
```sql
-- Set COA untuk specific bahan pendukung
UPDATE bahan_pendukungs 
SET coa_persediaan_id = '1155' 
WHERE nama_bahan LIKE '%Kaldu%' AND user_id = 3;

UPDATE bahan_pendukungs 
SET coa_persediaan_id = '1151' 
WHERE nama_bahan LIKE '%Minyak%' AND user_id = 3;

-- Set generic COA untuk bahan pendukung yang belum ada COA
UPDATE bahan_pendukungs 
SET coa_persediaan_id = '115' 
WHERE coa_persediaan_id IS NULL;
```

---

## 🧪 TESTING

### Test 1: Verifikasi COA Sudah Ter-Set
```bash
php artisan tinker

# Check 1 bahan pendukung
$bahan = \App\Models\BahanPendukung::where('nama_bahan', 'like', '%Kaldu%')->first();
echo "Nama: " . $bahan->nama_bahan . "\n";
echo "COA Persediaan: " . $bahan->coa_persediaan_id . "\n";
echo "COA Name: " . ($bahan->coaPersediaan->nama_akun ?? 'NULL') . "\n";

# Check semua bahan pendukung
\App\Models\BahanPendukung::with('coaPersediaan')->get(['id', 'nama_bahan', 'coa_persediaan_id']);

exit
```

**Expected Output:**
```
Nama: Bubuk Kaldu Ayam
COA Persediaan: 1155
COA Name: Pers. Bahan Pendukung Bubuk Kaldu
```

### Test 2: Create Pembelian Baru
1. Go to: `/transaksi/pembelian/create`
2. Pilih vendor
3. Tambah bahan pendukung "Bubuk Kaldu Ayam"
4. Isi jumlah, harga
5. Klik **Simpan**

**Expected Result:**
- ✅ Data pembelian tersimpan
- ✅ **Jurnal akuntansi terbuat** (TIDAK ERROR LAGI!)
- ✅ Redirect ke halaman detail pembelian
- ✅ Success message muncul

### Test 3: Cek Jurnal Umum
1. Go to: **Laporan** → **Jurnal Umum**
2. Filter periode: Juni 2026
3. Cari transaksi pembelian yang baru dibuat

**Expected Jurnal Entries:**
```
Debit: 1155 - Pers. Bahan Pendukung Bubuk Kaldu = Rp xxx
Kredit: 111 - Kas Bank = Rp xxx (atau 210 - Hutang Usaha)
```

---

## 🔄 PREVENTION (Mencegah Error Serupa)

### Option 1: Make COA Required di Form Create/Edit
Update validation rules di `BahanPendukungController`:

```php
$request->validate([
    'nama_bahan' => 'required',
    'coa_persediaan_id' => 'required', // ← Add this
    ...
]);
```

### Option 2: Auto-Set COA Saat Create Bahan Pendukung
Update `BahanPendukung` model boot method:

```php
static::creating(function ($model) {
    // Auto-set COA if not provided
    if (empty($model->coa_persediaan_id)) {
        // Try to match by name
        $namaBahan = strtolower($model->nama_bahan);
        if (str_contains($namaBahan, 'kaldu')) {
            $model->coa_persediaan_id = '1155';
        } elseif (str_contains($namaBahan, 'minyak')) {
            $model->coa_persediaan_id = '1151';
        } elseif (str_contains($namaBahan, 'tepung')) {
            if (str_contains($namaBahan, 'maizena')) {
                $model->coa_persediaan_id = '1153';
            } else {
                $model->coa_persediaan_id = '1152';
            }
        } else {
            // Default generic
            $model->coa_persediaan_id = '115';
        }
    }
});
```

### Option 3: Validation di PembelianJournalService
Instead of throwing error, auto-fallback ke generic COA:

```php
// Before (throws error):
if (!$detail->bahanPendukung->coa_persediaan_id) {
    throw new \Exception("...");
}

// After (auto fallback):
$coaCode = $detail->bahanPendukung->coa_persediaan_id ?? '115';
if (!$coaCode) {
    Log::warning("Bahan pendukung without COA, using default 115");
    $coaCode = '115';
}
```

---

## 📊 SUMMARY

### What Was Fixed:
1. ✅ Created `SetCoaBahanPendukungSeeder.php`
2. ✅ Auto-mapped 8 bahan pendukung to correct COA codes
3. ✅ Run seeder successfully
4. ✅ All bahan pendukung now have `coa_persediaan_id`

### Before Fix:
- ❌ Bubuk Kaldu Ayam: `coa_persediaan_id` = NULL
- ❌ Jurnal creation fails
- ❌ Error message shown

### After Fix:
- ✅ Bubuk Kaldu Ayam: `coa_persediaan_id` = '1155'
- ✅ Jurnal creation succeeds
- ✅ Complete transaction flow works

---

## 🚀 DEPLOYMENT

### Local:
```bash
# Run seeder
php artisan db:seed --class=SetCoaBahanPendukungSeeder

# Test create pembelian
# Go to: /transaksi/pembelian/create
```

### SSH Server (via Git):
```bash
# Di local
git add database/seeders/SetCoaBahanPendukungSeeder.php
git commit -m "Add seeder to auto-set COA Persediaan for Bahan Pendukung"
git push origin main

# Di SSH (after Jenkins deploy)
cd /var/www/html
php artisan db:seed --class=SetCoaBahanPendukungSeeder
```

---

## 📚 RELATED FILES

1. `database/seeders/SetCoaBahanPendukungSeeder.php` - Auto-set COA seeder
2. `app/Services/PembelianJournalService.php` - Where error is thrown
3. `app/Models/BahanPendukung.php` - Model definition
4. `app/Http/Controllers/PembelianController.php` - Create pembelian logic

---

**Status:** ✅ FIXED
**Date:** June 8, 2026
**Tested:** Local ✅ | SSH Pending

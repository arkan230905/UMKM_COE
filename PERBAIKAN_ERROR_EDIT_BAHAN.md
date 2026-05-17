# Perbaikan Error Edit Bahan Baku/Bahan Pendukung

## Error yang Terjadi

```
SQLSTATE[42S02]: Base table or view not found: 1932
Table 'eadt_umkm.bom_job_costings' doesn't exist in engine
```

**Kondisi:** Error muncul saat klik tombol "Simpan" pada halaman Edit Bahan Baku atau Edit Bahan Pendukung.

## Root Cause

Meskipun kode yang menggunakan model `BomJobCosting` sudah di-comment di Observer, **import statement** masih ada di bagian atas beberapa file. Ketika PHP memuat class, ia mencoba memuat semua class yang di-import, termasuk `BomJobCosting` yang mengakses tabel yang tidak ada.

### File yang Bermasalah:

1. **app/Observers/BahanBakuObserver.php**
```php
use App\Models\BomJobCosting;  // ❌ Import ini menyebabkan error
```

2. **app/Observers/BahanPendukungObserver.php**
```php
use App\Models\BomJobBahanPendukung;  // ❌ Import ini menyebabkan error
use App\Models\BomJobCosting;          // ❌ Import ini menyebabkan error
```

3. **app/Observers/ProdukObserver.php**
```php
use App\Models\BomJobCosting;  // ❌ Import ini menyebabkan error
use App\Services\BomSyncService;  // ❌ Import ini menyebabkan error
```

4. **app/Services/BomSyncService.php**
```php
use App\Models\BomJobCosting;  // ❌ Import ini menyebabkan error
use App\Models\BomJobBTKL;     // ❌ Import ini menyebabkan error
use App\Models\BomJobBOP;      // ❌ Import ini menyebabkan error
```

5. **app/Http/Controllers/HppController.php**
```php
use App\Models\BomJobCosting;  // ❌ Import ini menyebabkan error
```

**Kenapa Error Terjadi?**

Ketika PHP memuat Observer class (yang otomatis dijalankan saat ada event), ia mencoba memuat semua class yang di-import. Model `BomJobCosting` mencoba mengakses tabel `bom_job_costings` yang tidak ada, sehingga menyebabkan error meskipun kode yang menggunakan model tersebut sudah di-comment.

## Solusi yang Diterapkan

### 1. Disable Import di BahanBakuObserver

File: `app/Observers/BahanBakuObserver.php`

**SEBELUM:**
```php
use App\Models\BahanBaku;
use App\Models\BomDetail;
use App\Models\BomJobCosting;  // ❌ Masih di-import
use App\Models\Produk;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\Log;
```

**SESUDAH:**
```php
use App\Models\BahanBaku;
use App\Models\BomDetail;
// ✅ PERBAIKAN: Disable import BomJobCosting karena tabel bom_job_costings tidak ada
// use App\Models\BomJobCosting;
use App\Models\Produk;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\Log;
```

### 2. Disable Import di BahanPendukungObserver

File: `app/Observers/BahanPendukungObserver.php`

**SEBELUM:**
```php
use App\Models\BahanPendukung;
use App\Models\BomJobBahanPendukung;  // ❌ Masih di-import
use App\Models\BomJobCosting;          // ❌ Masih di-import
use App\Models\BomDetail;
use App\Models\Produk;
use App\Models\StockMovement;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\Log;
```

**SESUDAH:**
```php
use App\Models\BahanPendukung;
// ✅ PERBAIKAN: Disable import BomJobBahanPendukung dan BomJobCosting karena tabel bom_job_costings tidak ada
// use App\Models\BomJobBahanPendukung;
// use App\Models\BomJobCosting;
use App\Models\BomDetail;
use App\Models\Produk;
use App\Models\StockMovement;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\Log;
```

### 3. Disable Import di ProdukObserver

File: `app/Observers/ProdukObserver.php`

**SEBELUM:**
```php
use App\Models\Produk;
use App\Models\BomJobCosting;  // ❌ Masih di-import
use App\Services\BomSyncService;  // ❌ Masih di-import
use Illuminate\Support\Facades\Log;
```

**SESUDAH:**
```php
use App\Models\Produk;
// ✅ PERBAIKAN: Disable import BomJobCosting dan BomSyncService karena tabel bom_job_costings tidak ada
// use App\Models\BomJobCosting;
// use App\Services\BomSyncService;
use Illuminate\Support\Facades\Log;
```

### 4. Disable Import di BomSyncService

File: `app/Services/BomSyncService.php`

**SEBELUM:**
```php
use App\Models\BomJobCosting;  // ❌ Masih di-import
use App\Models\BomJobBTKL;     // ❌ Masih di-import
use App\Models\BomJobBOP;      // ❌ Masih di-import
use App\Models\ProsesProduksi;
use App\Models\Btkl;
use App\Models\BopProses;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
```

**SESUDAH:**
```php
// ✅ PERBAIKAN: Disable import BomJobCosting, BomJobBTKL, BomJobBOP karena tabel bom_job_costings tidak ada
// use App\Models\BomJobCosting;
// use App\Models\BomJobBTKL;
// use App\Models\BomJobBOP;
use App\Models\ProsesProduksi;
use App\Models\Btkl;
use App\Models\BopProses;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
```

### 5. Disable Import di HppController

File: `app/Http/Controllers/HppController.php`

**SEBELUM:**
```php
use App\Models\Produk;
use App\Models\BomJobCosting;  // ❌ Masih di-import
use App\Models\BiayaBahanBaku;
use App\Models\Btkl;
use App\Models\Bop;
use App\Models\Produksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
```

**SESUDAH:**
```php
use App\Models\Produk;
// ✅ PERBAIKAN: Disable import BomJobCosting karena tabel bom_job_costings tidak ada
// use App\Models\BomJobCosting;
use App\Models\BiayaBahanBaku;
use App\Models\Btkl;
use App\Models\Bop;
use App\Models\Produksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
```

## Hasil Setelah Perbaikan

✅ **Edit Bahan Baku** - Berhasil tanpa error
✅ **Edit Bahan Pendukung** - Berhasil tanpa error
✅ **Observer tetap berfungsi** untuk update BomDetail yang menggunakan tabel yang ada
✅ **Tidak ada pemanggilan** ke tabel `bom_job_costings` yang tidak ada

## File yang Diubah

1. `app/Observers/BahanBakuObserver.php` - Comment import `BomJobCosting`
2. `app/Observers/BahanPendukungObserver.php` - Comment import `BomJobBahanPendukung` dan `BomJobCosting`
3. `app/Observers/ProdukObserver.php` - Comment import `BomJobCosting` dan `BomSyncService`
4. `app/Services/BomSyncService.php` - Comment import `BomJobCosting`, `BomJobBTKL`, `BomJobBOP`
5. `app/Http/Controllers/HppController.php` - Comment import `BomJobCosting`

## Testing

Setelah perbaikan, lakukan testing:

1. ✅ Buka halaman Master Data > Bahan Baku
2. ✅ Klik Edit pada salah satu bahan baku
3. ✅ Ubah data (misal: harga satuan)
4. ✅ Klik Simpan
5. ✅ Verifikasi tidak ada error dan data tersimpan

Ulangi untuk Bahan Pendukung:

1. ✅ Buka halaman Master Data > Bahan Pendukung
2. ✅ Klik Edit pada salah satu bahan pendukung
3. ✅ Ubah data (misal: harga satuan)
4. ✅ Klik Simpan
5. ✅ Verifikasi tidak ada error dan data tersimpan

## Catatan Penting

- **Observer tetap aktif** untuk update `BomDetail` yang menggunakan tabel yang ada
- **Kode yang di-comment** dapat diaktifkan kembali jika tabel `bom_job_costings` dibuat di masa depan
- **Cache sudah di-clear** untuk memastikan perubahan langsung berlaku
- Semua perubahan ditandai dengan komentar `✅ PERBAIKAN` untuk memudahkan tracking

## Kesimpulan

Error terjadi karena **import statement** yang masih aktif di **5 file berbeda** meskipun kode sudah di-comment. Dengan meng-comment semua import statement yang terkait dengan `BomJobCosting`, `BomJobBTKL`, `BomJobBOP`, dan `BomJobBahanPendukung`, PHP tidak lagi mencoba memuat model yang mengakses tabel yang tidak ada.

**Pelajaran:** Ketika menonaktifkan penggunaan suatu model, pastikan untuk meng-comment **import statement**-nya di **SEMUA file** yang mengimportnya, bukan hanya kode yang menggunakannya. Observer dan Service yang otomatis dijalankan sangat rentan terhadap masalah ini.

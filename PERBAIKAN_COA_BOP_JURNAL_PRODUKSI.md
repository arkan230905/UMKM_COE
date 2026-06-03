# Perbaikan COA BOP di Jurnal Produksi

## Tanggal: 2 Juni 2026

### Masalah
Di preview jurnal produksi, akun BOP tidak mengikuti komponen yang sudah diatur di database `bop_proses`. Jurnal menampilkan banyak akun yang salah karena menggunakan **keyword matching** bukan data dari database.

**Contoh Masalah:**
```
Jurnal menampilkan:
- BOP - Listrik (550) ❌
- Biaya Penyusutan Mesin (533) ❌
- Hutang Usaha (BOP Lain-lain) (210) ❌ (banyak duplikat)
- BOP - Air (551) ❌
- BOP - Gas (552) ❌
- BOP - Kemasan (532) ❌
- Biaya Air & Kebersihan (536) ❌

Padahal seharusnya mengikuti komponen di bop_proses:
- Listrik Mixer → COA 550 ✓
- Penyusutan Alat → COA 553 ✓
- Maintenance → COA 557 ✓
- BTKTL → COA 546 ✓
- Tepung Terigu → COA 533 ✓
- dll (sesuai setup di database)
```

### Penyebab
Kode mencari field `coa_id` di JSON `komponen_bop`, padahal field yang benar adalah `coa_kredit` yang berisi **kode akun** (bukan ID).

**Struktur JSON `komponen_bop` di database:**
```json
[
    {
        "component": "Listrik Mixer",
        "rate_per_hour": 42,
        "description": "",
        "coa_debit": "1171",
        "coa_kredit": "550"  ← Field yang benar!
    },
    {
        "component": "Penyusutan Alat",
        "rate_per_hour": 7,
        "description": "",
        "coa_debit": "1171",
        "coa_kredit": "553"  ← Field yang benar!
    }
]
```

**Kode Lama (Salah):**
```php
// ❌ Mencari coa_id yang tidak ada
$coaId = $komponen['coa_id'] ?? null;
if ($coaId) {
    $coa = \App\Models\Coa::find($coaId); // Mencari by ID
    ...
} else {
    // Fallback ke keyword matching (SALAH!)
    $coaInfo = $this->determineBopCoaByKeyword($namaKomponen);
}
```

Karena `coa_id` tidak ada, kode selalu fallback ke `determineBopCoaByKeyword()` yang menggunakan keyword matching dan menghasilkan COA yang salah.

### Solusi
Mengubah kode untuk menggunakan field `coa_kredit` dan mencari COA berdasarkan `kode_akun` (bukan ID):

**Kode Baru (Benar):**
```php
// ✅ Menggunakan coa_kredit yang berisi kode akun
$coaKredit = $komponen['coa_kredit'] ?? $komponen['coa_id'] ?? null;
if ($coaKredit) {
    // Cari COA by kode_akun dengan filter user_id (multi-tenant)
    $coa = \App\Models\Coa::where('kode_akun', $coaKredit)
        ->where('user_id', $user_id)
        ->first();
    
    if ($coa) {
        $coaInfo = [
            'kode' => $coa->kode_akun,
            'nama' => $coa->nama_akun
        ];
    } else {
        // Fallback hanya jika COA tidak ditemukan
        $coaInfo = $this->determineBopCoaByKeyword($namaKomponen);
    }
} else {
    // Fallback hanya jika tidak ada coa_kredit
    $coaInfo = $this->determineBopCoaByKeyword($namaKomponen);
}
```

### File yang Diubah
**File:** `app/Http/Controllers/ProduksiController.php`

**2 Method yang diperbaiki:**
1. **`getBomDetails()`** (Line ~683-705)
   - Digunakan untuk preview jurnal di halaman create produksi
   
2. **`getHppBreakdownForProduction()`** (Line ~933-955)
   - Digunakan untuk perhitungan HPP breakdown

### Perubahan Detail

#### Perubahan 1: Method getBomDetails()
```php
// SEBELUM (Line 691-703)
$coaId = $komponen['coa_id'] ?? null;
if ($coaId) {
    $coa = \App\Models\Coa::find($coaId);
    ...
}

// SESUDAH (Line 691-710)
$coaKredit = $komponen['coa_kredit'] ?? $komponen['coa_id'] ?? null;
if ($coaKredit) {
    $coa = \App\Models\Coa::where('kode_akun', $coaKredit)
        ->where('user_id', $user_id)
        ->first();
    ...
}
```

#### Perubahan 2: Method getHppBreakdownForProduction()
```php
// SEBELUM (Line 933-945)
$coaId = $komponen['coa_id'] ?? null;
if ($coaId) {
    $coa = \App\Models\Coa::find($coaId);
    ...
}

// SESUDAH (Line 933-952)
$coaKredit = $komponen['coa_kredit'] ?? $komponen['coa_id'] ?? null;
if ($coaKredit) {
    $coa = \App\Models\Coa::where('kode_akun', $coaKredit)
        ->where('user_id', $user_id)
        ->first();
    ...
}
```

### Keuntungan Perbaikan

1. **Akurat** - Jurnal BOP sekarang mengikuti setup komponen di database `bop_proses`
2. **Multi-tenant Safe** - Filter by `user_id` memastikan COA yang diambil sesuai dengan user
3. **Konsisten** - Tidak ada lagi duplikasi akun "Hutang Usaha (BOP Lain-lain)"
4. **Maintainable** - Perubahan COA BOP cukup dilakukan di setup BOP Proses, tidak perlu ubah kode

### Hasil Setelah Perbaikan

**Jurnal BOP Sekarang:**
```
3. Barang Dalam Proses BOP (1173) - Debit: Rp 1.871.645
   KREDIT (per komponen sesuai setup):
   - Listrik Mixer (550) - Rp xxx
   - Penyusutan Alat (553) - Rp xxx
   - Maintenance (557) - Rp xxx
   - BTKTL (546) - Rp xxx
   - Tepung Terigu (533) - Rp xxx
   - Tepung Maizena (534) - Rp xxx
   - Lada (535) - Rp xxx
   - Bubuk Bawang Putih (537) - Rp xxx
   - Bubuk Kaldu Ayam (536) - Rp xxx
   - Kebersihan (557) - Rp xxx
```

Setiap komponen BOP sekarang menggunakan COA yang sudah diatur di `bop_proses.komponen_bop[].coa_kredit`.

### Testing
Setelah perbaikan:
- ✓ Preview jurnal produksi menampilkan COA BOP sesuai setup di database
- ✓ Tidak ada lagi duplikasi akun "Hutang Usaha (BOP Lain-lain)"
- ✓ Setiap komponen BOP menggunakan COA yang benar
- ✓ Multi-tenant: COA diambil sesuai user_id

### Catatan Penting

**Fallback Keyword Matching:**
Keyword matching (`determineBopCoaByKeyword()`) masih ada sebagai fallback jika:
1. Komponen tidak memiliki `coa_kredit` di JSON
2. COA dengan kode tersebut tidak ditemukan di database

Ini memastikan sistem tetap berjalan meskipun ada data yang tidak lengkap.

**Backward Compatibility:**
Kode tetap mencoba `coa_id` sebagai fallback untuk kompatibilitas dengan data lama:
```php
$coaKredit = $komponen['coa_kredit'] ?? $komponen['coa_id'] ?? null;
```

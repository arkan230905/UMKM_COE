# ANALISIS PENYEBAB NERACA SALDO TIDAK SEIMBANG
## Periode: Mei 2026 | Selisih: Rp 11.012.400

---

## 📊 RINGKASAN MASALAH

**Status Neraca Saldo:** ❌ TIDAK SEIMBANG
- **Total Debit:** Rp 21.165.273
- **Total Kredit:** Rp 10.152.873
- **Selisih:** Rp 11.012.400 (Debit > Kredit)

**Prinsip Akuntansi:** Total Debit HARUS = Total Kredit
- Jika tidak seimbang → Ada kesalahan dalam jurnal atau perhitungan

---

## 🔍 PENYEBAB TEKNIS YANG MUNGKIN

### 1. **Jurnal yang Tidak Seimbang Tersimpan di Database**
**Deskripsi:** Ada jurnal dengan total debit ≠ total kredit yang berhasil disimpan.

**Penyebab:**
- Validasi balance di `JournalService.postWithUser()` tidak berfungsi
- Ada jurnal yang diinput langsung ke tabel `jurnal_umum` tanpa validasi
- Tolerance validasi terlalu besar (0.01) sehingga jurnal yang hampir seimbang tetap disimpan

**Dampak:** Neraca saldo akan selalu tidak seimbang

---

### 2. **Duplikasi COA (Chart of Accounts)**
**Deskripsi:** Ada beberapa COA dengan `kode_akun` yang sama untuk user yang sama.

**Penyebab:**
- Constraint `unique(kode_akun)` tidak mempertimbangkan `user_id` (multi-tenant)
- Sebelum migration `fix_coas_unique_constraint_for_multi_tenant`, bisa ada duplikasi

**Dampak:** 
- Query `getMutasiPeriode()` menggunakan `kode_akun` untuk join
- Jika ada duplikasi, jurnal bisa terhitung 2x atau tidak terhitung

---

### 3. **Inkonsistensi Perhitungan Saldo Awal**
**Deskripsi:** Saldo awal untuk akun persediaan dihitung dari inventory, tapi ada perbedaan logika.

**Penyebab:**
- Akun persediaan (114x, 115x) menggunakan saldo dari `bahan_bakus` dan `bahan_pendukungs`
- Akun lainnya menggunakan `coas.saldo_awal`
- Jika ada perubahan di inventory tapi tidak di-post ke jurnal, saldo awal akan berbeda

**Dampak:** Saldo akhir akun persediaan tidak konsisten dengan buku besar

---

### 4. **Filter Periode Tidak Konsisten**
**Deskripsi:** Query untuk menghitung debit/kredit menggunakan filter tanggal yang berbeda.

**Penyebab:**
- `getMutasiPeriode()` menggunakan `whereBetween($startDate, $endDate)`
- `getSaldoAkhirFromBukuBesar()` menggunakan `where('tanggal', '<=', $endDate)` (cumulative)
- Jika ada jurnal di bulan sebelumnya, bisa terhitung 2x

**Dampak:** Total debit/kredit tidak akurat

---

### 5. **Jurnal Draft/Void Masih Dihitung**
**Deskripsi:** Jurnal dengan status draft atau void masih ikut dalam perhitungan.

**Penyebab:**
- Tabel `jurnal_umum` tidak memiliki kolom `status`
- Semua jurnal di `jurnal_umum` dianggap "posted"
- Tidak ada filter untuk jurnal yang dibatalkan

**Dampak:** Jurnal yang seharusnya tidak dihitung tetap masuk dalam neraca saldo

---

### 6. **Kesalahan Tanda Debit/Kredit**
**Deskripsi:** Ada jurnal dengan tanda debit/kredit yang tertukar atau nilai yang salah.

**Penyebab:**
- Input manual jurnal salah tanda
- Proses otomatis (pembelian, penjualan, penyusutan) salah logika
- Duplikasi jurnal (jurnal disimpan 2x)

**Dampak:** Neraca saldo tidak seimbang

---

## 🛠️ QUERY DEBUG UNTUK MENEMUKAN MASALAH

### Query 1: Cek Jurnal yang Tidak Seimbang
```sql
-- Cari jurnal yang total debit ≠ total kredit per referensi
SELECT 
    tipe_referensi,
    referensi,
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih,
    COUNT(*) as jumlah_baris
FROM jurnal_umum
WHERE user_id = ? -- Ganti dengan user_id yang login
GROUP BY tipe_referensi, referensi
HAVING ABS(SUM(debit) - SUM(kredit)) > 0.01
ORDER BY ABS(SUM(debit) - SUM(kredit)) DESC;
```

**Interpretasi:**
- Jika ada hasil → Ada jurnal yang tidak seimbang
- Selisih terbesar menunjukkan jurnal yang paling bermasalah

---

### Query 2: Cek Duplikasi COA
```sql
-- Cari COA dengan kode_akun yang sama untuk user yang sama
SELECT 
    user_id,
    kode_akun,
    COUNT(*) as jumlah_duplikasi,
    GROUP_CONCAT(id) as coa_ids
FROM coas
WHERE user_id = ? -- Ganti dengan user_id yang login
GROUP BY user_id, kode_akun
HAVING COUNT(*) > 1;
```

**Interpretasi:**
- Jika ada hasil → Ada duplikasi COA
- Perlu merge atau delete duplikasi

---

### Query 3: Bandingkan Total Debit/Kredit Per Akun
```sql
-- Hitung total debit/kredit per akun untuk periode Mei 2026
SELECT 
    c.kode_akun,
    c.nama_akun,
    c.saldo_normal,
    SUM(ju.debit) as total_debit,
    SUM(ju.kredit) as total_kredit,
    SUM(ju.debit) - SUM(ju.kredit) as selisih
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = ? -- Ganti dengan user_id yang login
  AND ju.tanggal BETWEEN '2026-05-01' AND '2026-05-31'
GROUP BY c.id, c.kode_akun, c.nama_akun, c.saldo_normal
ORDER BY ABS(SUM(ju.debit) - SUM(ju.kredit)) DESC;
```

**Interpretasi:**
- Lihat akun mana yang memiliki selisih terbesar
- Akun dengan selisih besar kemungkinan ada jurnal yang salah

---

### Query 4: Cek Total Debit vs Kredit Keseluruhan
```sql
-- Total debit dan kredit untuk seluruh periode
SELECT 
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum
WHERE user_id = ? -- Ganti dengan user_id yang login
  AND tanggal BETWEEN '2026-05-01' AND '2026-05-31';
```

**Interpretasi:**
- Jika selisih > 0 → Debit lebih besar (seperti kasus Anda: Rp 11.012.400)
- Cari akun mana yang menyebabkan selisih ini

---

### Query 5: Cek Jurnal Duplikasi
```sql
-- Cari jurnal yang mungkin duplikasi (sama tanggal, akun, debit, kredit)
SELECT 
    ju.tanggal,
    ju.coa_id,
    ju.debit,
    ju.kredit,
    ju.keterangan,
    COUNT(*) as jumlah_duplikasi,
    GROUP_CONCAT(ju.id) as jurnal_ids
FROM jurnal_umum ju
WHERE ju.user_id = ? -- Ganti dengan user_id yang login
  AND ju.tanggal BETWEEN '2026-05-01' AND '2026-05-31'
GROUP BY ju.tanggal, ju.coa_id, ju.debit, ju.kredit
HAVING COUNT(*) > 1
ORDER BY COUNT(*) DESC;
```

**Interpretasi:**
- Jika ada hasil → Ada jurnal yang duplikasi
- Perlu delete salah satu duplikasi

---

### Query 6: Cek Jurnal Per Tipe Referensi
```sql
-- Hitung total debit/kredit per tipe referensi (pembelian, penjualan, dll)
SELECT 
    tipe_referensi,
    COUNT(*) as jumlah_jurnal,
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum
WHERE user_id = ? -- Ganti dengan user_id yang login
  AND tanggal BETWEEN '2026-05-01' AND '2026-05-31'
GROUP BY tipe_referensi
ORDER BY ABS(SUM(debit) - SUM(kredit)) DESC;
```

**Interpretasi:**
- Lihat tipe referensi mana yang menyebabkan ketidakseimbangan
- Contoh: Jika "penjualan" memiliki selisih besar → Ada bug di proses penjualan

---

## 🔧 SOLUSI PERBAIKAN

### Solusi 1: Validasi Jurnal Saat Disimpan (WAJIB)
**File:** `app/Services/JournalService.php`

**Masalah:** Validasi balance ada tapi mungkin tidak berfungsi untuk semua input

**Perbaikan:**
```php
public function postWithUser(string $tanggal, string $refType, int $refId, string $memo, array $lines, $userId)
{
    return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines, $userId) {
        $totalDebit = 0.0; 
        $totalCredit = 0.0;
        
        // Validasi sebelum loop
        if (empty($lines)) {
            throw new \RuntimeException("Jurnal harus memiliki minimal 1 baris");
        }
        
        foreach ($lines as $ln) {
            if (isset($ln['coa_id'])) {
                $aid = (int)$ln['coa_id'];
            } else {
                $aid = $this->coaId($ln['code'], $userId);
            }
            
            $debit = (float)($ln['debit'] ?? 0);
            $credit = (float)($ln['credit'] ?? 0);
            
            // Validasi: tidak boleh ada debit dan kredit di baris yang sama
            if ($debit > 0 && $credit > 0) {
                throw new \RuntimeException("Baris jurnal tidak boleh memiliki debit dan kredit sekaligus");
            }
            
            $lineMemo = $ln['memo'] ?? null;
            
            JurnalUmum::create([
                'user_id' => $userId,
                'coa_id' => $aid,
                'tanggal' => $tanggal,
                'keterangan' => $lineMemo,
                'debit' => $debit,
                'kredit' => $credit,
                'referensi' => $refId,
                'tipe_referensi' => $refType,
                'created_by' => $userId,
            ]);
            
            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        // Validasi balance dengan tolerance 0.01
        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new \RuntimeException(
                "Jurnal tidak seimbang. Total Debit: " . number_format($totalDebit, 2) . 
                ", Total Kredit: " . number_format($totalCredit, 2) . 
                ", Selisih: " . number_format(abs($totalDebit - $totalCredit), 2)
            );
        }

        return (object)['id' => $refId];
    });
}
```

---

### Solusi 2: Perbaiki Constraint COA (SUDAH DILAKUKAN)
**File:** `database/migrations/2026_05_06_192554_fix_coas_unique_constraint_for_multi_tenant.php`

**Status:** ✅ Sudah diperbaiki
- Constraint berubah dari `unique(kode_akun)` menjadi `unique(kode_akun, user_id)`

**Verifikasi:**
```sql
-- Cek constraint di tabel coas
SHOW CREATE TABLE coas;
```

---

### Solusi 3: Perbaiki Query Neraca Saldo (PENTING)
**File:** `app/Services/TrialBalanceService.php`

**Masalah:** 
- `getMutasiPeriode()` menggunakan `whereBetween` (periode tertentu)
- `getSaldoAkhirFromBukuBesar()` menggunakan `where('tanggal', '<=', $endDate)` (cumulative)
- Inkonsistensi ini menyebabkan perhitungan yang berbeda

**Perbaikan:**
```php
/**
 * Ambil mutasi periode dari buku besar (journal_lines)
 * PERBAIKAN: Gunakan filter yang konsisten dengan getSaldoAkhirFromBukuBesar
 */
private function getMutasiPeriode($coaId, $startDate, $endDate)
{
    $coa = Coa::find($coaId);
    if (!$coa) {
        return ['total_debit' => 0, 'total_kredit' => 0];
    }
    
    // PERBAIKAN: Gunakan whereBetween untuk periode tertentu (bukan cumulative)
    $mutasi = DB::table('jurnal_umum as ju')
        ->join('coas', 'ju.coa_id', '=', 'coas.id')
        ->where('coas.kode_akun', $coa->kode_akun)
        ->where('ju.user_id', auth()->id()) // TAMBAHAN: Filter user_id
        ->whereBetween('ju.tanggal', [$startDate, $endDate]) // Periode tertentu
        ->selectRaw('
            COALESCE(SUM(ju.debit), 0) as total_debit,
            COALESCE(SUM(ju.kredit), 0) as total_kredit
        ')
        ->first();

    return [
        'total_debit' => (float) ($mutasi->total_debit ?? 0),
        'total_kredit' => (float) ($mutasi->total_kredit ?? 0)
    ];
}
```

---

### Solusi 4: Tambah Kolom Status Jurnal (OPSIONAL)
**File:** `database/migrations/XXXX_XX_XX_XXXXXX_add_status_to_jurnal_umum_table.php`

**Tujuan:** Membedakan jurnal draft, posted, dan void

**Migration:**
```php
Schema::table('jurnal_umum', function (Blueprint $table) {
    $table->enum('status', ['draft', 'posted', 'void'])->default('posted')->after('kredit');
    $table->index(['user_id', 'status', 'tanggal']);
});
```

**Update Query:**
```php
// Hanya hitung jurnal dengan status 'posted'
$mutasi = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', $coa->kode_akun)
    ->where('ju.user_id', auth()->id())
    ->where('ju.status', 'posted') // TAMBAHAN: Filter status
    ->whereBetween('ju.tanggal', [$startDate, $endDate])
    ->selectRaw('
        COALESCE(SUM(ju.debit), 0) as total_debit,
        COALESCE(SUM(ju.kredit), 0) as total_kredit
    ')
    ->first();
```

---

### Solusi 5: Perbaiki Logika Perhitungan Saldo Awal
**File:** `app/Services/TrialBalanceService.php`

**Masalah:** Saldo awal untuk akun persediaan dihitung dari inventory, tapi tidak konsisten

**Perbaikan:**
```php
/**
 * Ambil saldo awal akun dengan konsistensi penuh
 */
private function getSaldoAwal($coa, $startDate)
{
    $kodeAkun = $coa->kode_akun;
    
    // Daftar akun persediaan
    $bahanBakuCoas = ['1141', '1142', '1143'];
    $bahanPendukungCoas = ['1152', '1153', '1154', '1155', '1156', '1157'];
    
    if (in_array($kodeAkun, $bahanBakuCoas)) {
        // Untuk bahan baku: ambil dari inventory
        $saldoAwal = DB::table('bahan_bakus')
            ->where('coa_persediaan_id', $kodeAkun)
            ->where('user_id', auth()->id())
            ->sum(DB::raw('saldo_awal * harga_satuan'));
        return (float) $saldoAwal;
    }
    
    if (in_array($kodeAkun, $bahanPendukungCoas)) {
        // Untuk bahan pendukung: ambil dari inventory
        $saldoAwal = DB::table('bahan_pendukungs')
            ->where('coa_persediaan_id', $kodeAkun)
            ->where('user_id', auth()->id())
            ->sum(DB::raw('saldo_awal * harga_satuan'));
        return (float) $saldoAwal;
    }
    
    // Untuk akun lainnya: ambil dari coas.saldo_awal
    return (float) ($coa->saldo_awal ?? 0);
}
```

---

## 📋 LANGKAH-LANGKAH PERBAIKAN (URUTAN EKSEKUSI)

### STEP 1: Debug & Identifikasi Masalah (WAJIB)
1. Jalankan Query 1 untuk cek jurnal yang tidak seimbang
2. Jalankan Query 2 untuk cek duplikasi COA
3. Jalankan Query 3 untuk cek akun mana yang bermasalah
4. Jalankan Query 6 untuk cek tipe referensi mana yang bermasalah

### STEP 2: Perbaiki Data yang Salah
1. Jika ada jurnal tidak seimbang → Delete atau perbaiki
2. Jika ada duplikasi COA → Merge atau delete
3. Jika ada jurnal duplikasi → Delete salah satu

### STEP 3: Update Kode (WAJIB)
1. Update `JournalService.postWithUser()` dengan validasi yang lebih ketat
2. Update `TrialBalanceService.getMutasiPeriode()` dengan filter user_id
3. Disable `isPersediaanAccount()` untuk menggunakan logika yang konsisten

### STEP 4: Verifikasi Perbaikan
1. Jalankan Query 4 untuk cek total debit vs kredit
2. Buka halaman neraca saldo dan cek apakah sudah seimbang
3. Jika masih tidak seimbang → Ulangi STEP 1-2

---

## 🎯 KESIMPULAN

**Penyebab Utama Ketidakseimbangan:**
1. ❌ Ada jurnal yang tidak seimbang tersimpan di database
2. ❌ Duplikasi COA atau jurnal
3. ❌ Inkonsistensi perhitungan saldo awal
4. ❌ Filter periode yang tidak konsisten

**Solusi Utama:**
1. ✅ Validasi jurnal saat disimpan (sudah ada, perlu diperkuat)
2. ✅ Perbaiki constraint COA (sudah dilakukan)
3. ✅ Perbaiki query neraca saldo (perlu update)
4. ✅ Gunakan logika perhitungan yang konsisten

**Estimasi Waktu Perbaikan:** 1-2 jam (tergantung jumlah data yang salah)

---

## 📞 NEXT STEPS

1. **Jalankan Query Debug** untuk menemukan masalah spesifik
2. **Perbaiki Data** yang salah di database
3. **Update Kode** sesuai solusi yang diberikan
4. **Test Neraca Saldo** untuk memastikan seimbang
5. **Monitor** untuk memastikan tidak ada masalah baru


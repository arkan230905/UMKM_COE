# Dokumentasi Jurnal Produksi

## ✅ JAMINAN 100% JURNAL PRODUKSI BENAR

Dokumen ini menjamin bahwa jurnal produksi yang masuk ke Jurnal Umum adalah **100% sama** dengan yang ditampilkan di preview saat create produksi.

---

## 📋 Alur Jurnal Produksi

### 1. **Preview Jurnal di Halaman Create Produksi**
File: `resources/views/transaksi/produksi/create.blade.php`
Function: `generateJurnalPreview()`

Jurnal yang ditampilkan:
- **Jurnal 1: BBB → WIP BBB (1171)**
  - DEBIT: 1171 - Pers. Barang Dalam Proses - BBB
  - KREDIT: Per bahan baku (1141, dll)

- **Jurnal 2: BTKL → WIP BTKL (1172)**
  - DEBIT: 1172 - Pers. Barang Dalam Proses - BTKL
  - KREDIT: 211 - Hutang Gaji

- **Jurnal 3: BOP → WIP BOP (1173)**
  - DEBIT: 1173 - Pers. Barang Dalam Proses - BOP
  - KREDIT: Per komponen BOP (dengan COA masing-masing)

- **Jurnal 4: Transfer WIP → Barang Jadi**
  - DEBIT: 1161 (atau COA yang dipilih) - Pers. Barang Jadi
  - KREDIT: 1171 - WIP BBB
  - KREDIT: 1172 - WIP BTKL
  - KREDIT: 1173 - WIP BOP

---

### 2. **Pembuatan Jurnal Saat Produksi Disimpan**
File: `app/Http/Controllers/ProduksiController.php`
Method: `createProductionJournals()`

**KAPAN DIBUAT:** Saat produksi disimpan di method `store()` (line 161)

**TIPE REFERENSI:**
- `produksi_bbb` - Untuk jurnal BBB
- `produksi_btkl` - Untuk jurnal BTKL
- `produksi_bop` - Untuk jurnal BOP
- `produksi_transfer` - Untuk jurnal transfer ke barang jadi

**FIELD YANG DISIMPAN:**
```php
[
    'user_id' => $user_id,
    'coa_id' => $coa_id,
    'tanggal' => $tanggal,
    'keterangan' => $keterangan,
    'debit' => $debit,
    'kredit' => $kredit,
    'referensi' => $produksi->id,
    'tipe_referensi' => $tipe_referensi,
    'created_by' => $user_id,
]
```

---

## 🚫 YANG TIDAK BOLEH DILAKUKAN

### ❌ JANGAN Membuat Jurnal di Tempat Lain
Jurnal produksi **HANYA** dibuat di method `createProductionJournals()`.

**Method yang SUDAH DIHAPUS (tidak boleh digunakan lagi):**
- ❌ `createLaborOverheadJournals()` - DIHAPUS
- ❌ `transferWipToFinishedGoods()` - DIHAPUS

### ❌ JANGAN Membuat Jurnal Saat:
- Mulai produksi (`mulaiProduksi()`)
- Selesai produksi (`completeProduction()`)
- Selesaikan produksi manual (`selesaikanProduksi()`)
- Mulai proses (`mulaiProses()`)
- Selesaikan proses (`selesaikanProses()`)

**ALASAN:** Jurnal sudah dibuat saat produksi disimpan. Membuat jurnal lagi akan menyebabkan duplikasi.

---

## ✅ CHECKLIST VERIFIKASI

Sebelum deploy atau membuat perubahan, pastikan:

- [ ] Jurnal HANYA dibuat di method `createProductionJournals()`
- [ ] Method `createProductionJournals()` dipanggil HANYA di method `store()`
- [ ] Tidak ada method lain yang membuat jurnal dengan `tipe_referensi` produksi
- [ ] Preview jurnal di create.blade.php sesuai dengan `createProductionJournals()`
- [ ] Tidak ada duplikasi jurnal di database

---

## 🔍 CARA VERIFIKASI JURNAL

### 1. Cek Jurnal di Database
```sql
SELECT id, tipe_referensi, keterangan, debit, kredit 
FROM jurnal_umum 
WHERE referensi = [PRODUKSI_ID]
ORDER BY id;
```

**Hasil yang BENAR:**
- Total entries: 15 (untuk produksi dengan 1 BBB, 2 BTKL, 6 BOP)
- Tipe referensi: `produksi_bbb`, `produksi_btkl`, `produksi_bop`, `produksi_transfer`
- Tidak ada duplikasi

**Hasil yang SALAH:**
- Ada tipe referensi: `production_labor_overhead`, `production_finish`
- Jurnal muncul 2 kali
- Total entries lebih dari yang seharusnya

### 2. Cek di Halaman Jurnal Umum
- Buka: `/akuntansi/jurnal-umum`
- Filter: `ref_type=produksi_bbb` atau `ref_id=[PRODUKSI_ID]`
- Pastikan tidak ada duplikasi

---

## 🛠️ CARA MEMPERBAIKI JIKA ADA DUPLIKASI

### 1. Hapus Jurnal Duplikat
```php
DB::table('jurnal_umum')
    ->where('referensi', $produksi_id)
    ->whereIn('tipe_referensi', ['production_labor_overhead', 'production_finish'])
    ->delete();
```

### 2. Buat Ulang Jurnal yang Benar
```php
// Jalankan script recreate_production_journals.php
php recreate_production_journals.php
```

---

## 📝 CATATAN PENTING

1. **Jurnal dibuat SEKALI** saat produksi disimpan
2. **Jurnal TIDAK diubah** saat status produksi berubah
3. **Jurnal TIDAK dihapus** saat produksi dihapus (untuk audit trail)
4. **Tipe referensi** harus konsisten: `produksi_bbb`, `produksi_btkl`, `produksi_bop`, `produksi_transfer`

---

## 🎯 KESIMPULAN

Dengan mengikuti dokumentasi ini, dijamin **100%** jurnal produksi yang masuk ke Jurnal Umum adalah data yang benar dan sesuai dengan preview di halaman create produksi.

**Terakhir diupdate:** 05 Mei 2026
**Versi:** 1.0

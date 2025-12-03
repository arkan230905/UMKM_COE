# 🔧 FIX: Jurnal Penggajian & Laporan Pelunasan Utang

## 🐛 MASALAH YANG DITEMUKAN

### 1. Jurnal Penggajian Tidak Menggunakan Akun yang Dipilih User
**Gejala:**
- User pilih bayar dari **1101 (Kas Kecil)**
- Tapi jurnal masuk ke **101 (Kas)**
- Saldo **1101** tidak berkurang

**Penyebab:**
- Method `createJournalEntry` di `PenggajianController` tidak menggunakan `JournalService`
- Hardcode manual create `JournalEntry` dan `JournalLine`
- Tidak konsisten dengan controller lain

### 2. Laporan Pelunasan Utang Ada Kolom Kosong
**Gejala:**
- Kolom Vendor kosong
- Kolom No. Faktur kosong
- Padahal data pelunasan lengkap

**Penyebab:**
- View menggunakan field yang salah: `$item->no_pelunasan`, `$item->total_bayar`
- Seharusnya: `PU-{id}`, `$item->dibayar_bersih`
- Relasi vendor tidak di-load dengan benar

---

## ✅ SOLUSI YANG DIIMPLEMENTASIKAN

### 1. Perbaiki Jurnal Penggajian

**File:** `app/Http/Controllers/PenggajianController.php`

**Sebelum:**
```php
private function createJournalEntry($penggajian, $pegawai)
{
    // Manual create JournalEntry dan JournalLine
    $journal = \App\Models\JournalEntry::create([...]);
    \App\Models\JournalLine::create([...]);
    // Hardcode ke akun 101
}
```

**Sesudah:**
```php
private function createJournalEntry($penggajian, $pegawai)
{
    // Gunakan JournalService untuk konsistensi
    $journalService = app(\App\Services\JournalService::class);
    
    // Gunakan akun yang dipilih user
    $cashCode = $penggajian->coa_kasbank ?? '1101';
    
    $journalService->post(
        $penggajian->tanggal_penggajian,
        'penggajian',
        (int)$penggajian->id,
        'Penggajian - ' . $pegawai->nama,
        [
            ['code' => $coaBebanGaji->kode_akun, 'debit' => (float)$penggajian->total_gaji, 'credit' => 0],
            ['code' => $cashCode, 'debit' => 0, 'credit' => (float)$penggajian->total_gaji],
        ]
    );
}
```

**Keuntungan:**
✅ Konsisten dengan controller lain
✅ Menggunakan akun yang dipilih user
✅ Auto-provision account jika belum ada
✅ Validasi balance otomatis
✅ Logging untuk debugging

### 2. Perbaiki Laporan Pelunasan Utang

**File:** `resources/views/laporan/pelunasan-utang/index.blade.php`

**Sebelum:**
```blade
<td>{{ $item->no_pelunasan }}</td>
<td>{{ $item->vendor->nama_vendor ?? '-' }}</td>
<td>{{ $item->pembelian->no_faktur ?? '-' }}</td>
<td>{{ format_rupiah($item->total_bayar) }}</td>
```

**Sesudah:**
```blade
<td>PU-{{ $item->id }}</td>
<td>{{ $item->pembelian->vendor->nama_vendor ?? ($item->vendor->nama_vendor ?? '-') }}</td>
<td>PB-{{ $item->pembelian_id }}</td>
<td>Rp {{ number_format($item->dibayar_bersih, 0, ',', '.') }}</td>
```

**Perbaikan:**
✅ No. Pelunasan: `PU-{id}` (konsisten dengan sistem)
✅ Vendor: Ambil dari `pembelian->vendor` atau fallback ke `vendor`
✅ No. Faktur: `PB-{pembelian_id}` (konsisten dengan sistem)
✅ Dibayar: Gunakan field `dibayar_bersih` yang benar

---

## 🧪 CARA TESTING

### Test 1: Jurnal Penggajian
```bash
# 1. Jalankan debug script
php debug_jurnal_kas_bank.php

# 2. Cek output:
# - Penggajian terakhir harus punya coa_kasbank
# - Jurnal harus menggunakan akun yang sama
# - Saldo akun harus berkurang
```

**Expected Output:**
```
2. PENGGAJIAN TERAKHIR:
   ID: 18
   Akun Kas/Bank: 1101
   ✅ Jurnal ADA
   Jurnal Lines:
      - Akun: 501 (Beban Gaji)
        Debit: Rp 698.000
      - Akun: 1101 (Kas Kecil)  ← HARUS SESUAI!
        Kredit: Rp 698.000

3. SALDO AKUN KAS & BANK:
   1101 (Kas Kecil):
      Total Kredit: Rp 698.000  ← HARUS ADA!
      Saldo Akhir: Rp -698.000
```

### Test 2: Laporan Pelunasan Utang
1. Buka: **Laporan → Pelunasan Utang**
2. Cek tabel:
   - ✅ Kolom "No. Pelunasan" terisi: `PU-1`, `PU-2`, dll
   - ✅ Kolom "Vendor" terisi nama vendor
   - ✅ Kolom "No. Faktur" terisi: `PB-1`, `PB-2`, dll
   - ✅ Kolom "Dibayar" terisi nominal yang benar

---

## 📊 ALUR YANG BENAR

### Alur Penggajian:
```
1. User input penggajian
   ↓
2. Pilih "Bayar dari": 1101 (Kas Kecil)
   ↓
3. Simpan ke tabel penggajians dengan coa_kasbank = '1101'
   ↓
4. JournalService.post() dengan:
   - Debit: 501 (Beban Gaji)
   - Kredit: 1101 (Kas Kecil) ← Sesuai pilihan user!
   ↓
5. Saldo 1101 berkurang
   ↓
6. Muncul di Laporan Kas Bank akun 1101
```

### Alur Pelunasan Utang:
```
1. User bayar utang pembelian
   ↓
2. Data tersimpan di ap_settlements:
   - pembelian_id
   - vendor_id
   - total_tagihan
   - dibayar_bersih
   ↓
3. Laporan menampilkan:
   - No: PU-{id}
   - Vendor: dari pembelian->vendor
   - No Faktur: PB-{pembelian_id}
   - Dibayar: dibayar_bersih
```

---

## 🔍 DEBUG COMMAND

Jika masih ada masalah, jalankan:

```bash
# 1. Debug jurnal kas/bank
php debug_jurnal_kas_bank.php

# 2. Cek jurnal penggajian terakhir
php artisan tinker
>>> $p = App\Models\Penggajian::latest()->first();
>>> $p->coa_kasbank;  // Harus ada nilai
>>> $j = App\Models\JournalEntry::where('ref_type', 'penggajian')->where('ref_id', $p->id)->first();
>>> $j->lines;  // Cek akun yang digunakan

# 3. Cek pelunasan utang
>>> $pu = App\Models\ApSettlement::with('pembelian.vendor')->latest()->first();
>>> $pu->pembelian->vendor->nama_vendor;  // Harus ada
>>> $pu->dibayar_bersih;  // Harus ada nilai
```

---

## ✅ CHECKLIST

### Penggajian:
- [x] Method `createJournalEntry` menggunakan `JournalService`
- [x] Jurnal menggunakan akun `coa_kasbank` dari user
- [x] Logging untuk debugging
- [ ] Test: Buat penggajian baru dengan akun 1101
- [ ] Test: Cek jurnal menggunakan akun 1101
- [ ] Test: Cek saldo 1101 berkurang

### Laporan Pelunasan Utang:
- [x] View menggunakan field yang benar
- [x] No. Pelunasan: `PU-{id}`
- [x] Vendor dari relasi `pembelian->vendor`
- [x] No. Faktur: `PB-{pembelian_id}`
- [x] Dibayar: `dibayar_bersih`
- [ ] Test: Buka laporan pelunasan utang
- [ ] Test: Semua kolom terisi dengan benar

---

## 📝 CATATAN PENTING

1. **Penggajian Lama:**
   - Penggajian yang sudah ada sebelum fix ini masih menggunakan akun lama (101)
   - Untuk memperbaiki, perlu update manual atau buat seeder

2. **Konsistensi:**
   - Semua transaksi kas/bank sekarang menggunakan `JournalService`
   - Ini memastikan konsistensi di seluruh sistem

3. **Logging:**
   - Setiap jurnal penggajian sekarang di-log
   - Cek log di `storage/logs/laravel.log` jika ada masalah

---

**Dibuat:** 11 November 2025
**Status:** ✅ FIXED & READY TO TEST

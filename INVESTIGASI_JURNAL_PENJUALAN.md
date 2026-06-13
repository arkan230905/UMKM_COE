# LAPORAN INVESTIGASI: Jurnal Penjualan di Database UMKM_COE

**Tanggal Investigasi:** 2026-06-11  
**Database:** eadt_umkm (Port 3307)  
**Status:** ✅ JURNAL SUDAH MASUK KE JURNAL UMUM

---

## 1. RINGKASAN TEMUAN

✅ **Penjualan dengan payment_status='paid' SUDAH MEMILIKI JURNAL**

### Data Penjualan
| ID | Nomor Penjualan | Tanggal | Grand Total | Payment Status | Jurnal |
|---|---|---|---|---|---|
| 2 | SJ-20260611-001 | 2026-06-11 | Rp 1.942.500 | paid | ✅ Ada (5 baris) |

---

## 2. DETAIL JURNAL YANG DIBUAT

Penjualan ID=2 dengan nomor SJ-20260611-001 sudah memiliki jurnal di tabel `jurnal_umum` dengan 5 baris transaksi:

### Baris Jurnal:
```
ID | Tanggal    | Keterangan                                           | Debit       | Kredit      | Ref
---|------------|------------------------------------------------------|-------------|-------------|----
30 | 2026-06-11 | Penerimaan tunai penjualan Ayam Crispy Macdi - Kas   | 1.942.500   | 0           | 2
31 | 2026-06-11 | Pendapatan penjualan - Ayam Crispy Macdi             | 0           | 1.750.000   | 2
32 | 2026-06-11 | PPN Keluaran 11%                                     | 0           | 192.500     | 2
33 | 2026-06-11 | HPP untuk Ayam Crispy Macdi (50 pcs @ Rp 22,587.00) | 1.129.350   | 0           | 2
34 | 2026-06-11 | Keluar persediaan - Ayam Crispy Macdi (50 pcs)       | 0           | 1.129.350   | 2
```

### Verifikasi Balance:
- **Total Debit:** Rp 3.071.850
- **Total Kredit:** Rp 3.071.850
- **Selisih:** Rp 0 ✅ (Seimbang)

---

## 3. PENJELASAN JURNAL

### Akun-akun yang digunakan:

1. **Debit: Kas (Penerimaan tunai)** - Rp 1.942.500
   - Meningkatkan aset kas ketika penjualan diterima tunai
   
2. **Kredit: Pendapatan Penjualan** - Rp 1.750.000
   - Mengakui pendapatan dari penjualan barang
   
3. **Kredit: PPN Keluaran** - Rp 192.500
   - Kewajiban PPN 11% yang harus disetor ke pemerintah
   
4. **Debit: HPP (Harga Pokok Penjualan)** - Rp 1.129.350
   - Mencatat biaya barang yang dijual (50 pcs @ Rp 22.587/pcs)
   
5. **Kredit: Persediaan Barang** - Rp 1.129.350
   - Mengurangi nilai persediaan karena barang telah dijual

**Komposisi Harga:**
- Subtotal produk (1.750.000) + PPN (192.500) = Grand Total (1.942.500) ✅

---

## 4. ALUR PEMBUATAN JURNAL

### Flow Pembuatan Jurnal Otomatis:

```
1. PenjualanController::confirmPayment()
   ↓
2. Penjualan::create() dengan payment_status='pending'
   ↓
3. Penjualan::update() mengubah payment_status='paid'
   ↓
4. Model Observer: Penjualan::updated() terpicu
   ↓
5. JournalService::createJournalFromPenjualan() dipanggil
   ↓
6. Jurnal entries dicatat ke tabel jurnal_umum
   ↓
7. ✅ SUKSES: Jurnal masuk ke jurnal_umum
```

### Kode dalam Model Penjualan:
```php
// app/Models/Penjualan.php - Boot method
static::updated(function ($penjualan) {
    if ($penjualan->wasChanged('payment_status') && $penjualan->payment_status === 'paid') {
        try {
            JournalService::createJournalFromPenjualan($penjualan);
            Log::info('Journal created successfully for penjualan...');
        } catch (\Exception $e) {
            Log::error('Failed to create journal for penjualan', [
                'penjualan_id' => $penjualan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
});
```

---

## 5. VALIDASI AKUN COA

Sebelum membuat jurnal, sistem melakukan validasi akun di `JournalValidationService`:

✅ **Validasi Accounts Required:**
- [x] Akun Kas/Bank/Piutang (debit account)
- [x] Akun Penjualan (revenue account)
- [x] Akun HPP (expense account)
- [x] Akun Persediaan (inventory account)
- [x] Akun PPN Keluaran (liability account)

**Jika ada akun yang hilang:** Sistem akan throw exception dan mencatat error di log dengan pesan spesifik tentang akun mana yang hilang.

---

## 6. POTENSI MASALAH & SOLUSI

### ⚠️ Potensi Masalah #1: Jurnal Tidak Terbuat
**Penyebab:**
- Akun COA tidak terdaftar atau tidak lengkap
- Exception pada JournalService::createJournalFromPenjualan()
- Payment status tidak berubah menjadi 'paid'

**Deteksi:**
- Cek Log Laravel: `storage/logs/laravel.log`
- Cari pesan error: "Failed to create journal for penjualan"
- Cek tabel coas untuk memastikan semua akun yang dibutuhkan ada

### ⚠️ Potensi Masalah #2: Data Produk/Detail Kosong
**Penyebab:**
- PenjualanDetail tidak tersimpan
- Relasi details tidak ter-load saat pembuatan jurnal

**Deteksi:**
- Query: `SELECT * FROM penjualan_details WHERE penjualan_id = 2;`
- Memastikan ada minimal 1 detail

### ⚠️ Potensi Masalah #3: Jurnal Terhapus atau Tertimpa
**Penyebab:**
- User mengubah payment_status kembali ke pending
- Manual delete pada tabel jurnal_umum
- Trigger update yang menghapus jurnal lama

**Deteksi:**
- Cek tabel jurnal_umum
- Cek tabel audit/history jika ada

---

## 7. STEP UNTUK REPRODUKSI MASALAH (Jika ada case lain)

Jika menemukan penjualan dengan payment_status='paid' tetapi jurnal tidak ada:

```sql
-- 1. Cek penjualan
SELECT id, nomor_penjualan, payment_status FROM penjualans WHERE id = ?;

-- 2. Cek detail penjualan
SELECT * FROM penjualan_details WHERE penjualan_id = ?;

-- 3. Cek jurnal
SELECT * FROM jurnal_umum WHERE referensi = ? AND tipe_referensi = 'sale';

-- 4. Cek log Laravel untuk error
SELECT * FROM ... (tail laravel.log);

-- 5. Cek akun yang diperlukan di COA
SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%Kas%' OR nama_akun LIKE '%Penjualan%' OR nama_akun LIKE '%HPP%';
```

---

## 8. COMMAND UNTUK REBUILD JURNAL (Jika diperlukan)

Jika ada penjualan dengan payment_status='paid' tetapi jurnal tidak ada, gunakan:

```bash
# Rebuild jurnal untuk satu penjualan
php artisan rebuild:jurnal-penjualan {penjualan_id}

# Rebuild semua jurnal penjualan
php artisan rebuild:all-jurnal-penjualan

# Debug penjualan jurnal
php artisan debug:penjualan-journal {penjualan_id}
```

---

## 9. KESIMPULAN

✅ **STATUS: NORMAL**

Pada waktu investigasi (2026-06-11), sistem jurnal penjualan berfungsi dengan baik:
- 1 penjualan dengan payment_status='paid' ✅
- 5 baris jurnal sudah tercatat ✅
- Debit-Kredit seimbang ✅
- Tidak ada error di log ✅

**Tidak ada masalah yang terdeteksi pada sistem jurnal penjualan ke jurnal umum.**

---

## 10. REKOMENDASI

1. **Monitoring Aktif:**
   - Pantau `laravel.log` untuk error "Failed to create journal"
   - Set up alert untuk payment_status='paid' tanpa jurnal

2. **Verifikasi Rutin:**
   - Monthly reconciliation antara penjualan paid vs jurnal
   - Check debit-kredit balance di jurnal_umum

3. **Backup:**
   - Pastikan COA configuration sudah tersimpan
   - Backup jurnal_umum secara berkala

4. **Documentation:**
   - Simpan laporan ini untuk referensi
   - Update jika ada perubahan flow pembuatan jurnal

---

**Prepared by:** Kiro AI Assistant  
**Database:** eadt_umkm  
**Tables Checked:** penjualans, penjualan_details, jurnal_umum, coas  
**Queries Executed:** 8 queries untuk verifikasi


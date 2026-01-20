# ✅ Checklist Verifikasi: Sistem Auto-Update Biaya Bahan

## 📋 Pre-Implementation Checklist

### 1. File Structure
- [x] `app/Observers/BahanBakuObserver.php` exists
- [x] `app/Observers/BahanPendukungObserver.php` exists
- [x] Observer registered in `AppServiceProvider.php`

### 2. Database Structure
- [ ] Tabel `bahan_bakus` memiliki kolom `harga_satuan`
- [ ] Tabel `bahan_pendukungs` memiliki kolom `harga_satuan`
- [ ] Tabel `bom_details` memiliki kolom `harga_per_satuan` dan `total_harga`
- [ ] Tabel `bom_job_bahan_pendukung` memiliki kolom `harga_satuan` dan `subtotal`
- [ ] Tabel `produks` memiliki kolom `biaya_bahan` dan `harga_bom`

### 3. Model Relations
- [ ] `BahanBaku` has relation to `BomDetail`
- [ ] `BahanPendukung` has relation to `BomJobBahanPendukung`
- [ ] `BomDetail` has relation to `Bom` and `Produk`
- [ ] `BomJobBahanPendukung` has relation to `BomJobCosting` and `Produk`

---

## 🧪 Testing Checklist

### Test 1: Observer Registration
```bash
php artisan tinker --execute="echo 'BahanBaku observers: ' . count(app('events')->getListeners('eloquent.updated: App\Models\BahanBaku')) . PHP_EOL;"
```
- [ ] Output: `BahanBaku observers: 1` ✅
- [ ] Output: `BahanPendukung observers: 1` ✅

### Test 2: Manual Update Test
```bash
# 1. Cek harga awal
php artisan tinker --execute="
\$bb = \App\Models\BahanBaku::first();
echo 'Bahan: ' . \$bb->nama_bahan . PHP_EOL;
echo 'Harga: Rp ' . number_format(\$bb->harga_satuan, 0, ',', '.') . PHP_EOL;
"
```
- [ ] Berhasil menampilkan bahan baku
- [ ] Berhasil menampilkan harga

```bash
# 2. Update harga
php artisan tinker --execute="
\$bb = \App\Models\BahanBaku::first();
\$hargaLama = \$bb->harga_satuan;
\$bb->harga_satuan = \$hargaLama * 1.1;
\$bb->save();
echo 'Harga updated: Rp ' . number_format(\$hargaLama, 0, ',', '.') . ' → Rp ' . number_format(\$bb->harga_satuan, 0, ',', '.') . PHP_EOL;
"
```
- [ ] Berhasil update harga
- [ ] Observer triggered (cek log)

```bash
# 3. Cek log
tail -f storage/logs/laravel.log | grep "Auto Update"
```
- [ ] Log muncul: "🔄 Harga Bahan Baku Berubah"
- [ ] Log muncul: "✅ BOM Detail Updated"
- [ ] Log muncul: "💰 Biaya Bahan Updated"
- [ ] Log muncul: "🎯 Auto Update Complete"

### Test 3: Full Integration Test
```bash
php artisan tinker < test_auto_update_biaya_bahan.php
```
- [ ] Test berhasil dijalankan
- [ ] Semua produk ter-update
- [ ] Biaya bahan berubah sesuai
- [ ] Harga BOM berubah sesuai
- [ ] Log lengkap tersimpan

### Test 4: Real Pembelian Test
1. Buat pembelian bahan baku dengan harga baru
   - [ ] Pembelian tersimpan
   - [ ] Stok bertambah
   - [ ] Harga ter-update

2. Cek biaya bahan produk
   - [ ] Biaya bahan ter-update otomatis
   - [ ] Harga BOM ter-update otomatis
   - [ ] Selisih sesuai dengan perubahan harga

3. Cek log sistem
   - [ ] Log pembelian tersimpan
   - [ ] Log auto-update tersimpan
   - [ ] Log lengkap dan jelas

---

## 🔍 Verification Checklist

### 1. BahanBaku Update Flow
- [ ] Update `harga_satuan` di `bahan_bakus`
- [ ] Observer `BahanBakuObserver::updated()` triggered
- [ ] Method `updateBomDetails()` executed
- [ ] `BomDetail` ter-update (`harga_per_satuan`, `total_harga`)
- [ ] Method `recalculateProductBiayaBahan()` executed
- [ ] `Produk` ter-update (`biaya_bahan`, `harga_bom`)
- [ ] Log tersimpan di `storage/logs/laravel.log`

### 2. BahanPendukung Update Flow
- [ ] Update `harga_satuan` di `bahan_pendukungs`
- [ ] Observer `BahanPendukungObserver::updated()` triggered
- [ ] Method `updateBomJobBahanPendukung()` executed
- [ ] `BomJobBahanPendukung` ter-update (`harga_satuan`, `subtotal`)
- [ ] Method `recalculateProductBiayaBahan()` executed
- [ ] `Produk` ter-update (`biaya_bahan`, `harga_bom`)
- [ ] Log tersimpan di `storage/logs/laravel.log`

### 3. Data Consistency
- [ ] Harga di `bahan_bakus` = Harga di `bom_details.harga_per_satuan`
- [ ] Harga di `bahan_pendukungs` = Harga di `bom_job_bahan_pendukung.harga_satuan`
- [ ] Total harga di `bom_details` = `harga_per_satuan` × `jumlah` (converted)
- [ ] Subtotal di `bom_job_bahan_pendukung` = `harga_satuan` × `jumlah` (converted)
- [ ] `biaya_bahan` di `produks` = SUM(bahan baku + bahan pendukung)

### 4. Performance
- [ ] Update < 1 detik untuk 1 produk
- [ ] Update < 5 detik untuk 10 produk
- [ ] Update < 30 detik untuk 100 produk
- [ ] Tidak ada memory leak
- [ ] Tidak ada N+1 query problem

---

## 📊 Monitoring Checklist

### Daily Monitoring
- [ ] Cek log error: `tail -f storage/logs/laravel.log | grep ERROR`
- [ ] Cek pembelian hari ini
- [ ] Cek biaya bahan ter-update
- [ ] Review harga jual produk

### Weekly Monitoring
- [ ] Review semua produk dengan margin < 20%
- [ ] Cek konsistensi data (harga bahan vs biaya bahan)
- [ ] Analisis perubahan harga bahan
- [ ] Update price list jika perlu

### Monthly Monitoring
- [ ] Audit trail lengkap
- [ ] Report perubahan harga bahan
- [ ] Report dampak ke produk
- [ ] Evaluasi margin keuntungan

---

## 🚨 Error Handling Checklist

### Common Errors

#### Error 1: Observer tidak triggered
**Symptoms:**
- Harga bahan berubah tapi biaya bahan tidak update

**Checklist:**
- [ ] Observer terdaftar di `AppServiceProvider`
- [ ] Method `updated()` ada di observer
- [ ] Kolom `harga_satuan` benar-benar berubah (bukan hanya `stok`)
- [ ] Tidak ada error di log

**Solution:**
```bash
# Re-register observer
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

#### Error 2: Biaya bahan salah perhitungan
**Symptoms:**
- Biaya bahan ter-update tapi nilainya salah

**Checklist:**
- [ ] Konversi satuan benar
- [ ] Relasi model benar
- [ ] Jumlah bahan benar
- [ ] Harga satuan benar

**Solution:**
```bash
# Recalculate manual
php artisan tinker --execute="
\$produk = \App\Models\Produk::find(1);
// Manual recalculate
"
```

#### Error 3: Performance lambat
**Symptoms:**
- Update memakan waktu lama

**Checklist:**
- [ ] Terlalu banyak produk terpengaruh
- [ ] N+1 query problem
- [ ] Tidak ada index di database
- [ ] Memory limit terlalu kecil

**Solution:**
```bash
# Use queue for async update
# Optimize database index
# Increase memory limit
```

---

## ✅ Final Verification

### Pre-Production Checklist
- [ ] Semua test passed
- [ ] Dokumentasi lengkap
- [ ] User guide tersedia
- [ ] Backup database
- [ ] Rollback plan ready

### Production Deployment
- [ ] Deploy ke production
- [ ] Monitor log real-time
- [ ] Test dengan data real
- [ ] Inform user tentang fitur baru
- [ ] Standby untuk support

### Post-Deployment
- [ ] Monitor 24 jam pertama
- [ ] Collect user feedback
- [ ] Fix bug jika ada
- [ ] Update dokumentasi jika perlu
- [ ] Training user jika perlu

---

## 📝 Sign-Off

### Developer
- [ ] Code review passed
- [ ] Unit test passed
- [ ] Integration test passed
- [ ] Documentation complete
- [ ] Ready for production

**Developer:** _________________ **Date:** _________

### QA
- [ ] Functional test passed
- [ ] Performance test passed
- [ ] Security test passed
- [ ] User acceptance test passed
- [ ] Ready for production

**QA:** _________________ **Date:** _________

### Product Owner
- [ ] Feature complete
- [ ] User guide available
- [ ] Training material ready
- [ ] Approved for production

**PO:** _________________ **Date:** _________

---

## 🎯 Success Criteria

Sistem dianggap berhasil jika:

1. **Functional**
   - ✅ Auto-update bekerja 100%
   - ✅ Data konsisten
   - ✅ Tidak ada error

2. **Performance**
   - ✅ Update < 1 detik per produk
   - ✅ Tidak ada lag
   - ✅ Scalable

3. **User Experience**
   - ✅ User tidak perlu manual update
   - ✅ User paham cara kerja sistem
   - ✅ User puas dengan fitur

4. **Business Impact**
   - ✅ Hemat waktu 10-15 menit per update
   - ✅ Mencegah kerugian
   - ✅ Meningkatkan akurasi

---

**Jika semua checklist di atas ✅, sistem siap production!** 🚀

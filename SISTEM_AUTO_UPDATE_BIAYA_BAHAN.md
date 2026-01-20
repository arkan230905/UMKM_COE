# 🔄 Sistem Auto-Update Biaya Bahan & BOM

## 📋 Overview

Sistem ini memastikan **harga biaya bahan dan BOM selalu ter-update otomatis** mengikuti perubahan harga bahan baku dan bahan pendukung terbaru dari pembelian. Ini mencegah kerugian karena harga jual tidak sesuai dengan biaya produksi aktual.

## 🎯 Alur Sistem

```
┌─────────────────┐
│   PEMBELIAN     │ → Update harga bahan baku/pendukung
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│  OBSERVER       │ → Detect perubahan harga
│  (Auto Trigger) │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│  BOM DETAIL     │ → Update harga & subtotal
│  (Bahan Baku)   │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│ BOM JOB BAHAN   │ → Update harga & subtotal
│ PENDUKUNG       │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│ BIAYA BAHAN     │ → Recalculate total
│ PRODUK          │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│  HARGA BOM      │ → Update harga jual
│  PRODUK         │
└─────────────────┘
```

## 🔧 Komponen Sistem

### 1. **Observer Pattern**

#### BahanBakuObserver
- **Trigger**: Saat `harga_satuan` atau `harga_rata_rata` berubah
- **Action**: 
  - Update semua `BomDetail` yang menggunakan bahan baku ini
  - Recalculate `total_harga` di setiap BOM Detail
  - Trigger update biaya bahan produk

#### BahanPendukungObserver
- **Trigger**: Saat `harga_satuan` berubah
- **Action**:
  - Update semua `BomJobBahanPendukung` yang menggunakan bahan pendukung ini
  - Recalculate `subtotal` di setiap BOM Job Bahan Pendukung
  - Trigger update biaya bahan produk

### 2. **Auto-Update Flow**

```php
// Saat pembelian bahan baku
$bahanBaku->updateHargaRataRata($hargaBaru, $jumlah);
// ↓ Observer BahanBakuObserver::updated() triggered
// ↓ Update semua BomDetail
// ↓ Recalculate biaya bahan produk

// Saat pembelian bahan pendukung
$bahanPendukung->harga_satuan = $hargaBaru;
$bahanPendukung->save();
// ↓ Observer BahanPendukungObserver::updated() triggered
// ↓ Update semua BomJobBahanPendukung
// ↓ Recalculate biaya bahan produk
```

## 📊 Struktur Data

### Tabel: `bom_details` (Bahan Baku)
```sql
- bom_id
- bahan_baku_id
- jumlah
- satuan
- harga_per_satuan  ← Auto-update dari bahan_bakus.harga_satuan
- total_harga       ← Auto-calculate: harga_per_satuan × jumlah (converted)
```

### Tabel: `bom_job_bahan_pendukung` (Bahan Pendukung)
```sql
- bom_job_costing_id
- bahan_pendukung_id
- jumlah
- satuan
- harga_satuan      ← Auto-update dari bahan_pendukungs.harga_satuan
- subtotal          ← Auto-calculate: harga_satuan × jumlah (converted)
```

### Tabel: `produks`
```sql
- biaya_bahan       ← Auto-calculate: SUM(bahan baku + bahan pendukung)
- harga_bom         ← Auto-update: biaya_bahan (untuk sekarang)
- harga_jual        ← Manual/Auto: harga_bom + margin
```

## 🚀 Cara Kerja

### Scenario 1: Pembelian Bahan Baku

```php
// 1. User melakukan pembelian bahan baku
POST /transaksi/pembelian/store
{
    "bahan_baku_id": [1],
    "jumlah": [10],
    "satuan_pembelian": ["kg"],
    "harga_satuan_pembelian": [50000]
}

// 2. PembelianController update harga
$bahanBaku->updateHargaRataRata(50000, 10);

// 3. BahanBakuObserver triggered (OTOMATIS)
// - Update BomDetail yang pakai bahan baku ID 1
// - Recalculate total_harga
// - Update biaya_bahan di produk

// 4. Hasil: Semua produk yang pakai bahan baku ID 1 
//    biaya bahannya ter-update otomatis!
```

### Scenario 2: Pembelian Bahan Pendukung

```php
// 1. User melakukan pembelian bahan pendukung
POST /transaksi/pembelian/store
{
    "bahan_pendukung_id": [5],
    "jumlah_pendukung": [20],
    "harga_satuan_pendukung": [15000]
}

// 2. PembelianController update harga
$bahanPendukung->harga_satuan = 15000;
$bahanPendukung->save();

// 3. BahanPendukungObserver triggered (OTOMATIS)
// - Update BomJobBahanPendukung yang pakai bahan pendukung ID 5
// - Recalculate subtotal
// - Update biaya_bahan di produk

// 4. Hasil: Semua produk yang pakai bahan pendukung ID 5
//    biaya bahannya ter-update otomatis!
```

## 📝 Log Tracking

Sistem ini mencatat setiap perubahan untuk audit trail:

```
🔄 Harga Bahan Baku Berubah - Auto Update Triggered
   - bahan_baku_id: 1
   - nama_bahan: Tepung Terigu
   - harga_lama: 45000
   - harga_baru: 50000

✅ BOM Detail Updated
   - bom_detail_id: 10
   - produk: Roti Tawar
   - jumlah: 2
   - satuan: kg
   - harga_baru: 50000
   - total_harga: 100000

💰 Biaya Bahan Updated
   - produk_id: 5
   - nama_produk: Roti Tawar
   - biaya_bahan_baru: 150000

🎯 Auto Update Complete
   - bahan_baku: Tepung Terigu
   - affected_products: 3
   - product_names: [Roti Tawar, Roti Manis, Kue Kering]
```

## ⚙️ Konfigurasi

### Register Observer (sudah dilakukan)

File: `app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    BahanBaku::observe(BahanBakuObserver::class);
    BahanPendukung::observe(BahanPendukungObserver::class);
}
```

## 🧪 Testing

### Test Manual

1. **Cek harga awal produk**
   ```
   GET /master-data/biaya-bahan
   - Lihat biaya bahan produk "Roti Tawar"
   - Catat: Rp 150.000
   ```

2. **Lakukan pembelian bahan baku dengan harga baru**
   ```
   POST /transaksi/pembelian/store
   - Beli Tepung Terigu 10kg @ Rp 55.000/kg (naik dari Rp 50.000)
   ```

3. **Cek harga produk setelah pembelian**
   ```
   GET /master-data/biaya-bahan
   - Lihat biaya bahan produk "Roti Tawar"
   - Harusnya naik otomatis: Rp 160.000 (naik Rp 10.000)
   ```

4. **Cek log**
   ```
   tail -f storage/logs/laravel.log
   - Lihat log auto-update
   ```

### Test Unit (Optional)

```php
// tests/Feature/BiayaBahanAutoUpdateTest.php
public function test_biaya_bahan_auto_update_on_price_change()
{
    // 1. Setup: Buat produk dengan BOM
    $produk = Produk::factory()->create();
    $bahanBaku = BahanBaku::factory()->create(['harga_satuan' => 50000]);
    
    $bom = Bom::create(['produk_id' => $produk->id]);
    BomDetail::create([
        'bom_id' => $bom->id,
        'bahan_baku_id' => $bahanBaku->id,
        'jumlah' => 2,
        'satuan' => 'kg',
        'harga_per_satuan' => 50000,
        'total_harga' => 100000
    ]);
    
    // 2. Update harga bahan baku
    $bahanBaku->update(['harga_satuan' => 60000]);
    
    // 3. Assert: BOM Detail ter-update
    $bomDetail = BomDetail::where('bom_id', $bom->id)->first();
    $this->assertEquals(60000, $bomDetail->harga_per_satuan);
    $this->assertEquals(120000, $bomDetail->total_harga);
    
    // 4. Assert: Biaya bahan produk ter-update
    $produk->refresh();
    $this->assertEquals(120000, $produk->biaya_bahan);
}
```

## 🎯 Keuntungan Sistem

### ✅ Otomatis
- Tidak perlu manual update biaya bahan
- Tidak perlu klik "Recalculate" berkali-kali
- Real-time update saat pembelian

### ✅ Akurat
- Harga selalu mengikuti pembelian terakhir
- Tidak ada selisih harga
- Mencegah kerugian

### ✅ Transparan
- Log lengkap setiap perubahan
- Audit trail jelas
- Mudah tracking

### ✅ Efisien
- Hemat waktu
- Mengurangi human error
- Scalable untuk banyak produk

## 🔍 Troubleshooting

### Problem: Biaya bahan tidak update otomatis

**Solusi:**
1. Cek observer sudah terdaftar di `AppServiceProvider`
2. Cek log error: `tail -f storage/logs/laravel.log`
3. Pastikan kolom `harga_satuan` berubah (bukan hanya `stok`)

### Problem: Harga update tapi salah perhitungan

**Solusi:**
1. Cek konversi satuan di `UnitConverter`
2. Cek relasi `satuan` di model
3. Cek log perhitungan di observer

### Problem: Performance lambat saat banyak produk

**Solusi:**
1. Gunakan queue untuk update async
2. Batch update produk
3. Cache hasil perhitungan

## 📚 File Terkait

```
app/
├── Observers/
│   ├── BahanBakuObserver.php          ← Observer bahan baku
│   └── BahanPendukungObserver.php     ← Observer bahan pendukung
├── Models/
│   ├── BahanBaku.php                  ← Model bahan baku
│   ├── BahanPendukung.php             ← Model bahan pendukung
│   ├── BomDetail.php                  ← BOM bahan baku
│   ├── BomJobBahanPendukung.php       ← BOM bahan pendukung
│   └── Produk.php                     ← Model produk
├── Http/Controllers/
│   ├── PembelianController.php        ← Trigger update harga
│   └── BiayaBahanController.php       ← View biaya bahan
└── Providers/
    └── AppServiceProvider.php         ← Register observer
```

## 🚀 Next Steps

### Enhancement Ideas:

1. **Queue Processing**
   ```php
   // Untuk update banyak produk secara async
   dispatch(new UpdateBiayaBahanJob($bahanBaku));
   ```

2. **Notification**
   ```php
   // Notif ke admin saat harga naik signifikan
   if ($hargaBaru > $hargaLama * 1.1) {
       Notification::send($admin, new HargaNaikNotification($bahanBaku));
   }
   ```

3. **History Tracking**
   ```php
   // Simpan history perubahan harga
   HargaHistory::create([
       'bahan_id' => $bahanBaku->id,
       'harga_lama' => $hargaLama,
       'harga_baru' => $hargaBaru,
       'tanggal' => now()
   ]);
   ```

4. **Auto Adjust Harga Jual**
   ```php
   // Auto adjust harga jual berdasarkan margin
   $produk->harga_jual = $produk->biaya_bahan * (1 + $produk->margin_percent / 100);
   ```

## ✅ Status

- [x] Observer BahanBaku created
- [x] Observer BahanPendukung created
- [x] Observer registered di AppServiceProvider
- [x] Auto-update BomDetail
- [x] Auto-update BomJobBahanPendukung
- [x] Auto-recalculate biaya_bahan produk
- [x] Logging & audit trail
- [x] Dokumentasi lengkap

## 🎉 Kesimpulan

Sistem ini memastikan **tidak ada kerugian** karena:
1. Harga biaya bahan selalu ter-update otomatis
2. BOM mengambil harga terbaru
3. Harga jual bisa disesuaikan berdasarkan biaya aktual
4. Transparan dan mudah di-audit

**Sistem siap digunakan!** 🚀

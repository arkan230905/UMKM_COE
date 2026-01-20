# 🧪 Quick Test Guide: BTKL & BOP Display

## ✅ WHAT WAS FIXED
BTKL dan BOP sekarang ditampilkan dengan benar di halaman Detail BOM menggunakan field names yang sesuai dengan model.

## 🎯 QUICK TEST (5 Menit)

### Step 1: Buka Detail BOM
```
URL: /master-data/bom/{id}
atau klik "Detail" dari halaman BOM Index
```

### Step 2: Scroll ke Section 3 - Proses Produksi

### Step 3: Verify Display

#### ✅ Jika Ada Proses Produksi:
```
Harus tampil:
- Tabel dengan kolom: No, Proses, Durasi, Satuan, Biaya BTKL, Biaya BOP, Total
- Detail per proses dengan urutan
- Tarif BTKL per proses
- Detail BOP breakdown (jika ada)
- Total BTKL dan Total BOP di footer
```

#### ✅ Jika Tidak Ada Proses, Ada BomJobCosting:
```
Harus tampil:
- Alert info: "BOM ini belum memiliki detail proses produksi"
- Section BTKL:
  * Nama proses / keterangan
  * Format: "X.XX jam × Rp XXX/jam"
  * Subtotal per item
- Section BOP:
  * Nama BOP
  * Format: "X.XX × Rp XXX"
  * Subtotal per item
- Total BTKL dan Total BOP
```

#### ✅ Jika Tidak Ada Proses & BomJobCosting:
```
Harus tampil:
- Alert warning: "BOM ini menggunakan perhitungan persentase"
- BTKL: Rp XXX (60% dari BBB)
- BOP: Rp XXX (40% dari BBB)
```

### Step 4: Verify Ringkasan HPP (Section 4)
```
Harus tampil:
- Total BBB: Rp XXX
- Total Bahan Pendukung: Rp XXX (jika ada)
- Total BTKL: Rp XXX (sama dengan Section 3)
- Total BOP: Rp XXX (sama dengan Section 3)
- HPP: Rp XXX (sum dari semua)
```

## 🔍 WHAT TO CHECK

### Format Display BTKL:
```
✅ Nama proses ditampilkan
✅ Format: "X.XX jam × Rp XXX/jam"
✅ Subtotal benar
✅ Total BTKL = sum dari semua subtotal
```

### Format Display BOP:
```
✅ Nama BOP ditampilkan
✅ Format: "X.XX × Rp XXX"
✅ Subtotal benar
✅ Total BOP = sum dari semua subtotal
```

### Perhitungan HPP:
```
✅ HPP = BBB + Bahan Pendukung + BTKL + BOP
✅ Persentase per komponen benar
✅ Total persentase = 100%
```

## ❌ COMMON ISSUES (FIXED)

### Before Fix:
```
❌ BTKL tidak tampil (field jumlah & tarif tidak ada)
❌ BOP tidak tampil (field kuantitas tidak ada)
❌ Error: "komponenBop" relationship not found
❌ Data kosong padahal ada di database
```

### After Fix:
```
✅ BTKL tampil dengan durasi_jam & tarif_per_jam
✅ BOP tampil dengan jumlah & tarif
✅ Relationship bop() berfungsi
✅ Data ditampilkan dengan benar
```

## 📊 EXPECTED RESULT

### Example Display (Scenario 2):
```
┌─────────────────────────────────────────────────────┐
│ 3. Proses Produksi (BTKL + BOP)                     │
├─────────────────────────────────────────────────────┤
│ ℹ️ BOM ini belum memiliki detail proses produksi.   │
│    Data BTKL dan BOP ditampilkan dari perhitungan   │
│    Job Costing.                                     │
├─────────────────────────────────────────────────────┤
│ 👷 Biaya Tenaga Kerja Langsung (BTKL)              │
│   Proses Mixing                                     │
│   2.00 jam × Rp 50.000/jam                         │
│                                    Rp 100.000       │
│   Proses Packaging                                  │
│   1.50 jam × Rp 50.000/jam                         │
│                                    Rp 75.000        │
├─────────────────────────────────────────────────────┤
│ ⚙️ Biaya Overhead Pabrik (BOP)                      │
│   Listrik                                           │
│   10.00 × Rp 5.000                                 │
│                                    Rp 50.000        │
│   Air                                               │
│   5.00 × Rp 3.000                                  │
│                                    Rp 15.000        │
├─────────────────────────────────────────────────────┤
│ Total BTKL                         Rp 175.000       │
│ Total BOP                          Rp 65.000        │
│ Total BTKL + BOP                   Rp 240.000       │
└─────────────────────────────────────────────────────┘
```

## 🎯 SUCCESS CRITERIA

Test berhasil jika:
- ✅ BTKL ditampilkan dengan format "X jam × Rp X/jam"
- ✅ BOP ditampilkan dengan format "X × Rp X"
- ✅ Subtotal per item benar
- ✅ Total BTKL dan Total BOP benar
- ✅ HPP di Ringkasan = BBB + Bahan Pendukung + BTKL + BOP
- ✅ Tidak ada error di console
- ✅ Tidak ada data kosong (jika ada data di database)

## 📝 NOTES

### Field Names Reference:
```php
// BomJobBTKL
durasi_jam       // Durasi dalam jam
tarif_per_jam    // Tarif per jam
subtotal         // durasi_jam × tarif_per_jam
nama_proses      // Nama proses
keterangan       // Keterangan tambahan

// BomJobBOP
jumlah           // Jumlah (NOT kuantitas)
tarif            // Tarif per unit
subtotal         // jumlah × tarif
nama_bop         // Nama BOP
keterangan       // Keterangan tambahan
```

### Relationships:
```php
// BomJobCosting
->detailBTKL     // hasMany BomJobBTKL
->detailBOP      // hasMany BomJobBOP

// BomJobBOP
->bop()          // belongsTo Bop (NOT komponenBop)
```

---
**Quick Test Duration**: ~5 menit
**Status**: ✅ Ready to test
**Last Updated**: 2025-01-15
